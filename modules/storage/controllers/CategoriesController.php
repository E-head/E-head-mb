<?php

class Storage_CategoriesController extends OSDN_Controller_Action
{
	/**
	 * @var PMS_class
	 */
	protected $_class;

	public function init()
	{
		$this->_class = new PMS_Storage_Categories();
		parent::init();
	}

    public function permission(OSDN_Controller_Action_Helper_Acl $acl)
    {
        $acl->setResource(OSDN_Acl_Resource_Generator::getInstance()->storage);
        $acl->isAllowed(OSDN_Acl_Privilege::VIEW, 'get-complete-tree-checked');
        $acl->isAllowed(OSDN_Acl_Privilege::VIEW, 'get');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'add');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'update');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'delete');
    }

	public function getCompleteTreeCheckedAction()
    {
        $asset = intval($this->_getParam('asset_id'));

        $checked = array();
        if (0 < $asset) {
            $assetsCategories = new PMS_Storage_Assets_Categories();
            $response = $assetsCategories->getAssetCategories($asset);
            if ($response->hasNotSuccess()) {
                $this->_collectErrors($response);
            }
            $checked = $response->getRowset();
        }

        $response = $this->_class->getCompleteTreeChecked($checked);
        if ($response->isSuccess()) {
            $this->view->assign($response->getRowset());
        } else {
            $this->_collectErrors($response);
        }
    }

	public function getAction()
    {
        $response = $this->_class->getListByParent($this->_getParam('node'));
        if ($response->isSuccess()) {
            $this->view->assign($response->getRowset());
        } else {
            $this->_collectErrors($response);
        }
    }

    public function addAction()
    {
        $response = $this->_class->add($this->_getParam('name'), $this->_getParam('parent'));
        if ($response->isSuccess()) {
            $this->view->success = true;
            $this->view->id = $response->id;
        } else {
           $this->_collectErrors($response);
        }
    }

    public function updateAction()
    {
        $response = $this->_class->update($this->_getParam('name'), $this->_getParam('node'));
        if ($response->isSuccess()) {
            $this->view->success = true;
        } else {
           $this->_collectErrors($response);
        }
    }

    public function deleteAction()
    {
        $response = $this->_class->delete($this->_getParam('id'));
        if ($response->isSuccess()) {
            $this->view->success = true;
        } else {
           $this->_collectErrors($response);
        }
    }
}