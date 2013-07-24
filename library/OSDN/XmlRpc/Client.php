<?php

/**
 * XML-RPC client
 * Allow logging
 */
class OSDN_XmlRpc_Client extends Zend_XmlRpc_Client
{
    protected $_format;

    protected static $_logDirectory;

    /**
     * @var Zend_Log
     */
    protected $_logger;

    protected $_responseSerializable = false;

    /**
     * @see Zend_XmlRpc_Client::call()
     */
    public function call($method, $params=array())
    {
        try {
            $result = parent::call($method, $params);
        } catch(Exception $e) {
            if (OSDN_DEBUG) {
                $this->_writeLog($e, $method, $params);
            }
            throw $e;
            $result = false;
        }

        return $result;
    }

    public function setResponseSerializable($flag)
    {
    	$this->_responseSerializable = $flag;
    }

    /**
     * @see Zend_XmlRpc_Client::doRequest()
     */
    public function doRequest($request, $response = null)
    {
    	if (is_null($response) && $this->_responseSerializable) {
    		$response = new OSDN_XmlRpc_Response_Http();
    		$response->setResponseSerializable(true);
    	}

    	$http = $this->getHttpClient();
    	$http->setParameterGet('serializable', 1);
    	return parent::doRequest($request, $response);
    }

    private function _writeLog(Exception $e, $method, $params)
    {
        if (is_null($this->_logger)) {
            $this->_logger = new Zend_Log();
            $writer = new Zend_Log_Writer_Stream(self::$_logDirectory . '/xmlrpc.log');

            $d = str_repeat('_', 25) . "\n";
            $delimeter = str_repeat('_', 100) . "\n\n";
            $this->_format = "\n"
                . "Code:%s\n"
                . "Message:%s\n" . $d
                . "Last request:\n'%s'\n" . $d
                . "Last response:'%s'\n" . $delimeter;

            $this->_logger->addWriter($writer);
        }

        ob_start();
        echo 'Method: "' . $method . "\"\n";
        echo 'Arguments: ' . "\n";
        var_dump($params);

        echo "\nTrace: " . $e;
        echo "\n";
        $request = ob_get_clean();

        $lastResponse = $this->_httpClient->getLastResponse();
        $response = "";
        if ($lastResponse instanceof Zend_Http_Response) {
            $response = $lastResponse->getBody();
        }

        $this->_logger->emerg(sprintf($this->_format, $e->getCode(), $e->getMessage(), $request, $response));
    }

    public static function setLogDirectory($directory)
    {
        self::$_logDirectory = rtrim($directory, '/');
    }
}