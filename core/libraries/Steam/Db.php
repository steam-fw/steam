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
    /**
     * The type of server being used {MySQL, etc}
     */
    public    static $server_type;
    
    /**
     * An array of arrays of server parameters
     */
    protected static $server_parameters;
    
    /**
     * An array of server connections
     */
    protected static $servers;
    
    /**
     * Adds a database server of the specified role to the pool. At least one
     * write master is required. It is recommended to only write to one server.
     * Servers can by of write, read, or search role. Steam_Exception_Database
     * is thrown if another role is specified.
     *
     * @throws Steam_Exception_Database
     * @return void
     * @param string $role server role {write, read, search}
     * @param array $parameters server parameters {host, user, password, etc}
     */
    public static function add_server($role, $parameters)
    {
        switch ($role)
        {
            case 'write':
            case 'read':
            case 'search':
                self::$server_parameters[$role][] = $parameters;
                break;
            default:
                throw new Steam_Exception_Database(sprintf(gettext('Invalid server role: %s, must be either "write", "read", or "search".'), $role));
        }
    }
    
    /**
     * Selects a server of the specified role at random to be used for the
     * remainder of the execution and returns its connection parameters. If
     * there are no servers available for the specified role, it defaults to a
     * write server. If there are no write servers, Steam_Exception_Database is
     * thrown.
     *
     * @throws Steam_Exception_Database
     * @return array
     * @param string $role server role
     */
    protected static function select_server($role)
    {
        if (!isset(self::$server_parameters[$role]))
        {
            if ($role == 'write' or !isset(self::$server_parameters['write']))
            {
                throw new Steam_Exception_Database(gettext('A master database server has not been defined.'));
            }
            else
            {
                return self::select_server('write');
            }
        }
        
        return self::$server_parameters[$role][array_rand(self::$server_parameters[$role])];
    }
    
    /**
     * Connects to the database servers added by add_server. If the database
     * server type is not supported, Steam_Exception_Database is thrown.
     *
     * @throws Steam_Exception_Database
     * @return void
     */
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
    
    /**
     * Returns a database object for writing.
     *
     * @return object
     */
    public static function write()
    {
        return self::$servers['write'];
    }
    
    /**
     * Returns a database object for reading.
     *
     * @return object
     */
    public static function read()
    {
        return self::$servers['read'];
    }
    
    /**
     * Returns a database object for searching.
     *
     * @return object
     */
    public static function search()
    {
        return self::$servers['search'];
    }
}

?>
