<?php

/**
 * OSDN_View_Engine_Debug
 * 
 * Allows contain view data and debug view data
 *
 * @category OSDN
 * @package OSDN_View
 * @subpackage OSDN_View_Engine
 */
class OSDN_View_Engine_Debug extends OSDN_View_Engine_Abstract 
{
    public function render($name)
    {
        if (!OSDN_DEBUG) {
            throw new OSDN_Exception('Debugging is disabled. Change the view');
            exit();    
        }
        
        Zend_Debug::dump($this->getCollection());
    }
    
    public function getEngine()
    {
        return 'debug';
    }
}