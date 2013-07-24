<?php

class PMS_Storage_Assets_Categories
{
	protected $_table;

    public function __construct()
    {
        $this->_table = new PMS_Storage_Assets_Categories_Table();
    }

    public function getAssetCategories($id)
    {
        $id = intval($id);
        $response = new OSDN_Response();
        try {
            $data = $this->_table->select()
                ->from($this->_table->getTableName(), 'category_id')
                ->distinct(true)
                ->where('asset_id = ?', $id)
                ->query()->fetchAll(Zend_Db::FETCH_COLUMN);
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
        }

        $response->setRowset($data);
        return $response->addStatus(new PMS_Status(PMS_Status::OK));
    }

    public function setAssetCategories($id, $categories = array())
    {
        $id = intval($id);
        $response = $this->deleteAssetCategories($id);

        if ($response->hasNotSuccess()) {
            return $response;
        }

        if (!is_array($categories)) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'categories'));
        }

        try {
            foreach ($categories as $c) {
                $result = $this->_table->insert(array(
                    'asset_id'      => $id,
                    'category_id'   => intval($c)
                ));
                if ($result === false) {
                    return $response->addStatus(new PMS_Status(
                        PMS_Status::DATABASE_ERROR));
                }
            }
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
        }

        return $response->addStatus(new PMS_Status(PMS_Status::OK));
    }

    public function deleteAssetCategories($id)
    {
        $id = intval($id);
        $response = new OSDN_Response();
        if (!$id) {
            return $response->addStatus(new PMS_Status(PMS_Status::INPUT_PARAMS_INCORRECT, 'asset_id'));
        }

        try {
            $result = $this->_table->deleteQuote(array('asset_id = ?' => $id));
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
        }
        return $response->addStatus(new PMS_Status(PMS_Status::retrieveAffectedRowStatus($result)));
    }
}