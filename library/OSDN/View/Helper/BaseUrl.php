<?php

/**
 * Get base url
 *
 * @category OSDN
 * @package OSDN_View
 * @subpackage OSDN_View_Helper
 */
class OSDN_View_Helper_BaseUrl
{
    
    /**
     * @var Zend_View
     */
    public $view;

    public function baseUrl($allowDomainName = false)
    {
        $domain = '';
        if ($allowDomainName) {
            $protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
            $domain = $protocol . '://' . $_SERVER['HTTP_HOST'];
        }
        
        return $domain . Zend_Controller_Front::getInstance()->getBaseUrl();
    }
    
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
}