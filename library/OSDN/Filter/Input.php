<?php

/**
 * OSDN_Filter_Input
 *
 * @category OSDN
 * @package OSDN_Filter
 */
class OSDN_Filter_Input extends Zend_Filter_Input
{

    const MODULE_CODE = 2;
    
    const MODULE_NAME = 'Filter';
    
    /**
     * OSDN_Filter_Input constructor
     *
     * @param array $filterRules
     * @param array $validatorRules
     * @param array $data       OPTIONAL
     * @param array $options    OPTIONAL
     *
     * @todo remove self::INPUT_NAMESPACE because it's deprecated
     */
    public function __construct($filterRules, $validatorRules, array $data = null, array $options = null)
    {
        $this->setOptions(array(
            self::INPUT_NAMESPACE => 'OSDN_Validate',
            self::ESCAPE_FILTER   => 'StringTrim'
        ));
        parent::__construct($filterRules, $validatorRules, $data, $options);
        
        $this->addFilterPrefixPath('OSDN_Filter', dirname(__FILE__));
    }
    
    /**
     * Retrieve statuses from OSDN_Filter_Input
     *
     * @return array     Contain OSDN_Response_Status collection
     */
    public function getStatuses()
    {
        $data = array();
        $messageCollection = $this->getMessages();
        
        foreach ($messageCollection as $field => $messages) {
            foreach ($messages as $status => $msg) {
                array_push($data, new OSDN_Response_Status(array(
                    'statusCode'    => OSDN_Response_Status_Storage_Abstract::INPUT_PARAMS_INCORRECT,
                    'statusMessage' => $msg,
                    'moduleCode'    => self::MODULE_CODE,
                    'moduleName'    => self::MODULE_NAME,
                    'field'         => $field,
                    'statusAbbr'    => $status
                )));
            }
        }
        
        return $data;
    }
    
    /**
     * Retrieve all data previously escaped
     *
     * @return array
     */
    public function getData()
    {
        $unescaped = $this->getEscaped();
        $unknown = $this->_escapeRecursive($this->getUnknown());
        return array_merge($unescaped, $unknown);
    }

    /**
     * Get escaped field
     * If field not present in valid field then try get it from unknown
     * maybe validator is not set for this field
     *
     * @param string $fieldName     OPTIONAL
     * @return mixed
     */
    public function getEscaped($fieldName = null)
    {
        $value = parent::getEscaped($fieldName);
        if (null !== $fieldName && is_null($value)) {
            if (array_key_exists($fieldName, $this->_unknownFields)) {
                return $this->_escapeRecursive($this->_unknownFields[$fieldName]);
            }
        }
        return $value;
    }
    
    /**
     * Get unescaped field
     *
     * If field not present in valid field then try get it from unkown
     * maybe validator is not set for this field
     *
     * @param string $fieldName OPTIONAL
     * @return mixed
     */
    public function getUnescaped($fieldName = null)
    {
        $value = parent::getUnescaped($fieldName);
        if (null !== $fieldName && is_null($value)) {
            if (array_key_exists($fieldName, $this->_unknownFields)) {
                return $this->_unknownFields[$fieldName];
            }
        }
    }
    
}