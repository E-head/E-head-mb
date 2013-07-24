<?php

class OSDN_Db_Adapter_Pdo_Mysql extends Zend_Db_Adapter_Pdo_Mysql
{
    /**
     * Contain last inserted id
     *
     * @var int
     */
    protected $_lastInsertId;
    
    /**
     * @see parent::nextSequenceId()
     */
    public function nextSequenceId($sequenceName)
    {
        if ($sequenceName == 'md5') {
            $this->_lastInsertId = md5(uniqid(mt_rand(), true)); 
            return $this->_lastInsertId;
        }
        
        return null;
    }
    
    /**
     * Gets the last ID generated automatically by an IDENTITY/AUTOINCREMENT column.
     *
     * On RDBMS brands that don't support sequences, $tableName and $primaryKey
     * are ignored.
     *
     * @param string $tableName   OPTIONAL Name of table.
     * @param string $primaryKey  OPTIONAL Name of primary key column.
     * @return string
     */
    public function lastInsertId($tableName = null, $primaryKey = null)
    {
        if (isset($this->_lastInsertId)) {
            return $this->_lastInsertId;
        }
        
        return parent::lastInsertId($tableName, $primaryKey);
    }
    
	/**
     * Creates and returns a new Zend_Db_Select object for this adapter.
     *
     * @return Zend_Db_Select
     */
    public function select()
    {
        return new OSDN_Db_Select($this);
    }
}