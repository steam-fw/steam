<?php
/**
 * Steam Exception Class
 *
 * All Steam exceptions extend this base class.
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

abstract class Exception extends \Exception
{
    /**
     * Creates a new instance of Steam_Exception.
     *
     * @param string $message message
     * @param int $code code
     * @param string $file file
     * @param int $line line number
     */
    public function __construct($message = NULL, $code = NULL, \Exception $previous = NULL)
    {
        if (is_null($message))
        {
            $message = $this->defaultMessage();
        }
        
        parent::__construct($message, $code, $previous);
    }
    
    public function setFile($file)
    {
        $this->file = $file;
    }
    
    public function setLine($line)
    {
        $this->line = $line;
    }
    
    /**
     * Returns the default message in the current language.
     *
     * @return void
     * @param &$message localized error message
     */
    abstract protected function defaultMessage();
}
?>
