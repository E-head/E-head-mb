<?php

/**
 * The basic id validator
 *
 * @category OSDN
 * @package OSDN_Validate
 */
class OSDN_Validate_Id extends Zend_Validate_Abstract
{

    const INCORRECT = 'notCorrectId';
    
    /**
     * Contain error messages
     * @var array
     */
    protected $_messageTemplates = array(
        self::INCORRECT => "'%value%' does not appear to be an id"
    );
    
    /**
     * Check if zero is allowed
     *
     * @var boolean
     */
    protected $_allowZero = false;
    
    /**
     * OSDN_Validate_Id constructor
     *
     * @param boolean $allowZero        true if zero is allowed
     */
    public function __construct($allowZero = false)
    {
        if (is_bool($allowZero)) {
            $this->_allowZero = $allowZero;
        }
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value is a valid id
     *
     * @param  string|int $value
     * @return boolean
     */
    public function isValid($value)
    {
        $int = new Zend_Validate_Int();
        if (!$int->isValid($value)) {
            $this->_error(null, $value);
            return false;
        }
        
        $valueInt = (int) $value;
        $this->_setValue($valueInt);
        
        $greaterThen = new Zend_Validate_GreaterThan($this->_allowZero ? -1 : 0);
        if (!$greaterThen->isValid($this->_value)) {
            $this->_error();
            return false;
        }

        return true;
    }
}
