<?php
/**
 * Steam Model Respose Class
 *
 * This class provides a standardized method of representing models.
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

namespace Steam\Model;

class Response extends Request
{
    
    public function __construct()
    {
        parent::__construct();
        
        $this->sxe->status = 200;
        $this->sxe->error  = '';
    }
    
    public function add_results(&$select)
    {
        $mysql  = $select->getAdapter()->getConnection();
        $result = mysqli_query($mysql, (string) $select, \MYSQLI_USE_RESULT);
        
        if ($result)
        {
            while ($item = $result->fetch_assoc()) $this->add_item($item);
            $result->free();
        }
    }
}

?>
