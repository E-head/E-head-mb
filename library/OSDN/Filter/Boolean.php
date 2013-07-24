<?php

/**
 * Allow to filter the boolean values
 *
 * @category OSDN
 * @package OSDN_Filter
 */
class OSDN_Filter_Boolean implements Zend_Filter_Interface
{
    /**
     * Allow strings, such as "true", "false"
     *
     * @var bool
     */
    protected $_allowStrings;
    
    protected $_allowed = array(0, 1, '0', '1', true, false);
    
    protected $_allowedStrings = array('true', 'false', 'null');
    
    /**
     * The constructor
     *
     * @param boolean $allowStrings
     */
    public function __construct($allowStrings = true)
    {
        $this->_allowStrings = (boolean) $allowStrings;
    }
    
    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns (boolean) $value
     *
     * @param  string $value
     * @return integer
     */
    public function filter($value)
    {
        if (in_array($value, $this->_allowed, true)) {
            return (boolean) $value;
        }
        
        if ($this->_allowStrings && in_array($value, $this->_allowedStrings, true)) {
            return 'true' === $value;
        }
    }
}
