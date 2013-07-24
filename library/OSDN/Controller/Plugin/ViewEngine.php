<?php

/**
 * Allow configure output content
 * Configure this functionality via Zend_Controller_Router_Route
 * /json        for json output
 * /xml         for xml output
 * /debug       for print the variable recursive
 * /assemble    for assemble callback functions
 *
 * @category    OSDN
 * @package     OSDN_Controller
 * @subpackage  OSDN_Controller_Plugin
 * @version     $Id: ViewEngine.php 6711 2009-02-10 13:30:04Z flash $
 */
class OSDN_Controller_Plugin_ViewEngine extends Zend_Controller_Plugin_Abstract
{

    protected $_options = array();

    public function __construct(array $options = array())
    {
        if (is_array($options)) {
            $this->_options = $options;
        }
    }

    /**
     * @var Zend_Controller_Front
     */
    protected $_frontController = null;

    /**
     * Called before Zend_Controller_Front begins evaluating the
     * request against its routes.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        $router = $this->getFrontController()->getRouter();

        // prepare json router
        $router->addRoute(
            'json',
            new Zend_Controller_Router_Route('json/:module/:controller/:action/*',
            array(
                'module'        => 'module',
                'controller'    => 'controller',
                'action'        => 'action'
            ))
        );

        // prepare xml router
        $router->addRoute(
            'xml',
            new Zend_Controller_Router_Route('xml/:module/:controller/:action/*',
            array(
                'module'        => 'module',
                'controller'    => 'controller',
                'action'        => 'action'
            ))
        );

        $router->addRoute(
            'assemble',
            new Zend_Controller_Router_Route_Regex('^([a-fA-F0-9]{32})$',
            array(
                'module'        => 'default',
                'controller'    => 'index',
                'action'        => 'index'
            ),
            array('id' => 1))
        );

        // prepare debug router
        if (OSDN_DEBUG) {
            $router->addRoute(
                'debug',
                new Zend_Controller_Router_Route('debug/:module/:controller/:action/*',
                array(
                    'module'        => 'module',
                    'controller'    => 'controller',
                    'action'        => 'action'
                ))
            );
        }
    }

    /**
     * Called after Zend_Controller_Router exits.
     *
     * Called after Zend_Controller_Front exits from the router.
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $frontController = $this->getFrontController();
        $route = $frontController->getRouter();
        $routeName = $route->getCurrentRouteName();
        $view = OSDN_View::factory($routeName, $this->_options);

        // set a predefined variables
        if ($view instanceof Zend_View_Abstract) {
            $view->assign($this->_options);
        }

        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $viewRenderer->setView($view);

        // set site address in config
        $config = Zend_Registry::get('config');
        if (!isset($config->site->address)) {
            $protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
            $host = isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST']) ?
                $_SERVER['HTTP_HOST'] . $this->getFrontController()->getBaseUrl() : '';

            $tmp = $config->toArray();
            if (!isset($tmp['site'])) {
                $tmp['site'] = array();
            }
            $tmp['site']['address'] = $protocol . '://' . $host;
            $config = new Zend_Config($tmp);
            $cache = Zend_Registry::get('cachecore');
            $cache->save($config, 'config');
            Zend_Registry::set('config', $config);
        }
    }

    /**
     * Get front controller instance
     *
     * @return Zend_Controller_Front
     * @throws OSDN_Controller_Exception
     */
    public function getFrontController()
    {
        if (!is_null($this->_frontController)) {
            return $this->_frontController;
        }

        if (class_exists('Zend_Controller_Front')) {
            $this->_frontController = Zend_Controller_Front::getInstance();
            return $this->_frontController;
        }

        // Throw exception in all other cases
        throw new OSDN_Controller_Exception('Front controller class has not been loaded');

    }
}
