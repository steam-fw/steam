<?php
/**
 * Steam Cache Interface Class
 *
 * This class provides a front end interface to Zend_Cache.
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
     * The Zend_Cache object.
     */
    protected static $cache;
    
    /**
     * Creates a new instance of Zend_Cache with the specified backend and
     * parameters.
     *
     * @return void
     * @param string $backend valid Zend_Cache backend
     * @param array $parameters Zend_Cache backend options
     */
    public static function initialize($backend, array $parameters)
    {
        self::$cache = Zend_Cache::factory('Core', $backend, array('automatic_serialization' => true), $parameters);
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
        if (!self::$cache->save($value, md5(self::format_context($context) . $identifier)))
        {
            throw new Steam_Exception_Cache(gettext('There was a problem storing data in the cache.'));
        }
    }
    
    /**
     * Retrieves data from memcache identified by the context and identifier. If
     * the data does not exist in the cache, Steam_Exception_Cache is thrown.
     *
     * @throws Steam_Exception_Cache
     * @return mixed cached value
     * @param string $context data context
     * @param string $identifier context specific identifier
     */
    public static function get($context, $identifier)
    {
        if (!$value = self::$cache->load(md5(self::format_context($context) . $identifier)))
        {
            throw new Steam_Exception_Cache(gettext('The specified data does not exist within the cache.'));
        }
        
        return $value;
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
        if (!self::$cache->remove(md5(self::format_context($context) . $identifier)))
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
        if (!self::$cache->clean())
        {
            throw new Steam_Exception_Cache(gettext('There was a problem flushing all stored data.'));
        }
    }
    
    /**
     * Returns the underlying Zend_Cache object.
     *
     * @return object Zend_Cache
     */
    public static function get_cache()
    {
        return self::$cache;
    }
    
    /**
     * Converts a relative context into an absolute context by prefixing it with
     * the application name if applicable.
     *
     * @return string absolute data context
     * @param string $context data context
     */
    protected static function format_context($context)
    {
        if ($context[0] != '/')
        {
            $context = '/' . Steam::$app_name . '/' . $context;
        }
        
        return $context;
    }
}

?>
