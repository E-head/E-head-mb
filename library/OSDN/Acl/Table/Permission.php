<?php

/**
 * Storage table for permitions
 *
 * @category OSDN
 * @package OSDN_Acl
 * @subpackage OSDN_Acl
 */
class OSDN_Acl_Table_Permission extends OSDN_Db_Table_Abstract
{
    /**
     * Table name
     *
     * @var string
     */
    protected $_name = 'acl_permissions';

    /**
     * Fetch permissions
     *
     * @todo implement response object
     *
     * @param int $roleId           The role id
     * @param int $resourceId       The resource id
     * @return array|false
     */
    public function fetchPermissions($roleId, $resourceId)
    {
        $cols = array();
        foreach (OSDN_Acl_Privilege::fetchAll() as $name => $value) {
            $name = strtolower($name);
            $cols[$name] = new Zend_Db_Expr("SUM(IF(`permissions`.`privilege_id` = '$value', 1, 0))");
        }

        $select = $this->_db->select()
            ->from(
                array('resources' => OSDN_Db_Table_Abstract::getDefaultPrefix() . 'acl_resources'),
                array('id', 'qtip' => 'name',
                    'text' => new Zend_Db_Expr("IF(LENGTH(`resources`.`title`),`resources`.`title`,`resources`.`name`)")
                )
           )
           ->joinLeft(
                array('permissions' => $this->getTableName()),
                $this->_db->quoteInto(
                    '`resources`.`id` = `permissions`.`resource_id` '
                    . 'AND `permissions`.`role_id` = ?', $roleId, 'int'
                ),
                $cols
           )
           ->group('resources.id');

        if (!$resourceId) {
            $select->where(new Zend_Db_Expr('`resources`.`parent_id` IS NULL'));
        } else {
            $select->where('`resources`.`parent_id` = ?', $resourceId);
        }

        try {
            $rowset = $select->query()->fetchAll();
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return false;
        }
        return $rowset;
    }
}