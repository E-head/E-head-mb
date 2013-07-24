<?php

class PMS_Orderslog
{
	protected $_table;

    public function __construct()
    {
        $this->_table = new PMS_Orderslog_Table();
    }

    public function add(array $params)
    {

        $f = new OSDN_Filter_Input(array(
            '*'             => 'StringTrim'
        ), array(
            'date'          => array(array('StringLength', 0, 10), 'presence' => 'required'),
            'staff_id'      => array('Id', 'presence' => 'required'),
            'summ_start'    => array(array('Float', 'en'), 'presence' => 'required'),
            'summ_income'   => array(array('Float', 'en'), 'presence' => 'required'),
            'summ_inkasso'  => array(array('Float', 'en'), 'presence' => 'required'),
            'summ_rest'     => array(array('Float', 'en'), 'presence' => 'required'),
            'inkasso_dst'   => array(array('StringLength', 1, 4096), 'presence' => 'required')
        ), $params);

        $response = new OSDN_Response();
        $response->addInputStatus($f);
        if ($response->hasNotSuccess()) {
            return $response;
        }

        // Check inkasso
        if ($f->summ_inkasso > (string)($f->summ_start + $f->summ_income)) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'summ_inkasso'));
        }

        // Check rest
        if ($f->summ_rest <> (string)(($f->summ_start + $f->summ_income) - $f->summ_inkasso)) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'summ_rest'));
        }

        try {
            $id = $this->_table->insert($f->getData());
            $status = $id ? PMS_Status::OK : PMS_Status::FAILURE;
        } catch (Exception $e) {
            return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
        }

        $response->id = $id;
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
        try {
            $affectedRows = $this->_table->deleteByPk($id);
            $status = PMS_Status::retrieveAffectedRowStatus($affectedRows);
        } catch (Exception $e) {
        	if (OSDN_DEBUG) {
                throw $e;
            }
            $status = PMS_Status::DATABASE_ERROR;
        }
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
     * Details of contain data
     * <code>
     *      rows array  the rows collection
     *      total int   the total count of rows
     * </code>
     */
    public function getList(array $params, array $where = array())
    {
        $response = new OSDN_Response();
        $select = $this->_table->getAdapter()->select();
        $select->from(array('f' => $this->_table->getTableName()), '*');
        $select->joinLeft(array('s' => 'staff'), 'f.staff_id=s.id',
            array('staff_name' => 's.name'));
        $plugin = new OSDN_Db_Plugin_Select($this->_table, $select);
        $plugin->parse($params);
        $status = null;
        try {
            $response->setRowset($select->query()->fetchAll());
            $response->totalCount = $plugin->getTotalCount();
            $response->rest = $this->_getLastRest();
            $status = PMS_Status::OK;
        } catch (Exception $e) {
            $status = PMS_Status::DATABASE_ERROR;
            if (OSDN_DEBUG) {
                throw $e;
            }
        }
        return $response->addStatus(new PMS_Status($status));
    }

    private function _getLastRest()
    {
        $rest = 0;

        $select = $this->_table->getAdapter()->select();
        $select->from(array('f' => $this->_table->getTableName()), 'summ_rest')
        ->order('created DESC')->limit(1);

        try {
            $rest = $select->query()->fetchColumn(0);
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
        }
        return $rest;
    }
}