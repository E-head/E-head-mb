<?php

/**
 * Generate link for html engine
 * 
 * @category OSDN
 * @package OSDN_View_Helper
 * 
 */
class OSDN_View_Helper_Link extends OSDN_View_Helper_Link_Abstract 
{

    /**
     * Generate the url by following params
     *
     * @param string $module                The module name
     * @param string $controller            The controller name
     * @param stirng $action                The action name
     * @param array $options                value/pair options
     * @return string
     */
    public function link($module, $controller, $action, array $options = array())
    {
        return parent::_link($module, $controller, $action, $options);
    }
}
