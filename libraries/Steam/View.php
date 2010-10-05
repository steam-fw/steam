<?php
/**
 * Steam View Class
 *
 * This class generates output from view modules.
 *
 * Copyright 2008-2010 Shaddy Zeineddine
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
 * @copyright 2008-2010 Shaddy Zeineddine
 * @license http://www.gnu.org/licenses/gpl.txt GPL v3 or later
 * @link http://code.google.com/p/steam-fw
 */

namespace Steam;

class View
{
    private static $includes = array();
    
    public static function insert($block, $html)
    {
        self::$includes[$block][] = $html;
    }
    
    public static function display($view, $_request, $_response)
    {
        try
        {
            $template     = '';
            $text         = array();
            $layout       = array();
            $content_type = 'text/html';
            
            include \Steam::app_path('views/' . $view . '.php');
        }
        catch (\Steam\Exception\FileNotFound $exception)
        {
            \Steam\Error::display(404, $exception->getMessage());
        }
        
        $_blocks       = array();
        $_template     = $template;
        $_text         = $text;
        $_layout       = $layout;
        $_content_type = $content_type;
        
        $output = ob_get_contents();
        
        if ($output)
        {
            ob_clean();
            
            \Steam\Event::trigger('steam-response');
            $_response->setHeader('Content-Type', $_content_type, true);
            $_response->sendHeaders();
            
            unset($_content_type);
            
            \Steam\Model::shutdown();
            \Steam\Db::shutdown();
            \Steam\Action::shutdown();
            
            print $output;
            
            return;
        }
        
        unset($template);
        unset($text);
        unset($layout);
        unset($content_type);
        unset($output);
        unset($view);
        
        if (key($_layout))
        {
            end($_layout);
            
            do
            {
                $_block   = key($_layout);
                $_blocks[$_block] = 0;
                
                $_block = '_' . $_block;
                
                $_widgets = current($_layout);
                
                if ($_block[1] == '_')
                {
                    throw new \Steam\Exception\General('Invalid block name.');
                }
                
                $$_block = '';
                
                foreach ($_widgets as $_widget)
                {
                    ob_clean();
                    @include \Steam::app_path('widgets/' . $_widget . '.php');
                    $$_block .= ob_get_contents();
                }
            }
            while (prev($_layout));
            
            unset($_widgets);
            
            ob_clean();
        }
        
        unset($_layout);
        
        foreach (self::$includes as $_block => $_strings)
        {
            $_blocks[$_block] = 0;
            $_block = '_' . $_block;
            
            foreach ($_strings as $_string)
            {
                if (!isset($$_block))
                {
                    $$_block = '';
                }
                
                $$_block .= $_string;
            }
        }
        unset($_block);
        unset($_string);
        unset($_strings);
        self::$includes = array();
        
        foreach ($_text as $_block => $_string)
        {
            $_blocks[$_block] = 0;
            $_block = '_' . $_block;
            
            if (!isset($$_block))
            {
                $$_block = '';
            }
            
            $$_block .= $_string;
        }
        
        unset($_text);
        unset($_block);
        unset($_string);
        
        \Steam\Event::trigger('steam-response');
        $_response->setHeader('Content-Type', $_content_type, true);
        $_response->sendHeaders();
        
        unset($_content_type);
        
        \Steam\Model::shutdown();
        \Steam\Db::shutdown();
        \Steam\Action::shutdown();
        
        foreach ($_blocks as $_block => $nothing)
        {
            $$_block = &${'_' . $_block};
        }
        
        @include \Steam::app_path('templates/' . $_template . '.php');
    }
}

?>
