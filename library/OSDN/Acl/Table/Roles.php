<?php

/**
 * Storage table for roles
 *
 * @category OSDN
 * @package OSDN_Acl
 * @subpackage OSDN_Acl_Table
 */
class OSDN_Acl_Table_Roles extends OSDN_Db_Table_Abstract
{
    /**
     * Table name
     *
     * @var string
     */
    protected $_name = 'acl_roles';

    protected $_nullableFields = array(
        'alias'
    );
}