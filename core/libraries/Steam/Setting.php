<?php
/**
 * Steam Setting Class
 *
 * This class manages dynamic settings stored in the database.
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

class Steam_Setting
{
    /**
     * Sets the value of a setting.
     *
     * @return void
     * @param string $name setting name
     * @param mixed $value setting value
     */
    public function set($name, $value)
    {
        Steam_Db::write()->query('UPDATE settings SET setting_value = \'' . Steam_Db::read()->escape($value) . '\' WHERE setting_name = \'' . Steam_Db::read()->escape($name) . '\'');
        Steam_Cache::set('setting', $name, $value);
    }
    
    /**
     * Retrieves the value of a setting.
     *
     * @return mixed
     * @param string $name setting name
     */
    public function get($name)
    {
        try
        {
            // check if the setting is stored in memcache
            $value = Steam_Cache::get('setting', $name);
        }
        catch (Steam_Exception_Cache $exception)
        {
            // if not, grab it from the db, then store it in memcache
            $value = Steam_Db::read()->select_field('SELECT setting_value FROM settings WHERE setting_name = \'' . Steam_Db::read()->escape($name) . '\'');
            Steam_Cache::set('setting', $name, $value);
        }
        
        return $value;
    }
}

?>
