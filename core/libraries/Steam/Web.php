<?php
/**
 * Steam Web Class
 *
 * This class loads pages.
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

class Steam_Web
{
    private static $instance;
    
    /**
     * Creates a new instance of Steam_Web.
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
     * Loads a page based on the given page code. Page code defaults to
     * "default".
     *
     * @return void
     * @param string $page_code page code
     */
    public function load_page($page_code = '')
    {
        Steam::$page_code = ($page_code) ? $page_code: 'default';
        unset($page_code);
        
        try
        {
            include_once Steam::$base_dir . 'sites/' . Steam::$site_id . '/pages/' . Steam::$page_code . '.php';
        }
        catch (Steam_Exception_FileNotFound $exception)
        {
            include_once Steam::$base_dir . 'sites/global/error_pages/HTTP_404.php';
        }
    }
}

?>
