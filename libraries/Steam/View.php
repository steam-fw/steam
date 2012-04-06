<?php
/**
 * Steam View Class
 *
 * This class inserts widgets into templates and outputs the result to
 * the client as specified in a view.
 *
 * Copyright 2008-2011 Shaddy Zeineddine
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
 * @copyright 2008-2012 Shaddy Zeineddine
 * @license http://www.gnu.org/licenses/gpl.txt GPL v3 or later
 * @link http://code.google.com/p/steam-fw
 */


namespace Steam;

class View
{
    private static $widget_id    = 0;
    private static $cache        = false;
    private static $includes     = array();
    private static $internal_css = array();
    private static $external_css = array();
    private static $internal_js  = array();
    private static $external_js  = array();
    private static $text         = array();
    private static $filters      = array();
    
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
        
        $first = substr($name, 0, 7);
        
        if ($name[0] == '/' or $first == 'http://' or $first == 'https:/')
        {
            $css =& self::$external_css;
        }
        else
        {
            $css =& self::$internal_css;
        }
        
        if (!isset($css[$media]))
        {
            $css[$media] = array();
        }
        
        if (!in_array($name, $css[$media]))
        {
            $css[$media][] = $name;
        }
    }
    
    private static function insert_css()
    {
        foreach (self::$external_css as $media => $css_files)
        {
            foreach ($css_files as $css_file)
            {
                self::insert('head', '<link rel="stylesheet" href="' . $css_file . '" media="' . $media . '"/>' . "\n");
            }
        }
        
        if (!\Steam::config('fingerprinting'))
        {
            foreach (self::$internal_css as $media => $css_files)
            {
                foreach ($css_files as $css_file)
                {
                    self::insert('head', '<link rel="stylesheet" href="' . \Steam\StaticResource::uri('/css/' . $css_file . '.css') . '" media="' . $media . '"/>' . "\n");
                }
            }
            
            return;
        }
        
        foreach (self::$internal_css as $media => $css_files)
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
        $first = substr($name, 0, 7);
        
        if ($name[0] == '/' or $first == 'http://' or $first == 'https:/')
        {
            $js =& self::$external_js;
        }
        else
        {
            $js =& self::$internal_js;
        }
        
        if (!in_array($name, $js))
        {
            $js[] = $name;
        }
    }
    
    private static function insert_js()
    {
        foreach (self::$external_js as $js_file)
        {
            self::insert('head', '<script type="text/javascript" src="' . $js_file . '"> </script>' . "\n");
        }
        
        foreach (self::$internal_js as $js_file)
        {
            self::insert('head', '<script type="text/javascript" src="' . \Steam\StaticResource::uri('/script/' . $js_file . '.js') . '"> </script>' . "\n");
        }
        
    }
    
    public static function cache($instance_id = NULL)
    {
        if (is_null($instance_id)) self::$cache = true;
        else self::$cache = '~' . $instance_id;
    }
    
    /**
     * Returns a unique identifier for the current widget.
     *
     * @return int
     */
    public static function widget_id()
    {
        return self::$widget_id;
    }
    
    public static function add_filter($type, $function)
    {
        self::$filters[$type][] = $function;
    }
    
    public static function text_filter($block, $text)
    {
        if (isset(self::$filters['text']))
            foreach (self::$filters['text'] as $filter) $text = $filter($block, $text);
        return $text;
    }
    
    public static function widget_filter($block, $widget, $content)
    {
        if (isset(self::$filters['widget']))
            foreach (self::$filters['widget'] as $filter) $content = $filter($block, $widget, $content);
        return $content;
    }
    
    /**
     *
     *
     *
     */
    public static function text($key, $value)
    {
        self::$text[$key] = $value;
    }
    
    public static function display($view, $_request, $_response)
    {
        try
        {
            $template     = '';
            $text         = array();
            $layout       = array();
            $content_type = 'text/html';
            $charset      = 'utf-8';
            
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
        
        if ($charset) $_content_type .= '; charset=' . $charset;
        
        $output = ob_get_contents();
        ob_clean();
        
        if (!empty($output))
        {
            \Steam\Logger::log('Output buffer not empty, outputting contents directly');
            \Steam\Event::trigger('steam-response');
            if (ini_get('expose_php'))
                $_response->setHeader('X-Powered-By', 'PHP/' .  phpversion() . ' Steam/' . \Steam::version(), true);
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
                    self::$widget_id++;
                    self::$cache = false;
                    ob_clean();
                    @include \Steam::app_path('widgets/' . $_widget . '.php');
                    
                    if (self::$cache)
                    {
                        $cache_id = $_widget . (is_string(self::$cache) ? self::$cache : '');
                        $output   = ob_get_contents();
                        \Steam\Cache::set('_widget:content',    $cache_id, $output);
                        \Steam\Cache::set('_widget:cache-date', $cache_id, time());
                    }
                    else
                    {
                        $output = ob_get_contents();
                    }
                    
                    
                    $$_block .= self::widget_filter($_block, $_widget, $output);
                    $output   = NULL;
                }
            }
            while (prev($_layout));
            
            unset($_widgets);
            
            ob_clean();
        }
        
        unset($_layout);
        
        self::insert_css();
        self::insert_js();
        
        foreach (self::$includes as $_block => $_strings)
        {
            $_blocks[$_block] = 0;
            $_block = '_' . $_block;
            
            foreach ($_strings as $_string)
            {
                if (!isset($$_block)) $$_block = '';
                
                $$_block .= $_string;
            }
        }
        unset($_block);
        unset($_string);
        unset($_strings);
        self::$includes = array();
        $_text = array_merge($_text, self::$text);
        self::$text = array();
        
        foreach ($_text as $_block => $_string)
        {
            $_blocks[$_block] = 0;
            $_block = '_' . $_block;
            
            if (!isset($$_block)) $$_block = '';
            
            $$_block .= self::text_filter($_block, $_string);
        }
        
        unset($_text);
        unset($_block);
        unset($_string);
        
        \Steam\Event::trigger('steam-response');
        $_response->setHeader('Content-Type', $_content_type, true);
        if (ini_get('expose_php'))
            $_response->setHeader('X-Powered-By', 'PHP/' .  phpversion() . ' Steam/' . \Steam::version(), true);
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
