<?php
/**
 * Steam Logging Class
 *
 * This class provides an interface to Zend\Log.
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

class Logger
{
    /**
     * An instance of Zend\Log\Logger.
     */
    protected static $logger;
    
    /**
     * References to the built-in writers.
     */
    protected static $writers = array();
    
    /**
     * Creates an instance of Zend\Log\Logger with a default Null writer.
     *
     * @return void
     */
    public static function initialize()
    {
        self::$logger = new \Zend\Log\Logger();
    }
    
    /**
     * Enables one of the built-in logging facilities.
     */
    public static function enable($writer)
    {
        switch (strtolower($writer))
        {
            case 'firebug':
                self::$writers['firebug'] = new \Zend\Log\Writer\FirePhp();
                self::add_writer(self::$writers['firebug']);
                break;
            case 'syslog':
                self::$writers['syslog'] = new \Zend\Log\Writer\Syslog();
                self::add_writer(self::$writers['syslog']);
                break;
        }
    }
    
    /**
     * Adds an additional writer to the logger.
     *
     * @return void
     * @param object $writer Zend Log Writer
     */
    public static function add_writer(\Zend\Log\Writer\AbstractWriter $writer)
    {
        return self::$logger->addWriter($writer);
    }
    
    /**
     * Returns the underlying Zend\Log\Logger object
     *
     * @return object Zend\Log\Logger
     */
    public static function get_logger()
    {
        return self::$logger;
    }
    
    /**
     * Writes a message to the logs with the specified priority.
     *
     * @return void
     * @param string $message log message
     * @param integer $priority  message priority
     */
    public static function write($message, $priority = NULL)
    {
        if (is_null($priority)) $priority = \Zend\Log\Logger::INFO;
        
        return self::$logger->log($message, $priority);
    }
}

?>
