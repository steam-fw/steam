<?php

if (!isset($argv[2]))
{
    print 'Invalid Arguments';
    exit(1);
}

$application   = $argv[1];
$resource_type = $argv[2];
$resource_name = '';

switch ($argv[2])
{
    case 'action':
    case 'view':
        if ($argc < 4)
        {
            print 'Invalid Arguments';
            exit(1);
        }
        
        $resource_name = $argv[3];
        $sid = 4;
	break;
    case 'model':
        if ($argc < 5)
        {
            print 'Invalid Arguments';
            exit(1);
        }
        
        switch ($argv[3])
        {
            case 'create':
                $_SERVER['REQUEST_METHOD'] = 'POST';
                break;
            case 'retrieve':
                $_SERVER['REQUEST_METHOD'] = 'GET';
                break;
            case 'update':
                $_SERVER['REQUEST_METHOD'] = 'PUT';
                break;
            case 'delete':
                $_SERVER['REQUEST_METHOD'] = 'DELETE';
                break;
            default:
                $_SERVER['REQUEST_METHOD'] = 'HEAD';
        }
        
        $temp = explode('?', $argv[4], 2);
        $resource_name = $temp[0];
        
        if (isset($temp[1]))
        {
            $_SERVER['QUERY_STRING'] = $temp[1];
        }
        else
        {
            $_SERVER['QUERY_STRING'] = '';
        }
        
        unset($temp);
        $sid = 5;
        
        break;
}

if (isset($argv[$sid]))
{
    session_id($argv[$sid]);
}

include 'functions.php';

include 'libraries/Steam.php';

ob_start();

\Steam::load_config();

\Steam::initialize();

Steam::set_request($application, $resource_type, $resource_name);

\Steam::dispatch();

\Steam\Event::trigger('steam-complete');

?>
