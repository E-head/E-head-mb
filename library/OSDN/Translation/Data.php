<?php

class OSDN_Translation_Data
{
    /**
     * @var OSDN_Translation_Table_Translation
     */
    protected $_translationTable;
    
    /**
     * The constructor
     *
     */
    public function __construct()
    {
        $this->_translationTable = new OSDN_Translation_Table_Translation();
    }
    
    public function getTranslation(array $params)
    {
        $response = new OSDN_Response();
        $rows = $this->_translationTable->getTranslation($params);
        $total = $this->_translationTable->getTranslationCount($params);
        
        $response->rows = $rows;
        $response->total = $total;
        $status = OSDN_Response_Status_Storage_Abstract::OK;
        $response->addStatus(new OSDN_Translation_Status($status));
        return $response;
    }
    
    public function updateTranslation($id, $value)
    {
        $result = $this->_translationTable->update(array(
                'translation'   => $value
            ), $this->_translationTable->getAdapter()->quoteInto('id = ?', $id)
        );
        
        return false !== $result;
    }
    
    public function getCaptionTranslation($alias, $languageId)
    {
        $alias = $this->_normalizeCaption($alias);
        $row = $this->_translationTable->fetchRow(array(
            'caption = ?'       => $alias,
            'language_id = ?'   => $languageId
        ));
        
        return $row? $row->toArray(): false;
    }
    
    public function makeTranslationWorkable($id)
    {
        $result = $this->_translationTable->update(array(
                'status'        => OSDN_Translation_Table_Translation::TRANSLATED_WORD,
                'last_used'     => date('Y-m-d H:i:s'),
            ), $this->_translationTable->getAdapter()->quoteInto('id = ?', $id)
        );
        return false !== $result;
    }
    
    /**
     * Add new translation
     *
     * @param string $alias
     * @param string $abbreviation
     * @return int|false
     */
    public function addTranslation($alias, $abbreviation, $internal = null)
    {
        $language = new OSDN_Language();
        $languageId = $language->abbr2id($abbreviation);
        if (false === $languageId) {
            return false;
        }
        
        $alias = $this->_normalizeCaption($alias);
        $translation = $this->getCaptionTranslation($alias, $languageId);
        
        if ($translation) {
            if (!$this->makeTranslationWorkable($translation['id'])) {
                return false;
            }
            return $translation['translation']? $translation['translation']: false;
        }
        
        $prepared = array(
            'caption'       => $alias,
            'language_id'   => $languageId
        );

        if (!empty($internal) && $internal == $abbreviation) {
            $prepared['translation'] = $alias;
        }
        
        try {
            $result = $this->_translationTable->insert($prepared);
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            return false;
        }
        
        return $result !== false;
    }
    
    /**
     * Check if present translation by language id
     *
     * @param string $caption       The translation caption
     * @param int    $languageId    The language id
     * @return bool
     */
    public function hasTranslation($caption, $languageId)
    {
        $caption = $this->_normalizeCaption($caption);
        $count = $this->_translationTable->count(array(
            'caption = ?'       => $caption,
            'language_id = ?'   => $languageId
        ));
        return $count > 0;
    }
    
    /**
     * Check if present translation by language abbreviation
     *
     * @param string $caption       The translation caption
     * @param string $abbreviation  The language abbreviation
     * @return bool
     */
    public function hasTranslationByLanguageAbbr($caption, $abbreviation)
    {
        $language = new OSDN_Language();
        $languageId = $language->abbr2id($abbreviation);
        if (false === $languageId) {
            return false;
        }

        $caption = $this->_normalizeCaption($caption);
        return $this->hasTranslation($caption, $languageId);
    }
    
    protected function _normalizeCaption($alias)
    {
        if (255 < mb_strlen($alias)) {
            $alias = mb_substr($alias, 0, 255);
        }
        
        return $alias;
    }
}
