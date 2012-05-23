<?php
/**
 * Steam Action Class
 *
 * This class handles action requests.
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

class Action
{
    public static function perform($action_name, $request, $response)
    {
        try
        {
            $action = include \Steam::app_path('actions/' . $action_name . '.php');
        }
        catch (\Steam\Exception\FileNotFound $exception)
        {
            \Steam\Error::display(404);
            exit;
        }
        
        if (!is_callable($action)) throw new \Steam\Exception\General('There was a problem performing the action.');
        
        try
        {
            $response->setHeader('Content-Type', 'text/plain');
            $result = $action($request, $response);
        }
        catch (\Exception $exception)
        {
            $response->setHeader('Content-Type', 'text/html');
            throw $exception;
        }
        
        if (headers_sent())
            return;
        elseif (is_bool($result) and !$result)
            ;// do nothing
        elseif (is_string($result) or is_numeric($result))
            $response->setBody($result);
        elseif (!$response->isRedirect() and isset($_SERVER['HTTP_REFERER']))
            $response->setRedirect($_SERVER['HTTP_REFERER'], 303);
        
        \Steam\Event::trigger('steam-response');
        
        $response->sendResponse();
    }
    
    public static function shutdown()
    {
    }
}

?>
