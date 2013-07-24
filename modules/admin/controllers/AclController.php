<?php

class Admin_AclController extends OSDN_Controller_Action
{
    public function permission(OSDN_Controller_Action_Helper_Acl $acl)
    {
        $acl->setResource(OSDN_Acl_Resource_Generator::getInstance()->admin);
        $acl->isAllowed(OSDN_Acl_Privilege::VIEW, 'get-list');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'allow');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'delete-resource');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'insert-resource');
    }
    
    public function getListAction()
    {
        $parentId = $this->_getParam('node');
        $permissions = new OSDN_Acl_Permission();
        $roleId = $this->_getParam('roleId');
        
        $response = $permissions->fetchPermission($roleId, $parentId);
        if ($response->isError()) {
            $this->_collectErrors($response);
            return;
        }
        
        $rows = $response->rows;
        $resource = new OSDN_Acl_Resource();
        foreach ($rows as & $row) {
            $countResponse = $resource->fetchCountByParentId($row['id']);
            $row['leaf'] = $countResponse->isSuccess() && 0 == $countResponse->count;
        }
        
        $response->rows = $rows;
        $this->view->assign($response->rows);
    }
    
    public function allowAction()
    {
        $resourceId = $this->_getParam('resourceId');
		$privilege = $this->_getParam('privilege');
		$value = $this->_getParam('value');
		$roleId = $this->_getParam('roleId');
		$permissions = new OSDN_Acl_Permission();
		$response = $permissions->setPermission($roleId, $resourceId, $privilege, $value);
		if ($response->isError()) {
			$this->_collectErrors ($response);
			return;
		}
        $this->view->success = true;
    }
    
    public function deleteResourceAction()
    {
        $resourceId = $this->_getParam('resourceId');
        $resource = new OSDN_Acl_Resource();
        $response = $resource->deleteResource($resourceId);
        if ($response->isError()) {
            $this->_collectErrors ($response);
            return;
        }
        $this->view->success = true;
    }
    
    public function insertResourceAction()
    {
        $resourceChain = explode(',', $this->_getParam('resource', ''));
        if (!OSDN_DEBUG || 0 == count($resourceChain) || empty($resourceChain)) {
            $this->view->success = false;
        }

        $generator = OSDN_Acl_Resource_Generator::getInstance();
        foreach ($resourceChain as $resource) {
            if (empty($resource)) {
                break;
            }
            
            $generator->$resource;
        }
        $this->view->success = true;
    }
}