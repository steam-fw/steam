<?php
/**
 * Steam Web Class
 *
 * This class loads web resources.
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

class Steam_Web
{
    /**
     * The Steam base URI, the URI of the directory that contains index.php
     */
    protected static $base_uri;
    protected static $headers_sent = false;
    protected static $headers      = array();
    protected static $body         = '';
    
    /**
     * The current portal through which Steam is being accessed.
     */
    protected static $portal;
    
    
    /**
     * Sets or retrieves the base URI. The base URI can only be set once. An
     * exception is thrown if an attempt is made to change it.
     *
     * @throws Steam_Exception_General
     * @return void|string base URI
     * @param string base URI
     */
    public static function base_uri($base_uri = NULL)
    {
        if (!is_null($base_uri))
        {
            if (!is_null(self::$base_uri))
            {
                throw new Steam_Exception_General();
            }
            else
            {
                self::$base_uri = $base_uri;
            }
        }
        
        return self::$base_uri;
    }
    
    /**
     * Converts a URI relative to the base URI into an absolute URI.
     *
     * @return string absolute uri
     * @param string relative uri
     */
    public static function uri($path = '')
    {
        return self::$base_uri . '/' . ltrim($path, '/');
    }
    
    /**
     * Loads a resource based on the given resource code. Resource code
     * defaults to "default". If the portal targets the API, the request is
     * processed through the api.
     *
     * @return void
     * @param object $portal Steam_Web_Portal object
     */
    public static function load(Steam_Web_Portal $portal)
    {
        // store the loaded portal
        self::$portal = $portal;
        
        // set the app environment variables
        Steam_Application::load($portal->app_name());
        Steam_Application::id($portal->app_id());
        Steam_Application::base_uri($portal->app_uri());
        
        // if the portal is for api requests, process the request and return response
        if ($portal->api())
        {
            return self::process_api_request($portal);
        }
        
        $page_name = $portal->resource_name();
        
        try
        {
            // try to load the global page if it exists
            include Steam::path('apps/' . Steam_Application::name() . '/pages/global.php');
        }
        catch (Steam_Exception_FileNotFound $exception)
        {
            // no global page, continue
        }
        catch (Exception $exception)
        {
            Steam_Error::log_exception($exception);
            
            // set the HTTP status code and message
            self::header('HTTP/1.1 500 ' . Zend_Http_Response::responseCodeAsText(500));
            
            // if the global resource raised an exception, display an error page
            include Steam::path('apps/global/error_pages/HTTP_500.php');
            return;
        }
        
        try
        {
            // try to load the requested page
            include Steam::path('apps/' . Steam_Application::name() . '/pages/' . $page_name . '.php');
            return;
        }
        catch (Steam_Exception_FileNotFound $exception)
        {
            // set the HTTP status code and message
            self::header('HTTP/1.1 404 ' . Zend_Http_Response::responseCodeAsText(404));
            
            // if it's not found, display the 404 error page
            include Steam::path('apps/global/error_pages/HTTP_404.php');
            return;
        }
        catch (Exception $exception)
        {
            Steam_Error::log_exception($exception);
            
            // set the HTTP status code and message
            self::header('HTTP/1.1 500 ' . Zend_Http_Response::responseCodeAsText(500));
            
            // if it raised an exception, show the 500 error page
            include Steam::path('apps/global/error_pages/HTTP_500.php');
            return;
        }
    }
    
    /**
     * Processes API requests using the data manipulation scripts and outputs
     * the response directly to the browser.
     *
     * @return void
     * @param object Steam_Web_URI
     */
    protected static function process_api_request(Steam_Web_Portal $portal)
    {
        // perform any special tasks for the type of method
        switch ($_SERVER['REQUEST_METHOD'])
        {
            case 'POST':
                $method = 'create';
                // translate the xml in the request to a query object
                try
                {
                    $query = new Steam_Data_Query(Steam_Web::raw_request());
                }
                catch (Steam_Exception_Type $exception)
                {
                    $query = new Steam_Data_Query($_POST);
                }
                break;
            case 'GET':
                $method = 'retrieve';
                // translate the get variables in the request to a query object
                $query = new Steam_Data_Query($_GET);
                break;
            case 'PUT':
                $method = 'update';
                // translate the xml in the request to a query object
                $query = new Steam_Data_Query(Steam_Web::raw_request());
                break;
            case 'DELETE':
                $method = 'delete';
                break;
        }
        
        // perform the actual request
        $response = Steam_Data::request($method, $portal->resource_name(), $query);
        
        // output the status of the response
        self::header('HTTP/1.1 ' . $response->status . ' ' . Zend_Http_Response::responseCodeAsText(intval($response->status)));
        
        // output any special headers or data for specific methods
        if ($method == 'create' and $response->status == 201)
        {
            // output the location of the newly created resource
            self::header('Location', $response->location);
        }
        elseif ($method == 'retrieve' and $response->status == 200)
        {
            // output an xml representation of the data
            self::header('Content-Type', 'text/xml; charset=utf-8');
            self::body($response->asXML());
        }
        elseif ($response->error)
        {
            // if there is an error message, output that in the body
            self::body($response->error);
        }
        
        self::send_response();
    }
    
    /**
     * Retrieves the current portal object.
     *
     * @return object Steam_Web_Portal object
     */
    public static function portal()
    {
        return self::$portal;
    }
    
    /**
     * Adds a header to the queue of headers to be sent. Headers are sent when
     * when send_headers or send_response is called.
     *
     * @return void
     * @param $header string header name
     * @param $value  string header value
     */
    public static function header($header, $value = NULL)
    {
        if (is_null($value))
        {
            self::$headers[] = $header;
        }
        else
        {
            self::$headers[strtolower($header)] = $header . ': ' . $value;
        }
    }
    
    /**
     * Sends the queued headers to the client.
     *
     * @return void
     */
    public static function send_headers()
    {
        foreach (self::$headers as $header)
        {
            header($header);
        }
        
        self::$headers_sent = true;
    }
    
    /**
     * Sets the body of the response. The body is sent when send_response is
     * called.
     *
     * @return void
     * @param $body string response body
     */
    public static function body($body)
    {
        self::$body = $body;
        self::header('Content-Length', strlen($body));
    }
    
    
    /**
     * Sends the body and headers to the client.
     *
     * @return void
     */
    public static function send_response()
    {
        if (!self::$headers_sent)
        {
            self::send_headers();
        }
        
        echo self::$body;
        
        self::$body = '';
    }
    
    /**
     * Retrieves the value of the specifed variable from the _REQUEST
     * superglobal array. If it is not set, it returns the second parameter or
     * a blank string if it is not specified.
     *
     * @return mixed
     * @param string $var variable name
     * @param mixed $default default value
     */
    public static function request($var, $default = '')
    {
        return (isset($_REQUEST[$var])) ? $_REQUEST[$var] : $default;
    }
    
    /**
     * Retrieves the value of the specifed variable from the _POST superglobal
     * array. If it is not set, it returns the second parameter or a blank
     * string if it is not specified.
     *
     * @return mixed
     * @param string $var variable name
     * @param mixed $default default value
     */
    public static function post($var, $default = '')
    {
        return (isset($_POST[$var])) ? $_POST[$var] : $default;
    }
    
    /**
     * Retrieves the value of the specifed variable from the _GET superglobal
     * array. If it is not set, it returns the second parameter or a blank
     * string if it is not specified.
     *
     * @return mixed
     * @param string $var variable name
     * @param mixed $default default value
     */
    public static function get($var, $default = '')
    {
        return (isset($_GET[$var])) ? $_GET[$var] : $default;
    }
    
    /**
     * Retrieves any and all data that was passed in the body of the current
     * request as a string.
     *
     * @return string request data
     */
    public static function raw_request()
    {
        return file_get_contents('php://input');
    }
    
    /**
     * Redirects the user to the specified page. The page name should not
     * include any other part of the uri.
     *
     * @return void
     * @param string $page page name
     */
    public static function redirect($page = '', $set_referrer = true)
    {
        if ($set_referrer)
        {
            $session = new Zend_Session_Namespace('ridazz');
            $session->referrer = self::$portal->resource_name();
        }
        
        $page = (string) $page;
        Steam_Web::header('HTTP/1.1 303 See Other');
        
        if ($page[0] == '/' or substr($page, 0, 7) == 'http://')
        {
            Steam_Web::header('Location', $page);
        }
        else
        {
            Steam_Web::header('Location', Steam_Application::uri($page));
        }
        
        Steam_Web::send_response();
        exit;
    }
}

?>
