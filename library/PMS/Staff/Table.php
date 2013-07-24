<?php

class PMS_Staff_Table extends OSDN_Db_Table_Abstract
{
    /**
     * Table name
     * @var string
     */
    protected $_name = 'staff';

    protected $_nullableFields = array('cv_file');
}