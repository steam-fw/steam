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
}

?>
