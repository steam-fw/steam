<?php
/**
 * Steam Static Resource Class
 *
 * This class manages the retrieval of static resources, leveraging
 * browser caching and supoprting resource fingerprinting.
 *
 * Copyright 2008-2011 Shaddy Zeineddine
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

class StaticResource
{
    public static function display($resource, $request, $response)
    {
        ini_set('zlib.output_compression', 0);
        
        // wrap everything in a try to handled errors gracefully
        try
        {
            $resource = rawurldecode($resource);
            
            try
            {
                // Check to see if this resource has been mapped to a different file
                $filepath = \Steam\Cache::get('_static:real-path', \Steam::uri('/' . \Steam::config('static_path') . '/' . $resource));
                
                if (substr($filepath, 0, 11) == 'cache-file:')
                {
                    $cache_file = \Steam\Cache::get('_static:cache-file-info', substr($filepath, 11));
                    $cache_file = unserialize($cache_file);
                    
                    $date = new \Zend_Date($cache_file['last_mod'], \Zend_Date::TIMESTAMP);
                    $last_mod = $date->toString(\Zend_Date::RFC_1123);
                    $expires  = $date->addYear(1)->toString(\Zend_Date::RFC_1123);
                    $max_age  = 31536000;
                    $content_length = $cache_file['content_length'];
                    $content_type   = $cache_file['content_type'];
                }
                
                // fingerprinted resources can be cached for the max time allowed
                $maxage = '1y';
            }
            catch (\Steam\Exception\Cache $exception)
            {
                // No special mapping has been found, assume it maps normally
                $filepath = \Steam::app_path('/static/' . ltrim($resource, '/'));
                
                // retrieve the maxage configuration value
                $maxage = \Steam::config('static_maxage');
            }
            
            if (!isset($cache_file))
            {
                // check if the file exists
                if (!file_exists($filepath))
                {
                    // is it an expired fingerprinted file?
                    if (preg_match('#/~[0-9a-f]{32}~#', $filepath))
                    {
                        $resource = preg_replace('#/~[0-9a-f]{32}~#', '/', $resource);
                        $response->setRawHeader('HTTP/1.1 301 Moved Permanently', true);
                        $response->setHeader('Location', \Steam\StaticResource::uri($resource));
                        $response->sendHeaders();
                        exit;
                    }
                    else
                    {
                        throw new \Steam\Exception\FileNotFound();
                    }
                }
                
                
                // create and set the file's last modified date
                $date = new \Zend_Date(filemtime($filepath), \Zend_Date::TIMESTAMP);
                
                // save the last modified date string
                $last_mod = $date->toString(\Zend_Date::RFC_1123);
                
                // store other info about the file
                $content_length = filesize($filepath);
                $content_type = file_mimetype($filepath);
                
                $date = \Zend_Date::now();
                
                // if it's not empty, parse and use it
                if ($maxage)
                {
                    $maxage_unit  = substr($maxage, -1, 1);
                    $maxage_value = substr($maxage, 0, strlen($maxage) - 1);
                    
                    switch ($maxage_unit)
                    {
                        case 'y':
                            $max_age = 365;
                            break;
                        case 'm':
                            $max_age = 30.42 * $maxage_value;
                            break;
                        default:
                            $max_age = $maxage_value;
                    }
                    
                    $date->addDay(intval($max_age));
                    $max_age = 86400 * max(min(365, intval($max_age)), 0);
                }
                // otherwise use a default of 30 days
                else
                {
                    $date->addDay(30);
                    $max_age = 2592000;
                }
                
                // save the expiration date string
                $expires = $date->toString(\Zend_Date::RFC_1123);
            }
            
            
            header_remove('X-Powered-By');
            header_remove('Expires');
            header_remove('Pragma');
            header_remove('Cache-Control');
            
            ob_end_clean();
            
            // set the appropriate headers
            $response->setHeader('Last-Modified',  $last_mod, true);
            $response->setHeader('Content-Type',   $content_type, true);
            $response->setHeader('Expires',        $expires, true);
            $response->setHeader('Vary',           'Accept-Encoding', true);
            $response->setHeader('Cache-Control', 'public, max-age=' . $max_age, true);
            
            if (isset($cache_file))
            {
                $content = \Steam\Cache::get('_static:cache-file', $cache_file['file_name']);
            }
            elseif ($content_type == 'application/javascript')
            {
                ob_start();
                include $filepath;
                $content = ob_get_contents();
                ob_end_clean();
            }
            else
            {
                $content = file_get_contents($filepath);
            }
            
            if (stripos($_SERVER['HTTP_ACCEPT_ENCODING'], "gzip") !== false)
            {
                $content = gzencode($content);
                $content_length = strlen($content);
                $response->setHeader('Content-Encoding', 'gzip', true);
            }
            
            $response->setHeader('Content-Length', $content_length, true);
            
            $response->sendHeaders();
            
            // if this is a GET request, send the file as well
            if ($request->isGet())
            {
                print $content;
            }
        }
        catch (\Steam\Exception\FileNotFound $exception)
        {
            $response->setRawHeader('HTTP/1.1 404 File Not Found', true);
            $response->sendHeaders();
            
            print($exception->getMessage());
            
            exit;
        }
        catch (\Exception $exception)
        {
            $response->setRawHeader('HTTP/1.1 500 Internal Server Error', true);
            $response->sendHeaders();
            
            print($exception->getMessage());
            
            exit;
        }
    }
    
    public static function real_uri($path)
    {
        return \Steam::uri('/' . \Steam::config('static_path') . '/' . ltrim($path, '/'));
    }
    
    public static function uri($path)
    {
        if (!\Steam::config('fingerprinting'))
        {
            return self::real_uri($path);
        }
        
        $filepath = \Steam::app_path('/static/' . $path);
        $fileuri  = \Steam::uri('/' . \Steam::config('static_path') . '/' . ltrim($path, '/'));
        
        if (!file_exists($filepath))
        {
            return $fileuri;
        }
        
        $last_mod = filemtime($filepath);
        
        try
        {
            $cache_date = \Steam\Cache::get('_static:cache-date', $filepath);
            
            if ($cache_date > $last_mod)
            {
                return \Steam\Cache::get('_static:cache-path', $filepath);
            }
        }
        catch (\Steam\Exception\Cache $exception)
        {
        }
        
        $fingerprint = md5_file($filepath);
        $last_slash = strrpos($fileuri, '/') + 1;
        $cacheuri = substr($fileuri, 0, $last_slash) . '~' . $fingerprint . '~' . substr($fileuri, $last_slash);
        
        \Steam\Cache::set('_static:cache-date', $filepath,  $last_mod);
        \Steam\Cache::set('_static:cache-uri',  $filepath,  $cacheuri);
        \Steam\Cache::set('_static:real-path',  $cacheuri, $filepath);
        
        return $cacheuri;
    }
}

?>
