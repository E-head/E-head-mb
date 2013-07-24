<?php

class PMS_Sales_Expendables
{
    public function __construct()
    {
        $this->_table = new PMS_Sales_Expendables_Table();
    }

    public function add(array $params)
    {
        $f = new OSDN_Filter_Input(array(
            '*'             => 'StringTrim'
        ), array(
            'name'      => array(array('StringLength', 1, 250), 'presence' => 'required'),
            'price'     => array(array('Float', 'en'), 'presence' => 'required'),
            'measure'   => array(array('StringLength', 1, 50), 'presence' => 'required')
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
            'id'        => array('Int', 'presence' => 'required'),
            'name'      => array(array('StringLength', 1, 250), 'presence' => 'required'),
            'price'     => array(array('Float', 'en'), 'presence' => 'required'),
            'measure'   => array(array('StringLength', 1, 50), 'presence' => 'required')
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

    /**
     * Retrieve rows
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
        $select = $this->_table->getAdapter()->select();
        $select->from($this->_table->getTableName());
        $plugin = new OSDN_Db_Plugin_Select($this->_table, $select, array('name'));
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

    public function get($id)
    {
        $response = new OSDN_Response();
        $validate = new OSDN_Validate_Id();
        if (!$validate->isValid($id)) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'id'));
        }
        $select = $this->_table->getAdapter()->select()
            ->from($this->_table->getTableName())
            ->where("id = ? ", $id);
        try {
            $response->setRow($select->query()->fetch());
            $status = PMS_Status::OK;
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            $status = PMS_Status::DATABASE_ERROR;
        }

        return $response->addStatus(new PMS_Status($status));
    }
}