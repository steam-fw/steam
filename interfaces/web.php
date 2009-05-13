<?php
/**
 * Steam Web Interface
 *
 * This script performs the necessary tasks to handle HTTP interaction.
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

// identify the current interface
Steam::$interface = 'web';

switch (Steam::$environment)
{
    case 'development':
        $request  = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();
        $channel = Zend_Wildfire_Channel_HttpHeaders::getInstance();
        $channel->setRequest($request);
        $channel->setResponse($response);
        $firebug = new Zend_Log_Writer_Firebug;
        Steam_Logger::add_writer($firebug);
        break;
    default:
        $syslog = new Zend_Log_Writer_Syslog;
        Steam_Logger::add_writer($syslog);
}

// configure Zend_Session to use a custom cache based save handler
Zend_Session::setSaveHandler(new Steam_Web_Session);

// load the requested page
Steam_Web::load(new Steam_Web_URI());

if (Steam::$environment == 'development')
{
    $channel->flush();
    $response->sendHeaders();
}

Steam_Web::send_response();

// fire the unload event which occurs at the end of execution
Steam_Event::trigger('unload');

?>
