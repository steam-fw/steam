<?php
/**
 * Steam Localization Class
 *
 * This class configures localization settings and translation of Steam text.
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

class Locale
{
    /**
     * Instance of Zend_Translate used for translating Steam strings.
     */
    protected static $translator;
    
    /**
     * Initializes localization by setting the default locale and timezone, and
     * creating instances of Zend_Locale and Zend_Translate.
     *
     * @return void
     * @param string $locale default locale identifier
     * @param string $timezone default timezone identifier
     */
    public static function initialize($locale, $timezone)
    {
        // set the default locale and timezone
        date_default_timezone_set($timezone);
        \Zend_Locale::setDefault($locale);
        
        if (!is_null(\Steam\Cache::get_cache()))
        {
            // configure Zend_Date, Zend_Locale and Zend_Translate to use the cache
            \Zend_Date::setOptions(array('cache' => \Steam\Cache::get_cache()));
            \Zend_Locale::setCache(\Steam\Cache::get_cache());
            \Zend_Translate::setCache(\Steam\Cache::get_cache());
        }
        
        // store Zend_Locale in the registry after 
        \Zend_Registry::set('Zend_Locale', new \Zend_Locale($locale));
        
        // create an instance of Zend_Translate for translating core Steam text
        #self::$translator = new \Zend_Translate('gettext', \Steam::path('apps/global/translations'), NULL, array('scan' => \Zend_Translate::LOCALE_DIRECTORY));
    }
    
    /**
     * Sets a new global timezone.
     *
     * @return void
     * @param string $timezone timezone identifier
     */
    public static function set_timezone($timezone)
    {
        date_default_timezone_set($timezone);
    }
    
    /**
     * Sets a new global locale.
     *
     * @return void
     * @param string $locale locale identifier
     */
    public static function set_locale($locale)
    {
        \Zend_Registry::get('Zend_Locale')->setLocale($locale);
    }
    
    /**
     * Alternative to the Zend_Translate gettext-like translate method which
     * supports string replacement via sprintf. Use %s for substitution.
     *
     * @return string translated string
     * @param string $string string to translate, in sprintf format
     */
    public static function _($string)
    {
        $args = func_get_args();
        
        if (count($args) > 1)
        {
            $string = call_user_func_array('sprintf', $args);
        }
        
        return self::$translator->translate($string);
    }
}

?>
