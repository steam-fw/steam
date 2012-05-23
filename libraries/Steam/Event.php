<?php
/**
 * Steam Event Class
 *
 * This class handles application events and bindings to those events.
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

class Event
{
    private static $hooks = array();
    
    /**
     * Fires the specified event.
     *
     * @return void
     * @param string $event event name
     */
    public static function trigger($event)
    {
        if (!isset(self::$hooks[$event]))
        {
            return;
        }
        
        foreach (self::$hooks[$event] as $hook)
        {
            call_user_func_array($hook[0], $hook[1]);
        }
    }
    
    public static function register($event, $function, $parameters = array())
    {
        self::$hooks[$event][] = array($function, $parameters);
    }
}

?>
