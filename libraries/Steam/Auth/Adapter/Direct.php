<?php
/**
 * Steam Direct Auth Class
 *
 * This class provides a way of forcing authentication with Zend Auth.
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


require_once 'Zend/Auth/Adapter/Interface.php';

class Steam_Auth_Adapter_Direct implements \Zend_Auth_Adapter_Interface
{
    protected $identity;
    
    public function __construct($identity = array())
    {
        $this->identity = $identity;
    }
    
    public function authenticate()
    {
        return new \Zend_Auth_Result(\Zend_Auth_Result::SUCCESS, $this->identity);
    }
}

?>
