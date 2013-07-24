<?php

/**
 * include js files
 *
 * @category OSDN
 * @package OSDN_View
 * @subpackage OSDN_View_Helper
 */
class OSDN_View_Helper_IncludeJs
{
    
    /**
     * @var Zend_View
     */
    public $view;

    public function IncludeJs($xmlPath, $params = array())
    {
        return OSDN_Script::factory()->generateHtml($params, $this->view->baseUrl());
    }
    
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view; 
    }
}