<?php

/**
 * OSDN_Response_Data_Collection
 *
 * @category OSDN
 * @package OSDN_Response
 */
class OSDN_Response_Data_Collection implements IteratorAggregate, Countable
{
    /**
     * Storage of response data
     *
     * @var array
     */
    protected $_data = array();
    
    /**
     * get iterator object
     * Complete the IteratorAggragate interface
     *
     * @return ArrayObject
     */
    public function getIterator()
    {
        return new ArrayObject($this->_data);
    }
    
    /**
     * Get count of response data storage
     * Complete the Contable interface
     *
     * @return int
     */
    public function count()
    {
        return count($this->_data);
    }
    
    /**
     * Clear the data collection
     *
     * @return OSDN_Response_Data_Collection
     */
    public function clear()
    {
	    $this->_data = array();
	    return $this;
    }
    
    /**
     * Add data to response data collection
     *
     * @param mixed $param
     * @param mixed $value
     * @return OSDN_Response_Data_Collection
     */
    public function add($param, $value = null)
    {
        if (is_array($param)) {
            $param = array_change_key_case($param, CASE_LOWER);
            $this->_data = array_merge($this->_data, $param);
        } else {
            $this->_data[strtolower($param)] = $value;
        }
        return $this;
    }
    
    /**
     * Get data by variable name
     *
     * @param string $param
     * @return mixed
     */
    public function get($param)
    {
        $param = strtolower($param);
        if (isset($this->$param)) {
            return $this->_data[$param];
        }
        return null;
    }

    /**
     * Alias for get()
     * Get data from response data collection
     *
     * @param string $param
     * @return mixed
     */
    public function __get($param)
    {
        return $this->get($param);
    }
    
    /**
     * Check if variable present in data collection
     *
     * @param string|int $param
     * @return boolean
     */
    public function __isset($param)
    {
        return array_key_exists(strtolower($param), $this->_data);
    }
    
    /**
     * Alias to add()
     * Add data to response data collection
     *
     * @param mixed $param
     * @param mixed $value
     */
    public function __set($param, $value = null)
    {
        $this->add($param, $value);
    }
    
    /**
     * Remove value from data collection
     *
     * @param mixed $param
     * @return void
     */
    public function __unset($param)
    {
        if (isset($this->$param)) {
            unset($this->_data[$param]);
        }
    }
    
    /**
     * Dump to array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_data;
    }
}