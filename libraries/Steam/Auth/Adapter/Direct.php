<?php

require_once 'Zend/Auth/Adapter/Interface.php';

class Steam_Auth_Adapter_Direct implements \Zend_Auth_Adapter_Interface
{
    protected $identity;
    
    public function __construct($identity = array())
    {
        $this->identity = $identity;
    }
    
    public function authenticate()
    {
        return new \Zend_Auth_Result(\Zend_Auth_Result::SUCCESS, $this->identity);
    }
}

?>
