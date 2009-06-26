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
    protected static $headers_sent = false;
    protected static $headers      = array();
    protected static $body         = '';
    protected static $uri;
    
    /**
     * Loads a resource based on the given resource code. Resource code
     * defaults to "default". If the URI targets the API, the request is
     * processed.
     *
     * @return void
     * @param object $uri Steam_Web_URI object
     */
    public static function load(Steam_Web_URI $uri)
    {
        // store the loaded uri
        self::$uri = $uri;
        
        // set the app environment variables
        Steam::$app_id   = $uri->app_id();
        Steam::$app_name = $uri->app_name();
        Steam::$app_uri  = $uri->app_uri();
        
        // if the app is the api, process the request and return response
        if (Steam::$app_name == 'api')
        {
            return self::process_api_request($uri);
        }
        
        $page_name = $uri->resource_name();
        
        try
        {
            // try to load the global page if it exists
            include Steam::$base_dir . 'apps/' . $uri->app_name() . '/pages/global.php';
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
            include Steam::$base_dir . 'apps/global/error_pages/HTTP_500.php';
            return;
        }
        
        try
        {
            // try to load the requested page
            include Steam::$base_dir . 'apps/' . $uri->app_name() . '/pages/' . $page_name . '.php';
            return;
        }
        catch (Steam_Exception_FileNotFound $exception)
        {
            // set the HTTP status code and message
            self::header('HTTP/1.1 404 ' . Zend_Http_Response::responseCodeAsText(404));
            
            // if it's not found, display the 404 error page
            include Steam::$base_dir . 'apps/global/error_pages/HTTP_404.php';
            return;
        }
        catch (Exception $exception)
        {
            Steam_Error::log_exception($exception);
            
            // set the HTTP status code and message
            self::header('HTTP/1.1 500 ' . Zend_Http_Response::responseCodeAsText(500));
            
            // if it raised an exception, show the 500 error page
            include Steam::$base_dir . 'apps/global/error_pages/HTTP_500.php';
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
    protected static function process_api_request(Steam_Web_URI $uri)
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
        $response = Steam_Data::request($method, $uri->resource_name(), $query);
        
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
    
    public static function uri()
    {
        return self::$uri;
    }
    
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
    
    public static function send_headers()
    {
        foreach (self::$headers as $header)
        {
            header($header);
        }
        
        self::$headers_sent = true;
    }
    
    public static function body($body)
    {
        self::$body = $body;
        self::header('Content-Length', strlen($body));
    }
    
    public static function send_body($body)
    {
        if (!self::$headers_sent)
        {
            self::send_headers();
        }
        
        echo $body;
    }
    
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
            $session->referrer = self::$uri->resource_name();
        }
        
        Steam_Web::header('HTTP/1.1 303 See Other');
        Steam_Web::header('Location', Steam::$app_uri . '/' . $page);
        Steam_Web::send_response();
        exit;
    }
}

?>
