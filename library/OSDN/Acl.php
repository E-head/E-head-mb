<?php

/**
 * Simple acl object
 *
 * @category		OSDN
 * @package		OSDN_Acl
 * @version		$Id: Acl.php 6701 2009-02-10 09:29:55Z flash $
 */
class OSDN_Acl
{
    /**
     * Permission rules collection
     *
     * @var array
     */
    protected $_rules = array();
    
    /**
     * Set privilege to resource
     *
     * @param int $resource
     * @param int $privilege
     * @return OSDN_Acl
     */
    public function allow($resource, $privilege)
    {
        $resource = (string) $resource;
        $this->addResource($resource);
        $this->_rules[$resource][] = $privilege;
        return $this;
    }
    
    /**
     * Check if allowed privilege to resource
     *
     * @param string|OSDN_Acl_Resource_Generator $resource
     * @param int $privilege
     * @return boolean
     */
    public function isAllowed($resource, $privilege)
    {
        $resource = (string) $resource;
        if (!array_key_exists($resource, $this->_rules)) {
            return false;
        }

        return in_array($privilege, $this->_rules[$resource]);
    }
    
    /**
     * Add resource to collectoin
     *
     * @param string|OSDN_Acl_Resource_Generator $resource
     * @return OSDN_Acl
     */
    public function addResource($resource)
    {
        $resource = (string) $resource;
        if (!array_key_exists($resource, $this->_rules)) {
            $this->_rules[$resource] = array();
        }
        
        return $this;
    }
    
    /**
     * Check if resource have any privilege
     *
     * @param int $resource
     * @return bool
     */
    public function hasAnyPrivilege($resource)
    {
        $resource = (string) $resource;
        if (!isset($this->_rules[$resource])) {
            return false;
        }
        return sizeof($this->_rules[$resource]) > 0;
    }
    
    /**
     * Dump rules collection
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_rules;
    }
}