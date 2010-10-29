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

namespace Steam;

class Session implements \Zend_Session_SaveHandler_Interface
{
    protected $lifetime;
    
    public function __construct()
    {
        $this->lifetime = ini_get('session.gc_maxlifetime');
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
        try
        {
            return \Steam\Cache::get('session', $session_id);
        }
        catch (\Steam\Exception\Cache $exception)
        {
            session_regenerate_id();
        }
    }
    
    public function write($session_id, $session_data)
    {
        if (\Steam\Cache::set('session', $session_id, $session_data, $this->lifetime))
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
        if (\Steam\Cache::delete('session', $session_id))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    public function gc($max_lifetime)
    {
        return true;
    }
}

?>
