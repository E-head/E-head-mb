<?php

class OSDN_Accounts_Table_Accounts extends OSDN_Db_Table_Abstract
{
    protected $_name = 'accounts';
    
    /**
     * Defined unallowed fields
     * This fields will be removed from rowset on fetching
     *
     * @var array
     */
    protected $_unallowed = array(
        'password'
    );

    /**
     * return password for set account id
     *
     * @param  int $id
     * @return string
     */
    public function fetchPassword($id)
    {
        $select = $this->_db->select()
            ->from($this->_name, array('password'))
            ->where("id = ? ", $id);
        return $select->query()->fetchColumn(0);
    }
    
    /**
     * Support method for fetching rows.
     * Remove unallowed fields such as "password", etc.
     *
     * @param  Zend_Db_Table_Select $select  query options.
     * @return array An array containing the row results in FETCH_ASSOC mode.
     */
    protected function _fetch(Zend_Db_Table_Select $select)
    {
        $stmt = $this->_db->query($select);
        $data = array();
        while ($row = $stmt->fetch(Zend_Db::FETCH_ASSOC)) {
            $data[] = $this->_filterRow($row);
        }
        return $data;
    }
    
    /**
     * Filter unallowed fields from row
     *
     * @param array $row        The fetched row
     * @return array
     */
    protected function _filterRow($row)
    {
        foreach ($this->_unallowed as $field) {
            if (array_key_exists($field, $row)) {
                unset($row[$field]);
            }
        }
        return $row;
    }
    
    /**
     * Check if field is allowed
     *
     * @param string $field     The field name
     */
    public function isAllowedField($field)
    {
        return !in_array($field, $this->_unallowed);
    }
}