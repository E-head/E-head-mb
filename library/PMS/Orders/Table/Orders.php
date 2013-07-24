<?php

class PMS_Orders_Table_Orders extends OSDN_Db_Table_Abstract
{
    /**
     * Table name
     *
     * @var string
     */
    protected $_name = 'orders';

    protected $_nullableFields = array(
        'description',
        'production_start_planned',
        'production_start_fact',
        'production_end_planned',
        'production_end_fact',
        'print_start_planned',
        'print_start_fact',
        'print_end_planned',
        'print_end_fact',
        'mount_start_planned',
        'mount_start_fact',
        'mount_end_planned',
        'mount_end_fact',
        'success_date_planned',
        'success_date_fact'
    );

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