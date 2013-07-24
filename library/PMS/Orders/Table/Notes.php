<?php

class PMS_Orders_Table_Notes extends OSDN_Db_Table_Abstract
{
    /**
     * Table name
     *
     * @var string
     */
    protected $_name = 'notes';

    /**
     * @param array $data
     * @return int|boolean  last_inserted_id | false if exception
     */
    public function insert(array $data)
    {
        if (empty($data['name'])) {
            $currentPerson = OSDN_Accounts_Prototype::getInformation();
            $data['name'] = $currentPerson->name;
        }
        return parent::insert($data);
    }
}