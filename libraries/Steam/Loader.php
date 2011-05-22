<?php
/**
 * Steam Class Loader Class
 *
 * This class automatically loads class files when they are needed.
 *
 * Copyright 2008-2011 Shaddy Zeineddine
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
 * @copyright 2008-2011 Shaddy Zeineddine
 * @license http://www.gnu.org/licenses/gpl.txt GPL v3 or later
 * @link http://code.google.com/p/steam-fw
 */

namespace Steam;

require_once "Zend/Loader.php";
require_once "Zend/Loader/Autoloader.php";

class Loader
{
    public static $directories = array();
    
    public static function initialize()
    {
        \Zend_Loader_Autoloader::getInstance()->setDefaultAutoloader(array('\Steam\Loader', 'load'))->registerNamespace('Steam');
        
        $libraries = \Steam::config('libraries');
        
        foreach ($libraries as $library)
        {
            self::register($library, \Steam::path('/libraries/'));
        }
    }
    
    public static function register($library, $directory = NULL)
    {
        self::$directories[$library] = $directory;
        
        \Zend_Loader_Autoloader::getInstance()->registerNamespace($library);
    }
    
    public static function load($class)
    {
        try
        {
            $directory = NULL;
            
            if (!is_numeric(strpos($class, '\\')))
            {
                $namespace = substr($class, 0, strpos($class, '_') + 1);
                
                if (isset(self::$directories[$namespace]))
                {
                    $directory = self::$directories[$namespace];
                }
                
                \Zend_Loader::loadClass($class, $directory);
            }
            else
            {
                $namespace = substr($class, 0, strpos($class, '\\') + 1);
                
                if (class_exists($class, false) or interface_exists($class, false))
                {
                    return;
                }
                
                $path = trim(str_replace('\\', '/', $class) . '.php', '/');
                
                if (substr($path, 0, 18) == 'Steam/Application/')
                {
                    include_once \Steam::path('apps/' . substr($path, 18));
                }
                else
                {
                    include_once $path;
                }
            }
        }
        catch (\Exception $exception)
        {
            \Steam\Error::exception_handler($exception);
            
            return false;
        }
        
        return $class;
    }
}

?>
