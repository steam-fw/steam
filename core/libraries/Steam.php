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
    private static $objects = array();
    public  static $timezone;
    public  static $language;
    public  static $base_uri;
    public  static $base_dir;
    public  static $db_user;
    public  static $db_pass;
    public  static $db_name;
    public  static $db_host;
    public  static $mc_host;
    public  static $mc_port;
    
    // prevent this class from being instantiated
    private function __construct()
    {
        throw self::_('Exception', 'General');
    }
    
    /**
     * Initializes the Steam class by loading the configuration variables,
     * setting the default timezone, setting the custom error and exception
     * handlers, and connecting the the database.
     *
     * @return void
     */
    public static function init()
    {
        // load the configuration file
        require_once self::$base_dir . 'config.php';
        
        // set the configuration variables
        self::$timezone = $timezone;
        self::$language = $language;
        self::$base_uri = $base_uri;
        self::$db_user  = $mysql_user;
        self::$db_pass  = $mysql_pass;
        self::$db_name  = $mysql_name;
        self::$db_host  = $mysql_host;
        self::$mc_host  = $memcache_host;
        self::$mc_port  = $memcache_port;
        
        // set the default timezone
        date_default_timezone_set(self::$timezone);
        
        // set the custom error and exception handlers
        set_exception_handler(array(self::_('Error'), 'exception_handler'));
        set_error_handler(array(self::_('Error'), 'error_handler'));
        
        // initialize and connect to the database
        self::_('Db')->connect();
    }
    
    /**
     * Basic method used to access components of the Steam library.
     *
     * @return object
     * @param string $class class identifier
     */
    public static function _($class)
    {
        // check if the object already exists to skip unnecessary steps
        if (!array_key_exists($class, self::$objects))
        {
            // form the actual class name from the class identifier
            $class_name = 'Steam_' . str_replace('/', '_', $class);
            
            // any extra arguments get passed to the class
            $args = func_get_args();
            array_shift($args);
            
            // include the class file
            require_once self::$base_dir . 'core/libraries/Steam/' . $class . '.php';
            
            // if the class has the factory method, it should not be reused
            if (method_exists($class_name, 'factory'))
            {
                // return a new instance of the class
                return call_user_func_array(array($class_name, 'factory'), $args);
            }
            else
            {
                // store an instance of the class
                self::$objects[$class] = call_user_func_array(array($class_name, 'construct'), $args);
            }
        }
        
        // return the object for the specified class
        return self::$objects[$class];
    }
    
    /**
     * Initializes the Zend Autoloader to enable the use of the Zend
     * Framework.
     *
     * @return void
     */
    public static function Zend()
    {
        // add the Zend library path to the include path
        set_include_path(self::$base_dir . 'core/libraries/Zend' . PATH_SEPARATOR . get_include_path());
        
        // include the Zend Loader class
        require_once "Zend/Loader.php";
        
        // activate the autoloader
        Zend_Loader::registerAutoload();
    }
}

?>
