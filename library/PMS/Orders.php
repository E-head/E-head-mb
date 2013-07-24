<?php

class PMS_Orders
{
    public function __construct()
    {
        $this->_table = new PMS_Orders_Table_Orders();
    }

    public function add(array $params)
    {
        $f = new OSDN_Filter_Input(array(
            '*'             => 'StringTrim'
        ), array(
            'customer_id'           => array('Int', 'allowEmpty' => true),
            'address'               => array(array('StringLength', 1, 255)),
            'description'           => array(array('StringLength', 0, 4096)),
            'cost'                  => array('Int', 'allowEmpty' => true),
            'advanse'               => array('Int', 'allowEmpty' => true),
            'mount'                 => array('Int', 'allowEmpty' => true),
            'production'            => array('Int', 'allowEmpty' => true),
            'success_date_planned'  => array(array('StringLength', 0, 10)),
            'success_date_fact'     => array(array('StringLength', 0, 10)),
        ), $params);

        $response = new OSDN_Response();
        $response->addInputStatus($f);
        if ($response->hasNotSuccess()) {
            return $response;
        }
        $id = $this->_table->insert($f->getData());
        $status = $id ? PMS_Status::OK : PMS_Status::FAILURE;
        $response->addData('id', $id);
        return $response->addStatus(new PMS_Status($status));
    }

    public function update(array $params)
    {
        $f = new OSDN_Filter_Input(array(
            '*'             => 'StringTrim'
        ), array(
            'id'                        => array('Int', 'presence' => 'required'),
            'customer_id'               => array('Int', 'allowEmpty' => true),
            'address'                   => array(array('StringLength', 1, 255)),
            'description'               => array(array('StringLength', 0, 4096)),
            'cost'                      => array('Int', 'allowEmpty' => true),
            'advanse'                   => array('Int', 'allowEmpty' => true),
            'mount'                     => array('Int', 'allowEmpty' => true),
            'production'                => array('Int', 'allowEmpty' => true),
            'production_start_planned'  => array(array('StringLength', 0, 10)),
            'production_start_fact'     => array(array('StringLength', 0, 10)),
            'production_end_planned'    => array(array('StringLength', 0, 10)),
            'production_end_fact'       => array(array('StringLength', 0, 10)),
            'mount_start_planned'       => array(array('StringLength', 0, 10)),
            'mount_start_fact'          => array(array('StringLength', 0, 10)),
            'mount_end_planned'         => array(array('StringLength', 0, 10)),
            'mount_end_fact'            => array(array('StringLength', 0, 10)),
            'success_date_planned'      => array(array('StringLength', 0, 10)),
            'success_date_fact'         => array(array('StringLength', 0, 10)),
            'archive'                   => array('Int', 'allowEmpty' => true)
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

    public function archive($params, $dir = true)
    {
        $response = new OSDN_Response();

        $validate = new OSDN_Validate_Id();

        if (!$validate->isValid($params['id'])) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'id'));
        }

    	$data = array();

    	if ($dir) {

	        $f = new OSDN_Filter_Input(array(
	            'id'   => 'Int',
	            '*'    => 'StringTrim'
	        ), array(
	            'id'               => array('id', 'presence' => 'required'),
	            'invoice_number'   => array(array('StringLength', 1, 255)),
	            'invoice_date'     => array(array('StringLength', 1, 255)),
	            'act_number'       => array(array('StringLength', 1, 255)),
	            'act_date'         => array(array('StringLength', 1, 255))
	        ), $params);
	        $response->addInputStatus($f);

	        if ($response->hasNotSuccess()) {
	            return $response;
	        }

	        $data = $f->getData();
            $data['archive_date'] = new Zend_Db_Expr('NOW()');
    	}

        $data['archive'] = $dir ? 1 : 0;

        $res = $this->_table->updateByPk($data, $params['id']);

        return $response->addStatus(new PMS_Status(
            $res === false ? PMS_Status::FAILURE : PMS_Status::OK
        ));
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
    public function getList(array $params, array $where = array(), $archive = 0)
    {
        $response = new OSDN_Response();
        $select = $this->_table->getAdapter()->select();
        $accounts = new OSDN_Accounts_Table_Accounts();
        $customers = new PMS_Customers_Table_Customers();
        $select->from(array('o' => $this->_table->getTableName()), array(
            '*', 'conflict' => 'IF(success_date_planned IS NULL'
            . ' OR success_date_planned < production_start_planned'
            . ' OR success_date_planned < production_end_planned'
            . ' OR success_date_planned < print_start_planned'
            . ' OR success_date_planned < print_end_planned'
            . ' OR success_date_planned < mount_start_planned'
            . ' OR success_date_planned < mount_end_planned, 1, 0)',
            'success' => 'IF(success_date_fact IS NULL, 0, 1)'));
        $select->joinLeft(array(
            'u' => $accounts->getTableName()),
            'o.creator_id=u.id',
            array('creator_name' => 'u.name')
        );
        $select->join(array(
            'c' => $customers->getTableName()),
            'o.customer_id=c.id',
            array('customer_name' => 'c.name')
        );
        $acl = OSDN_Accounts_Prototype::getAcl();

        // Show only orders created by this account for managers
        if ($acl->isAllowed(
            OSDN_Acl_Resource_Generator::getInstance()->orders->owncheck,
            OSDN_Acl_Privilege::VIEW)
        ) {
            $userId = OSDN_Accounts_Prototype::getId();
            $select->where('creator_id = ?', $userId);
        }

        // Hide orders with production disabled
        if ($acl->isAllowed(
            OSDN_Acl_Resource_Generator::getInstance()->orders->hideproduction,
            OSDN_Acl_Privilege::VIEW)
        ) {
            $select->where('production = 1');
        }

        // Hide orders with print disabled
        if ($acl->isAllowed(
            OSDN_Acl_Resource_Generator::getInstance()->orders->hideprint,
            OSDN_Acl_Privilege::VIEW)
        ) {
            $select->where('print = 1');
        }

        // Hide orders with mountage disabled
        if ($acl->isAllowed(
            OSDN_Acl_Resource_Generator::getInstance()->orders->hidemount,
            OSDN_Acl_Privilege::VIEW)
        ) {
            $select->where('mount = 1');
        }

        $select->where('archive = ?', (int)$archive);

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
        $select->order('success');
        $select->order('conflict DESC');
        //$select->order('success_date_fact');
        $plugin = new OSDN_Db_Plugin_Select($this->_table, $select,
            array('o.id' => 'id', 'address', 'success_date_fact', 'success_date_planned',
                'created', 'creator_id', 'creator_name', 'customer_name', 'conflict', 'success')
        );
        $plugin->parse($params);

        $status = null;
        try {
            $files = new PMS_Files();
        	$rows = $select->query()->fetchAll();
        	foreach ($rows as &$data) {
	            $resp = $files->getAll($data['id']);
	            if ($resp->isSuccess()) {
	                $rowset = $resp->getRowset();
	            } else {
	                $data['files_errors'] = $resp->getStatusCollection();
	                $rowset = array();
	            }
	            $data['files'] = $rowset;
        	}
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

    public function get($id)
    {
        $response = new OSDN_Response();
        $validate = new OSDN_Validate_Id();
        if (!$validate->isValid($id)) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'id'));
        }
        $select = $this->_table->getAdapter()->select();
        $accounts = new OSDN_Accounts_Table_Accounts();
        $customers = new PMS_Customers_Table_Customers();
        $select->from(array('o' => $this->_table->getTableName()), '*');
        $select->join(array(
            'u' => $accounts->getTableName()),
            'o.creator_id=u.id',
            array('creator_name' => 'name')
        );
        $select->join(array(
            'c' => $customers->getTableName()),
            'o.customer_id=c.id',
            array('customer_name' => 'name')
        );
        $select->where('o.id = ?', $id);
        $status = null;
        try {
            $files = new PMS_Files();
        	$row = $select->query()->fetch();
            $resp = $files->getAll($id);
            if ($resp->isSuccess()) {
                $rowset = $resp->getRowset();
            } else {
                $row['files_errors'] = $resp->getStatusCollection();
                $rowset = array();
            }
            $row['files'] = $rowset;
            $response->setRow($row);
            $status = PMS_Status::OK;
        } catch (Exception $e) {
            $status = PMS_Status::DATABASE_ERROR;
            if (OSDN_DEBUG) {
                throw $e;
            }
        }
        return $response->addStatus(new PMS_Status($status));
    }

    public function changeUser($orderId, $userId)
    {
        $response = new OSDN_Response();
        $validate = new OSDN_Validate_Id();
        if (!$validate->isValid($orderId)) {
            return $response->addStatus(new PMS_Status(
            	PMS_Status::INPUT_PARAMS_INCORRECT, 'orderId'));
        }
        if (!$validate->isValid($userId)) {
            return $response->addStatus(new PMS_Status(
            	PMS_Status::INPUT_PARAMS_INCORRECT, 'userId'));
        }
        $res = $this->_table->updateByPk(array('creator_id' => $userId), $orderId);
        $status = $res === false ? PMS_Status::FAILURE : PMS_Status::OK;
        return $response->addStatus(new PMS_Status($status));
    }

    public function getNotes($orderId)
    {
        $response = new OSDN_Response();
        $validate = new OSDN_Validate_Id();
        if (!$validate->isValid($orderId)) {
            return $response->addStatus(new PMS_Status(
            	PMS_Status::INPUT_PARAMS_INCORRECT, 'orderId'));
        }
        $tableNotes = new PMS_Orders_Table_Notes();
        try {
            $result = $tableNotes->fetchAll(
            	array('order_id = ?' => $orderId), 'time ASC');
            $response->setRowset($result->toArray());
            $status = PMS_Status::OK;
        } catch (Exception $e) {
            $status = PMS_Status::DATABASE_ERROR;
        }
        return $response->addStatus(new PMS_Status($status));
    }

    public function addNote($orderId, $text)
    {
        $response = new OSDN_Response();
        $validate = new OSDN_Validate_Id();
        if (!$validate->isValid($orderId)) {
            return $response->addStatus(new PMS_Status(
            	PMS_Status::INPUT_PARAMS_INCORRECT, 'orderId'));
        }
        $text = strip_tags($text);
        $data = array('order_id' => $orderId, 'text' => $text);
        $tableNotes = new PMS_Orders_Table_Notes();
        $id = $tableNotes->insert($data);
        $status = $id ? PMS_Status::OK : PMS_Status::FAILURE;
        return $response->addStatus(new PMS_Status($status));
    }
}