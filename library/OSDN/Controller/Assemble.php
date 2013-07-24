<?php

class OSDN_Controller_Assemble
{
    /**
     * The table object
     *
     * @var OSDN_Controller_Assemble_Table_Assemble
     */
    protected $_table;

    /**
     * The expires time
     *
     * @var seconds
     */
    protected $_expires;
    
    /**
     * The constructor
     *
     */
    public function __construct()
    {
        $this->_table = new OSDN_Controller_Assemble_Table_Assemble();
        $cfg = Zend_Registry::get('config');
        $this->_expires = (int) $cfg->assemble->expires;
    }
    
    /**
     * Insert new assemble
     *
     * @param array $data
     * @return OSDN_Response
     * <code> {
     *    id: int
     * }
     * </code>
     */
    public function insert($data)
    {
        $response = new OSDN_Response();
        
        $id = $this->_table->insert(array(
            'data'  => $data
        ));
        
        if (false != $id) {
            $status = OSDN_Controller_Assemble_Status::OK;
            $response->id = $id;
        } else {
            $status = OSDN_Controller_Assemble_Status::DATABASE_ERROR;
        }
        $response->addStatus(new OSDN_Controller_Assemble_Status($status));
        return $response;
    }
    
    /**
     * Fetch assemble configs by code
     *
     * @param string $code      The char 32 string format
     * @return OSDN_Response
     */
    public function fetch($code)
    {
        $response = new OSDN_Response();
        $row = $this->_table->findOne($code);
        if (!is_null($row)) {
            $status = OSDN_Controller_Assemble_Status::OK;
            $options = unserialize($row->data);
            $response->options = $options;
        } else {
            $status = OSDN_Controller_Assemble_Status::DATABASE_ERROR;
        }
        $response->addStatus(new OSDN_Controller_Assemble_Status($status));
        return $response;
    }
}