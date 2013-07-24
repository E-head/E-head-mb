<?php

/**
 * Simple Resource generator
 *
 * @category		OSDN
 * @package		OSDN_Acl
 * @version		$Id: Generator.php 5991 2008-12-23 08:53:33Z flash $
 */
class OSDN_Acl_Resource_Generator
{
    /**
     * Unique id of Resource
     *
     * @var string
     */
    protected $_resourceId = null;
    
    /**
     * Resource table object
     *
     * @var OSDN_Acl_Table_Resource
     */
    protected $_tableResource = null;
    
    /**
     * Instance of self
     *
     * @var OSDN_Acl_Resource_Generator
     */
    protected static $_instance = null;
    
    /**
     * Retrieve the instance of self
     *
     * @return OSDN_Acl_Resource_Generator
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        self::$_instance->_resourceId = null;
        return self::$_instance;
    }
    
    /**
     * Constructor closed
     *
     */
    private function __construct()
    {
        $this->_tableResource = new OSDN_Acl_Table_Resource();
    }
        
    /**
     * Fetch resource and create if not exists
     *
     * @param int   The resource
     * @return OSDN_Acl_Resource_Generator
     */
    public function __get($param)
    {
        return $this->_getResource($param, true);
    }
    
    /**
     * Get resource
     *
     * @param int $resource
     * @return OSDN_Acl_Resource_Generator
     */
    public function get($resource)
    {
        return $this->_getResource($resource, false);
    }
    
    /**
     * Fetch resources
     *
     * @param string $param
     * @return OSDN_Acl_Resource_Generator
     */
    protected function _getResource($resource, $autoCreate = true)
    {
        $id = $this->_tableResource->fetchResourceId($resource, $this->_resourceId, $autoCreate);
        $this->_resourceId = $id;
        return $this;
    }
    
    /**
     * To string comparision
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->_resourceId;
    }
}