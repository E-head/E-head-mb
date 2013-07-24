<?php

class Staff_HrController extends OSDN_Controller_Action
{
	/**
	 * @var PMS_class
	 */
	protected $_class;

	public function init()
	{
		$this->_class = new PMS_Staff_Hr();
		parent::init();
	}

    public function permission(OSDN_Controller_Action_Helper_Acl $acl)
    {
        $acl->setResource(OSDN_Acl_Resource_Generator::getInstance()->staff);
        $acl->isAllowed(OSDN_Acl_Privilege::VIEW, 'get');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'set');
    }

    public function getAction()
    {
    	$response = $this->_class->get(
    	   $this->_getParam('staff_id'),
    	   $this->_getParam('date')
        );
    	if ($response->isSuccess()) {
    	    $this->view->success = true;
    	    $this->view->metaData = $response->metaData;
    	    $this->view->data = $response->data;
    	} else {
    	   $this->_collectErrors($response);
    	}
    }

    public function setAction()
    {
    	$response = $this->_class->set($this->_getAllParams());
    	if ($response->isSuccess()) {
    	    $this->view->success = true;
    	} else {
    	   $this->_collectErrors($response);
    	}
    }
}