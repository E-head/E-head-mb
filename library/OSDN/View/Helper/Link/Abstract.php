<?php

/**
 * Generage url link in view template
 *
 * @category OSDN
 * @package OSDN_View
 * @subpackage OSDN_View_Helper
 */
abstract class OSDN_View_Helper_Link_Abstract
{
    
    /**
     * @var Zend_View
     */
    public $view;

    /**
     * Generates url
     *
     * @param string $module
     * @param string $controller
     * @param string $action
     * @param array $options
     */
    protected function _link($module, $controller, $action, array $options = array(), $engine = null) 
    {
        $front = Zend_Controller_Front::getInstance();
        
        if (empty($module)) {
            throw new OSDN_Exception('Module is empty');
        }

        $urlParts = array($front->getBaseUrl());
        if (!is_null($engine)) {
            array_push($urlParts, $engine);    
        }
        $urlParts = array_merge($urlParts, array($module, $controller, $action));
        $url = join('/', $urlParts);

        $params = array();
        if (!empty($options)) {
            foreach ($options as $p => $v) {
                $params[] = urlencode($p) . '/' . urlencode($v);
            }
            $url .= '/' . join('/', $params);
        }
        
        return $url;
    }
    
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view; 
    }
}