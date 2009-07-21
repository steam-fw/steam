<?php
/**
 * Steam Class
 *
 * This class initializes the Steam environment.
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
     * The current environment which controls debugging output
     */
    protected static $environment;
    
    /**
     * The filesystem directory that contains the config and index.php
     */
    protected static $base_dir;
    
    /**
     * The current application interface identifier
     */
    protected static $app_interface;
    
    /**
     * Initializes the Steam environment by reading the configuration files and
     * initializing the subcomponents.
     *
     * @return void
     */
    public static function initialize()
    {
        // add the Steam library path to the include path
        set_include_path(self::$base_dir . 'libraries' . PATH_SEPARATOR . get_include_path());
        
        // load the configuration file
        require_once self::$base_dir . 'config.php';
        
        // include the Loader classes
        require_once "Steam/Loader.php";
        
        // add the Steam library to the autoloader
        $libraries[] = 'Steam_';
        
        // activate the autoloader and register the custom autoloader
        Steam_Loader::initialize($libraries);
        
        // store certain configuration variables
        Steam_Web::base_uri($base_uri);
        self::$environment = $environment;
        
        // initialize caching with the configured backend and parameters
        Steam_Cache::initialize($cache_backend, $cache_params);
        
        // initialize localization support
        Steam_Locale::initialize($locale, $timezone);
        
        // initialize the logger
        Steam_Logger::initialize();
        
        // initialize error and exception handling
        Steam_Error::initialize();
        
        // initialize the database with the configured adapter and parameters
        Steam_Db::initialize($db_adapter, $db_params);
        
        // load the global application
        Steam_Application::load('global');
    }
    
    /**
     * Sets or retrieves the base directory. The base directory can only be set
     * once. An exception is thrown if an attempt is made to change it.
     *
     * @throws Steam_Exception_General
     * @return void|string base directory
     * @param string base directory
     */
    public static function base_dir($base_dir = NULL)
    {
        if (!is_null($base_dir))
        {
            if (!is_null(self::$base_dir))
            {
                throw new Steam_Exception_General();
            }
            else
            {
                self::$base_dir = $base_dir;
            }
        }
        
        return self::$base_dir;
    }
    
    /**
     * Converts a path relative to the base directory into an absolute path.
     *
     * @return string absolute path
     * @param string relative path
     */
    public static function path($path = '')
    {
        return self::$base_dir . ltrim($path, '/');
    }
    
    public static function app_interface()
    {
        return self::$app_interface;
    }
    
    public static function environment()
    {
        return self::$environment;
    }
}

?>
