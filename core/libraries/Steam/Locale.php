<?php
/**
 * Steam Locale Class
 *
 * This class manages localization including date and number formatting,
 * language and timezone.
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

class Steam_Locale
{
    /**
     * This class can only be instantiated using the construct method.
     *
     * @return void
     */
    private function __construct()
    {
    }
    
    public static function get_locales()
    {
        $locales = `locale --all-locales`;
        
        return explode("\n", $locales);
    }
    
    public static function get_timezones()
    {
        return DateTimeZone::listIdentifiers();
    }
    
    public static function set($category, $locale = 0)
    {
        if (!$locale)
        {
            return setlocale($category, $locale);
        }
        else
        {
            if (!setlocale($category, $locale))
            {
                throw Steam::_('Exception', 'Locale', sprintf(gettext('Unknown locale identifier: %s'), $locale));
            }
        }
    }
    
    public static function timezone($timezone = NULL)
    {
        if (is_null($timezone))
        {
            return date_default_timezone_get();
        }
        else
        {
            if (!date_default_timezone_set($timezone))
            {
                throw Steam::_('Exception', 'Locale', sprintf(gettext('Unknown timezone identifier: %s'), $timezone));
            }
        }
    }
}

?>
