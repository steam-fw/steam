<?php
/**
 * Steam MySQL Database Abstraction Class
 *
 * This class simplifies interaction with a MySQL database.
 *
 * Copyright 2008-2009 Shaddy Zeineddine
 *
 * This file is part of Steam, a PHP application framework.
 *
 * This file incorporates code from TrellisDB.php which is part of Trellis.
 * Copyright 2008 The Learning Network, Inc.
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
 * @copyright 2008 The Learning Network, Inc.
 * @license http://www.gnu.org/licenses/gpl.txt GPL v3 or later
 * @link http://code.google.com/p/steam-fw
 */

class Steam_Db_MySQL
{
    protected $mysqli;
    public    $affected_rows    = 0;
    public    $selected_rows    = 0;
    public    $query_count      = 0;
    public    $last_query_time  = 0;
    public    $total_query_time = 0;
    
    public function __construct($parameters)
    {
        $this->mysqli = new mysqli($parameters['host'],  $parameters['user'], $parameters['password'], $parameters['database']);
        
        if ($this->mysqli->connect_errno)
        {
            throw new Steam_Exception_Database($this->mysqli->connect_error);
        }
    }
    
    public function close()
    {
        $this->mysqli->close();
    }
    
    public function select_db($database)
    {
        $this->mysqli->select_db($database);
        
        if ($this->mysqli->errno)
        {
            throw new Steam_Exception_Database($this->mysqli->error);
        }
    }

    private function query($query, $mode = NULL)
    {
        $this->query_count++;
        $start_time = microtime(true);
        $result     = $this->mysqli->query($query, $mode);
        $end_time   = microtime(true);
        
        if ($this->mysqli->errno)
        {
            throw new Steam_Exception_Database($this->mysqli->error);
        }
        
        $this->last_query_time   = $end_time - $start_time;
        $this->total_query_time += $this->last_query_time;
        $this->affected_rows     = $this->mysqli->affected_rows;
        
        return $result;
    }
    
    public function select_rows($query, $key = '')
    {
        $result = $this->query($query);
        $this->selected_rows = $result->num_rows;
        $rows = array();
        $row  = array();
        
        if ($key)
        {
            while ($row = $result->fetch_assoc())
            {
                $rows[$row[$key]] = $row;
            }
        }
        else
        {
            while ($row = $result->fetch_assoc())
            {
                $rows[] = $row;
            }
        }
        
        $result->close();
        
        return $rows;
    }
    
    public function select_row($query, $indices = '')
    {
        $result = $this->query($query, MYSQLI_USE_RESULT);
        $row = $this->next_row($result, $indices);
        
        if ($row)
        {
            $result->close();
            return $row;
        }
        else
        {
            return array();
        }
    }
    
    public function select_column($query, $auto_assoc = true)
    {
        $result = $this->query($query);
        $this->selected_rows = $result->num_rows;
        $rows = array();
        $row  = array();
        
        if ($result->field_count > 1 and $auto_assoc)
        {
            while ($row = $result->fetch_row())
            {
                $rows[$row[0]] = $row[1];
            }
        }
        else
        {
            while ($row = $result->fetch_row())
            {
                $rows[] = $row[0];
            }
        }
        
        $result->close();
        return $rows;
    }
    
    public function select_field($query)
    {
        $result = $this->query($query, MYSQLI_USE_RESULT);
        $row = $this->next_row($result, 'numeric');
        
        if ($row)
        {
            $result->close();
            return $row[0];
        }
        else
        {
            return '';
        }
    }
    
    public function next_row($result, $indices = '')
    {
        switch ($indices)
        {
            case 'object':
                $row = $result->fetch_object();
                break;
            case 'num':
            case 'numeric':
                $row = $result->fetch_row();
                break;
            case 'array':
            case 'both':
                $row = $result->fetch_array();
                break;
            default:
                $row = $result->fetch_assoc();
        }
        
        if (!$row)
        {
            $result->close();
        }
        
        return $row;
    }
    
    public function next_field($result)
    {
        $row = $this->next_row($result, 'numeric');
        return $row[0];
    }
    
    public function exists($table, $condition = '')
    {
        $condition = ($condition != '') ? ' WHERE ' . $condition : '';
        $query = 'SELECT 1 FROM ' . $table . $condition . ' LIMIT 1';
        $this->select_rows($query);
        
        return ($this->selected_rows) ? true : false;
    }

    public function count($table, $condition = '', $count_field = '')
    {
        $condition   = (trim($condition)   != '') ? ' WHERE ' . $condition : '';
        $count_field = (trim($count_field) != '') ? $count_field           : '*';
        $query = 'SELECT COUNT(' . $count_field . ') FROM ' . $table . $condition;
        $count = $this->select_field($query);
        
        return ($count) ? $count : 0;
    }
    
    public function last_insert_id()
    {
        return $this->mysqli->insert_id;
    }
    
    public function prepare($query)
    {
        return $this->mysqli->prepare($query);
    }
    
    public function start_transaction()
    {
        $this->mysqli->autocommit(false);
    }
    
    public function rollback()
    {
        $this->mysqli->rollback();
        $this->mysqli->autocommit(true);
    }
    
    public function commit()
    {
        $this->mysqli->commit();
        $this->mysqli->autocommit(true);
    }
    
    public function error()
    {
        if ($this->mysqli->errno)
        {
            return $this->mysqli->error;
        }
    }
    
    public function escape($string)
    {
        return $this->mysqli->real_escape_string($string);
    }
}

?>
