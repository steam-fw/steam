<?php
/**
 * Steam Class Loader Class
 *
 * This class manages class autoloading.
 *
 * Copyright 2008-2012 Shaddy Zeineddine
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
 * @copyright 2008-2012 Shaddy Zeineddine
 * @license http://www.gnu.org/licenses/gpl.txt GPL v3 or later
 * @link http://code.google.com/p/steam-fw
 */

namespace Steam;

class Loader
{
    /**
     * Stores the autoloader instance
     */
    public static $autoloader;
    
    /**
     * Initializes the standard Zend autoloader and registers required librarlies
     *
     * @return void
     */
    public static function initialize()
    {
        include \Steam::path('libraries/Zend/Loader/StandardAutoloader.php');
        
        $options = array(
            'autoregister_zf'     => true,
            'fallback_autoloader' => false,
            'namespaces'          => array(
                'Steam'           => \Steam::path('libraries/Steam'),
            ),
            'prefixes'            => array(
                'Minify_'         => \Steam::path('libraries/Minify'),
            ),
        );
        
        self::$autoloader = new \Zend\Loader\StandardAutoloader($options);
        
        self::$autoloader->register();
    }
    
    /**
     * Registers an additional namespaced library with the autoloader
     * @see Steam\Loader::register
     *
     * @param string namespace
     * @return void
     * @throws Steam\Exception\FileNotFound if library directory cannot be located
     */
    public static function registerNamespace($namespace)
    {
        self::register($namespace, 'namespace');
    }
    
    /**
     * Registers an additional prefixed library with the autoloader
     * @see Steam\Loader::register
     *
     * @param string prefix
     * @return void
     * @throws Steam\Exception\FileNotFound if library directory cannot be located
     */
    public static function registerPrefix($prefix)
    {
        self::register($prefix, 'prefix');
    }
    
    /**
     * Registers an additional library with the autoloader
     *
     * @param string namespace or prefix
     * @param string type: {"namespace", "prefix", "" (autodetect)}
     * @return void
     * @throws Steam\Exception\FileNotFound if library directory cannot be located
     */
    protected static function register($name, $type = NULL)
    {
        if (is_null($type)) $type = (substr($name, -1, 1) == '_') ? 'prefix' : 'namespace';
        
        if (!is_dir($library_path = \Steam::app_path('libraries/' . $name)))
        {
            if (!is_dir($library_path = \Steam::path('libraries/' . $name)))
                throw new \Steam\Exception\FileNotFound('Could not locate ' . $name . ' library.');
        }
        
        if ($type == 'prefix')
            self::$autoloader->registerPrefix($name, $library_path);
        else
            self::$autoloader->registerNamespace($name, $library_path);
    }
}

?>
