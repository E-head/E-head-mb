<?php

class PMS_Storage_Assets
{
	protected $_table;

    public function __construct()
    {
        $this->_table = new PMS_Storage_Assets_Table();
    }

    public function getList($params)
    {
        $response = new OSDN_Response();

        $select = $this->_table->getAdapter()->select()
            ->from(array('a' => $this->_table->getTableName()));

        if (isset($params['categoryId']) && intval($params['categoryId']) > 0) {
            $tableAC = new PMS_Storage_Assets_Categories_Table();
            $select->join(array('c' => $tableAC->getTableName()),
                $tableAC->getAdapter()->quoteInto(
                    'c.asset_id = a.id AND c.category_id = ?', intval($params['categoryId'])
                ),
            null);
        }
        $plugin = new OSDN_Db_Plugin_Select($this->_table, $select);
        $plugin->setSqlCalcFoundRows(true);
        $plugin->parse($params);

        try {
            $rows = $select->query()->fetchAll();
            $response->totalCount = $plugin->getTotalCountSql();
            $response->setRowset($rows);
            $status = PMS_Status::OK;
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            $status = PMS_Status::DATABASE_ERROR;
        }
        return $response->addStatus(new PMS_Status($status));
    }

    public function add(array $params)
    {
        $response = new OSDN_Response();

        $f = new OSDN_Filter_Input(array(
            '*'             => 'StringTrim'
        ), array(
            'name'          => array(array('StringLength', 1, 250), 'presence' => 'required'),
            'measure'       => array(array('StringLength', 1, 50), 'presence' => 'required'),
            'unit_price'    => array(array('Float', 'en'), 'allowEmpty' => true),
            'categories'    => array(array('StringLength', 0, 1000), 'presence' => 'required')
            // Here we use en locale to set "."(point) as deciminal separator
        ), $params);

        $response->addInputStatus($f);
        if ($response->hasNotSuccess()) {
            return $response;
        }

        $id = $this->_table->insert($f->getData());
        $status = $id ? PMS_Status::OK : PMS_Status::FAILURE;
        $response->id = $id;
        if ($id && !empty($f->categories)) {
            $categories = array_map('intval', split(',', $f->categories));
            $ac = new PMS_Storage_Assets_Categories();
            $resp = $ac->setAssetCategories($id, $categories);
            if ($resp->hasNotSuccess()) {
                return $response->importStatuses($resp->getStatusCollection());
            }
        }

        return $response->addStatus(new PMS_Status($status));
    }

    public function update(array $params)
    {
        $response = new OSDN_Response();

        $f = new OSDN_Filter_Input(array(
            'id'            => 'Int',
            '*'             => 'StringTrim'
        ), array(
            'id'            => array(array('Id', true)),
            'name'          => array(array('StringLength', 1, 250), 'presence' => 'required'),
            'measure'       => array(array('StringLength', 1, 50), 'presence' => 'required'),
            'unit_price'    => array(array('Float', 'en'), 'allowEmpty' => true),
            'categories'    => array(array('StringLength', 0, 1000), 'presence' => 'required')
        ), $params);

        $response->addInputStatus($f);
        if ($response->hasNotSuccess()) {
            return $response;
        }

        $this->_table->updateByPk($f->getData(), $f->id);
        if (!empty($f->categories)) {
            $categories = array_map('intval', split(',', $f->categories));
            $ac = new PMS_Storage_Assets_Categories();
            $resp = $ac->setAssetCategories($f->id, $categories);
            if ($resp->hasNotSuccess()) {
                return $response->importStatuses($resp->getStatusCollection());
            }
        }
        return $response->addStatus(new PMS_Status(PMS_Status::OK));
    }

    public function delete($id)
    {
        $id = intval($id);
        $response = new OSDN_Response();
        if ($id == 0) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'id'));
        }
        // TODO: check relations
        $this->_table->deleteByPk($id);
        return $response->addStatus(new PMS_Status(PMS_Status::OK));
    }

    public function check($params)
    {
        $id = intval($params['id']);
        $value = intval($params['value']);
        $response = new OSDN_Response();
        if ($id == 0) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'id'));
        }
        $this->_table->updateByPk(array('checked' => $value), $id);
        return $response->addStatus(new PMS_Status(PMS_Status::OK));
    }

    public function resetChecks()
    {
        $response = new OSDN_Response();
        $this->_table->update(array('checked' => 0), '');
        return $response->addStatus(new PMS_Status(PMS_Status::OK));
    }

    public function assetQtyUpdate($asset_id, $qty, $order_id = null, $reciever_id = null)
    {
        $response = new OSDN_Response();

        $validator = new OSDN_Validate_Id();
        if (!$validator->isValid($asset_id)) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'asset_id'));
        }
        if ($order_id !== null && !$validator->isValid($order_id)) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'order_id'));
        }
        if ($reciever_id !== null && !$validator->isValid($reciever_id)) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'reciever_id'));
        }

        $validator = new Zend_Validate_Int();
        if (!$validator->isValid($qty)) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'qty'));
        }

        $historyTable = new PMS_Storage_History_Table();

        $data = array(
            'asset_id'      => $asset_id,
            'qty'           => $qty,
            'order_id'      => intval($order_id) > 0 ? intval($order_id) : null,
            'sender_id'     => $reciever_id !== null ? OSDN_Accounts_Prototype::getId() : null,
            'reciever_id'   => $reciever_id !== null ? $reciever_id : OSDN_Accounts_Prototype::getId()
        );
        $id = $historyTable->insert($data);
        if (!$id) {
            return $response->addStatus(new PMS_Status(PMS_Status::ADD_FAILED));
        }

        $result = $this->updateQty($asset_id, $qty);
        if (!$result) {
            $historyTable->deleteByPk($id);
            return $response->addStatus(new PMS_Status(PMS_Status::UPDATE_FAILED));
        }

        return $response->addStatus(new PMS_Status(PMS_Status::OK));
    }

    public function updateQty($id, $qty)
    {
        $id  = intval($id);
        $qty = intval($qty);

        if ($id == 0) {
            return false;
        }

        $expr = $this->_table->getAdapter()->quoteInto('qty + ?', $qty);
        return $this->_table->updateByPk(array('qty' => new Zend_Db_Expr($expr)), $id);
    }

    public function getHistoryByAssetId($params)
    {
        $response = new OSDN_Response();

        $id = intval($params['asset_id']);
        if ($id == 0) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'asset_id'));
        }

        $select = $this->_table->getAdapter()->select()
        ->from(array('sh' => 'storage_history', array('id', 'order_id', 'qty', 'created')))
        ->joinLeft(array('ac' => 'accounts'),
            'sh.reciever_id=ac.id', array('account_name' => 'ac.name'))
        ->where('asset_id = ?', $id);

        $plugin = new OSDN_Db_Plugin_Select($this->_table, $select);
        $plugin->setSqlCalcFoundRows(true);
        $plugin->parse($params);

        try {
            $rows = $select->query()->fetchAll();
            $response->setRowset($rows);
            $response->totalCount = $plugin->getTotalCountSql();
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