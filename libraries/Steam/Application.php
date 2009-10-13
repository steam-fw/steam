<?php
/**
 * Steam Application Class
 *
 * This class contains utilities related to the current application.
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

class Steam_Application
{
    /**
     * The application name, the name of its directory
     */
    protected static $app_name     = 'startup';
    
    protected static $app_id       = 0;
    
    protected static $app_base_dir;
    
    protected static $app_base_uri;
    
    /**
     * The application object
     */
    protected static $app_object;
    
    
    public static function load($app_name)
    {
        self::$app_name     = $app_name;
        self::$app_base_dir = Steam::path('apps/' . self::$app_name . '/');
        
        if (!is_dir(self::$app_base_dir))
        {
            throw new Steam_Exception_AppNotFound();
        }
        
        try
        {
            include self::path('config.php');
            
            if (isset($library))
            {
                Steam_Loader::register($library, self::$app_base_dir);
            }
        }
        catch (Steam_Exception_FileNotFound $exception)
        {
            // no application configuration file, continue
        }
        catch (Exception $exception)
        {
            Steam_Error::log_exception($exception);
        }
    }
    
    /**
     * Retrieves the application name
     *
     * @return string application name
     */
    public static function name()
    {
        return self::$app_name;
    }
    
    public static function id($app_id = NULL)
    {
        if (is_null($app_id))
        {
            return self::$app_id;
        }
        else
        {
            self::$app_id = $app_id;
        }
    }
    
    /**
     * Retrieves the application base directory.
     *
     * @return string base directory
     */
    public static function base_dir()
    {
        return self::$app_base_dir;
    }
    
    /**
     * Converts a path relative to the applicatoin base directory into an
     * absolute path.
     *
     * @return string absolute path
     * @param string relative path
     */
    public static function path($path = '')
    {
        return self::$app_base_dir . ltrim($path, '/');
    }
    
    /**
     * Sets or retrieves the application base URI. The base URI can only be set
     * once. An exception is thrown if an attempt is made to change it.
     *
     * @throws Steam_Exception_General
     * @return void|string application base URI
     * @param string application base URI
     */
    public static function base_uri($app_base_uri = NULL)
    {
        if (!is_null($app_base_uri))
        {
            if (!is_null(self::$app_base_uri))
            {
                throw new Steam_Exception_General();
            }
            else
            {
                self::$app_base_uri = $app_base_uri;
            }
        }
        
        return self::$app_base_uri;
    }
    
    /**
     * Converts a URI relative to the application base URI into an absolute URI.
     *
     * @return string absolute uri
     * @param string relative uri
     */
    public static function uri($path = '')
    {
        return rtrim(self::$app_base_uri, '/') . '/' . ltrim($path, '/');
    }
    
    /**
     * Returns the application name as the string representation of the object.
     *
     * @return string application name
     */
    public static function __toString()
    {
        return self::$app_name;
    }
}

?>
