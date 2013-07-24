<?php

class PMS_Storage_Requests_Table extends OSDN_Db_Table_Abstract
{
    /**
     * Table name
     * @var string
     */
    protected $_name = 'storage_requests';

    protected $_nullableFields = array('asset_id', 'order_id', 'name', 'measure');

    /**
     * @param array $data
     * @return int|boolean  last_inserted_id | false if exception
     */
    public function insert(array $data)
    {
        $data['account_id'] = OSDN_Accounts_Prototype::getId();
        unset($data['created']);
        return parent::insert($data);
    }
}