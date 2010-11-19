<?php
/*
Plugin Name: SteamWP
Plugin URI: http://code.google.com/p/steam-fw/
Description: Steam is a php web application framework which, when combined with WordPress, provides alternative facilities for URI mapping, data manipulation, and templating while providing other useful functionality.
Version: 0.1.0
Author: Shaddy Zeineddine, Braydon Fuller
*/

function steam_initialize()
{
    include 'functions.php';

    include 'libraries/Steam.php';
    
    ob_start();

    Steam::load_config();

    Steam::initialize();

    add_filter('rewrite_rules_array', 'steam_rewrites');
    
    add_filter('query_vars', 'steam_query_vars');
    
    add_action('template_redirect', 'steam_template_redirect');
}

function steam_query_vars($wp_vars)
{
    $wp_vars[] = 'steam_action';
    $wp_vars[] = 'steam_model';
    $wp_vars[] = 'steam_view';
    
    return $wp_vars;
}

function steam_template_redirect()
{
    $type     = get_query_var('steam_resource_type');
    $resource = get_query_var('steam_resource_name');
    
    if ($type and $resource)
    {
        Steam::set_request('wordpress', $type, $resource);
        Steam::dispatch();
        exit;
    }
    
}

function steam_rewrites($wp_rules)
{
    $steam_rules = array(
        'actions/(.+)$' => 'index.php?steam_resource_type=action&steam_resource_name=$matches[1]',
        'models/(.+)$'  => 'index.php?steam_resource_type=model&steam_resource_name=$matches[1]',
        'views/(.+)$'   => 'index.php?steam_resource_type=view&steam_resource_name=$matches[1]',
    );
    
    return array_merge($steam_rules, $wp_rules);
}

function steam_activation()
{
    global $wp_rewrite;
    add_filter('rewrite_rules_array', 'steam_rewrites');
    $wp_rewrite->flush_rules();
}

function steam_deactivation()
{
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
}

add_action('init', 'steam_initialize');

register_activation_hook(Steam::path('steam-wp.php'), 'steam_activation');

register_deactivation_hook(Steam::path('steam-wp.php'), 'steam_deactivation');

?>
