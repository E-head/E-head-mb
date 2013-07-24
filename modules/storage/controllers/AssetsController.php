<?php

class Storage_AssetsController extends OSDN_Controller_Action
{
	/**
	 * @var PMS_class
	 */
	protected $_class;

	public function init()
	{
		$this->_class = new PMS_Storage_Assets();
		parent::init();
	}

    public function permission(OSDN_Controller_Action_Helper_Acl $acl)
    {
        $acl->setResource(OSDN_Acl_Resource_Generator::getInstance()->storage);
        $acl->isAllowed(OSDN_Acl_Privilege::VIEW, 'get-list');
        $acl->isAllowed(OSDN_Acl_Privilege::VIEW, 'get');
        $acl->isAllowed(OSDN_Acl_Privilege::VIEW, 'history');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'add');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'update');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'delete');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'check');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'reset-checks');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'income');
        $acl->isAllowed(OSDN_Acl_Privilege::UPDATE, 'outgo');
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

    public function checkAction()
    {
    	$response = $this->_class->check($this->_getAllParams());
    	if ($response->isSuccess()) {
    	    $this->view->success = true;
    	} else {
    	   $this->_collectErrors($response);
    	}
    }

    public function resetChecksAction()
    {
    	$response = $this->_class->resetChecks();
    	if ($response->isSuccess()) {
    	    $this->view->success = true;
    	} else {
    	   $this->_collectErrors($response);
    	}
    }

    public function historyAction()
    {
        $response = $this->_class->getHistoryByAssetId($this->_getAllParams());
        if ($response->isSuccess()) {
            $this->view->success = true;
            $this->view->data = $response->getRowset();
        } else {
            $this->_collectErrors($response);
        }
    }

    public function incomeAction()
    {
        $qty = $this->_getParam('qty');
        $asset_id = $this->_getParam('asset_id');
        $order_id = $this->_getParam('order_id');
        $order_id = intval($order_id) > 0 ? intval($order_id) : null;
        $response = $this->_class->assetQtyUpdate($asset_id, $qty, $order_id);
        if ($response->isSuccess()) {
            $this->view->success = true;
            $this->view->id = $response->id;
        } else {
           $this->_collectErrors($response);
        }
    }

    public function outgoAction()
    {
        $qty = $this->_getParam('qty');
        $asset_id = $this->_getParam('asset_id');
        $order_id = $this->_getParam('order_id');
        $order_id = intval($order_id) > 0 ? intval($order_id) : null;
        $reciever_id = $this->_getParam('reciever_id');
        $reciever_id = intval($reciever_id) > 0 ? intval($reciever_id) : null;
        if ($qty > 0) {
            $qty = -$qty;
        }
        $response = $this->_class->assetQtyUpdate($asset_id, $qty, $order_id, $reciever_id);
        if ($response->isSuccess()) {
            $this->view->success = true;
            $this->view->id = $response->id;
        } else {
           $this->_collectErrors($response);
        }
    }
}