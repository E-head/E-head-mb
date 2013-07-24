<?php

/**
 * Reports conroller
 * @version $Id: $
 */
class Staff_ReportController extends OSDN_Controller_Action
{
    protected $_reports;

	public function init()
	{
		$this->_reports = new PMS_Staff_Reports();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->setLayout('report');
	}

    public function permission(OSDN_Controller_Action_Helper_Acl $acl)
    {
        $acl->setResource(OSDN_Acl_Resource_Generator::getInstance()->staff);
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'index');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'vacations');
    }

    public function indexAction()
    {
    	$response = $this->_reports->generateStaff($this->_getAllParams());
    	if ($response->isSuccess()) {
	    	$this->view->data = $response->data;
	        $this->view->content = $this->view->render('report/index.phtml');
    	} else {
    		$this->_collectErrors($response);
    	}
    }

    public function vacationsAction()
    {
    	$response = $this->_reports->generateVacations($this->_getAllParams());
    	if ($response->isSuccess()) {
	    	$this->view->data = $response->data;
	        $this->view->content = $this->view->render('report/vacations.phtml');
    	} else {
    		$this->_collectErrors($response);
    	}
    }
}
