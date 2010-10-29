<?php

namespace Steam;

class Notify
{
    protected static $session;
    
    public static function initialize($channel)
    {
        self::$session = new \Zend_Session_Namespace($channel);
        
        if (!isset(self::$session->steam_notify))
        {
            self::$session->steam_notify = array();
        }
    }
    
    public static function write($message, $type = NULL)
    {
        array_unshift(self::$session->steam_notify, array($message, $type));
    }
    
    public static function read()
    {
        return array_pop(self::$session->steam_notify);
    }
}

?>
