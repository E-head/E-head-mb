<?php

class FixedAssets_FilesController extends OSDN_Controller_Action
{
	/**
	 * @var PMS_Files
	 */
	protected $_class;

	public function init()
	{
		$this->_class = new PMS_FixedAssets_Files();
		parent::init();
	}

    public function permission(OSDN_Controller_Action_Helper_Acl $acl)
    {
        $acl->setResource(OSDN_Acl_Resource_Generator::getInstance()->admin);
        $acl->isAllowed(OSDN_Acl_Privilege::VIEW,   'get-all');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'upload');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'update');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'delete');
    }

    public function getAllAction()
    {
        $response = $this->_class->getAll($this->_getParam('item_id'));
        if ($response->isSuccess()) {
            $this->view->success = true;
            $this->view->files = $response->getRowset();
        } else {
           $this->_collectErrors($response);
        }
    }

    public function uploadAction()
    {
        $response = $this->_class->upload($this->_getParam('item_id'), $_FILES['file']);
        $this->view->success = $response->isSuccess();
    }

    public function updateAction()
    {
        $response = $this->_class->update(array(
            'id'          => $this->_getParam('id'),
            'description' => $this->_getParam('description')
        ));
        $this->view->success = $response->isSuccess();
    }

    public function deleteAction()
    {
        $response = $this->_class->delete($this->_getParam('id'));
        $this->view->success = $response->isSuccess();
    }
}