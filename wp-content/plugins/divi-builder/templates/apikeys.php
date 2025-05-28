<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Apikeys {

    public function __construct() {
        add_shortcode('my_active_apikeys', array($this, 'my_apikeys'));
        
        
        //Generate Sitebloxx Plugin API Key
        add_action("wp_ajax_bloxx_generated_key", array($this, "bloxx_generated_key"));
        add_action("wp_ajax_nopriv_bloxx_generated_key", array($this, "bloxx_generated_key"));


        //API Key Action
        add_action("wp_ajax_useraction_api", array($this, "useraction_api"));
        add_action("wp_ajax_nopriv_useraction_api", array($this, "useraction_api"));
    }
    
    
    public function bloxx_generated_key($type= null){

        global $wpdb;
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;   

        
        
        $api_limit= get_user_meta($user_id, 'api_limit', true);
        
        $bloxx_apis_tb = $wpdb->prefix . 'bloxx_apis';
        $api_query = "Select * from $bloxx_apis_tb where user_id='$user_id'";
        $connected_api = $wpdb->get_results($api_query);
        $count_api = count($connected_api);
        $userdata = get_user_by('id', $user_id);
        $bloxx_username = $userdata->user_login;
        //pre($connected_api);
        
        if($count_api < $api_limit){            
            $bloxx_generated_key = password_hash("bloxx_builder", PASSWORD_DEFAULT);
            $now=date('Y-m-d H:i:s');
            $data = array(
                //'api_username' => $bloxx_username,
                'api_key' => $bloxx_generated_key,
                'status' => 2,
                'user_id' => $user_id,                
                'api_token' => md5("bloxx_builder"),
                'prime_key' => 1,
                'plugin_limit'=> $api_limit,
                'created_at'=> $now
            );
            $id = $wpdb->insert($bloxx_apis_tb, $data);  
           
            $redirect_url=site_url()."/bloxx-account/?tab=apikey";
            $result=array(
                'code'=> 200,
                'message'=> "Plugin API generated successfully",
                'redirect_url'=> $redirect_url,
                'generated_id'=> $id
            );

        } else {
            $result=array(
                'code'=> 202,
                'redirect_url'=> $redirect_url,
                'message'=> "You have exceeded the maximum number of API limits, Please upgrade your plan limit"                
            );
        }
        
        if($type=="from_function"){
            return true;
        } else {
            echo json_encode($result);
            die();
        }
        
    }


    public function useraction_api(){
        global $wpdb;
        extract($_REQUEST);
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;   
        $plugins_limit= get_user_meta($user_id, 'api_limit', true);

        $bloxx_apis_tb = $wpdb->prefix . 'bloxx_apis';        
        $api_query = "Select * from $bloxx_apis_tb where id='$type_id'";
        $connected_api = $wpdb->get_row($api_query);
        $api_key= $connected_api->api_key;
        $total_res = $wpdb->get_results($api_query);
        $count_api = count($total_res);
        $redirect_url=site_url()."/bloxx-account/?tab=apikey";

        if($count_api==0){
            $result=array(
                'code'=> 202,
                'message'=> "May be record already deleted, If not then please refresh page and try again"                
            );
        } else {
        
            if($type=="regenerate_key"){
                $bloxx_generated_key = password_hash("bloxx_builder", PASSWORD_DEFAULT);

                $data = array(
                    'api_key' => $bloxx_generated_key,
                    'plugin_limit'=> $plugins_limit,
                    'status' => 2
                );
                $wpdb->update($bloxx_apis_tb, $data, array('api_key' => $api_key));
                $result=array(
                    'code'=> 200,
                    'redirect_url'=> $redirect_url,
                    'message'=> "Plugin API generated successfully"                
                );
            } else if($type=="trash_key"){
                $wpdb->delete( $bloxx_apis_tb, array('api_key' => $api_key));
                $result=array(
                    'code'=> 200,
                    'redirect_url'=> $redirect_url,
                    'message'=> "Plugin API deleted successfully"                
                );
            } else {
                $result=array(
                    'code'=> 202,
                    'redirect_url'=> $redirect_url,
                    'message'=> "Action has been failed, Please try again later"                
                );
            }
        }

        echo json_encode($result);
        die();
    }
    
    
    public function my_apikeys() {
        if (is_user_logged_in()) {
            global $current_user, $wp_roles;
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
            ?>


            <div class="contentWrapper user_actions" id="table-page">
                <!-- //sidebar  --> 
                <?php require_once 'builder_siderbar.php'; ?>

                <div class="wrapContent">
                    <div class="topWrapmenu">
                        <div>
                            <a href="javascript:void(0);" class="togglebar"><img src="<?php echo plugins_url(); ?>/divi-builder/images/right-angle.png"/></a>
                        </div>
                        <div class="rowWrap">
                            <div class="flex-3">                                                
                                <?php
                                global $wpdb;
                                global $wp_roles;
                                global $ultimatemember;
                                $user = wp_get_current_user();

                                $current_user_id = $user->ID;
                                $current_email = $user->user_email;

                                $subs_plan = get_user_meta($current_user_id, 'current_plan', true);
                                $plugins_limit = get_user_meta($current_user_id, 'plugins_limit', true);


                                $wp_inv = $wpdb->prefix . 'wpinv_subscriptions';
                                $wp_inv_item = $wpdb->prefix . 'getpaid_invoice_items';
                                $plan_query = "select e.*, o.* from $wp_inv e join $wp_inv_item o where e.parent_payment_id=o.post_id and e.product_id='$subs_plan' order by e.id desc limit 1 ";
                                $plan_status = $wpdb->get_row($plan_query);

                                $plan_price = 0.00;
                                if (isset($plan_status->price)) {
                                    $plan_price = $plan_status->price;
                                }
                                $plan_ren_date = "-N/A-";
                                if (isset($plan_status->expiration)) {
                                    $plan_ren_date = date("M d, Y", strtotime($plan_status->expiration));
                                }

                                $plan_period = "-N/A-";
                                if (isset($plan_status->period)) {
                                    $plan_period = ucfirst($plan_status->period);
                                }
                                ?>
                                <h5><?php echo get_the_title(); ?></h5>
                            </div>
                            <div class="flex-9 text-right">
                                <ul class="topMenuUser">
                                <a href="<?php echo builder_url.'assets/addons/bloxx.zip'; ?>" download><i class="far fa-file-archive"></i> Download Plugin</a>
                                    <a href="javascript:void(0)" class="videoButton"><i class="fas fa-video"></i> Tutorials</a>                                    
                                    <li><a href="#"><i class="far fa-bell"></i></a></li>
                                    <li><?php echo do_shortcode('[profile_details]'); ?></li>
                                </ul>
                            </div>      
                        </div>
                    </div>


                    <div class="wrapContainer">
                        <a href="<?= site_url(); ?>/my-projects" class="link-btn">
                            <img src="<?php echo plugins_url(); ?>/divi-builder/images/arrow-alt-circle-left.png" alt="..." /> Go Back to Apps</span>
                        </a>
                        <div class="rowWrap">
                            <div class="flex-12">
                                <div class="dashboard_no mBottom hostingTables">
                                    <h3>Account Plan</h3>
                                    <div class="mediaObjext">
                                        <div class="mediaImage">
                                            <img src="<?php echo plugins_url(); ?>/divi-builder/images/teamplan.png" alt="..." />
                                            <p>
                                                <strong><?php echo str_replace(" Monthly Plan", "", str_replace(" Yearly Plan", "", get_the_title($subs_plan))); ?></strong>
                                                <span>Limit <?= $plugins_limit; ?></span>
                                            </p>
                                        </div>
                                        <div class="mediaPrice"><a href="<?php echo site_url('plans/'); ?>" class="colorTeal">Upgrade</a> $<?= $plan_price; ?></div>
                                    </div>

                                    <h3>Connected API's <a href="javascript:void(0)" title="Create API Key" class="default-btn create_apis">Create API Key</a></h3>

                                    <?php
                                    $i = 1;
                                    $bloxx_apis_tb = $wpdb->prefix . 'bloxx_apis';
                                    $api_query = "Select * from $bloxx_apis_tb where user_id='$user_id' order by id desc";
                                    $connected_api = $wpdb->get_results($api_query);
                                    $count_api = count($connected_api);
                                    ?>

                                    <div class="table-responsive">
                                        <table>
                                            <tbody>
                                                <tr class="smallHidden">
                                                    <th>Sr.No</th>
                                                    <th>Website</th>                                                    
                                                    <th>API Key</th>
                                                    <th>Status</th>
                                                    <th>Created</th>
                                                    <th>Action</th>
                                                </tr>

                                                <?php if (isset($connected_api) && !empty($connected_api)) { ?>

                                                    <?php
                                                    foreach ($connected_api as $con_api):
                                                        $key_id=$con_api->id;
                                                        $website_url= ($con_api->website_url!="") ? $con_api->website_url : '-N/A-';
                                                        $api_keys=$con_api->api_key;
                                                        $api_keys_format= "<span class='apKey'><span>".$api_keys."</span> <a href='javascript:void(0)' class='copy-btn' id='".$key_id."' data-id='".$api_keys."'><i class='fa fa-copy'></i></a><span class='copyAlert' id='alert_$key_id' style='display:none;'>Copied!</span></span>";
                                                        $api_created= date('M d, Y', strtotime($con_api->created_at));
                                                        $website_status= ($con_api->status!=2) ? "<span class='connected'><i class='fa fa-check'></i></span>" : "<span class='disconnected'><i class='fas fa-times'></i></span>";
                                                        ?>
                                                        <tr class="users_app" id="app_<?= $key_id; ?>">
                                                            <td><span class="desktopHidden">Sr.No</span><?= $i; ?></td>
                                                            <td><span class="desktopHidden">Website</span><span class="webUrl"><?= $website_url; ?></span></td>
                                                            <td><span class="desktopHidden">API Key</span><?= $api_keys_format; ?></td>
                                                            <td><span class="desktopHidden">Status</span><?= $website_status; ?></td>  
                                                            <td><span class="desktopHidden">Created</span><?= $api_created; ?></td>
                                                            <td class="user_action_api"><span class="desktopHidden">Action</span><a href="javascript:void(0)" class="regenerate_key" id="<?= $key_id; ?>" title="Re-Generate Key" data-title="regenerate_key"><i class='fas fa-key'></i></a> <a href="javascript:void(0)" class="trash_key" id="<?= $key_id; ?>" title="Re-Generate Key" data-title="trash_key"><i class="fas fa-trash-alt"></i></a></td>
                                                        </tr>
                                                        <?php $i++; ?>
                                                    <?php endforeach; ?>
                                                <?php } else { ?>
                                                    <tr><td colspan="6" style="text-align: center;font-weight: bold;">Create your first API Key</td></tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>  
                <?php require_once 'builder_mobile_nav.php'; ?>
            </div>
            <?php
        } else {
            restricate_page_content();
        }
    }

}

$apikeys = new Apikeys();
