<?php

class PMS_Goods_Model
{
    public function __construct()
    {
        $this->_table = new PMS_Goods_Table();
    }

    public function add(array $params)
    {
        $f = new OSDN_Filter_Input(array(
            '*'             => 'StringTrim'
        ), array(
            'name'          => array(array('StringLength', 1, 250), 'presence' => 'required'),
            'price'         => array(array('Float', 'en'), 'presence' => 'required'),
            'descr'         => array(array('StringLength', 0, 4096))
        ), $params);

        $response = new OSDN_Response();
        $response->addInputStatus($f);
        if ($response->hasNotSuccess()) {
            return $response;
        }

        $id = $this->_table->insert($f->getData());
        if (!$id) {
            return $response->addStatus(new PMS_Status(PMS_Status::ADD_FAILED));
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
            'name'          => array(array('StringLength', 1, 250), 'presence' => 'required'),
            'price'         => array(array('Float', 'en'), 'presence' => 'required'),
            'descr'         => array(array('StringLength', 0, 4096))
        ), $params);

        $response = new OSDN_Response();
        $response->addInputStatus($f);
        if ($response->hasNotSuccess()) {
            return $response;
        }

        $res = $this->_table->updateByPk($f->getData(), $f->id);
        if ($res === false) {
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

        $select = $this->_table->getAdapter()->select();
        $select->from(array('g' => $this->_table->getTableName()), '*');

        $plugin = new OSDN_Db_Plugin_Select($this->_table, $select);
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
}