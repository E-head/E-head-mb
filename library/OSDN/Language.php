<?php

/**
 * Manipulate with system languages
 *
 * @category OSDN
 * @package OSDN_Language
 */
class OSDN_Language
{
	/**
	 * @var OSDN_Language_Table_Language
	 */
	protected $_tableLanguage;
	
	/**
	 * The default locale
	 *
	 * @var string
	 */
	protected static $_defaultLocale;
	
	/**
	 * The default locale id
	 *
	 * @var int
	 */
	protected static $_defaultLocaleId;
	
	/**
	 * The constructor
	 */
	public function __construct()
	{
		$this->_tableLanguage = new OSDN_Language_Table_Language();
	}

	/**
	 * The default namespace for Zend_Session_Namespace
	 *
	 * @var string
	 */
	protected static $_namespace = 'OSDN_Language';
	
    /**
     * Check whether two records are equal or not
     *
     * @param array $a
     * @param array $b  
     *
     * @return boolean
     */
    public function isRecordsEqual($a, $b) 
    {
        return $a && $b 
            && 
            md5($a['abbreviation'] . $a['caption'])
            === 
            md5($b['abbreviation'] . $b['caption']);
    }
    
    /**
     * Synchronize translations with passed data
     *
     * @param array $data
     *
     * @return boolean
     */
    public function synchronize($data) 
    {
    
        if ($this->_tableLanguage->delete(array()) === false) {
            return false;
        }
        foreach ($data as $row) {
            $res = $this->_tableLanguage->insert($row);
            if (!$res) {
                return false;
            }
        }
        return true;
    }
    
	/**
	 * Convert the id to abbreviation
	 *
	 * @param int $id
	 * @return string|false
	 */
	public function id2abbr($id)
    {
        $row = $this->_tableLanguage->findOne($id);
        if (!is_null($row)) {
        	return $row->abbreviation;
        }
        return false;
    }
    
    /**
     * Convert the abbreviation to id
     *
     * @param string $alias
     * @return int|false
     */
    public function abbr2id($alias)
    {
    	$tl = $this->_tableLanguage;
        $where = $tl->getAdapter()->quoteInto('abbreviation = ?', $alias);
        $row = $tl->fetchRow($where);
        if (!is_null($row)) {
            return $row->id;
        }
        return false;
    }
    
    /**
     * Set default locale
     * 
     * @param string $language  Locale en|nl|ru|ua...
     * @return void
     */
    public static function setDefaultLocale($locale, $overwrite = false)
    {
        $ns = new Zend_Session_Namespace(self::$_namespace);
        if (!$ns->locale || true === $overwrite) {
            $ns->locale = $locale;
            
            $language = new self();
            $ns->localeId = $language->abbr2id($locale);
        }
        
        self::$_defaultLocale = $ns->locale;
        Zend_Locale::setDefault(self::$_defaultLocale);
    }
    
    /**
     * Get default locale
     *
     * @return string
     */
    public static function getDefaultLocale()
    {
        if (!is_null(self::$_defaultLocale)) {
            return self::$_defaultLocale;
        }
        
        $ns = new Zend_Session_Namespace(self::$_namespace);
        self::$_defaultLocale = $ns->locale;
        return self::$_defaultLocale;
    }
    
    /**
     * Fetch default locale id
     * If locale is not present is session registry
     * The fetch locale and find locale id and also 
     * save in session for next retrieving 
     *
     * @return int
     */
    public static function getDefaultLocaleId()
    {
        if (!is_null(self::$_defaultLocaleId)) {
            return self::$_defaultLocaleId;
        }
        
        $ns = new Zend_Session_Namespace(self::$_namespace);
        if (!empty($ns->localeId)) {
            self::$_defaultLocaleId = $ns->localeId;
            return self::$_defaultLocaleId;
        }
        
        $locale = self::getDefaultLocale();
        $language = new self();
        $localeId = $language->abbr2id($locale);
        $ns->localeId = $localeId;
        self::$_defaultLocaleId = $localeId;
        return self::$_defaultLocaleId;
    }
    
    /**
     * Check if locale present in system
     *
     * @return bool
     */
    public function isAvailableLocale($locale)
    {
        return $this->abbr2id($locale) > 0;
    }
    
    /**
     * Get list of locales, present in system
     *
     * @return OSDN_Response
     * <code> array(
     *     'rows' => Zend_Db_Table_Rowset | null
     * )
     * </code>
     */
    public function getLocalesList()
    {
        $response = new OSDN_Response();
        $response->rows = $this->_tableLanguage->fetchAll();
        $response->addStatus(new OSDN_Language_Status(OSDN_Language_Status::OK));
        return $response; 
    }

}