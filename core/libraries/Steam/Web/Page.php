<?php
/**
 * Steam Page Class
 *
 * This class is used to create documents to output to browsers.
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

class Steam_Web_Page
{
    /**
     * @see __construct
     */
    public static function factory($type = NULL)
    {
        $class = __CLASS__;
        
        return new $class($type);
    }
    
    /**
     * Creates a new Steam_Web_Page object of the specified type.
     *
     * @return object
     * @param string $type document type
     */
    public function __construct($type = NULL)
    {
    }
}

?>
