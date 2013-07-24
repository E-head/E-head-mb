<?php

class Orderslog_IndexController extends OSDN_Controller_Action
{
	/**
	 * @var PMS_class
	 */
	protected $_class;

	public function init()
	{
		$this->_class = new PMS_Orderslog();
		parent::init();
	}

    public function permission(OSDN_Controller_Action_Helper_Acl $acl)
    {
        $acl->setResource(OSDN_Acl_Resource_Generator::getInstance()->orders);
        $acl->isAllowed(OSDN_Acl_Privilege::VIEW, 'import');
        $acl->isAllowed(OSDN_Acl_Privilege::VIEW, 'get-list');
        $acl->isAllowed(OSDN_Acl_Privilege::VIEW, 'report');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'add');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'delete');
    }

	public function importAction()
    {
        if (empty($_FILES['f'])
        || $_FILES['f']['error'] != UPLOAD_ERR_OK
        || !is_uploaded_file($_FILES['f']['tmp_name'])
        || !file_exists($_FILES['f']['tmp_name'])) {
            $response = new OSDN_Response();
            $response->addStatus(new PMS_Status(PMS_Status::INPUT_PARAMS_INCORRECT, 'f'));
            $this->_collectErrors($response);
            return;
        }

        $class = new PMS_DbfImport();
        $response = $class->start($_FILES['f']['tmp_name']);
    	if ($response->isSuccess()) {
    		$this->view->success = true;
    	    $this->view->data = $response->getRowset();
    	} else {
    		$this->_collectErrors($response);
    	}
    }

	public function getListAction()
    {
    	$response = $this->_class->getList($this->_getAllParams());
    	if ($response->isSuccess()) {
    		$this->view->success = true;
    	    $this->view->data = $response->getRowset();
    	    $this->view->totalCount = $response->totalCount;
    	    $this->view->rest = $response->rest;
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

    public function deleteAction()
    {
    	$response = $this->_class->delete($this->_getParam('id'));
    	if ($response->isSuccess()) {
    	    $this->view->success = true;
    	} else {
    	   $this->_collectErrors($response);
    	}
    }

    public function reportAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->setLayout('report');
        $report = new PMS_Orderslog_Report();
        $response = $report->generate($this->_getAllParams());
        if ($response->isSuccess()) {
            $this->view->data = $response->data;
            $this->view->content = $this->view->render('index/report.phtml');
        } else {
            $this->_collectErrors($response);
        }
    }
}