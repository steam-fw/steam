<?php

namespace Steam;

class Action
{
    public static function perform($action_name, $request, $response)
    {
        try
        {
            $action = include \Steam::app_path('actions/' . $action_name . '.php');
        }
        catch (\Steam\Exeption\FileNotFound $exception)
        {
            \Steam\Error::display(404);
            exit;
        }
        
        if (!is_callable($action))
        {
            throw new \Steam\Exception\General('There was a problem performing the action.');
        }
        
        $action($request, $response);
        
        if (isset($_SERVER['HTTP_REFERER']))
        {
            $response->setRedirect($_SERVER['HTTP_REFERER'], 303);
        }
        
        \Steam\Event::trigger('steam-response');
        
        $response->sendResponse();
    }
    
    public static function shutdown()
    {
    }
}

?>
