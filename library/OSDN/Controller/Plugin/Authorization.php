<?php

/**
 * OSDN authorization plugin
 *
 * @uses        Zend_Controller_Plugin_Abstract
 * @category    OSDN
 * @package     OSDN_Controller
 * @subpackage  OSDN_Controller_Plugin
 * @version     $Id: Authorization.php 6735 2009-02-12 10:57:32Z flash $
 */

class OSDN_Controller_Plugin_Authorization extends Zend_Controller_Plugin_Abstract
{

    protected $_public = 'public';

    /**
     * The list of allowed routers
     *
     * @example <code>
     * array(
     *   'default'   => true,
     *   'accounts'  => array('auth')
     * );
     * </code>
     * @var array
     */
    private $_allowRouters = array();


    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $module = strtolower($request->getModuleName());
        $controller = strtolower($request->getControllerName());

        // account is authenticated
        // add acl helper
        if (OSDN_Accounts_Prototype::isAuthenticated()) {
            Zend_Controller_Action_HelperBroker::addHelper(new OSDN_Controller_Action_Helper_Acl());
            return;
        }

        // if not public area -> go out
        if (false === $this->_isAllowed($module, $controller)) {

            $frontController = Zend_Controller_Front::getInstance();
            $route = $frontController->getRouter();
            header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized', false, 401);

            /**
             * Current request is not allowed
             * Try to execute the default request settings
             */
            if ('default' == $route->getCurrentRouteName()) {
                $dispatcher = $frontController->getDispatcher();
                $request
                    ->setModuleName($dispatcher->getDefaultModule())
                    ->setControllerName($dispatcher->getDefaultControllerName())
                    ->setActionName($dispatcher->getDefaultAction());
            } else {

                // other routes
                exit;
            }
        }
    }

    /**
     * Fetch allowed routers
     *
     * @return array
     */
    protected function getAllowedRouters()
    {
        return $this->_allowRouters += array(
            Zend_Controller_Front::getInstance()->getDefaultModule() => true,
            $this->_public  => true
        );
    }

    /**
     * Check if dispatched controller is public
     *
     * @param string $module        The module name
     * @return boolean
     */
    protected function _isPublic($module)
    {
        return $module == $this->_public;
    }

    /**
     * Check if module is allowed
     *
     * @param string $module        The module name
     * @param string $controller    The controller name
     * @return boolean
     */
    protected function _isAllowed($module, $controller)
    {
        $access = false;
        $routers = $this->getAllowedRouters();
        if (array_key_exists($module, $routers)) {
            if ($routers[$module] === true || in_array($controller, $routers[$module])) {
                $access = true;
            }
        }
        return $access;
    }
}