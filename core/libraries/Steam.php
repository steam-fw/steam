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
    
    public static function init()
    {
        // load the configuration file
        require_once self::$base_dir . 'config.php';
        
        // set the configuration variables
        self::$timezone = $timezone;
        self::$language = $language;
        self::$base_uri = $base_uri;
        self::$db_user  = $db_user;
        self::$db_pass  = $db_pass;
        self::$db_name  = $db_name;
        self::$db_host  = $db_host;
        
        // set the default timezone
        date_default_timezone_set(self::$timezone);
        
        // set the custom error and exception handlers
        set_exception_handler(array(self::_('Error'), 'exception_handler'));
        set_error_handler(array(self::_('Error'), 'error_handler'));
        
        // initialize and connect to the database
        self::_('Db')->connect();
    }
    
    public static function _($class)
    {
        // check if the object already exists to skip unnecessary steps
        if (!array_key_exists($class, self::$objects))
        {
            // if an object doesn't exist, create a new one
            $args = func_get_args();
            self::$objects[$class] = call_user_func_array(array(__CLASS__, '_new'), $args);
        }
        
        // return the object for the specified class
        return self::$objects[$class];
    }
    
    public static function _new($class)
    {
        // grab the arguments in an array in order to pass them to the class
        $args  = func_get_args();
        $class = array_shift($args);
        
        // include the class file
        require_once self::$base_dir . 'core/libraries/Steam/' . $class . '.php';
        
        // create a new instance of the class and return it
        return call_user_func_array(array('Steam_' . str_replace('/', '_', $class), 'construct'), $args);
    }
}

?>
