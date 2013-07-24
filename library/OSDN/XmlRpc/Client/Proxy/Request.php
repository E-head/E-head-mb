<?php

/**
 * The namespace decorator enables object chaining to permit
 * calling XML-RPC namespaced functions like "foo.bar.baz()"
 * as "$remote->foo->bar->baz()".
 *
 * I'd like to extend this class from Zend_XmlRpc_Client_ServerProxy
 * but the client and namespace are private properties
 * @todo Try resolve this information in future
 * 
 * @category    OSDN
 * @package     OSDN_XmlRpc
 * @subpackage  Client
 * @version     $Id: $
 */
class OSDN_XmlRpc_Client_Proxy_Request
{
    /**
     * @var Zend_XmlRpc_Client
     */
    protected $_client = null;

    /**
     * @var string
     */
    protected $_namespace = 'proxy.request';


    /**
     * @var array of OSDN_XmlRpc_Client_Proxy_Request
     */
    protected $_cache = array();


    /**
     * Class constructor
     *
     * @param string             $namespace
     * @param Zend_XmlRpc_Client $client
     */
    public function __construct($client, $namespace = '')
    {
        $this->_namespace = $namespace;
        $this->_client    = $client;
    }


    /**
     * Get the next successive namespace
     *
     * @param string $name
     * @return OSDN_XmlRpc_Client_Proxy_Request
     */
    public function __get($namespace)
    {
        $namespace = ltrim("$this->_namespace.$namespace", '.');
        if (!isset($this->_cache[$namespace])) {
            $this->_cache[$namespace] = new $this($this->_client, $namespace);
        }
        return $this->_cache[$namespace];
    }


    /**
     * Call a method in this namespace.
     *
     * @param  string $methodN
     * @param  array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        $method = ltrim("$this->_namespace.$method", '.');
        $method = str_replace('proxy.request', "", $method);
        
        $proxyArgs = array(
            'method'    => $method,
            'args'      => $args
        );
        
        return $this->_client->call('proxy.request', array($proxyArgs));
    }
}