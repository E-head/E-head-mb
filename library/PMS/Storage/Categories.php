<?php

class PMS_Storage_Categories
{
	protected $_table;

    public function __construct()
    {
        $this->_table = new PMS_Storage_Categories_Table();
    }

    public function get($id)
    {
        $id = intval($id);
        $response = new OSDN_Response();
        try {
            $row = $this->_table->findOne($id);
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
        }

        if ($row === false) {
            return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
        }

        $response->setRow(is_null($row) ? array() : $row->toArray());
        return $response->addStatus(new PMS_Status(PMS_Status::OK));
    }

    public function getCompleteTreeChecked($checked = array())
    {
        $response = new OSDN_Response();

        $nodes = $this->_walkTree(0, $checked);
        if (false === $nodes) {
            $status = PMS_Status::DATABASE_ERROR;
            $response->setRowset(array());
        } else {
            $status = PMS_Status::OK;
            $response->setRowset($nodes);
        }
        return $response->addStatus(new PMS_Status($status));
    }

    public function getListByParent($parent = 0)
    {
        $response = new OSDN_Response();
        $rowset = $this->_getChildNodes($parent);
        if (false === $rowset) {
            return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
        }

        // Form array for tree node
        $nodes = array();
        foreach ($rowset as $row) {
            $nodes[] = array(
                'id'        => $row['id'],
                'text'      => $row['name']
            );
        }
        $response->setRowset($nodes);
        return $response->addStatus(new PMS_Status(PMS_Status::OK));
    }

    public function add($name = '', $parent = 0)
    {
        $parent = intval($parent);
        $response = new OSDN_Response();
        $data = array('name' => $name);
        if ($parent) {
            $data['parent_id'] = $parent;
        }
        try {
            $id = $this->_table->insert($data);
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
        }
        $response->id = $id;
        return $response->addStatus(new PMS_Status(PMS_Status::OK));
    }

    public function update($name, $id)
    {
        $id = intval($id);
        $name = trim($name);
        $response = new OSDN_Response();
        $validator = new Zend_Validate_StringLength(1, 250);
        if ($id == 0 || !$validator->isValid($name)) {
            return $response->addStatus(new PMS_Status(PMS_Status::INPUT_PARAMS_INCORRECT));
        }
        $data = array('name' => $name);
        try {
            $result = $this->_table->updateByPk($data, $id);
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
        }
        return $response->addStatus(new PMS_Status(PMS_Status::retrieveAffectedRowStatus($result)));
    }

    public function delete($id)
    {
        $id = intval($id);
        $response = new OSDN_Response();
        if (!$id) {
            return $response->addStatus(new PMS_Status(PMS_Status::INPUT_PARAMS_INCORRECT));
        }

        // Check if category contain subcategories
        $resp = $this->getListByParent($id);
        if ($resp->isSuccess()) {
            $rows = $resp->getRowset();
            if (count($rows) > 0) {
                return $response->addStatus(new PMS_Status(PMS_Status::DELETE_FAILED));
            }
        } else {
            return $response->import($resp);
        }
        // Check if category contain assets
        $assets = new PMS_Storage_Assets();
        $resp = $assets->getList(array('categoryId' => $id));
        if ($resp->isSuccess()) {
            if ($resp->totalCount > 0) {
                return $response->addStatus(new PMS_Status(PMS_Status::DELETE_FAILED));
            }
        } else {
            return $response->import($resp);
        }

        try {
            $result = $this->_table->deleteByPk($id);
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
        }
        return $response->addStatus(new PMS_Status(PMS_Status::retrieveAffectedRowStatus($result)));
    }

    /* Private methods */

    private function _getChildNodes($parent = 0)
    {
        $parent = intval($parent);
        $where = $parent ? array('parent_id = ?' => $parent) : array('parent_id IS NULL');
        try {
            $rowset = $this->_table->fetchAll($where, 'name ASC')->toArray();
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return false;
        }
        return $rowset;
    }

    private function _walkTree($parent = 0, &$checked = array())
    {
        $parent = intval($parent);
        $where = $parent ? array('parent_id = ?' => $parent) : array('parent_id IS NULL');
        try {
            $rowset = $this->_table->fetchAll($where, 'name ASC')->toArray();
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return false;
        }

        // Form array for tree node
        $nodes = array();
        foreach ($rowset as $row) {
            $nodes[] = array(
                'id'        => $row['id'],
                'checked'   => in_array($row['id'], $checked),
                'text'      => $row['name'],
                'children'  => $this->_walkTree($row['id'], $checked)
            );
        }
        return $nodes;
    }
}