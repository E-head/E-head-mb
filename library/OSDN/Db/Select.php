<?php
/*
 * Class for SQL SELECT generation and results.
 * 
 * @category	OSDN
 * @package		OSDN_Db
 * @version 	$Id: $
 */
class OSDN_Db_Select extends Zend_Db_Select 
{
	const SQL_CALC_FOUND_ROWS = 'sqlCalcFoundRows';
	
	/**
     * Class constructor
     *
     * @param Zend_Db_Adapter_Abstract $adapter
     */
    public function __construct(Zend_Db_Adapter_Abstract $adapter)
    {
    	self::$_partsInit = array_merge(array(
    		self::SQL_CALC_FOUND_ROWS => false
    	), self::$_partsInit);
    	
    	parent::__construct($adapter);
    }
    
    /**
     * Makes the query SELECT SQL_CALC_FOUND_ROWS.
     *
     * @param bool $flag Whether or not the SELECT is calculatable (default true).
     * @return OSDN_Db_Select This OSDN_Db_Select object.
     */
    public function setSqlCalcFoundRows($flag = true)
    {
    	$this->_parts[self::SQL_CALC_FOUND_ROWS] = (boolean) $flag;
        return $this;
    }
    
    /**
     * Render SQL_CALC_FOUND_ROWS clause
     *
     * @param string   $sql SQL query
     * @return string
     */
    protected function _renderSqlCalcFoundRows($sql)
    {
        if (true === $this->_parts[self::SQL_CALC_FOUND_ROWS]) {
            $sql .= ' SQL_CALC_FOUND_ROWS';
        }

        return $sql;
    }
}