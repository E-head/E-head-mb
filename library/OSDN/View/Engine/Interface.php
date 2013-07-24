<?php

/**
 * Interface for OSDN_View_Engine compability
 *  
 * @category OSDN
 * @package OSDN_View
 * @subpackage OSDN_View_Engine
 */
interface OSDN_View_Engine_Interface extends Zend_View_Interface 
{
    
    /**
     * Retrieve a valiable from data container
     *
     * @param string $key   The variable key
     */
    public function __get($key);
    
    /**
     * Retrieve the data from collection storage
     *
     * @return array
     */
    public function getCollection();
}