<?php

namespace Steam\Model;

class Response extends Request
{
    
    public function __construct()
    {
        parent::__construct();
        
        $this->sxe->status = 200;
        $this->sxe->error  = '';
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
