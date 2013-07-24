<?php


abstract class OSDN_Script_Abstract
{
    protected $_basePath;
    
    protected $_xmlUrl;
    
    public function __construct()
    {
    	$config = Zend_Registry::get('config');
        $this->_xmlUrl = LAYOUT_DIR . '/jsCss.xml';
        $this->_basePath = ROOT_DIR. '/html';
    }
	
    public function getBasePath()
    {
        return $this->_basePath;
    }
    
    protected function setBasePath($basePath)
    {
        $this->_basePath = $basePath;
    }
    
    protected function _insertParameters(&$body, $params)
    {
        foreach ($params as $k => $v) {
            $body = str_replace($k, $v, $body);
        }
    }
    
    protected function _concatFilesToOne($params = array(), $fileType = 'script')
    {
        $xmlConfig = new Zend_Config_Xml($this->_xmlUrl);
        $cfg = $xmlConfig->toArray();
        $data = $cfg['data'];
        $result = array();
        
        $siteAddress = Zend_Registry::get('config')->site->address;
        
        $companyName = Zend_Registry::get('config')->company->name;
        
        session_write_close();
        $opts = array(
		    'http' => array(
		        'method' => 'GET',
		        'header' => 'Cookie: ' . session_name() . '=' . session_id()
		    )
		);
        
        foreach ((array)$data[$fileType] as $el) {
            if (is_array($el) && (isset($el['src']) || isset($el['href']))) {
                if (isset($el['debug']) && OSDN_DEBUG != (boolean)($el['debug'] === 'true')) {
                    continue;
                }
                
                if (isset($el['project']) && strcasecmp($el['project'], $companyName) != 0) {
                    continue;
                }
                
                $src = isset($el['src']) ? $el['src'] : $el['href'];
                $last = strrchr($src, '.');
                
                if (0 === strpos($src, "http://")) {
                    $url = $src;
                    $separator = -1 == strpos($url, '?') ? '&' : '?';
                    $url .= $separator . session_name() . '=' . session_id();
                } elseif (!$last || $last != '.js') {
                    $url = $siteAddress . $src;
                } else {
                    $url = $this->getBasePath() . $src;
                }

                if (isset($el['cache']) && "false" == $el['cache']) {
                	$separator = -1 == strpos($url, '?') ? '&' : '?';
                    $url .= $separator . '_dc=' . uniqid();
                }
                
                $context = null;
                $context = stream_context_create($opts);
                $content = file_get_contents($url, 0, $context);
                
//                $content = file_get_contents($url);
            } else {
                $this->_insertParameters($el, $params);
                $content = $el;
            }
            $result[] = $content;
        }
        
        session_start();
        
        return join("\r\n\r\n", $result);
    }
    
    public function _generateHtmlSC($params = array(), $fileType = 'script')
    {
        $xmlConfig = new Zend_Config_Xml($this->_xmlUrl);
        $cfg = $xmlConfig->toArray();
        $data = $cfg['data'];
        
        $result = array();
        
        $companyName = Zend_Registry::get('config')->company->name;
        
        foreach ((array)$data[$fileType] as $el) {
            if (is_array($el) && (isset($el['src']) || isset($el['href']))) {
                if (isset($el['debug']) && OSDN_DEBUG != (boolean)($el['debug'] === 'true')) {
                    continue;
                }
                if (isset($el['project']) && strcasecmp($el['project'], $companyName) != 0) {
                    continue;
                }
                $src = isset($el['src']) ? $el['src'] : $el['href'];
                $file = $this->getBasePath() . $src . '?' . filemtime(ROOT_DIR . $src); 
                if ($fileType == 'script') {
                    $content = '<script src="' . $file . '" type="text/javascript" ></script>';
                } else {
                    $content = '<link rel="stylesheet" href="' . $file . '" type="text/css" />';
                }
            } else {
                if ($fileType == 'script') {
                    $content = '<script type="text/javascript" >' . $el . '</script>';
                } else {
                    $content = '<style>' . $el . '</style>';
                }
            }
            $result[] = $content;
        }
        $res = join("\r\n", $result);
        $this->_insertParameters($res, $params);
        return $res;
    }
    
    abstract function get();
    
    abstract function generateHtml();
}