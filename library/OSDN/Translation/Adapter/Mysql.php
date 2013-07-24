<?php

/**
 * Simple mysql adapter for translation
 *
 * @category OSDN
 * @package  OSDN_Translation
 */
class OSDN_Translation_Adapter_Mysql extends Zend_Translate_Adapter
{
	/**
     * Load translation data
     *
     * @param  mixed              $data
     * @param  string|Zend_Locale $locale
     * @param  array              $options (optional)
     * @return void
     */
    public function _loadTranslationData($data, $locale, array $options = array())
    {
    	if (is_array($data)) {
    	    foreach ($data as $key => $translate) {
                if (!$this->isTranslated($key)) {
                    $this->_translate[$locale][$key] = $translate;
                }
            }
    	} else {
    	
	        $translationTable = new OSDN_Translation_Table_Translation();
	        $rowset = $translationTable->getTranslationByLocale($locale);
	        
	        if (!is_array($rowset)) {
	            throw new Zend_Translate_Exception("Error fetching data");
	        }
	        
	        $options = $options + $this->_options;
	        if (($options['clear'] == true) || !isset($this->_translate[$locale])) {
	            $this->_translate[$locale] = array();
	        }
	        
    	    foreach ($rowset as $row) {
	            $this->_translate[$locale][$row['alias']] = $row['translation'];
	        }
    	}
    	
        if (empty($this->_translate[$locale])) {
            $this->_translate[$locale] = true;
        }
            
    }
    
    /**
     * Translates the given string
     * returns the translation
     *
     * @see Zend_Locale
     * @param  string             $messageId Translation string
     * @param  string|Zend_Locale $locale    (optional) Locale/Language to use, identical with
     *                                       locale identifier, @see Zend_Locale for more information
     * @param string              $internal If internal matched with locale then will translated now
     * @return string
     */
    public function translate($messageId, $locale = null, $internal = null)
    {
    	if (!$this->isTranslated($messageId)) {
            $translation = '_' . $messageId;
            $translationObject = new OSDN_Translation_Data();
            $translationObject->addTranslation($messageId, $locale, $internal);
            $this->addTranslation(array($messageId => ""), $locale);
            return $translation;
    	}
    
    	$translation = parent::translate($messageId, $locale);
    	return "" == $translation ? ('_' . $messageId) : $translation;
    }
    
    /**
     * Returns the adapter name
     *
     * @return string
     */
    public function toString()
    {
        return 'Mysql';
    }
}