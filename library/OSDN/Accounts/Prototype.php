<?php

/**
 * Contain information about logged account at the moment
 *
 * @category OSDN
 * @package OSDN_Accounts
 */
class OSDN_Accounts_Prototype
{
    const ID = 'id';

    const ROLE = 'role_id';

    const ACL = 'acl';

    /**
     * Check if user is authenticated
     *
     * @return boolean
     */
    public static function isAuthenticated()
    {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            return false;
        }

        $info = self::getInformation();
        if (empty($info) || !property_exists($info, 'id')) {
            return false;
        }
        return (boolean) $info->id;
    }

    /**
     * Fetch account information for logged user
     *
     * @return StdClass
     */
    public static function getInformation()
    {
        return Zend_Auth::getInstance()->getIdentity();
    }

    /**
     * Fetch account id
     *
     * @return int|null
     */
    public static function getId()
    {
        return (int) self::getProperty(self::ID);
    }

    /**
     * Fetch role id
     *
     * @return int|null
     */
    public static function getRoleId()
    {
        return (int) self::getProperty(self::ROLE);
    }

    /**
     * Retrieve the acl object
     *
     * @return OSDN_Acl|null
     */
    public static function getAcl()
    {
        return self::getProperty(self::ACL);
    }

    /**
     * Fetch property from account information
     *
     * @param string $property
     * @return mixed | null if not exists
     */
    protected static function getProperty($property)
    {
        if (self::isAuthenticated()) {
            $information = self::getInformation();
            if (!empty($information) && property_exists($information, $property)) {
                 return $information->{$property};
            }
        }

        return null;
    }
}