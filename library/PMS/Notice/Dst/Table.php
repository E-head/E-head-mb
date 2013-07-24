<?php

class PMS_Notice_Dst_Table extends OSDN_Db_Table_Abstract
{
    /**
     * Table name
     * @var string
     */
    protected $_name = 'notice_dst';

    /**
     * @param array $data
     * @return int|boolean  last_inserted_id | false if exception
     */
    public function insert(array $data)
    {
        $data['date'] = new Zend_Db_Expr('NULL');
        return parent::insert($data);
    }
}