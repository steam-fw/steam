<?php

namespace Steam;

class Format
{
    public static function implode($separator, $array)
    {
        $first = true;
        $string = '';
        
        foreach ($array as $item)
        {
            if ($first)
            {
                $string .= $item;
                $first = false;
            }
            else
            {
                $string .= $separator . $item;
            }
        }
        
        return $string;
    }
}

?>
