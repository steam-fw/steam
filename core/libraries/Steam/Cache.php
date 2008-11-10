<?php
/**
 * Steam Memcache Abstraction Class
 *
 * This class provides a simple interface to PHP's Memcache class.
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

class Steam_Cache
{
    /**
     * Memcache object
     */
    protected static $memcache;
    
    /**
     * Connects to the specified memcache host. Steam_Exception_Cache is thrown
     * if connecting fails.
     *
     * @throws Steam_Exception_Cache
     * @return void
     * @param string $host memcache host
     * @param int $port memcache port
     */
    public static function connect($host, $port = NULL)
    {
        self::$memcache = new Memcache;
        
        if (!self::$memcache->connect($host, $port))
        {
            throw new Steam_Exception_Cache(gettext('There was a problem connecting to the memcache server.'));
        }
    }
    
    /**
     * Closes the active memcache connection. Steam_Exception_Cache is thrown
     * if there is no active connection or if there was an error closing the
     * connection.
     *
     * @throws Steam_Exception_Cache
     * @return void
     */
    public static function close()
    {
        if (!is_object(self::$memcache))
        {
            throw new Steam_Exception_Cache(gettext('There is no memcache connection to close.'));
        }
        
        if (!self::$memcache->close())
        {
            throw new Steam_Exception_Cache(gettext('There was an problem closing the memcache connection.'));
        }
        
        self::$memcache = NULL;
    }
    
    /**
     * Adds data to memcache using the context and identifier strings as the
     * key. If data already exists with the same context and identifier, the
     * data is not added and Steam_Exception_Cache is thrown.
     *
     * @throws Steam_Exception_Cache
     * @return void
     * @param string $context data context
     * @param string $identifier context specific identifier
     * @param mixed $value data to store
     */
    public static function add($context, $identifier, $value)
    {
        if (!self::$memcache->add(md5($context . $identifier), $value);
        {
            throw new Steam_Exception_Cache(gettext('Data already exists with the specified identifier.'));
        }
    }
    
    /**
     * Decrements a stored number by the amount specified using the context and
     * identifier strings as the key. The new value is returned if successful,
     * otherwise Steam_Exception_Cache is thrown.
     *
     * @return int
     * @param string $context data context
     * @param string $identifier context specific identifier
     * @param int $value amount to decrement
     */
    public static function decrement($context, $identifier, $value = 1)
    {
        if (!is_int($result = self::$memcache->decrement(md5($context . $identifier), $value)))
        {
            throw new Steam_Exception_Cache;
        }
        
        return $result;
    }
    
    /**
     * Deletes data from memcache using the context and identifier strings as
     * the key. If deletion fails, Steam_Exception_Cache is thrown.
     *
     * @throws Steam_Exception_Cache
     * @return void
     * @param string $context data context
     * @param string $identifier context specific identifier
     */
    public static function delete($context, $identifier)
    {
        if (!self::$memcache->delete(md5($context . $identifier)))
        {
            throw new Steam_Exception_Cache(gettext('There was a problem deleting the stored data.'));
        }
    }
    
    /**
     * Deletes all data from memcache. If flushing fails, Steam_Exception_Cache
     * is thrown.
     *
     * @throws Steam_Exception_Cache
     * @return void
     */
    public static function flush()
    {
        if (!self::$memcache->flush())
        {
            throw new Steam_Exception_Cache(gettext('There was a problem flushing all stored data.'));
        }
    }
    
    /**
     * Retrieves data from memcache identified by the context and identifier. If
     * the data does not exist in the cache, Steam_Exception_Cache is thrown.
     *
     * @throws Steam_Exception_Cache
     * @return mixed
     * @param string $context data context
     * @param string $identifier context specific identifier
     */
    public static function get($context, $identifier)
    {
        if (!$value = self::$memcache->get(md5($context . $identifier)))
        {
            throw new Steam_Exception_Cache(gettext('The specified data does not exist within the cache.'));
        }
        
        return $value;
    }
    
    /**
     * Increments a stored number by the amount specified using the context and
     * identifier strings as the key. The new value is returned if successful,
     * otherwise Steam_Exception_Cache is thrown.
     *
     * @return int
     * @param string $context data context
     * @param string $identifier context specific identifier
     * @param int $value amount to increment
     */
    public static function increment($context, $identifier, $value)
    {
        if (!is_int($result = self::$memcache->increment(md5($context . $identifier), $value)))
        {
            throw new Steam_Exception_Cache;
        }
        
        return $result;
    }
    
    /**
     * Replaces data to memcache using the context and identifier strings as the
     * key. If data already exists with the same context and identifier, the
     * existing data is replaced. If data does not already exist,
     * Steam_Exception_Cache is thrown.
     *
     * @throws Steam_Exception_Cache
     * @return void
     * @param string $context data context
     * @param string $identifier context specific identifier
     * @param mixed $value data to store
     */
    public static function replace($context, $identifier, $value)
    {
        if (!self::$memcache->replace(md5($context . $identifier), $value))
        {
            throw new Steam_Exception_Cache(gettext('No data was replaced in the cache.'));
        }
    }
    
    /**
     * Sets data in memcache using the context and identifier strings as the key.
     * If data already exists with the same context and identifier, the data is
     * not overwritten. If storing fails, Steam_Exception_Cache is thrown.
     *
     * @throws Steam_Exception_Cache
     * @return void
     * @param string $context data context
     * @param string $identifier context specific identifier
     * @param mixed $value data to store
     */
    public static function set($context, $identifier, $value)
    {
        if (!self::$memcache->set(md5($context . $identifier), $value))
        {
            throw new Steam_Exception_Cache(gettext('There was a problem storing data in the cache.'));
        }
    }
}

?>
