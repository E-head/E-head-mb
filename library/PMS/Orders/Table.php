<?php

class PMS_Orders_Table extends OSDN_Db_Table_Abstract
{
    /**
     * Table name
     *
     * @var string
     */
    protected $_name = 'orders';

    protected $_nullableFields = array('ondate', 'closed');

}