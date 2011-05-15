<?php
/**
 * Steam Session Handler
 *
 * This class replaces the built-in PHP session handler with a custom one with a
 * Memcache back end.
 *
 * Copyright 2008-2011 Shaddy Zeineddine
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
 * @copyright 2008-2011 Shaddy Zeineddine
 * @license http://www.gnu.org/licenses/gpl.txt GPL v3 or later
 * @link http://code.google.com/p/steam-fw
 */

namespace Steam;

class Session implements \Zend_Session_SaveHandler_Interface
{
    /**
     * The length of time which cached data should persist.
     *
     * @var int seconds
     */
    protected $lifetime;
    
    /**
     * Stores any important configuration settings.
     *
     * @return void
     */
    public function __construct()
    {
        $this->lifetime = ini_get('session.gc_maxlifetime');
    }
    
    /**
     * Ensures session data is committed.
     *
     * @return void
     */
    public function __destruct()
    {
        \Zend_Session::writeClose();
    }
    
    /**
     * Opens a session
     *
     * @return bool
     */
    public function open($save_path, $session_name)
    {
        return true;
    }
    
    /**
     * Closes a session
     *
     * @return bool
     */
    public function close()
    {
        return true;
    }
    
    /**
     * Retrieves session data.
     *
     * @return string
     */
    public function read($session_id)
    {
        try
        {
            $data = \Steam\Cache::get('_session', $session_id);
            
            return $data;
        }
        catch (\Steam\Exception\Cache $exception)
        {
            \Steam\Error::log_exception($exception);
            
            return '';
        }
    }
    
    /**
     * Writes session data.
     *
     * @return bool
     */
    public function write($session_id, $session_data)
    {
        try
        {
            \Steam\Cache::set('_session', $session_id, $session_data, $this->lifetime)
            
            return true;
        }
        catch (\Steam\Exception\Cache $exception)
        {
            \Steam\Error::log_exception($exception);
            
            return false;
        }
    }
    
    /**
     * Deletes all data associated with a session.
     *
     * @return bool
     */
    public function destroy($session_id)
    {
        try
        {
            \Steam\Cache::delete('_session', $session_id)
            
            return true;
        }
        catch (\Steam\Exception\Cache $exception)
        {
            \Steam\Error::log_exception($exception);
            
            return false;
        }
    }
    
    /**
     * Triggers garbage collection
     *
     * @return bool
     */
    public function gc($max_lifetime)
    {
        return true;
    }
}

?>
