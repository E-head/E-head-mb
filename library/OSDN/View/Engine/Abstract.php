<?php

/**
 * Abstract class for OSDN_View_Engine_Interface implementation
 * 
 * @category OSDN
 * @package OSDN_View
 * @subpackage OSDN_View_Engine
 */
abstract class OSDN_View_Engine_Abstract implements OSDN_View_Engine_Interface
{
    /**
     * Data container
     *
     * @var array
     */
    protected $_data = array();

    public function getEngine()
    {}
    
    /**
     * Retrieve the data from collection
     *
     * @return array
     */
    public function getCollection()
    {
        return $this->_data;
    }
    
    /**
     * Directly assigns a variable
     *
     * @param string $key   The variable key
     * @param mixed $val    The variable value
     * @return null 
     */
    public function __set($key, $val) 
    {
        $this->_data[$key] = $val;
    }
    
    /**
     * Directly access to variable
     *
     * @param string $key       The variable key
     * @return mixed
     */
    public function __get($key)
    {
        if ($this->_isProtected($key)) {
            throw new OSDN_Exception('Protected or private is not allowed.');
            return;
        }
        
        if (isset($this->$key)) {
            return $this->_data[$key];
        }
        return;
    }
    
    /**
     * Allows testing with empty() and isset()
     *
     * @param string $key   The variable key
     * @return boolean
     */
    public function __isset($key)
    {
        return isset($this->_data[$key]);
    }

    /**
     * Allows unset() on object properties to work
     *
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        if (isset($this->$key)) {
            unset($this->$key);
        }
    }
    
    /**
     * Assigns variables to the storage via differing strategies.
     *
     * assign('name', $value) assigns a variable called 'name'
     * with the corresponding $value.
     *
     * assign($array) assigns the array keys as variable
     * names (with the corresponding array values).
     *
     * @see    __set()
     * @param  string|array The assignment strategy to use.
     * @param  mixed (Optional) If assigning a named variable, use this
     * as the value.
     * @return OSDN_View_Abstract Fluent interface
     * @throws OSDN_Exception if $spec is neither a string nor an array,
     * or if an attempt to set a private or protected member is detected
     */
    public function assign($spec, $value = null)
    {
        if (is_string($spec)) {
            if ($this->_isProtected($spec)) {
                throw new OSDN_Exception('Private or protected members is not allowed');
                return;
            }
            $this->$spec = $value;
            
        } elseif (is_array($spec)) {
            
            $error = false;
            foreach ($spec as $key => $val) {
                if ($this->_isProtected($key)) {
                    $error = true;
                    break;
                }
                $this->$key = $val;
            }
            
            if ($error) {
                throw new OSDN_Exception('Setting private or protected class members is not allowed');
            }
        } else {
            throw new OSDN_Exception('assign() expects a string or array, received ' . gettype($spec));
        }

        return $this;
    }
    
    /**
     * Check if key can be private
     *
     * @param string $key   The variable key
     * @return boolean
     */
    private function _isProtected($key)
    {
        return '_data' == substr($key, 0, 5);
    }
    
    /**
     * Writed for implement Zend_View_Interface only
     *
     * @param unknown_type $path
     */
    public function setScriptPath($path) {}
    public function getScriptPaths() 
    {
        return array();
    }
    public function setBasePath($path, $classPrefix = 'Zend_View') {}
    public function addBasePath($path, $classPrefix = 'Zend_View') {}
    public function clearVars() {}
    public function render($name) {}
}