<?php

/**
 * Configuration status constants
 * 
 * @category OSDN
 * @package OSDN_Configuration
 */
class OSDN_Configuration_Status extends OSDN_Response_Status_Storage_Abstract     
{
    /**
     * Module code
     *
     * @var int
     */
    protected $_moduleCode = 123;
    
    /**
     * Module name
     *
     * @var int
     */
    protected $_moduleName = 'Configuration';
       
    /**
     * Description storage
     *
     * @var array
     */
    protected $_storage = array(
        self::ADDED                         => 'Added',
        self::ADD_FAILED                    => 'Addition failed',
        
        self::DELETED                       => 'Deleted',
        self::DELETE_FAILED                 => 'Deleting failed',
        
        self::UPDATED                       => 'Updated successfully',
        self::UPDATE_FAILED               => 'Update failed',
        self::UPDATED_NO_ONE_ROWS_UPDATED   => 'No rows were updated'
    );
}
