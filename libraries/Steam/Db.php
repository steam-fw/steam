<?php
/**
 * Steam Database Class
 *
 * This class manages database connections and load balancing.
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

class Steam_Db
{
    /**
     * An array of Zend_Db objects
     */
    protected static $servers;
    
    
    /**
     * Initializes the database by setting the adapter and randomly selecting
     * servers to use for the current execution. At least one write server must
     * be defined, while read and search servers are optional and will default
     * to the defined write server.
     *
     * @throws Steam_Exception_Database
     * @return void
     * @param string $adapter Zend_Db adapter
     * @param array $parameters server parameters
     */
    public static function initialize($adapter, $parameters)
    {
        if (!isset($parameters['write'][0]))
        {
                throw new Steam_Exception_Database(gettext('A write database server has not been defined, but is required.'));
        }
        
        foreach (array('write', 'read', 'search') as $type)
        {
            $use_type = (count($parameters[$type])) ? $type : 'write';
            
            self::$servers[$type] = Zend_Db::factory($adapter, $parameters[$use_type][array_rand($parameters[$use_type])]);
        }
    }
    
    /**
     * Returns a Zend_Db object for writing.
     *
     * @return object
     */
    public static function write()
    {
        return self::$servers['write'];
    }
    
    /**
     * Returns a Zend_Db object for reading.
     *
     * @return object
     */
    public static function read()
    {
        return self::$servers['read'];
    }
    
    /**
     * Returns a Zend_Db object for searching.
     *
     * @return object
     */
    public static function search()
    {
        return self::$servers['search'];
    }
}

?>
