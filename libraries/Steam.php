<?php
/**
 * Steam Class
 *
 * This class initializes and manages the Steam environment.
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


class Steam
{
    /**
     * Stores the framework version
     */
    private static $version = '';
    
    /**
     * Stores the name of the current application
     *
     * @type string
     */
    private static $app = '';
    
    /**
     * Stores the configuration values
     *
     * @type array
     */
    private static $config;
    
    /**
     * Stores the name of the current resource
     *
     * @type string
     */
    private static $resource = '';
    
    /**
     * Stores the type of the current resource (action, model, view, static)
     */
    private static $resource_type = '';
    
    /**
     * Stores a custom parameter for the resource
     *
     * @type string
     */
    private static $resource_param = '';
    
    /**
     * Stores the current request object
     *
     * @type Zend_Http_Request
     */
    public static $request;
    
    /**
     * Stores the current response object.
     *
     * @type Zend_Http_Response
     */
    public static $response;
    
    /**
     * Steam is a static class, and cannot be instantiated.
     *
     * @throws E_USER_ERROR
     * @return void
     */
    private function __construct()
    {
        trigger_error('The Steam class cannot be instantiated', E_USER_ERROR);
    }
    
    /**
     * Retrieves the version of Steam. 
     *
     * @return string version
     */
    public static function version()
    {
        return self::$version;
    }
    
    /**
     * Retrieves the name of the current application. 
     *
     * @return string application name
     */
    public static function app()
    {
        return self::$app;
    }
    
    /**
     * Generates an absolute path from a relative path relative to the base
     * directory.
     *
     * @return string absolute path
     * @param string $path relative path
     */
    public static function path($path = '')
    {
        return self::$config['base_dir'] . ltrim($path, '/');
    }
    
    /**
     * Generates an absolute path from a relative path relative to the current
     * applications directory.
     *
     * @return string
     * @param string $path relative path
     */
    public static function app_path($path = '')
    {
        return self::$config['app_dir'] . ltrim($path, '/');
    }
    
    /**
     * Generates an absolute URI from a relative URI relative to the base URI.
     * The outputted URI does not include the host name.
     *
     * @return string
     * @param string $path relative uri path
     */
    public static function uri($path)
    {
        return rtrim(self::$config['base_uri'], '/') . '/' . ltrim($path, '/');
    }
    
    /**
     * Generates a complete URI from a relative URI relative to the base URI.
     * The outputted URI includes the HTTP scheme and host name.
     *
     * @return string
     * @param string $path relative uri path
     */
    public static function full_uri($path)
    {
        return 'http://' . $_SERVER['HTTP_HOST'] . self::uri($path);
    }
    
    /**
     * DEPRECATED (Refer to \Steam\StaticResource)
     * Generates an absolute URI from a relative URI used to access static
     * resources served directly by the HTTP server. The outputted URI does not
     * include the host name.
     *
     * @return string
     * @param string $path relative uri path
     */
    public static function static_uri($path)
    {
        #return rtrim('/static/' . ltrim($path, '/'), '/');
        return \Steam\StaticResource::uri($path);
    }
    
    /**
     * Returns the URI used to request the current resource. The outputted URI
     * does not include the host name.
     *
     * @return string
     */
    public static function this_uri($new_query = NULL)
    {
        $old_query = (!empty($_SERVER['QUERY_STRING'])) ? '?' . $_SERVER['QUERY_STRING'] : '';
        
        if (!is_null($new_query) and $new_query)
        {
            if ($old_query)
            {
                parse_str(ltrim($old_query, '?&'), $old_params);
                parse_str(ltrim($new_query, '?&'), $new_params);
                $query_string = '?' . http_build_query(array_merge($old_params, $new_params));
            }
            else $query_string = $new_query;
        }
        else $query_string = $old_query;
        
        return preg_replace('~\\?.*~', '', $_SERVER['REQUEST_URI']) . $query_string;
    }
    
    /**
     * Returns the name of the current resource.
     *
     * @return string
     */
    public static function this_resource()
    {
        return self::$resource;
    }
    
    /**
     * Returns the type of the current resource.
     *
     * @return string
     */
    public static function this_resource_type()
    {
        return self::$resource_type;
    }
    
    /**
     * Returns the param for the current resource.
     *
     * @return string
     */
    public static function this_resource_param()
    {
        return self::$resource_param;
    }
    
    /**
     * Retrieves the value of the specific configuration setting.
     *
     * @throws Steam\Exception\General
     * @return mixed
     * @param string $key configuration setting name
     * @param mixed $value new configuration value *NOT IMPLEMENTED*
     */
    public static function config($key, $value = NULL)
    {
        if (!is_null($value)) return self::$config[$key] = $value;
        
        // check to see the configuration setting exists
        if (isset(self::$config[$key]))
        {
            return self::$config[$key];
        }
        else
        {
            throw new \Steam\Exception\General('There is no configuration option "' . $key . '".');
        }
    }
    
    /**
     * Helper method which executes the complete Steam sequence.
     *
     * @return void
     */
    public static function go()
    {
        // output buffering is critical for preventing output from being sent to
        // the client too early
        ob_start();
        
        // load the global config
        self::load_config();
        
        // initialize the Steam environment
        self::initialize();
        
        // map the request to a resource
        self::map_request();
        
        // dispatch the request to the resource
        self::dispatch();
        
        // trigger the steam-complete event signifying execution has completed
        \Steam\Event::trigger('steam-complete');
    }
    
    /**
     * Loads the global configuration file and stores its values.
     *
     * @return void
     */
    public static function load_config()
    {
        // set the default values for all settings
        $environment    = '';
        $base_uri       = '';
        $libraries      = '';
        $logs           = array('php');
        $error_page     = '';
        $static_maxage  = '30d';
        $static_path    = 'static';
        $fingerprinting = true;
        $cache_backend  = '';
        $cache_params   = array('cache_dir' => str_replace('libraries/Steam.php', 'cache/', __FILE__));
        $cache_session  = true;
        $db_adapter     = '';
        $db_params      = array();
        $portals        = array(array('app' => 'sample', 'domain' => '/.*/', 'path' => '/^.*/'));
        $initialize     = null;
        
        // load the global configuration file (replacing default values)
        include str_replace('libraries/Steam.php', 'config.php', __FILE__);
        
        // store the values in the static class variable
        self::$config = array(
            'base_dir'       => str_replace('libraries/Steam.php', '', __FILE__),
            'environment'    => $environment,
            'base_uri'       => $base_uri,
            'libraries'      => $libraries,
            'logs'           => $logs,
            'error_page'     => $error_page,
            'static_maxage'  => $static_maxage,
            'static_path'    => trim($static_path, '/'),
            'fingerprinting' => $fingerprinting,
            'cache_backend'  => $cache_backend,
            'cache_params'   => $cache_params,
            'cache_session'  => $cache_session,
            'db_adapter'     => $db_adapter,
            'db_params'      => $db_params,
            'portals'        => $portals,
            'initialize'     => $initialize,
        );
        
        self::$version = trim(file_get_contents(self::path('version')));
    }
    
    /**
     * Initializes the Steam environment by starting all necessary services
     * including the class autoloader, error and exception handlers, logging
     * facilities, caching, session, database connections, and plugins.
     *
     * @return void
     */
    public static function initialize()
    {
        // include the class loading manager
        include \Steam::path('libraries/Steam/Loader.php');
        
        // activate autoloading
        \Steam\Loader::initialize();
        
        // register global libraries
        foreach (self::$config['libraries'] as $library)
            \Steam\Loader::register($library);
        
        // initialize error and exception handling
        \Steam\Error::initialize();
        
        self::$request  = new \Zend\Http\PhpEnvironment\Request();
        self::$response = new \Zend\Http\PhpEnvironment\Response();
        
        // initialize logging services
        \Steam\Logger::initialize();
        
        foreach (self::$config['logs'] as $writer) \Steam\Logger::enable($writer);
        
        if (self::$config['cache_backend'])
        {
            // initialize caching with the configured backend and parameters
            \Steam\Cache::initialize(self::$config['cache_backend'], self::$config['cache_params']);
        }
        
        if (self::$config['db_adapter'])
        {
            // initialize the database with the configured adapter and parameters
            \Steam\Db::initialize(self::$config['db_adapter'], self::$config['db_params']);
        }
        
        \Steam\Plugin::initialize();
        
        if (is_callable(self::$config['initialize'])) \Steam\Event::register('steam-initialize', self::$config['initialize']);
        
        // Signal that initialization is complete
        \Steam\Event::trigger('steam-initialized');
    }
    
    /**
     * Maps the current request to a specific application and resource based on
     * the portal rules in the configuration file. This method internally calls
     * @set_request once complete.
     *
     * @return void
     */
    public static function map_request()
    {
        // construct the complete request URI
        $request_uri = \Zend\Uri\UriFactory::factory('http' . ((isset($_SERVER['HTTPS']) and $_SERVER['HTTPS']) ? 's' : '') . '://' . (($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_ADDR']) . $_SERVER['REQUEST_URI']);
        
        // define/intialize variables
        $matches;
        $portal;
        $app_name       = '';
        $resource_type  = '';
        $resource_name  = '';
        $resource_param = '';
        
        // iterate over portals until a match is found
        foreach (self::$config['portals'] as $portal)
        {
            // check if domain matches
            if (isset($portal['domain']) and !preg_match($portal['domain'], $request_uri->getHost()))
            {
                continue;
            }
            
            // check if path matches
            if (!preg_match($portal['path'], $request_uri->getPath(), $matches))
            {
                continue;
            }
            
            // both domain and path matched, map to this resource
            $app_name      = $portal['app'];
            $resource_type = $portal['type'];
            
            // if a custom handler has been set, use it
            if (isset($portal['resource']))
            {
                $resource_name  = $portal['resource'];
                $resource_param = isset($matches[1]) ? $matches[1] : '';
            }
            // if a formatter function has been defined to translate the request
            elseif (isset($portal['formatter']) and is_callable($portal['formatter']))
            {
                $resource_name = $portal['formatter']($request_uri->getPath());
                
                $resource = explode('?', $resource_name, 2);
                
                if (isset($resource[1]))
                {
                    $resource_name = $resource[0];
                    parse_str($resource[1], $request_vars);
                    
                    foreach ($request_vars as $key => $value)
                    {
                        $_REQUEST[$key] = $value;
                    }
                }
            }
            // otherwise attempt to match the contents of the first ()
            elseif (isset($matches[1]))
            {
                $resource_name = ltrim($matches[1], '/');
            }
            // grab the whole path after the fist slash
            elseif (is_int(strrpos('/', $request_uri->getPath())))
            {
                $resource_name = substr($request_uri->getPath(), strrpos('/', $request_uri->getPath()) + 1);
            }
            // grab the whole path
            else
            {
                $resource_name = $request_uri->getPath();
            }
            
            // if there is no resource name, set it to "default" if it's a view
            // this is the equivalent of the default index.html routing
            if (!$resource_name and $resource_type == 'view')
            {
                $resource_name = 'default';
            }
            elseif (preg_match('~index\\.(php|html|htm)$~', $resource_name))
            {
                self::$response->setRedirect(preg_replace('~index.php$~', '', $resource_name), 303);
                self::$response->sendResponse();
                exit;
            }
            
            // don't continue to match portals, match has been found
            break;
        }
        
        // set the request to the matched values
        self::set_request($app_name, $resource_type, $resource_name, $resource_param);
    }
    
    /**
     * Sets the current application, resource type, and resource name to the
     * values specified.
     *
     * @return void
     */
    public static function set_request($app_name, $resource_type, $resource_name, $resource_param = '')
    {
        self::$app            = $app_name;
        self::$resource_type  = $resource_type;
        self::$resource       = $resource_name;
        self::$resource_param = $resource_param;
    }
    
    /**
     * Dispatches the request to the current application and resource by first
     * loading the application and then handing off execution to the resource.
     *
     * @return void
     */
    public static function dispatch()
    {
        try
        {
            self::load_app();
            
            switch (self::$resource_type)
            {
                case 'view':
                    \Steam\View::display(self::$resource, self::$request, self::$response);
                    break;
                case 'model':
                    \Steam\Model::display(self::$resource . '?' . $_SERVER['QUERY_STRING'], self::$request, self::$response);
                    break;
                case 'action';
                    \Steam\Action::perform(self::$resource, self::$request, self::$response);
                    break;
                case 'static';
                    \Steam\StaticResource::display(self::$resource, self::$request, self::$response);
                    break;
            }
        }
        catch (\Steam\Exception\AppNotFound $exception)
        {
            \Steam\Error::log_exception($exception);
            
            return \Steam\Error::display(500, $exception->getMessage());
        }
        catch (\Steam\Exception\FileNotFound $exception)
        {
            \Steam\Error::log_exception($exception);
            
            return \Steam\Error::display(404, $exception->getMessage());
        }
        catch (\Steam\Exception\General $exception)
        {
            \Steam\Error::log_exception($exception);
            
            return \Steam\Error::display(500, $exception->getMessage());
        }
        catch (\Exception $exception)
        {
            \Steam\Error::log_exception($exception);
            
            return \Steam\Error::display(500, $exception->getMessage());
        }
    }
    
    /**
     * Loads the current application and application specific configuration
     * values from the application config.
     *
     * @return void
     */
    public static function load_app()
    {
        // load the application config, overriding global config values
        self::$config['app_dir'] = self::$config['base_dir'] . 'apps/' . self::$app . '/';
        
        try
        {
            // set the current values for settings
            $base_uri       = self::$config['base_uri'];
            $error_page     = self::$config['error_page'];
            $static_maxage  = self::$config['static_maxage'];
            $static_path    = self::$config['static_path'];
            $fingerprinting = self::$config['fingerprinting'];
            $cache_session  = self::$config['cache_session'];
            
            // load the application configuration file (replacing current values)
            include_once self::$config['app_dir'] . 'config.php';
            
            // if additional logs are defined, enable them and update the config var
            if (isset($logs))
            {
                foreach ($logs as $writer)
                {
                    self::$config['logs'][] = $writer;
                    
                    \Steam\Logger::enable($writer);
                }
            }
            
            // if additional libraries are defined, add them and update the config var
            if (isset($libraries))
            {
                foreach ($libraries as $library)
                {
                    self::$config['libraries'][] = $library;
                    
                    \Steam\Loader::register($library, self::app_path('/'));
                }
            }
            
            // if there is a different db adapter, update it
            if (isset($db_adapter) and isset($db_params))
            {
                self::$config['db_adapter'] = $db_adapter;
                self::$config['db_params']  = $db_params;
                
                \Steam\Db::initialize($db_adapter, $db_params);
            }
            
            // update the settings in the static class variable
            self::$config['base_uri']       = $base_uri;
            self::$config['error_page']     = $error_page;
            self::$config['static_maxage']  = $static_maxage;
            self::$config['static_path']    = trim($static_path, '/');
            self::$config['fingerprinting'] = $fingerprinting;
            self::$config['cache_session']  = $cache_session;
        }
        catch (\Steam\Exception\FileNotFound $exception)
        {
            //config file doesn't exist, ignore and continue
        }
        
        if (self::$config['cache_backend'] and self::$config['cache_session'])
        {
            if (self::$config['cache_session'] == 'zend')
            {
                // configure Zend_Session to use a custom cache based save handler
                \Zend_Session::setSaveHandler(new \Steam\Session\SaveHandler\Cache());
            }
            else
            {
                // override PHP's session handling to use a custom cache based handler
                \Steam\Session\Handler::initialize();
            }
        }
        
        try
        {
            // load the application class
            include_once self::$config['app_dir'] . self::$app . '.php';
            
            // construct the expected class name
            $app_class = ucfirst(self::$app) . 'Application';
            
            // make sure the applicatino class extends Steam\Application
            if (get_parent_class($app_class) != 'Steam\\Application')
            {
                throw new \Steam\Exception\General('The application could not be loaded properly.');
            }
            
            // instantiate application class
            $app = new $app_class();
            
            // initialize application
            $app->initialize();
        }
        catch (\Steam\Exception\FileNotFound $exception)
        {
            \Steam\Error::log_exception($exception);
            
            throw new \Steam\Exception\AppNotFound('The application could not be found.');
        }
    }
}

?>
