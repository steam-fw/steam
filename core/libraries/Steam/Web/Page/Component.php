<?php
/**
 * Steam Page Component Class
 *
 * This class is used to create page components to add to pages.
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

class Steam_Web_Page_Component
{
    protected $name;
    protected $options = array();
    protected $data;
    
    /**
     * Creates a new Steam_Web_Page object from the specified layout.
     *
     * @return object
     * @param string $layout page layout
     */
    public function __construct($name)
    {
        $this->name = $name;
    }
    
    /**
     * This method gets or sets an option for the component. If only the option
     * name is passed, the method will return the current value of the option.
     * If a value is passed as well, the method will set the option to the
     * specified value. If NULL is passed as the value, the option is unset.
     *
     * @return mixed|void
     * @param string $name option name
     * @param mixed $value option value
     */
    public function option($name)
    {
        $args = func_get_args();
        $name = array_shift($args);
        
        if (array_key_exists($args[0]))
        {
            $value = array_shift($args);
            if (is_null($value))
            {
                unset($this->options[$name]);
            }
            else
            {
                $this->options[$name] = $value;
            }
        }
        else
        {
            return $this->options[$name];
        }
    }
    
    /**
     * This method sets the components data property. The format of the data is
     * dependent upon the component being used.
     *
     * @return void
     * @param mixed $data data
     */
    public function data($data)
    {
        $this->data = $data;
    }
    
    /**
     * This method creates a string representation of the component by
     * including the component file which constructs the output, whether it be
     * html or another type of output.
     *
     * @return string
     */
    public function __toString()
    {
        $options = $this->options;
        $data    = $this->data;
        
        try
        {
            $output = include Steam::$base_dir . 'apps/' . Steam::$site_name . '/components/' . $this->name . '.php';
            return $output;
        }
        catch (Exception $exception)
        {
            return '';
        }
    }
}

?>
