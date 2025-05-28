<?php

class Builder_dashboard {

    public function __construct() {
        add_shortcode('builder_dashboard', array($this, 'builder_dashboard'));


        //Starred Project Action
        add_action("wp_ajax_builder_starred", array($this, "builder_starred"));
        add_action("wp_ajax_nopriv_builder_starred", array($this, "builder_starred"));


        //Starred Project Action
        add_action("wp_ajax_builder_projectdpl", array($this, "builder_projectdpl"));
        add_action("wp_ajax_nopriv_builder_projectdpl", array($this, "builder_projectdpl"));

        //Starred Project Action
        add_action("wp_ajax_project_rename_ajax", array($this, "project_rename_ajax"));
        add_action("wp_ajax_nopriv_project_rename_ajax", array($this, "project_rename_ajax"));
    }

    public function project_rename_ajax() {
        global $wpdb;
        extract($_REQUEST);
        
        $current_user = wp_get_current_user();
        $current_user_id = $current_user->ID;
        $conn_site = $wpdb->prefix . 'connected_sites';
        $project_query = "SELECT * FROM $conn_site where siteblox_user_id='$current_user_id' and `is_connect`='1' and siteblox_termid='$rn_id' order by id desc limit 1";

        $connected_sites = $wpdb->get_results($project_query);
        $count_connected = count($connected_sites);
        if ($count_connected == 1) {

            //API Create Page
            $con_details = $wpdb->get_row($project_query);
            $curl_url = $con_details->site_url . "/wp-json/bloginfo/update";

            $blog_array = array('server_bloginfo' => $rn_nm);
            $blog_arr_json = json_encode($blog_array);

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $curl_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $blog_arr_json,
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache",
                    "content-type: application/json"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                $result = array(
                    'code' => 202,
                    'message' => $err
                );
            } else {
                $cid = wp_update_term($rn_id, 'project_categories', array(
                    'name' => $rn_nm,
                ));               
                
                $result = array(
                    'code' => 200,
                    'message' => "Project name successfully changed"
                );
            }
        } else {
            $result = array(
                'code' => 202,
                'message' => 'Failed to connect reference website'
            );
        }
        echo json_encode($result);
        die();
    }

    public function builder_projectdpl() {
        extract($_REQUEST);
        $user = wp_get_current_user();
        $ran_number = rand(1, 100);
        $project_catnm = "$dpl_nm copy $ran_number";
        $project_catnm = strip_tags(htmlspecialchars($project_catnm));

        //Meta fields
        $builder_cat_user = get_term_meta($dpl_id, 'builder_cat_user', true);
        $project_header = get_term_meta($dpl_id, 'project_header', true);
        $project_footer = get_term_meta($dpl_id, 'project_footer', true);
        $term_image = get_term_meta($dpl_id, 'term_image', true);
        $starred_projects = get_term_meta($dpl_id, 'starred_projects', true);

        $cid = wp_insert_term($project_catnm, 'project_categories', array(
            'description' => '',
        ));
        if (is_wp_error($cid)) {
            $error_message = $cid->get_error_message();
            $result = array(
                'code' => 202,
                'message' => $error_message
            );
        } else {
            $new_term_id = $cid['term_taxonomy_id'];
            update_term_meta($new_term_id, "builder_cat_user", $builder_cat_user);
            update_term_meta($new_term_id, "project_header", $project_header);
            update_term_meta($new_term_id, "project_footer", $project_footer);
            update_term_meta($new_term_id, "term_image", $term_image);
            update_term_meta($new_term_id, "starred_projects", $starred_projects);

            $args = array(
                'post_type' => 'customer_templates',
                'order' => 'asc',
                'posts_per_page' => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'project_categories',
                        'field' => 'term_id',
                        'terms' => $dpl_id,
                        'compare' => '='
                    )
                )
            );
            $query = new WP_Query($args);
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $dpl_post_title = get_the_title();
                    $dpl_post_content = get_the_content();

                    $duplicate_posts = array(
                        'post_content' => $dpl_post_content,
                        'post_title' => $dpl_post_title,
                        'post_status' => 'publish',
                        'post_author' => $user->ID,
                        'post_type' => 'customer_templates'
                    );
                    $pid = wp_insert_post($duplicate_posts);
                    wp_set_object_terms($pid, intval($new_term_id), 'project_categories');
                    update_post_meta($pid, '_et_pb_use_builder', "on");
                    update_post_meta($pid, '_et_pb_page_layout', 'et_no_sidebar');
                }

                $result = array(
                    'code' => 200,
                    'dpl_projnm' => $project_catnm,
                    'message' => "Duplicate Project ($project_catnm) created successfully, also its pages"
                );
            } else {
                $result = array(
                    'code' => 200,
                    'dpl_projnm' => $project_catnm,
                    'message' => "Duplicate Project ($project_catnm) created successfully"
                );
            }
        }
        echo json_encode($result);
        die();
    }

    public function builder_starred() {
        extract($_REQUEST);
        $post_title = get_the_title($star_id);

        if ($star_type == "removed") {
            if (get_term_meta($star_id, 'starred_projects', true)) {
                update_term_meta($star_id, 'starred_projects', "no");
                $result = array(
                    'code' => 200,
                    'message' => "$post_title removed from starred successfully"
                );
            } else {
                $result = array(
                    'code' => 202,
                    'message' => 'Failed to removed from starred'
                );
            }
        } else {
            update_term_meta($star_id, "starred_projects", 1);
            $result = array(
                'code' => 200,
                'message' => "$post_title added to starred successfully"
            );
        }

        echo json_encode($result);
        die();
    }

    public function builder_dashboard() {
        if (is_user_logged_in() && !(is_admin())) {
            global $current_user, $wp_roles, $wpdb;
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
            $user_role = get_user_role_name($user_id);

            

            $current_plan= $plan_title = get_user_meta($user_id, 'current_plan_selected', true);
            $current_plan_id= get_user_meta($user_id, 'current_plan', true);
            


            $is_purchase_plan= get_user_meta($user_id, 'is_purchase_plan', true);

            if($current_plan==""){
                $current_plan= $plan_title ="Free";
            }

            if($is_purchase_plan!="no"){
                $subs_id = get_user_meta($user_id, 'subs_id', true);
                $subsc_tb = $wpdb->prefix . 'yith_ywsbs_order_lookup';
                $sub_query = "select * from $subsc_tb where subscription_id='$subs_id'";
                $sub_result = $wpdb->get_row($sub_query);
                @$subs_id = $sub_result->subscription_id;


                $subsc_tb_state = $wpdb->prefix . 'yith_ywsbs_stats';
                $sub_query_state = "select * from $subsc_tb_state where subscription_id='$subs_id'";
                $sub_result_state = $wpdb->get_row($sub_query_state);
                @$next_payment_due_date = date("M d, Y", $sub_result_state->next_payment_due_date);
                @$total_payment= number_format($sub_result_state->total, 2, '.', ' ');
            }


            

            //Billing Information
            $bill_fnm = get_user_meta($user_id, '_wpinv_first_name', true);
            $bill_lnm = get_user_meta($user_id, '_wpinv_last_name', true);

            
            $bill_add = get_user_meta($user_id, '_wpinv_address', true);
            $bill_city = get_user_meta($user_id, '_wpinv_city', true);
            $bill_state = get_user_meta($user_id, '_wpinv_state', true);
            $bill_cnty = get_user_meta($user_id, '_wpinv_country', true);
            $bill_zip = get_user_meta($user_id, '_wpinv_zip', true);
            $bill_fullnm = $bill_fnm . " " . $bill_lnm;
            $bill_address = $bill_add;
            $plugins_limit = get_user_meta($user_id, 'plugins_limit', true);



            $bloxx_apis_tb = $wpdb->prefix . 'bloxx_apis';
            $api_query = "Select * from $bloxx_apis_tb where user_id='$user_id' order by id asc";
            $api_result = $wpdb->get_results($api_query);
            $con_api = $wpdb->get_row($api_query);
            $count_api = count($api_result);

            if ($count_api!=0) {
                $key_id=$con_api->id;
                $api_keys=$con_api->api_key;
                $api_keys_format= "<span class='apKey'><span>*******</span> <a href='javascript:void(0)' class='copy-btn' id='".$key_id."' data-id='".$api_keys."'><i class='fa fa-copy'></i></a><span class='copyAlert' id='alert_$key_id' style='display:none;'>Copied!</span></span>";
            } else {
                $api_keys_format= "-N/A-";
            }
            ?>


            <!-- Dashboard Content Area -->

            <div class="contentWrapper user_actions">
                <!-- //sidebar  --> 
                <?php require_once 'builder_siderbar.php'; ?>

                <div class="wrapContent">
                    <!-- //Top Bar  --> 
                    <?php require_once 'builder_topnav.php'; ?>

                    <div class="tabWrapcontent-main">
                        <div class="tabWrapcontent">							
                            <?php
                            if (!get_user_meta($user_id, 'site_guide', true)){
                                update_user_meta($user_id, 'site_guide', 1);
                            }
                            ?>

                            <!-- Inner Main Content  -->
                            <div class="builder_template_section dashboardPage" id="tab_planinfo" style="display: block;">
                                <?php do_action('notification_text'); ?>
                                <h2>
                                    <img width="190" height="190" alt="<?= $display_nm; ?>" data-default="<?= $avatar_uri; ?>" data-src="<?= $avatar_uri; ?>" class="gravatar avatar avatar-190 um-avatar um-avatar-default lazyloaded" src="<?= $avatar_uri; ?>">
                                    <strong>Hi, </strong> <?= ucfirst((!empty($display_nm))?$display_nm:$nickname); ?>
                                </h2>



                                <!-- Dashboard Project Counts Data -->
                                <div class="rowWrap">
                                    <div class="flex-6 dashboard_no">
                                        <div class="box bg-white p-2 accountInfo">
                                            <h4>Account Summary <a href="<?= site_url() . '/bloxx-account/'; ?>" class="editicon"><i class="fas fa-pencil-alt"></i></a></h4>			
                                            <ul>
                                                <li>Name 
                                                    <span>
                                                        <?php 
                                                        if(!empty($bill_fullnm) && trim($bill_fullnm) != ''){

                                                            echo $bill_fullnm; 
                                                        } else {
                                                            echo (!empty($display_nm))?$display_nm:$nickname; 
                                                        }
                                                        

                                                        $user_credit = get_user_meta(get_current_user_id(),'writesonic_credit',true);//print_r($user_credit);
                                                        if(empty($user_credit)){
                                                            $user_credit = 0;
                                                        }
                                                        ?>
                                                 
                                                    </span>
                                                </li>


                                                <li>API Key <span style="max-width: 250px;text-overflow: ellipsis;overflow: hidden;white-space: nowrap;"><?= $api_keys_format; ?></span></li>
                                                <li>Email <span><?= $current_user_email ?></span></li>
                                               
                                                <!--<li class="thecoin_img">Power Crystals:  <span><a href="/writer" target="_blank">
                                                <img src="<?php echo builder_url; ?>images/bloxx_coins.png" alt=""><?= $user_credit;?></a></span></li>-->
                                                <!-- <li> <span></span></li> -->
                                            </ul>
                                            
                                        </div>
                                    </div>


                                    <div class="flex-6 dashboard_no">
                                        <div class="box bg-white p-2 accountInfo">
                                            <h4>Billing Information <a href="<?= site_url() . '/bloxx-account/'; ?>" class="editicon"><i class="fas fa-pencil-alt"></i></a></h4>
                                            <ul>
                                                <?php if($user_role=="Administrator"){ ?>
                                                    <li>Plans <span><a style="margin-right: 5px;" href="<?php echo site_url('plans/'); ?>" type="button" class="default-btn btn-pink"><i class="fa fa-user" style="margin-right: 0.2rem;"></i> <?= $plan_title; ?></a> <a class="colorTeal" href="<?php echo site_url('active-plan/'); ?>">View All</a>
                                                </span> </li>

                                                    <li>Monthly Recurring Total <span class="colorTeal">$<?php echo $total_payment; ?></span></li>
                                                    
                                                    <li>Next Payment Date <span class="colorLight">-N/A-</span></li>

                                                    <li>History <span class="colorLight"> <a class="colorTeal" href="<?php echo site_url('billing/'); ?>">View All</a></span></li>

                                                <?php } else { ?>

                                                    <?php if ($is_purchase_plan != "no") { ?>
                                                        <li>Plans <span><a style="margin-right: 5px;" href="<?php echo site_url('plans/'); ?>" type="button" class="default-btn btn-pink"><i class="fa fa-user" style="margin-right: 0.2rem;"></i> <?= $plan_title; ?></a> <a class="colorTeal" href="<?php echo site_url('active-plan/'); ?>">View All</a>
                                                    </span> </li>

                                                        <li>Monthly Recurring Total <span class="colorTeal">$<?php echo $total_payment; ?></span></li>
                                                        
                                                        <li>Next Payment Date <span class="colorLight"><?php echo $next_payment_due_date ?></span></li>

                                                        <li>History <span class="colorLight"> <a class="colorTeal" href="<?php echo site_url('billing/'); ?>">View All</a></span></li>
                                                    <?php } else { ?>

                                                        <?php
                                                        $trial_days= get_field('trial_days', $current_plan_id);
                                                        $trial_text= get_field('trial_text', $current_plan_id);
                                                        $user_register_date= get_the_author_meta( 'user_registered', $user_id );
                                                        $expire_date = date('M d, Y H:i:s', strtotime($user_register_date . $trial_days.' day'));

                                                        $pending_days= ceil(abs(strtotime($expire_date) - strtotime($user_register_date)) / 86400);
                                                        ?>

                                                        <li>Plans <a class="colorTeal" href="<?php echo site_url('plans/'); ?>">Free</a></li>

                                                        <li>Trial <span class="colorLight"><?= $trial_days." ". $trial_text; ?></span></li>

                                                        <li>Trial Expire On <span class="colorLight"><?php echo $expire_date; ?></span></li>
                                                        <li>Trial Days <span class="colorLight"><?php echo $pending_days; ?> Days Left</span></li>
                                                    <?php } ?>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <!-- End Inner Main Content -->
                        </div>
                    </div>





                </div>

                <?php require_once 'builder_footer.php'; ?>
            </div>
            <!-- End Dashboard Content Area -->	

            <?php

        } else {
            restricate_page_content();
        }
    }

}

$builder_dashboard = new Builder_dashboard();