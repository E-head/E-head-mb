<?php

class PMS_Orderslog_Table extends OSDN_Db_Table_Abstract
{
    /**
     * Table name
     *
     * @var string
     */
    protected $_name = 'orderslog';

    /**
     * @param array $data
     * @return int|boolean  last_inserted_id | false if exception
     */
    public function insert(array $data)
    {
        if (!isset($data['creator_id'])) {
            $data['creator_id'] = OSDN_Accounts_Prototype::getId();
        }
        return parent::insert($data);
    }
}