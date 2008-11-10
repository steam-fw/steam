<?php
/**
 * Steam Database Class
 *
 * This class manages database connections and load balancing.
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

class Steam_Db
{
    public    static $server_type;
    protected static $server_parameters;
    protected static $servers;
    
    /**
     * This class can only be instantiated using the construct method.
     *
     * @return void
     */
    private function __construct()
    {
    }
    
    public static function add_server($type, $parameters)
    {
        switch ($type)
        {
            case 'write':
            case 'read':
            case 'search':
                self::$server_parameters[$type][] = $parameters;
                break;
            default:
                throw new Steam_Exception_Database(sprintf(gettext('Invalid server type: %s, must be either "write", "read", or "search".'), $type));
        }
    }
    
    protected static function select_server($type)
    {
        if (!isset(self::$server_parameters[$type]))
        {
            if ($type == 'write' or !isset(self::$server_parameters['write']))
            {
                throw new Steam_Exception_Database(gettext('A master database server has not been defined.'));
            }
            else
            {
                return self::select_server('write');
            }
        }
        
        return self::$server_parameters[$type][array_rand(self::$server_parameters[$type])];
    }
    
    public static function connect()
    {
        switch (self::$server_type)
        {
            case 'mysql':
                $class = 'Steam_Db_MySQL';
                break;
            default:
                throw Steam_Exception_Database(sprintf(gettext('Unsupported database server: %s.'), self::$server_type));
        }
        
        self::$servers['write']  = new $class(self::select_server('write'));
        self::$servers['read']   = new $class(self::select_server('read'));
        self::$servers['search'] = new $class(self::select_server('search'));
    }
    
    public static function write()
    {
        return self::$servers['write'];
    }
    
    public static function read()
    {
        return self::$servers['read'];
    }
    
    public static function search()
    {
        return self::$servers['search'];
    }
}

?>
