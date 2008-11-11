<?php
/**
 * Steam Test Class
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

require_once 'PHPUnit/Framework.php';

class SteamTest extends PHPUnit_Framework_TestCase
{
    public function testSteamInitialization()
    {
        $base_dir = str_replace('tests/SteamTest.php', '', __FILE__);
        
        $include_path = $base_dir . 'core/libraries' . PATH_SEPARATOR . get_include_path();
        
        function test_exception_handler($exception) {}
        function test_error_handler($errno, $errstr, $errfile, $errline, $errcontext) {}
        
        require_once $base_dir . 'core/libraries/Steam.php';
        
        Steam::$base_dir = $base_dir;
        
        Steam::initialize();
        
        $this->assertEquals($include_path, get_include_path());
        
        //zend autoloader
        
        $this->assertEquals('Steam_Error::exception_handler', set_exception_handler('test_exception_handler'));
        
        $this->assertEquals('Steam_Error::error_handler', set_error_handler('test_error_handler'));
        
        $this->assertEquals(0, ini_get('display_errors'));
        
        require $base_dir . 'config.php';
        
        $this->assertEquals($locale, setlocale(LC_ALL, 0));
        
        $this->assertEquals($timezone, date_default_timezone_get());
        
        //memcache
        
        //db
        
        $this->markTestIncomplete();
    }
    
    public function testSteamVariables()
    {
        $base_dir = str_replace('tests/SteamTest.php', '', __FILE__);
        require_once $base_dir . 'core/libraries/Steam.php';
        
        $variables = array(
            'base_uri',
            'base_dir',
            'interface',
            'app_id',
            'app_name',
            );
        
        foreach ($variables as $variable)
        {
            Steam::$$variable = 'test';
            $this->assertEquals('test', Steam::$$variable);
        }
    }
}
