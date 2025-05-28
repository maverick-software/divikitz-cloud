<?php

class BloxxAdmin{
	
	public $response_msg = '';
	public $server_sizes = [ "768MB", "1GB", "2GB", "4GB", "8GB", "16GB", "32GB", "64GB" ];
	public function __construct(){
		add_action( 'admin_menu', array($this,'bloxx_admin_menu') );
		add_action('wp_ajax_mark_as_read',array($this,'markasread'));
		add_action('wp_ajax_nopriv_mark_as_read',array($this,'markasread'));
		add_action( 'admin_enqueue_scripts', array($this,'enqueue_admin_scripts') );
		add_shortcode( 'bloxx_cron_hook', array($this,'serverUsage') );
		add_action( 'bloxx_cron_hookk', array($this,'bloxx_cron_process') );
		add_action( 'bloxx_cron_hook_app', array($this,'bloxx_cron_process_app') );
		add_action( 'trackserverUsage',  array($this,'serverUsage' ),10,2);
		add_action( 'monitorserver', array($this,'checkforserverstatus'),10,1 );




	}

	public function enqueue_admin_scripts() {
		wp_enqueue_style('bloxxapp_admin-css', bloxx_url."admin/bloxx_admin.css?v=".time());
		wp_enqueue_style('bloxxapp_admin-datatable-css', "//cdn.datatables.net/1.11.1/css/jquery.dataTables.min.css?v=".time());
	    wp_enqueue_script( 'bloxxapp_admin-js', bloxx_url.'admin/bloxx_admin.js?v='.time(), array('jquery'));
	    wp_enqueue_script( 'bloxxapp_admin-datatable-js', '//cdn.datatables.net/1.11.1/js/jquery.dataTables.min.js?v='.time(), array('jquery'));

	}
	
	

	public function appstatus($op_id){global $wpdb;
		$cloud = new Cloudways();
		$output = json_decode($cloud->getRequest('/operation/'.$op_id.'?access_token='.$cloud->getToken()));
			
		if(isset($output->operation)  && $output->operation->is_completed == '0'){
			return $this->appstatus($op_id);
		}else{
			$wpdb->insert('bloxx_testing',['data'=>'app created']);
			return true;
		}
	}


		
	public function generateRandomString($length = 5) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}

	public function bloxx_cron_process($args){
		global $wpdb;
		$group = $wpdb->get_row( "SELECT * FROM ".$wpdb->prefix."bloxx_clusters WHERE id = ".$args['group_id']  );
		$table = $wpdb->prefix."bloxx_clusters";
		if(!empty($group)){
			$cloudways = new Cloudways();
			$servername = 'Bloxx '.$this->generateRandomString(5);
			$response = $cloudways->postRequest('/server',array('cloud' => 'do','region'=>'sfo1','instance_type'=>$group->server_size,'application'=>'wordpress','app_version'=>'5.8','server_label'=>$servername ,'app_label'=>$servername));
			$response = json_decode($response);
			
			if(isset($response->server) && !empty($response->server)){
				$newarr = @unserialize($group->servers);
				if($newarr === false){
					$newarr = [];
				}
				array_push($newarr, $response->server->id);
			    $wpdb->update($table, array('servers'=>serialize($newarr)),['id'=>$group->id]);die;
				
			}
		}
	}		

	public function checkifserverCreated(){
		$cloudways = new Cloudways();
		$output = json_decode($cloud->getRequest('/operation/'.$operation_id.'?access_token='.$cloud->getToken()));
		if(isset($output->operation)  && $output->operation->is_completed == '1'){
			return true;
		}else{
			sleep(60);
			return $this->checkifserverCreated($operation_id);
		}
	}

	public function checkforserverstatus($server_id){	
		// if(is_admin()){
		// 	return;
		// }
		// $server_id = '666664';
		$cloudways = new Cloudways();
		$result = json_decode($cloudways->getRequest('/server/analytics/serverUsage?server_id='.$server_id.'&access_token='.$cloudways->getToken()));
		if(isset($result->operation_id)){
			$time = time() + 60;
            wp_schedule_single_event($time, 'trackserverUsage', [$result->operation_id,$server_id]);
		}

		
	}

	public function serverUsage($operation_id,$server_id){
		//$server_id = '666664';
		$cloudways = new Cloudways();
		//$operation_id = '13660381';
		$result = json_decode($cloudways->getRequest('/operation/'.$operation_id.'?access_token='.$cloudways->getToken()));
		$all_servers = $cloudways->getallapps();
		$all_servers = json_decode($all_servers);
		if(isset($result->operation) && $result->operation->is_completed == '1'){
			$used_space = (float)str_replace('%', '', json_decode($result->operation->parameters)->server->disk_used_perc);
			if($used_space >= 85){
				$pkey = getparentkeyindex($server_id,$all_servers->servers);
				if($pkey != '' && !empty($pkey)){
					if($pkey + 1 < count($this->server_sizes)) {
						$output = $cloudways->postRequest('/server/scaleServer',array('instance_type'=>$this->server_sizes[$pkey],'server_id'=>$server_id));
					}
					die;
				}else{
					die;
				}
			}else{
				echo "no";die;
			}
		}else{
			echo "here";die;
		}
	}

	public function bloxx_admin_menu() {
		add_menu_page( BLOXX_PLUGIN_NAME, 'NeoSoft', 'administrator', 'bloxx-app', array( $this, 'server_api_settings' ), 'dashicons-tickets', 26  );
		//add_submenu_page('bloxx-app','Clusters', 'Clusters','administrator', 'clusters', array( $this, 'bloxxclusters' ) ); 
		//add_submenu_page('bloxx-app','Servers', 'Servers','administrator', 'servers', array( $this, 'bloxxserverslist' ) ); 
		add_submenu_page('bloxx-app','Cloudways', 'Cloudways','administrator', 'cloudways', array( $this, 'bloxxcloudways' ) );
	}

	public function bloxxNotifications(){
		require_once('bloxx_notifications.php');
	}

	public function bloxxclusters(){
		$msg = '';
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(isset($_POST['name']) && !empty($_POST['name'])){
				global $wpdb;
				$table = $wpdb->prefix.'bloxx_clusters';
				$res = 200;
				if(isset($_POST['update_id']) && !empty($_POST['update_id'])){
					$plan = (isset($_POST['plan_id']))?serialize($_POST['plan_id']):null;
					$wpdb->update($table, ['name'=>$_POST['name'],'app_size'=>$_POST['size'],'server_size'=>$_POST['memory_size'],'plans'=>$plan,'type'=>$_POST['type']], array('id'=>$_POST['update_id']));
				}else{
					$plan = (isset($_POST['plan_id']))?serialize($_POST['plan_id']):null;
					$wpdb->insert($table,['name'=>$_POST['name'],'app_size'=>$_POST['size'],'server_size'=>$_POST['memory_size'],'servers'=>null,'plans'=>$plan,'type'=>$_POST['type']]);
				}
				$msg = 'Settings Saved Successfully';
			}
		}
		include_once( plugin_dir_path( __FILE__ ) . 'templates/clusters.php');
	}

	public function bloxxcloudways(){

		//Get the active tab from the $_GET param

		$msg = '';
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(isset($_POST['name']) && !empty($_POST['name'])){
				global $wpdb;
				$table = $wpdb->prefix.'bloxx_clusters';
				$res = 200;
				if(isset($_POST['update_id']) && !empty($_POST['update_id'])){
					$plan = (isset($_POST['plan_id']))?serialize($_POST['plan_id']):null;
					$wpdb->update($table, ['name'=>$_POST['name'],'app_size'=>$_POST['size'],'server_size'=>$_POST['memory_size'],'plans'=>$plan,'type'=>$_POST['type']], array('id'=>$_POST['update_id']));
				}else{
					$plan = (isset($_POST['plan_id']))?serialize($_POST['plan_id']):null;
					$wpdb->insert($table,['name'=>$_POST['name'],'app_size'=>$_POST['size'],'server_size'=>$_POST['memory_size'],'servers'=>null,'plans'=>$plan,'type'=>$_POST['type']]);
				}
				$msg = 'Settings Saved Successfully';
			}
		}


  $default_tab = null;
  $tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;

  ?>
  <!-- Our admin page content should all be inside .wrap -->
  <div class="wrap">
    <!-- Print the page title -->
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <!-- Here are our tabs -->
    <nav class="nav-tab-wrapper">
      <a href="?page=cloudways&tab=servers" class="nav-tab <?php if($tab==='servers'):?>nav-tab-active<?php endif; ?>">Servers</a>
      <!-- <a href="?page=my-plugin&tab=settings" class="nav-tab <?php if($tab==='settings'):?>nav-tab-active<?php endif; ?>">Settings</a> -->
      <a href="?page=cloudways&tab=clusters" class="nav-tab <?php if($tab==='clusters'):?>nav-tab-active<?php endif; ?>">Load Balancing</a>
    </nav>

    <div class="tab-content">
    <?php switch($tab) :
      case 'servers':
        //echo 'Server Settings'; //Put your HTML here

        if(isset($_GET['type']) && $_GET['type'] == 'new'){
			include_once( plugin_dir_path( __FILE__ ) . 'templates/add_server.php');
		}elseif(isset($_GET['show_apps']) && $_GET['show_apps'] == true && isset($_GET['server_id'])){
			include_once( plugin_dir_path( __FILE__ ) . 'templates/server_apps.php');
		}elseif(isset($_GET['type']) && $_GET['type'] == 'stats' && isset($_GET['server_id'])){
			include_once( plugin_dir_path( __FILE__ ) . 'templates/server_stats.php');
		}else{
			include_once( plugin_dir_path( __FILE__ ) . 'templates/servers.php');
		}


        break;
      case 'clusters':
        include_once( plugin_dir_path( __FILE__ ) . 'templates/clusters.php');
        break;
      default:
        include_once( plugin_dir_path( __FILE__ ) . 'templates/servers.php');
        break;
    endswitch; ?>
    </div>
  </div>
  	<?php
		// if(isset($_GET['type']) && $_GET['type'] == 'new'){
		// 	include_once( plugin_dir_path( __FILE__ ) . 'templates/add_server.php');
		// }elseif(isset($_GET['show_apps']) && $_GET['show_apps'] == true && isset($_GET['server_id'])){
		// 	include_once( plugin_dir_path( __FILE__ ) . 'templates/server_apps.php');
		// }elseif(isset($_GET['type']) && $_GET['type'] == 'stats' && isset($_GET['server_id'])){
		// 	include_once( plugin_dir_path( __FILE__ ) . 'templates/server_stats.php');
		// }else{
		// 	include_once( plugin_dir_path( __FILE__ ) . 'templates/servers.php');
		// }
	}

	public function markasread(){
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			global $wpdb;
			$table = $wpdb->prefix . 'bloxx_notifications';
			if($wpdb->update($table, array('status'=>1), array('id'=>$_POST['id']))){
				$current_user = wp_get_current_user();
				$to = $current_user->user_email;
				$email  = get_option('admin_email');
				$subject = "Domain Request";
				$headers = 'From: '. $email . "\r\n" .
				'Reply-To: ' . $email . "\r\n";
				
				$message = 'Your request for adding primary domain to your application has been completed.';
				wp_mail($to, $subject, strip_tags($message), $headers);
				$code = 200; $msg = 'Mark completed successfully.';
			}else{
				$code = 200; $msg = 'Something Went Wrong!';
			}
			$result=array(
				'code' => $code,					
				'message' => $msg
			);
			echo json_encode($result);
			die();
		}else{
			$result=array(
				'code' => 500,					
				'message' => 'Method Not Allowed'
			);
			echo json_encode($result);
			die();
		}
	}
	public function server_api_settings(){
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$this->saveSettings($_POST);
		}
		return $this->getsettingsForm();
	}

	private function saveSettings($data){
		if(isset($data['options'])){
			foreach ($data['options'] as $key => $value) { 
				update_option( $key, $value);
			}
		}
		return true;
	}
	private function getsettingsForm(){
	?>
		<div class="container">
			<div class="setting-content wrap bloxx-setting-box">
				<form method="post">
					<div class="form-group">
						<label for="email">Enter Your Server Email
						<input type="email" id="email" name="options[cloudways_email]" value="<?php echo get_option('cloudways_email','');?>" class="regular-text"  required>
					</div>
					<div class="form-group">
						<label for="api_key">Enter Your API Key</label>
						<input type="text" id="api_key" name="options[cloudways_api_key]" value="<?php echo get_option('cloudways_api_key','');?>" class="regular-text" required>
					</div>
					<input type="submit" name="save" value="Save">
				</form>
			</div>
		</div>
	<?php	
	}
}


$domain = new BloxxAdmin();