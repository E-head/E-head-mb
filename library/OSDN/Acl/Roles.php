<?php

class OSDN_Acl_Roles
{
    /**
     * The role table object
     *
     * @var OSDN_Acl_Table_Roles
     */
    protected $_table;

    public function __construct()
    {
        $this->_table = new OSDN_Acl_Table_Roles();
    }


    /**
     * Resolve role ID by alias
     *
     * @param string $alias
     * @return int $id || false
     */
    public function alias2id($alias)
    {
        $row = $this->_table->fetchRow(array('alias = ?' => $alias));
        return is_null($row) ? false : $row->id;
    }

    /**
     * Fetch all roles
     *
     * @return OSDN_Response <code>
     *  rows: array(
     *      id: int
     *      name: string
     *  );
     * </code>
     */
    public function fetchRoles()
    {
        $response = new OSDN_Response();
        $rowset = $this->_table->fetchAll()->toArray();
        $response->setRowset($rowset);
        return $response->addStatus(new OSDN_Acl_Status(OSDN_Acl_Status::OK));
    }

    /**
     * Retrieve the role by id
     *
     * @param int $id
     * @return OSDN_Response
     */
    public function fetchRole($id)
    {
        $response = new OSDN_Response();

        $validate = new OSDN_Validate_Id();
        if (!$validate->isValid($id)) {
            $response->addStatus(new OSDN_Acl_Status(OSDN_Acl_Status::INPUT_PARAMS_INCORRECT, 'id'));
            return $response;
        }

        $row = $this->_table->findOne($id);
        if (!empty($row)) {
            $row = $row->toArray();
        }

        $response->addStatus(new OSDN_Acl_Status(OSDN_Acl_Status::OK));
        $response->setRow($row);
        return $response;
    }

    /**
     * Rename role
     *
     * @param int $id       The role id
     * @param string $name  New role name
     * @return OSDN_Response
     */
    public function rename($id, $name)
    {
        $response = new OSDN_Response();
        $validate = new OSDN_Validate_Id();
        if (!$validate->isValid($id)) {
            $response->addStatus(new OSDN_Acl_Status(OSDN_Acl_Status::INPUT_PARAMS_INCORRECT, 'id'));
            return $response;
        }

        $affectedRows = $this->_table->updateByPk(array(
            'name'  => $name
        ), $id);
        $response->addStatus(new OSDN_Acl_Status(OSDN_Acl_Status::retrieveAffectedRowStatus($affectedRows)));
        return $response;
    }

    /**
     * Create new role
     *
     * @param string $name      The role name
     * @return OSDN_Response <code>
     *  id: int
     * </code>
     */
    public function createRole($name)
    {
        $response = new OSDN_Response();
        $id = $this->_table->insert(array(
            'name'  => $name
        ));
        $response->addStatus(new OSDN_Acl_Status(OSDN_Acl_Status::OK));
        $response->id = $id;
        return $response;
    }

    /**
     * Delete role
     *
     * @param int $id       The role id
     * @return OSDN_Response
     */
    public function delete($id)
    {
        $response = new OSDN_Response();
        $validate = new OSDN_Validate_Id();
        if (!$validate->isValid($id)) {
            $response->addStatus(new OSDN_Acl_Status(OSDN_Acl_Status::INPUT_PARAMS_INCORRECT, 'id'));
            return $response;
        }

        $affectedRows = $this->_table->deleteByPk($id);
        $response->addStatus(new OSDN_Acl_Status(OSDN_Acl_Status::retrieveAffectedRowStatus($affectedRows)));
        return $response;
    }

    /**
     * Update role by id
     *
     * @param int $id
     * @param array $data
     *
     * @return OSDN_Response
     */
    public function update($id, array $data = array())
    {
        $response = new OSDN_Response();
        $data['id'] = $id;
        $f = new OSDN_Filter_Input(array(
            '*' => 'StringTrim'
        ), array(
            'name'  => array('presence' => 'required'),
            'alias' => array('allowEmpty' => true),
            'id'    => array('id', 'presence' => 'required')
        ), $data);

        $response->addInputStatus($f);
        if ($response->hasNotSuccess()) {
            return $response;
        }

        unset($data['id']);

        $affectedRows = $this->_table->updateByPk($data, $id);
        $response->addStatus(new OSDN_Acl_Status(OSDN_Acl_Status::retrieveAffectedRowStatus($affectedRows)));
        return $response;
    }
}