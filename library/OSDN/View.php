<?php

/**
 * Provide the output functionality by different engine
 * xml, json, html
 *
 * @category OSDN
 * @package OSDN_View
 */
class OSDN_View
{
    
    /**
     * Factory for OSDN_View classes
     *
     * First argument may be a string containing the base of the engine class
     * name, e.g. 'json' corresponds to class OSDN_View_Engine_Json.  This
     * is case-insensitive.
     *
     * Second argument is optional and may be an associative array of key-value
     * pairs.  This is used as the argument to the engine constructor.
     *
     * @param  mixed $engine String name of base engine class.
     * @param  mixed $config  OPTIONAL; an array of engine parameters.
     * @return OSDN_View_Engine_Abstract | Zend_View_Abstract
     */
    public static function factory($engine, $config = array())
    {
        if (!is_string($engine) || empty($engine)) {
            throw new OSDN_Exception('View engine must be specified in a string');
        }
        
        $engine = strtolower($engine);
        switch ($engine) {
            case 'json':
            case 'debug':
                $engineName = 'OSDN_View_Engine_' . ucfirst($engine);
                $engineObject = new $engineName();
                break;

            default:
                $mvc = Zend_Layout::startMvc($config);
                if (!empty($config['disableLayout']) && true == $config['disableLayout']) {
                    $mvc->disableLayout();
                }
                
                $engineObject = new Zend_View($config);
                $engineObject->addHelperPath('OSDN/View/Helper', 'OSDN_View_Helper');
                break;
        }
        
        return $engineObject;
    }
    
}
