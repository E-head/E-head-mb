<?php

/**
 * Class for SQL table interface
 *
 * @category        OSDN
 * @package         OSDN_Db
 * @subpackage      OSDN_Db_Table
 * @version         $Id: Abstract.php 17666 2010-03-19 15:14:31Z yaroslav $
 */
abstract class OSDN_Db_Table_Abstract extends Zend_Db_Table_Abstract
{
    /**
     * Define the logic for new values in the primary key.
     * May be a string, boolean true, or boolean false.
     *
     * @var mixed
     */
    protected $_sequence = null;

    /**
     * The table prefix
     *
     * @var string
     */
    protected $_prefix = "";

    /**
     * Clear primary key on insert operation
     *
     * @var boolean
     */
    protected $_clearPkOnInsert = true;

    /**
     * Clear primary key on update operation
     *
     * @var boolean
     */
    protected $_clearPkOnUpdate = true;

    /**
     * Contain nullable fields
     * which will be removed on insert or set "NULL" in SQL query
     *
     * @var array
     */
    protected $_nullableFields = array();

    /**
     * The default table prefix using for each OSDN_Db_Table_Abstract object
     *
     * @var string
     */
    protected static $_defaultPrefix = "";

    /**
     * The default talbe sequense
     *
     * @var mixed
     */
    protected static $_defaultSequence = null;

    /**
     * Constructor
     *
     * @see Zend_Db_Table_Abstract::__construct() about params configuration
     *
     * @param array $config
     */
    public function __construct($config = array())
    {
        if (empty($this->_prefix) && !empty(self::$_defaultPrefix)) {
            $this->_prefix = self::getDefaultPrefix();
        }

        if (is_null($this->_sequence) && !is_null(self::getDefaultSequence())) {
        	$this->_sequence = self::getDefaultSequence();
        }

        parent::__construct($config);

    }

    /**
     * Initialize table name with prefix if last is not empty
     *
     */
    protected function _setupTableName()
    {
        parent::_setupTableName();
        if (!empty($this->_prefix)) {
            $this->_name = (string) $this->_prefix . $this->_name;
        }
    }

    /**
     * Retrieve the table prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->_prefix;
    }

    /**
     * Set the default prefix for all OSDN_Db_Table objects
     *
     * @param string $prefix
     * @return void
     */
    public static function setDefaultPrefix($prefix)
    {
        self::$_defaultPrefix = $prefix;
    }

    /**
     * Retrieve default table prefix
     *
     * @return string
     */
    public static function getDefaultPrefix()
    {
        return self::$_defaultPrefix;
    }

    /**
     * Set the default sequense for all OSDN_Db_Table objects
     * Sequense will be used only in insert operations
     * when response return from db object
     *
     * true allow use the standart db id
     * md5 for use char 32 length
     *
     * @param mixed $sequense
     */
    public static function setDefaultSequence($sequence)
    {
    	self::$_defaultSequence = $sequence;
    }

    /**
     * Retrieve the default sequense
     *
     * @return string|bool
     */
    public static function getDefaultSequence()
    {
        return self::$_defaultSequence;
    }

    /**
     * Insert data into table
     *
     * All fields with values equals to null
     * or not present in column structure will be unset
     * If primary key is present in data and pk is autoincrement then drip pk
     *
     * @param array $data
     * @return  int|false         SQL last inserted id
     */
    public function insert(array $data)
    {
        foreach ($data as $key => $value) {
            if (is_null($value) || !$this->hasColumn($key)) {
                unset($data[$key]);
                continue;
            }

            if (in_array($key, $this->_nullableFields) && empty($data[$key])) {
                unset($data[$key]);
                continue;
            }

            // if primary key then not needed and the sequense is not user defined unset it
            if (
                true === $this->_clearPkOnInsert &&
                true === $this->_metadata[$key]['PRIMARY'] &&
                true === $this->_metadata[$key]['IDENTITY'] &&
                !is_string($this->_sequence)
            ) {
                unset($data[$key]);
                continue;
            }
        }

        try {
            return parent::insert($data);
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return false;
        }
    }

    /**
     * Update existings rows
     *
     * @param array $data         Columns-value pairs
     * @param string|array $where An SQL WHERE clause, or an array of SQL WHERE clauses.
     * @return      int           Number of rows updated
     *               false         If some exceptions throws
     */
    public function update(array $data, $where)
    {
        foreach ($data as $key => $value) {
            if (
            	is_null($value) || !$this->hasColumn($key) || (
            	true === $this->_clearPkOnInsert &&
                true === $this->_metadata[$key]['PRIMARY'] &&
                true === $this->_metadata[$key]['IDENTITY']
            )) {
                unset($data[$key]);
                continue;
            }

            if (in_array($key, $this->_nullableFields) && empty($data[$key])) {
                $data[$key] = new Zend_Db_Expr('NULL');
                continue;
            }

        }

        try {
            return parent::update($data, $where);
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return false;
        }
    }

    /**
     * Deletes existing rows.
     *
     * @param  array|string $where SQL WHERE clause(s).
     * @return int          The number of rows deleted.
     */
    public function delete($where)
    {
        try {
            return parent::delete($where);
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }

            return false;
        }
    }

    /**
     * Update existing rows
     *
     * @param array $data       Columns-value pairs
     * @param array $clause     An array of SQL WHERE clauses
     *
     * <pre>For example: </pre>
     * <code>
     *    $data = array('datafield' => 1);
     *    $clause = array('clausefield = ?' => 2);
     *    $this->updateQuote($data, $clause);
     * </code>
     *
     * @see update()
     */
    public function updateQuote(array $data, array $clause)
    {
        $preparedClause = array();
        foreach ($clause as $cond => $value) {
            if (is_int($cond) && $value instanceof Zend_Db_Expr) {
                $preparedClause[] = $value;
                continue;
            }
            $preparedClause[] = $this->_db->quoteInto($cond, $value);
        }
        return $this->update($data, $preparedClause);
    }

    /**
     * Delete existing rows
     *
     * @param array $clause    An array of SQL WHERE clauses
     *
     * <pre>For example: </pre><code>
     *      $this->deleteQuote(array(
     *          'testfield = ?' => 1
     *      ));
     * </code>
     *
     * @return affectedRows | false if error
     */
    public function deleteQuote(array $clause)
    {
        $preparedClause = array();
        foreach ($clause as $cond => $value) {
            $preparedClause[] = $this->_db->quoteInto($cond, $value);
        }

        return $this->delete($preparedClause);
    }

    /**
     * Delete records by primary key
     *
     * @param mixed $pkValue    The id | array of primary keys
     * @return int number of row deleted
     */
    public function deleteByPk($pkValue)
    {
        $this->_setupPrimaryKey();
    	$keyNames = array_values((array) $this->_primary);
        if (count($keyNames) == 0) {
            throw new OSDN_Db_Exception('Primary key is not defined');
        }

        if (count($keyNames) > 1) {
            throw new OSDN_Db_Exception('Too many primary key are defined');
        }

        reset($keyNames);
        $key = current($keyNames);

        $collection = $this->_preparePkCollection($pkValue);
        if (0 === count($collection)) {
            return 0;
        }

        $clause = $this->_db->quoteInto("`$key` IN(?)", $collection);
        return $this->delete($clause);
    }

    /**
     * Update records by primary key
     *
     * @param array $data  Column-value pairs.
     * <code>array(
     *      'count'  => 1,
     *      'date'   => new Zend_Db_Expr('NOW()')
     * )</code>
     * @param mixed $pkValue
     * $return int  number of affected rows
     */
    public function updateByPk(array $data, $pkValue)
    {
        $this->_setupPrimaryKey();
    	$keyNames = array_values((array) $this->_primary);
        if (count($keyNames) == 0) {
            throw new OSDN_Db_Exception('Primary key is not defined');
        }

        if (count($keyNames) > 1) {
            throw new OSDN_Db_Exception('Too many primary key are defined');
        }

        reset($keyNames);
        $key = current($keyNames);

        $collection = $this->_preparePkCollection($pkValue);
        if (0 === count($collection)) {
        	return 0;
        }

        $clause = $this->_db->quoteInto("`$key` IN(?)", $collection);
    	return $this->update($data, $clause);
    }

    /**
     * Prepare the collection values
     * @param mixed $pkValue
     * @return array        The collection of primary keys
     */
    private function _preparePkCollection($pkValue)
    {
        if (true === $this->_sequence) {
            $validate = new OSDN_Validate_Id();
        } else {
            $validate = new OSDN_Validate_SecureId();
        }

        if (!is_array($pkValue)) {
            $pkValue = (array) $pkValue;
        }

        $collection = array();
        foreach ($pkValue as $v) {
            if ($validate->isValid($v)) {
                array_push($collection, $v);
            }
        }
        return $collection;
    }

    /**
     * Return one row by primary key
     *
     * @param int $primaryId
     * @return Zend_Db_Table_Row
     *          | null if empty result
     *          | false if error
     *
     * @throws Zend_Db_Exception
     */
    public function findOne($primaryId)
    {
    	try {
	        $data = parent::find($primaryId);
	        if ($data->count() > 0) {
	            return $data->current();
	        }
    	} catch (Exception $e) {
    		if (OSDN_DEBUG) {
    			throw $e;
    		}
    		return false;
    	}
        return null;
    }

    /**
     * Retrieve "any" column by primary key
     *
     * Here we using the db query for retrieveing
     * because sometimes we wants to add in _fetch method
     * some omit columns
     *
     * @return string|false if column is not found in row model definition
     */
    public function findColumn($primaryId, $field)
    {
        if (!$this->hasColumn($field)) {
            return false;
        }

        $select = $this->_db->select()
            ->from($this->getTableName(), $field)
            ->where('id = ?', $primaryId);

        return $select->query()->fetchColumn();
    }

    /**
     * Retrieve the table name
     *
     * @return String
     */
    public function getTableName()
    {
        return $this->_name;
    }

    /**
     * Check if column is present in table metadata
     *
     * @param string $name
     * @return boolean
     */
    public function hasColumn($name)
    {
        return in_array($name, $this->_getCols());
    }

    /**
     * Build the order query
     *
     * @param string|array $field   table field
     * @param string $orderKeys     ASC|DESC
     * @return array
     */
    public function createOrder($field, $dir = null)
    {
        if (!is_array($field)) {
            $field = array($field => $dir);
        }
        $orderCollection = array();
        foreach ($field as $fieldName => $key) {
            if (!empty($fieldName) && $this->hasColumn($fieldName)) {
                $key = strtoupper($key);
                if (!in_array($key, array('ASC', 'DESC'))) {
                    $key = 'ASC';
                }
                $orderCollection[] = $fieldName . ' ' . $key;
            }
        }
        return $orderCollection;
    }

    /**
     * Retrieve the count rows from table by some where condition.
     * If condition are empty then return count of all rows
     *
     * <pre>For example: </pre><code>
     *      $this->count(array(
     *          'testfield = ?' => 1,
     *          'anotherfield = ?' => new Zend_Db_Expr('NOW()'))
     *      ));
     *      $this->count($this->getAdapter()->quoteInto('testfield = ?', 1));
     * </code>
     *
     * @param string|array  $where
     * @param int           $limit
     * @return int
     * @throws Zend_Db_Exception
     */
    public function count($where = null, $limit = null)
    {
        $select = $this->select()->from($this->getTableName(),
            array('count' => new Zend_Db_Expr('COUNT(*)')));

        if (is_array($where)) {
            foreach ($where as $condition => $value) {
                $select->where($condition, $value);
            }
        } elseif (null !== $where) {
            $select->where($where);
        }

        if (!is_null($limit)) {
            $select->limit($limit);
        }

        try {
            return (int) $select->query()->fetchColumn();
        } catch (Exception $e) {
        	if (OSDN_DEBUG) {
        		throw $e;
        	}
        	return false;
        }
    }

    public function fetchAllColumns($where = null, $order = null, array $columns)
    {
        if (!($where instanceof Zend_Db_Table_Select)) {
            $select = $this->select()->from($this->getTableName());

            $select->reset();

            if ($where !== null) {
                $this->_where($select, $where);
            }

            if ($order !== null) {
                $this->_order($select, $order);
            }
        } else {
            $select = $where;
        }

        $select->from($this->getTableName(), array());
        $select->columns($columns);

        return $this->fetchAll($select);
    }

    public function fetchAllColumn($where = null, $order = null, $column)
    {
        if (!($where instanceof Zend_Db_Table_Select)) {
            $select = $this->select()->from($this->getTableName());

            $select->reset();

            if ($where !== null) {
                $this->_where($select, $where);
            }

            if ($order !== null) {
                $this->_order($select, $order);
            }
        } else {
            $select = $where;
        }

        $select->from($this->getTableName(), array());
        $select->columns($column);

        $stmt = $this->_db->query($select);
        return $stmt->fetchAll(Zend_Db::FETCH_COLUMN);
    }

    /**
     * Clone the row by pkId and return pkId of new row
     *
     * @param int $pkId
     * @param array $exclude
     * @return  int|false         SQL last inserted id
     */
    public function cloneRow($pkId, array $exclude = array())
    {
        $this->_setupPrimaryKey();

        $primary = (array) $this->_primary;

        /**
         * @todo 	Is it correct to change identity to int?
         *			Because when sequense is not autoincrement it will work uncorrect!!!
         */
        $pkIdentity = $primary[(int)$this->_identity];

        $row = $this->findOne($pkId)->toArray();
        if(isset($row[$pkIdentity])){
            unset($row[$pkIdentity]);
        }

        foreach ($exclude as $k => $v){
            if(isset($row[$v])){
                unset($row[$v]);
            }
        }

        return $this->insert($row);
    }
}