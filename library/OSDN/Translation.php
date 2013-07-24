<?php

/**
 * The simply wrapper for Zend_Translate
 * Can contain the instance of object
 *
 * @category OSDN
 * @package OSDN_Translation
 */
class OSDN_Translation
{
    /**
     * The OSDN_Translation instance
     * 
     * @var OSDN_Translation
     */
    protected static $_instance;

    /**
     * The default adapter class name
     */
    protected static $_defaultAdapterCls = 'OSDN_Translation_Adapter_Mysql';
    
    /**
     * Create the instance of class 
     * 
     * @return Zend_Translate
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = self::factory();
        }
        
        return self::$_instance;
    }
    
    
    
    /**
     * Simply factory object for generates the standard translation object
     *
     * @param  string              $adapter  Adapter to use
     * @param  array               $data     Translation source data for the adapter
     *                                       Depends on the Adapter
     * @param  string|Zend_Locale  $locale   OPTIONAL locale to use
     * @param  array               $options  OPTIONAL options for the adapter
     * 
     * @throws Zend_Translate_Exception
     * @return Zend_Translate
     */
    public static function factory($adapter = null, $data = null, $locale = null, array $options = array()) 
    {
        if (!is_null(self::$_instance)) {
            throw new OSDN_Exception('The instance is alrady created. ' .
                'Use the method OSDN_Translation::getInstance() for retrieve the object.');
            return;
        }
        
        if (is_null($adapter)) {
            $adapter = self::$_defaultAdapterCls;
        }
        
        if (is_null($locale)) {
        	$locale = OSDN_Language::getDefaultLocale();
        }
        
        try { 
            self::$_instance = new Zend_Translate($adapter, $data, $locale, $options);
        } catch (Exception $e) {
        	if (OSDN_DEBUG) {
        		throw $e;
        	}
        	return false;
        } 
        
        return self::$_instance;
    }
}