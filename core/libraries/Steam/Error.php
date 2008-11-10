<?php
/**
 * Steam Error Class
 *
 * This class handles errors and error logging.
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
    protected static $errors = array();
    
    /**
     * This class can only be instantiated using the construct method.
     *
     * @return void
     */
    private function __construct()
    {
    }
    
    /**
     * Custom error handler which converts errors into exceptions.
     *
     * @throws Steam_Exception... multiple types
     * @return void
     */
    public static function error_handler($code, $message, $file, $line, $context)
    {
        $map = array(
            'failed to open stream: No such file or directory' => 'FileNotFound',
            );
        
        foreach ($map as $error => $exception)
        {
            if (is_numeric(strpos($message, $error)))
            {
                $exception = 'Steam_Exception_' . $exception;
                
                throw new $exception($message, $code, $file, $line);
            }
        }
        
        throw new Steam_Exception_PHP($message, $code, $file, $line);
    }
    
    /**
     * Custom exception handler which logs uncaught exceptions using syslog.
     *
     * @return void
     */
    public static function exception_handler($exception)
    {
        try
        {
            #if (($exception->getCode() & error_reporting()) == $exception->getCode())
            #{
                self::$errors[] = $exception->getMessage() . ' on line ' . $exception->getLine() . ' of ' . $exception->getFile();
            #}
            
            self::log(LOG_ERR, Steam::$site_name . ': ' . $exception->getType() . ': ' . $exception->getMessage() . '; ' . $exception->getFile() . ' @ line ' . $exception->getLine());
        }
        catch (Exception $exception)
        {
            echo $exception->getType() . ' Exception : ' . $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine();
        }
    }
    
    /**
     * Writes messages to syslog with the specified priority.
     *
     * @return void
     * @param int $priority syslog priority
     * @param string $message log message
     */
    public static function log($priority, $message)
    {
        define_syslog_variables();
        openlog('Steam/' . Steam::$interface, LOG_ODELAY, Steam::$syslog_facility);
        syslog($priority, $message);
        closelog();
    }
    
    public static function report()
    {
        $errors = self::$errors;
        self::$errors = array();
        return $errors;
    }
    
    public static function shutdown()
    {
        if (count(self::$errors))
        {
            foreach (self::$errors as $error)
            {
                echo $error . '<br>' . "\n";
            }
        }
        elseif ($error = error_get_last())
        {
            echo $error['message'] . ' on line ' . $error['line'] . ' of ' . $error['file'];
        }
    }
}

?>
