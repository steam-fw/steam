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
    private $start_index = NULL;
    private $max_results = NULL;
    private $sort_field  = NULL;
    private $sort_order  = NULL;
    
    private $params = array();
    private $request;
    private $response;
    private $schema;
    private $key;
    private $secondary = array();
    private $search = '';
    
    public function __construct(\Steam\Model\Request &$request, \Steam\Model\Response &$response, $schema = NULL)
    {
        $this->request  = $request;
        $this->response = $response;
        $this->schema   = $schema;
        
        if (!empty($request->parameters))
        {
            $this->params = http_parse_query((string) $request->parameters);
        }
        
        if (isset($this->params['start-index']))
        {
            $this->start_index = $this->params['start-index'];
            unset($this->params['start-index']);
        }
        elseif (!empty($this->request->start_index))
        {
            $this->start_index = (int) $this->request->start_index;
        }
        
        if (isset($this->params['max-results']))
        {
            $this->max_results = $this->params['max-results'];
            unset($this->params['max-results']);
        }
        elseif (!empty($this->request->max_results))
        {
            $this->max_results = (int) $this->request->max_results;
        }
        
        if (isset($this->params['sort-field']))
        {
            $this->sort_field = $this->params['sort-field'];
            unset($this->params['sort-field']);
        }
        elseif (!empty($this->request->sort_field))
        {
            $this->sort_field = (string) $this->request->sort_field;
        }
        
        if (isset($this->params['sort-order']))
        {
            $this->sort_order = $this->params['sort-order'];
            unset($this->params['sort-order']);
        }
        elseif (!empty($this->request->sort_order))
        {
            $this->sort_order = (string) $this->request->sort_order;
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
                $select->where($select->getAdapter()->quoteIdentifier($field) . ' = ' . $select->getAdapter()->quote($value));
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
        if (!$this->request->search_string)
        {
            return;
        }
        
        $this->search = true;
        
        $options = array();
        $search_fields = array();
        
        foreach (explode(',', $this->request->search_fields) as $search_field)
        {
            if ($search_field = trim($search_field))
            {
                $search_fields[] = $search_field;
            }
        }
        
        if (!count($search_fields))
        {
            return;
        }
        
        $db = $select->getAdapter();
        
        $options = array('stopwords' => false, 'min_length' => 2, 'max_words' => 5);
        $search_words = array();
        
        $stopwords = array(); //($options['stopwords']) ? \Steam\Setting::get('stopwords') : array();
        
        foreach (explode(' ', $this->request->search_string) as $search_word)
        {
            if (!$search_word = trim($search_word))
            {
                continue;
            }
            
            if (strlen($search_word) > $options['min_length'] and !in_array($search_word, $stopwords))
            {
                $search_words[$db->quote($search_word)] = strlen($search_word);
            }
            
            if (count($search_words) >= $options['max_words'])
            {
                break;
            }
        }
        
        if (!count($search_words))
        {
            //no search
            return;
        }
        
        $search = ' (';
        
        foreach ($search_fields as $search_field)
        {
            $search_field = $db->quoteIdentifier($search_field);
            
            foreach ($search_words as $search_word => $word_length)
            {
                #$search .= ' IF(LOCATE(' . $search_word . ', ' . $search_field . '), 1, 0) +';
                $search .= ' IF(LOCATE(' . $search_word . ', ' . $search_field . '), 3 + ((CHAR_LENGTH(' . $search_field . ') - CHAR_LENGTH(REPLACE(LOWER(' . $search_field . '), LOWER(' . $search_word . '), \'\'))) / ' . $word_length . '), 0) +';
            }
        }
        
        $this->search = rtrim($search, '+') . ')';
        $select->columns(array('search_rank' => new \Zend_Db_Expr($this->search)))
               ->having('search_rank > 0')
               ->order('search_rank DESC');
    }
    
    public function count(&$select)
    {
        if (is_null($this->max_results))
        {
            return;
        }
        
        $max_results = $this->max_results;
        
        $select_count = clone $select;
        $select_count->reset(\Zend_Db_Select::ORDER)->reset(\Zend_Db_Select::COLUMNS)->reset(\Zend_Db_Select::HAVING)->columns(array('row_count' => 'COUNT(*)'));
        
        if (is_null($this->start_index))
        {
            $select->limit($max_results);
        }
        else
        {
            $page = ((int) ($this->start_index / $max_results)) + 1;
            $select->limitPage($page, $max_results);
        }
        
        if ($this->search)
        {
            $select_count->where(new \Zend_Db_Expr($this->search . ' > 0'));
        }
        
        $this->response->total_items    = $select_count->query()->fetch(\Zend_Db::FETCH_OBJ)->row_count;
        $this->response->items_per_page = $max_results;
    }
    
    public function order(&$select)
    {
        if (is_null($this->sort_field))
        {
            return;
        }
        
        if (is_null($this->sort_field) or (string) $this->sort_order != 'dsc')
        {
            $sort_order = 'ASC';
        }
        else
        {
            $sort_order = 'DESC';
        }
        
        $select->reset(\Zend_Db_Select::ORDER);
        $select->order((string) $this->sort_field, $sort_order);
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
