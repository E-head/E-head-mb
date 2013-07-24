<?php

/**
 * The OSDN_Response class is a class for make easily communication
 * interface and API parts
 *
 * @category OSDN
 * @package OSDN_Response
 */
class OSDN_Response
{

    /**
     * Status storage container
     *
     * @var OSDN_Response_Status_Collection
     */
    protected $_statusStorage;

    /**
     * Data storage container
     *
     * @var OSDN_Response_Data_Collection
     */
    protected $_dataStorage;

    /**
     * Response constructor
     *
     * The constructor create default storage object for status and data,
     * take the status and add to status storage
     *
     * @param OSDN_Response_Status_Storage_Interface $status
     */
    public function __construct(OSDN_Response_Status_Storage_Interface $status = null)
    {
        $this->_statusStorage = new OSDN_Response_Status_Collection();
        $this->_dataStorage = new OSDN_Response_Data_Collection();

        if ($status !== null) {
            $this->addStatus($status);
        }
    }

    /**
     * Add new response status
     *
     * @param OSDN_Response_Status_Storage_Interface $status    status object with predefined status
     * @return OSDN_Response
     */
    public function addStatus(OSDN_Response_Status_Storage_Interface $status)
    {
        $this->getStatusCollection()->add($status->getStatus());
        return $this;
    }

    /**
     * Import statuses to response object
     *
     * @param OSDN_Response_Status_Collection $collection
     * @return OSDN_Response
     */
    public function importStatuses(OSDN_Response_Status_Collection $collection)
    {
        foreach ($collection as $status) {
            $this->getStatusCollection()->add($status);
        }
        return $this;
    }

    /**
     * Add new response status by OSDN_Filter_Input data
     *
     * @param OSDN_Filter_Input $filterInput
     * @return OSDN_Response
     */
    public function addInputStatus(OSDN_Filter_Input $filterInput)
    {
        $statuses = $filterInput->getStatuses();
        foreach ($statuses as $s) {
            $this->getStatusCollection()->add($s);
        }
        return $this;
    }

    /**
     * Check if response is success
     * False if no one status defined or one of statuses is not success
     *
     * @return Boolean
     */
    public function isSuccess()
    {
        $statuses = $this->getStatusCollection();

        if ($statuses->count() == 0) {
            return false;
        }

        return !$this->hasNotSuccess();
    }

    /**
     * Check if response is failed
     *
     * @return boolean
     */
    public function isError()
    {
        return !$this->isSuccess();
    }

    /**
     * Check if present not success statuses in the status collection
     *
     * @return boolean
     */
    public function hasNotSuccess()
    {
        $statuses = $this->getStatusCollection();
        if ($statuses->count() == 0) {
            return false;
        }

        foreach ($statuses as $s) {
            if (!$s->isSuccess()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Assign the variable to data storage
     *
     * @param mixed $param      The variable name or array of data
     * @param mixed $value      The variable value
     */
    public function __set($param, $value)
    {
        $this->addData($param, $value);
    }

    /**
     * Check if param isset in data storage
     *
     * @param mixed $param
     * @return boolean
     */
    public function __isset($param)
    {
        return isset($this->getDataCollection()->$param);
    }

    /**
     * Get data from Storage Data Collection by variable name
     *
     * @param string|int $param
     *
     * @return mixed
     */
    public function __get($param)
    {
        return $this->getData($param);
    }

    /**
     * Alias to __set
     *
     * @param mixed $param
     * @param mixed $value
     * @return OSDN_Response
     */
    public function addData($param, $value = null)
    {
        $this->getDataCollection()->add($param, $value);
        return $this;
    }

    /**
     * Retrieve a status collection
     *
     * @return OSDN_Response_Status_Collection
     */
    public function getStatusCollection()
    {
        return $this->_statusStorage;
    }

    /**
     * Retrieve a data collection
     *
     * @return OSDN_Response_Data_Collection
     */
    public function getDataCollection()
    {
        return $this->_dataStorage;
    }

    /**
     * Alias to __get()
     *
     * @param mixed $key
     * @return mixed
     */
    public function getData($param)
    {
        if (isset($this->$param)) {
            return $this->getDataCollection()->get($param);
        }
        return null;
    }

    /**
     * Import existing response collections
     * If wrong parameters set the failure status.
     *
     * @param array $configs
     * <code>
     * contain array (
     *      data: array
     *      status: array
     * );
     * </code>
     *
     * @return OSDN_Response
     */
    public function import($configs)
    {
        if (!is_array($configs)) {
            $status = new OSDN_Response_Status_Storage(OSDN_Response_Status_Storage::FAILURE);
            $this->getStatusCollection()->add($status->getStatus());
            return $this;
        }

        if (!empty($configs['status']) && is_array($configs['status'])) {
            foreach ($configs['status'] as $status) {
                $this->getStatusCollection()->add(new OSDN_Response_Status($status));
            }
        }

        if (!empty($configs['data']) && is_array($configs['data'])) {
            foreach ($configs['data'] as $param => $value) {
                $this->getDataCollection()->add($param, $value);
            }
        }

        return $this;
    }

    /**
     * Set rowset to data collection
     *
     * @param array $rowset The rows collection
     * @return OSDN_Response
     */
    public function setRowset(array $rowset)
    {
        $this->rowset = $rowset;
        return $this;
    }

    /**
     * Fetch rowset from data collection
     *
     * @return array        The rows collection
     */
    public function getRowset()
    {
        $rowset = $this->rowset;
        if (!is_array($rowset)) {
            $rowset = array();
        }
        return $rowset;
    }

    /**
     * Set row to data collection
     *
     * @param mixed $row    The row collection
     * @return OSDN_Response
     */
    public function setRow($row)
    {
        $this->setRowset(array($row));
        return $this;
    }

    /**
     * Fetch row from data collection
     *
     * @return mixed
     */
    public function getRow()
    {
        $row = array();
        $rowset = $this->getRowset();

        if (!empty($rowset[0])) {
            $row = $rowset[0];
        }

        return $row;
    }

    /**
     * Dump statuses to array
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'status'    => $this->getStatusCollection()->toArray(),
            'data'      => $this->getDataCollection()->toArray()
        );
    }
}