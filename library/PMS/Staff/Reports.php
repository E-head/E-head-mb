<?php

class PMS_Staff_Reports
{
    public function generateStaff(array $params)
    {
        $response = new OSDN_Response();

        $f = new OSDN_Filter_Input(array(
            '*' => 'StringTrim'
        ), array(
            'start'  => array('Date', 'allowEmpty' => false, 'presence' => 'required'),
            'end'    => array('Date', 'allowEmpty' => false, 'presence' => 'required')
        ), $params);

        $response->addInputStatus($f);
        if ($response->hasNotSuccess()) {
            return $response;
        }

        $tableHr = new PMS_Staff_Hr_Table();
        $tablePayments = new PMS_Staff_Payments_Table();
        $tableStaff = new PMS_Staff_Table();

        $result = array();

        // Get list of persons for given period
        $select = $tableHr->getAdapter()->select()
        ->from(array('s' => $tableStaff->getTableName()),
            array(
                'id', 'name', 'function',
                'rate'              => 's.pay_rate',
                'period'            => 's.pay_period',
                'hours_total'       => '(0)',
                'summ_total'        => '(0)',
                'pays_total'        => '(0)',
                'hours_super_total' => '(0)',
                'summ_super_total'  => '(0)',
                'pays_super_total'  => '(0)'
            )
        )->where('archive = 0')->order('name');

        try {
            $rows = $select->query()->fetchAll();
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
        }

        // Parse result rows into one result array with keys insertion
        foreach ($rows as $row) {
            $result[$row['id']] = $row;
        }





        // Get total summ of working hours by person for given period
        $select = $tableHr->getAdapter()->select()
        ->from(array('s' => $tableStaff->getTableName()),
            array(
                'id', 'name', 'function',
                'rate' => 's.pay_rate',
                'period' => 's.pay_period',
                'hours_total'   => new Zend_Db_Expr('SUM(h.value)'),
                'summ_total'    => new Zend_Db_Expr('IF(s.pay_period = "month",
                    s.pay_rate,SUM(h.value*s.pay_rate))')
            )
        )
        ->joinLeft(array('h' => $tableHr->getTableName()),
            'h.staff_id=s.id', array()
        )
        ->group('s.id')
        ->where('archive = 0')
        ->where('h.date >= ?', $f->start)
        ->where('h.date <= ?', $f->end)
        ;

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
            $result[$row['id']]['hours_total'] = $row['hours_total'];
            $result[$row['id']]['summ_total'] = $row['summ_total'];
        }



        // Get total summ of working hours by person for WHOLE period
        $select = $tableHr->getAdapter()->select()
        ->from(array('s' => $tableStaff->getTableName()),
            array(
                'id', 'name', 'function',
                'rate' => 's.pay_rate',
                'period' => 's.pay_period',
                'hours_super_total'   => new Zend_Db_Expr('SUM(h.value)'),
                'summ_super_total'    => new Zend_Db_Expr('IF(s.pay_period = "month",
                    s.pay_rate,SUM(h.value*s.pay_rate))')
            )
        )
        ->joinLeft(array('h' => $tableHr->getTableName()),
            'h.staff_id=s.id', array()
        )
        ->group('s.id')
        ->where('archive = 0')
        ;

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
            $result[$row['id']]['hours_super_total'] = $row['hours_super_total'];
            $result[$row['id']]['summ_super_total'] = $row['summ_super_total'];
        }





        // Get total summ of payments by person for given period
        $select = $tableHr->getAdapter()->select()
        ->from(array('s' => $tableStaff->getTableName()),
            array(
                'id', 'name', 'function',
                'rate' => 's.pay_rate',
                'period' => 's.pay_period',
                'pays_total'   => new Zend_Db_Expr('SUM(p.value)')
            )
        )
        ->joinLeft(array('p' => $tablePayments->getTableName()),
            'p.staff_id=s.id', array()
        )
        ->group('s.id')
        ->where('archive = 0')
        ->where('p.date >= ?', $f->start)
        ->where('p.date <= ?', $f->end)
        ;

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
            $result[$row['id']]['pays_total'] = $row['pays_total'];
        }




        // Get total summ of payments by person for WHOLE period
        $select = $tableHr->getAdapter()->select()
        ->from(array('s' => $tableStaff->getTableName()),
            array(
                'id', 'name', 'function',
                'rate' => 's.pay_rate',
                'period' => 's.pay_period',
                'pays_super_total'   => new Zend_Db_Expr('SUM(p.value)')
            )
        )
        ->joinLeft(array('p' => $tablePayments->getTableName()),
            'p.staff_id=s.id', array()
        )
        ->group('s.id')
        ->where('archive = 0')
        ;

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
            $result[$row['id']]['pays_super_total'] = $row['pays_super_total'];
        }




        $response->data = array(
            'rows'  => array_values($result),
            'start' => $f->start,
            'end'   => $f->end
        );
        return $response->addStatus(new PMS_Status(PMS_Status::OK));
    }

    public function generateVacations($params)
    {
        $response = new OSDN_Response();

        $f = new OSDN_Filter_Input(array(
            '*' => 'StringTrim'
        ), array(
            'start'  => array('Date', 'allowEmpty' => false, 'presence' => 'required'),
            'end'    => array('Date', 'allowEmpty' => false, 'presence' => 'required')
        ), $params);

        $response->addInputStatus($f);
        if ($response->hasNotSuccess()) {
            return $response;
        }

        $tableStaff = new PMS_Staff_Table();
        $result = array();

        $debug = array();

        $persons = $tableStaff->fetchAllColumns(null, null, array('id', 'name', 'function'));

        if ($persons->count()) {

            $tableVacations = new PMS_Staff_Vacations_Table();
            $persons = $persons->toArray();

            foreach ($persons as $person) {

                $select = $tableVacations->getAdapter()->select()
                ->from(array('v' => $tableVacations->getTableName()), array('from', 'to'))
                ->where('v.staff_id = ?', $person['id'])
                ->where('(v.from >= ?', $f->start)
                ->where('v.from <= ?', $f->end)
                ->orWhere('v.to >= ?', $f->start)
                ->where('v.to <= ?)', $f->end)
                ;

                $debug[] = $select->assemble();

                try {
                    $rows = $select->query()->fetchAll();
                } catch (Exception $e) {
                    if (OSDN_DEBUG) {
                        throw $e;
                    }
                    return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
                }

                if (count($rows) > 0) {

                    $person['periods'] = array();

                    foreach ($rows as $row) {
                        $person['periods'][] = array(
                            'from'  => new Zend_Date($row['from']),
                            'to'    => new Zend_Date($row['to'])
                        );
                    }

                    $result[$person['id']] = $person;
                }
            }
        }

        $response->data = array(
            'debug' => $debug,
            'rows'  => $result,
            'days'  => array(),
            'start' => $f->start,
            'end'   => $f->end
        );
        return $response->addStatus(new PMS_Status(PMS_Status::OK));
    }
}