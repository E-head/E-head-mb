<?php

class OSDN_Acl_Permission
{
    /**
     * Permission table instance
     *
     * @var OSDN_Acl_Table_Permission
     */
    protected $_tablePermission = null;

    /**
     * The constructor
     *
     */
    public function __construct()
    {
        $this->_tablePermission = new OSDN_Acl_Table_Permission();
    }

    /**
     * Retrieve permissions by role id
     *
     * @param int $roleId
     * @return OSDN_Response
     * <code>contain data {
     *     rows : array
     * }</code>
     */
    public function fetchByRoleId($roleId)
    {
        $response = new OSDN_Response();
        $validate = new OSDN_Validate_Id();
        if (!$validate->isValid($roleId)) {
            $response->addStatus(new OSDN_Acl_Status(OSDN_Acl_Status::INPUT_PARAMS_INCORRECT, 'roleid'));
            return $response;
        }

        try {
            $rowset = $this->_tablePermission->fetchAll(array('role_id = ?' => $roleId));
            $response->setRowset($rowset->toArray());
            $status = OSDN_Acl_Status::OK;
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            $status = OSDN_Acl_Status::DATABASE_ERROR;
        }
        return $response->addStatus(new OSDN_Acl_Status($status));
    }

    /**
     * Retrive the permissions by resource id and role id
     *
     * @param int $roleId       The role id
     * @param int $resourceId   The resource id
     * @return OSDN_Response
     */
    public function fetchPermission($roleId, $resourceId)
    {
        $response = new OSDN_Response();
        $validate = new OSDN_Validate_Id();
        if (!$validate->isValid($roleId)) {
            return $response->addStatus(new OSDN_Acl_Status(
                OSDN_Acl_Status::INPUT_PARAMS_INCORRECT, 'role_id'));
        }

        $validateResource = new OSDN_Validate_Id(true);
        if (!$validateResource->isValid($resourceId)) {
            return $response->addStatus(new OSDN_Acl_Status(
                OSDN_Acl_Status::INPUT_PARAMS_INCORRECT, 'resource_id'));
        }

        $rows = $this->_tablePermission->fetchPermissions($roleId, $resourceId);
        if (false !== $rows) {
            $status = OSDN_Acl_Status::OK;
            $response->rows = $rows;
        } else {
            $status = OSDN_Acl_Status::DATABASE_ERROR;
        }
        return $response->addStatus(new OSDN_Acl_Status($status));
    }

    /**
     * Set permission on resource for role
     *
     * @param int $roleId       The role id
     * @param int $resourceId   The resource id
     * @param string $privilege The privilege name  @see OSDN_Acl_Privilege
     * @param mixed $value      The value
     *                           Value can be string, int or boolean
     *                           @see OSDN_Validate_Boolean for choose allowed format
     * @return OSDN_Response <code>
     * The possible returned data is:
     * array(
     *      id: int
     * )</code>
     */
    public function setPermission($roleId, $resourceId, $privilege, $value)
    {
        $validate = new OSDN_Validate_Id();
        $response = new OSDN_Response();

        if (!$validate->isValid($roleId)) {
            return $response->addStatus(new OSDN_Acl_Status(
                OSDN_Acl_Status::INPUT_PARAMS_INCORRECT, 'role_id'));
        }

        if (!$validate->isValid($resourceId)) {
            $response->addStatus(new OSDN_Acl_Status(
                OSDN_Acl_Status::INPUT_PARAMS_INCORRECT, 'resource_id'));
        }

        if (true !== OSDN_Acl_Privilege::isExists($privilege)) {
            return $response->addStatus(new OSDN_Acl_Status(
                OSDN_Acl_Status::PRIVILEGE_DOES_NOT_EXISTS, 'privilege'));
        }

        $validateValue = new OSDN_Validate_Boolean(true);
        if (!$validateValue->isValid($value)) {
            return $response->addStatus(new OSDN_Acl_Status(
                OSDN_Acl_Status::INPUT_PARAMS_INCORRECT, 'value'));
        }

        $booleanFilter = new OSDN_Filter_Boolean(true);
        $value = $booleanFilter->filter($value);
        $privilegeId = OSDN_Acl_Privilege::name2id($privilege);

        if (true === $value) {
            $id = $this->_tablePermission->insert(array(
                'role_id'       => $roleId,
                'resource_id'   => $resourceId,
                'privilege_id'  => $privilegeId
            ));

            if (false !== $id) {
                $status = OSDN_Acl_Status::OK;
                $response->id = $id;
            } else {
                $status = OSDN_Acl_Status::DATABASE_ERROR;
            }
            $response->addStatus(new OSDN_Acl_Status($status));
            return $response;
        }

        // Prevent to remove admin permissions for yourself
        $rg = OSDN_Acl_Resource_Generator::getInstance();
        if (OSDN_Accounts_Prototype::getRoleId() == $roleId
            && $rg->get('admin') == $resourceId) {
            return $response->addStatus(new OSDN_Acl_Status(OSDN_Acl_Status::FAILURE));
        }

        $this->_deletePermission($roleId, $resourceId, $privilegeId);
        return $response->addStatus(new OSDN_Acl_Status(OSDN_Acl_Status::OK));
    }

    /**
     * Cascading delete permission
     *
     * @param int $roleId           The role id
     * @param int $resourceId       The resource id
     * @param int $privilegeId      The privilege id
     * @return void
     */
    protected function _deletePermission($roleId, $resourceId, $privilegeId)
    {
        $resourceIds = array($resourceId);
        $tableResource = new OSDN_Acl_Table_Resource();

        do {
            $affectedRows = false;
            foreach ($resourceIds as $id) {
                $affectedRows = $this->_tablePermission->deleteQuote(array(
                    'role_id = ?'       => $roleId,
                    'resource_id = ?'   => $id,
                    'privilege_id = ?'  => $privilegeId
                ));
            }

            $whereClause = $tableResource->getAdapter()->quoteInto('parent_id IN (?)', $resourceIds);
            $resourceIds = array();

            try {
                $rowset = $tableResource->fetchAll($whereClause);
                $rows = $rowset->toArray();
            } catch (Exception $e) {
                if (OSDN_DEBUG) {
                    throw $e;
                }
                continue;
            }

            foreach ($rows as $row) {
                $resourceIds[] = $row['id'];
            }

        } while(sizeof($resourceIds) > 0);
    }
}