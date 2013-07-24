<?php

/**
 * OSDN_View_Engine_Json
 * 
 * Allows contain view data and return json object
 *
 * @category OSDN
 * @package OSDN_View
 * @subpackage OSDN_View_Engine
 */
class OSDN_View_Engine_Json extends OSDN_View_Engine_Abstract 
{
    public function render($name)
    {
        echo Zend_Json::encode($this->getCollection());
    }
    
    public function getEngine()
    {
        return 'json';
    }
}