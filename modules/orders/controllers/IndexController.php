<?php

class Orders_IndexController extends OSDN_Controller_Action
{
	protected $_class;

	public function init()
	{
		$this->_class = new PMS_Orders_Model();
		parent::init();
	}

    public function permission(OSDN_Controller_Action_Helper_Acl $acl)
    {
        $acl->setResource(OSDN_Acl_Resource_Generator::getInstance()->orders);
        $acl->isAllowed(OSDN_Acl_Privilege::VIEW,   'get-list');
        $acl->isAllowed(OSDN_Acl_Privilege::VIEW,   'get');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'add');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'update');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'delete');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'close');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'make');
    }


    public function makeAction()
    {
        $response = $this->_class->make($this->_getAllParams());
        if ($response->isSuccess()) {
            $this->view->success = true;
            $this->sendEmail($id);
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
    	$data = $this->_getAllParams();
        $response = $this->_class->update($data);
        if ($response->isSuccess()) {
    		$this->sendEmail($this->_getParam('id'));
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

    public function closeAction()
    {
        $response = $this->_class->close($this->_getParam('id'));
        if ($response->isSuccess()) {
            $this->view->success = true;
        } else {
           $this->_collectErrors($response);
        }
    }

    /*
     * =========================================================================
     *
     * Private methods
     *
     * =========================================================================
     */
    private function sendEmail($orderId)
    {
    	$accounts = new OSDN_Accounts();
    	$roles = new OSDN_Acl_Roles();
    	$roleId = $roles->alias2id('admin');

    	if ($roleId) {

    	    $response = $accounts->fetchByRole($roleId);

    	    if ($response->isSuccess()) {

    	        // Send emails
    	        $rows = $response->getRowset();
    	    	$config = Zend_Registry::get('config');
    	    	$server = $config->mail->SMTP;
    	        $mail = new Zend_Mail('UTF-8');
                foreach ($rows as $row) {
                    $mail->addTo($row['email'], $row['name']);
        		}
    	        $mail->setFrom($config->mail->from->address, $config->mail->from->caption);
    	        $mail->setSubject("Добавлен заказ №$orderId на $server");
    	        $mail->setBodyHtml("");
    	        try {
    	            @$mail->send();
    	        } catch (Exception $e) {
    	            //echo $e->getMessage();
    	        }

    	        // Send SMS

        	}
    	}
    }
}