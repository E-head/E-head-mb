<?php

class PMS_Storage_History_Table extends OSDN_Db_Table_Abstract
{
    /**
     * Table name
     * @var string
     */
    protected $_name = 'storage_history';

    /**
     * @param array $data
     * @return int|boolean  last_inserted_id | false if exception
     */
    public function insert(array $data)
    {
        unset($data['created']);
        return parent::insert($data);
    }
}