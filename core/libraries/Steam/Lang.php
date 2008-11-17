<?php
/**
 * Steam Language/Translation Class
 *
 * This class provides an interface to Zend_Translate.
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

class Steam_Lang
{
    /**
     * Array of Zend_Translate instances.
     */
    protected static $translators = array();
    
    /**
     * The name of the app from which we are currently outputting translations
     */
    protected static $app_name = 'steam';
    
    /**
     * Changes the translation file source directory to the one for the
     * specified application.
     *
     * @return void
     * @param string $app_name app to use for translation files
     */
    public static function source($app_name)
    {
        self::$app_name = $app_name;
    }
    
    /**
     * Returns the Zend_Translate object for the specified application.
     *
     * @return object Zend_Translate
     * @param string $app_name application name for translations
     */
    public static function get($app_name = NULL)
    {
        // if an app is not specified, use the current one
        if (!is_null($app_name))
        {
            self::source($app_name);
        }
        
        // if there isn't a Zend_Translate instance for the app already, make it
        if (!isset(self::$translators[self::$app_name]))
        {
            self::$translators[self::$app_name] = new Zend_Translate('Steam_Translate', Steam::$base_dir . 'apps/' . self::$app_name . '/translations', NULL, array('scan' => Zend_Translate::LOCALE_DIRECTORY));
        }
        
        return self::$translators[self::$app_name];
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
        
        return self::get()->_($string);
    }
}

?>
