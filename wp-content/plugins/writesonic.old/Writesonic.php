<?php
/*
Plugin Name: Writesonic
Description: Use shortcode [Writesonic_fliter] to display form on frontend.  
Version: 1.0.1
*/

define('writesonic_url', plugin_dir_url(__FILE__));
define('writesonic_path', plugin_dir_path(__FILE__));
define('writesonic_plugin', plugin_basename(__FILE__));

require 'include/API_call.php';


class Writesonic{
	public $integration = array(
				"advertising" => array(
						array(
							"name"=>"Google ads",
							"slug" => "google-ads",
							"desc"=>"Quality ads that rank in the search results and drive more traffic.",
							"thumb"=> writesonic_url."/assets/images/google-ads.png"
						),

						array(
							"name"=>"Facebook ads",
							"slug" => "facebook-ads",
							"desc"=>"Facebook ad copies that make your ads truly stand out.",
							"thumb"=> writesonic_url."/assets/images/facebook-ads.png"
						),


						array(
							"name"=>"Facebook Ads No Promo",
							"slug"=>"facebook-ads-no-promo",
							"desc"=>"Facebook ad copies that make your ads truly stand out.",
							"thumb"=> writesonic_url."/assets/images/facebook-ads.png"
						)
				),
				"webpage-content" => array(
					array(
						"name"=>"Landing Page Headlines",
						"slug"=> "landing-page-headlines",
						"desc"=>"Unique and catchy headlines that are perfect for your product or service.",
						"thumb"=> writesonic_url."/assets/images/landing-page-headlines.png"
					),

					array(
						"name"=>"Landing Pages",
						"slug"=> "landing-pages",
						"desc"=>"Tailored high-converting landing page copies that drive more leads, sales, and signups.",
						"thumb"=> writesonic_url."/assets/images/landing-pages.png"
					),

					array(
						"name"=>"Product Descriptions",
						"slug"=>"product-descriptions",
						"desc"=>"Authentic product descriptions that will compel, inspire, and influence.",
						"thumb"=> writesonic_url."/assets/images/product-descriptions.png"
					),
					array(
						"name"=>"Feature To Benefits",
						"slug"=>"feature-to-benefits",
						"desc"=>"Unique content that focuses on features to emphasize benefits of your product or service.",
						"thumb"=> writesonic_url."/assets/images/feature-benefits.png"
					),

					array(
						"name"=>"Content Rephrase",
						"slug"=>"content-rephrase",
						"desc"=>"Rephrase your content in a different voice and style to appeal to different readers.",
						"thumb"=> writesonic_url."/assets/images/content-rephrase.png"
					),


					array(
						"name"=>"Content Rephrase Model Two",
						"slug"=>"content-rephrase-model-two",
						"desc"=>"Rephrase your content in a different voice and style to appeal to different readers.",
						"thumb"=> writesonic_url."/assets/images/content-rephrase-modal.png"
					),
					array( 
						"name"=>"Sentence Expand",
						"slug"=>"sentence-expand",
						"desc"=>"Expand short sentences into more descriptive and interesting ones.",
						"thumb"=> writesonic_url."/assets/images/sentence-expand.png"
					),
					array(
						"name"=>"Sentence Expand Model Two",
						"slug"=>"sentence-expand-model-two",
						"desc"=>"Expand short sentences into more descriptive and interesting ones.",
						"thumb"=> writesonic_url."/assets/images/sentence-expand-modal.png"
					)
				),
				"blogs" => array(
						array(
							"name"=>"Listicle Ideas",
							"slug"=>"listicle-ideas",
							"desc"=>"Creative listicle ideas that are easy to write and perform well on social media.",
							"thumb"=> writesonic_url."/assets/images/listicle-ideas.png"
						),
						array(
							"name"=>"Blog Ideas",
							"slug"=>"blog-ideas",
							"desc"=>"Article/blog ideas that you can use to generate more traffic, leads, and sales for your business.",
							"thumb"=> writesonic_url."/assets/images/blog-ideas.png"
						),

						array(
							"name"=>"Blog Intros",
							"slug"=> "blog-intros",
							"desc"=>"Enticing article/blog introductions that capture the attention of the audience.",
							"thumb"=> writesonic_url."/assets/images/blog-intros.png"
						),

						array(
							"name"=>"Blog Outlines",
							"slug"=> "blog-outlines",
							"desc"=>"Detailed article outlines that help you write better content on a consistent basis.",
							"thumb"=> writesonic_url."/assets/images/google-ads.png"
						),

					),
				"emails" => array(
					array(
						"name"=>"Emails",
						"slug"=>"emails",
						"desc"=>"Professional-looking emails that help you engage leads and customers.",
						"thumb"=> writesonic_url."/assets/images/blog-outlines.png"
					),
				),
				"others" => array(
						array(
							"name"=>"Growth Ideas",
							"slug"=> "growth-ideas",
							"desc"=>"High-impact growth tactics to help your business grow.",
							"thumb"=> writesonic_url."/assets/images/growth-ideas.png"
						),

						array(
							"name"=>"Summary",
							"slug"=> "summary",
							"desc"=>"Shortened text copy that provides the main ideas and most important details of your original text.",
							"thumb"=> writesonic_url."/assets/images/summary.png"
						),

						array(
							"name"=>"Pain Agitate Solution",
							"slug"=>"pas",
							"desc"=>"The main formula for writing high-converting sales copy.",
							"thumb"=> writesonic_url."/assets/images/agitile-solutions.png"
						),

						array(
							"name"=>"Keywords Extract",
							"slug"=>"keywords",
							"desc"=>"Keywords extracted from content that you can use for your optimization, SEO, or content creation purposes.",
							"thumb"=> writesonic_url."/assets/images/keyword-extract.png"
						)
				)
														
		);
	public function __construct(){
		add_action('admin_menu', array($this, 'setting_menu'));
		add_action('admin_init', array($this, 'setting_save'));
		add_action("wp_ajax_writesonic_search", array($this, "writesonic_search"));
		add_action("wp_ajax_nopriv_writesonic_search", array($this, "writesonic_search"));
		add_action("wp_ajax_writesonic_search_front", array($this, "writesonic_search_front"));
		add_action("wp_ajax_nopriv_writesonic_search_front", array($this, "writesonic_search_front"));
		add_action( 'wpinv_status_publish', array($this,'wpi_custom_on_payment_complete'), 1, 1);
		add_shortcode('Writesonic_fliter', array($this, 'Writesonic_fliter'));
		add_filter( 'manage_users_columns', array( $this, 'writesonic_add_user_column') );
		add_filter( 'manage_users_custom_column', array( $this, 'writesonic_user_column_content') , 10, 3 );
		register_activation_hook( __FILE__, array( $this, 'transaction_table_add'));
		add_action('wp_enqueue_scripts', array( $this,'callback_for_setting_up_scripts'));
	}

	public function callback_for_setting_up_scripts() {
	     wp_register_style( 'writesonic-style', plugins_url('/assets/css/style_front.css?v'.time(), __FILE__), false,'all');
	     wp_enqueue_style( 'writesonic-style' );
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
		$column['writesonic_credit'] = __('Bloxx Credits','writesonic_credit');
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
	    add_menu_page('Writesonic', 'Writesonic', 'manage_options', 'writesonic',array($this,  'content') );
	    add_submenu_page( 'writesonic', 'Setting', 'Setting', 'manage_options','writesonic_setting',array($this,  'settings'), 1 );
	}

	public function settings(){
		/*if ( ! class_exists( 'WPInv_Plugin', false ) ) {
			die("<h1>GetPaid plugin should Enable</h1>");
		}*/
	 	require 'template/setting.php';
	}
	public function content(){
		if(empty(get_option( 'api_key_writesonic' ))){
			die("<h1>Please Configure API key for writesonic</h1>");
		}
		echo "<style>".file_get_contents(plugin_dir_path(__FILE__).'/assets/css/style.css')."</style>";
		require 'template/content.php';
		echo "<script> var ajax_url= '".admin_url( 'admin-ajax.php' )."';".file_get_contents(plugin_dir_path(__FILE__).'/assets/js/script.js')."</script>";
	}
	public function Writesonic_fliter($arg){
		if(empty(get_option( 'api_key_writesonic' ))){
			return "<h1>Please Configure API key for writesonic</h1>";
		}else{
			ob_start();
			if ( is_user_logged_in() ) {
				?>
				<div class="contentWrapper user_actions" id="table-page">
				<!-- //sidebar  --> 
				<?php require_once WP_PLUGIN_DIR.'/divi-builder/templates/builder_siderbar.php'; ?>
				<div class="wrapContent">
				   <div class="topWrapmenu">
				      <div>
				         <a href="javascript:void(0);" class="togglebar"><img src="<?php echo plugins_url(); ?>/divi-builder/images/right-angle.png"/></a>
				      </div>
				      <div class="rowWrap">
				         <div class="flex-3">
				            <?php 
					            global $wp_roles;
					            global $ultimatemember;
					            $user = wp_get_current_user();
					            $current_user_id= $user->ID;

					            $current_plan=get_user_meta($current_user_id, 'current_plan', true);
					            if($current_plan!=""){
					                $plan_title=get_the_title($current_plan);                
					            } else {
					                $plan_title="Free";                
					            }

					            $display_nm= get_user_meta($current_user_id, "display_name", true);
					            $timestemp= strtotime(date("Y-m-d H:i:s"));
					            $nonce = wp_create_nonce( 'um_upload_nonce-' . $timestemp);

					            um_fetch_user( $current_user_id );
					            
					            $user_profile=get_user_meta($current_user_id, "profile_photo", true);

					            $avatar_uri = um_get_avatar_uri( um_profile('profile_photo'), 32 );
					            if($user_profile==""){
					            	$avatar_uri= builder_url."images/profile-icon.png";
					            }
				            ?>
				            <?php
                                global $wp_query;
                                $post_id = $wp_query->post->ID;
                                ?>
                                <h5><?php echo get_the_title( $post_id ); ?></h5>
				         </div>
				         <div class="flex-9 text-right">
				            <ul class="topMenuUser">
				            	<!-- <a href="<?php // echo builder_url.'assets/addons/bloxx.zip'; ?>" download class="default-btn">Download Plugin</a> -->
				              
				               	<li class="storeIcon"><a href="https://sitebloxx.com/"><i class="fas fa-shopping-basket"></i> Store</a></li>
				               	<li class="plusSign"><a href="javascript:void(0)" title="Add New Section"><i class="fas fa-plus"></i></a></li>
				               	<li><a href="#"><i class="far fa-bell"></i></a></li>
				               	
				               	<li><?php echo do_shortcode('[profile_details]'); ?></li>
				            </ul>
				         </div>
				      </div>
				   </div>
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
				$current_url = home_url(add_query_arg(array($_GET), $wp->request));
			   	$login_url = wp_login_url($current_url);
			   	echo "<a href='".$login_url."'>Click Here</a> for login";
			}
			$output .= ob_get_contents();
			ob_end_clean();
			return $output;
		}		
	}
	function setting_save(){
		if(isset($_POST['api_key_writesonic'])){
			update_option( 'api_key_writesonic', $_POST['api_key_writesonic'] );
			update_option( 'writesonic_credit', json_encode($_POST['writesonic_credit']));
			update_option( 'writesonic_credit_product', $_POST['writesonic_credit_product']);
			wp_safe_redirect(esc_url(site_url( '/wp-admin/admin.php?page=writesonic_setting' )));
			exit();
		}
	}
	public function writesonic_search(){
		unset($_POST['action']);
		$_POST['end_user_id'] = (string)time();
		$API = new API_call_writesonic();
		$list = $API->call($_POST,$_POST['type']);
		echo $list;
		die;
	}
	public function writesonic_search_front(){
		$int_arr = array_column($this->integration[$_POST['category']], 'name','slug');
		unset($_POST['action']);
		$writesonic_credit = json_decode(get_option( 'writesonic_credit' ),true);
		$credit_required = $writesonic_credit[$_POST['type']];
		$user_credit = get_user_meta(get_current_user_id(),'writesonic_credit',true);
		if(empty($user_credit)){
			$user_credit = 0;
		}
		if($user_credit<$credit_required){			
			echo json_encode(array('success'=>false));
			die;
		}else{
			$_POST['end_user_id'] = (string)get_current_user_id();
			$API = new API_call_writesonic();
			$list = $API->call($_POST,$_POST['type']);
			$list_reponse = json_decode($list,true);
			if(!empty($list_reponse['copies'])){
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
$Writesonic = new Writesonic();
?>