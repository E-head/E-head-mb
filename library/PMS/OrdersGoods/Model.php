<?php

class PMS_OrdersGoods_Model
{
    public function __construct()
    {
        $this->_table = new PMS_OrdersGoods_Table();
    }

    public function getList(array $params)
    {
        $response = new OSDN_Response();

        $validate = new OSDN_Validate_Id();
        if (!isset($params['order_id']) || !$validate->isValid($params['order_id'])) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'order_id'));
        }

        $orders = new PMS_Orders_Table();
        $goods = new PMS_Goods_Table();

        $select = $this->_table->getAdapter()->select();
        $select->from(array('og' => $this->_table->getTableName()),
            array('id', 'number', 'summ' => new Zend_Db_Expr('number * price') ))
        ->join(array('o' => $orders->getTableName()), 'og.order_id=o.id', array())
        ->join(array('g' => $goods->getTableName()), 'og.good_id=g.id',
            array('name', 'price'))
        ->where('order_id = (?)', $params['order_id']);

        try {
            $rows = $select->query()->fetchAll();
            $response->setRowset($rows);
            $status = PMS_Status::OK;
        } catch (Exception $e) {
            $status = PMS_Status::DATABASE_ERROR;
            if (OSDN_DEBUG) {
                throw $e;
            }
        }
        return $response->addStatus(new PMS_Status($status));
    }

    public function add(array $params)
    {
        $f = new OSDN_Filter_Input(array(
            '*'             => 'StringTrim'
        ), array(
            'order_id'  => array('Int', 'presence' => 'required'),
            'good_id'   => array('Int', 'presence' => 'required')
        ), $params);

        $response = new OSDN_Response();
        $response->addInputStatus($f);
        if ($response->hasNotSuccess()) {
            return $response;
        }

        $id = $this->_table->insert($f->getData());
        $status = $id ? PMS_Status::OK : PMS_Status::FAILURE;
        $response->addData('id', $id);
        return $response->addStatus(new PMS_Status($status));
    }

    public function update(array $params)
    {
        $f = new OSDN_Filter_Input(array(
            '*'             => 'StringTrim'
        ), array(
            'id'        => array('Int', 'presence' => 'required'),
            'number'    => array('Int', 'presence' => 'required')
        ), $params);

        $response = new OSDN_Response();
        $response->addInputStatus($f);
        if ($response->hasNotSuccess()) {
            return $response;
        }
        $res = $this->_table->updateByPk($f->getData(), $f->id);
        $status = $res === false ? PMS_Status::FAILURE : PMS_Status::OK;
        return $response->addStatus(new PMS_Status($status));
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
}