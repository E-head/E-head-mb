<?php

/**
 * The response status object interface
 *
 * @category OSDN
 * @package OSDN_Response
 */
interface OSDN_Response_Status_Interface
{
    /**
     * Retrieve the status code
     */
    public function getCode();
    
    /**
     * Retrieve the status message
     *
     */
    public function getMessage();
    
    /**
     * Retrieve the module code
     *
     */
    public function getModuleCode();
    
    /**
     * Retrieve the module name
     *
     */
    public function getModuleName();
    
    /**
     * Retrieve the field
     *
     */
    public function getField();
    
    /**
     * Retrieve the array of status and module configs
     *
     * @return array
     */
    public function toArray();
}