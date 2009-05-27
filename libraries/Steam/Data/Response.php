<?php

class Steam_Data_Response
{
    protected $sxe;
    
    public function __construct()
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>' . "\n" .
               '<data>' . "\n" .
               '    <status>500</status>' . "\n" .
               '    <error></error>' . "\n" .
               '    <total_items>0</total_items>' . "\n" .
               '    <start_index>1</start_index>' . "\n" .
               '    <items></items>' . "\n" .
               '</data>';
        
        $this->sxe = new SimpleXMLElement($xml);
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
    
    public function add_items(&$items)
    {
        foreach ($items as &$item)
        {
            $item_elemment = $this->items->addChild('item');
            
            foreach ($item as $name => $value)
            {
                if ($value === '')
                {
                    $value = NULL;
                }
                
                $item_elemment->addChild($name, $value);
            }
            
            $item = NULL;
        }
        unset($item);
    }
    
    public function get_item($index)
    {
        return $this->sxe->items->item[$index];
    }
    
    public function next_item()
    {
        return $this->get_item($this->index++);
    }
}

?>
