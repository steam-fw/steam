<?php
/**
 * Steam Class
 *
 * Copyright 2008-2010 Shaddy Zeineddine
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
 * @copyright 2008-2010 Shaddy Zeineddine
 * @license http://www.gnu.org/licenses/gpl.txt GPL v3 or later
 * @link http://code.google.com/p/steam-fw
 */


class Steam
{
    private static $app = '';
    private static $config;
    private static $resource = '';
    public static $request;
    public static $response;
    
    private function __construct()
    {
        trigger_error('The Steam class cannot be instantiated', E_USER_ERROR);
    }
    
    public static function app()
    {
        return self::$app;
    }
    
    public static function path($path)
    {
        return self::$config['base_dir'] . ltrim($path, '/');
    }
    
    public static function app_path($path)
    {
        return self::$config['app_dir'] . ltrim($path, '/');
    }
    
    public static function uri($path)
    {
        return rtrim(self::$config['base_uri'], '/') . '/' . ltrim($path, '/');
    }
    
    public static function this_uri()
    {
        return rtrim(self::$config['base_uri'], '/') . '/' . ltrim(self::$resource, '/');
    }
    
    public static function static_uri($path)
    {
        return rtrim('/' . self::$app . '/' . ltrim($path, '/'), '/');
    }
    
    public static function go()
    {
        // first thing's first, begin output buffering
        ob_start();
        
        self::load_config();
        
        self::initialize();
        
        self::dispatch();
        
        \Steam\Event::trigger('steam-complete');
    }
    
    private static function load_config()
    {
        $locale        = 'en_US.utf8';
        $timezone      = 'America/Los_Angeles';
        $base_uri      = '';
        $libraries     = '';
        $logs          = array('php');
        $cache_backend = 'File';
        $cache_params  = array('cache_dir' => 'cache/');
        $db_adapter    = '';
        $db_params     = array();
        $portals       = array(array('app' => 'sample', 'domain' => '/.*/', 'path' => '/^.*/'));
        
        include str_replace('libraries/Steam.php', 'config.php', __FILE__);

        self::$config = array(
            'base_dir'      => str_replace('libraries/Steam.php', '', __FILE__),
            'locale'        => $locale,
            'timezone'      => $timezone,
            'base_uri'      => $base_uri,
            'libraries'     => $libraries,
            'logs'          => $logs,
            'cache_backend' => $cache_backend,
            'cache_params'  => $cache_params,
            'db_adapter'    => $db_adapter,
            'db_params'     => $db_params,
            'portals'       => $portals,
        );
    }
    
    private static function initialize()
    {
        // add the Steam library path to the include path
        set_include_path(self::$config['base_dir'] . 'libraries' . PATH_SEPARATOR . get_include_path());
        
        // include the Loader classes
        include_once 'Steam/Loader.php';
        
        // activate the autoloader and register the custom autoloader
        \Steam\Loader::initialize();
        
        // initialize error and exception handling
        \Steam\Error::initialize();
        
        self::$request  = new \Zend_Controller_Request_Http();
        self::$response = new \Zend_Controller_Response_Http();
        
        // initialize logging services
        \Steam\Logger::initialize();
        
        foreach (self::$config['logs'] as $writer)
        {
            \Steam\Logger::enable($writer);
        }
        
        // initialize caching with the configured backend and parameters
        \Steam\Cache::initialize(self::$config['cache_backend'], self::$config['cache_params']);
        
        // initialize localization support
        \Steam\Locale::initialize(self::$config['locale'], self::$config['timezone']);
        
        // configure Zend_Session to use a custom cache based save handler
        \Zend_Session::setSaveHandler(new \Steam\Session());
        
        if (self::$config['db_adapter'])
        {
            // initialize the database with the configured adapter and parameters
            \Steam\Db::initialize(self::$config['db_adapter'], self::$config['db_params']);
        }
        
        \Steam\Plugin::initialize();
        
        \Steam\Event::trigger('steam-initialized');
    }
    
    private static function dispatch()
    {
        $request_uri = \Zend_Uri_Http::fromString('http' . ((isset($_SERVER['HTTPS']) and $_SERVER['HTTPS']) ? 's' : '') . '://' . (($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_ADDR']) . $_SERVER['REQUEST_URI']);
        
        $matches;
        $portal;
        $app_name      = '';
        $resource_name = '';
        $resource_type = '';
        
        foreach (self::$config['portals'] as $portal)
        {
            if (isset($portal['domain']) and !preg_match($portal['domain'], $request_uri->getHost()))
            {
                continue;
            }
            
            if (!preg_match($portal['path'], $request_uri->getPath(), $matches))
            {
                continue;
            }
            
            $app_name      = $portal['app'];
            $resource_type = $portal['type'];
            
            if (isset($portal['resource']))
            {
                $resource_name = $portal['resource'];
            }
            elseif (isset($portal['formatter']) and is_callable($portal['formatter']))
            {
                $resource_name = $portal['formatter']($request_uri->getPath());
            }
            elseif (isset($matches[1]))
            {
                $resource_name = $matches[1];
            }
            elseif (is_int(strrpos('/', $request_uri->getPath())))
            {
                $resource_name = substr($request_uri->getPath(), strrpos('/', $request_uri->getPath()) + 1);
            }
            else
            {
                $resource_name = $request_uri->getPath();
            }
            
            if (!$resource_name and $resource_type == 'view')
            {
                $resource_name = 'default';
            }
            
            break;
        }
        
        self::$resource = $resource_name;
        
        unset($matches);
        unset($portal);
        
        try
        {
            self::load_app($app_name);
            
            switch ($resource_type)
            {
                case 'view':
                    \Steam\View::display($resource_name, self::$request, self::$response);
                    break;
                case 'model':
                    \Steam\Model::display($resource_name . '?' . $_SERVER['QUERY_STRING'], self::$request, self::$response);
                    break;
                case 'action';
                    \Steam\Action::perform($resource_name, self::$request, self::$response);
                    break;
            }
        }
        catch (\Steam\Exception\AppNotFound $exception)
        {
            return \Steam\Error::display(500, $exception->getMessage());
        }
        catch (\Steam\Exception\FileNotFound $exception)
        {
            return \Steam\Error::display(404, $exception->getMessage());
        }
        catch (\Steam\Exception\General $exception)
        {
            return \Steam\Error::display(500, $exception->getMessage());
        }
        catch (\Exception $exception)
        {
            return \Steam\Error::display(500, $exception->getMessage());
        }
    }
    
    public static function load_app($app)
    {
        self::$app = $app;
        
        self::$config['app_dir'] = self::$config['base_dir'] . 'apps/' . self::$app . '/';
        
        try
        {
            include_once self::$config['app_dir'] . 'config.php';
            
            if (isset($logs))
            {
                foreach ($logs as $writer)
                {
                    self::$config['logs'][] = $writer;
                    
                    \Steam\Logger::enable($writer);
                }
            }
            
            if (isset($libraries))
            {
                foreach ($libraries as $library)
                {
                    self::$config['libraries'][] = $library;
                    
                    \Steam\Loader::register($library, self::$app_base_dir);
                }
            }
            
            if (isset($timezone))
            {
                self::$config['timezone'] = $timezone;
                
                \Steam\Locale::set_timezone($timezone);
            }
            
            if (isset($locale))
            {
                self::$config['locale'] = $locale;
                
                \Steam\Locale::set_locale($locale);
            }
            
            if (isset($db_adapter) and isset($db_params))
            {
                self::$config['db_adapter'] = $db_adapter;
                self::$config['db_params']  = $db_params;
                
                \Steam\Db::initialize($db_adapter, $db_params);
            }
            
            if (isset($base_uri))
            {
                self::$config['base_uri'] = $base_uri;
            }
        }
        catch (\Steam\Exception\FileNotFound $exception)
        {
            //ignore
        }
        
        try
        {
            include_once self::$config['app_dir'] . self::$app . '.php';
            
            $app_class = ucfirst(self::$app) . 'Application';
            
            if (get_parent_class($app_class) != 'Steam\\Application')
            {
                throw new \Steam\Exception\General('The application could not be loaded properly.');
            }
            
            $app = new $app_class();
            
            $app->initialize();
        }
        catch (\Steam\Exception\FileNotFound $exception)
        {
            throw new \Steam\Exception\AppNotFound('The application could not be found.');
        }
    }
}

?>
