<?php
/**
 * Steam Session Storage
 *
 * This class provides enhanced session interaction with locking
 *
 * Copyright 2012 Shaddy Zeineddine
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
 * @copyright 2012 Shaddy Zeineddine
 * @license http://www.gnu.org/licenses/gpl.txt GPL v3 or later
 * @link http://code.google.com/p/steam-fw
 */

namespace Steam;

class Session
{
    /**
     * The current session's index file.
     *
     * @var string file name
     */
    protected static $index;
    
    /**
     * An array of locks currently held by this process
     *
     * @var array locks
     */
    protected static $locks;
    
    /**
     * The index file system pointer
     *
     * @var resource file pointer
     */
    protected static $ip;
    
    /**
     * The length of time which session data should remain fresh.
     *
     * @var int seconds
     */
    protected static $lifetime;
    
    /**
     * The namespace of the active session
     *
     * @var string namespace
     */
    protected static $namespace;
    
    /**
     * The time of the last modification of the index file.
     *
     * @var int unix timestamp
     */
    protected static $mtime;
    
    /**
     * A cache of recently retrieved session variables.
     *
     * @var array session cache
     */
    protected static $cache;
    
    
    /**
     * Initializes the session's variable index for locking
     *
     * @return void
     */
    public static function initialize($namespace = NULL)
    {
        // store the config setting for session lifetime to use for session data
        self::$lifetime = ini_get('session.gc_maxlifetime');
        
        // initialize the in memory array of currently held locks and variable cache
        self::$locks = array();
        self::$cache = array();
        
        // define the active namespace
        self::$namespace = is_null($namespace) ? 'default' : $namespace;
        
        // start the session
        session_start();
        
        // if this is a new session, create a new index
        if (!isset($_SESSION['_steam_session_index--' . self::$namespace]))
        {
            // generate a unique filename for the index
            $_SESSION['_steam_session_index--' . self::$namespace] = tempnam('/tmp', 'steam_session--' . self::$namespace . '--');
            
            // initialize an empty array and store it to the index
            $array = array();
            file_put_contents($_SESSION['_steam_session_index--' . self::$namespace], serialize($array));
        }
        
        // store the location of the index
        self::$index = $_SESSION['_steam_session_index--' . self::$namespace];
        
        // close the session to release the lock
        session_write_close();
    }
    
    public static function destroy()
    {
        session_start();
        
        if (!isset($_SESSION['_steam_session_index--' . self::$namespace]))
        {
            unlink($_SESSION['_steam_session_index--' . self::$namespace]);
            unset($_SESSION['_steam_session_index--' . self::$namespace]);
        }
        
        session_destroy();
    }
    
    protected static function check_cache()
    {
        try
        {
            $mtime = filemtime(self::$index);
        }
        catch (\Steam\Exception\FileNotFound $exception)
        {
            $array = array();
            file_put_contents($_SESSION['_steam_session_index--' . self::$namespace], serialize($array));
            $mtime = filemtime(self::$index);
        }
        
        if ($mtime > self::$mtime)
        {
            self::$cache = array();
            self::$mtime = $mtime;
        }
    }
    
    /**
     * Gets the named session variable
     *
     * @param string name
     * @return string value
     */
    public static function get($name)
    {
        self::check_cache();
        
        if (isset(self::$cache[$name])) return $cache[$name];
        
        self::lock($name);
        
        try
        {
            $value = \Steam\Cache::get(self::$index, $name);
        }
        catch (\Steam\Exception\Cache $exception)
        {
            $value = NULL;
        }
        
        self::unlock($name);
        
        return $value;
    }
    
    /**
     * Sets the named session variable
     *
     * @param string name
     * @param string value
     * @return string value
     */
    public static function set($name, $value)
    {
        self::lock($name);
        \Steam\Cache::set(self::$index, $name, $value, array(), self::$lifetime);
        self::unlock($name);
    }
    
    /**
     * Aquires an exclusive lock on the named session variable
     *
     * @param string session id
     * @return void
     */
    public static function lock($name)
    {
        if (in_array($name, self::$locks)) return;
        
        $index = self::index_read();
        
        if (in_array($name, $index))
        {
            self::index_close();
            usleep(50000);
            return self::lock($name);
        }
        
        $index[] = $name;
        self::index_write($index);
        self::index_close();
        self::$locks[] = $name;
    }
    
    /**
     * Releases an exclusive lock on the named session variable
     *
     * @param string session id
     * @return void
     */
    public static function unlock($name)
    {
        if (!in_array($name, self::$locks)) return;
        
        $index = self::index_read();
        
        if ($key = array_search($name, $index))
        {
            unset($index[$key]);
            self::index_write($index);
        }
        
        self::index_close();
    }
    
    /**
     * Opens the index file with an exclusive lock, reads
     * the contents, and returns an array of the index.
     *
     * @return array index
     */
    protected static function index_read()
    {
        self::$ip = fopen(self::$index, 'rb+');
        
        if (!flock(self::$ip, \LOCK_EX)) throw new \Steam\Exception\General('Could not obtain a lock on the session index.');
        
        return unserialize(fread(self::$ip, filesize(self::$index)));
    }
    
    /**
     * Writes the index array to the index file.
     *
     * @param array index
     * @return void
     */
    protected static function index_write($array)
    {
        fwrite(self::$ip, serialize($array));
    }
    
    /**
     * Closes the index file and relases the exclusive lock
     *
     * @return void
     */
    protected static function index_close()
    {
        flock(self::$ip, \LOCK_UN);
        fclose(self::$ip);
    }
}

?>