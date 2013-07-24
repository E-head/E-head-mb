<?php

/**
 * OSDN_Configuration_Abstract
 *
 * @category OSDN
 * @package OSDN_Configuration
 *
 */
abstract class OSDN_Configuration_Abstract
{
    /**
     * @var OSDN_Configuration_Table_Configuration
     */
    protected $_table;
    
    protected $_accountId = null;
    
    protected $_languageId = null;
        
    protected $_allowedParams = array();
    
    public function __construct($allowedParams = null, $accountId = null, $languageId = null)
    {
        if (isset($languageId)) {
            $this->_languageId = $languageId;
        } else {
            $this->_languageId = OSDN_Language::getDefaultLocaleId();
        }
        if (isset($accountId)) {
            $this->_accountId = $accountId;
        } else {
            $this->_accountId = OSDN_Accounts_Prototype::getId();
        }
        if (isset($allowedParams)) {
            $this->_allowedParams = $allowedParams;
        }
        $this->_table = new OSDN_Configuration_Table_Configuration();
    }
    
    /**
     * Save system settings
     *
     * @param array  $params
     * @return OSDN_Response
     */
    public function saveSystemSettings($params)
    {
        $response = new OSDN_Response();
        
        $flag = true;
        
        foreach ($params as $k => $v) {
            if (!isset($this->_allowedParams[$k]))
            {
                continue;
            }
            
            $data = array('param' => $k, 'value' => $v);
            if ($this->_allowedParams[$k]) {
                $data['language_id'] = $this->_languageId;
            }
            
            if (false === $this->getSystemParam($k)) {
                $res = $this->_table->insert($data);
            } else {
                $where = array();
                $where[] = "param = '$k'";
                $where[] = "account_id is null";
                if ($this->_allowedParams[$k]) {
                    $where[] = "language_id = '{$this->_languageId}'";
                } else {
                    $where[] = "language_id is null";
                }
                $res = $this->_table->update($data, join(" and ", $where));
            }
            if (false === $res) {
                $response->addStatus(new OSDN_Configuration_Status(OSDN_Configuration_Status::FAILURE));
                return $response;
            }
        }
        $response->addStatus(new OSDN_Configuration_Status(OSDN_Configuration_Status::OK));
        return $response;
    }
    
    /**
     * Save protfile settings
     *
     * @param array  $params
     * @return OSDN_Response
     */
    public function saveProfileSettings($params)
    {
        $response = new OSDN_Response();
        $flag = true;
        foreach ($params as $k => $v) {
            if (!isset($this->_allowedParams[$k]))
            {
                continue;
            }
            $data = array(
                'param'         => $k,
                'value'         => $v,
                'account_id'    => $this->_accountId
            );
            if ($this->_allowedParams[$k]) {
                $data['language_id'] = $this->_languageId;
            }
            
            if (false === $this->getProfileParam($k, false)) {
                $res = $this->_table->insert($data);
            } else {
                $res = $this->_table->update($data, " param = '$k' and account_id = '$this->_accountId' ");
            }
            if (false === $res) {
                $response->addStatus(new OSDN_Configuration_Status(OSDN_Configuration_Status::FAILURE));
                return $response;
            }
        }
        $response->addStatus(new OSDN_Configuration_Status(OSDN_Configuration_Status::OK));
        return $response;
    }
    
    /**
     * return all system settings
     *
     * @return OSDN_Response
	 * <code>
	 *  data - array of settings
	 * </code>
     */
    public function getProfileSettings()
    {
        $response = new OSDN_Response();
        
        $select = $this->_table->getAdapter()->select();
        $select->from($this->_table->getTableName());
        $select->where(" account_id = '$this->_accountId' ");
        
        try {
            $rowset = $select->query()->fetchAll();
        } catch (Exception $e) {
            $response->addStatus(new OSDN_Configuration_Status(OSDN_Configuration_Status::DATABASE_ERROR));
            return $response;
        }
        $result = array();
        if ($rowset) {
            foreach ($rowset as $v) {
                if (isset($this->_allowedParams[$v['param']])
                    && (($this->_allowedParams[$v['param']] && $v['language_id'] == $this->_languageId)
                    || (!$this->_allowedParams[$v['param']] && !$v['language_id']) )) {
                        
                    $result[$v['param']] = $v['value'];
                }
            }
        }
        $res = $this->getSystemSettings();
        if ($res->isError()) {
            return $res;
        }
        foreach ($res->data as $k => $v) {
            if (!isset($result[$k])) {
                $result[$k] = $v;
            }
        }
        $response->data = $result;
        $response->addStatus(new OSDN_Configuration_Status(OSDN_Configuration_Status::OK));
        return $response;
    }
    
    /**
     * return all system settings
     *
     * @return OSDN_Response
     * <code>
     *  data - array of settings
     * </code>
     */
    public function getSystemSettings()
    {
        $response = new OSDN_Response();
        
        $select = $this->_table->getAdapter()->select();
        $select->from($this->_table->getTableName());
        $select->where(" account_id is null ");
        
        try{
            $rowset = $select->query()->fetchAll();
        }
        catch (Exception $e) {
            $response->addStatus(new OSDN_Configuration_Status(OSDN_Configuration_Status::DATABASE_ERROR));
            return $response;
        }
        $result = array();
        if ($rowset) {
            foreach ($rowset as $v) {
                if (isset($this->_allowedParams[$v['param']])
                    && (($this->_allowedParams[$v['param']] && $v['language_id'] == $this->_languageId)
                    || (!$this->_allowedParams[$v['param']] && !$v['language_id']) )) {
                        
                        
                        
                    $result[$v['param']] = $v['value'];
                }
            }
        }
        $response->data = $result;
        $response->addStatus(new OSDN_Configuration_Status(OSDN_Configuration_Status::OK));
        return $response;
    }
    
    /**
     * return system setting
     *
	 * @param string $name
     * @return string | false
     */
    public function getSystemParam($name, array $options = array())
    {
        $where[] = "param = '$name'";
        $where[] = "account_id is null";
        if ($this->_allowedParams[$name]) {
            $where[] = "language_id = '{$this->_languageId}'";
        } else {
            $where[] = "language_id is null";
        }
        $select = $this->_table->getAdapter()->select();
        $select->from($this->_table->getTableName());
        $select->where(join(" and ", $where));
        try{
            $rowset = $select->query()->fetchAll();
        }
        catch (Exception $e) {
            return false;
        }
        if (count($rowset) != 1) {
            return false;
        }
        $param = $rowset[0]['value'];
        
        if(!empty($options)){
            $keywords = array();
            $values = array();
            foreach ($options as $k => $v){
                $keywords[] = $k;
                $values[] = $v;
            }
            return str_replace($keywords, $values, $param);
        }
        return $param;
    }

	/**
     * return profile setting
     *
	 * @param string $name
     * @return string | false
     */
    public function getProfileParam($name, $system = true)
    {
        $response = new OSDN_Response();
        $where[] = "param = '$name'";
        $where[] = "account_id = '{$this->_accountId}'";
        if ($this->_allowedParams[$name]) {
            $where[] = "language_id = '{$this->_languageId}'";
        } else {
            $where[] = "language_id is null";
        }
        $select = $this->_table->getAdapter()->select();
        $select->from($this->_table->getTableName());
        $select->where(join(" and ", $where));
        try {
            $rowset = $select->query()->fetchAll();
        }
        catch (Exception $e) {
            return false;
        }
        if (count($rowset) != 1 && $system) {
            return $this->getSystemParam($name);
        }
        return isset($rowset[0]['value'])? $rowset[0]['value']: false;
    }
}