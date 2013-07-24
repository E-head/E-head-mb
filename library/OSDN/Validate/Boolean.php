<?php

/**
 * The basic boolean validator
 *
 * @category OSDN
 * @package OSDN_Validate
 */
class OSDN_Validate_Boolean extends Zend_Validate_Abstract
{

    const INCORRECT = 'notCorrectBoolean';
    
    protected $_allowStrings = true;

    protected $_allowed = array(0, 1, '0', '1', true, false);
    
    protected $_allowedStrings = array('true', 'false');
    
    public function __construct($allowStrings = null)
    {
        if (!is_null($allowStrings)) {
            $this->_allowStrings = $allowStrings;
        }
    }
    
    /**
     * Contain error messages
     * @var array
     */
    protected $_messageTemplates = array(
        self::INCORRECT => "'%value%' does not appear to be a boolean"
    );
    
    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value is a valid boolean
     *
     * @param  string|int|boolean $value
     * @return boolean
     */
    public function isValid($value)
    {
        if (in_array($value, $this->_allowed, true)) {
            return true;
        }
        
        if ($this->_allowStrings && in_array($value, $this->_allowedStrings, true)) {
            return true;
        }
        
        $this->_error(null, $value);
        return false;
    }
}
