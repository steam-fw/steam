<?php
/**
 * Steam URI Class
 *
 * This class contains utilities for interacting with URIs.
 *
 * Copyright 2008-2009 Shaddy Zeineddine
 *
 * This file is part of Steam, a PHP application framework.
 *
 * Steam is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Steam is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category Frameworks
 * @package Steam
 * @copyright 2008-2009 Shaddy Zeineddine
 * @license http://www.gnu.org/licenses/gpl.txt GPL v3 or later
 * @link http://code.google.com/p/steam-fw
 */

class Steam_Web_URI
{
    protected $uri;
    protected $scheme;
    protected $domain;
    protected $path;
    protected $app_id;
    protected $app_name;
    protected $app_uri;
    protected $resource_name;
    protected $portal_uri;
    protected $api;
    
    /**
     * Returns a string representation of the URI.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->uri;
    }
    
    /**
     * Creates a new Steam_Web_URI object from a URI. If a URI is not specified,
     * it will be taken from the current request.
     *
     * @return object
     * @param string $uri URI
     */
    public function __construct($uri = NULL)
    {
        if (is_null($uri))
        {
            $this->scheme = 'http://';
            $this->domain = $_SERVER['HTTP_HOST'];
            $this->path   = preg_replace('/\\?.*$/', '', $_SERVER['REQUEST_URI']);
            
            $this->uri = $this->scheme . $this->domain . $this->path;
        }
        else
        {
            $this->uri = $uri;
            
            preg_match('/([a-z]+:\\/\\/)([a-z][a-z0-9.-]*)(\\/[^?]*)?/i', $this->uri, $matches);
            
            if (!array_key_exists(3, $matches))
            {
                throw new Steam_Exception_Type(Steam_Locale::_('The supplied string is not a valid URI.'));
            }
            
            $this->scheme = $matches[1];
            $this->domain = $matches[2];
            $this->path   = $matches[3];
        }
        
        $this->parse();
    }
    
    /**
     * parses the given URI or the current URI and extracts the app_id and
     * resource_name from it.
     *
     * @return void
     * @param string $uri URI
     */
    protected function parse()
    {
        try
        {
            $portal_data = Steam_Cache::get('/global/portal', $this->domain . $this->path);
            
            $this->resource_name = $portal_data['resource_name'];
        }
        catch (Steam_Exception_Cache $exception)
        {
            $db = Steam_Db::read();
            $select = $db->select();
            $select->from('portals', array('app_id', 'domain', 'path', 'resource_name', 'api'))
                   ->join('apps', 'apps.app_id = portals.app_id', array('app_name', 'app_uri'))
                   ->where($db->quote($this->domain) . ' LIKE domain AND ' . $db->quote($this->path) . ' LIKE CONCAT(' . $db->quote(Steam::$base_uri) . ', path)')
                   ->order('portal_sequence ASC');
            $portal_data = $select->query()->fetch();
            
            $this->resource_name = $portal_data['resource_name'];
            
            Steam_Cache::set('/global/portal', $this->domain . $this->path, $portal_data);
        }
        
        $this->app_id        = $portal_data['app_id'];
        $this->app_name      = $portal_data['app_name'];
        $this->app_uri       = Steam::$base_uri . $portal_data['app_uri']; //this should be the default app uri, rather than the portal ???
        $this->portal_uri    = rtrim(preg_replace('/%.*$/', '', Steam::$base_uri . $portal_data['path']), '/');
        $this->app_full_uri  = $this->scheme . $portal_data['domain'] . preg_replace('/%.*$/', '', Steam::$base_uri . $portal_data['path']);
        $this->api           = ($portal_data['api'] == 1) ? true : false;
        
        if ($this->resource_name == '')
        {
            $this->resource_name = trim(preg_replace('/^' . preg_quote(Steam::$base_uri . trim($portal_data['path'], '%'), '/') . '/i', '', $this->path), '/');
            
            // if there was no resource name specified, use default
            if ($this->resource_name == '')
            {
                $this->resource_name = 'default';
            }
        }
    }
    
    public function uri()
    {
        return $this->uri;
    }
    
    public function scheme()
    {
        return $this->scheme;
    }
    
    public function domain()
    {
        return $this->domain;
    }
    
    public function path()
    {
        return $this->path;
    }
    
    public function app_id()
    {
        return $this->app_id;
    }
    
    public function app_name()
    {
        return $this->app_name;
    }
    
    public function app_uri()
    {
        return $this->app_uri;
    }
    
    public function resource_name()
    {
        return $this->resource_name;
    }
    
    public function portal_uri()
    {
        return $this->portal_uri;
    }
    
    public function api()
    {
        return $this->api;
    }
}

?>
