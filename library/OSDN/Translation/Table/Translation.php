<?php

class OSDN_Translation_Table_Translation extends OSDN_Db_Table_Abstract
{
    const NEW_WORD = 0;
    
    const SYNCHRONIZED_WORD = 1;
    
    const TRANSLATED_WORD = 2;
    
    const OLD_WORD = 3;
    
    protected $_name = 'translation';
    
    protected $_primary = 'id';
    
    protected $_tableLanguage;
    
    protected $_clearPkOnInsert = false;
    
    public function init()
    {
        $this->_tableLanguage = $this->_prefix . 'languages';
    }
    
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
            md5($a['caption'] . $a['translation'] . $a['language_id'] . $a['status'])
            ===
            md5($b['caption'] . $b['translation'] . $b['language_id'] . $b['status']);
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
        if (!$this->getAdapter()->query("TRUNCATE TABLE `{$this->getTableName()}`")) {
            return false;
        }
        try {
            $query = "INSERT INTO `" . $this->getTableName() . "` (`id`, `caption`, `translation`, `language_id`, `status`) VALUES ";
            $subQuery = '';
            foreach ($data as $row) {
//                $this->insert($row);
                if ($subQuery != '') {
                    $subQuery .= ', ';
                }
                $row['caption'] = addslashes($row['caption']);
                $row['translation'] = addslashes($row['translation']);
                $row['language_id'] = addslashes($row['language_id']);
                $subQuery .= "('{$row['id']}', '{$row['caption']}', '{$row['translation']}', '{$row['language_id']}', '{$row['status']}')";
                if (strlen ($subQuery) > 1000) {
                    // echo $query . $subQuery . '<br>';
                    if (!$this->getAdapter()->query($query . $subQuery)) {
                        $this->getAdapter()->rollBack();
                        return false;
                    }
                    $subQuery = '';
                }
            }
            if ($subQuery != '') {
                if (!$this->getAdapter()->query($query . $subQuery)) {
                    return false;
                }
            }
        } catch ( Exception $e) {
            return false;
        }
        return true;
    }
    
    /**
     * return translations for set locale
     *
     * @param string $locale
     *
     * @return array
     */
    public function getTranslationByLocale($locale)
    {
        $select = $this->_getSelectStatement();
            
        if (!is_null($locale)) {
            $select->where('abbreviation = ?', $locale);
        }
        
        $select->where('status <= ?', self::TRANSLATED_WORD);
        
        try {
            $rows = $select->query()->fetchAll();
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return array();
        }
        return $rows;
    }
    
    /**
     * return new translations
     *
     * @return array
     */
    public function getNewTranslations()
    {
        try {
            $rows = $this->fetchAll(array('status = ? or status = "' . self::TRANSLATED_WORD . '"' => self::NEW_WORD))->toArray();
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return false;
        }
        return $rows;
    }
    
    /**
     * return translations depend on set parameters
     *
     * @param array $options
     *
     * @return array
     */
    public function getTranslation(array $options)
    {
        $select = $this->_getSelectStatement(false);
        
        $fields = array(
            'translation.translation'   => 'translation',
            'translation.caption'       => 'alias',
            'languages.id'              => 'caption'
        );
        
        $filter = new OSDN_Db_Plugin_Select($this, $select, $fields);
        $filter->parse($options);

        try {
            $rows = $select->query()->fetchAll();
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return false;
        }
        return $rows;
    }
    
    /**
     * Get the count of translations
     *
     * @param array $filters
     * @return int|false
     */
    public function getTranslationCount(array $options)
    {
        $select = $this->_getSelectStatement(true);
        
        $fields = array(
            'translation.translation'   => 'translation',
            'translation.caption'       => 'alias',
            'languages.id'              => 'caption'
        );
        
        $filter = new OSDN_Db_Plugin_Select($this, $select, $fields);
        $filter->parse($options);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        
        try {
            $count = $select->query()->fetchColumn();
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return false;
        }
        return $count;
    }
    
    /**
     * Prepare select statement
     *
     * @param boolean $countOnly
     * @return Zend_Db_Select
     */
    protected function _getSelectStatement($countOnly = false)
    {
        $select = $this->getAdapter()->select();
        $fromLanguage = null;
        if (true === $countOnly) {
            $fromTranslation = array('count' => 'count(*)');
        } else {
            $fromTranslation = array('alias' => 'caption', 'id', 'translation');
            $fromLanguage = array('caption');
        }
        
        $select->from(array('translation' => $this->getTableName()), $fromTranslation);
        $select->join(
            array('languages' => $this->_tableLanguage),
            'translation.language_id = languages.id',
            $fromLanguage
        );
        return $select;
    }
    
    /**
     * Add new translation
     *
     * @param string $caption
     * @param string $translation
     * @param int $languageId
     * @return int|false
     */
    public function addTranslation($caption, $translation, $languageId)
    {
    	return $this->insert(array(
        	'caption'      => $caption,
        	'translation'  => $translation,
        	'language_id'  => $languageId
        ));
    }
}
