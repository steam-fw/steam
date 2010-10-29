<?php
/**
 * Steam Model Manipulation Class
 *
 * This class provides an interface for manipulating stored model/resources.
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

namespace Steam;

class Model
{
    /**
     * Performs a create model request.
     *
     * @see request
     * @return object Steam\Model\Response
     * @param string $resource resource identifier
     * @param object $query Steam\Model\Query | array item
     */
    public static function create($resource, $query)
    {
        if (is_array($query))
        {
            $item = $query;
            
            $query = new \Steam\Model\Query();
            
            $query->add_item($item);
            
            unset($item);
        }
        
        $response = self::request('create', $resource, $query);
        
        return $response;
    }
    
    /**
     * Performs a retrieve model request.
     *
     * @see request
     * @return object Steam\Model\Response
     * @param string $resource resource identifier
     * @param object $query Steam\Model\Query
     */
    public static function retrieve($resource, \Steam\Model\Query $query = NULL)
    {
        $response = self::request('retrieve', $resource, $query);
        
        return $response;
    }
    
    /**
     * Performs an update model request.
     *
     * @see request
     * @return object Steam\Model\Response
     * @param string $resource resource identifier
     * @param object $query Steam\Model\Query | array item
     */
    public static function update($resource, $query)
    {
        if (is_array($query))
        {
            $item = $query;
            
            $query = new \Steam\Model\Query();
            
            $query->add_item($item);
            
            unset($item);
        }
        
        $response = self::request('update', $resource, $query);
        
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
     * @see request
     * @return object Steam\Model\Response
     * @param string $method model method {create, retrieve, update, delete}
     * @param string $resource resource identifier
     * @param object $query Steam\Model\Query
     */
    public static function request($method, $resource, \Steam\Model\Query $query = NULL)
    {
        if (is_null($query))
        {
            $query = new \Steam\Model\Query();
        }
        
        $response = new \Steam\Model\Response();
        
        // check the resource identifier to make sure it's valid
        // the first piece of the identifier is the app name
        // the second piece is the name of the resource
        // the third piece is an optional identifier
        if (preg_match('/^([^\\/\\?]+)(\\/.*)?(\\?.*)?$/', $resource, $resource_components))
        {
            $query->method     = $method;
            $query->model_name = $resource_components[1];
            
            if (!empty($resource_components[2]))
            {
                $query->resource_id = trim($resource_components[2], '/');
            }
            
            if (!empty($resource_components[3]))
            {
                $query->parameters = ltrim($resource_components[3], '/?');
                
                switch ($query->count())
                {
                    case 0:
                        $item = http_parse_query((string) $query->parameters);
                        $query->add_item($item);
                        break;
                    case 1:
                        foreach (http_parse_query((string) $query->parameters) as $name => $value)
                        {
                            if (!isset($query[0]->{$name}))
                            {
                                $query[0]->{$name} = $value;
                            }
                        }
                        break;
                }
            }
            
            self::_request($query, $response);
        }
        else
        {
            $response->status = 400;
        }
        
        return $response;
    }
    
    private static function _request(&$query, &$response)
    {
        try
        {
            // include the script which contains the model manipulation code
            include_once \Steam::app_path('models/' . $query->model_name . '.php');
            
            $model_class = ucfirst($query->model_name) . 'Model';
            
            if (get_parent_class($model_class) != 'Steam\\Model')
            {
                throw new \Steam\Exception\General('The model could not be loaded properly.');
            }
            
            $items = $query->count();
            
            if ($items > 0)
            {
                for ($i = 0; $i < $items; $i++)
                {
                    // check to see if the client is allowed to access the resource
                    if (!call_user_func($model_class . '::is_allowed', $query->method, $query[$i]))
                    {
                        throw new \Steam\Exception\Access();
                    }
                }
            }
            else
            {
                // check to see if the client is allowed to access the resource
                if (!call_user_func($model_class . '::is_allowed', $query->method))
                {
                    throw new \Steam\Exception\Access();
                }
            }
            
            // call the method
            call_user_func($model_class . '::' . '_' . $query->method, $query, $response);
        }
        // if there are access requirements which were not fulfilled
        catch (\Steam\Exception\Access $exception)
        {
            $response->error = $exception->getMessage();
            \Steam\Error::display(401, $exception->getMessage());
        }
        // if the method isn't implemented
        catch (\Steam\Exception\MethodNotImplemented $exception)
        {
            $response->error = $exception->getMessage();
            \Steam\Error::display(405, $exception->getMessage());
        }
        // if the file doesn't exist
        catch (\Steam\Exception\FileNotFound $exception)
        {
            $response->error = $exception->getMessage();
            \Steam\Error::display(404, $exception->getMessage());
        }
        // catch all other exceptions and return the error in the response
        catch (Exception $exception)
        {
            $response->error = $exception->getMessage();
            \Steam\Error::display(500, $exception->getMessage());
        }
    }
    
    public static function display($resource_name, $request, $response)
    {
        // perform any special tasks for the type of method
        switch ($request->getMethod())
        {
            case 'POST':
                $method = 'create';
                // translate the xml in the request to a query object
                try
                {
                    $query = new \Steam\Model\Query($request->getRawBody());
                }
                catch (\Steam\Exception\Type $exception)
                {
                    $query = new \Steam\Data\Query($_POST);
                }
                break;
            case 'GET':
            case 'HEAD':
                $method = 'retrieve';
                // translate the get variables in the request to a query object
                $query = new \Steam\Model\Query($_GET);
                break;
            case 'PUT':
                $method = 'update';
                // translate the xml in the request to a query object
                $query = new \Steam\Model\Query($request->getRawBody());
                break;
            case 'DELETE':
                $method = 'delete';
                $query = new \Steam\Model\Query();
                break;
            default:
                $method = '';
                $query = new \Steam\Model\Query();
        }
        
        // perform the actual request
        $response_xml = \Steam\Model::request($method, $resource_name, $query);
        
        // output the status of the response
        $response->setRawHeader('HTTP/1.1 ' . $response_xml->status . ' ' . \Zend_Http_Response::responseCodeAsText(intval($response_xml->status)));
        
        // output an xml representation of the data
        $response->setHeader('Content-Type', 'text/xml; charset=utf-8');
        $response->setBody($response_xml->asXML());
        
        \Steam\Event::trigger('steam-response');
        
        $response->sendResponse();
    }
    
    protected static function is_allowed($method, $item = NULL)
    {
        return true;
    }
    
    protected static function _create(\Steam\Data\Query &$query, \Steam\Data\Response &$response)
    {
        throw new \Steam\Exception\MethodNotImplemented();
    }
    
    protected static function _update(\Steam\Data\Query &$query, \Steam\Data\Response &$response)
    {
        throw new \Steam\Exception\MethodNotImplemented();
    }
    
    protected static function _retrieve(\Steam\Data\Query &$query, \Steam\Data\Response &$response)
    {
        throw new \Steam\Exception\MethodNotImplemented();
    }
    
    protected static function _delete(\Steam\Data\Query &$query, \Steam\Data\Response &$response)
    {
        throw new \Steam\Exception\MethodNotImplemented();
    }
    
    public static function shutdown()
    {
    }
    
}

?>
