<?php

/**
 * Helper for check the permission on controller action
 * If permission is not set then redirect on predefined controller
 * on some deny action
 *
 * Also contain the acl object
 * Can simply interface for access from any controller to check the acl
 *
 * @category OSDN
 * @package OSDN_Controller
 */
class OSDN_Controller_Action_Helper_Acl extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * The acl instance
     *
     * @var OSDN_Acl
     */
    protected $_acl;
    
    /**
     * The role
     *
     * @var Zend_Acl_Role
     */
    protected $_role;
    
    /**
     * The common resource
     *
     * @var Zend_Acl_Resource
     */
    protected $_resource;
    
    /**
     * The active controller action
     *
     * @var string
     */
    protected $_action;

    /**
     * Contain resource, privilege information
     * Structure: <code>
     * array(
     *  array(resource, privilege),
     *  array(resource, privilege),
     *  ...
     * );
     * </code>
     */
    protected $_trace = array();
    
    protected $_errorPage = array(
        'module'        => 'default',
        'controller'    => 'error',
        'action'        => 'denied'
    );
    
    /**
     * Indicate if access is allowed
     */
    protected $_isAllowed = false;
    
    protected $_allowRouters = array(
        'default'
    );
    
    /**
     * Helper initialization
     *
     * @return void
     */
    public function init()
    {
        if (empty($GLOBALS['i'])) {
            $GLOBALS['i'] = 0;
        }
        
        $GLOBALS['i']++;
        $this->_acl = OSDN_Accounts_Prototype::getAcl();
        $this->_role = OSDN_Accounts_Prototype::getRoleId();
        $this->_action = $this->getRequest()->getActionName();
        $this->_isAllowed = false;
        $this->_trace = array();
    }
    
    /**
     * Check if permission is allowed
     *
     * @param string            $privilege         The privilege type      @see OSDN_Acl_Privilege
     * @param string            $action
     * @param Zend_Acl_Resource $resource          The specific resource   OPTIONAL
     * @return boolean
     */
    public function isAllowed($privilege, $action, $resource = "")
    {
        if ($this->_action != $action) {
            return false;
        }
        
        return $this->isAllowedAction($privilege, $resource);
    }

    public function isAllowedAction($privilege, $resource = "")
    {
        if (empty($resource)) {
            $resource = $this->_resource;
        }
        
        $resource = (string) $resource;

        if (!is_array($privilege)) {
            if ($result = $this->_acl->isAllowed($resource, $privilege)) {
                $this->_isAllowed = true;
            } else {
                $this->_trace[] = array($resource, $privilege);
            }
            return $result;
        }
        
        $result = array();
        foreach ($privilege as $p) {
            if (! $allowed = (int) $this->_acl->isAllowed($resource, $p)) {
                $this->_trace[] = array($resource, $p);
            }
            
            $result[] = $allowed;
        }
        
        if ($result = array_sum($result) > 0) {
            $this->_isAllowed = true;
        }
        
        return $result;
    }
    
    /**
     * Set up the common resource
     *
     * @param Zend_Acl_Resource $resource
     * @return OSDN_Controller_Action_Helper_Acl
     */
    public function setResource($resource)
    {
        $this->_resource = (string) $resource;
        return $this;
    }
    
    public function preDispatch()
    {
        if (in_array(strtolower($this->getActionController()->getRequest()->getModuleName()),
            $this->_allowRouters)) {
            return;
        }
        
        $this->getActionController()->permission($this);
        
        if (true !== $this->_isAllowed) {
            $frontController = $this->getFrontController();
            if ('default' == $frontController->getRouter()->getCurrentRouteName()) {
                Zend_Auth::getInstance()->clearIdentity();
                $redirector = $this->getActionController()->getHelper('Redirector');
                $redirector->setGotoRoute();
                $redirector->redirectAndExit();
            } else {
                $this->denyAccess();
            }
        }
    }
    
    /**
     * Deny Access Function
     * Redirects to errorPage, this can be called from an action using the action helper
     *
     * @return void
     **/
    public function denyAccess()
    {
        // this creppy code because the method _forward is protected
        $this->getActionController()->getRequest()->setModuleName($this->_errorPage['module'])
            ->setControllerName($this->_errorPage['controller'])
            ->setActionName($this->_errorPage['action'])
            ->setParam('trace', $this->getTrace())
            ->setDispatched(false);
    }

    /**
     * Retrieve the trace information
     *
     * @see $this->_trace;
     * @return array
     */
    public function getTrace()
    {
        return $this->_trace;
    }
}