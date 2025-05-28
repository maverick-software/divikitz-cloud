<?php
/*
Plugin Name: Free Stock Images
Description: Image API integration for pixabay.com Use shortcode [free_stock_image] to display image  
Version: 1.0.2
*/

define('pixr_url', plugin_dir_url(__FILE__));
define('pixr_path', plugin_dir_path(__FILE__));
define('pixr_plugin', plugin_basename(__FILE__));

require 'include/API_call.php';
class Free_Stock_Image{
	
	public function __construct(){
		add_action("wp_ajax_free_stock_list", array($this, "free_stock_list"));
		add_action("wp_ajax_nopriv_free_stock_list", array($this, "free_stock_list"));

		add_action("wp_ajax_free_stock_list_media_element", array($this, "free_stock_list_media_element"));
		add_action("wp_ajax_nopriv_free_stock_list_media_element", array($this, "free_stock_list_media_element"));

		add_action("wp_ajax_free_stock_add_media", array($this, "free_stock_add_media"));
		add_action("wp_ajax_nopriv_free_stock_add_media", array($this, "free_stock_add_media"));
		
		add_action('admin_menu', array($this, 'setting_menu'));
		add_action('admin_init', array($this, 'setting_save'));
		add_shortcode('free_stock_image', array($this, 'free_stock_image_list'));
		add_action('admin_enqueue_scripts', array($this, 'add_media_element'));
		add_action('wp_enqueue_scripts', array($this, 'add_media_element'));
	}

	function add_media_element(){
		$ajax_url = admin_url( 'admin-ajax.php' );
    	wp_enqueue_script( 'pixabay-media-tab', plugin_dir_url( __FILE__ ) . 'assets/js/media_element.js', array( 'jquery' ), '1.0.2', true );
    
     	$ajax_url = admin_url( 'admin-ajax.php' );
    	wp_localize_script( 'pixabay-media-tab', 'pixabay_media_tab_ajax_object', 
		  	array( 
				'ajax_url' => $ajax_url,
				
			) 
		);
	}

	function free_stock_add_media(){
		$image_url = $_REQUEST['url'];
		$upload_dir = wp_upload_dir();
		$image_data = file_get_contents($image_url);
		$filename = basename($image_url);
		if(wp_mkdir_p($upload_dir['path']))
		    $file = $upload_dir['path'] . '/' . $filename;
		else
		    $file = $upload_dir['basedir'] . '/' . $filename;
		file_put_contents($file, $image_data);

		$wp_filetype = wp_check_filetype($filename, null );
		$attachment = array(
		    'post_mime_type' => $wp_filetype['type'],
		    'post_title' => sanitize_file_name($filename),
		    'post_content' => '',
		    'post_status' => 'inherit'
		);
		$attach_id = wp_insert_attachment( $attachment, $file );
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		echo json_encode(array("post_id"=>$attach_id)); die;
	}
	
	function setting_menu(){

		add_submenu_page( 'bloxx-app', 'Pixabay', 'Pixabay', 'manage_options','free_stock_image',array($this,  'setting') );

	   // add_menu_page('Free Stock Image', 'Free Stock Image', 'manage_options', 'free_stock_image',array($this,  'setting') );

	    add_submenu_page( 'upload.php', 'Pixabay', 'Pixabay', 'manage_options','Pixabay_image',array($this,  'free_stock_image_list_page'), 1 );
	}

	 function setting(){
	 	require 'template/setting.php';
	 }


	
	function setting_save(){
		if(isset($_POST['api_key_pixabay'])){
			update_option( 'api_key_pixabay', $_POST['api_key_pixabay'] );
			wp_safe_redirect(esc_url(site_url( '/wp-admin/admin.php?page=free_stock_image' )));
			exit();
		}
	}

	

	function free_stock_image_list_page(){
		$API = new API_call();

		$images = $API->call();

		echo "<style>".file_get_contents(plugin_dir_path(__FILE__).'/assets/css/style.css')."</style>";
	 	require 'template/front/image_list.php';

	 	echo "<script> var ajax_url= '".admin_url( 'admin-ajax.php' )."';var plugin_url= '".plugin_dir_url(__FILE__)."';".file_get_contents(plugin_dir_path(__FILE__).'/assets/js/script.js')."</script>";
		
	}
	function free_stock_image_list(){
		$API = new API_call();

		$images = $API->call();
		echo "<style>".file_get_contents(plugin_dir_path(__FILE__).'/assets/css/style.css')."</style>";
		require 'template/front/image_list.php';

	 	echo "<script> var ajax_url= '".admin_url( 'admin-ajax.php' )."';var plugin_url= '".plugin_dir_url(__FILE__)."';".file_get_contents(plugin_dir_path(__FILE__).'/assets/js/script.js')."</script>";
		$output .= ob_get_contents();
		ob_end_clean();
		return $output;
	}


	
	function free_stock_list(){
		$query = $_REQUEST['q'];
		$page = $_REQUEST['p'];
		$API = new API_call();
		$API->set_query($query);
		$API->set_page($page);
		$images = $API->call();

		ob_start();

	    foreach ($images->hits as $key => $image) { 

	      require 'template/front/image_element_search.php';

	    } 

		$output = ob_get_contents();
		ob_end_clean();
		
		echo json_encode(array('list'=>$output));
		die;
	}
	function free_stock_list_media_element(){
		$query = $_REQUEST['q'];
		$page = $_REQUEST['p'];
		$API = new API_call();
		$API->set_query($query);
		$API->set_page($page);
		$images = $API->call();

		ob_start();

	    foreach ($images->hits as $key => $image) { 

	      require 'template/front/image_element.php';

	    } 

		$output = ob_get_contents();
		ob_end_clean();
		
		echo json_encode(array('list'=>$output));
		die;
	}
}
$Free_Stock_Image = new Free_Stock_Image();

?>