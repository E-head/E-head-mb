<?php

class OSDN_Controller_Assemble_Table_Assemble extends OSDN_Db_Table_Abstract 
{
    protected $_name = 'assemble';
    
    protected $_sequence = 'md5';

    /**
     * @see parent::insert()
     */
    public function insert(array $data) 
    {
        if (isset($data['data'])) {
            $data['data'] = serialize($data['data']);
        }
        $data['expires'] = new Zend_Db_Expr('NOW()');
        return parent::insert($data);
    } 
    
    /**
     * @see parent::update()
     */
    public function update(array $data, $where)
    {
        if (isset($data['data'])) {
            $data['data'] = serialize($data['data']);
        }
        parent::update($data, $where);
    }
}