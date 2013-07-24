<?php

class PMS_Storage_Measures
{
	protected $_table;

    public function __construct()
    {
        $this->_table = new PMS_Storage_Measures_Table();
    }

    public function getAll()
    {
        $response = new OSDN_Response();

        try {
            $rows = $this->_table->fetchAll()->toArray();
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

    public function add($name)
    {
        $response = new OSDN_Response();

        $validator = new Zend_Validate_StringLength(1, 50);

        if (!$validator->isValid($name)) {
            return $response->addStatus(new PMS_Status(PMS_Status::INPUT_PARAMS_INCORRECT, 'name'));
        }

        try {
            $rows = $this->_table->find($name)->toArray();
            if (count($rows) > 0) {
                $status = PMS_Status::ADD_FAILED;
            } else {
                $id = $this->_table->insert(array('name' => $name));
                $status = $id ? PMS_Status::OK : PMS_Status::FAILURE;
            }
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            $status = PMS_Status::DATABASE_ERROR;
        }

        return $response->addStatus(new PMS_Status($status));
    }

    public function delete($name)
    {
        $response = new OSDN_Response();

        $validator = new Zend_Validate_StringLength(1, 50);

        if (!$validator->isValid($name)) {
            return $response->addStatus(new PMS_Status(PMS_Status::INPUT_PARAMS_INCORRECT, 'name'));
        }

        try {
            $rows = $this->_table->deleteQuote(array('name = ?' => $name));
            $status = $rows ? PMS_Status::OK : PMS_Status::DELETE_FAILED;
        } catch (Exception $e) {
            $status = PMS_Status::DATABASE_ERROR;
            if (OSDN_DEBUG) {
                throw $e;
            }
        }
        return $response->addStatus(new PMS_Status($status));
    }
}