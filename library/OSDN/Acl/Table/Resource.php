<?php

/**
 * Storage table for resources
 *
 * @category OSDN
 * @package OSDN_Acl
 * @subpackage OSDN_Acl
 */
class OSDN_Acl_Table_Resource extends OSDN_Db_Table_Abstract
{
    /**
     * Table name
     *
     * @var string
     */
    protected $_name = 'acl_resources';
    
    /**
     * Fetch resource id by name
     * if resource is not present in parent scope then
     * insert new resource
     *
     * @todo implement response object
     * @param string    $name           The resource name
     * @param int       $parentId       The resource parent id
     * @param bool      $autoGenerate   Insert new resource if not found.
     *
     * @return int
     */
    public function fetchResourceId($name, $parentId, $autoGenerate = true)
    {
        $clause = array('name = ?' => $name);
        if ($parentId) {
            $clause['parent_id = ?'] = $parentId;
        } else {
            $clause[] = new Zend_Db_Expr('parent_id IS NULL');
        }
        
        $row = $this->fetchRow($clause);
        if (!is_null($row)) {
            return $row->id;
        }
        
        if (true !== $autoGenerate) {
            return 0;
        }
        
        $data = array('name' => $name);
        if (0 != $parentId) {
            $data['parent_id'] = $parentId;
        }
        return $this->insert($data);
    }
}