<?php

/**
 * The collection of response statuses
 * @category OSDN
 * @package OSDN_Response
 */
class OSDN_Response_Status_Collection implements IteratorAggregate, Countable
{

    /**
     * storage container
     *
     * @var array
     */
    protected $_data = array();

    /**
     * getIterator() - return an iteratable object for use in foreach
     * Completes an IteratorAggregate interface
     *
     * @return ArrayObject iterable container of statuses
     */
    public function getIterator()
    {
        return new ArrayObject($this->_data);
    }
    
    /**
     * Return the count of statuses
     * Complete a Countable interface
     *
     * @return int  Count of statuses
     */
    public function count()
    {
        return count($this->_data);
    }
    
    /**
     * Add new status to storage
     *
     * @param OSDN_Response_Status_Interface $data      status object
     * @return OSDN_Response_Status_Collection
     */
    public function add(OSDN_Response_Status_Interface $data)
    {
        array_push($this->_data, $data);
        return $this;
    }

    /**
     * Retrieve a first status from collection
     *
     * @return OSDN_Response_Status_Interface status object
     */
    public function first()
    {
        return current($this->_data);
    }
    
    /**
     * Debug mode for status collection
     *
     * @return string
     */
    public function __toString()
    {
        $output = array();
        foreach ($this->_data as $status) {
            $output[] = '"' . $status->getCode() . '" -- "' . $status->getMessage() . '"';
        }
        return join("<br />\n", $output);
    }
    
    /**
     * Dump to array
     *
     * @return array
     */
    public function toArray()
    {
        $output = array();
        foreach ($this->_data as $status) {
            $output[] = $status->toArray();
        }
        
        return $output;
    }
}
