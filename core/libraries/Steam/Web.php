<?php
/**
 * Steam Web Class
 *
 * This class loads pages.
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
     * Loads a page based on the given page code. Page code defaults to
     * "default".
     *
     * @return void
     * @param object $uri Steam_Web_URI object
     */
    public static function load(Steam_Web_URI $uri)
    {
        Steam::$app_id   = $uri->get_app_id();
        Steam::$app_name = $uri->get_app_name();
        
        if (Steam::$app_name == 'api')
        {
            return self::process_api_request($uri);
        }
        
        $page_name = $uri->get_page_name();
        
        if (!$page_name)
        {
            $page_name = 'default';
        }
        
        try
        {
            include Steam::$base_dir . 'apps/' . $uri->get_app_name() . '/pages/global.php';
        }
        catch (Steam_Exception_FileNotFound $exception)
        {
        }
        catch (Exception $exception)
        {
            include Steam::$base_dir . 'apps/global/error_pages/HTTP_500.php';
            return;
        }
        
        try
        {
            include Steam::$base_dir . 'apps/' . $uri->get_app_name() . '/pages/' . $page_name . '.php';
            return;
        }
        catch (Steam_Exception_FileNotFound $exception)
        {
            include Steam::$base_dir . 'apps/global/error_pages/HTTP_404.php';
            return;
        }
        catch (Exception $exception)
        {
            include Steam::$base_dir . 'apps/global/error_pages/HTTP_500.php';
            return;
        }
    }
    
    protected static function process_api_request(Steam_Web_URI $uri)
    {
        switch ($_SERVER['REQUEST_METHOD'])
        {
            case 'POST':
                $method = 'create';
                $query = Steam_Data_Query::from_xml(Steam_Web::raw_request());
                break;
            case 'GET':
                $method = 'retrieve';
                $query = Steam_Data_Query::from_array($_GET);
                break;
            case 'PUT':
                $method = 'update';
                $query = Steam_Data_Query::from_xml(Steam_Web::raw_request());
                break;
            case 'DELETE':
                $method = 'delete';
                break;
        }
        
        $result = Steam_Data::request($method, $uri->get_page_name(), $query);
        
        header('HTTP/1.1 ' . $result->status . ' ' . Zend_Http_Response::responseCodeAsText($result->status));
        
        if ($method == 'create' and $result->status == 201)
        {
            header('Location: ' . $result->items[0]['uri']);
        }
        elseif ($method == 'retrieve' and $result->status == 200)
        {
            $xml = $result->get_xml();
            header('Content-Type: text/xml; charset=utf-8');
            header('Content-Length: ' . strlen($xml));
            echo $xml;
        }
        elseif ($result->error)
        {
            echo $result->error;
        }
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
    
    public static function raw_request()
    {
        $http = fopen("php://input", "r");
        $body = stream_get_contents($http);
        fclose($http);
        
        return $body;
    }
}

?>
