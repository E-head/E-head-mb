<?php

/**
 * Language status constant
 * 
 * @category OSDN
 * @package OSDN_Language
 */
class OSDN_Language_Status extends OSDN_Response_Status_Storage_Abstract     
{
    /**
     * Module code
     *
     * @var int
     */
    protected $_moduleCode = 2;
    
    /**
     * Module name
     *
     * @var int
     */
    protected $_moduleName = 'Language';
    
    /**
     * Description storage
     *
     * @var array
     */
    protected $_storage = array();
}
