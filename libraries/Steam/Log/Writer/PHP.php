<?php
/**
 * PHP Log Writer
 *
 * This class allows errors to be logged through PHP's default facility.
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

namespace Steam\Log\Writer;

class PHP extends \Zend_Log_Writer_Abstract
{
    public static function factory($config)
    {
        return new self();
    }
    
    protected function _write($event)
    {
        if (false === @error_log($event['message']))
        {
            throw new \Zend_Log_Exception("Unable to log using PHP's default logging facility.");
        }
    }

}
