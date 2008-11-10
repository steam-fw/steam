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
    protected $driver;
    protected $master;
    protected $slaves;
    protected $slave;
    private static $instance;
    
    /**
     * Creates a new instance of Steam_Db.
     *
     * @return object
     */
    public static function construct($driver, $parameters)
    {
        if (!isset(self::$instance))
        {
            $class = __CLASS__;
            
            self::$instance = new $class($driver, $parameters);
        }
        
        return self::$instance;
    }
    
    /**
     * This class can only be instantiated using the construct method.
     *
     * @return void
     */
    private function __construct($driver, $parameters)
    {
        switch (strtolower($driver))
        {
            case 'mysql':
                $this->driver = 'MySQL';;
                break;
            default:
                throw Steam::_('Exception', 'Database', 'Unsupported database server.');
        }
        $this->master = $parameters;
    }
    
    /**
     * This class cannot be cloned.
     *
     * @throws Steam_Exception_General when cloning is attempted
     * @return void
     */
    public function __clone()
    {
        throw Steam::_('Exception', 'General');
    }
    
    public function add_server($parameters, $type = NULL)
    {
        if (is_null($type))
        {
            $type = 'default';
        }
        
        $this->slaves[$type][] = $parameters;
    }
    
    public function master()
    {
        if (!array_key_exists('object', $this->master))
        {
            $this->master['object'] = Steam::_('Db/' . $this->driver, $this->master);
        }
        
        return $this->master['object'];
    }
    
    public function slave($type = NULL)
    {
        if (is_null($type))
        {
            $type = 'default';
        }
        
        if (!array_key_exists($type, $this->slave))
        {
            if (!array_key_exists($type, $this->slaves))
            {
                throw Steam::_('Exception', 'General');
            }
            
            $this->slave[$type] = Steam::_('Db/' . $this->driver, array_rand($this->slaves[$type]));
        }
        
        return $this->slave[$type];
    }
    
    public function __call($method, $arguments)
    {
        return call_user_func_array(array($this->master(), $method), $arguments);
    }
}

?>
