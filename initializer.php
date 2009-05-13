<?php
/**
 * Steam Initializer
 *
 * This script initializes the Steam environment.
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

// first thing's first, begin output buffering
ob_start();

// identify the directory where Steam resides
$base_dir = str_replace('initializer.php', '', __FILE__);

// include the Steam class
require_once $base_dir . 'libraries/Steam.php';

// move the base_dir var to the Steam class and unset the temporary var
Steam::$base_dir = $base_dir;
unset($base_dir);

// initialize the Steam class, this loads the config
Steam::initialize();

// include useful functions to augment PHP's built-in functions
require_once Steam::$base_dir . 'functions.php';

// fire the ready event
Steam_Event::trigger('ready');

?>
