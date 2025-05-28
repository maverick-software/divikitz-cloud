<?php
/*
Plugin Name: Writesonic Business
Description: Use shortcode [Writesonic_Business_fliter] to display form on frontend.  
Version: 1.0.1
*/
define('writesonic_business_url', plugin_dir_url(__FILE__));
define('writesonic_business_path', plugin_dir_path(__FILE__));
define('writesonic_business_plugin', plugin_basename(__FILE__));

require 'include/API_call.php';
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(0);

class Writesonic_Business{
	//public $writesonic_integration_desc = json_decode(get_option( 'writesonic_integration_desc' ),true);
	public $integration;
	public function __construct(){
		$writesonic_integration_desc = json_decode(get_option( 'writesonic_integration_desc' ),true);
		$writesonic_integration_switch = json_decode(get_option( 'writesonic_integration_switch' ),true);
		$this->integration = array(
				"advertising" => array(
						array(
							"name"=>"Google ads",
							"slug" => "google-ads",
							"desc"=> $writesonic_integration_desc['google-ads'],
							"status"=> $writesonic_integration_switch['google-ads'],
							"thumb"=> writesonic_business_url."/assets/images/google-ads.png"
						),

						array(
							"name"=>"Facebook ads",
							"slug" => "facebook-ads",
							"desc"=> $writesonic_integration_desc['facebook-ads'],
							"status"=> $writesonic_integration_switch['facebook-ads'],
							"thumb"=> writesonic_business_url."/assets/images/facebook-ads.png"
						),


						array(
							"name"=>"Linkedin Ad Headlines",
							"slug"=>"linkedin-ad-headlines",
							"desc"=> $writesonic_integration_desc['linkedin-ad-headlines'],
							"status"=> $writesonic_integration_switch['linkedin-ad-headline'],
							"thumb"=> writesonic_business_url."/assets/images/linkedin-ads.png"
						),


						array(
							"name"=>"Linkedin Ad Descriptions",
							"slug"=>"linkedin-ad-descriptions",
							"desc"=> $writesonic_integration_desc['linkedin-ad-descriptions'],
							"status"=> $writesonic_integration_switch['linkedin-ad-descriptions'],
							"thumb"=> writesonic_business_url."/assets/images/linkedin-ads.png"
						),


						array(
							"name"=>"Linkedin Ads",
							"slug"=>"linkedin-ads",
							"desc"=> $writesonic_integration_desc['linkedin-ads'],
							"status"=> $writesonic_integration_switch['linkedin-ads'],
							"thumb"=> writesonic_business_url."/assets/images/linkedin-ads.png"
						),


						array(
							"name"=>"Amazon Ad Headlines",
							"slug"=>"amazon-ad-headlines",
							"desc"=> $writesonic_integration_desc['amazon-ad-headlines'],
							"status"=> $writesonic_integration_switch['amazon-ad-headlines'],
							"thumb"=> writesonic_business_url."/assets/images/amazon_icon.png"
						)
				),
				"webpage-content" => array(
					array(
						"name"=>"Linkedin Posts",
						"slug"=> "linkedin-posts",
						"desc"=> $writesonic_integration_desc['linkedin-posts'],
						"status"=> $writesonic_integration_switch['linkedin-posts'],
						"thumb"=> writesonic_business_url."/assets/images/linkedin-ads.png"
					),
					array(
						"name"=>"Landing Page Headlines",
						"slug"=> "landing-page-headlines",
						"desc"=> $writesonic_integration_desc['landing-page-headlines'],
						"status"=> $writesonic_integration_switch['landing-page-headlines'],
						"thumb"=> writesonic_business_url."/assets/images/landing-page-headlines.png"
					),
					array(
						"name"=>"Landing Pages",
						"slug"=> "landing-pages",
						"desc"=> $writesonic_integration_desc['landing-pages'],
						"status"=> $writesonic_integration_switch['landing-pages'],
						"thumb"=> writesonic_business_url."/assets/images/landing-pages.png"
					),

					array(
						"name"=>"Product Descriptions",
						"slug"=>"product-descriptions",
						"desc"=> $writesonic_integration_desc['product-descriptions'],
						"status"=> $writesonic_integration_switch['product-descriptions'],
						"thumb"=> writesonic_business_url."/assets/images/product-descriptions.png"
					),

					array(
						"name"=>"Amazon Product Descriptions",
						"slug"=>"amazon-product-descriptions",
						"desc"=> $writesonic_integration_desc['amazon-product-descriptions'],
						"status"=> $writesonic_integration_switch['amazon-product-descriptions'],
						"thumb"=> writesonic_business_url."/assets/images/amazon_icon.png"
					),array(
						"name"=>"Amazon Product Titles",
						"slug"=>"amazon-product-titles",
						"desc"=> $writesonic_integration_desc['amazon-product-titles'],
						"status"=> $writesonic_integration_switch['amazon-product-title'],
						"thumb"=> writesonic_business_url."/assets/images/amazon_icon.png"
					),array(
						"name"=>"Amazon Product Features",
						"slug"=>"amazon-product-features",
						"desc"=> $writesonic_integration_desc['amazon-product-features'],
						"status"=> $writesonic_integration_switch['amazon-product-features'],
						"thumb"=> writesonic_business_url."/assets/images/amazon_icon.png"
					),
					array(
						"name"=>"Feature To Benefit",
						"slug"=>"feature-to-benefits",
						"desc"=> $writesonic_integration_desc['feature-to-benefits'],
						"status"=> $writesonic_integration_switch['feature-to-benefits'],
						"thumb"=> writesonic_business_url."/assets/images/feature-benefits.png"
					),

					array(
						"name"=>"Content Rephrase",
						"slug"=>"content-rephrase",
						"desc"=> $writesonic_integration_desc['content-rephrase'],
						"status"=> $writesonic_integration_switch['content-rephrase'],
						"thumb"=> writesonic_business_url."/assets/images/content-rephrase.png"
					),

					array( 
						"name"=>"Sentence Expander",
						"slug"=>"sentence-expand",
						"desc"=> $writesonic_integration_desc['sentence-expand'],
						"status"=> $writesonic_integration_switch['sentence-expand'],
						"thumb"=> writesonic_business_url."/assets/images/sentence-expand.png"
					),
					array(
						"name"=>"Content Shorten",
						"slug"=>"content-shorten",
						"desc"=> $writesonic_integration_desc['content-shorten'],
						"status"=> $writesonic_integration_switch['content-shorten'],
						"thumb"=> writesonic_business_url."/assets/images/sentence-expand-modal.png"
					)
				),
				"blogs" => array(
						array(
							"name"=>"Listicle Ideas",
							"slug"=>"listicle-ideas",
							"desc"=> $writesonic_integration_desc['listicle-ideas'],
							"status"=> $writesonic_integration_switch['listicle-ideas'],
							"thumb"=> writesonic_business_url."/assets/images/listicle-ideas.png"
						),
						array(
							"name"=>"Blog Ideas",
							"slug"=>"blog-ideas",
							"desc"=> $writesonic_integration_desc['blog-ideas'],
							"status"=> $writesonic_integration_switch['blog-ideas'],
							"thumb"=> writesonic_business_url."/assets/images/blog-ideas.png"
						),

						array(
							"name"=>"Blog Intros",
							"slug"=> "blog-intros",
							"desc"=> $writesonic_integration_desc['blog-intros'],
							"status"=> $writesonic_integration_switch['blog-intros'],
							"thumb"=> writesonic_business_url."/assets/images/blog-intros.png"
						),

						array(
							"name"=>"Blog Outlines",
							"slug"=> "blog-outlines",
							"desc"=> $writesonic_integration_desc['blog-outlines'],
							"status"=> $writesonic_integration_switch['blog-outlines'],
							"thumb"=> writesonic_business_url."/assets/images/blog-outlines.png"
						),

					),
				"emails" => array(
					array(
						"name"=>"Emails",
						"slug"=>"emails",
						"desc"=> $writesonic_integration_desc['emails'],
						"status"=> $writesonic_integration_switch['emails'],
						"thumb"=> writesonic_business_url."/assets/images/emails.png"
					),
				),
				"others" => array(
						array(
							"name"=>"Growth Ideas",
							"slug"=> "growth-ideas",
							"desc"=> $writesonic_integration_desc['growth-ideas'],
							"status"=> $writesonic_integration_switch['growth-ideas'],
							"thumb"=> writesonic_business_url."/assets/images/growth-ideas.png"
						),array(
							"name"=>"Startup Ideas",
							"slug"=> "startup-ideas",
							"desc"=> $writesonic_integration_desc['startup-ideas'],
							"status"=> $writesonic_integration_switch['startup-ideas'],
							"thumb"=> writesonic_business_url."/assets/images/startup-ideas.png"
						),

						array(
							"name"=>"Summary",
							"slug"=> "summary",
							"desc"=> $writesonic_integration_desc['summary'],
							"status"=> $writesonic_integration_switch['summary'],
							"thumb"=> writesonic_business_url."/assets/images/summary.png"
						),

						array(
							"name"=>"Pain Agitate Solution",
							"slug"=>"pas",
							"desc"=> $writesonic_integration_desc['pas'],
							"status"=> $writesonic_integration_switch['pas'],
							"thumb"=> writesonic_business_url."/assets/images/agitile-solutions.png"
						),

						array(
							"name"=>"Keywords Extract",
							"slug"=>"keyword-extract",
							"desc"=> $writesonic_integration_desc['keyword-extract'],
							"status"=> $writesonic_integration_switch['keyword-extract'],
							"thumb"=> writesonic_business_url."/assets/images/keyword-extract.png"
						),

						array(
							"name"=>"Aida",
							"slug"=>"aida",
							"desc"=> $writesonic_integration_desc['aida'],
							"status"=> $writesonic_integration_switch['aida'],
							"thumb"=> writesonic_business_url."/assets/images/aida-framework.png"
						),

						array(
							"name"=>"Seo Meta Tags Home",
							"slug"=>"meta-home",
							"desc"=> $writesonic_integration_desc['meta-home'],
							"status"=> $writesonic_integration_switch['meta-home'],
							"thumb"=> writesonic_business_url."/assets/images/seo-tags.png"
						),

						array(
							"name"=>"Seo Meta Tags Blog",
							"slug"=>"meta-blog",
							"desc"=> $writesonic_integration_desc['meta-blog'],
							"status"=> $writesonic_integration_switch['meta-blog'],
							"thumb"=> writesonic_business_url."/assets/images/seo-tags.png"
						),

						array(
							"name"=>"Seo Meta Tags Product",
							"slug"=>"meta-prod",
							"desc"=> $writesonic_integration_desc['meta-prod'],
							"status"=> $writesonic_integration_switch['meta-prod'],
							"thumb"=> writesonic_business_url."/assets/images/seo-tags.png"
						),

						array(
							"name"=>"Youtube Titles",
							"slug"=>"youtube-titles",
							"desc"=> $writesonic_integration_desc['youtube-titles'],
							"status"=> $writesonic_integration_switch['youtube-titles'],
							"thumb"=> writesonic_business_url."/assets/images/youtube-ideas.png"
						),

						array(
							"name"=>"Youtube Ideas",
							"slug"=>"youtube-ideas",
							"desc"=> $writesonic_integration_desc['youtube-ideas'],
							"status"=> $writesonic_integration_switch['youtube-ideas'],
							"thumb"=> writesonic_business_url."/assets/images/youtube-ideas.png"
						),

						array(
							"name"=>"Youtube Outlines",
							"slug"=>"youtube-outlines",
							"desc"=> $writesonic_integration_desc['youtube-outlines'],
							"status"=> $writesonic_integration_switch['youtube-outlines'],
							"thumb"=> writesonic_business_url."/assets/images/youtube-ideas.png"
						),

						array(
							"name"=>"Youtube Descriptions",
							"slug"=>"youtube-descriptions",
							"desc"=> $writesonic_integration_desc['youtube-descriptions'],
							"status"=> $writesonic_integration_switch['youtube-descriptions'],
							"thumb"=> writesonic_business_url."/assets/images/youtube-ideas.png"
						),

						array(
							"name"=>"Youtube Intros",
							"slug"=>"youtube-intros",
							"desc"=> $writesonic_integration_desc['youtube-intros'],
							"status"=> $writesonic_integration_switch['youtube-intro'],
							"thumb"=> writesonic_business_url."/assets/images/youtube-ideas.png"
						),

						array(
							"name"=>"Product Names",
							"slug"=>"product-names",
							"desc"=> $writesonic_integration_desc['product-names'],
							"status"=> $writesonic_integration_switch['product-names'],
							"thumb"=> writesonic_business_url."/assets/images/product-names.png"
						),

						array(
							"name"=>"Analogy Maker",
							"slug"=>"analogies",
							"desc"=> $writesonic_integration_desc['analogies'],
							"status"=> $writesonic_integration_switch['analogies'],
							"thumb"=> writesonic_business_url."/assets/images/analogy-maker.png"
						),

						array(
							"name"=>"Short Press Releases",
							"slug"=>"short-press-releases",
							"desc"=> $writesonic_integration_desc['short-press-releases'],
							"status"=> $writesonic_integration_switch['short-press-releases'],
							"thumb"=> writesonic_business_url."/assets/images/press-release.png"
						),

						array(
							"name"=>"Company Bios",
							"slug"=>"company-bios",
							"desc"=> $writesonic_integration_desc['company-bios'],
							"status"=> $writesonic_integration_switch['company-bios'],
							"thumb"=> writesonic_business_url."/assets/images/company-bios.png"
						),

						array(
							"name"=>"Personal Bios",
							"slug"=>"personal-bios",
							"desc"=> $writesonic_integration_desc['personal-bios'],
							"status"=> $writesonic_integration_switch['personal-bios'],
							"thumb"=> writesonic_business_url."/assets/images/personal-bios.png"
						),

						array(
							"name"=>"Ai Article Writer",
							"slug"=>"ai-article-writer",
							"desc"=> $writesonic_integration_desc['ai-article-writer'],
							"status"=> $writesonic_integration_switch['ai-article-writ'],
							"thumb"=> writesonic_business_url."/assets/images/ai-1.png"
						),

						array(
							"name"=>"Ai Article Writer V2",
							"slug"=>"ai-article-writer-v2",
							"desc"=> $writesonic_integration_desc['ai-article-writer-v2'],
							"status"=> $writesonic_integration_switch['ai-article-writer-v'],
							"thumb"=> writesonic_business_url."/assets/images/ai-2.png"
						)
				)
														
		);
		

		add_action('admin_menu', array($this, 'setting_menu'));
		add_action('admin_init', array($this, 'setting_save'));
		add_action("wp_ajax_Writesonic_Business_search", array($this, "writesonic_search"));
		add_action("wp_ajax_nopriv_Writesonic_Business_search", array($this, "writesonic_search"));
		add_action("wp_ajax_Writesonic_Business_search_front", array($this, "writesonic_search_front"));
		add_action("wp_ajax_nopriv_Writesonic_Business_search_front", array($this, "writesonic_search_front"));
		add_action( 'wpinv_status_publish', array($this,'wpi_custom_on_payment_complete'), 1, 1);
		add_shortcode('Writesonic_Business_fliter', array($this, 'Writesonic_fliter'));
		add_filter( 'manage_users_columns', array( $this, 'writesonic_add_user_column') );
		add_filter( 'manage_users_custom_column', array( $this, 'writesonic_user_column_content') , 10, 3 );
		register_activation_hook( __FILE__, array( $this, 'transaction_table_add'));
		add_action('wp_enqueue_scripts', array( $this,'callback_for_setting_up_scripts'));
		add_action('rest_api_init', array($this, 'get_writer_data_api'));
		add_action('rest_api_init', array($this, 'get_writer_ads_content_api'));
		add_action('woocommerce_before_thankyou', array($this, 'update_credit'), 10, 1);
		
	}

	function update_credit($order_id) {
	    if ( ! $order_id )
	        return;

	    
	    global $wpdb;
	    global $current_user, $wp_roles;
	    $user_id = $current_user->ID;
	    $api_order_id = get_user_meta(get_current_user_id(), "SMMAPI_order_$order_id", true);

	    $order = wc_get_order($order_id);
	    
        $items = $order->get_items();        
        foreach ($items as $item_id => $item_data) {
            $qty = $item_data->get_quantity();
            $order_product_id = $item_data->get_product_id();
	        if ($order_product_id == get_option( 'writesonic_bussiness_credit_product' )) {
	            if(!get_post_meta( $order_id, 'writesonic_credit_updated', TRUE)) {
	                $user_credit=0;
	                $user_last_credit= get_user_meta($user_id, 'writesonic_credit', true);
	                if(isset($user_last_credit)){            
	                    $user_credit= (int) $user_last_credit+$qty;
	                }

	                update_user_meta($user_id, 'writesonic_credit', $user_credit);
	                update_post_meta($order_id,'writesonic_credit_updated',1);
	                echo "<script>window.location.href=window.location.origin+'/writer';</script>";
	            }  
	        }	          
        }
	    
	}
	public function get_writer_data_api() {
		 register_rest_route( 'wpcustomusers/v1', '/all/', array(
            'methods' => 'POST',
            'callback' => array($this, 'get_writer_ads_info_func'),
                )
        );
	}

	public function get_writer_ads_info_func($request_data){
		$integration = $this->integration;
		$writesonic_credit = json_decode(get_option( 'writesonic_credit' ),true);
		// $user_credit = get_user_meta(get_current_user_id(),'writesonic_credit',true);//print_r($user_credit);
		// if(empty($user_credit)){
		// 	$user_credit = 0;
		// }
		//print_r($request_data);
		//exit;
		$siteblox_key 		= $request_data['siteblox_key'];

		global $wpdb;
		$conn_site 			= $wpdb->prefix . 'bloxx_apis';
		$conn_limit 		= "SELECT * FROM `$conn_site` WHERE `api_key` = '$siteblox_key' AND `prime_key` = '1' LIMIT 1";
		$conn_limit_query 	= $wpdb->get_results($conn_limit);
		$count_limit 		= count($conn_limit_query);
		if($count_limit==1){
			$bloxx_api 				= $wpdb->get_row($conn_limit);
			$bloxx_userid 			= $bloxx_api->user_id;
			$user_credit 			= get_user_meta($bloxx_userid,'writesonic_credit',true);
		}
		return array('writesonic_credit' => $writesonic_credit,'integration'=>$integration,'user_credit'=>$user_credit);
	}
	public function get_writer_ads_content_api() {
		 register_rest_route( 'wpadscustomusers/v1', '/all/', array(
            'methods' => 'POST',
            'callback' => array($this, 'get_writer_ads_content_func'),
                )
        );
	}

	public function get_writer_ads_content_func($request_data){
		 global $wpdb;

		$request = $request_data->get_params();
		$re_type = $request['type'];
		$re_category = $request['category'];
		$int_arr = array_column($this->integration[$re_category], 'name','slug');
		$writesonic_credit  = json_decode(get_option( 'writesonic_credit' ),true);
		$credit_required 	= $writesonic_credit[$request['type']];
		
		$siteblox_key 		= $request['siteblox_key'];

		// get blox user id
		$conn_site 			= $wpdb->prefix . 'bloxx_apis';
		$conn_limit 		= "SELECT * FROM `$conn_site` WHERE `api_key` = '$siteblox_key' AND `prime_key` = '1' LIMIT 1";
		$conn_limit_query 	= $wpdb->get_results($conn_limit);
		$count_limit 		= count($conn_limit_query);

		if($count_limit==1){
			$bloxx_api 				= $wpdb->get_row($conn_limit);
			$request['end_user_id'] = $bloxx_api->user_id;
			$bloxx_userid 			= $bloxx_api->user_id;
			$user_credit 			= get_user_meta($bloxx_userid,'writesonic_credit',true);
		}else{
			echo json_encode(array('success'=>false));
			die;
		}
		// $request['end_user_id'] = 6;
		// $bloxx_userid 			= 6;
		// $user_credit = 15;
		
		
		if($user_credit < $credit_required){			
			echo json_encode(array('success'=>false));
			die;
		}else{
			//echo '<pre>';
			//print_r($request);exit;
			//$request['end_user_id'] = (string)get_current_user_id();
			$API = new API_call_writesonic_business();
			$list = $API->call($request,$request['type'],$request['lang'],$request['engine']);
			$list_reponse = json_decode($list,true);
			
			
			if(empty($list_reponse['detail']) || !isset($list_reponse['detail'])){
				$credit_left = $user_credit-$credit_required;
				update_user_meta( $bloxx_userid, "writesonic_credit", $credit_left);
				global $wpdb;     
				$table_name = $wpdb->prefix . 'writesonic_credit_transaction';
				$date = date('Y-m-d H:i:s');
				$wpdb->insert($table_name, array('credit' => $credit_required, 'type' => "debit", 'action' => "Credit used for search AI ".$int_arr[$re_type], 'user_id' => $bloxx_userid, 'dateTime' => $date)); 
			}
			
			
			echo $list;
			die;
		}
	}

	public function callback_for_setting_up_scripts() {
	     wp_register_style( 'writesonic-Bussiness-style', plugins_url('/assets/css/style_front.css?v'.time(), __FILE__), false,'all');
	     wp_enqueue_style( 'writesonic-Bussiness-style' );
	}
	function transaction_table_add(){
		global $wpdb;
	    $charset_collate = $wpdb->get_charset_collate();
	    $table_name = $wpdb->prefix . 'writesonic_credit_transaction';
	    $sql = "CREATE TABLE `$table_name` (
											    `id` INT(11) NOT NULL AUTO_INCREMENT,
											    `credit` int(11) DEFAULT '1',
											    `type` text DEFAULT NULL,
											    `action` text DEFAULT NULL,
											    `user_id` int(11) DEFAULT '1',
											    `dateTime` dateTime DEFAULT CURRENT_TIMESTAMP,
											    PRIMARY KEY(id)
											 ) 
											 ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
	    	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	    	dbDelta($sql);
	    }
	}	
	function wpi_custom_on_payment_complete( $invoice_id ) {
        $invoice = wpinv_get_invoice( $invoice_id );            	        
        if ( !empty( $invoice ) ) {
            $cart_items = $invoice->get_cart_details();
            if ( !empty( $cart_items ) ) {
                foreach ( $cart_items as $key => $cart_item ) {                	
                	if($cart_item['item_id']==get_option( 'writesonic_credit_product' )){
                		$writesonic_credit = get_user_meta(  $invoice->author, 'writesonic_credit',true );
                		if(empty($writesonic_credit)){
                			$writesonic_credit =0;
                		}
                		$writesonic_credit = $writesonic_credit+$cart_item['quantity'];
                		update_user_meta( $invoice->author, "writesonic_credit", $writesonic_credit);
						global $wpdb;     
						$table_name = $wpdb->prefix . 'writesonic_credit_transaction';
						$date = date('Y-m-d H:i:s');     
						$wpdb->insert($table_name, array('credit' => $cart_item['quantity'], 'type' => "credit", 'action' => "New credit purchase", 'user_id' => $invoice->author, 'dateTime' => $date)); 
                	}
                }
            }
        }
	}
	public function writesonic_add_user_column($column){
		$column['writesonic_credit'] = __('Neo Crystals','writesonic_credit');
        return $column;
	}
	public function writesonic_user_column_content( $val, $column_name, $user_id ) {
        switch ($column_name) {
            case 'writesonic_credit' :
            	$writesonic_credit = get_user_meta(  $user_id, 'writesonic_credit',true );
        		if(empty($writesonic_credit)){
        			$writesonic_credit =0;
        		}
                return $writesonic_credit;
                break;
            default:
        }
        return $val;
    }
	public function setting_menu(){

		if ( is_plugin_active( 'divi-builder/divi_builder.php' ) ) {
		    //plugin is activated
		    //add_submenu_page( 'bloxx-app', 'Writesonic Tools', 'Writesonic Tools', 'manage_options','writesonic-bussiness',array($this,  'content') );
		    add_submenu_page( 'bloxx-app', 'Writesonic', 'Writesonic', 'manage_options','writesonic',array($this,  'content') );
	    	//add_submenu_page( 'bloxx-app', 'Writesonic Settings', 'Writesonic Settings', 'manage_options','writesonic_bussiness_setting',array($this,  'settings') );
		} else{
			 add_menu_page('Writesonic Tools', 'Writesonic Tools', 'manage_options', 'writesonic',array($this,  'content') );
	         add_submenu_page( 'writesonic-bussiness', 'Setting', 'Setting', 'manage_options','writesonic_bussiness_setting',array($this,  'settings'), 1 );
		}	
	    

	    
	}

	public function settings(){
		/*if ( ! class_exists( 'WPInv_Plugin', false ) ) {
			die("<h1>GetPaid plugin should Enable</h1>");
		}*/
	 	require 'template/setting.php';
	}
	public function content(){
		if(empty(get_option( 'api_key_writesonic_bussiness' ))){
			die("<h1>Please Configure API key for writesonic</h1>");
		}
		echo "<style>".file_get_contents(plugin_dir_path(__FILE__).'/assets/css/style.css')."</style>";


		 $default_tab = null;
  		$tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;

  ?>
  <!-- Our admin page content should all be inside .wrap -->
  <div class="wrap">
    <!-- Print the page title -->
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <!-- Here are our tabs -->
    <nav class="nav-tab-wrapper">
      <a href="?page=writesonic&tab=writesonic_tools" class="nav-tab <?php if($tab==='writesonic_tools'):?>nav-tab-active<?php endif; ?>">Tools</a>
      <!-- <a href="?page=my-plugin&tab=settings" class="nav-tab <?php if($tab==='settings'):?>nav-tab-active<?php endif; ?>">Settings</a> -->
      <a href="?page=writesonic&tab=writesonic_settings" class="nav-tab <?php if($tab==='writesonic_settings'):?>nav-tab-active<?php endif; ?>">Settings</a>
    </nav>

    <div class="tab-content">
    <?php switch($tab) :
      case 'writesonic_tools':
     // die('stop tools');
        //echo 'Server Settings'; //Put your HTML here
		include_once( plugin_dir_path( __FILE__ ) . 'template/content.php');
		break;
      case 'writesonic_settings':
        include_once( plugin_dir_path( __FILE__ ) . 'template/setting.php');
        break;
      default:
        include_once( plugin_dir_path( __FILE__ ) . 'template/content.php');
        break;
    endswitch; ?>
    </div>
  </div>



		<?php
		echo "<script> var ajax_url= '".admin_url( 'admin-ajax.php' )."';".file_get_contents(plugin_dir_path(__FILE__).'/assets/js/script.js')."</script>";
	}
	public function Writesonic_fliter($arg){
		if(empty(get_option( 'api_key_writesonic_bussiness' ))){
			return "<h1>Please Configure API key for writesonic</h1>";
		}else{
			ob_start();
			if ( is_user_logged_in() ) {
				?>
				<div class="contentWrapper user_actions" id="table-page">
					<!-- //sidebar  --> 
					<?php require_once WP_PLUGIN_DIR.'/divi-builder/templates/builder_siderbar.php'; ?>
					<div class="wrapContent">
					   
						<?php require_once WP_PLUGIN_DIR.'/divi-builder/templates/builder_topnav.php'; ?>

					   	<div class="wrapContainer user_actions">
						   	<div class="sectionTitle filter-options">
						   		<!-- <h3><img src="<?php //echo plugins_url(); ?>/divi-builder/images/section-icon.png" alt="..." /> My Library</h3> -->
								   <?php
								echo "<style>".file_get_contents(plugin_dir_path(__FILE__).'/assets/css/style_front.css')."</style>";
								require 'template/front/short_code.php';			   	
								?>
							</div>
						</div>
					</div>
				</div>
				<?php
				echo "<script> var ajax_url= '".admin_url( 'admin-ajax.php' )."';jQuery('#".@$_GET['type']."').show();".file_get_contents(plugin_dir_path(__FILE__).'/assets/js/script_front.js')."</script>";
			} else {
				global $wp;  
				// $current_url = home_url(add_query_arg(array($_GET), $wp->request));
			 	// $login_url = wp_login_url($current_url);
			 	// echo "<a href='".$login_url."'>Click Here</a> for login";
			 	restricate_page_content();
			}
			$output .= ob_get_contents();
			ob_end_clean();
			return $output;
		}		
	}
	function setting_save(){
		
		
		if(isset($_POST['api_key_writesonic_bussiness'])){
			
			update_option( 'api_key_writesonic_bussiness', $_POST['api_key_writesonic_bussiness'] );
			update_option( 'writesonic_credit', json_encode($_POST['writesonic_bussiness_credit']));
			update_option( 'writesonic_integration_desc', json_encode($_POST['writesonic_integration_desc']));
			update_option( 'writesonic_integration_switch', json_encode($_POST['writesonic_integration_switch']));
			update_option( 'writesonic_bussiness_credit_product', $_POST['writesonic_bussiness_credit_product']);
			wp_safe_redirect(esc_url(site_url( '/wp-admin/admin.php?page=writesonic&tab=writesonic_settings' )));
			exit();
		}
	}
	public function writesonic_search(){
		unset($_POST['action']);
		$_POST['end_user_id'] = (string)time();
		$API = new API_call_writesonic_business();
		$list = $API->call($_POST,$_POST['type'],$_POST['lang'],$_POST['engine']);
		echo $list;
		die;
	}
	public function writesonic_search_front(){
		$int_arr = array_column($this->integration[$_POST['category']], 'name','slug');
		unset($_POST['action']);
		$writesonic_credit = json_decode(get_option( 'writesonic_credit' ),true);
		$credit_required = $writesonic_credit[$_POST['type']];
		$user_credit = get_user_meta(get_current_user_id(),'writesonic_credit',true);
		
		if($user_credit<$credit_required){			
			echo json_encode(array('success'=>false));
			die;
		}else{
			$_POST['end_user_id'] = (string)get_current_user_id();
			$API = new API_call_writesonic_business();
			$list = $API->call($_POST,$_POST['type'],$_POST['lang'],$_POST['engine']);
			$list_reponse = json_decode($list,true);
			if(empty($list_reponse['detail']) || !isset($list_reponse['detail'])){
				$credit_left = $user_credit-$credit_required;
				update_user_meta( get_current_user_id(), "writesonic_credit", $credit_left);
				global $wpdb;     
				$table_name = $wpdb->prefix . 'writesonic_credit_transaction';
				$date = date('Y-m-d H:i:s');
				$wpdb->insert($table_name, array('credit' => $credit_required, 'type' => "debit", 'action' => "Credit used for search AI ".$int_arr[$_POST['type']], 'user_id' => get_current_user_id(), 'dateTime' => $date)); 
			}
			echo $list;
			die;
		}		
	}
}
$Writesonic = new Writesonic_Business();