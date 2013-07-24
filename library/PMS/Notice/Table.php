<?php

class PMS_Notice_Table extends OSDN_Db_Table_Abstract
{
    /**
     * Table name
     * @var string
     */
    protected $_name = 'notice';

    /**
     * @param array $data
     * @return int|boolean  last_inserted_id | false if exception
     */
    public function insert(array $data)
    {
        $data['account_id'] = OSDN_Accounts_Prototype::getId();
        unset($data['date']);
        return parent::insert($data);
    }
}