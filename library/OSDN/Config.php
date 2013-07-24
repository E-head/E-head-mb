<?php

class OSDN_Config extends OSDN_Configuration 
{
    private static $_instance;
    
    public static function getInstance() {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public static function get($name) {
        return self::getInstance()->getProfileParam($name);
    }
}
