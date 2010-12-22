<?php
/**
 * Steam configuration file
 *
 * Stores the values for the basic configuration of Steam.
 *
 * Copyright 2008-2010 Shaddy Zeineddine
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
 * @copyright 2008-2010 Shaddy Zeineddine
 * @license http://www.gnu.org/licenses/gpl.txt GPL v3 or later
 * @link http://code.google.com/p/steam-fw
 */

/**
 * environment
 *
 * Sets the user-defined environment identifier.
 *
 * Default: "development"
 * Suggestions: "development", "production", "staging"
 *
 * Alternatively, you can include a file which defines the environment.
 */
# include 'environment.php';
$environment = 'development';

/**
 * locale
 *
 * Defines the default locale for use with Zend_Locale.
 *
 * Default: "en_US.utf8"
 */
$locale = 'en_US.utf8';

/**
 * timezone
 *
 * Defines the default timezone.
 *
 * Default: "America/Los_Angeles"
 */
$timezone = 'America/Los_Angeles';

/**
 * base_uri
 *
 * Sets the global base URI of the Steam installation. The URI is relative to
 * the domain. Therefore do not include the domain in the URI.
 *
 * Default: "/steam"
 * Suggestions: "/steam", ""
 */
$base_uri = '';

/**
 * libraries
 *
 * Adds user-defined libraries to the class autoloader. Typically libraries
 * should end with an underscore such as "Steam_".
 *
 * Default: none
 */
$libraries = array();

/**
 * logs
 *
 * Defines the logging facilities used by Steam.
 *
 * Default: "firebug", "php"
 * Suggestions: "firebug", "php", "syslog"
 */
$logs = array('firebug', 'php');

/**
 * error_page
 *
 * Sets the default error page view. Empty values will prompt Steam to use its
 * own basic error page.
 *
 * Default: ""
 * Suggestions: "", "error", "error_page"
 */
$error_page = '';

/**
 * static_maxage
 *
 * Defines length browsers should cache static resources. The format is a
 * number followed by a letter identifying the units. The following units
 * are recognized: d=day, m=month, y=year. The maximum allowed value is
 * one year, the minimum recommended value is one day.
 *
 * Default: "30d"
 * Suggestions: "30d", "1m", "1y"
 */
$static_maxage = '30d';

/**
 * static_path
 *
 * Sets the partial uri path which is used to access static resources. This
 * setting much match the path used in your portals to map to static resources.
 *
 * Default: "static"
 * Suggestions: "static", "assets"
 */
$static_path = 'static';

/**
 * fingerprinting
 *
 * Enables or disables the use of static resource fingerprinting which allows
 * resource expiration dates to be set to a year in the future and maximize
 * the browser cache hit rate.
 *
 * Default: "false"
 * Suggestions: "true", "false"
 */
$fingerprinting = false;

/**
 * cache_backend
 *
 * Defines the shared data cache which relies upon Zend_Cache.
 *
 * Default: ""
 * Suggestions: "File", "Memcached"
 */
$cache_backend = '';

/**
 * cache_params
 *
 * Parameter array sent to the Zend_Cache backend.
 *
 * Default: ""
 */
$cache_params = array();

/**
 * db_adapter
 *
 * The database adapter to use for all connections. The adapter is used by
 * Zend_Db.
 *
 * Default: ""
 * Suggestions: "Mysqli", "Pdo_Pgsql", "Pdo_Sqlite"
 */
$db_adapter = '';

/**
 * db_params
 *
 * Array of parameter arrays to send to the database adapter. This array is
 * broken down into three sections: "write", "read", and "search". If you only
 * have one database host, define it under "write". In load balanced systems,
 * define the different type of hosts under the relevant section.
 */
$db_params        = array(
    'write'  => array(
        array(
            'host'     => 'localhost',
            'socket'   => '/var/run/mysqld/mysqld.sock',
            'username' => 'root',
            'password' => '',
            'dbname'   => 'epcc',
            'adapterNamespace' => 'Steam_Db_Adapter'
            ),
    ),
    
    'read'   => array(
    ),
    
    'search' => array(
    ),
);

/**
 * portals
 *
 * Array of web portals which map URI's to resources. The portals are checked in
 * order against the current request URI until one matches.
 *
 * Parameters:
 *     domain    : regular expression to match against the domain (optional)
 *     path      : regular expression to match against the URI path
 *     app       : the name of the application that will accept the request
 *     type      : the type of resource, one of {"view", "data"} or user-defined
 *     formatter : a callback function to format the path (optional)
 *     resource  : the name of the resource to accept the request (optional)
 */
$portals = array(
    array(
        'app'    => 'example',
        'domain' => '~^.*$~',
        'path'   => '~^/actions/(.*)~',
        'type'   => 'action',
    ),
    array(
        'app'    => 'example',
        'domain' => '~^.*$~',
        'path'   => '~^/data/(.*)~',
        'type'   => 'model',
    ),
    array(
        'app'    => 'example',
        'domain' => '~^.*$~',
        'path'   => '~^/static/(.*)~',
        'type'   => 'static',
    ),
    array(
        'app'    => 'example',
        'domain' => '~^.*$~',
        'path'   => '~^.*~',
        'type'   => 'view',
    ),
);

?>
