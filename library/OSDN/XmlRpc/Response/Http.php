<?php

class OSDN_XmlRpc_Response_Http extends Zend_XmlRpc_Response_Http
{
	protected $_serializable = false;
	
	/**
     * Retrieve the XMLRPC value for the return value
     *
     * @return Zend_XmlRpc_Value
     */
	protected function _getXmlRpcReturn()
	{
		$value = $this->isResponseSerializable() ? serialize($this->_return) : $this->_return;
		return Zend_XmlRpc_Value::getXmlRpcValue($value);
	}
	
	/**
     * Retrieve the return value
     *
     * @todo catch error from not serialized string
     *
     * @return mixed
     */
    public function getReturnValue()
    {
        return $this->isResponseSerializable() ? @unserialize($this->_return) : $this->_return;
    }
    
    public function isResponseSerializable()
    {
    	return $this->_serializable || (isset($_GET['serializable']) && 1 == $_GET['serializable']);
    }
    
    public function setResponseSerializable($flag)
    {
    	$this->_serializable = (boolean) $flag;
    	return $this;
    }
}