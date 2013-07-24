<?php

class PMS_FixedAssets_Files
{
    public function __construct()
    {
        $this->_table = new PMS_FixedAssets_Files_Table();
    }

    public function getAll($itemId)
    {
    	$response = new OSDN_Response();
        $validate = new OSDN_Validate_Id();
        if (!$validate->isValid($itemId)) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'itemId'));
        }
        try {
            $response->setRowset($this->_table->fetchAll(
            	array('item_id = ?' => $itemId))->toArray());
            $status = PMS_Status::OK;
        } catch (Exception $e) {
            $status = PMS_Status::DATABASE_ERROR;
            if (OSDN_DEBUG) {
                throw $e;
            }
        }
        return $response->addStatus(new PMS_Status($status));
    }

    public function upload($itemId, $file)
    {
    	$response = new OSDN_Response();
        $validate = new OSDN_Validate_Id();
        if (!$validate->isValid($itemId)) {
            $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'itemId'));
            return $response;
        }
        if ($file['error'] > 0) {
            $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'file'));
            return $response;
        }
        $filenameArray = split('\.', $file['name']);
        $ext = array_pop($filenameArray);
       	$filename = 'fixed_assets_' . uniqid() . '.' . $ext;
       	$filepath = FILES_DIR . '/' . $filename;
        try {
        	if (move_uploaded_file($file['tmp_name'], $filepath)) {
	            $this->_table->insert(array(
	               'item_id'       => $itemId,
	               'filename'      => $filename,
	               'is_photo'      => getimagesize($filepath) ? 1 : 0,
	               'description'   => join(' ', $filenameArray)
	            ));
	            $status = PMS_Status::OK;
        	} else {
        		$status = PMS_Status::ADD_FAILED;
        	}
        } catch (Exception $e) {
            $status = PMS_Status::DATABASE_ERROR;
            if (OSDN_DEBUG) {
                throw $e;
            }
        }
        return $response->addStatus(new PMS_Status($status));
    }

    public function update($params)
    {
        $response = new OSDN_Response();
        $f = new OSDN_Filter_Input(array(
            'id'            => 'Int',
            'description'   => 'StringTrim'
        ), array(
            'id'            => array('id', 'presence' => 'required'),
            'description'   => array(array('StringLength', 0, 255))
        ), $params);
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
        $file = $this->_table->find($id)->toArray();
        if (!count($file)) {
        	return $response->addStatus(new PMS_Status(
        		PMS_Status::INPUT_PARAMS_INCORRECT, 'id'));
        }
        if (empty($file[0]['filename'])) {
			return $response->addStatus(new PMS_Status(PMS_Status::DELETE_FAILED));
        }
        $filename = FILES_DIR . '/' . $file[0]['filename'];
        if (file_exists($filename) && !@unlink($filename)) {
			return $response->addStatus(new PMS_Status(PMS_Status::DELETE_FAILED));
		}
        $affectedRows = $this->_table->deleteByPk($id);
        $status = PMS_Status::retrieveAffectedRowStatus($affectedRows);
		return $response->addStatus(new PMS_Status($status));
    }
}