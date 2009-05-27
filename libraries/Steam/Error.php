<?php
/**
 * Steam Error Class
 *
 * This class handles errors and exceptions.
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

class Steam_Error
{
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
        set_exception_handler('Steam_Error::exception_handler');
        set_error_handler('Steam_Error::error_handler');
        register_shutdown_function('Steam_Error::shutdown');
        
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
            );
        
        foreach ($map as $error_message => $exception_type)
        {
            if (is_numeric(strpos($message, $error_message)))
            {
                $exception_type = 'Steam_Exception_' . $exception_type;
                
                $exception = new $exception_type($message, $level, $file, $line);
            }
        }
        
        if (!isset($exception))
        {
            // if there wasn't a matching exception, throw the general PHP exception
            $exception = new Steam_Exception_PHP($message, $level, $file, $line);
        }
        
        self::log_exception($exception);
        
        throw $exception;
    }
    
    /**
     * Custom exception handler which stores and logs uncaught exceptions using
     * syslog.
     *
     * @return void
     * @param object $exception exception
     */
    public static function exception_handler(Exception $exception)
    {
        try
        {
            self::$exceptions[] = self::log_exception($exception);
        }
        catch (Exception $exception)
        {
            echo 'There was a problem handling the exception : ' . $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine();
        }
    }
    
    public static function log_exception(Exception $exception)
    {
            $message = $exception->getMessage() . ' on line ' . $exception->getLine() . ' of ' . $exception->getFile();
            
            Steam_Logger::log(Steam::$app_name . ': ' . $message, Zend_Log::ERR);
            
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
        if (count(self::$exceptions))
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
}

?>
