<?php

class Steam_Loader
{
    public static $directories = array();
    
    public static function initialize($libraries = array())
    {
        require_once "Zend/Loader.php";
        require_once "Zend/Loader/Autoloader.php";
        
        Zend_Loader_Autoloader::getInstance()->setDefaultAutoloader(array('Steam_Loader', 'load'))->registerNamespace($libraries);
    }
    
    public static function register($library, $directory = NULL)
    {
        self::$directories[$library] = $directory;
        
        Zend_Loader_Autoloader::getInstance()->registerNamespace($library);
    }
    
    public static function load($class)
    {
        try
        {
            $directory = NULL;
            $namespace = substr($class, 0, strpos($class, '_') + 1);
            
            if (isset(self::$directories[$namespace]))
            {
                $directory = self::$directories[$namespace];
            }
            
            Zend_Loader::loadClass($class, $directory);
        }
        catch (Exception $exception)
        {
            Steam_Error::exception_handler($exception);
            
            return false;
        }
        
        return $class;
    }
}

?>
