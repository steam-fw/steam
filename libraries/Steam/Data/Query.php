<?php

class Steam_Data_Query
{
    protected $sxe;
    protected $index = 0;
    
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
            $item_elemment = $this->sxe->items->addChild('item');
        }
        catch (Exception $exception)
        {
            $this->sxe->addChild('items');
            $item_elemment = $this->sxe->items->addChild('item');
        }
        
        foreach ($item as $name => $value)
        {
            if ($value === '')
            {
                $value = NULL;
            }
            
            $item_elemment->addChild($name, $value);
        }
    }
    
    public function get_item($index)
    {
        return $this->sxe->items->item[$index];
    }
    
    public function next_item()
    {
        return $this->get_item($this->index++);
    }
    
    public function rewind()
    {
        $this->index = 0;
    }
}

?>
