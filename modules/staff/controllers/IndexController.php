<?php

class Staff_IndexController extends OSDN_Controller_Action
{
	/**
	 * @var PMS_class
	 */
	protected $_class;

	public function init()
	{
		$this->_class = new PMS_Staff();
		parent::init();
	}

    public function permission(OSDN_Controller_Action_Helper_Acl $acl)
    {
        $acl->setResource(OSDN_Acl_Resource_Generator::getInstance()->staff);
        $acl->isAllowed(OSDN_Acl_Privilege::VIEW, 'get-list');
        $acl->isAllowed(OSDN_Acl_Privilege::VIEW, 'get');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'add');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'update');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'delete');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'archive');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'change-category');
    }

	public function getListAction()
    {
    	$response = $this->_class->getList($this->_getAllParams());
    	if ($response->isSuccess()) {
    		$this->view->success = true;
    	    $this->view->data = $response->getRowset();
    	    $this->view->totalCount = $response->totalCount;
    	} else {
    		$this->_collectErrors($response);
    	}
    }

    public function getAction()
    {
    	$response = $this->_class->get($this->_getParam('id'));
    	if ($response->isSuccess()) {
    	    $this->view->success = true;
    	    $this->view->data = $response->getRow();
    	} else {
    	   $this->_collectErrors($response);
    	}
    }

    public function addAction()
    {
    	$response = $this->_class->add($this->_getAllParams());
    	if ($response->isSuccess()) {
    	    $this->view->success = true;
            $this->view->id = $response->id;
    	} else {
    	   $this->_collectErrors($response);
    	}
    }

    public function updateAction()
    {
    	$response = $this->_class->update($this->_getAllParams());
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

    public function archiveAction()
    {
    	$response = $this->_class->archive(
    	   $this->_getParam('id'),
    	   $this->_getParam('archive')
        );
    	if ($response->isSuccess()) {
    	    $this->view->success = true;
    	} else {
    	   $this->_collectErrors($response);
    	}
    }

    public function changeCategoryAction()
    {
        $response = $this->_class->changeCategory(
            $this->_getParam('id'),
            $this->_getParam('category_id')
        );
        if ($response->isSuccess()) {
            $this->view->success = true;
        } else {
           $this->_collectErrors($response);
        }
    }
}