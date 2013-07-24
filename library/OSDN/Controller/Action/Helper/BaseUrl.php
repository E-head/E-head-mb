<?php

/**
 * Get base url
 *
 * @category OSDN
 * @package OSDN_View
 * @subpackage OSDN_Controller_Action_Helper
 */
class OSDN_Controller_Action_Helper_BaseUrl extends Zend_Controller_Action_Helper_Abstract
{
    public function baseUrl($allowDomainName = false)
    {
        $domain = '';
        if ($allowDomainName) {
            $protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
            $domain = $protocol . '://' . $_SERVER['HTTP_HOST'];
        }
        
        return $domain . Zend_Controller_Front::getInstance()->getBaseUrl();
    }
    
    public function direct($allowDomainName = false)
    {
        return $this->baseUrl($allowDomainName);
    }
}