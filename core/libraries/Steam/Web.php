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
    /**
     * Loads a page based on the given page code. Page code defaults to
     * "default".
     *
     * @return void
     * @param object $uri Steam_Web_URI object
     */
    public static function load(Steam_Web_URI $uri)
    {
        $page_name = $uri->get_page_name();
        
        if (!$page_name)
        {
            $page_name = 'default';
        }
        
        try
        {
            include Steam::$base_dir . 'apps/' . $uri->get_app_name() . '/pages/global.php';
        }
        catch (Steam_Exception_FileNotFound $exception)
        {
        }
        catch (Exception $exception)
        {
            include Steam::$base_dir . 'apps/global/error_pages/HTTP_500.php';
            return;
        }
        
        try
        {
            include Steam::$base_dir . 'apps/' . $uri->get_app_name() . '/pages/' . $page_name . '.php';
            return;
        }
        catch (Steam_Exception_FileNotFound $exception)
        {
            include Steam::$base_dir . 'apps/global/error_pages/HTTP_404.php';
            return;
        }
        catch (Exception $exception)
        {
            include Steam::$base_dir . 'apps/global/error_pages/HTTP_500.php';
            return;
        }
    }
}

?>
