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
    private static $instance;
    private $lifetime;
    
    public static function construct()
    {
        if (!isset(self::$instance))
        {
            $class = __CLASS__;
            
            self::$instance = new $class;
        }
        
        return self::$instance;
    }
    
    private function __construct()
    {
        $this->lifetime = ini_get('session.gc_maxlifetime');
        session_set_save_handler(array(&$this, 'open'), array(&$this, 'close'), array(&$this, 'read'), array(&$this, 'write'), array(&$this, 'destroy'), array(&$this, 'clean'));
        session_start();
        register_shutdown_function(array(&$this, '__destruct'));
    }

    public function __clone()
    {
        throw Steam::_('Exception', 'General');
    }
    
    public function __destruct()
    {
        session_write_close();
    }
    
    public function open($save_path, $session_name)
    {
        return true;
    }
    
    public function close()
    {
        return true;
    }
    
    public function read($session_id)
    {
        return Steam::_('Cache')->get('session', $session_id);
    }
    
    public function write($session_id, $session_data)
    {
        if (Steam::_('Cache')->set('session', $session_id, $session_data, $this->lifetime))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    public function destroy($session_id)
    {
        if (Steam::_('Cache')->delete('session', $session_id))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    public function clean($max_lifetime)
    {
        return true;
    }
}
?>
