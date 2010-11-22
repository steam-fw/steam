<?php
/**
 * Extra Functions
 *
 * This script defines some useful additions to PHP's built-in functions
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
 * determines the mime type of a file based on its extension.
 *
 * @return string mime type
 * @param string file path
 */
function file_mimetype($file)
{
    static $mime_types = array(
        '323'     => 'text/h323',
        'acx'     => 'application/internet-property-stream',
        'ai'      => 'application/postscript',
        'aif'     => 'audio/x-aiff',
        'aifc'    => 'audio/x-aiff',
        'aiff'    => 'audio/x-aiff',
        'asf'     => 'video/x-ms-asf',
        'asr'     => 'video/x-ms-asf',
        'asx'     => 'video/x-ms-asf',
        'au'      => 'audio/basic',
        'avi'     => 'video/x-msvideo',
        'axs'     => 'application/olescript',
        'bas'     => 'text/plain',
        'bcpio'   => 'application/x-bcpio',
        'bmp'     => 'image/bmp',
        'c'       => 'text/plain',
        'cat'     => 'application/vnd.ms-pkiseccat',
        'cdf'     => 'application/x-cdf',
        'cer'     => 'application/x-x509-ca-cert',
        'clp'     => 'application/x-msclip',
        'cmx'     => 'image/x-cmx',
        'cod'     => 'image/cis-cod',
        'cpio'    => 'application/x-cpio',
        'crd'     => 'application/x-mscardfile',
        'crl'     => 'application/pkix-crl',
        'crt'     => 'application/x-x509-ca-cert',
        'csh'     => 'application/x-csh',
        'css'     => 'text/css',
        'dcr'     => 'application/x-director',
        'der'     => 'application/x-x509-ca-cert',
        'dir'     => 'application/x-director',
        'dll'     => 'application/x-msdownload',
        'doc'     => 'application/msword',
        'dot'     => 'application/msword',
        'dvi'     => 'application/x-dvi',
        'dxr'     => 'application/x-director',
        'eps'     => 'application/postscript',
        'etx'     => 'text/x-setext',
        'evy'     => 'application/envoy',
        'fif'     => 'application/fractals',
        'flr'     => 'x-world/x-vrml',
        'gif'     => 'image/gif',
        'gtar'    => 'application/x-gtar',
        'gz'      => 'application/x-gzip',
        'h'       => 'text/plain',
        'hdf'     => 'application/x-hdf',
        'hlp'     => 'application/winhlp',
        'hqx'     => 'application/mac-binhex40',
        'hta'     => 'application/hta',
        'htc'     => 'text/x-component',
        'htm'     => 'text/html',
        'html'    => 'text/html',
        'htt'     => 'text/webviewhtml',
        'ico'     => 'image/x-icon',
        'ief'     => 'image/ief',
        'iii'     => 'application/x-iphone',
        'ins'     => 'application/x-internet-signup',
        'isp'     => 'application/x-internet-signup',
        'jfif'    => 'image/pipeg',
        'jpe'     => 'image/jpeg',
        'jpeg'    => 'image/jpeg',
        'jpg'     => 'image/jpeg',
        'js'      => 'application/javascript',
        'latex'   => 'application/x-latex',
        'lsf'     => 'video/x-la-asf',
        'lsx'     => 'video/x-la-asf',
        'm13'     => 'application/x-msmediaview',
        'm14'     => 'application/x-msmediaview',
        'm3u'     => 'audio/x-mpegurl',
        'man'     => 'application/x-troff-man',
        'mdb'     => 'application/x-msaccess',
        'me'      => 'application/x-troff-me',
        'mht'     => 'message/rfc822',
        'mhtml'   => 'message/rfc822',
        'mid'     => 'audio/mid',
        'mny'     => 'application/x-msmoney',
        'mov'     => 'video/quicktime',
        'movie'   => 'video/x-sgi-movie',
        'mp2'     => 'video/mpeg',
        'mp3'     => 'audio/mpeg',
        'mpa'     => 'video/mpeg',
        'mpe'     => 'video/mpeg',
        'mpeg'    => 'video/mpeg',
        'mpg'     => 'video/mpeg',
        'mpp'     => 'application/vnd.ms-project',
        'mpv2'    => 'video/mpeg',
        'ms'      => 'application/x-troff-ms',
        'mvb'     => 'application/x-msmediaview',
        'nws'     => 'message/rfc822',
        'oda'     => 'application/oda',
        'p10'     => 'application/pkcs10',
        'p12'     => 'application/x-pkcs12',
        'p7b'     => 'application/x-pkcs7-certificates',
        'p7c'     => 'application/x-pkcs7-mime',
        'p7m'     => 'application/x-pkcs7-mime',
        'p7r'     => 'application/x-pkcs7-certreqresp',
        'p7s'     => 'application/x-pkcs7-signature',
        'pbm'     => 'image/x-portable-bitmap',
        'pdf'     => 'application/pdf',
        'pfx'     => 'application/x-pkcs12',
        'pgm'     => 'image/x-portable-graymap',
        'pko'     => 'application/ynd.ms-pkipko',
        'pma'     => 'application/x-perfmon',
        'pmc'     => 'application/x-perfmon',
        'pml'     => 'application/x-perfmon',
        'pmr'     => 'application/x-perfmon',
        'pmw'     => 'application/x-perfmon',
        'png'     => 'image/png',
        'pnm'     => 'image/x-portable-anymap',
        'pot,'    => 'application/vnd.ms-powerpoint',
        'ppm'     => 'image/x-portable-pixmap',
        'pps'     => 'application/vnd.ms-powerpoint',
        'ppt'     => 'application/vnd.ms-powerpoint',
        'prf'     => 'application/pics-rules',
        'ps'      => 'application/postscript',
        'pub'     => 'application/x-mspublisher',
        'qt'      => 'video/quicktime',
        'ra'      => 'audio/x-pn-realaudio',
        'ram'     => 'audio/x-pn-realaudio',
        'ras'     => 'image/x-cmu-raster',
        'rgb'     => 'image/x-rgb',
        'rmi'     => 'audio/mid',
        'roff'    => 'application/x-troff',
        'rtf'     => 'application/rtf',
        'rtx'     => 'text/richtext',
        'scd'     => 'application/x-msschedule',
        'sct'     => 'text/scriptlet',
        'setpay'  => 'application/set-payment-initiation',
        'setreg'  => 'application/set-registration-initiation',
        'sh'      => 'application/x-sh',
        'shar'    => 'application/x-shar',
        'sit'     => 'application/x-stuffit',
        'snd'     => 'audio/basic',
        'spc'     => 'application/x-pkcs7-certificates',
        'spl'     => 'application/futuresplash',
        'src'     => 'application/x-wais-source',
        'sst'     => 'application/vnd.ms-pkicertstore',
        'stl'     => 'application/vnd.ms-pkistl',
        'stm'     => 'text/html',
        'svg'     => 'image/svg+xml',
        'sv4cpio' => 'application/x-sv4cpio',
        'sv4crc'  => 'application/x-sv4crc',
        'swf'     => 'application/x-shockwave-flash',
        't'       => 'application/x-troff',
        'tar'     => 'application/x-tar',
        'tcl'     => 'application/x-tcl',
        'tex'     => 'application/x-tex',
        'texi'    => 'application/x-texinfo',
        'texinfo' => 'application/x-texinfo',
        'tgz'     => 'application/x-compressed',
        'tif'     => 'image/tiff',
        'tiff'    => 'image/tiff',
        'tr'      => 'application/x-troff',
        'trm'     => 'application/x-msterminal',
        'tsv'     => 'text/tab-separated-values',
        'txt'     => 'text/plain',
        'uls'     => 'text/iuls',
        'ustar'   => 'application/x-ustar',
        'vcf'     => 'text/x-vcard',
        'vrml'    => 'x-world/x-vrml',
        'wav'     => 'audio/x-wav',
        'wcm'     => 'application/vnd.ms-works',
        'wdb'     => 'application/vnd.ms-works',
        'wks'     => 'application/vnd.ms-works',
        'wmf'     => 'application/x-msmetafile',
        'wps'     => 'application/vnd.ms-works',
        'wri'     => 'application/x-mswrite',
        'wrl'     => 'x-world/x-vrml',
        'wrz'     => 'x-world/x-vrml',
        'xaf'     => 'x-world/x-vrml',
        'xbm'     => 'image/x-xbitmap',
        'xla'     => 'application/vnd.ms-excel',
        'xlc'     => 'application/vnd.ms-excel',
        'xlm'     => 'application/vnd.ms-excel',
        'xls'     => 'application/vnd.ms-excel',
        'xlt'     => 'application/vnd.ms-excel',
        'xlw'     => 'application/vnd.ms-excel',
        'xof'     => 'x-world/x-vrml',
        'xpm'     => 'image/x-xpixmap',
        'xwd'     => 'image/x-xwindowdump',
        'z'       => 'application/x-compress',
        'zip'     => 'application/zip',
    );
    
    $ext = substr($file, 1 + strrpos($file, '.'));
    
    if (isset($mime_types[$ext]))
    {
        return $mime_types[$ext];
    }
    else
    {
        return 'application/octet-stream';
    }
}

?>
