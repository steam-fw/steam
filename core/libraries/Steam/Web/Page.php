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
    protected $components = array();
    protected $substitutions = array();
    
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
     * Adds the passed component to the page at the specified destination.
     *
     * @return void
     * @param object $component Steam_Web_Page_Component
     */
    public function insert($destination, Steam_Web_Page_Component $component)
    {
        $this->components[] = array($destination, $component);
    }
    
    public function set($destination, $string)
    {
        $this->substitutions[] = array($destination, $string);
    }
    
    /**
     * Constructs the page and outputs it to the user's browser.
     *
     * @return void
     */
    public function display()
    {
        $page = file_get_contents(Steam::$base_dir . 'sites/' . Steam::$site_name . '/layouts/' . $this->layout);
        
        foreach ($this->substitutions as &$substitution)
        {
            $page = str_replace('<<<' . $substitution[0] . '>>>', $substitution[1], $page);
            $substitution = NULL;
        }
        unset($substitution);
        
        foreach ($this->components as &$component)
        {
            $page = str_replace('<<<' . $component[0] . '>>>', $component[1], $page);
            $component = NULL;
        }
        unset($component);
        
        if ($content_type = Steam_Web_MIME::get_type($this->layout))
        {
            header('Content-Type: ' . $content_type);
        }
        
        header('Content-Length: ' . strlen($page));
        
        echo $page;
    }
}

?>
