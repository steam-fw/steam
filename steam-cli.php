<?php
/**
 * Steam Command-Line Interface
 *
 * This provides an interface for accessing Steam resources through the
 * command line
 *
 * Copyright 2008-2011 Shaddy Zeineddine
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
 * @copyright 2008-2011 Shaddy Zeineddine
 * @license http://www.gnu.org/licenses/gpl.txt GPL v3 or later
 * @link http://code.google.com/p/steam-fw
 */

if (!isset($argv[2]))
{
    print 'Invalid Arguments';
    exit(1);
}

$application   = $argv[1];
$resource_type = $argv[2];
$resource_name = '';

switch ($argv[2])
{
    case 'action':
    case 'view':
        if ($argc < 4)
        {
            print 'Invalid Arguments';
            exit(1);
        }
        
        $resource_name = $argv[3];
        $sid = 4;
	break;
    case 'model':
        if ($argc < 5)
        {
            print 'Invalid Arguments';
            exit(1);
        }
        
        switch ($argv[3])
        {
            case 'create':
                $_SERVER['REQUEST_METHOD'] = 'POST';
                break;
            case 'retrieve':
                $_SERVER['REQUEST_METHOD'] = 'GET';
                break;
            case 'update':
                $_SERVER['REQUEST_METHOD'] = 'PUT';
                break;
            case 'delete':
                $_SERVER['REQUEST_METHOD'] = 'DELETE';
                break;
            default:
                $_SERVER['REQUEST_METHOD'] = 'HEAD';
        }
        
        $resource_name = $argv[4];
        $sid = 5;
        
        break;
    default:
        print 'Usage: APPLICATION RESOURCE_TYPE [ACTION] RESOURCE_NAME SESSION_ID' . "\n";
        exit(1);
}


$temp = explode('?', $resource_name, 2);
$resource_name = $temp[0];

if (isset($temp[1]))
{
    $_SERVER['QUERY_STRING'] = $temp[1];
    
    parse_str($_SERVER['QUERY_STRING'], $_GET);
    
    foreach ($_GET as $name => $value)
    {
        $_REQUEST[$name] = $value;
    }
}
else
{
    $_SERVER['QUERY_STRING'] = '';
}
unset($temp);

if (isset($argv[$sid]))
{
    session_id($argv[$sid]);
}

include 'functions.php';

include 'libraries/Steam.php';

ob_start();

\Steam::load_config();

\Steam::initialize();

Steam::set_request($application, $resource_type, $resource_name);

\Steam::dispatch();

\Steam\Event::trigger('steam-complete');

?>
