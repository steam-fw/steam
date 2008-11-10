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
    protected static $memcache;
    
    private function __construct()
    {
    }
    
    public static function connect($host, $port = NULL)
    {
        self::$memcache = new Memcache;
        self::$memcache->connect($host, $port);
    }
    
    public static function close()
    {
        self::$memcache->close();
    }
    
    public static function add($context, $identifier, $value)
    {
        return self::$memcache->add(md5($context . $identifier), $value);
    }
    
    public static function decrement($context, $identifier, $value)
    {
        return self::$memcache->decrement(md5($context . $identifier), $value);
    }
    
    public static function delete($context, $identifier)
    {
        return self::$memcache->delete(md5($context . $identifier));
    }
    
    public static function flush()
    {
        return self::$memcache->flush();
    }
    
    public static function get($context, $identifier)
    {
        if (!$value = self::$memcache->get(md5($context . $identifier)))
        {
            throw new Steam_Exception_NotCached;
        }
        
        return $value;
    }
    
    public static function increment($context, $identifier, $value)
    {
        return self::$memcache->increment(md5($context . $identifier), $value);
    }
    
    public static function replace($context, $identifier, $value)
    {
        return self::$memcache->replace(md5($context . $identifier), $value);
    }
    
    public static function set($context, $identifier, $value)
    {
        return self::$memcache->set(md5($context . $identifier), $value);
    }
}

?>
