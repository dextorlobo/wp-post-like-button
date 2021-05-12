<?php
/*
Plugin Name: APLD Like Post
Plugin URI: 
Description: APLD Like Post is a plugin for adding like and unlike functionality for posts. Use the [apld_like_post_buttons] shortcode to display like and unlike buttons.
Version: 1.0.0
Author: arunsharma
Author URI: http://imarun.me
*/


global $apld_like_post_db_version, $apld_ip_address;
$apld_like_post_db_version = "1.4.4";
$apld_ip_address = ApldGetRealIpAddress();

register_activation_hook(__FILE__, 'SetOptionsApldLikePost');

/**
 * Basic options function for the plugin settings
 * @param no-param
 * @return void
 */
function SetOptionsApldLikePost() {
	global $wpdb, $apld_like_post_db_version;

	// Creating the like post table on activating the plugin
	$apld_like_post_table_name = $wpdb->prefix . "apld_like_post";
	
	if ($wpdb->get_var("show tables like '$apld_like_post_table_name'") != $apld_like_post_table_name) {
		$sql = "CREATE TABLE " . $apld_like_post_table_name . " (
			`id` bigint(11) NOT NULL AUTO_INCREMENT,
			`post_id` int(11) NOT NULL,
			`value` int(2) NOT NULL,
			`date_time` datetime NOT NULL,
			`ip` varchar(40) NOT NULL,
			`user_id` int(11) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
		)";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	add_option('apld_like_post_db_version', $apld_like_post_db_version, '', 'yes');
}

// Include the file for functions
require_once('apld_like_post_functions.php');
// Include the file for ajax calls
require_once('apld_like_post_ajax.php');
// Include the file for widget
require_once('apld_widget_like_posts.php');

add_action('init', 'ApldLikePostEnqueueScripts');
/**
 * Add the javascript and css for the plugin
 * @param no-param
 */
/**
 * Add the javascript and css for the plugin
 * @param no-param
 */
function ApldLikePostEnqueueScripts() {
	// Load javascript file
	wp_register_script( 'apld_like_post_script', plugins_url( 'js/apld_like_post.js', __FILE__ ), array('jquery'), 1.3 );
	wp_localize_script( 'apld_like_post_script', 'apldlp', array( 'ajax_url' => admin_url( 'admin-ajax.php' )));

	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'apld_like_post_script' );
	
	// Load css file
	wp_register_style( 'apld_like_post_style', plugins_url( 'css/apld_like_post.css', __FILE__ ) );
	wp_enqueue_style( 'apld_like_post_style' );
}

add_action( 'wp_enqueue_scripts', 'load_dashicons_front_end' );
function load_dashicons_front_end() {
  wp_enqueue_style( 'dashicons' );
}

/**
 * Get the actual ip address
 * @param no-param
 * @return string
 */
function ApldGetRealIpAddress() {
	if (getenv('HTTP_CLIENT_IP')) {
		$ip = getenv('HTTP_CLIENT_IP');
	} elseif (getenv('HTTP_X_FORWARDED_FOR')) {
		$ip = getenv('HTTP_X_FORWARDED_FOR');
	} elseif (getenv('HTTP_X_FORWARDED')) {
		$ip = getenv('HTTP_X_FORWARDED');
	} elseif (getenv('HTTP_FORWARDED_FOR')) {
		$ip = getenv('HTTP_FORWARDED_FOR');
	} elseif (getenv('HTTP_FORWARDED')) {
		$ip = getenv('HTTP_FORWARDED');
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	
	return $ip;
}