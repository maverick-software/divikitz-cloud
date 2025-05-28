<?php
/*
Plugin Name: Bloxx Apps
Plugin URI: https://web1experts.com/
Description: Create Bloxx Apps
Version: 5.9.1
*/


define( 'bloxx_url', plugin_dir_url( __FILE__ ) );
define( 'bloxx_path', plugin_dir_path( __FILE__ ) );
define( 'bloxx_plugin', plugin_basename( __FILE__ ) );
define('BLOXX_PLUGIN_NAME','Bloxx Apps');

require_once 'admin/bloxx_admin.php';
require_once 'admin/templates/hosting_settings.php';
require_once 'includes/bloxxapp_core.php';
require_once 'includes/cloudways.php';
require_once 'templates/apps.php';
require_once 'templates/domain.php';
require_once 'templates/ssl.php';
register_activation_hook(
    __FILE__,
    'bloxxapp_pages'
);
function bloxxapp_pages(){
		if ( ! current_user_can( 'activate_plugins' ) ) return;

		global $wpdb;
		$current_user = wp_get_current_user();


		$table = $wpdb->prefix . 'bloxx_operations';
    $charset = $wpdb->get_charset_collate();
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table (
      `id` int(11) NOT NULL auto_increment,  
      `user_id` int(11) NOT NULL, 
      `server_id` varchar(255) NOT NULL,
      `app_id` varchar(255) NOT NULL,
      `operation_id` varchar(255) NOT NULL,
      `operation_type` varchar(255) NOT NULL,
      `status` int(11) NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY  (`id`)       
    )";
        
    $wpdb->query($sql);

    $table = $wpdb->prefix . 'bloxx_notifications';
    $charset = $wpdb->get_charset_collate();
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table (
      `id` int(11) NOT NULL auto_increment,   
      `app_name` varchar(50) NOT NULL,
      `user_id` int(11) NOT NULL,
      `msg` text NOT NULL,
      `status` varchar(255) NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY  (`id`)       
    )";
    
    $wpdb->query($sql);

		// if ( null === $wpdb->get_row( "SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name = 'bloxx-app-create'", 'ARRAY_A' ) ) { 			

		// 	$createpage = array(
		// 		'post_content'   => '[bloxx_app_create]',
		// 		'post_title'     => 'Create Application',
		// 		'post_status'    => 'publish',
		// 		'post_author' 	 => $current_user->ID,
		// 		'post_type'      => 'page'
		// 	);
		// 	wp_insert_post( $createpage );
		// }

		// if ( null === $wpdb->get_row( "SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name = 'bloxx-app-list'", 'ARRAY_A' ) ) {

		// 	$listpage = array(
		// 		'post_content'   => '[bloxx_app_list]',
		// 		'post_title'     => 'Bloxx Applications',
		// 		'post_status'    => 'publish',
		// 		'post_author' 	 => $current_user->ID,
		// 		'post_type'      => 'page'
		// 	);

		// 	wp_insert_post( $listpage );
		// }

		// if ( null === $wpdb->get_row( "SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name = 'bloxx-application-detail'", 'ARRAY_A' ) ) {

		// 	$listpage = array(
		// 		'post_content'   => '[bloxx_app_details]',
		// 		'post_title'     => 'Bloxx Application Details',
		// 		'post_status'    => 'publish',
		// 		'post_author' 	 => $current_user->ID,
		// 		'post_type'      => 'page'
		// 	);

		// 	wp_insert_post( $listpage );
		// }

	}

function insertOperation($data){
	global $wpdb;
	$table = $wpdb->prefix . 'bloxx_operations';
	$wpdb->insert($table, $data);
	return true;
}

function getoperation($app_id){

	global $wpdb;
	$table = $wpdb->prefix . 'bloxx_operations';
	$cloud = new Cloudways();
	$operations = $wpdb->get_results( "SELECT * FROM $table WHERE app_id = '$app_id' AND status = 'processing'" );

	if(!empty($operations)){
		foreach($operations as $k => $val){
			$output = json_decode($cloud->getRequest('/operation/'.$val->operation_id.'?access_token='.$cloud->getToken()));
			
			if(isset($output->operation)  && $output->operation->is_completed == '0'){
				return false;
			}else{
				$wpdb->update($table, array('status'=>'completed'), array('id'=>$val->id));
			}
		}
	}	
	return true;	
}


function getparentkeyindex($subject, $array)
	{
		foreach ($array as $key => $val) {
	       if ($val->id === $subject) {
	           return $key;
	       }
	   }
	   return null;
	}