<?php

/**
 * Reports conroller
 * @version $Id: $
 */
class Orders_ReportController extends OSDN_Controller_Action
{
    protected $_reports;

	public function init()
	{
		$this->_reports = new PMS_Reports();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->setLayout('report');
	}

    public function permission(OSDN_Controller_Action_Helper_Acl $acl)
    {
        $acl->setResource(OSDN_Acl_Resource_Generator::getInstance()->orders);
        $acl->isAllowed(OSDN_Acl_Privilege::VIEW, 'schedule-production');
        $acl->isAllowed(OSDN_Acl_Privilege::VIEW, 'schedule-print');
        $acl->isAllowed(OSDN_Acl_Privilege::VIEW, 'schedule-mount');
        $acl->isAllowed(OSDN_Acl_Privilege::VIEW, 'planning');

        $acl->setResource(OSDN_Acl_Resource_Generator::getInstance()->reports);
        $acl->isAllowed(OSDN_Acl_Privilege::VIEW, 'managers');
        $acl->isAllowed(OSDN_Acl_Privilege::VIEW, 'customers');
        $acl->isAllowed(OSDN_Acl_Privilege::VIEW, 'customers-list');
    }

    public function scheduleMountAction()
    {
    	$response = $this->_reports->generateSchedule('mount');
    	if ($response->isSuccess()) {
	    	$this->view->data = $response->data;
	        $this->view->content = $this->view->render('report/schedule.phtml');
    	} else {
    		$this->_collectErrors($response);
    	}
    }

    public function scheduleProductionAction()
    {
    	$response = $this->_reports->generateSchedule('production');
    	if ($response->isSuccess()) {
	    	$this->view->data = $response->data;
	        $this->view->content = $this->view->render('report/schedule.phtml');
    	} else {
    		$this->_collectErrors($response);
    	}
    }

    public function schedulePrintAction()
    {
    	$response = $this->_reports->generateSchedule('print');
    	if ($response->isSuccess()) {
	    	$this->view->data = $response->data;
	        $this->view->content = $this->view->render('report/schedule.phtml');
    	} else {
    		$this->_collectErrors($response);
    	}
    }

    public function planningAction()
    {
    	$response = $this->_reports->generatePlanning();
    	if ($response->isSuccess()) {
	    	$this->view->data = $response->data;
	        $this->view->content = $this->view->render('report/planning.phtml');
    	} else {
    		$this->_collectErrors($response);
    	}
    }

    public function managersAction()
    {
    	$response = $this->_reports->generateManagers($this->_getAllParams());
    	if ($response->isSuccess()) {
	    	$this->view->data = $response->data;
	        $this->view->content = $this->view->render('report/managers.phtml');
    	} else {
    		$this->_collectErrors($response);
    	}
    }

    public function customersAction()
    {
    	$response = $this->_reports->generateCustomers($this->_getAllParams());
    	if ($response->isSuccess()) {
	    	$this->view->data = $response->data;
	        $this->view->content = $this->view->render('report/customers.phtml');
    	} else {
    		$this->_collectErrors($response);
    	}
    }

    public function staffAction()
    {
    	$response = $this->_reports->generateStaff($this->_getAllParams());
    	if ($response->isSuccess()) {
	    	$this->view->data = $response->data;
	        $this->view->content = $this->view->render('report/staff.phtml');
    	} else {
    		$this->_collectErrors($response);
    	}
    }

    public function customersListAction()
    {
        $customers = new PMS_Customers();
    	$response = $customers->getList($this->_getAllParams());
    	if ($response->isSuccess()) {
	    	$this->view->data = $response->getRowset();
	        $this->view->content = $this->view->render('report/customers-list.phtml');
    	} else {
    		$this->_collectErrors($response);
    	}
    }
}
