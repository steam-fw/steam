<?php
/**
 * Steam Error Class
 *
 * This class handles errors and exceptions.
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

namespace Steam;

class Error
{
    private static $exception;
    
    /**
     * Exceptions which were caught by the default exception handler.
     */
    protected static $exceptions = array();
    
    /**
     * Initializes error and exception handlers.
     *
     * @return void
     */
    public static function initialize()
    {
        // set the custom error and exception handlers
        set_exception_handler('\Steam\Error::exception_handler');
        set_error_handler('\Steam\Error::error_handler');
        register_shutdown_function('\Steam\Error::shutdown');
        
        // don't display errors because Steam is handling error output now
        ini_set('display_errors', 0);
        ini_set('html_errors',    0);
        error_reporting(E_ALL);
    }
    
    /**
     * Custom error handler which converts errors into exceptions.
     *
     * @throws Steam_Exception... multiple types
     * @return void
     * @param int $level php error level
     * @param string $message error string
     * @param string $file file
     * @param int $line line number
     * @param array $context error context
     */
    public static function error_handler($level, $message, $file, $line, $context)
    {
        // someone used @ so let's stay quiet
        if( ($level & error_reporting()) != $level)
        {
            return;
        }
        
        // maps certain errors to different exceptions
        // currently only works if the error strings are in english
        $map = array(
            'failed to open stream: No such file or directory' => 'FileNotFound',
            'stat failed for' => 'FileNotFound',
            'Undefined variable: ' => 'Fatal',
            );
        
        $exception_class = 'Steam\\Exception\\PHP';
        
        foreach ($map as $error_message => $exception_type)
        {
            if (is_numeric(strpos($message, $error_message)))
            {
                $exception_class = 'Steam\\Exception\\' . $exception_type;
                
                break;
            }
        }
        
        $exception = new $exception_class($message, $level);
        $exception->setFile($file);
        $exception->setLine($line);
        
        self::log_exception($exception);
        
        // certain errors cannot be handled properly when thrown
        if ($exception_class == 'Steam\Exception\Fatal')
        {
            self::exception_handler($exception);
        }
        else
        {
            throw $exception;
        }
    }
    
    /**
     * Custom exception handler which stores and logs uncaught exceptions using
     * syslog.
     *
     * @return void
     * @param object $exception exception
     */
    public static function exception_handler(\Exception $exception)
    {
        try
        {
            self::$exceptions[] = self::log_exception($exception);
        }
        catch (\Exception $exception)
        {
            echo 'There was a problem handling the exception : ' . $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine();
        }
    }
    
    public static function log_exception(\Exception $exception)
    {
            self::$exception = $exception;
            
            $message = $exception->getMessage() . ' on line ' . $exception->getLine() . ' of ' . $exception->getFile();
            
            \Steam\Logger::log(\Steam::app() . ': ' . $message, \Zend_Log::ERR);
            
            return $message;
    }
    
    /**
     * Displays any errors that were triggered near the end of execution which
     * may not be properly handled by the custom error or exception handlers.
     *
     * @return void
     */
    public static function shutdown()
    {
        if (isset(self::$exceptions[0]))
        {
            foreach (self::$exceptions as $exception)
            {
                echo $exception . '<br>' . "\n";
            }
        }
        elseif ($error = error_get_last())
        {
            echo $error['message'] . ' on line ' . $error['line'] . ' of ' . $error['file'];
        }
    }
    
    public static function display($http_status_code, $error_message = '')
    {
        // set the HTTP status code and message
        \Steam::$response->setRawHeader('HTTP/1.1 ' . $http_status_code . ' ' . \Zend_Http_Response::responseCodeAsText($http_status_code), true);
        
        \Steam\Event::trigger('steam-response');
        
        \Steam::$response->sendHeaders();
        
        if ($error_page = \Steam::config('error_page'))
        {
            try
            {
                $request  = new \Zend_Controller_Request_Http();
                $response = new \Zend_Controller_Response_Http();
                \Steam\View::display($error_page, $request, $response);
            }
            catch (\Exception $exception)
            {
                include \Steam::path('apps/global/error_pages/HTTP_' . $http_status_code . '.php');
            }
        }
        else
        {
            // if it's not found, display the 404 error page
            include \Steam::path('apps/global/error_pages/HTTP_' . $http_status_code . '.php');
            /*
            if ($error_message)
            {
                print $error_message;
            }*/
        }
        
    }
    
    public static function last_exception()
    {
        return self::$exception;
    }
}
?>
