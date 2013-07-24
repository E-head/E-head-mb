<?php

class PMS_Staff_Hr
{
	protected $_table, $_staffTable;

    public function __construct()
    {
        $this->_table = new PMS_Staff_Hr_Table();
        $this->_staffTable = new PMS_Staff_Table();
    }

    public function get($staffId, $date)
    {
        $response = new OSDN_Response();

        $staffId = intval($staffId);
        if ($staffId == 0) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'staff_id'));
        }

        $dateValidator = new Zend_Validate_Date();
        if (!$dateValidator->isValid($date)) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'date'));
        }

        /*
        $personRow = $this->_staffTable->findOne($staffId);
        if (!$personRow) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'staff_id'));
        }
        */

        $date = new Zend_Date($date);

        $lastDay = $date->setDay(1)->addMonth(1)->addDay(-1)->toValue(Zend_Date::DAY);

        $dates = array();
        for ($i = 1; $i <= $lastDay; $i++) {
            $dates[$date->setDay($i)->toString(MYSQL_DATE_FORMAT, 'php')] = '';
        }

        $select = $this->_table->getAdapter()->select()
            ->from($this->_table->getTableName())
            ->where('staff_id = ?', $staffId)
            ->where('date IN (\'?\')',
                new Zend_Db_Expr(implode("', '", array_keys($dates))));

        try {
            $rows = $select->query()->fetchAll();
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
        }

        $fields = array();
        foreach($dates as $k => $v) {
            $fields[$k] = array('name' => $k);
        }
        foreach($rows as $row) {
            //$fields[$row['date']] = array();
            $dates[$row['date']] = intval($row['value']);
        }

        $response->metadata = array_values($fields);
        $response->data = array(array_merge(array('id' => 1), $dates));
        return $response->addStatus(new PMS_Status(PMS_Status::OK));
    }

    public function set(array $params)
    {
        $response = new OSDN_Response();

        $f = new OSDN_Filter_Input(array(
            'staff_id'  => 'Int',
            '*'         => 'StringTrim'
        ), array(
            'staff_id'  => array('Id', 'presence' => 'required'),
            'data'      => array(array('StringLength', 0, 65535), 'presence' => 'required')
        ), $params);

        $response->addInputStatus($f);
        if ($response->hasNotSuccess()) {
            return $response;
        }

        try {
            $data = Zend_Json::decode($f->data);
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'data'));
        }
        if (!is_array($data) || empty($data)) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'data'));
        }

        $validateKey = new Zend_Validate_Date();
        $validateValue = new Zend_Validate_Between(0, 24, true);
        foreach($data as $key => $value) {
            if (!$validateKey->isValid($key) || !$validateValue->isValid($value)) {
                return $response->addStatus(new PMS_Status(
                    PMS_Status::INPUT_PARAMS_INCORRECT, 'data'));
            }
        }

        $select = $this->_table->select()
            ->where('staff_id = ?', $f->staff_id)
            ->where('date IN (\'?\')',
                new Zend_Db_Expr(implode("', '", array_keys($data))));

        try {
            $rowsHr = $select->query()->fetchAll();
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
        }

        foreach($rowsHr as $row) {
            $value = intval($data[$row['date']]);
            if ($value != intval($row['value'])) {
                $count = $this->_table->updateByPk(array('value' => $value), $row['id']);
                if ($count == 0) {
                    return $response->addStatus(new PMS_Status(PMS_Status::UPDATE_FAILED));
                }
            }
            unset($data[$row['date']]);
        }

        if (empty($data)) {
            return $response->addStatus(new PMS_Status(PMS_Status::OK));
        }

        $personRow = $this->_staffTable->findOne($f->staff_id);
        if (!$personRow) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'staff_id'));
        }
        $person = $personRow->toArray();

        foreach($data as $key => $value) {
            $res = $this->_table->insert(array(
                    'staff_id'      => $f->staff_id,
                    'date'          => $key,
                    'value'         => $value,
                    'pay_period'    => $person['pay_period'],
                    'pay_rate'      => $person['pay_rate']
                )
            );
            if (!$res) {
                return $response->addStatus(new PMS_Status(PMS_Status::ADD_FAILED));
            }
        }
        return $response->addStatus(new PMS_Status(PMS_Status::OK));
    }
}