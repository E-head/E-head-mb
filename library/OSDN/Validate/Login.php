<?php

/**
 * The login validator
 *
 * @category    OSDN
 * @package     OSDN_Validate
 * @version     $Id: Login.php 5929 2008-12-18 12:37:19Z flash $
 */
class OSDN_Validate_Login extends Zend_Validate_Abstract
{

    const INCORRECT = 'notCorrectLogin';
    
    /**
     * Contain error messages
     * @var array
     */
    protected $_messageTemplates = array(
        self::INCORRECT => "'%value%' is not correct login"
    );
    
    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value is a valid login
     *
     * @param  string|int $value
     * @return boolean
     */
    public function isValid($value)
    {
        $regex = new Zend_Validate_Regex('/^.{3,50}$/');
        if (!$regex->isValid($value)) {
            $this->_error(null, $value);
            return false;
        }

        return true;
    }
}
