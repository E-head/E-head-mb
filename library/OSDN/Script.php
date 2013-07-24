<?php

/**
 * Class for using the script loading
 *
 * @category		OSDN
 * @package		OSDN_Script
 */
class OSDN_Script
{
    /**
     * Create the script instance
     *
     * @param string $adapter
     * @return OSDN_Script_Interface
     */
    public static function factory($adapter = 'Js')
    {
        $adapter = ucfirst(strtolower($adapter));
        $adapterNamespace = 'OSDN_Script_';
        $adapterCls = $adapterNamespace . $adapter;
        $adapterInstance = new $adapterCls();
        return $adapterInstance;
    }
}