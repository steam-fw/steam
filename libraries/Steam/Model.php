<?php
/**
 * Steam Model Manipulation Class
 *
 * This class provides an interface for manipulating stored model/resources.
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

namespace Steam;

class Model
{
    /**
     * Identifies whether the current request is privileged or not.
     *
     * @type boolean
     */
    private static $privileged_request = false;
    
    /**
     * Performs a create model request.
     *
     * @see request
     * @return object Steam\Model\Response
     * @param string $resource resource identifier
     * @param object $request Steam\Model\Request | array item
     */
    public static function create($resource, $request)
    {
        if (is_array($request))
        {
            $item = $request;
            
            $request = new \Steam\Model\Request();
            
            $request->add_item($item);
            
            unset($item);
        }
        
        $response = self::request('create', $resource, $request);
        
        return $response;
    }
    
    /**
     * Performs a retrieve model request.
     *
     * @see request
     * @return object Steam\Model\Response
     * @param string $resource resource identifier
     * @param object $request Steam\Model\Request
     */
    public static function retrieve($resource, \Steam\Model\Request $request = NULL)
    {
        $response = self::request('retrieve', $resource, $request);
        
        return $response;
    }
    
    /**
     * Performs an update model request.
     *
     * @see request
     * @return object Steam\Model\Response
     * @param string $resource resource identifier
     * @param object $request Steam\Model\Request | array item
     */
    public static function update($resource, $request)
    {
        if (is_array($request))
        {
            $item = $request;
            
            $request = new \Steam\Model\Request();
            
            $request->add_item($item);
            
            unset($item);
        }
        
        $response = self::request('update', $resource, $request);
        
        return $response;
    }
    
    /**
     * Performs a delete model request.
     *
     * @see request
     * @return object Steam\Model\Response
     * @param string $resource resource identifier
     */
    public static function delete($resource)
    {
        $response = self::request('delete', $resource);
        
        return $response;
    }
    
    
    /**
     * Performs a model manipulation request.
     *
     * @return object Steam\Model\Response
     * @param string $method model method {create, retrieve, update, delete}
     * @param string $resource resource identifier
     * @param object $request Steam\Model\Request
     */
    public static function request($method, $resource, \Steam\Model\Request $request = NULL)
    {
        if (is_null($request))
        {
            $request = new \Steam\Model\Request();
        }
        
        $response = new \Steam\Model\Response();
        
        // check the resource identifier to make sure it's valid
        // the first piece of the identifier is the app name
        // the second piece is the name of the resource
        // the third piece is an optional identifier
        if (preg_match('~^(/?[^/?]+)(/[^?]*)?(\\?.*)?$~', $resource, $resource_components))
        {
            $request->method     = $method;
            $request->model_name = $resource_components[1];
            
            // the model is located in another application
            if (isset($resource_components[2]) and !empty($resource_components[2]))
            {
                $request->application = trim($resource_components[1], '/');
                $request->model_name  = trim($resource_components[2], '/');
            }
            
            if (isset($resource_components[2]) and !empty($resource_components[3]))
            {
                $request->parameters = ltrim($resource_components[3], '/?');
                
                switch ($request->count())
                {
                    case 0:
                        $item = http_parse_query((string) $request->parameters);
                        $request->add_item($item);
                        break;
                    case 1:
                        foreach (http_parse_query((string) $request->parameters) as $name => $value)
                        {
                            if (!isset($request[0]->{$name}))
                            {
                                $request[0]->{$name} = $value;
                            }
                        }
                        break;
                }
            }
            
            self::_request($request, $response);
        }
        else
        {
            $response->status = 400;
        }
        
        return $response;
    }
    
    /**
     * Performs a privileged model manipulation request which bypasses standard
     * access controls. This can only be called internally by php scripts to
     * allow certain methods to be protected from http requests.
     *
     * @return object Steam\Model\Response
     * @param string $method model method {create, retrieve, update, delete}
     * @param string $resource resource identifier
     * @param object $request Steam\Model\Request
     */
    public static function privileged_request($method, $resource, \Steam\Model\Request $request = NULL)
    {
        self::$privileged_request = true;
        
        $response = self::request($method, $resource, $request);
        
        self::$privileged_request = false;
        
        return $response;
    }
    
    private static function _request(&$request, &$response)
    {
        try
        {
            // include the script which contains the model manipulation code
            include_once \Steam::path('apps/' . $request->application . '/models/' . $request->model_name . '.php');
            
            $model_class = ucfirst($request->model_name) . 'Model';
            
            if (get_parent_class($model_class) != 'Steam\\Model')
            {
                throw new \Steam\Exception\General('The model could not be loaded properly.');
            }
            
            $items = $request->count();
            
            if (!self::$privileged_request)
            {
                if ($items > 0)
                {
                    for ($i = 0; $i < $items; $i++)
                    {
                        // check to see if the client is allowed to access the resource
                        if (!$model_class::is_allowed($request->method, $request[$i]))
                        {
                            throw new \Steam\Exception\Access();
                        }
                    }
                }
                else
                {
                    // check to see if the client is allowed to access the resource
                    if (!$model_class::is_allowed($request->method))
                    {
                        throw new \Steam\Exception\Access();
                    }
                }
            }
            
            // call the method
            $method = '_' . $request->method;
            
            $model_class::$method($request, $response);
        }
        // if there are access requirements which were not fulfilled
        catch (\Steam\Exception\Access $exception)
        {
            $response->error = $exception->getMessage();
            $response->status = 401;
        }
        // if the method isn't implemented
        catch (\Steam\Exception\MethodNotImplemented $exception)
        {
            $response->error = $exception->getMessage();
            $response->status = 405;
        }
        // if the file doesn't exist
        catch (\Steam\Exception\FileNotFound $exception)
        {
            $response->error = $exception->getMessage();
            $response->status = 404;
        }
        // catch all other exceptions and return the error in the response
        catch (\Exception $exception)
        {
            $response->error = $exception->getMessage();
            $response->status = 500;
        }
    }
    
    public static function display($resource_name, $request, $response)
    {
        // perform any special tasks for the type of method
        switch ($request->getMethod())
        {
            case 'POST':
                $method = 'create';
                // translate the xml in the request to a request object
                try
                {
                    $request = new \Steam\Model\Request($request->getRawBody());
                }
                catch (\Steam\Exception\Type $exception)
                {
                    $request = new \Steam\Model\Request($_POST);
                }
                break;
            case 'GET':
            case 'HEAD':
                $method = 'retrieve';
                // translate the get variables in the request to a request object
                $request = new \Steam\Model\Request($_GET);
                break;
            case 'PUT':
                $method = 'update';
                // translate the xml in the request to a request object
                $request = new \Steam\Model\Request($request->getRawBody());
                break;
            case 'DELETE':
                $method = 'delete';
                $request = new \Steam\Model\Request();
                break;
            default:
                $method = '';
                $request = new \Steam\Model\Request();
        }
        
        // perform the actual request
        $response_xml = \Steam\Model::request($method, $resource_name, $request);
        
        // output the status of the response
        $response->setRawHeader('HTTP/1.1 ' . $response_xml->status . ' ' . \Zend_Http_Response::responseCodeAsText(intval($response_xml->status)));
        
        switch ($request->response_format)
        {
            case 'xml':
                // output an xml representation of the data
                $response->setHeader('Content-Type', 'text/xml; charset=utf-8');
                $response->setBody($response_xml->asXML());
                break;
            case 'json':
                // output an xml representation of the data
                $response->setHeader('Content-Type', 'text/javascript; charset=utf-8');
                $response->setBody($response_xml->asJSON());
                break;
            case 'jsonp':
                // output an xml representation of the data
                $response->setHeader('Content-Type', 'text/javascript; charset=utf-8');
                $response->setBody($response_xml->asJSONP());
                break;
            default:
                // output an xml representation of the data
                $response->setHeader('Content-Type', 'text/xml; charset=utf-8');
                $response->setBody($response_xml->asFormat($request->response_format));
        }
        
        \Steam\Event::trigger('steam-response');
        
        $response->sendResponse();
    }
    
    protected static function is_allowed($method, $item = NULL)
    {
        return true;
    }
    
    protected static function _create(\Steam\Model\Request &$request, \Steam\Model\Response &$response)
    {
        throw new \Steam\Exception\MethodNotImplemented();
    }
    
    protected static function _update(\Steam\Model\Request &$request, \Steam\Model\Response &$response)
    {
        throw new \Steam\Exception\MethodNotImplemented();
    }
    
    protected static function _retrieve(\Steam\Model\Request &$request, \Steam\Model\Response &$response)
    {
        throw new \Steam\Exception\MethodNotImplemented();
    }
    
    protected static function _delete(\Steam\Model\Request &$request, \Steam\Model\Response &$response)
    {
        throw new \Steam\Exception\MethodNotImplemented();
    }
    
    public static function shutdown()
    {
    }
    
}

?>
