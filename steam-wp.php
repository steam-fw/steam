<?php
/*
Plugin Name: SteamWP
Plugin URI: http://code.google.com/p/steam-fw/
Description: Steam is a php web application framework which, when combined with WordPress, provides alternative facilities for URI mapping, data manipulation, and templating while providing other useful functionality.
Version: 0.2.0
Author: Shaddy Zeineddine, Braydon Fuller
*/

include 'functions.php';
include 'libraries/Steam.php';

add_action('init', function()
{
    ob_start();
    Steam::load_config();
    Steam::initialize();
    
    add_filter('query_vars', function($wp_vars)
    {
        $wp_vars[] = 'steam_resource_type';
        $wp_vars[] = 'steam_resource_name';
        
        return $wp_vars;
    });
    
    add_action('template_redirect', function()
    {
        global $wp_rewrite;
        $type     = get_query_var('steam_resource_type');
        $resource = get_query_var('steam_resource_name');
        
        if ($type and $resource)
        {
            Steam::set_request('wordpress', $type, $resource);
            Steam::dispatch();
            exit;
        }
    });
});

register_activation_hook(__FILE__, function()
{
    global $wp_rewrite;
    add_filter('rewrite_rules_array', function($wp_rules)
    {
        $steam_rules = array(
            'static/(.+)$'  => 'index.php?steam_resource_type=static&steam_resource_name=$matches[1]',
            'actions/(.+)$' => 'index.php?steam_resource_type=action&steam_resource_name=$matches[1]',
            'models/(.+)$'  => 'index.php?steam_resource_type=model&steam_resource_name=$matches[1]',
            'views/(.+)$'   => 'index.php?steam_resource_type=view&steam_resource_name=$matches[1]',
        );
        
        return $steam_rules + $wp_rules;
    });
    $wp_rewrite->flush_rules();
});

register_deactivation_hook(__FILE__, function()
{
    global $wp_rewrite;
    add_filter('rewrite_rules_array', function($wp_rules)
    {
        unset($wp_rules['static/(.+)$']);
        unset($wp_rules['actions/(.+)$']);
        unset($wp_rules['models/(.+)$']);
        unset($wp_rules['views/(.+)$']);
        
        return $wp_rules;
    });
    $wp_rewrite->flush_rules();
});

?>
