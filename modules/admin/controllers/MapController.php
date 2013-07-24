<?php

class Admin_MapController extends OSDN_Controller_Action
{
    public function permission(OSDN_Controller_Action_Helper_Acl $acl)
    {
        $acl->setResource(OSDN_Acl_Resource_Generator::getInstance()->map);
        $acl->isAllowed(OSDN_Acl_Privilege::VIEW, 'open');
    }

	public function openAction()
    {
        $this->disableLayout(true);
        $config = Zend_Registry::get('config');

        if ($config->map->enable != 1) {
            $this->disableRender(true);
            echo 'Ошибка! Модуль отключен либо отсутствует';
            return;
        }

        $this->view->host = $config->map->host;
        $this->view->login = $config->map->login;
        $this->view->password = $config->map->password;
    }
}