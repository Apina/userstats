<?php
/**
 * Constants used by this plugin
 * 
 * @package PluginTemplate
 * 
 * @author kynatro
 * @version 1.0.0
 * @since 1.0.0
 */

// The current version of this plugin
if( !defined( 'USERSTATS_VERSION' ) ) define( 'USERSTATS_VERSION', '1.0.0' );

// The directory the plugin resides in
if( !defined( 'USERSTATS_DIRNAME' ) ) define( 'USERSTATS_DIRNAME', dirname( dirname( __FILE__ ) ) );

// The URL path of this plugin
if( !defined( 'USERSTATS_URLPATH' ) ) define( 'USERSTATS_URLPATH', WP_PLUGIN_URL . "/" . plugin_basename( USERSTATS_DIRNAME ) );

if( !defined( 'IS_AJAX_REQUEST' ) ) define( 'IS_AJAX_REQUEST', ( !empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' ) );
