<?php
/**
 * Steam Session Auth Storage Class
 *
 * This class provides a way of storing authentication for Zend Auth.
 *
 * Copyright 2012 Shaddy Zeineddine
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
 * @copyright 2012 Shaddy Zeineddine
 * @license http://www.gnu.org/licenses/gpl.txt GPL v3 or later
 * @link http://code.google.com/p/steam-fw
 */


namespace Steam\Auth\Storage;

class Session implements \Zend_Auth_Storage_Interface
{
    public function clear()
    {
        try
        {
            \Steam\Session::set('_Zend_Auth_Storage', '');
        }
        catch (\Exception $exception)
        {
            throw new \Zend_Auth_Storage_Exception($exception->getMessage);
        }
    }
    
    public function isEmpty()
    {
        try
        {
            return !(bool) \Steam\Session::get('_Zend_Auth_Storage', '');
        }
        catch (\Exception $exception)
        {
            throw new \Zend_Auth_Storage_Exception($exception->getMessage);
        }
    }
    
    public function read()
    {
        try
        {
            return \Steam\Session::get('_Zend_Auth_Storage', '');
        }
        catch (\Exception $exception)
        {
            throw new \Zend_Auth_Storage_Exception($exception->getMessage);
        }
    }
    
    public function write($contents)
    {
        try
        {
            \Steam\Session::set('_Zend_Auth_Storage', $contents);
        }
        catch (\Exception $exception)
        {
            throw new \Zend_Auth_Storage_Exception($exception->getMessage);
        }
    }
}

?>
