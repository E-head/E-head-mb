<?php

/**
 * Zend_Controller_Action
 *
 * @category OSDN
 * @package OSDN_Controller
 */
class OSDN_Controller_Action extends Zend_Controller_Action
{
    /**
     * Set permission in this function
     * Overwrite this function and set own permission
     *
     * @param OSDN_Controller_Action_Helper_Acl $acl     An acl helper
     *
     * <example>
     *   $acl = $this->_helper->acl;
     *   $acl->setResource(OSDN_Acl_Resource_Generator::getInstance()->student->commentary);
     *   $acl->isAllowed(OSDN_Acl_Privilege::VIEW, 'get-list');
     * </example>
     */
    public function permission(OSDN_Controller_Action_Helper_Acl $acl)
    {}
    
    /**
     * Generate url link
     *
     * @param string $action
     * @param string $controller
     * @param string $module
     * @param string $params
     * @return string
     */
    public function link($action, $controller, $module, $params = array())
    {
        return $this->_helper->url($action, $controller, $module, $params);
    }
    
    /**
     * Disable rendering
     * Alias for set no render
     *
     * @param boolean $flag        true to disable
     * @return OSDN_Controller_Action
     */
    public function disableRender($flag = true)
    {
        $this->disableLayout($flag);
        $this->_helper->viewRenderer->setNoRender($flag);
        return $this;
    }
    
    /**
     * Disable layout
     *
     * @param bool $flag
     * @return OSDN_Controller_Action
     */
    public function disableLayout($flag = true)
    {
        if (!$this->_helper->hasHelper('layout')) {
            return;
        }
        
        $layout = $this->_helper->layout;
        if ($flag) {
            $layout->disableLayout();
        } else {
            $layout->enableLayout();
        }
        
        return $this;
    }
    
    /**
     * Collect errors from response to $this->view->errors
     *
     * @param OSDN_Response $response
     *
     * @param void
     */
    protected function _collectErrors(OSDN_Response $response)
    {
        if ($response->hasNotSuccess()) {
            $this->view->success = false;
        }
        
        $this->view->errors = $this->_getErrors($response);
    }
    
    /**
     * Return errors from given response
     *
     * @param OSDN_Response $response
     *
     * @return array
     */
    protected function _getErrors(OSDN_Response $response)
    {
        $errors = array();
        if ($response->hasNotSuccess()) {
            $statuses = $response->getStatusCollection();

            foreach ($statuses as $status) {
                $msg = $status->getMessage();
                
                switch($status->getModuleCode()) {
                    case OSDN_Filter_Input::MODULE_CODE:
                        break;
                    default:
                        $msg = $msg;
                }
                
                $errors[] = array(
                    'id'    => $status->getField(),
                    'msg'   => $msg,
                    'code'  => $status->getCode()
                );
            }
        }
        return $errors;
    }
}