<?php
/**
 * Steam Class
 *
 * This class initializes the Steam environment and stores important variables.
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
    /**
     * The Steam base URI, the URI of the directory that contains index.php
     */
    public static $base_uri;
    
    /**
     * The filesystem directory that contains the config and index.php
     */
    public static $base_dir;
    
    /**
     * The current interface identifier
     */
    public static $interface;
    
    /**
     * The current application identifier
     */
    public static $app_id;
    
    /**
     * The current application name, the name of its directory
     */
    public static $app_name;
    
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
        ini_set('html_errors',    0);
        
        // load the configuration file
        require_once self::$base_dir . 'config.php';
        
        // store certain configuration variables
        self::$base_uri = $base_uri;
        
        // initialize caching with the configured backend and parameters
        Steam_Cache::initialize($cache_backend, $cache_params);
        
        // initialize the database with the configured adapter and parameters
        Steam_Db::initialize($db_adapter, $db_params);
        
        // set the default locale and timezone
        date_default_timezone_set($timezone);
        Zend_Locale::setDefault($locale);
        
        Zend_Locale::setCache(Steam_Cache::$cache);
        Zend_Translate::setCache(Steam_Cache::$cache);
        
        Zend_Registry::set('Zend_Locale', new Zend_Locale(Zend_Locale::BROWSER));
        
        Steam_Lang::source('steam');
    }
}

?>
