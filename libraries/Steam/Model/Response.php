<?php

namespace Steam\Model;

class Response extends Request
{
    
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
        
        $this->sxe = new \SimpleXMLElement($xml);
    }
    
    public function add_results(&$select)
    {
        $statement = $select->query();
        
        while ($item = $statement->fetch())
        {
            $this->add_item($item);
        }
    }
}

?>
