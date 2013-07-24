<?php

class PMS_Reports
{
	protected $_tableOrders, $_tableAccounts;

	protected $_monthNames = array(
	   'Нулябрь', 'Январь', 'Февраль', 'Март', 'Апрель', 'Май',
	   'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
	);

    public function __construct()
    {
        $this->_tableOrders = new PMS_Orders_Table_Orders();
        $this->_tableAccounts = new OSDN_Accounts_Table_Accounts();
        $this->_tableCustomers = new PMS_Customers_Table_Customers();
    }

    public function generateSchedule($type)
    {
        $response = new OSDN_Response();

        $types = array('production', 'print', 'mount');
        if (!in_array($type, $types)) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'type'));
        }

    	$date = date_create();
    	$select = $this->_tableOrders->getAdapter()->select();
    	$select->from(array('o' => $this->_tableOrders->getTableName()),
            array('id', 'address'));
    	$select->joinLeft(array('a' => $this->_tableAccounts->getTableName()),
            'o.creator_id=a.id', array(
                'creator_name' => new Zend_Db_Expr('CONCAT(a.name, ", тел. ", a.phone)')
            )
        );
    	$select->joinLeft(array('c' => $this->_tableCustomers->getTableName()),
            'o.customer_id=c.id', array('customer_name' => 'name')
        );
    	$select->where($type . '_start_planned <= ?', date_format($date, 'Y-m-d'));
    	$select->where($type . '_end_fact IS NULL');
    	$select->where('success_date_fact IS NULL');
        $select->order($type . '_start_planned');
    	try {
            $orders = $select->query()->fetchAll();
    	} catch (Exception $e) {
    	   if (OSDN_DEBUG) {
                throw $e;
            }
    		return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
    	}
    	$response->data = array(
    	   //'debug'     => $select->assemble(),
    	   'type'      => $type,
    	   'date'      => $date,
    	   'orders'    => $orders
    	);
    	return $response->addStatus(new PMS_Status(PMS_Status::OK));
    }

    public function generatePlanning()
    {
    	$response = new OSDN_Response();

    	$out = array();

    	$today = new Zend_Date();
        $startDate = $today->setDay(1);

        $days = $this->getMonthDays($startDate);

        // Fetch orders with full cycle
    	$select = $this->_tableOrders->getAdapter()->select()
            ->from(array('o' => $this->_tableOrders->getTableName()))
            ->joinLeft(array('a' => $this->_tableAccounts->getTableName()),
                      'o.creator_id=a.id', array('creator_name' => 'name'))
            ->joinLeft(array('c' => $this->_tableCustomers->getTableName()),
                      'o.customer_id=c.id', array('customer_name' => 'name'))
            ->where('success_date_fact IS NULL')
            ->where('(production = 1 OR print = 1)')
            ->order('success_date_planned');
    	try {
            $orders = $select->query()->fetchAll();
    	} catch (Exception $e) {
    		return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
    	}

        $orders = $this->prepareOrders($orders, $days, $startDate);
        if ($orders !== false) {
            $out[] = array(
                'title'  => 'Заказы с полным циклом',
                'days'   => $days,
                'orders' => $orders
            );
        }

        // Fetch orders only with montage
    	$select = $this->_tableOrders->getAdapter()->select()
            ->from(array('o' => $this->_tableOrders->getTableName()))
            ->joinLeft(array('a' => $this->_tableAccounts->getTableName()),
                      'o.creator_id=a.id', array('creator_name' => 'name'))
            ->joinLeft(array('c' => $this->_tableCustomers->getTableName()),
                      'o.customer_id=c.id', array('customer_name' => 'name'))
            ->where('success_date_fact IS NULL')
            ->where('production = 0')
            ->order('success_date_planned');
    	try {
            $orders = $select->query()->fetchAll();
    	} catch (Exception $e) {
    		return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
    	}

        $orders = $this->prepareOrders($orders, $days, $startDate);
        if ($orders !== false) {
            $out[] = array(
                'title'  => 'Заказы только на монтаж',
                'days'   => $days,
                'orders' => $orders
            );
        }

    	$response->data = array(
    	   'data' => $out,
    	   'date' => $this->_monthNames[$today->get('M')] . $today->get(' Y')
    	);
        return $response->addStatus(new PMS_Status(PMS_Status::OK));
    }

    public function generateManagers(array $params)
    {
        $response = new OSDN_Response();

        $f = new OSDN_Filter_Input(array(
            '*' => 'StringTrim'
        ), array(
            'start'  => array(array('StringLength', 0, 10), 'allowEmpty' => true),
            'end'    => array(array('StringLength', 0, 10), 'allowEmpty' => true)
        ), $params);

        $response->addInputStatus($f);
        if ($response->hasNotSuccess()) {
            return $response;
        }

        $tableOrders    = $this->_tableOrders->getTableName();
        $tableAccounts  = $this->_tableAccounts->getTableName();
        $rowsMerged     = array();
        $rowStructure   = array(
            'summ_success'  => 0,
            'summ_added'    => 0,
            'failed_count'  => 0,
            'name'          => ''
        );
        $select = $this->_tableOrders->getAdapter()->select();

        // Get total summ of success orders by account for given period
        $select->reset()->from(array('o' => $tableOrders),
            array('value' => new Zend_Db_Expr('SUM(cost)'), 'creator_id')
        )
        ->joinLeft(array('a' => $tableAccounts), 'o.creator_id=a.id', 'name')
        ->where('success_date_fact IS NOT NULL')
        ->group('creator_id');

        if (!empty($f->start)) {
            $select->where('success_date_fact >= ?', $f->start);
        }
        if (!empty($f->end)) {
            $select->where('success_date_fact <= ?', $f->end);
        }

        try {
            $rows = $select->query()->fetchAll();
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
        }

        // Parse result rows into one merged array
        foreach ($rows as $row) {
            $rowsMerged[$row['creator_id']] = $rowStructure;
            $rowsMerged[$row['creator_id']]['name'] = $row['name'];
            $rowsMerged[$row['creator_id']]['summ_success'] = $row['value'];
        }

        // Get total summ of added orders by account for given period
        $select->reset()->from(array('o' => $tableOrders),
            array('value' => new Zend_Db_Expr('SUM(cost)'), 'creator_id')
        )
        ->joinLeft(array('a' => $tableAccounts), 'o.creator_id=a.id', 'name')
        ->group('creator_id');

        if (!empty($f->start)) {
            $select->where('created >= ?', $f->start);
        }
        if (!empty($f->end)) {
            $select->where('created <= ?', $f->end);
        }

        try {
            $rows = $select->query()->fetchAll();
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
        }

        // Parse result rows into one merged array
        foreach ($rows as $row) {
            if (isset($rowsMerged[$row['creator_id']])) {
                $rowsMerged[$row['creator_id']]['summ_added'] = $row['value'];
            } else {
                $rowsMerged[$row['creator_id']] = $rowStructure;
                $rowsMerged[$row['creator_id']]['name'] = $row['name'];
                $rowsMerged[$row['creator_id']]['summ_added'] = $row['value'];
            }
        }

        // Get total count of failed orders by account for given period
        $select->reset()->from(array('o' => $tableOrders),
            array('value'  => new Zend_Db_Expr('COUNT(*)'), 'creator_id')
        )
        ->joinLeft(array('a' => $tableAccounts), 'o.creator_id=a.id', 'name')
        ->where('success_date_fact IS NULL')
        ->orWhere('success_date_fact IS NOT NULL
            AND success_date_planned < success_date_fact')
        ->group('creator_id');

        if (!empty($f->start)) {
            $select->where('success_date_planned >= ?', $f->start);
        }
        if (!empty($f->end)) {
            $select->where('success_date_planned <= ?', $f->end);
        }

        try {
            $rows = $select->query()->fetchAll();
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
        }

        // Parse result rows into one merged array
        foreach ($rows as $row) {
            if (isset($rowsMerged[$row['creator_id']])) {
                $rowsMerged[$row['creator_id']]['failed_count'] = $row['value'];
            } else {
                $rowsMerged[$row['creator_id']] = $rowStructure;
                $rowsMerged[$row['creator_id']]['name'] = $row['name'];
                $rowsMerged[$row['creator_id']]['failed_count'] = $row['value'];
            }
        }

        $response->data = array(
            'rows'  => array_values($rowsMerged),
            'start' => $f->start,
            'end'   => $f->end
        );
        return $response->addStatus(new PMS_Status(PMS_Status::OK));
    }

    public function generateCustomers(array $params)
    {
        $response = new OSDN_Response();

        $f = new OSDN_Filter_Input(array(
            '*' => 'StringTrim'
        ), array(
            'start'  => array(array('StringLength', 0, 10), 'allowEmpty' => true),
            'end'    => array(array('StringLength', 0, 10), 'allowEmpty' => true)
        ), $params);

        $response->addInputStatus($f);
        if ($response->hasNotSuccess()) {
            return $response;
        }

        $tableCustomers = new PMS_Customers_Table_Customers();
        $tableCustomers = $tableCustomers->getTableName();
        $tableOrders    = $this->_tableOrders->getTableName();
        $rowsMerged     = array();
        $rowStructure   = array(
            'customer_id'   => 0,
            'summ_success'  => 0,
            'summ_added'    => 0,
            'failed_count'  => 0,
            'name'          => ''
        );
        $select = $this->_tableOrders->getAdapter()->select();

        // Get total summ of success orders by account for given period
        $select->reset()->from(array('o' => $tableOrders),
            array('value' => new Zend_Db_Expr('SUM(cost)'), 'customer_id')
        )
        ->joinLeft(array('c' => $tableCustomers), 'o.customer_id=c.id', 'name')
        ->where('success_date_fact IS NOT NULL')
        ->group('customer_id');

        if (!empty($f->start)) {
            $select->where('success_date_fact >= ?', $f->start);
        }
        if (!empty($f->end)) {
            $select->where('success_date_fact <= ?', $f->end);
        }

        try {
            $rows = $select->query()->fetchAll();
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
        }

        // Parse result rows into one merged array
        foreach ($rows as $row) {
            $rowsMerged[$row['customer_id']] = $rowStructure;
            $rowsMerged[$row['customer_id']]['customer_id'] = $row['customer_id'];
            $rowsMerged[$row['customer_id']]['name'] = $row['name'];
            $rowsMerged[$row['customer_id']]['summ_success'] = $row['value'];
        }

        // Get total summ of unfinished orders by account for given period
        $select->reset()->from(array('o' => $tableOrders),
            array('value' => new Zend_Db_Expr('SUM(cost)'), 'customer_id')
        )
        ->joinLeft(array('c' => $tableCustomers), 'o.customer_id=c.id', 'name')
        ->group('customer_id');

        if (!empty($f->start)) {
            $select->where('created >= ?', $f->start);
        }
        if (!empty($f->end)) {
            $select->where('created <= ?', $f->end);
        }

        try {
            $rows = $select->query()->fetchAll();
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
        }

        // Parse result rows into one merged array
        foreach ($rows as $row) {
            if (isset($rowsMerged[$row['customer_id']])) {
                $rowsMerged[$row['customer_id']]['summ_added'] = $row['value'];
            } else {
                $rowsMerged[$row['customer_id']] = $rowStructure;
                $rowsMerged[$row['customer_id']]['customer_id'] = $row['customer_id'];
                $rowsMerged[$row['customer_id']]['name'] = $row['name'];
                $rowsMerged[$row['customer_id']]['summ_added'] = $row['value'];
            }
        }

        // Get total count of failed orders by account for given period
        $select->reset()->from(array('o' => $tableOrders),
            array('value'  => new Zend_Db_Expr('COUNT(*)'), 'customer_id')
        )
        ->joinLeft(array('c' => $tableCustomers), 'o.customer_id=c.id', 'name')
        ->where('success_date_fact IS NULL')
        ->orWhere('success_date_fact IS NOT NULL
            AND success_date_planned < success_date_fact')
        ->group('customer_id');

        if (!empty($f->start)) {
            $select->where('success_date_planned >= ?', $f->start);
        }
        if (!empty($f->end)) {
            $select->where('success_date_planned <= ?', $f->end);
        }

        try {
            $rows = $select->query()->fetchAll();
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
        }

        // Parse result rows into one merged array
        foreach ($rows as $row) {
            if (isset($rowsMerged[$row['customer_id']])) {
                $rowsMerged[$row['customer_id']]['failed_count'] = $row['value'];
            } else {
                $rowsMerged[$row['customer_id']] = $rowStructure;
                $rowsMerged[$row['customer_id']]['customer_id'] = $row['customer_id'];
                $rowsMerged[$row['customer_id']]['name'] = $row['name'];
                $rowsMerged[$row['customer_id']]['failed_count'] = $row['value'];
            }
        }

        $response->data = array(
            'rows'  => array_values($rowsMerged),
            'start' => $f->start,
            'end'   => $f->end
        );
        return $response->addStatus(new PMS_Status(PMS_Status::OK));
    }

    //--------------------------------------------------------------------------

    private function getMonthDays($date)
    {
        $date = clone($date);
        $month = $date->get('M');
        $days = array();
        for ($i = 1; $i < 32; $i++) {
            $days[$i] = $date->get(Zend_Date::WEEKDAY_NAME);
            $date->add(1, 'd');
            if ($date->get('M') != $month) {
                break;
            }
        }
        return $days;
    }

    private function prepareOrders($orders, $days, $startDate)
    {
        $out = array();
        foreach ($orders as $order) {
            $sdp = $order['success_date_planned'];
            $order['success_date_planned'] = empty($sdp) ? false : new Zend_Date($sdp);

            $psp = $order['production_start_planned'];
            $order['production_start_planned'] = empty($psp) ? false : new Zend_Date($psp);
            $psf = $order['production_start_fact'];
            $order['production_start_fact'] = empty($psf) ? false : new Zend_Date($psf);
            $pep = $order['production_end_planned'];
            $order['production_end_planned'] = empty($pep) ? false : new Zend_Date($pep);
            $pef = $order['production_end_fact'];
            $order['production_end_fact'] = empty($pef) ? false : new Zend_Date($pef);

            $prsp = $order['print_start_planned'];
            $order['print_start_planned'] = empty($prsp) ? false : new Zend_Date($prsp);
            $prsf = $order['print_start_fact'];
            $order['print_start_fact'] = empty($prsf) ? false : new Zend_Date($prsf);
            $prep = $order['print_end_planned'];
            $order['print_end_planned'] = empty($prep) ? false : new Zend_Date($prep);
            $pref = $order['print_end_fact'];
            $order['print_end_fact'] = empty($pref) ? false : new Zend_Date($pref);

            $msp = $order['mount_start_planned'];
            $order['mount_start_planned'] = empty($msp) ? false : new Zend_Date($msp);
            $msf = $order['mount_start_fact'];
            $order['mount_start_fact'] = empty($msf) ? false : new Zend_Date($msf);
            $mep = $order['mount_end_planned'];
            $order['mount_end_planned'] = empty($mep) ? false : new Zend_Date($mep);
            $mef = $order['mount_end_fact'];
            $order['mount_end_fact'] = empty($mef) ? false : new Zend_Date($mef);

            $order['days'] = $this->prepareDays($order, $days, clone($startDate));
            if ($order['days'] !== false) {
                $out[] = $order;
            }
        }
        return empty($out) ? false : $out;
    }

    private function prepareDays($order, $days, $startDate)
    {
        $out = array();
        foreach ($days as $day => $dayNumber) {
            $val = $this->getDay($order, $startDate);
            $out[$day] = $val;
            $startDate->add(1, 'd');
            if ($val) {
                $count++;
            }
        }
        return $count ? $out : false;
    }

    private function getDay($order, $date)
    {
        $sdp = $order['success_date_planned'];

        $psp = $order['production_start_planned'];
        $psf = $order['production_start_fact'];
        $pep = $order['production_end_planned'];
        $pef = $order['production_end_fact'];

        $prsp = $order['print_start_planned'];
        $prsf = $order['print_start_fact'];
        $prep = $order['print_end_planned'];
        $pref = $order['print_end_fact'];

        $msp = $order['mount_start_planned'];
        $msf = $order['mount_start_fact'];
        $mep = $order['mount_end_planned'];
        $mef = $order['mount_end_fact'];

        $today = new Zend_Date();

        if ($psp && $pep) {
            if ($date >= $psp && $date <= $pep) {
                return 'planning_production';
            }
            if ($pef === false) {
                if ($date <= $today && $date >= $psp) {
                    return 'planning_production late';
                }
            } else {
                if ($date <= $pef && $date >= $psp) {
                    return 'planning_production late';
                }
            }
        }

        if ($prsp && $prep) {
            if ($date >= $prsp && $date <= $prep) {
                return 'planning_print';
            }
            if ($pref === false) {
                if ($date <= $today && $date >= $prsp) {
                    return 'planning_print late';
                }
            } else {
                if ($date <= $pref && $date >= $prsp) {
                    return 'planning_print late';
                }
            }
        }

        if ($msp != false && $mep != false) {
            if ($date >= $msp && $date <= $mep) {
                return 'planning_mount';
            }
            if ($mef === false) {
                if ($date <= $today  && $date >= $msp) {
                    return 'planning_mount late';
                }
            } else {
                if ($date <= $mef && $date >= $msp) {
                    return 'planning_mount late';
                }
            }
        }

        return '';
    }
}