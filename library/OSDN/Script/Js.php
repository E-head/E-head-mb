<?php

class OSDN_Script_Js extends OSDN_Script_Abstract
{
    public function get($params = array())
    {
        return $this->_concatFilesToOne($params, 'script');
    }
    
    public function generateHtml($params = array(), $basePath = null)
    {
    	if(!is_null($basePath)){
    		$this->setBasePath($basePath);
    	}
        return $this->_generateHtmlSC($params, 'script');
    }
}