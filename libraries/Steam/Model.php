<?php
/**
 * Steam Model Manipulation Class
 *
 * This class provides an interface for manipulating stored model/resources.
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
            else
            {
                $request->application = \Steam::app();
            }
            
            if (isset($resource_components[2]) and !empty($resource_components[3]))
            {
                $request->parameters = ltrim($resource_components[3], '/?');
                
                if ($method == 'delete' and $request->count() === 0)
                {
                    parse_str((string) $request->parameters, $item);
                    $request->add_item($item);
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
            
            if (!is_subclass_of($model_class, 'Steam\\Model'))
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
                    if (isset($request->parameters))
                    {
                        parse_str((string) $request->parameters), $item);
                        $item = (object) $item;
                    }
                    else $item = NULL;
                    
                    // check to see if the client is allowed to access the resource
                    if (!$model_class::is_allowed($request->method, $item))
                    {
                        throw new \Steam\Exception\Access();
                    }
                }
            }
            
            // call the method
            $method = '_' . $request->method;
            
            $model_class::$method($request, $response);
        }
        // if the class threw an exception targeted at users
        catch (Steam\Exception\User $exception)
        {
            \Steam\Error::log_exception($exception, \Zend_Log::INFO);
            $response->status = 400;
            $response->error  = $exception->getMessage();
        }
        // if there are access requirements which were not fulfilled
        catch (\Steam\Exception\Access $exception)
        {
            \Steam\Error::log_exception($exception, \Zend_Log::INFO);
            $response->error = $exception->getMessage();
            $response->status = 401;
        }
        // if the method isn't implemented
        catch (\Steam\Exception\MethodNotImplemented $exception)
        {
            \Steam\Error::log_exception($exception, \Zend_Log::INFO);
            $response->error = $exception->getMessage();
            $response->status = 405;
        }
        // if the file doesn't exist
        catch (\Steam\Exception\FileNotFound $exception)
        {
            \Steam\Error::log_exception($exception, \Zend_Log::INFO);
            $response->error = $exception->getMessage();
            $response->status = 404;
        }
        // catch all other exceptions and return the error in the response
        catch (\Exception $exception)
        {
            \Steam\Error::log_exception($exception);
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
                $method  = 'retrieve';
                $request = new \Steam\Model\Request();
                
                if (isset($_GET['response_format'])) $request->response_format = $_GET['response_format'];
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
        
        // set the meta information in the headers as well
        $response->setHeader('X-Steam-Total-Results', $response_xml->total_results);
        $response->setHeader('X-Steam-Total-Items',   $response_xml->total_items  );
        $response->setHeader('X-Steam-Start-Index',   $response_xml->start_index  );
        
        if (!isset($request->response_format)) $request->response_format = 'xml';
        
        switch ($request->response_format)
        {
            case 'xml':
                // output an xml representation of the data
                $response->setHeader('Content-Type', 'text/xml; charset=utf-8', true);
                $response->setBody($response_xml->asXML());
                break;
            case 'json':
                // output an json representation of the data
                $response->setHeader('Content-Type', 'text/javascript; charset=utf-8', true);
                $response->setBody($response_xml->asJSON());
                break;
            case 'jsonp':
                // output an json representation of the data
                $callback = (isset($_REQUEST['jsonp'])) ? $_REQUEST['jsonp'] : 'jsonp';
                $response->setHeader('Content-Type', 'text/javascript; charset=utf-8', true);
                $response->setBody($response_xml->asJSONP($callback));
                break;
            case 'csv':
                // output an csv representation of the data
                $response->setHeader('Content-Type', 'text/csv; charset=utf-8', true);
                $response->setHeader('Content-Disposition', 'attachment; filename=' . preg_replace('~\\?.*$~', '', $resource_name) . '.csv', true);
                $response->setBody($response_xml->asCSV());
                break;
            default:
                // output a custom representation of the data
                if (preg_match('~^([^(]*)\\((.*)\\)$~', $request->response_format, $matches))
                {
                    $response->setHeader('Content-Type', 'text/plain; charset=utf-8', true);
                    
                    if (empty($matches[2]))
                    {
                        $response->setBody(call_user_func($matches[1], $response_xml));
                    }
                    else
                    {
                        $response->setBody(call_user_func($matches[1], $matches[2], $response_xml));
                    }
                }
                else
                {
                    // fallback to XML output
                    $response->setHeader('Content-Type', 'text/xml; charset=utf-8', true);
                    $response->setBody($response_xml->asXML());
                }
        }
        
        \Steam\Event::trigger('steam-response');
        ob_clean();
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
