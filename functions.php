<?php
/**
 * Extra Functions
 *
 * This script defines some useful additions to PHP's built-in functions
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
            return 'application/javascript';
        case 'php':
            return 'application/x-php';
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

function quote_csv($string)
{
    return str_replace('"', '""', $string);
}

?>
