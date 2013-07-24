<?php

error_reporting(E_ALL | E_STRICT);

define('ROOT_DIR', realpath(dirname(__FILE__) . '/..'));
set_include_path(ROOT_DIR . '/library/');

require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->setFallbackAutoloader(true);

$config = array(
    'accept_schemes'    => 'digest',
    'realm'             => 'Please login for changing site configuration',
    'digest_domains'    => '/setup',
    'nonce_timeout'     => 3600
);

$adapter = new Zend_Auth_Adapter_Http($config);

$digestResolver = new Zend_Auth_Adapter_Http_Resolver_File();
$digestResolver->setFile(dirname(__FILE__) . '/_files/passwd.txt');

$adapter->setDigestResolver($digestResolver);
$adapter->setRequest(new Zend_Controller_Request_Http());
$adapter->setResponse(new Zend_Controller_Response_Http());

$auth = Zend_Auth::getInstance();
$auth->setStorage(new Zend_Auth_Storage_NonPersistent());
$result = $auth->authenticate($adapter);

if (true !== $result->isValid()) {
    $adapter->getResponse()->sendHeaders();
    echo join('<br/>', $result->getMessages());
    exit;
}

$filename = ROOT_DIR . "/config/config.xml";
$cacheDir = ROOT_DIR . "/cache";
$action = !empty($_GET['action']) ? trim($_GET['action']) : '';

switch ($action):
    case 'submit':
        try {
            $config = new Zend_Config(array('general' => $_POST));
            $writer = new Zend_Config_Writer_Xml();
            $writer->setFilename($filename);
            $writer->setConfig($config);
            $writer->write();
	        // Clear cache dir after config save
	        foreach((array)glob($cacheDir . "/*") as $fn) {
	            @unlink($fn);
	        }
	        echo Zend_Json::encode(array('success' => true));
	        break;
        } catch (Exception $e) {
           echo Zend_Json::encode(array('success' => false, 'error' => $e->getMessage()));
           break;
        }
    case 'load':
        try {
            $config = new Zend_Config_Xml($filename);
        } catch (Exception $e) {
           echo Zend_Json::encode(array('success' => false, 'data' => array(), 'error' => $e->getMessage()));
           break;
        }
        echo Zend_Json::encode(array('success' => true, 'data' => $config->general->toArray()));
        break;
    default:
    ?>
        <!DOCTYPE HTML PUBLIC  "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
        <html><head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" type="text/css" href="/js/Library/extjs/framework/resources/css/ext-all.css"/>
        <link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon" />
        <link rel="icon" href="/images/favicon.png" type="image/png" />
        </head><body bgcolor="#C3DAF9">
        <script src="/js/Library/extjs/framework/adapter/ext/ext-base.js"></script>
        <script src="/js/Library/extjs/framework/ext-all.js"></script>
        <script src="Form.js"></script>
        <script>
            Ext.BLANK_IMAGE_URL = '/js/Library/extjs/framework/resources/images/default/s.gif';
            Ext.onReady(function(){
                new Setup.Form({
                    timeZones: <?=Zend_Json::encode(timezone_identifiers_list()); ?>,
                    loadUrl: '/setup/?action=load',
                    saveUrl: '/setup/?action=submit',
                	width: 650,
                    border: true,
                    autoScroll: true,
                    style: {margin: '50px auto'},
                    renderTo: Ext.getBody()
                }).load();
            });
        </script></body></html>
<? endswitch; ?>