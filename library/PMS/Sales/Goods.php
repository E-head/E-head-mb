<?php

class PMS_Sales_Goods
{
    public function __construct()
    {
        $this->_table = new PMS_Sales_Goods_Table();
    }

    public function add(array $params)
    {
        $f = new OSDN_Filter_Input(array(
            '*'             => 'StringTrim'
        ), array(
            'code'          => array(array('StringLength', 1, 4), 'presence' => 'required'),
            'name'          => array(array('StringLength', 1, 250), 'presence' => 'required'),
            'price'         => array(array('Float', 'en'), 'presence' => 'required'),
            'measure'       => array(array('StringLength', 1, 50), 'presence' => 'required'),
            'loss_margin'   => array('Int', 'presence' => 'required')
        ), $params);

        $response = new OSDN_Response();
        $response->addInputStatus($f);
        if ($response->hasNotSuccess()) {
            return $response;
        }

        $ingredients = Zend_Json::decode($f->ingredients);
        if (!is_array($ingredients)) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'ingredients'));
        }

        $id = $this->_table->insert($f->getData());
        if (!$id) {
            return $response->addStatus(new PMS_Status(PMS_Status::ADD_FAILED));
        }

        $res = $this->setRelations($id, $ingredients);
        if ($res === false) {
            $this->deleteRelations($id);
            return $response->addStatus(new PMS_Status(PMS_Status::FAILURE));
        }

        $response->addData('id', $id);
        return $response->addStatus(new PMS_Status(PMS_Status::OK));
    }

    public function update(array $params)
    {
        $f = new OSDN_Filter_Input(array(
            '*'             => 'StringTrim'
        ), array(
            'id'            => array('Int', 'presence' => 'required'),
            'code'          => array(array('StringLength', 1, 4), 'presence' => 'required'),
            'name'          => array(array('StringLength', 1, 250), 'presence' => 'required'),
            'price'         => array(array('Float', 'en'), 'presence' => 'required'),
            'measure'       => array(array('StringLength', 1, 50), 'presence' => 'required'),
            'loss_margin'   => array('Int', 'presence' => 'required')
        ), $params);

        $response = new OSDN_Response();
        $response->addInputStatus($f);
        if ($response->hasNotSuccess()) {
            return $response;
        }

        $ingredients = Zend_Json::decode($f->ingredients);
        if (!is_array($ingredients)) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'ingredients'));
        }

        $res = $this->_table->updateByPk($f->getData(), $f->id);
        if ($res === false) {
            return $response->addStatus(new PMS_Status(PMS_Status::FAILURE));
        }

        $res = $this->deleteRelations($f->id);
        if ($res === false) {
            return $response->addStatus(new PMS_Status(PMS_Status::FAILURE));
        }

        $res = $this->setRelations($f->id, $ingredients);
        if ($res === false) {
            $this->deleteRelations($f->id);
            return $response->addStatus(new PMS_Status(PMS_Status::FAILURE));
        }

        return $response->addStatus(new PMS_Status(PMS_Status::OK));
    }

    public function delete($id)
    {
        $response = new OSDN_Response();
        $validate = new OSDN_Validate_Id();
        if (!$validate->isValid($id)) {
            return $response->addStatus(new PMS_Status(
            	PMS_Status::INPUT_PARAMS_INCORRECT, 'id'));
        }
        $affectedRows = $this->_table->deleteByPk($id);
        return $response->addStatus(new PMS_Status(
            PMS_Status::retrieveAffectedRowStatus($affectedRows)
        ));
    }

    /**
     * Retrieve rows
     *
     * @param array $params
     * The param examples<code>
     *      sort    => 'name'
     *      dir     => 'ASC'
     *      limit   => 20
     *      start   => 1
     *      ...
     *      filter[0][data][type]   string
     *      filter[0][data][value]  1
     *      filter[0][field]        alias
     * </code>
     * @param array $where      The array of where clauses<code>
     *  array(
     *      array('name = ?' => test),
     *      array('id' => 1)
     *  );</code>
     *
     * @return OSDN_Response
     * Details of contain data <code>
     *      rows array  the rows collection
     *      total int   the total count of rows
     * </code>
     */
    public function getList(array $params)
    {
        $response = new OSDN_Response();

        $expTable = new PMS_Sales_Expendables_Table();
        $relTable = new PMS_Sales_GoodsExpendables_Table();

        $select = $this->_table->getAdapter()->select();
        $select->from(array('g' => $this->_table->getTableName()), array())
               ->joinLeft(array('r' => $relTable->getTableName()),
                        'r.goods_id=g.id', array()
               )
               ->joinLeft(array('e' => $expTable->getTableName()),
                        'r.expendables_id=e.id', array()
               )
               ->columns(
                    array('g.id', 'g.code', 'g.name', 'g.price', 'g.measure',
                        'total_cost' => new Zend_Db_Expr('SUM(
                                  (r.qty * e.price)
                                + ((r.qty * e.price) / 100 * g.loss_margin)
                            )'
                        )
                    )
               )
               ->group('g.id');

        $plugin = new OSDN_Db_Plugin_Select($this->_table, $select, array(
            'g.code' => 'code',
            'g.name' => 'name'
        ));
        $plugin->parse($params);
        $status = null;
        try {
        	$rows = $select->query()->fetchAll();
            $response->setRowset($rows);
            $response->totalCount = $plugin->getTotalCount();
            $status = PMS_Status::OK;
        } catch (Exception $e) {
            $status = PMS_Status::DATABASE_ERROR;
            if (OSDN_DEBUG) {
                throw $e;
            }
        }
        return $response->addStatus(new PMS_Status($status));
    }

    public function get($id)
    {
        $response = new OSDN_Response();
        $validate = new OSDN_Validate_Id();
        if (!$validate->isValid($id)) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'id'));
        }
        $select = $this->_table->getAdapter()->select()
            ->from($this->_table->getTableName())
            ->where("id = ?", $id);
        try {
            $row = $select->query()->fetch();
            $row['ingredients'] = $this->getRelations($id);
            $response->setRow($row);
            $status = PMS_Status::OK;
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            $status = PMS_Status::DATABASE_ERROR;
        }

        return $response->addStatus(new PMS_Status($status));
    }

    /**
     *
     * @param string $code
     * @return int|bool good id | false if not found
     */
    public function getGoodsIdByCode($code)
    {
        $select = $this->_table->getAdapter()->select()
            ->from($this->_table->getTableName())
            ->where("code = ?", $code);
        try {
            $row = $select->query()->fetch();
            if (!is_array($row)) {
                return false;
            }
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return false;
        }

        return $row['id'];
    }

    /**
     * Get rows, related to the given goods id
     *
     * @param int $goodsId
     * @return array|bool rows | false if error
     *
     */
    public function getRelations($goodsId)
    {
        $expTable = new PMS_Sales_Expendables_Table();
        $relTable = new PMS_Sales_GoodsExpendables_Table();

        $select = $this->_table->getAdapter()->select();
        $select->from(array('e' => $expTable->getTableName()))
            ->join(array('r' => $relTable->getTableName()),
                    'r.expendables_id=e.id',
                    array('qty', 'cost' => new Zend_Db_Expr('r.qty * e.price')))
            ->where('r.goods_id = ?', $goodsId);

        try {
            $rows = $select->query()->fetchAll();
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return false;
        }

        return $rows;
    }

    /**
     * Delete relations by the given goods id
     *
     * @param int $goodsId
     * @return int|bool number of deleted records | false if error
     *
     */
    private function deleteRelations($goodsId)
    {
        $table = new PMS_Sales_GoodsExpendables_Table();
        return $table->delete(array('goods_id = ?' => $goodsId));
    }

    /**
     * Set relations for the given goods id
     *
     * @param int $goodsId
     * @param array $relations = array(array(expendables_id, qty), ...)
     * @return bool
     *
     */
    private function setRelations($goodsId, array $relations)
    {
        $table = new PMS_Sales_GoodsExpendables_Table();

        foreach($relations as $relation) {

            if (!is_array($relation) || count($relation) != 2) {
                continue;
            }

            $res = $table->insert(array(
                'goods_id'          => $goodsId,
                'expendables_id'    => $relation[0],
                'qty'               => $relation[1]
            ));

            if ($res === false) {
                return false;
            }
        }

        return true;
    }

}