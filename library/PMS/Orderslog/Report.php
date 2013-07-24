<?php

class PMS_Orderslog_Report
{
	protected $_tableOrderslog, $_tableStaff;

    public function __construct()
    {
        $this->_tableOrderslog = new PMS_Orderslog_Table();
        $this->_tableStaff = new PMS_Staff_Table();
    }

    public function generate(array $params)
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

        $select = $this->_tableOrderslog->getAdapter()->select()
        ->from(array('o' => $this->_tableOrderslog->getTableName()))
        ->joinLeft(array('s' => $this->_tableStaff->getTableName()),
            'o.staff_id=s.id', array('staff_name' => 'name'))
        ->order('date ASC');

        if (!empty($f->start)) {
            $select->where('date >= ?', $f->start);
        }
        if (!empty($f->end)) {
            $select->where('date <= ?', $f->end);
        }

        try {
            $rows = $select->query()->fetchAll();
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
        }

        $response->data = array(
            'rows'  => $rows,
            'start' => $f->start,
            'end'   => $f->end
        );

        return $response->addStatus(new PMS_Status(PMS_Status::OK));
    }
}