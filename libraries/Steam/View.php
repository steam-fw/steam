<?php
/**
 * Steam View Class
 *
 * This class inserts widgets into templates and outputs the result to
 * the client as specified in a view.
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
    private static $cache = '';
    private static $includes = array();
    private static $css = array();
    private static $js  = array();
    
    public static function insert($block, $html)
    {
        self::$includes[$block][] = $html;
    }
    
    public static function css($name, $media = NULL)
    {
        if (is_null($media))
        {
            $media = 'all';
        }
        
        if (!isset(self::$css[$media]))
        {
            self::$css[$media] = array();
        }
        
        if (!in_array($name, self::$css[$media]))
        {
            self::$css[$media][] = $name;
        }
    }
    
    private static function insert_css()
    {
        if (!\Steam::config('fingerprinting'))
        {
            foreach (self::$css as $media => $css_files)
            {
                sort($css_files);
                
                foreach ($css_files as $css_file)
                {
                    $first = substr($css_file, 0, 7);
                    
                    if ($css_file[0] == '/' or $first == 'http://' or $first == 'https:/')
                    {
                        self::insert('head', '<link rel="stylesheet" href="' . $css_file . '" media="' . $media . '"/>' . "\n");
                    }
                    else
                    {
                        self::insert('head', '<link rel="stylesheet" href="' . \Steam\StaticResource::uri('/css/' . $css_file . '.css') . '" media="' . $media . '"/>' . "\n");
                    }
                }
            }
            
            return;
        }
        
        foreach (self::$css as $media => $css_files)
        {
            sort($css_files);
            
            $filename = implode('~', $css_files) . '.css';
            
            try
            {
                $fileinfo = \Steam\Cache::get('_static:cache-file-info', $filename);
                $fileinfo = unserialize($fileinfo);
                
                foreach ($css_files as $css_file)
                {
                    $filepath = \Steam::app_path('/static/css/' . $css_file . '.css');
                    
                    if (filemtime($filepath) > $fileinfo['last_mod'])
                    {
                        throw new \Steam\Exception\Cache();
                    }
                }
                
                $filepath = $fileinfo['file_path'];
            }
            catch (\Steam\Exception\Cache $exception)
            {
                $combined_css = '';
                
                foreach ($css_files as $css_file)
                {
                    $filepath = \Steam::app_path('/static/css/' . $css_file . '.css');
                    
                    $css = file_get_contents($filepath);
                    
                    $combined_css .= \Minify_CSS_Compressor::process($css) . "\n";
                    
                    $css = '';
                }
                
                $filepath = \Steam\StaticResource::uri('/css/~' . md5($combined_css) . '~' . $filename);
                
                $cache_file = array(
                    'file_name'      => $filename,
                    'file_path'      => $filepath,
                    'last_mod'       => time(),
                    'content_type'   => 'text/css',
                    'content_length' => strlen($combined_css),
                );
                
                \Steam\Cache::set('_static:real-path',       $filepath, 'cache-file:' . $filename);
                \Steam\Cache::set('_static:cache-file-info', $filename, serialize($cache_file));
                \Steam\Cache::set('_static:cache-file',      $filename, $combined_css);
            }
            
            self::insert('head', '<link rel="stylesheet" href="' . $filepath . '" media="' . $media . '"/>' . "\n");
        }
    }
    
    public static function js($name)
    {
        if (!in_array($name, self::$js))
        {
            self::$js[] = $name;
        }
    }
    
    private static function insert_js()
    {
        $js_files = self::$js;
        
        foreach ($js_files as $js_file)
        {
            $first = substr($js_file, 0, 7);
            
            if ($js_file[0] == '/' or $first == 'http://' or $first == 'https:/')
            {
                self::insert('head', '<script type="text/javascript" src="' . $js_file . '"> </script>' . "\n");
            }
            else
            {
                self::insert('head', '<script type="text/javascript" src="' . \Steam\StaticResource::uri('/script/' . $js_file . '.js') . '"> </script>' . "\n");
            }
        }
        
    }
    
    public static function cache($instance_id = '')
    {
        self::$cache = '~' . $instance_id;
    }
    
    public static function display($view, $_request, $_response)
    {
        try
        {
            $template     = '';
            $text         = array();
            $layout       = array();
            $content_type = 'text/html';
            
            include \Steam::app_path('views/' . trim($view, '/') . '.php');
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
                    self::$cache = '';
                    ob_clean();
                    @include \Steam::app_path('widgets/' . $_widget . '.php');
                    
                    if (self::$cache)
                    {
                        $output = ob_get_contents();
                        \Steam\Cache::set('_widget:content',    $_widget . self::$cache, $output);
                        \Steam\Cache::set('_widget:cache-date', $_widget . self::$cache, time());
                        $$_block .= $output;
                        unset($output);
                    }
                    else
                    {
                        $$_block .= ob_get_contents();
                    }
                }
            }
            while (prev($_layout));
            
            unset($_widgets);
            
            ob_clean();
        }
        
        unset($_layout);
        
        self::insert_js();
        self::insert_css();
        
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
        
        try
        {
            include \Steam::app_path('templates/' . $_template . '.php');
        }
        catch (\Steam\Exception\FileNotFound $exception)
        {
            \Steam\Error::display(500, 'Template Not Found');
        }
    }
}

?>
