<?php

class Steam_Data_Query implements Iterator, ArrayAccess
{
    protected $sxe;
    protected $index = 0;
    
    public $max_items      = 0;
    public $start_index    = 1;
    public $items_per_page = 0;
    
    public function __construct($xml = NULL)
    {
        if (is_null($xml))
        {
            $xml = array();
        }
        
        if (is_array($xml))
        {
            $xml = self::array_to_xml($xml);
        }
        
        try
        {
            $this->sxe = new SimpleXMLElement($xml);
        }
        catch (Exception $exception)
        {
            throw new Steam_Exception_Type($exception->getMessage());
        }
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
        return $this->offsetGet($this->index++);
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
        try
        {
            $item_element = $this->sxe->items->addChild('item');
        }
        catch (Exception $exception)
        {
            $this->sxe->addChild('items');
            $item_element = $this->sxe->items->addChild('item');
        }
        
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
                    throw new Steam_Exception_General('Invalid Format.');
                }
                
                continue;
            }
            
            $item_element->addChild($name, $value);
        }
    }
    
    public function get_item($index)
    {
        return $this->offsetGet($index);
    }
    
    public function next_item()
    {
        return $this->next();
    }
    
    public function __toString()
    {
        return $this->sxe->asXML();
    }
}

?>
