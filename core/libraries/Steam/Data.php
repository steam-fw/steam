<?php

class Steam_Data
{
    public static function create($resource, Steam_Data_Query $query)
    {
        $response = self::request('create', $resource, $query);
        
        return $response;
    }
    
    public static function retrieve($resource, Steam_Data_Query $query = NULL)
    {
        $response = self::request('retrieve', $resource, $query);
        
        return $response;
    }
    
    public static function update($resource, Steam_Data_Query $query)
    {
        $response = self::request('update', $resource, $query);
        
        return $response;
    }
    
    public static function delete($resource)
    {
        $response = self::request('delete', $resource);
        
        return $response;
    }
    
    public static function request($method, $resource, Steam_Data_Query $query = NULL)
    {
        $response = new Steam_Data_Response;
        
        if (!preg_match('/^([^\\/]+)\\/([^\\/]+)(\\/.*)?$/', $resource, $resource_components))
        {
            $response->status = 400;
            return $response;
        }
        
        try
        {
            $parameters = explode('/', trim($resource_components[3], '/'));
            include Steam::$base_dir . 'apps/' . $resource_components[1] . '/queries/' . $resource_components[2] . '/' . $method . '.php';
        }
        catch (Steam_Exception_Auth $exception)
        {
            $response->status = 401;
            $response->error  = $exception->getMessage();
        }
        catch (Steam_Exception_FileNotFound $exception)
        {
            if (file_exists(Steam::$base_dir . 'apps/' . $resource_components[1] . '/queries/' . $resource_components[2]))
            {
                $response->status = 405;
            }
            else
            {
                $response->status = 404;
                $response->error  = $exception->getMessage();
            }
        }
        catch (Exception $exception)
        {
            $response->status = 500;
            $response->error  = $exception->getMessage();
        }
        
        return $response;
    }
}

?>
