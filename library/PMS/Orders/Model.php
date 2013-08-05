<?php

class PMS_Orders_Model
{
    public function __construct()
    {
        $this->_table = new PMS_Orders_Table();
    }

    public function add()
    {
        $response = new OSDN_Response();
        $id = $this->_table->insert(array(
            'account_id' => OSDN_Accounts_Prototype::getId()
        ));
        $status = $id ? PMS_Status::OK : PMS_Status::FAILURE;
        $response->addData('id', $id);
        return $response->addStatus(new PMS_Status($status));
    }

    public function update(array $params)
    {
        $f = new OSDN_Filter_Input(array(
            '*'             => 'StringTrim'
        ), array(
            'id'      => array('Int', 'presence' => 'required'),
            'ondate'  => array(array('StringLength', 0, 10))
        ), $params);

        $response = new OSDN_Response();
        $response->addInputStatus($f);
        if ($response->hasNotSuccess()) {
            return $response;
        }
        $res = $this->_table->updateByPk($f->getData(), $f->id);
        $status = $res === false ? PMS_Status::FAILURE : PMS_Status::OK;
        return $response->addStatus(new PMS_Status($status));
    }

    public function delete($id)
    {
        $response = new OSDN_Response();
        $validate = new OSDN_Validate_Id();
        if (!$validate->isValid($id)) {
            return $response->addStatus(new PMS_Status(
            	PMS_Status::INPUT_PARAMS_INCORRECT, 'id'));
        }
        $affectedRows = $this->_table->deleteByPk($id);
        return $response->addStatus(new PMS_Status(
            PMS_Status::retrieveAffectedRowStatus($affectedRows)
        ));
    }

    public function close($id)
    {
        $response = new OSDN_Response();
        $validate = new OSDN_Validate_Id();
        if (!$validate->isValid($id)) {
            return $response->addStatus(new PMS_Status(
            	PMS_Status::INPUT_PARAMS_INCORRECT, 'id'));
        }
        $res = $this->_table->updateByPk(array('closed' => 'NOW()'), $id);
        $status = $res === false ? PMS_Status::FAILURE : PMS_Status::OK;
        return $response->addStatus(new PMS_Status($status));
    }

    /**
     * Retrieve orders
     *
     * @param array $params
     * The param examples<code>
     *      sort    => 'name'
     *      dir     => 'ASC'
     *      limit   => 20
     *      start   => 1
     *      ...
     *      filter[0][data][type]   string
     *      filter[0][data][value]  1
     *      filter[0][field]        alias
     * </code>
     * @param array $where      The array of where clauses<code>
     *  array(
     *      array('name = ?' => test),
     *      array('id' => 1)
     *  );</code>
     *
     * @return OSDN_Response
     * Details of contain data <code>
     *      rows array  the rows collection
     *      total int   the total count of rows
     * </code>
     */
    public function getList(array $params)
    {
        $response = new OSDN_Response();

        $accounts = new OSDN_Accounts_Table_Accounts();

        $select = $this->_table->getAdapter()->select();
        $select->from(array('o' => $this->_table->getTableName()))
        ->joinLeft(array(
            'u' => $accounts->getTableName()),
            'o.account_id=u.id',
            array('account_name' => 'u.name')
        );

        $acl = OSDN_Accounts_Prototype::getAcl();

        // Show only orders created by this account for managers
        if (!$acl->isAllowed(
            OSDN_Acl_Resource_Generator::getInstance()->admin,
            OSDN_Acl_Privilege::VIEW)
        ) {
            $select->where('account_id = ?', OSDN_Accounts_Prototype::getId());
        }

        /*
        switch($params['Xfilter']) {
        	case 1: // Current
        		$select->where('success_date_fact IS NULL');
        		break;
        	case 2: // Success
        		$select->where('success_date_fact IS NOT NULL');
        		break;
        	case 3: // Overdue
        		$select->where('success_date_fact IS NULL');
        		$select->where('success_date_planned < CURDATE()');
        		break;
        	default:
        }
        $select->order('success_date_fact');
        */

        $select->order('closed');
        $select->order('ondate ASC');

        $plugin = new OSDN_Db_Plugin_Select($this->_table, $select);
        $plugin->parse($params);

        $status = null;
        try {
        	$rows = $select->query()->fetchAll();
            $response->setRowset($rows);
            $response->totalCount = $plugin->getTotalCount();
            $status = PMS_Status::OK;
        } catch (Exception $e) {
            $status = PMS_Status::DATABASE_ERROR;
            if (OSDN_DEBUG) {
                throw $e;
            }
        }
        return $response->addStatus(new PMS_Status($status));
    }

    public function make(array $params)
    {
        $f = new OSDN_Filter_Input(array(
            '*'             => 'StringTrim'
        ), array(
            'ondate'  => array(array('StringLength', 1, 20))
        ), $params);

        $response = new OSDN_Response();
        $response->addInputStatus($f);
        if ($response->hasNotSuccess()) {
            return $response;
        }
        $id = $this->_table->insert(array(
            'account_id' => OSDN_Accounts_Prototype::getId(),
            'ondate'     => $f->ondate
        ));

        if (!$id) {
            return $response->addStatus(new PMS_Status(PMS_Status::FAILURE));
        }

        $ordersGoods = new PMS_OrdersGoods_Table();
        $data = Zend_Json::decode($params['data']);
        foreach ($data as $row) {
            $ordersGoods->insert(array(
                'order_id'  => $id,
                'good_id'   => $row['good_id'],
                'number'    => $row['number']
            ));
        }

        $response->addData('id', $id);
        return $response->addStatus(new PMS_Status(PMS_Status::OK));
    }
}