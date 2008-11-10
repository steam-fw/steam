<?php
/**
 * Steam Class
 *
 * This class provides access to the Steam library and configuration variables.
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

class Steam
{
    protected static $objects = array();
    public    static $site_id;
    public    static $site_name = 'test';
    public    static $page_code;
    public    static $interface;
    public    static $base_uri;
    public    static $base_dir;
    public    static $config;
    
    // prevent this class from being instantiated
    private function __construct()
    {
    }
    
    /**
     * Initializes the Steam class by loading the configuration variables,
     * setting the default timezone, setting the custom error and exception
     * handlers, and connecting the the database.
     *
     * @return void
     */
    public static function initialize()
    {
        // add the Steam library path to the include path
        set_include_path(self::$base_dir . 'core/libraries' . PATH_SEPARATOR . get_include_path());
        
        // include the Zend Loader class
        require_once "Zend/Loader.php";
        
        // activate the autoloader
        Zend_Loader::registerAutoload();
        
        // set the custom error and exception handlers
        set_exception_handler('Steam_Error::exception_handler');
        set_error_handler('Steam_Error::error_handler');
        register_shutdown_function('Steam_Error::shutdown');
        
        // don't display errors because Steam is handling error output now
        ini_set('display_errors', 0);
        
        // load the configuration file
        require_once self::$base_dir . 'config.php';
        
        // store certain configuration variables
        self::$base_uri = $base_uri;
        
        // set the default locale and timezone
        Steam_Locale::set(LC_ALL, $locale);
        Steam_Locale::timezone($timezone);
        
        // connect to the memcache
        Steam_Cache::connect($memcache_host, $memcache_port);
        
        // set the database server type {MySQL, etc}
        Steam_Db::$server_type = $db_server_type;
        
        // add the master db server
        Steam_Db::add_server('write', $db_write_master);
        
        // add any slave read servers
        foreach ($db_read_slaves as $db_read_slave)
        {
            Steam_Db::add_server('read', $db_read_slave);
        }
        
        // add any slave searc servers
        foreach ($db_search_slaves as $db_search_slave)
        {
            Steam_Db::add_server('search', $db_read_slave);
        }
        
        Steam_Db::connect();
    }
}

?>
