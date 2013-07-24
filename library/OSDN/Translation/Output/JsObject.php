<?php

/**
 * Retrieve the translation and put into output format
 *
 * @category OSDN
 * @package OSDN_Translation
 */
class OSDN_Translation_Output_JsObject
{
	/**
	 * @var Zend_Translate
	 */
	protected $_translation;
	
	protected $_className;
	
    public function __construct($className)
    {
    	$this->_translation = OSDN_Translation::getInstance();
    	$this->_className = $className;
    }
    
    public function parse()
    {
        $msgs = $this->_translation->getMessages();
        $storage = array();
        if (empty($msgs) || !is_array($msgs)) {
            return "";
        }
        
        foreach ($msgs as $key => $value) {
            $key = str_replace(array("\r", "\n"), '', nl2br($key));
            $value = str_replace(array("\r", "\n"), '', nl2br($value));
            $storage[] = '"' . addslashes($key) . '":"' . addslashes($value) . '"';
        }
        return join(',', $storage);
    }
    
    public function __toString()
    {
        return $this->_className . ' = {' . $this->parse() . '};';
    }
}