<?php
/**
 * Steam Session Handler
 *
 * This class replaces the built-in PHP session handler with a custom one with a
 * Memcache back end.
 *
 * Copyright 2008-2009 Shaddy Zeineddine
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
 * @copyright 2008-2009 Shaddy Zeineddine
 * @license http://www.gnu.org/licenses/gpl.txt GPL v3 or later
 * @link http://code.google.com/p/steam-fw
 */

class Steam_Web_Session
{
    protected static $lifetime;
    
    private function __construct()
    {
    }
    
    public static function start()
    {
        self::$lifetime = ini_get('session.gc_maxlifetime');
        session_set_save_handler('Steam_Web_Session::open', 'Steam_Web_Session::close', 'Steam_Web_Session::read', 'Steam_Web_Session::write', 'Steam_Web_Session::destroy', 'Steam_Web_Session::clean');
        session_start();
        #register_shutdown_function('session_write_close');
    }
    
    public static function open($save_path, $session_name)
    {
        return true;
    }
    
    public static function close()
    {
        return true;
    }
    
    public static function read($session_id)
    {
        try
        {
            return Steam_Cache::get('session', $session_id);
        }
        catch (Steam_Exception_Cache $exception)
        {
            session_regenerate_id();
        }
    }
    
    public static function write($session_id, $session_data)
    {
        if (Steam_Cache::set('session', $session_id, $session_data, $this->lifetime))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    public static function destroy($session_id)
    {
        if (Steam_Cache::delete('session', $session_id))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    public static function clean($max_lifetime)
    {
        return true;
    }
}

?>
