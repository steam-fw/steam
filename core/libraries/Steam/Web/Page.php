<?php
/**
 * Steam Page Class
 *
 * This class is used to create documents to output to browsers.
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

class Steam_Web_Page
{
    protected $layout;
    protected $substitutions = array();
    protected $css = array();
    protected $script = array();
    
    /**
     * Creates a new Steam_Web_Page object from the specified layout.
     *
     * @return object
     * @param string $layout page layout
     */
    public function __construct($layout = NULL)
    {
        $this->layout = $layout;
    }
    
    /**
     * Inserts a component to the page at the specified destination.
     *
     * @return void
     * @param string $destination destination layout variable
     * @param object $component Steam_Web_Page_Component
     */
    public function insert($destination, Steam_Web_Page_Component &$component)
    {
        $component->page($this);
        
        $this->substitutions[] = array('insert', $destination, $component);
    }
    
    /**
     * Sets the value of a layout variable to the specified value.
     *
     * @return void
     * @param string $destination destination layout variable
     * @param string $string string
     */
    public function set($destination, $string)
    {
        $this->substitutions[] = array('set', $destination, $string);
    }
    
    public function css($path, $media = 'all')
    {
        $this->css[$path] = $media;
    }
    
    public function script($path, $type = 'text/javascript')
    {
        $this->script[$path] = $type;
    }
    
    /**
     * Constructs the page and outputs it to the user's browser. If the layout
     * cannot be found, Steam_Exception_General is thrown.
     *
     * @throws Steam_Exception_General
     * @return void
     */
    public function display()
    {
        try
        {
            $page = file_get_contents(Steam::$base_dir . 'apps/' . Steam::$app_name . '/layouts/' . $this->layout);
        }
        catch (Steam_Exception_FileNotFound $exception)
        {
            throw new Steam_Exception_General(sprintf(gettext('The page layout %s could not be found.'), $this->layout));
        }
        
        $uris = array(
            'GLOBAL_BASE'      => Steam::$base_uri,
            'GLOBAL_RESOURCES' => Steam::$base_uri . '/resources/global',
            'BASE'             => Steam::$base_uri . '/' . Steam::$app_name,
            'RESOURCES'        => Steam::$base_uri . '/resources/' . Steam::$app_name,
            );
        
        foreach ($uris as $name => $value)
        {
            $page = str_replace('<<<URI:' . $name . '>>>', $value, $page);
        }
        unset($name);
        unset($value);
        
        foreach ($this->substitutions as &$substitution)
        {
            switch ($substitution[0])
            {
                case 'insert':
                    $page = str_replace('<<<' . $substitution[1] . '>>>', $substitution[2] . '<<<' . $substitution[1] . '>>>', $page);
                    break;
                case 'set':
                    $page = str_replace('<<<' . $substitution[1] . '>>>', $substitution[2], $page);
                    break;
            }
            $substitution = NULL;
        }
        unset($substitution);
        
        foreach ($this->css as $css => $media)
        {
            $page = str_replace('</head>', '<link rel="stylesheet" type="text/css" href="' . $uris['RESOURCES'] . '/css/' . $css . '" media="' . $media . '"/>' . "\n" . '</head>', $page);
        }
        $this->css = array();
        unset($css);
        unset($media);
        
        foreach ($this->script as $script => $type)
        {
            $page = str_replace('</head>', '<script type="' . $type . '" src="' . $uris['RESOURCES'] . '/script/' . $script . '"></script>' . "\n" . '</head>', $page);
        }
        $this->script = array();
        unset($script);
        unset($type);
        unset($uris);
        
        $page = preg_replace('/<<<.*>>>/i', '', $page);
        
        if ($content_type = Steam_Web_MIME::get_type($this->layout))
        {
            Steam_Web::header('Content-Type', $content_type);
        }
        unset($content_type);
        
        Steam_Web::body($page);
        
        unset($page);
    }
}

?>
