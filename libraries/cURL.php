<?php
/**
 * cURL wrapper class
 *
 * This class provides an object-oriented way of using the cURL library in
 * PHP. It does not natively support multiple processing, but the internal
 * cURL handle can be retrieved using gethandle(). Options can be set using
 * either the cURL constants or, alternatively, with string identifiers. For
 * instance, executing $curl->setopt('post', true) is the equivalent of
 * executing curl_setopt($ch, CURLOPT_POST, true). Finally, by default the
 * RETURNTRANSFER option is set to true. This differs than the PHP default
 * because it is almost always used. To disable it, set the option to false.
 *
 * @author Shaddy Zeineddine <shaddy@dradistribution.com>
 * @date 2011-07-01
 */

class cURL
{
    private $ch;
    private $closed = false;
    
    public function __construct($url = NULL)
    {
        $this->ch = curl_init($url);
        curl_setopt($this->ch, \CURLOPT_RETURNTRANSFER, true);
    }
    
    public function __destruct()
    {
        $this->close();
    }
    
    public function __clone()
    {
        $this->ch = curl_copy_handle($this->ch);
    }
    
    public function close()
    {
        if ($this->closed) return;
        else $this->closed = true;
        return curl_close($this->ch);
    }
    
    public function errno()
    {
        return curl_errno($this->ch);
    }
    
    public function error()
    {
        return curl_error($this->ch);
    }
    
    public function exec()
    {
        return curl_exec($this->ch);
    }
    
    public function getinfo($opt = NULL)
    {
        return is_null($opt) ? curl_getinfo($this->ch) : curl_getinfo($this->ch, $opt);
    }
    
    public function setopt($option, $value = NULL)
    {
        if (is_array($option)) return $this->setopt_array($option);
        
        return curl_setopt($this->ch, $this->convertopt($option), $value);
    }
    
    private function setopt_array($options)
    {
        $options_copy = array();
        foreach ($options as $option => $value) $options_copy[$this->convertopt($option)] = $value;
        
        return curl_setopt_array($this->ch, $options_copy);
    }
    
    public function version($age = \CURLVERSION_NOW)
    {
        return curl_version($this->ch, $age);
    }
    
    public function gethandle()
    {
        return $this->ch;
    }
    
    private function convertopt($option)
    {
        if (is_int($option))
            return $option;
        if (defined('\\CURLOPT_'  . strtoupper($option)))
            return constant('\\CURLOPT_'  . strtoupper($option));
        if (defined('\\CURLINFO_' . strtoupper($option)))
            return constant('\\CURLINFO_' . strtoupper($option));
        else return 0;
    }
}

?>
