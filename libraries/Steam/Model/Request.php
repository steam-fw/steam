<?php
/**
 * Steam Model Request Class
 *
 * This class provides a standardized method of requesting model.
 *
 * Copyright 2008-2010 Shaddy Zeineddine
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
 * @copyright 2008-2010 Shaddy Zeineddine
 * @license http://www.gnu.org/licenses/gpl.txt GPL v3 or later
 * @link http://code.google.com/p/steam-fw
 */

namespace Steam\Model;

class Request implements \Iterator, \ArrayAccess
{
    protected $sxe;
    protected $index = 0;
    
    public function __construct($xml = NULL)
    {
        if (is_null($xml) or !$xml)
        {
            $xml = array();
        }
        
        if (is_array($xml))
        {
            $xml = self::array_to_xml($xml);
        }
        
        try
        {
            $this->sxe = new \SimpleXMLElement($xml);
        }
        catch (\Exception $exception)
        {
            throw new \Steam\Exception\Type($exception->getMessage());
        }
        
        if (!isset($this->sxe->response_format)) $this->sxe->response_format = 'xml';
        if (!isset($this->sxe->total_results  )) $this->sxe->total_results   = 0;
        if (!isset($this->sxe->total_items    )) $this->sxe->total_items     = 0;
        if (!isset($this->sxe->max_items      )) $this->sxe->max_items       = 0;
        if (!isset($this->sxe->start_index    )) $this->sxe->start_index     = 1;
        if (!isset($this->sxe->items          )) $this->sxe->addChild('items');
    }
    
    public function __set($name, $value)
    {
        $this->sxe->$name = $value;
    }
    
    public function __get($name)
    {
        return $this->sxe->$name;
    }
    
    public function __isset($name)
    {
        return isset($this->sxe->$name);
    }
    
    public function __unset($name)
    {
        unset($this->sxe->$name);
    }
    
    public function __call($method, $arguments)
    {
        return call_user_func_array(array($this->sxe, $method), $arguments);
    }
    
    public function count()
    {
        return count($this->sxe->items->item);
    }
    
    public function current()
    {
        return $this->offsetGet($this->index);
    }
    
    public function key()
    {
        return $this->index;
    }
    
    public function next()
    {
        $this->index++;
    }
    
    public function rewind()
    {
        $this->index = 0;
    }
    
    public function valid()
    {
        return $this->offsetExists($this->index);
    }
    
    public function offsetExists($index)
    {
        return isset($this->sxe->items->item[$index]);
    }
    
    public function offsetGet($index)
    {
        return $this->sxe->items->item[$index];
    }
    
    public function offsetSet($index, $value)
    {
        $this->sxe->items->item[$index] = $value;
    }
    
    public function offsetUnset($index)
    {
        unset($this->sxe->items->item[$index]);
    }
    
    protected function array_to_xml($array)
    {
        $xml  = '<?xml version="1.0"?>' . "\n";
        $xml .= self::xml_element('data', $array);
        
        return $xml;
    }
    
    public function xml_to_std($sxe)
    {
        $item = new \stdClass();
        
        foreach ($sxe as $name => $data)
        {
            $count = count($data);
            
            if ($count > 0)
            {
                $item->{$name} = array();
                
                foreach ($data as $value)
                {
                    $item->{$name}[] = (string) $value;
                }
            }
            else
            {
                $item->{$name} = (string) $data;
            }
        }
        
        return $item;
    }
    
    private function xml_element($name, $value, $tags = true)
    {
        $xml = '';
        
        if (is_array($value) or is_object($value))
        {
            $xml .= "\n";
            
            foreach ($value as $_name => $_value)
            {
                if (is_numeric($_name))
                {
                    $xml .= self::xml_element($name, $_value, false);
                }
                else
                {
                    $xml .= self::xml_element($_name, $_value);
                }
            }
        }
        else if (is_string($value))
        {
            $xml .= htmlspecialchars($value);
        }
        else
        {
            $xml .= $value;
        }
        
        if ($tags === false)
        {
            return $xml . "\n";
        }
        else
        {
            return '<' . $name . '>' . $xml . '</' . $name . '>' . "\n";
        }
    }
    
    public function add_items(&$items)
    {
        foreach ($items as &$item)
        {
            $this->add_item($item);
            $item = NULL;
        }
        unset($item);
    }
    
    public function add_item($item)
    {
        $item_element = $this->sxe->items->addChild('item');
        
        foreach ($item as $name => $value)
        {
            if ($value === '')
            {
                $value = NULL;
            }
            elseif (is_array($value))
            {
                if (count($value) == 1)
                {
                    $tag = key($value);
                    $sub_element = $item_element->addChild($name);
                    
                    foreach ($value[$tag] as $sub_value)
                    {
                        $sub_element->addChild($tag, $sub_value);
                    }
                }
                else
                {
                    throw new \Steam\Exception\General('Invalid Format.');
                }
                
                continue;
            }
            
            $item_element->addChild($name, htmlspecialchars($value));
        }
        
        $this->sxe->total_items = (int) $this->sxe->total_items + 1;
    }
    
    public function get_item($index)
    {
        return $this->offsetGet($index);
    }
    
    public function next_item()
    {
        return $this->next();
    }
    
    /**
     * Helper method for getting/setting the parameters from the request.
     *
     * @param array parameters
     * @return void|array parameters
     */
    public function parameters($parameters = NULL)
    {
        if (is_array($parameters))
        {
            $this->sxe->parameters = http_build_query($parameters);
        }
        else
        {
            $item = array();
            
            if ($this->offsetExists(0))
            {
                $item = (array) $this->offsetGet(0);
            }
            
            return array_merge(http_parse_query((string) $this->sxe->parameters), $item);
        }
    }
    
    public function asJSON()
    {
        return '';
    }
    
    public function asJSONP($callback)
    {
        return $callback . '(' . $this->asJSON() . ')';
    }
    
    public function __toString()
    {
        return $this->sxe->asXML();
    }
}

?>
