<?php

class PMS_Staff_Payments
{
	protected $_table;

    public function __construct()
    {
        $this->_table = new PMS_Staff_Payments_Table();
    }

    public function getList($params)
    {
        $response = new OSDN_Response();

        if (!isset($params['staff_id']) || intval($params['staff_id']) == 0) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'staff_id'));
        }

        $select = $this->_table->getAdapter()
            ->select()
            ->from($this->_table->getTableName())
            ->where('staff_id = ?', intval($params['staff_id']))
        ;

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
            'staff_id'  => 'Int',
            'value'     => 'Int',
            '*'         => 'StringTrim'
        ), array(
            'date'      => array(array('StringLength', 1, 10), 'presence' => 'required'),
            'staff_id'  => array('Id', 'allowEmpty' => false, 'presence' => 'required'),
            'value'     => array('Id', 'allowEmpty' => false, 'presence' => 'required')
        ), $params);
        $response->addInputStatus($f);
        if ($response->hasNotSuccess()) {
            return $response;
        }

        $data = $f->getData();

        try {
            $id = $this->_table->insert($data);
            $status = $id ? PMS_Status::OK : PMS_Status::FAILURE;
            $response->id = $id;
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            $status = PMS_Status::DATABASE_ERROR;
        }

        return $response->addStatus(new PMS_Status($status));
    }

    public function update(array $params)
    {
        $response = new OSDN_Response();

        $f = new OSDN_Filter_Input(array(
            'id'        => 'Int',
            'staff_id'  => 'Int',
            'value'     => 'Int',
            '*'         => 'StringTrim'
        ), array(
            'id'        => array(array('Id'), 'presence' => 'required'),
            'date'      => array(array('StringLength', 1, 10), 'presence' => 'required'),
            'staff_id'  => array('Id'),
            'value'     => array('Id', 'allowEmpty' => false, 'presence' => 'required')
        ), $params);
        $response->addInputStatus($f);
        if ($response->hasNotSuccess()) {
            return $response;
        }

        try {
            $this->_table->updateByPk($f->getData(), $f->id);
            $status = PMS_Status::OK;
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            $status = PMS_Status::DATABASE_ERROR;
        }

        return $response->addStatus(new PMS_Status($status));
    }

    public function recalculate()
    {
        $response = new OSDN_Response();

        $select = $this->_table->getAdapter()->select()
        ->from($this->_table->getTableName())->where('paid < value')->order('date');

        try {
            $rowsPayments = $select->query()->fetchAll();
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
        }

        $result = array();

        if ($rowsPayments) {

            $tableHr = new PMS_Staff_Hr_Table();

            foreach ($rowsPayments as $rowPayment) {

                $select = $tableHr->getAdapter()->select()->from($tableHr->getTableName())
                    ->where('staff_id = ?', intval($rowPayment['staff_id']))
                    ->where('value > 0')
                    ->where('paid < (value * pay_rate)')
                    ->order('date');

                try {
                    $rowsHr = $select->query()->fetchAll();
                } catch (Exception $e) {
                    if (OSDN_DEBUG) {
                        throw $e;
                    }
                    return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
                }

                if ($rowsHr) {

                    $rest = $rowPayment['value'] - $rowPayment['paid'];

                    $debug = array('rest' => array(), 'restHr' => array());
                    $debug['rest'][] = $rest;

                    foreach ($rowsHr as $hr) {

                        $restHr = $hr['value'] * $hr['pay_rate'] - $hr['paid'];

                        $debug['restHr'][] = $restHr;

                        if ($rest >= $hr['pay_rate']) {

                            $paid = $hr['value'] * $hr['pay_rate'];
                            while ($paid > $restHr) {
                                $paid = $paid - $hr['pay_rate'];
                            }

                            $res = $tableHr->updateByPk(array('paid' => $paid), $hr['id']);
                            if (!$res) {
                                return $response->addStatus(
                                    new PMS_Status(PMS_Status::DATABASE_ERROR));
                            }

                            $rest = $rest - $paid;
                        }
                    }
                    $debug['rest'][] = $rest;
                }

                $rowPayment['debug'] = $debug;
                $rowPayment['rowsHr'] = $rowsHr;

                $result[] = $rowPayment;

                $paid = $rowPayment['value'] - $rest;
                $res = $this->_table->updateByPk(array('paid' => $paid), $rowPayment['id']);
                if (!$res) {
                    return $response->addStatus(
                        new PMS_Status(PMS_Status::DATABASE_ERROR));
                }
            }
        }

        $response->setRowset($result);
        return $response->addStatus(new PMS_Status(PMS_Status::OK));
    }
}