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
    private static $instance;
    private $memcache;
    
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
        $this->memcache = new Memcache;
        $this->memcache->connect(Steam::$mc_host, Steam::$mc_port);
    }
    
    public function __destruct()
    {
        $this->memcache->close();
    }

    public function __clone()
    {
        throw Steam::_('Exception', 'General');
    }
    
    public function add($context, $identifier, $value)
    {
        return $this->memcache->add(md5($context . $identifier), $value);
    }
    
    public function decrement($context, $identifier, $value)
    {
        return $this->memcache->decrement(md5($context . $identifier), $value);
    }
    
    public function delete($context, $identifier)
    {
        return $this->memcache->delete(md5($context . $identifier));
    }
    
    public function flush()
    {
        return $this->memcache->flush();
    }
    
    public function get($context, $identifier)
    {
        if (!$value = $this->memcache->get(md5($context . $identifier)))
        {
            throw Steam::_('Exception', 'NotCached');
        }
        
        return $value;
    }
    
    public function increment($context, $identifier, $value)
    {
        return $this->memcache->increment(md5($context . $identifier), $value);
    }
    
    public function replace($context, $identifier, $value)
    {
        return $this->memcache->replace(md5($context . $identifier), $value);
    }
    
    public function set($context, $identifier, $value)
    {
        return $this->memcache->set(md5($context . $identifier), $value);
    }
}

?>
