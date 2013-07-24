<?php

class Storage_IndexController extends OSDN_Controller_Action
{
    public function permission(OSDN_Controller_Action_Helper_Acl $acl)
    {
        $acl->setResource(OSDN_Acl_Resource_Generator::getInstance()->storage);
        $acl->isAllowed(OSDN_Acl_Privilege::VIEW, 'get-accounts');
    }

    public function getAccountsAction()
    {
        $accounts = new OSDN_Accounts();
        $response = $accounts->fetchAllActiveNames();
        if ($response->isError()) {
            $this->_collectErrors($response);
            return;
        }
        $this->view->data = $response->getRowset();
        $this->view->success = true;
    }
}