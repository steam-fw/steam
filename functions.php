<?php
/**
 * Extra Functions
 *
 * This script defines some useful additions to PHP's built-in functions
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
 * @copyright 2008-2011 Shaddy Zeineddine
 * @license http://www.gnu.org/licenses/gpl.txt GPL v3 or later
 * @link http://code.google.com/p/steam-fw
 */

/**
 * a recursive unlink function
 *
 * this function will delete a directory and all its contents, be careful!
 *
 * @return void
 * @param string $directory directory
 */
function unlink_r($directory)
{
    // remove any trailing slashes so all paths are uniform
    $directory = rtrim($directory, '/');
    
    // if the target is not a directory, ignore
    if (is_dir($directory))
    {
        //iterate through the contents of the directory
        foreach(glob($directory . '/*') as $file)
        {
            // if the file is a directory, call self
            if (is_dir($file) and !is_link($file))
            {
                unlink_r($file);
            }
            // if the file is a file, use standard unlink
            else
            {
                unlink($file);
            }
        }
        
        //iterate through the hidden contents of the directory
        foreach(glob($directory . '/.*') as $file)
        {
            // get the name of the file only, not the full path
            $file_only = str_replace($directory, '', $file);
            
            // if the file is . or .., skip
            if ($file_only == '/.' or $file_only == '/..')
            {
                continue;
            }
            // if the file is a directory, call self
            elseif (is_dir($file) and !is_link($file))
            {
                unlink_r($file);
            }
            // if the file is a file, use standard unlink
            else
            {
                unlink($file);
            }
        }
        
        // remove the current directory now that it is empty
        rmdir($directory);
    }
}

/**
 * the inverse of http_build_query
 *
 * @return array
 * @return string query string
 * @return string separator
 */
function http_parse_query($query, $separator = NULL)
{
    if (empty($query))
    {
        return array();
    }
    
    if (is_null($separator))
    {
        $separator = ini_get('arg_separator.output');
    }
    
    $pairs = explode($separator, $query);
    $array = array();
    
    foreach ($pairs as $pair)
    {
        $kv = explode('=', $pair);
        
        if (isset($kv[1]))
        {
            $array[$kv[0]] = urldecode($kv[1]);
        }
        else
        {
            $array[$kv[0]] = NULL;
        }
    }
    
    return $array;
} 

if (!function_exists('gettext'))
{
    /**
     * defines a dummy gettext function if gettext is not available
     *
     * @return string
     * @param string text
     */
    function gettext($string)
    {
        return $string;
    }
}

/**
 * implode function for string representations of arrays in xml
 *
 * @return xarray
 * @param string separator
 * @param array
 */
function ximplode($separator, $array)
{
    $array = current($array);
    
    if (!is_array($array))
    {
        return (string) $array;
    }
    
    $first = true;
    $string = '';
    
    foreach ($array as $item)
    {
        if ($first)
        {
            $string .= $item;
            $first = false;
        }
        else
        {
            $string .= $separator . $item;
        }
    }
    
    return $string;
}

/**
 * in array function for string representations of arrays in xml
 *
 * @return bool
 * @param string needle
 * @param xarray haystack
 */
function xin_array($needle, $haystack)
{
    foreach ($haystack as $hay)
    {
        if ((string) $hay == (string) $needle)
        {
            return true;
        }
    }
    
    return false;
}

/**
 * converts an xarray into a normal array
 *
 * @return array
 * @param xarray
 */
function xarray($xarray)
{
    $xarray = current($xarray);
    
    if (is_array($xarray))
    {
        $array = array();
        
        foreach ($xarray as $element)
        {
            $array[] = $element;
        }
        
        return $array;
    }
    else
    {
        return array((string) $xarray);
    }
}

/**
 * determines the mime type of a file.
 *
 * @return string mime type
 * @param string file path
 */
function file_mimetype($file, $string = false)
{
    $extension = substr($file, strrpos($file, '.') + 1);
    
    switch ($extension)
    {
        case 'css':
            return 'text/css';
        case 'js':
            return 'text/javascript';
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    
    if ($string)
    {
        $mime_type = finfo_buffer($finfo, $file);
    }
    else
    {
        $mime_type = finfo_file($finfo, $file);
    }
    
    finfo_close($finfo);
    
    if (!$mime_type)
        return 'application/octet-stream';
    else
        return $mime_type;
}

?>
