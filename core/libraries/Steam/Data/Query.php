<?php

class Steam_Data_Query
{
    protected $sxe;
    
    public function __construct($xml)
    {
        if (is_array($xml))
        {
            $xml = self::get_to_xml($xml);
        }
        
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
    
    protected function get_to_xml($get)
    {
        $xml = '<?xml version="1.0"?><data>';
        
        foreach ($get as $name => $value)
        {
            $xml .= '<' . $name . '>' . htmlspecialchars($value) . '</' . $name . '>';
        }
        
        return $xml . '</data>';
    }
}

?>
