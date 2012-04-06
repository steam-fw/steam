<?php
/**
 * Steam SQL Helper Class
 *
 * This class simplifies retrieval from SQL databases
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

namespace Steam\Model;

class SQL
{
    private $start_index    = NULL;
    private $max_items      = NULL;
    private $sort_field     = NULL;
    private $sort_order     = NULL;
    private $search_fields  = NULL;
    private $search_string  = NULL;
    private $special_fields = array(
        'response_format',
        'start_index',
        'max_items',
        'sort_field',
        'sort_order',
        'search_fields',
        'search_string',
    );
    
    private $params = array();
    private $request;
    private $response;
    private $schema;
    private $key;
    private $secondary = array();
    private $search = '';
    public  $skip_count = false;
    
    public function __construct(\Steam\Model\Request &$request, \Steam\Model\Response &$response, $schema = NULL)
    {
        $this->request  = $request;
        $this->response = $response;
        $this->schema   = $schema;
        
        if (!empty($request->parameters))
            $this->params = http_parse_query((string) $request->parameters);
        
        foreach ($this->special_fields as $field)
        {
            if (isset($this->params[$field]))
                $this->{$field} = $this->params[$field];
            elseif (isset($this->request->{$field}))
                $this->{$field} = (string) $this->request->{$field};
        }
    }
    
    public function key($key)
    {
        $this->key = $key;
    }
    
    public function each($callback, $arguments = NULL)
    {
        if (is_null($arguments))
        {
            $arguments = array();
        }
        elseif (!is_array($arguments))
        {
            $arguments = array($arguments);
        }
        
        $this->secondary[] = array('callback' => $callback, 'arguments' => $arguments);
    }
    
    public function retrieve(&$select)
    {
        if ($this->request->resource_id)
        {
            $select->where($this->key . ' = ' . $select->getAdapter()->quote($this->request->resource_id));
            $this->response->total_items = 1;
        }
        else
        {
            foreach ($this->params as $field => $value)
            {
                if (in_array($field, $this->special_fields)) continue;
                
                $modifier = substr($field, -1, 1);
                $field    = substr($field, 0, strlen($field) - 1);
                
                switch ($modifier)
                {
                    case '>':
                        $condition = ' >= ' . $select->getAdapter()->quote($value);
                        break;
                    case '<':
                        $condition = ' <= ' . $select->getAdapter()->quote($value);
                        break;
                    case '~':
                        $condition = ' LIKE ' . $select->getAdapter()->quote($value);
                        break;
                    case '*':
                        $condition = ' LIKE ' . $select->getAdapter()->quote('%' . $value . '%');
                        break;
                    case '#':
                        $condition = ' RLIKE ' . $select->getAdapter()->quote($value);
                        break;
                    case '!':
                        $condition = ' <> ' . $select->getAdapter()->quote($value);
                        break;
                    case '|':
                        $condition = ' IN (' . $select->getAdapter()->quote(explode('|', $value)) . ')';
                        break;
                    default:
                        $field    .= $modifier;
                        $condition = ' = ' . $select->getAdapter()->quote($value);
                }
                
                $fields     = explode(',', $field);
                $conditions = array();
                
                foreach ($fields as $field)
                    $conditions[] = $select->getAdapter()->quoteIdentifier($field) . $condition;
                
                $select->where(implode(' OR ', $conditions));
            }
            
            $this->search($select);
            $this->count($select);
            $this->order($select);
        }
        
        $this->add_results($select);
        
        if ((int) $this->response->total_items)
        {
            $this->response->status = 200;
        }
        else
        {
            $this->response->status = 204;
        }
    }
    
    public function search(&$select)
    {
        if (!$this->search_string) return;
        
        $this->search = true;
        
        $options = array();
        $search_fields = array();
        
        foreach (explode(',', $this->search_fields) as $search_field)
            if ($search_field = trim($search_field))
                $search_fields[] = $search_field;
        
        if (!count($search_fields)) return;
        
        $db = $select->getAdapter();
        
        $options = array('stopwords' => false, 'min_length' => 1, 'max_words' => 5, 'ignore_repeats' => true);
        $search_words = array();
        
        $stopwords = array(); //($options['stopwords']) ? \Steam\Setting::get('stopwords') : array();
        
        foreach (explode(' ', $this->search_string) as $search_word)
        {
            if (!$search_word = trim($search_word))
                continue;
            
            if (strlen($search_word) >= $options['min_length'] and !in_array($search_word, $stopwords))
                $search_words[$db->quote($search_word)] = strlen($search_word);
            
            if (count($search_words) >= $options['max_words'])
                break;
        }
        
            //no search
        if (!count($search_words)) return;
        
        $search = ' (';
        
        if (isset($search_fields[1]))
        {
            $search_field = 'CONCAT_WS(\' \', ';
            foreach ($search_fields as $field) $search_field .= $db->quoteIdentifier($field) . ', ';
            $search_field = rtrim($search_field, ', ') . ')';
        }
        else $search_field = $search_fields[0];
        
        foreach ($search_words as $search_word => $word_length)
        {
            if ($options['ignore_repeats'])
                $search .= ' IF(LOCATE(' . $search_word . ', ' . $search_field . '), 1, 0) +';
            else
                $search .= ' IF(LOCATE(' . $search_word . ', ' . $search_field . '), 3 + ((CHAR_LENGTH(' . $search_field . ') - CHAR_LENGTH(REPLACE(LOWER(' . $search_field . '), LOWER(' . $search_word . '), \'\'))) / ' . $word_length . '), 0) +';
        }
        
        $this->search = rtrim($search, '+') . ')';
        $select->columns(array('search_rank' => new \Zend_Db_Expr($this->search)))
               ->having('search_rank > 0')
               ->order('search_rank DESC');
    }
    
    public function count(&$select)
    {
        if (is_null($this->max_items) or $this->skip_count) return;
        
        $select_count = clone $select;
        $select_count->reset(\Zend_Db_Select::ORDER);
        
        $having = $select->getPart(\Zend_Db_Select::HAVING);
        if (!isset($having[0])) $select_count->reset(\Zend_Db_Select::ORDER)->reset(\Zend_Db_Select::COLUMNS)->columns(array('row_count' => 'COUNT(*)'));
        else $select_count->columns(array('row_count' => 'COUNT(*)'));
        
        if ($this->search)
            $select_count->where(new \Zend_Db_Expr($this->search . ' > 0'));
        
        $max_items   = $this->max_items;
        $start_index = (is_null($this->start_index)) ? 1 : $this->start_index;
        
        $select->limit($max_items, $start_index - 1);
        $result = $select_count->query();
        
        $this->response->max_items      = $max_items;
        $this->response->start_index    = $start_index;
        $this->response->total_results  = 0;
        
        while ($row = $result->fetch()) $this->response->total_results += $row['row_count'];
    }
    
    public function order(&$select)
    {
        if (is_null($this->sort_field) or empty($this->sort_field))
        {
            return;
        }
        
        $order = strtolower(trim($this->sort_order));
        
        if ($order != 'desc' and $order != 'dsc')
        {
            $sort_order = 'ASC';
        }
        else
        {
            $sort_order = 'DESC';
        }
        
        $select->reset(\Zend_Db_Select::ORDER);
        $select->order(explode(',', str_replace(',', ' ' . $sort_order . ',', trim($this->sort_field, ',')) . ' ' . $sort_order));
    }
    
    private function add_results(&$select)
    {
        if (isset($this->secondary[0]))
        {
            $result = $select->query();
            $count = 0;
            
            while ($row = $result->fetch())
            {
                $count++;
                $item = $this->response->items->addChild('item');
                
                foreach ($row as $name => $value)
                {
                    if ($value === '')
                    {
                        $value = NULL;
                    }
                    
                    $item->addChild($name, $value);
                }
                
                foreach ($this->secondary as $secondary)
                {
                    call_user_func_array($secondary['callback'], array_merge(array(&$item, &$row), $secondary['arguments']));
                }
            }
        }
        else
        {
            $this->response->add_results($select);
            $count = count($this->response->items->item);
        }
        
        if ($count)
        {
            if (!$this->request->max_items)
            {
                $this->response->total_items = $count;
            }
        }
        else
        {
            $this->response->total_items = 0;
        }
    }
}

?>
