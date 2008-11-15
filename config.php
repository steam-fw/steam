<?php
/**
 * Steam configuration file
 *
 * Stores the values for the basic configuration of Steam.
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

$locale           = 'en_US.utf8';
$timezone         = 'America/Los_Angeles';
$base_uri         = '/steam';

$cache_backend    = 'Memcached';
$cache_params     = array(
    'servers' => array(
        array(
            'host' => 'localhost',
            'port' => '11211',
            ),
        ),
    );

$db_adapter       = 'Mysqli';
$db_params        = array(
    'write'  => array(
        array(
            'host'     => 'localhost',
            'username' => 'root',
            'password' => '',
            'dbname'   => 'steam'
            ),
        ),
    'read'   => array(
        ),
    'search' => array(
        ),
    );

?>
