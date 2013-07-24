<?php

class OSDN_XmlRpc_Client_Proxy_Response
{
    /**
     * @var Zend_XmlRpc_Client
     */
    protected $_client;
    
    /**
     * The constructor
     * Initialize XML-RPC client
     * 
     * @param Zend_XmlRpc_Client $client
     * @return 
     */
    public function __construct(Zend_XmlRpc_Client $client)
    {
        $this->_client = $client;
    }

    /**
     * Send an XML-RPC request to the service (for a specific method)
     * 
     * @param struct $params    The array of args <code>
     *  array(
     *      method - method name
     *      args   - the method arguments
     *  );
     * </code>
     * @return mixed
     * @throws Exception
     */    
    public function request($params)
    {
        if (empty($params['method']) || !is_string($params['method'])) {
            throw new Exception('The method must be only string and valid method name');
        }
        
        if (empty($params['args']) || !is_array($params['args'])) {
            throw new Exception('The args must be an array.');
        }
        
        $method = $params['method'];
        $args = $params['args'];
        
        return $this->_client->call($method, $args);
    }
}