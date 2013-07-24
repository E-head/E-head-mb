<?php

/**
 * The class give interface for easily operating with response status information
 *
 * @category OSDN
 * @package OSDN_Response
 */
class OSDN_Response_Status implements OSDN_Response_Status_Interface
{
    /**
     * Status code
     *
     * @var int
     */
    protected $_statusCode;
    
    /**
     * Status message
     *
     * @var string
     */
    protected $_statusMessage;
    
    /**
     * Module code
     *
     * @var int
     */
    protected $_moduleCode;
    
    /**
     * Module name
     *
     * @var string
     */
    protected $_moduleName;
    
    /**
     * Field name
     *
     * @var string
     */
    protected $_field;
    
    /**
     * Status abbreviation
     *
     * @var string
     */
    protected $_statusAbbr;
    
    /**
     * Status constructor
     *
     * The constructor fill status and module configs
     *
     * @param array $config
     * <b>possible attributes</b><pre>
     *      statusCode          ing         required
     *      statusMessage       string      required
     *      moduleCode          int         required
     *      moduleName          string      required
     *      field               string      may be empty
     *      statusAbbr          string      may be empty
     * </pre>
     * @throws OSDN_Exception
     */
    public function __construct(array $config)
    {
        foreach ($config as $attribute => $value) {
            switch ($attribute) {
                case 'statusCode':
                    $this->_statusCode = $value;
                    break;
                case 'statusMessage':
                    $this->_statusMessage = $value;
                    break;
                case 'moduleCode':
                    $this->_moduleCode = $value;
                    break;
                case 'moduleName':
                    $this->_moduleName = $value;
                    break;
                case 'field':
                    $this->_field = $value;
                    break;
                case 'statusAbbr':
                    $this->_statusAbbr = $value;
                    break;
            }
        }
        
        if (empty($this->_statusCode)
            || empty($this->_statusMessage)
            || empty($this->_moduleCode)
            || empty($this->_moduleName)) {
            throw new OSDN_Exception('Bad arguments for OSDN_Response_Status::__construct()');
        }
    }
    
    /**
     * Retrieve the status code
     *
     * @return int
     */
    public function getCode()
    {
        return $this->_statusCode;
    }
    
    /**
     * Retrieve the status message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->_statusMessage;
    }
    
    /**
     * Retrieve the module code
     *
     * @return int
     */
    public function getModuleCode()
    {
        return $this->_moduleCode;
    }
    
    /**
     * Retrieve the module name
     *
     * @return string
     */
    public function getModuleName()
    {
        return $this->_moduleName;
    }
    
    /**
     * Retrieve the field
     *
     * @return string
     */
    public function getField()
    {
        return $this->_field;
    }
    
    /**
     * Retrieve the array of status and module configs
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'statusCode'    => $this->_statusCode,
            'statusMessage' => $this->_statusMessage,
            'moduleCode'    => $this->_moduleCode,
            'moduleName'    => $this->_moduleName,
            'field'         => $this->_field,
            'statusAbbr'    => $this->_statusAbbr
        );
    }
    
    /**
     * Verify the status on success
     *
     * @return Boolean
     */
    public function isSuccess()
    {
        return $this->getCode() > 0;
    }
}
