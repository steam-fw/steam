<?php

class Steam_Data_Response
{
    public $status = 500;
    public $error  = '';
    public $items  = array();
    public $total_items = 0;
    public $start_index = 1;
    
    public function get_xml()
    {
        $items = '';
        
        foreach ($this->items as $item)
        {
            $items .= '        <item>' . "\n";
            
            foreach ($item as $field => $value)
            {
                $items .= $this->get_field_xml($field, $value);
            }
            
            $items .= '        </item>' . "\n";
        }
        
        $xml =
            '<?xml version="1.0" encoding="utf-8"?>' . "\n" .
            '<data>' . "\n" .
            '    <items>' . "\n" .
            $items .
            '    </items>' . "\n" .
            '</data>' . "\n";
        
        return $xml;
    }
    
    protected function get_field_xml($field, $value, $depth = 0)
    {
        $indent = str_repeat('    ', $depth + 3);
        
        if (is_array($value))
        {
            $xml = $indent . '<' . $field . '>' . "\n";
            
            foreach ($value as $subfield => $subvalue)
            {
                $xml .= $this->get_field_xml($subfield, $subvalue, $depth + 1);
            }
            
            $xml .= $indent . '</' . $field . '>' . "\n";
        }
        else
        {
            $xml = $indent . '<' . $field . '>' . htmlspecialchars($value) . '</' . $field . '>' . "\n";
        }
        
        return $xml;
    }
}

?>
