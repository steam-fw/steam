<?php
/**
 * Steam Event Class
 *
 * This class handles application events and bindings to those events.
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

class Steam_Event
{
    private static $instance;
    
    /**
     * Creates a new instance of Steam_Event.
     *
     * @return object
     */
    public static function construct()
    {
        if (!isset(self::$instance))
        {
            $class = __CLASS__;
            
            self::$instance = new $class;
        }
        
        return self::$instance;
    }
    
    /**
     * This class can only be instantiated using the construct method.
     *
     * @return void
     */
    private function __construct()
    {
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
    
    /**
     * Fires the specified event.
     *
     * @return void
     * @param string $event event name
     */
    public function trigger($event)
    {
    }
}

?>
