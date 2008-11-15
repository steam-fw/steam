<?php

class Steam_Lang
{
    protected static $translators = array();
    protected static $app_name = 'steam';
    
    public static function source($app_name)
    {
        self::$app_name = $app_name;
    }
    
    public static function get($app_name = NULL)
    {
        if (!is_null($app_name))
        {
            self::source($app_name);
        }
        
        if (!isset(self::$translators[self::$app_name]))
        {
            self::$translators[self::$app_name] = new Zend_Translate('Steam_Translate', Steam::$base_dir . 'apps/' . self::$app_name . '/translations', NULL, array('scan' => Zend_Translate::LOCALE_DIRECTORY));
        }
        
        return self::$translators[self::$app_name];
    }
    
    public static function _($string)
    {
        $args = func_get_args();
        
        if (count($args) > 1)
        {
            $string = call_user_func_array('sprintf', $args);
        }
        
        return self::get()->_($string);
    }
}

?>
