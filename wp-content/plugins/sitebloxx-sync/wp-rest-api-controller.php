<?php
/*
Plugin Name:       Sitebloxx Sync
Plugin URI:        https://app.sitebloxx.com/
Description:       This Plugin allow you to direct import from Bulder Project
Version: 5.9.1
*/


if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'WP_REST_API_CONTROLLER_PATH' ) ) {
	define( 'WP_REST_API_CONTROLLER_PATH', plugin_dir_path( __FILE__ ) );
}



if ( ! defined( 'WP_REST_API_CONTROLLER_URL' ) ) {
	define( 'WP_REST_API_CONTROLLER_URL', plugin_dir_url( __FILE__ ) );
}



function wp_rest_api_controller_text_domain_init() {
	load_plugin_textdomain( 'wp-rest-api-controller', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'wp_rest_api_controller_text_domain_init' );



register_activation_hook( __FILE__, 'activate_wp_rest_api_controller' );
function activate_wp_rest_api_controller() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-rest-api-controller-activator.php';
	wp_rest_api_controller_Activator::activate();
}



register_deactivation_hook( __FILE__, 'deactivate_wp_rest_api_controller' );
function deactivate_wp_rest_api_controller() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-rest-api-controller-deactivator.php';
	wp_rest_api_controller_Deactivator::deactivate();
}



require plugin_dir_path( __FILE__ ) . 'includes/class-wp-rest-api-controller.php';

require plugin_dir_path( __FILE__ ) . 'includes/builderapi.php';

function run_wp_rest_api_controller() {

	$plugin = new wp_rest_api_controller();
	$plugin->run();

}
run_wp_rest_api_controller();
