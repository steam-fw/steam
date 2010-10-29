<?php

namespace Steam;

class Model \Iterator, \ArrayAccess
{
    public $max_items   = 0;
    public $total_items = 0;
    public $start_index = 1;
    public $status      = 0;
    public $error       = '';
    
    private $items;
    private $index = 0;
    
    public function __construct()
    {
    }
    
    public function count()
    {
        return count($this->items);
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
        return isset($this->items[$index]);
    }
    
    public function offsetGet($index)
    {
        return $this->items->item[$index];
    }
    
    public function offsetSet($index, $value)
    {
        $this->items->item[$index] = $value;
    }
    
    public function offsetUnset($index)
    {
        unset($this->items->item[$index]);
    }
    
    public function add($items)
    {
        foreach ($items as $item)
        {
            $this->items[] = $item;
        }
    }
    
    public __toString()
    {
        $xml = '<?xml ?>' . "\n" . '<data>';
        
        foreach ($this->items as $item)
        {
        }
        
        $xml .= '</data>';
        
        return $xml;
    }
}

?>
