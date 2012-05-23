<?php
/**
 * Steam Notify Class
 *
 * This class provides a system for passing notification messages to the user.
 *
 * Copyright 2008-2012 Shaddy Zeineddine
 *
 * This file is part of Steam, a PHP application framework.
 *
 * Steam is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Steam is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category Frameworks
 * @package Steam
 * @copyright 2008-2012 Shaddy Zeineddine
 * @license http://www.gnu.org/licenses/gpl.txt GPL v3 or later
 * @link http://code.google.com/p/steam-fw
 */

namespace Steam;

class Notify
{
    protected static $channel;
    
    protected static $session;
    
    public static function initialize($channel)
    {
        if (\Steam::config('cache_session') == 'zend')
        {
            self::$session = new \Zend_Session_Namespace($channel);
            
            if (!isset(self::$session->steam_notify))
            {
                self::$session->steam_notify = array();
            }
            
            return;
        }
        
        self::$channel = $channel;
        
        $notify = \Steam\Session::get('steam_notify--' . self::$channel);
        
        if (!is_array($notify))
        {
            $notify = array();
            \Steam\Session::set('steam_notify--' . self::$channel, $notify);
        }
    }
    
    public static function write($message, $type = NULL)
    {
        if (!is_null(self::$session)) return self::zend_write($message, $type);
        
        $notify = \Steam\Session::get('steam_notify--' . self::$channel);
        
        array_unshift($notify, array($message, $type));
        
        \Steam\Session::set('steam_notify--' . self::$channel, $notify);
    }
    
    public static function read()
    {
        if (!is_null(self::$session)) return self::zend_read();
        
        $notify = \Steam\Session::get('steam_notify--' . self::$channel);
        
        $notification = array_pop($notify);
        
        \Steam\Session::set('steam_notify--' . self::$channel, $notify);
        
        return $notification;
    }
    
    protected static function zend_write($message, $type = NULL)
    {
        array_unshift(self::$session->steam_notify, array($message, $type));
    }
    
    protected static function zend_read()
    {
        return array_pop(self::$session->steam_notify);
    }
}

?>
