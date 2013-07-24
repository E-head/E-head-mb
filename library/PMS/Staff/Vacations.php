<?php

class PMS_Staff_Vacations
{
	protected $_table;

    public function __construct()
    {
        $this->_table = new PMS_Staff_Vacations_Table();
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
            '*'         => 'StringTrim'
        ), array(
            'from'      => array(array('StringLength', 1, 10), 'presence' => 'required'),
            'to'        => array(array('StringLength', 1, 10), 'presence' => 'required'),
            'staff_id'  => array('Id', 'allowEmpty' => false, 'presence' => 'required')
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
            '*'         => 'StringTrim'
        ), array(
            'id'        => array(array('Id'), 'presence' => 'required'),
            'from'      => array(array('StringLength', 1, 10), 'presence' => 'required'),
            'to'        => array(array('StringLength', 1, 10), 'presence' => 'required')
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

    public function delete($id)
    {
        $id = intval($id);
        $response = new OSDN_Response();
        if ($id == 0) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'id'));
        }
        // TODO: check relations
        try {
            $this->_table->deleteByPk($id);
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