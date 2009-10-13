<?php
/**
 * Steam Data Manipulation Class
 *
 * This class provides an interface for manipulating stored data/resources.
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

class Steam_Data
{
    /**
     * Performs a create data request.
     *
     * @see request
     * @return object Steam_Data_Response
     * @param string $resource resource identifier
     * @param object $query Steam_Data_Query
     */
    public static function create($resource, Steam_Data_Query $query)
    {
        $response = self::request('create', $resource, $query);
        
        return $response;
    }
    
    /**
     * Performs a retrieve data request.
     *
     * @see request
     * @return object Steam_Data_Response
     * @param string $resource resource identifier
     * @param object $query Steam_Data_Query
     */
    public static function retrieve($resource, Steam_Data_Query $query = NULL)
    {
        $response = self::request('retrieve', $resource, $query);
        
        return $response;
    }
    
    /**
     * Performs an update data request.
     *
     * @see request
     * @return object Steam_Data_Response
     * @param string $resource resource identifier
     * @param object $query Steam_Data_Query
     */
    public static function update($resource, Steam_Data_Query $query)
    {
        $response = self::request('update', $resource, $query);
        
        return $response;
    }
    
    /**
     * Performs a delete data request.
     *
     * @see request
     * @return object Steam_Data_Response
     * @param string $resource resource identifier
     */
    public static function delete($resource)
    {
        $response = self::request('delete', $resource);
        
        return $response;
    }
    
    
    /**
     * Performs a data manipulation request.
     *
     * @see request
     * @return object Steam_Data_Response
     * @param string $method data method {create, retrieve, update, delete}
     * @param string $resource resource identifier
     * @param object $query Steam_Data_Query
     */
    public static function request($method, $resource, Steam_Data_Query $query = NULL)
    {
        if (is_null($query))
        {
            $query = new Steam_Data_Query();
        }
        
        $response = new Steam_Data_Response();
        
        // check the resource identifier to make sure it's valid
        // the first piece of the identifier is the app name
        // the second piece is the name of the resource
        // the third piece is an optional identifier
        if (preg_match('/^(\\/[^\\/]+\\/)?([^\\/]+)(\\/.*)?$/', $resource, $resource_components))
        {
            $query->app_name      = ($resource_components[1]) ? trim($resource_components[1], '/') : Steam_Application::name();
            $query->method        = $method;
            $query->resource_name = $resource_components[2];
            $query->resource_id   = (isset($resource_components[3])) ? trim($resource_components[3], '/') : NULL;
            
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
            include_once Steam::path('apps/' . $query->app_name . '/resources/dynamic/global.php');
        }
        catch (Steam_Exception_FileNotFound $exception)
        {
            //ignore
        }
        catch (Exception $exception)
        {
            $response->status = 500;
            $response->error  = $exception->getMessage();
            
            return;
        }
        
        try
        {
            // include the script which contains the data manipulation code
            include Steam::path('apps/' . $query->app_name . '/resources/dynamic/' . $query->resource_name . '/' . $query->method . '.php');
        }
        // if there are access requirements which were not fulfilled
        // it's the manipulation script's responsibility to throw this
        catch (Steam_Exception_Auth $exception)
        {
            $response->status = 401;
            $response->error  = $exception->getMessage();
        }
        // if the script doesn't exist
        catch (Steam_Exception_FileNotFound $exception)
        {
            // if the resource exists, then the method isn't implemented
            if (file_exists(Steam_Application::path('resources/dynamic/' . $query->resource_name)))
            {
                $response->status = 405;
            }
            // otherwise the resource doesn't exist
            else
            {
                $response->status = 404;
                $response->error  = $exception->getMessage();
            }
        }
        // catch all other exceptions and return the error in the response
        catch (Exception $exception)
        {
            $response->status = 500;
            $response->error  = $exception->getMessage();
        }
    }
}

?>
