<?php

/**
 * Storage for statuses, statuses description
 *
 * @abstract
 *
 * @category       OSDN
 * @package        OSDN_Response
 * @version		$Id: Abstract.php 8987 2009-05-27 08:27:15Z yaroslav $
 */
abstract class OSDN_Response_Status_Storage_Abstract implements OSDN_Response_Status_Storage_Interface
{

	const OK                        = 1;
    
    const FAILURE                   = -1;
    
    const INPUT_PARAMS_INCORRECT    = -2;
    
    const DATABASE_ERROR            = -3;
    
    const XMLRPC                    = -4;
    
    const SPECIAL_ERROR             = -5;
    
    const ADDED                     = 10;
    const ADD_FAILED                = -10;
    
    const DELETED                   = 20;
    const DELETE_FAILED             = -20;
    
    const UPDATED                   = 30;
    const UPDATE_FAILED           = -31;
    const UPDATED_NO_ONE_ROWS_UPDATED = 31;
    const NO_ONE_ROWS_AFFECTED      = 32;
    
    
	/**
     * Module code
     *
     * @var int
     */
    protected $_moduleCode = null;
    
    /**
     * Module name
     *
     * @var string
     */
    protected $_moduleName = null;

    /**
     * Status code
     *
     * @var int
     */
    protected $_status = null;
    
    /**
     * Status code
     *
     * @var string
     */
    protected $_msg = null;
    
    /**
     * Field name
     * Contain field name or id
     *
     * @var string
     */
    protected $_field = null;
    
    /**
     * Status description collection
     * Different for each module
     *
     * @var array
     */
    protected $_storage = array();
    
    /**
     * System status description collection
     * Common for all modules
     *
     * @var array
     */
    protected $_systemStatuses = array(
        self::OK                            => 'Complete successfully.',
        self::FAILURE                       => 'Failed.',
        self::INPUT_PARAMS_INCORRECT        => 'Input params are missing or incorrect',
        self::DATABASE_ERROR                => 'Operation failed. Database error.',
        self::XMLRPC                        => 'XML-RPC request failed.',
                                 
        self::ADDED                         => 'Added successfully.',
        self::ADD_FAILED                    => 'Addition failed.',
        
        
        self::UPDATED                       => 'Complete successfully.',
        self::UPDATE_FAILED               => 'Update failed.',
        self::UPDATED_NO_ONE_ROWS_UPDATED   => 'Complete successfully. No one rows updated.',
        
        self::DELETED                       => 'Deleted successfully.',
        self::DELETE_FAILED                 => 'Deleting failed.',
        self::NO_ONE_ROWS_AFFECTED          => 'No one rows affected'
    );
    
    /**
     * OSDN_Response_Status_Storage_Abstract constructor
     *
     * Set the status code
     *
     * @throws OSDN_Exception
     * @param   int     $status
     * @param   string  $field
     */
    public function __construct($status, $field = null)
    {
        if ($status === null) {
            throw new OSDN_Exception('Status code is not defined');
        }

        // merge all statuses
        $this->_storage += $this->_systemStatuses;
        
        $allowed = $this->isAllowed($status) || self::SPECIAL_ERROR == $status;
        if (! $allowed) {
            throw new OSDN_Exception('Status code is not exists.');
        }
        $this->_status = $status;
        
        if (null !== $field) {
            $this->_field = $field;
        }
        
        if (null === $this->_moduleCode) {
            throw new OSDN_Exception('Module code is not defined.');
        }
        
        if (null === $this->_moduleName) {
            throw new OSDN_Exception('Module name is not defined.');
        }
    }
    
    /**
     * Retrieve the status object
     *
     * @return OSDN_Response_Status_Interface   status object
     */
    public function getStatus()
    {
        $status = new OSDN_Response_Status(array(
            'statusCode'    => $this->_status,
            'statusMessage' => $this->getMessage($this->_status),
            'moduleCode'    => $this->_moduleCode,
            'moduleName'    => $this->_moduleName,
            'field'         => $this->_field
        ));
        return $status;
    }
    
    /**
     * Retrieve status message
     *
     * @param int $status
     * @return string
     */
    public function getMessage($status)
    {
        if (self::SPECIAL_ERROR !== $status) {
            return $this->_storage[$status];
        } else {
            return $this->_msg;
        }
    }
    
    /**
     * Check if status code exists in storage
     *
     * @param int $status       status code
     * @return boolean
     */
    public function isAllowed($status)
    {
        return array_key_exists($status, $this->_storage);
    }
    
    /**
     * Parse update status
     *
     * @param int|bool $affectedRows
     * @return int
     */
    public static function retrieveAffectedRowStatus($affectedRows)
    {
    	$status = null;
        if (false === $affectedRows) {
            $status = self::DATABASE_ERROR;
        } elseif (0 === $affectedRows) {
            $status = self::UPDATED_NO_ONE_ROWS_UPDATED;
        } else {
            $status = self::OK;
        }
        return $status;
    }
}