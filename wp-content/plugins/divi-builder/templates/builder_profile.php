<?php

class Builder_profile {

    public function __construct() {
        add_shortcode('builder_profile', array($this, 'builder_profile'));
        add_action("wp_ajax_update-user", array($this, "updateProfile"));
        add_action("wp_ajax_nopriv_update-user", array($this, "updateProfile"));

        add_action("wp_ajax_update-divi_license", array($this, "updatedivi_license"));
        add_action("wp_ajax_nopriv_update-divi_license", array($this, "updatedivi_license"));

        //Update billing info
        add_action("wp_ajax_update_billinfo", array($this, "update_billinfo"));
        add_action("wp_ajax_nopriv_update_billinfo", array($this, "update_billinfo"));

        //Save Card Info
        add_action("wp_ajax_save_card_details", array($this, "save_card_details"));
        add_action("wp_ajax_nopriv_save_card_details", array($this, "save_card_details"));
        
        //Delete Card Info
        add_action("wp_ajax_delete_card_element", array($this, "delete_card_element"));
        add_action("wp_ajax_nopriv_delete_card_element", array($this, "delete_card_element"));

        add_filter('um_account_page_default_tabs_hook', array($this, 'remove_privacy'));



        // Block/unblock site  
        add_action("wp_ajax_sitebloxx_block_site_callback", array($this, "sitebloxx_block_site_callback"));
        add_action("wp_ajax_nopriv_sitebloxx_block_site_callback", array($this, "sitebloxx_block_site_callback"));
    }

    function remove_privacy($tabs) {
        unset($tabs[300]);
        return $tabs;
    }


    public function sitebloxx_block_site_callback(){
        extract($_REQUEST);
        global $wpdb;

        $blocked_sites_table = $wpdb->prefix . 'bloxx_blocked_sites';
       
        if (isset($user_action) && $user_action=='block') { 
            $now=date('Y-m-d H:i:s');
            $data = array(
                'api_key' => $api_key,
                'user_id' => $user_id,                
                'term_id' => $term_id,
                'site_url' => $site_url,
                'datetime'=> $now
            );
            $id = $wpdb->insert($blocked_sites_table, $data); 


            $result = array(
                'code' => 200,
                'message' => "URL Blocked Successfully"
            );
        }else if (isset($user_action) && $user_action=='unblock') {   
            $wpdb->delete( $blocked_sites_table, array( 'term_id' => $term_id ) );

            $result = array(
                'code' => 201,
                'message' => "URL Unblocked Successfully"
            );
        } else {
            $result = array(
                'code' => 202,
                'message' => "There is some problem blocking url"
            );
        }
        echo json_encode($result);
        die();
    }

    
    public function delete_card_element(){
        extract($_REQUEST);
        global $current_user;
        $user_id = $current_user->ID;
        $user_card_details = get_user_meta($user_id, 'getpaid_stripe_tokens', true);        
        if (isset($user_card_details) && !empty($user_card_details)) {            
            unset($user_card_details[$del_element]);
            update_user_meta($user_id, 'getpaid_stripe_tokens', $user_card_details);
            $result = array(
                'code' => 200,
                'message' => "Card Deleted Successfully"
            );
        } else {
            $result = array(
                'code' => 202,
                'message' => "There is some proble to delete card details"
            );
        }
        echo json_encode($result);
        die();
    }

    public function save_card_details() {
        extract($_REQUEST);
        global $current_user;
        $user_id = $current_user->ID;
        $user_card_details = get_user_meta($user_id, 'card_details', true);
        
        if($cardfirst_name!="" && $cardlast_name!="" && $number!="" && $expiry!="" && $cvc!=""){
        
            $card_number = str_replace(" ", "", $number);
            if (isset($user_card_details) && !empty($user_card_details)) {
                $card_detail = array(
                    array(
                        'first_name' => $cardfirst_name,
                        'last_name' => $cardlast_name,
                        'card_number' => $card_number,
                        'expiry' => $expiry,
                        'cvc' => $cvc
                    )
                );
                $card_data = array_merge($user_card_details, $card_detail);
            } else {
                $card_data = array(
                    array(
                        'first_name' => $cardfirst_name,
                        'last_name' => $cardlast_name,
                        'card_number' => $card_number,
                        'expiry' => $expiry,
                        'cvc' => $cvc
                    )
                );
            }
            update_user_meta($user_id, 'card_details', $card_data);
            $result = array(
                'code' => 200,
                'message' => "Billing Information has been updated."
            );
        } else {
            $result = array(
                'code' => 202,
                'message' => "All fields are required."
            );
        }
        echo json_encode($result);
        die();
    }

    public function update_billinfo() {
        extract($_REQUEST);
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        update_user_meta($user_id, "_wpinv_first_name", $bill_fnm);
        update_user_meta($user_id, "_wpinv_last_name", $bill_lnm);
        update_user_meta($user_id, "billing_email", $bill_eml);
        update_user_meta($user_id, "_wpinv_address", $billing_address_1);
        update_user_meta($user_id, "_wpinv_zip", $billing_postcode);
        update_user_meta($user_id, "_wpinv_phone", $billing_phone);
        $result = array(
            'code' => 200,
            'message' => "Billing Information has been updated."
        );
        echo json_encode($result);
        die();
    }

    public function updateProfile() {
        /* Get user info. */
        global $current_user, $wp_roles;
        $error = array();
        /* If profile was saved, update profile. */
        if ('POST' == $_SERVER['REQUEST_METHOD'] && !empty($_POST['action']) && $_POST['action'] == 'update-user') {

            try {
                /* Update user password. */
                if (!empty($_POST['pass1']) && !empty($_POST['pass2'])) {
                    if ($_POST['pass1'] == $_POST['pass2']) {
                        wp_update_user(array('ID' => $current_user->ID, 'user_pass' => esc_attr($_POST['pass1'])));
                    } else {
                        $result = array(
                            'code' => 202,
                            'message' => "The passwords you entered do not match.  Your password was not updated."
                        );
                    }
                }
                /* Update user password. */
                if (!empty($_POST['pass1']) && !empty($_POST['pass2'])) {
                    if ($_POST['pass1'] == $_POST['pass2']) {
                        wp_update_user(array('ID' => $current_user->ID, 'user_pass' => esc_attr($_POST['pass1'])));
                    } else {
                        $result = array(
                            'code' => 202,
                            'message' => "The passwords you entered do not match. Your password was not updated."
                        );
                    }
                }
                if (!empty($_POST['email'])) {
                    if (!is_email(esc_attr($_POST['email']))) {
                        $result = array(
                            'code' => 202,
                            'message' => "The Email you entered is not valid.  please try again."
                        );
                    } elseif (email_exists(esc_attr($_POST['email'])) != $current_user->id) {
                        $result = array(
                            'code' => 202,
                            'message' => "This email is already used by another user. Try a different one."
                        );
                    } else {
                        wp_update_user(array('ID' => $current_user->ID, 'user_email' => esc_attr($_POST['email'])));
                    }
                }
                if (!empty($_POST['first-name']))
                    update_user_meta($current_user->ID, 'first_name', esc_attr($_POST['first-name']));

                if (!empty($_POST['last-name']))
                    update_user_meta($current_user->ID, 'last_name', esc_attr($_POST['last-name']));
                if (!empty($_POST['description']))
                    update_user_meta($current_user->ID, 'description', esc_attr($_POST['description']));
                //action hook for plugins and extra fields saving

                $full_nm = $_REQUEST['first-name'] . " " . $_REQUEST['last-name'];
                update_user_meta($current_user->ID, "display_name", $full_nm);

                do_action('edit_user_profile_update', $current_user->ID);

                $result = array(
                    'code' => 200,
                    'message' => "The profile has been updated, Refresh browser for change revert"
                );
            } catch (\Exception $ex) {
                $result = array(
                    'code' => 202,
                    'message' => "Something went wrong!, Please try again later"
                );
            }


            echo json_encode($result);
            die();
        }
    }


    public function updatedivi_license(){
        extract($_REQUEST);
        global $current_user;
      //  if(isset($username) && !empty($username) && isset($apikey) && !empty($apikey)){

            global $wp_version;

            // Prepare settings for API request
            $options = array(
                'timeout'    => 30,
                'body'       => array(
                    'action'   => 'check_hosting_card_status',
                    'username' => $username,
                    'api_key'  => $apikey,
                ),
                'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url( '/' ),
            );

            $request               = wp_remote_post( 'https://www.elegantthemes.com/api/api.php', $options );
            $request_response_code = wp_remote_retrieve_response_code( $request );
            $response_body         = wp_remote_retrieve_body( $request );
            $response = json_decode($response_body,true);


            if(empty($username) || empty($apikey)){
                $result = array(
                        'code' => 200,
                        'message' => "Divi License Updated Successfully"
                    );
            }else{
                if($response['success'] === 1 || $response['success'] === true){
                    update_user_meta($current_user->ID,'divi_username',$username);
                    update_user_meta($current_user->ID,'divi_api_key',$apikey);
                    $result = array(
                        'code' => 200,
                        'message' => "Divi License Activated"
                    );
                }else{
                    $result = array(
                        'code' => 500,
                        'message' => (isset($response['error']))?$response['error']:'Invalid Username OR License Key'
                    );
                }
            }
            

             
        /*}
        else{
             $result = array(
                    'code' => 500,
                    'message' => "All fields are required."
                );
        }*/


        echo json_encode($result);
            die();
    }

    public function builder_profile() {
        /* Get user info. */
        if (is_user_logged_in()) {
            global $current_user, $wp_roles;
            ob_start();
            ?>
            <div class="contentWrapper user_actions" id="table-page">
                <?php require_once 'builder_siderbar.php'; ?>
                <!-- //sidebar  --> 
                <div class="wrapContent">
                    <?php require_once 'builder_topnav.php'; ?> 


                    <!-- Top Nav Bar -->
                    
                    <?php
                    global $wpdb;
                    global $wp_roles;
                    global $ultimatemember;
                    ?>
                    <!-- End Top Nav Bar -->
                    
                    <?php if (isset($_REQUEST['active'])): ?>                    
                        <script>
                            jQuery(function ($) {
                                var coll_active = "<?php echo $_REQUEST['active']; ?>";
                                setTimeout(function () {
                                    $("#" + coll_active).trigger('click');
                                }, 200);
                            });
                        </script>
                    <?php endif; ?>


                    <div class="wrapContainer">		
                        <div class="tabbedPanels profileTabContent">
                            <ul class="tabs">
                                <li><a class="active" href="#panelProfile">Profile Information</a></li>
                                <li><a href="#panelAccount">Billing Information</a></li>
                                <li><a id="card_info_trigger" href="#apikey">API Key</a></li>
                                <!-- <li><a id="card_info_divi" href="#divi_licensekey">Divi License Key</a></li> -->
                            </ul>

                            <div class="panelContainer">
                                <div id="panelProfile" class="panel">
                                    <div class="bloxx_account">							
                                        <form  id="user-profile">
                                            <h4>Account Information</h4>
                                            <div class="rowWrap">
                                                <div class="flex-6">
                                                    <p class="form-username">
                                                        <label for="first-name"><?php _e('First Name', 'profile'); ?></label>
                                                        <input class="text-input regular-text" name="first-name" type="text" id="first-name" value="<?php the_author_meta('first_name', $current_user->ID); ?>"  required/>
                                                    </p><!-- .form-username -->
                                                </div>
                                                <div class="flex-6">
                                                    <p class="form-username">
                                                        <label for="last-name"><?php _e('Last Name', 'profile'); ?></label>
                                                        <input class="text-input regular-text" name="last-name" type="text" id="last-name" value="<?php the_author_meta('last_name', $current_user->ID); ?>" required/>
                                                    </p><!-- .form-username -->
                                                </div>
                                                <div class="flex-6">
                                                    <p class="form-email">
                                                        <label for="email"><?php _e('E-mail *', 'profile'); ?></label>
                                                        <input class="text-input regular-text" type="text" id="email" value="<?php the_author_meta('user_email', $current_user->ID); ?>" readonly="true" style="background: #ddd;border: 1px solid #ccc;"/>
                                                    </p><!-- .form-email -->
                                                </div>
                                                <div class="flex-6">
                                                    <p class="form-password">
                                                        <label for="pass1"><?php _e('Password *', 'profile'); ?> </label>
                                                        <input class="text-input regular-text" name="pass1" type="password" id="pass1"/>
                                                    </p><!-- .form-password -->
                                                </div>
                                                <div class="flex-6">
                                                    <p class="form-password">
                                                        <label for="pass2"><?php _e('Repeat Password *', 'profile'); ?></label>
                                                        <input class="text-input regular-text" name="pass2" type="password" id="pass2"/>
                                                    </p><!-- .form-password -->
                                                </div>

                                                <div class="flex-12">
                                                    <p class="form-textarea">
                                                        <label for="description"><?php _e('Bio', 'profile') ?></label>
                                                        <textarea name="description" id="description" rows="5" cols="30" style="width: 100%;height: 150px;"><?php the_author_meta('description', $current_user->ID); ?></textarea>
                                                    </p><!-- .form-textarea -->
                                                </div>
                                                <div class="flex-12">
                                                    <input type="hidden" name="action" value="update-user">
                                                    <button type="submit" class="default-btn" id="update_info">Save</button>
                                                    <p id="ajax-response" class="alert alert-success hidden"></p>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>


                                <div id="panelAccount" class="panel">
                                    <div class="bloxx_account">							
                                        <form  id="bill_profile">
                                            <h4>Billing Information</h4>
                                            <div class="rowWrap">
                                                <div class="flex-6">
                                                    <p class="form_fnm">
                                                        <label for="bill_fnm"><?php _e('First Name', 'profile'); ?></label>
                                                        <input class="text-input regular-text" name="bill_fnm" type="text" id="bill_fnm" value="<?php echo get_user_meta($current_user->ID, '_wpinv_first_name', true); ?>"  required/>
                                                    </p><!-- .form-username -->
                                                </div>

                                                <div class="flex-6">
                                                    <p class="form_lnm">
                                                        <label for="bill_lnm"><?php _e('Last Name', 'profile'); ?></label>
                                                        <input class="text-input regular-text" name="bill_lnm" type="text" id="bill_lnm" value="<?php echo get_user_meta($current_user->ID, '_wpinv_last_name', true); ?>" required/>
                                                    </p><!-- .form-username -->
                                                </div>

                                                <div class="flex-6">
                                                    <p class="form-email">
                                                        <label for="bill_eml"><?php _e('E-mail *', 'profile'); ?></label>
                                                        <input class="text-input regular-text" name="bill_eml" type="text" id="bill_eml" value="<?php echo get_user_meta($current_user->ID, 'billing_email', true); ?>" required/>
                                                    </p><!-- .form-email -->
                                                </div>


                                                <div class="flex-6">
                                                    <p class="form_company">
                                                        <label for="billing_address_1"><?php _e('Address 1 *', 'profile'); ?> </label>
                                                        <input class="text-input regular-text" name="billing_address_1" type="text" id="billing_address_1" value="<?php echo get_user_meta($current_user->ID, '_wpinv_address', true); ?>"/>
                                                    </p>
                                                </div>


                                                <div class="flex-6">
                                                    <p class="form_company">
                                                        <label for="billing_postcode"><?php _e('Postal Code *', 'profile'); ?> </label>
                                                        <input class="text-input regular-text" name="billing_postcode" type="text" id="billing_postcode" value="<?php echo get_user_meta($current_user->ID, '_wpinv_zip', true); ?>"/>
                                                    </p>
                                                </div>
                                                <div class="flex-6">
                                                    <p class="form_company">
                                                        <label for="billing_phone"><?php _e('Phone *', 'profile'); ?> </label>
                                                        <input class="text-input regular-text" name="billing_phone" type="text" id="billing_phone" value="<?php echo get_user_meta($current_user->ID, '_wpinv_phone', true); ?>"/>
                                                    </p>
                                                </div>
                                                <div class="flex-6">
                                                    <input type="hidden" name="action" value="update_billinfo">
                                                    <button type="submit" class="default-btn" id="update_info">Submit</button>
                                                    <p id="ajax-response" class="alert alert-success hidden"></p>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div id="apikey" class="panel">
                                    <div class="flex-12 dashboard_no mBottom hostingTables">
                                        <?php
                                        $plugins_limit = get_user_meta($current_user_id, 'api_limit', true);
                                        $i = 1;
                                        $bloxx_apis_tb = $wpdb->prefix . 'bloxx_apis';
                                        $api_query = "Select * from $bloxx_apis_tb where user_id='$current_user_id' and prime_key='1' order by id asc";                                            

                                        $api_result = $wpdb->get_results($api_query);

                                        // echo "<pre>";
                                        // print_r($api_result);
                                        // echo "</pre>";

                                        $con_api = $wpdb->get_row($api_query);
                                        $count_api = count($api_result);



                                        $api_total_connected = "Select * from $bloxx_apis_tb where user_id='$current_user_id' and status='1' and is_external='1' order by id asc";


                                        $total_result = $wpdb->get_results($api_total_connected);
                                        $total_conn=count($total_result);
                                        ?>

                                        <h3>API Keys <?php if($count_api==0){ ?><a href="javascript:void(0)" title="Create API Key" class="default-btn create_apis">Create API Key</a><?php } ?></h3>

                                        <div class="table-responsive">
                                            <table>
                                                <tbody>
                                                    <tr class="smallHidden">                                 
                                                        <th>API Key</th>
                                                        <th>Activations</th>
                                                        <th>Limits</th>
                                                        <th>Connected</th>
                                                        <th>Action</th>
                                                    </tr>

                                                    <?php if ($count_api!=0) { 
                                                        $key_id=$con_api->id;
                                                        $website_url= ($con_api->website_url!="") ? $con_api->website_url : '-N/A-';
                                                        $api_keys=$con_api->api_key;
                                                        // $api_keys_format= "<span class='apKey'><span>".$api_keys."</span> <a href='javascript:void(0)' class='copy-btn' id='".$key_id."' data-id='".$api_keys."'><i class='fa fa-copy'></i></a><span class='copyAlert' id='alert_$key_id' style='display:none;'>Copied!</span></span>";


                                                        $api_keys_format= "<span class='apKey'><span>******************************</span> <a href='javascript:void(0)' class='copy-btn' id='".$key_id."' data-id='".$api_keys."'><i class='fa fa-copy'></i></a><span class='copyAlert' id='alert_$key_id' style='display:none;'>Copied!</span></span>";


                                                        $api_created= date('M d, Y', strtotime($con_api->created_at));
                                                        $website_status= ($con_api->status!=2) ? "<span class='connected'><i class='fa fa-check'></i></span>" : "<span class='disconnected'><i class='fas fa-times'></i></span>";
                                                        ?>
                                                        <tr class="users_app" id="app_<?= $key_id; ?>">                 
                                                            <td><span class="desktopHidden">API Key</span><?= $api_keys_format; ?></td>
                                                            <td><?= $total_conn; ?></td>
                                                            <td><?= $plugins_limit; ?></td>  
                                                            <td><span class="desktopHidden">Created</span><?= $api_created; ?></td>
                                                            <td class="user_action_api"><span class="desktopHidden">Action</span><a href="javascript:void(0)" class="regenerate_key" id="<?= $key_id; ?>" title="Re-Generate Key" data-title="regenerate_key"><i class='fas fa-key'></i></a> <a href="javascript:void(0)" class="trash_key" id="<?= $key_id; ?>" title="Re-Generate Key" data-title="trash_key"><i class="fas fa-trash-alt"></i></a></td>
                                                        </tr>                                                
                                                    <?php } else { ?>
                                                        <tr><td colspan="6" style="text-align: center;font-weight: bold;">Create your first API Key</td></tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>   


                                    <div class="the_api_extended_tbl">
                                        <h3>Connected Sites</h3>
                                        <div class="table-responsive">
                                            <table>
                                                <tbody>
                                                    <tr class="smallHidden">                                 
                                                        <th>Sr.</th>
                                                        <th>Domain</th>
                                                        <th>Builder</th>
                                                        <!-- <th>Writr</th>
                                                        <th>Pixr</th> -->
                                                        <th>Action</th>
                                                    </tr>
                                                    <?php 
                                                        global $wpdb;
                                                       
                                                        $current_user = wp_get_current_user();
                                                        $user_id = $current_user->ID;   
                                                        

                                                        //$bloxx_apis_tbl = $wpdb->prefix . 'bloxx_apis';        
                                                        //$api_query = "Select * from $bloxx_apis_tbl where user_id='$user_id' and term_id!='NULL'";
                                                        //$api_rows = $wpdb->get_results($api_query);
                                                       $api_query = "Select * from $bloxx_apis_tb where user_id='$current_user_id' and is_external='1' order by id asc";
                                                       $api_rows = $wpdb->get_results($api_query);

                                                        
                                                        if(count($api_rows) >0){
                                                            $i = 1;
                                                            foreach ($api_rows as $key => $data) {
                                                                $term_id = $data->term_id;
                                                                $site_url = $data->website_url;
                                                                $api_key = $data->api_key;
                                                                $user_id = $data->user_id;
                                                                $term_nm = get_term_meta($term_id,'term_nm',true);

                                                                if (strlen($term_nm) > 25){
                                                                    $appname = substr($term_nm, 0, 22) . '...';
                                                                }else{
                                                                    $appname = $term_nm;
                                                                }

                                                                //$buildr_plugin_status = get_term_meta($term_id, 'buildr_plugin_status',true);
                                                                $buildr_plugin_status = $data->status;
                                                                
                                                                // if($buildr_plugin_status=='active'){
                                                                //     $buildr_plugin_status_final = '<i class="fas fa-check-circle fas_apis_green"></i>';
                                                                // }else{
                                                                //     $buildr_plugin_status_final = '<i class="fas fa-times-circle fas_apis_red"></i>';
                                                                // }

                                                                if($buildr_plugin_status=='1'){
                                                                    $buildr_plugin_status_final = '<i class="fas fa-check-circle fas_apis_green"></i>';
                                                                } else {
                                                                    $buildr_plugin_status_final = '<i class="fas fa-times-circle fas_apis_red"></i>';
                                                                }

                                                                $writr_plugin_status = get_term_meta($term_id, 'writr_plugin_status',true);
                                                                if($writr_plugin_status=='active'){
                                                                    $writr_plugin_status_final = '<i class="fas fa-check-circle fas_apis_green"></i>';
                                                                }else{
                                                                    $writr_plugin_status_final = '<i class="fas fa-times-circle fas_apis_red"></i>';
                                                                }

                                                            ?>
                                                            <tr class="smallHidden">                                 
                                                                <td><?php echo $i; ?></td>
                                                                <td><a href="<?php echo $data->website_url; ?>" target="_blank">
                                                                    <?php echo $site_url; ?></a>
                                                                </td>
                                                                <td><?php echo $buildr_plugin_status_final; ?></td>
                                                                <!-- <td><?php echo $writr_plugin_status_final; ?></td>
                                                                <td><i class="fas fa-times-circle fas_apis_red"></i></td> -->
                                                                <td>
                                                                    <?php 
                                                                        if(isSiteBlocked($api_key, $term_id)){
                                                                            // means site is blocked
                                                                            $action = 'unblock';
                                                                            $btn_text = 'Unblock';
                                                                        }else{
                                                                            $action = 'block';
                                                                            $btn_text = 'Block';
                                                                        }
                                                                    ?>
                                                                    <a class="block_site_button" href="javascript:void(0)" data-site_url="<?php echo $site_url; ?>"  data-term_id="<?php echo $term_id; ?>"  data-user_id="<?php echo $user_id; ?>" data-api_key="<?php echo $api_key; ?>" data-action="<?php echo $action; ?>" data-redirect_url="<?php echo get_site_url(); ?>/bloxx-account/?tab=apikey"><?php echo $btn_text; ?></a>
                                                                </td>
                                                            </tr>
                                                        <?php
                                                                $i++;
                                                            } // end foreach
                                                         }   else{ ?>
                                                            <tr><td colspan="6">No data found</td></tr>
                                                        <?php }                                                
                                                         ?>
                                                </tbody>
                                            </table>
                                    </div> 
                                </div>


                                </div>


                                <div id="divi_licensekey" class="panel">
                                    <div class="flex-12 dashboard_no mBottom ">
                                        <?php
                                        $divi_username = get_user_meta($current_user_id , 'divi_username',true);
                                        $divi_key = get_user_meta($current_user_id,'divi_api_key',true);
                                        ?>
                                        <form  id="divi_licensekey-form">
                                            <h4>Divi License Key</h4>
                                            <div class="rowWrap">
                                                <div class="flex-6">
                                                    <p class="form-username">
                                                        <label for="user-name">Username</label>
                                                        <input class="text-input regular-text" name="username" type="text" id="user-name" value="<?php echo $divi_username; ?>" />
                                                    </p>
                                                </div>
                                                <div class="flex-6">
                                                    <p class="form-username">
                                                        <label for="api-key">Api Key</label>
                                                        <input class="text-input regular-text" name="apikey" type="text" id="api-key" value="<?php echo $divi_key; ?>" />
                                                    </p><!-- .form-username -->
                                                </div>
                                                
                                                <div class="flex-12">
                                                    <input type="hidden" name="action" value="update-divi_license">
                                                    <button type="submit" class="default-btn" id="update-divi_license">Save</button>
                                                    <p id="ajax-response" class="alert alert-success hidden"></p>
                                                </div>
                                            </div>
                                        </form>
                                    </div>                                    
                                </div>

                            
                            </div>
                        </div>
                    </div>	
                    <?php require_once 'builder_footer.php'; ?>
                </div>
            </div>
            </div>
            <?php
            $output .= ob_get_contents();
            ob_end_clean();
            return $output;
            ?>
            <?php
        } else {
            $this->restricate_page_content();
        }
        ?>
        <?php
    }

    public function restricate_page_content() {
        ?>
        <div class="contentWrapper">
            <div class="wrapContent">
                <div class="topWrapmenu">
                    <ul class="builder_back_dashboard">							
                        <li>
                            <a href="<?php echo site_url(); ?>/login">Login</a>
                        </li>
                    </ul>						
                </div>

                <div class="tabWrapcontent builder_create_template">
                    <p class="um-notice warning">You must <a href="<?php echo site_url(); ?>/login">login</a> to access this page</p>			
                </div>
            </div>
        </div>

        <?php
    }

}

$builder_profile = new Builder_profile();
?>