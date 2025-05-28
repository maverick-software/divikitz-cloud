<?php

class Builder_apihandle {

    public function __construct() {
        //Generate Sitebloxx Key
        add_action("wp_ajax_siteblox_key_gen", array($this, "siteblox_key_gen"));
        add_action("wp_ajax_nopriv_siteblox_key_gen", array($this, "siteblox_key_gen"));

        //Verify Generate Key
        add_action("wp_ajax_siteblox_key_verify", array($this, "siteblox_key_verify"));
        add_action("wp_ajax_nopriv_siteblox_key_verify", array($this, "siteblox_key_verify"));

        add_action("wp_ajax_builder_login_request", array($this, "builder_login_request"));
        add_action("wp_ajax_nopriv_builder_login_request", array($this, "builder_login_request"));

        add_action("wp_ajax_builder_sync_request", array($this, "builder_sync_request"));
        add_action("wp_ajax_nopriv_builder_sync_request", array($this, "builder_sync_request"));

        add_action("wp_ajax_builder_sync_disconnect", array($this, "builder_sync_disconnect"));
        add_action("wp_ajax_nopriv_builder_sync_disconnect", array($this, "builder_sync_disconnect"));

        //Globally set Header and Footer
        add_action("wp_ajax_sitebloxsinc_headerfooter", array($this, "sitebloxsinc_headerfooter"));
        add_action("wp_ajax_nopriv_sitebloxsinc_headerfooter", array($this, "sitebloxsinc_headerfooter"));

        //Shop Site Registrartion and Login
        add_action("wp_ajax_api_registerandlogin", array($this, "api_registerandlogin"));
        add_action("wp_ajax_nopriv_api_registerandlogin", array($this, "api_registerandlogin"));


        //Insert Header/ Footer For Project
        add_action("wp_ajax_headfooter_assign", array($this, "headfooter_assign"));
        add_action("wp_ajax_nopriv_headfooter_assign", array($this, "headfooter_assign"));

        add_action('rest_api_init', array($this, 'pageinsert_api'));

        add_action('rest_api_init', array($this, 'neo_assets_save'));

        add_action('rest_api_init', array($this, 'neo_assets_cats'));
       
    }

    //GET API from Reference website for Save user layout and secion into their library

    function neo_assets_cats(){
        @register_rest_route(
            'neo_directory', '/assets_cats/', array(
                'methods' => 'POST',
                'callback' => array($this, 'neo_cloud_cats'),
            )
        );
    }


    public function neo_cloud_cats($request){
        $neo_type=$request['neo_type'];
        $user_id=$request['user_id'];

        //if($neo_type=="layout"){
        $result= get_industry_and_cats_api($user_id);

        // echo "<pre>";
        // print_r($result);
        // echo "</pre>";
        echo json_encode($result);
        die();
        //}
    }



    //insert_page_hooks
    function neo_assets_save() {
        @register_rest_route(
            'neo_directory', '/save_assets/', array(
                'methods' => 'POST',
                'callback' => array($this, 'neo_save_cloud'),
            )
        );
    }


    function neo_save_cloud($request){
        $user_email=$request['user_email'];
        $user_id=$request['user_id'];
        $neo_content=$request['neo_content'];
        $neo_type=$request['neo_type'];

        if($neo_type=="layout"){

            $neo_title=$request['page_title'];
            $neo_catID=$request['neo_catID'];
            $neo_indID=$request['neo_indID'];

            $ajax_content = "";
            foreach ($neo_content as $drop_content):
                $ajax_content .= $drop_content;                
            endforeach;

            $remove_farward_slash = str_replace('\\', '', $ajax_content);


            $neo_cloud_user = array(
                'post_title' => $neo_title,
                'post_author' => $user_id,
                'post_content' => $remove_farward_slash,
                'post_status' => 'publish',
                'post_type' => 'layouts'
            );
            $pid= wp_insert_post($neo_cloud_user);
            
            wp_set_object_terms($pid, intval($neo_catID), 'bloxx_categories');
            wp_set_object_terms($pid, intval($neo_indID), 'service_type');
            
        } else {
            $neo_title=$request['page_title'];
            $neo_catID=$request['neo_catID'];
            

            $remove_farward_slash = str_replace('\\', '', $neo_content);

            $neo_cloud_user = array(
                'post_title' => $neo_title,
                'post_author' => $user_id,
                'post_content' => $remove_farward_slash,
                'post_status' => 'publish',
                'post_type' => 'project'
            );
            $pid= wp_insert_post($neo_cloud_user);
            wp_set_object_terms($pid, intval($neo_catID), 'project_category');
            
            $result = array(
                'code' => 200,
                'attachment' => 'Success',
                'message' => 'Your section has been saved successfully'
            );
        }


        //Content HEre

        update_post_meta($pid, '_et_pb_use_builder', "on");
        update_post_meta($pid, '_et_pb_page_layout', 'et_no_sidebar');
        update_post_meta($pid,'premium_section',0);
        update_post_meta($pid, 'builder_custom_cat_user', $user_id);
        update_post_meta($pid,'_et_pb_post_hide_nav','default');
        update_post_meta($pid,'_et_pb_project_nav','off');
        update_post_meta($pid,'_et_pb_side_nav','off');

        //Screenshot_capture
        $link_url = get_the_permalink($pid);
        $shot_nm = "layout_$pid.png";
        $version = $link_url . "?screenshot=" . time();
        
        $scriptpath = "node " . siteblox_path . "/builder_nodescript.js {$version} {$shot_nm}";                    
        exec($scriptpath, $output);

        $myJSON = $output;
        $pepe = implode($myJSON);
        
        if ($pepe == "screenshot_captured") {
            //Set Feature image
            $feature_nm=$shot_nm;
            $feature_image= siteblox_path."/project_shots/".$feature_nm;                
            $siteblox_core = new Siteblox_core();
            $attachment_id = $siteblox_core->upload_feature_image($feature_image, $feature_nm);

            set_post_thumbnail( $pid, $attachment_id );

            $result = array(
                'code' => 200,
                'project_id' => $pid,
                'attachment' => 'Success',
                'message' => 'Your Layout has been saved'
            );
            
        } else {
            $result = array(
                'code' => 202,
                'project_id' => $pid,
                'attachment' => 'Failed',
                'message' => 'Your Layout has been saved but due to some issue, You need to assign image manually to this layout.'
            );
        }

        return $result;
    }



    //insert_page_hooks
    function pageinsert_api() {
        @register_rest_route(
            'bloxx-page', '/insert/', array(
                'methods' => 'POST',
                'callback' => array($this, 'syn_insert_page'),
            )
        );
    }

    function syn_insert_page($request) {
        $page_content = $request['project_content'];
        $page_name = $request["server_title"];
        $page_id = $request['server_page_id'];
        $term_id= $request['bloxx_term'];
        $user_id= $request['bloxx_user'];

        $create_page = array(
            'server_pageid' => $page_id,
            'post_title' => $page_name,
            'post_status' => 'publish',
            'post_author' => $user_id,
            'post_type' => 'customer_templates'
        );

        $pid = wp_insert_post($create_page);   
        wp_set_object_terms($pid, intval($term_id), 'project_categories');     
        update_post_meta($pid, '_et_pb_page_layout', 'et_no_sidebar');
        update_post_meta($pid, '_et_pb_use_builder', 'on');
        update_post_meta($pid, '_et_pb_use_builder', 'on');
        update_post_meta($pid, 'template_user_cats', $term_id);
        update_post_meta($pid, 'template_user', $user_id);
        update_post_meta($pid, 'server_page_id', $page_id);
        update_post_meta($pid, 'server_page_url', '');

        $result = array(
            "code" => 200,
            "message" => "Page Created Successfully"                       
        );

        echo json_encode($result);
    }


    public function headfooter_assign(){

        extract($_REQUEST);
        global $wpdb;

        $current_user = wp_get_current_user();
        $currentuser_id= $current_user->ID;
        $make_global=2;
        $headerfooter = $wpdb->prefix.'header_footer';

        //Update Term Meta for assign header and footer

        if($assign_type=="assign_header"){            
            update_term_meta($term_id, 'project_header', $header_id);
            $assign_nm= get_the_title($header_id);
            $resp_msg="Selected header assigned successfully for all pages of this project";


            //Sync Header with below function by API
            $hedaer_content = get_post($header_id);
            $project_content = $hedaer_content->post_content;
            $assign_title = $hedaer_content->post_title;
            $assign_slug = strtolower(str_replace(" ", '-', $assign_title));
            
            
            $result= $this->synchead_footer($term_id, $assign_title, $assign_slug, $project_content, $assign_type, $page_id);

            $response=json_decode($result, true);            

        } else {
            update_term_meta($term_id, 'project_footer', $footer_id);
            $assign_nm= get_the_title($footer_id);
            $resp_msg="Selected footer assigned successfully for all pages of this project";

            //Sync Footer with below function BY API

            $footer_content = get_post($footer_id);
            $project_content = $footer_content->post_content;
            $assign_title = $footer_content->post_title;
            $assign_slug = strtolower(str_replace(" ", '-', $assign_title));
            $this->synchead_footer($term_id, $assign_title, $assign_slug, $project_content, $assign_type, $page_id);
        }
        $result=array(
            'code' => 200,
            'message' => $resp_msg,
            'assign_nm' => $assign_nm
        );
        echo json_encode($result);
        die();
    }


    public function sitebloxsinc_headerfooter() {
        extract($_REQUEST);
        
        if ($assign_type == "assign_header") {
            //Header Content
            $header_id = get_term_meta($pcat, 'project_header', true);
            $hedaer_content = get_post($header_id);
            $project_content = $hedaer_content->post_content;
            $assign_title = $hedaer_content->post_title;
            $assign_slug = strtolower(str_replace(" ", '-', $assign_title));
            $this->synchead_footer($pcat, $assign_title, $assign_slug, $project_content, $assign_type, $page_id);
        } else if ($assign_type == "assign_footer") {
            //Footer Content
            $footer_id = get_term_meta($pcat, 'project_footer', true);
            $footer_content = get_post($footer_id);
            $project_content = $footer_content->post_content;
            $assign_title = $footer_content->post_title;
            $assign_slug = strtolower(str_replace(" ", '-', $assign_title));
            $this->synchead_footer($pcat, $assign_title, $assign_slug, $project_content, $assign_type, $page_id);
        } else {
            $result = array(
                'code' => 202,
                'message' => "Failed to sync, try again later"
            );
            echo json_encode($result);
            die();
        }
    }

    public function synchead_footer($pcat, $assign_title, $assign_slug, $project_content, $assign_type, $page_id) {
        global $wpdb;
        $server_page_id=get_post_meta($page_id, 'server_page_id', true);
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $conn_site = $wpdb->prefix . 'connected_sites';
        $project_query = "SELECT * FROM $conn_site where siteblox_user_id='$user_id' and `is_connect`='1' and siteblox_termid='$pcat' order by id desc limit 1";
        $connected_sites = $wpdb->get_results($project_query);
        $count_connected = count($connected_sites);

        if ($count_connected == 1) {
            $user_data = $wpdb->get_row($project_query);
            $user_id = $user_data->user_id;
            $server_site_url = $user_data->site_url;
            $send_array = array(
                'post_content' => $project_content,
                'post_title' => $assign_title,
                'post_slug' => $assign_slug,
                'user_id' => $user_id,
                'server_pageid'=> $server_page_id,
                'type' => $assign_type
            );
            $send_data = json_encode($send_array);
            $curl_url = $server_site_url . "/wp-json/globally/header_footer";
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
                CURLOPT_POSTFIELDS => $send_data,
                CURLOPT_HTTPHEADER => array("content-type: application/json"),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            echo $response;
            die();
        } else {
            $result = array(
                'code' => 202,
                'message' => "Failed to sync, try again later"
            );
            echo json_encode($result);
            die();
        }
    }

    //Shop Site API
    public function api_registerandlogin() {
        global $wpdb;
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $user_email = $current_user->user_email;
        $user_login = $current_user->user_login;
        $user_pass = $current_user->user_pass;
        $nick_name = get_user_meta($user_id, 'nickname', true);
        $first_name = get_user_meta($user_id, 'first_name', true);
        $last_name = get_user_meta($user_id, 'last_name', true);
        $description = get_user_meta($user_id, 'description', true);
        $account_status = get_user_meta($user_id, 'account_status', true);
        $full_name = get_user_meta($user_id, 'full_name', true);

        $register_array = array(
            'user_email' => $user_email,
            'user_login' => $user_login,
            'user_pass' => $user_pass,
            'nick_name' => $nick_name,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'description' => $description,
            'account_status' => $account_status,
            'full_name' => $full_name
        );

        $send_api = json_encode($register_array);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://sitebloxx.com/wp-json/shop-auth/register",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $send_api,
            CURLOPT_HTTPHEADER => array("content-type: application/json"),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        echo $response;
        die();
    }

    public function siteblox_key_verify() {
        extract($_REQUEST);
        global $wpdb;
        $conn_site = $wpdb->prefix . 'connected_sites';
        $query_data = "select * from $conn_site where siteblox_key='$siteblox_key' and is_connect='1'";
        $my_query = $wpdb->get_results($query_data);
        $count_data = count($my_query);
        if ($count_data == 1) {
            $result = array(
                'code' => 200,
                'message' => "Your API Key Verified, Now you can sync directly"
            );
        } else {
            $result = array(
                'code' => 202,
                'message' => "Failed to verify, Please copy and paste generated key in your plugin dashboard and click on connect button."
            );
        }
        echo json_encode($result);
        die();
    }

    public function siteblox_key_gen($term_connect = null) {
        extract($_REQUEST);
        global $wpdb;
        $current_user = wp_get_current_user();
        $currentuser_id = $current_user->ID;

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < 10; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        $micro_time = round(microtime(true));

        $sitebox_key = $randomString . "==" . $micro_time;

        $conn_site = $wpdb->prefix . 'connected_sites';
        $data = array(
            'siteblox_key' => $sitebox_key,
            'siteblox_user_id' => $currentuser_id,
            'siteblox_termid' => $term_connect
        );
        $id = $wpdb->insert($conn_site, $data);

        $result = array(
            'code' => 200,
            'sitebox_key' => $sitebox_key,
            'message' => "$sitebox_key API key generated successfully"
        );
        echo json_encode($result);
        die();
    }

    public function builder_sync_disconnect() {
        extract($_REQUEST);
        global $wpdb;
        $conn_site = $wpdb->prefix . 'connected_sites';
        extract($_REQUEST);
        $data = array(
            'is_connect' => 2
        );
        $wpdb->update($conn_site, $data, array('siteblox_termid' => $project_id));
        $result = array(
            'code' => 200,
            'message' => "Connection has been disconnect successfully"
        );
        echo json_encode($result);
        die();
    }

    public function sync_request_saved($data) {
        global $wpdb;
        $conn_site = $wpdb->prefix . 'connected_sites';
        $search_item = $data['site_url'];
        $my_query = $wpdb->get_results("select * from $conn_site where site_url like '%$search_item%'");
        $count_data = count($my_query);
        if ($count_data == 0) {
            $id = $wpdb->insert($conn_site, $data);
        } else {
            $id = $my_query[0]->id;
            $wpdb->update($conn_site, $data, array('id' => $id));
        }
        return $id;
    }

    public function builder_login_request() {
        if (is_user_logged_in()) {
            $builder_userurl = strip_tags(htmlspecialchars($_REQUEST['builder_userurl']));
            $builder_username = strip_tags(htmlspecialchars($_REQUEST['builder_username']));
            $builder_userpassword = strip_tags(htmlspecialchars($_REQUEST['builder_userpassword']));
            $term_id = $_REQUEST['term_id'];

            $curl_url = $builder_userurl . "/wp-json/builder-auth/login";

            $auth_array = array(
                "username" => $builder_username,
                "password" => $builder_userpassword
            );

            // insert connection detail into database
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
            $date = date('Y-m-d H:i:s');
            $site_data = array(
                "site_url" => $builder_userurl,
                "username" => $builder_username,
                "password" => $builder_userpassword,
                "status" => 'Connected',
                'user_id' => $user_id,
                'term_id' => $term_id,
                "date" => $date
            );


            $builder_auth_json = json_encode($auth_array);

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $curl_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $builder_auth_json,
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache",
                    "content-type: application/json"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);


            $builder_auth = json_decode($response, true);

            if (isset($builder_auth) && !empty($builder_auth)) {
                $insert_data = $this->sync_request_saved($site_data);
                $server_userid = $builder_auth['data']['ID'];
                $server_usernm = $builder_auth['data']['display_name'];

                $builder_userweb_cred = array(
                    'builder_site_url' => $builder_userurl,
                    'builder_username' => $builder_username,
                    'builder_userpass' => $builder_userpassword
                );
                $current_user = wp_get_current_user();
                $user_id = $current_user->ID;
                update_user_meta($user_id, 'builder_userweb_cred', $builder_userweb_cred);


                $result = array(
                    'code' => 200,
                    'server_userid' => $server_userid,
                    'server_usernm' => ucfirst($server_usernm),
                    'message' => "User Auth Successfully"
                );
            } else {
                $result = array(
                    'code' => 202,
                    'message' => "Username or password invalid, Please try with valid detail"
                );
            }
        } else {
            $login_url = site_url() . '/login';
            $result = array(
                'code' => 202,
                'message' => "Please <a href='$login_url'>Login</a> to complete this action"
            );
        }

        echo json_encode($result);
        die();
    }

    public function builder_sync_request() {
        global $wpdb;
        extract($_REQUEST);

        //Sync Content
        $project_content = "";

        $header_assign = get_term_meta($pcat, 'project_header', true);
        $footer_assign = get_term_meta($pcat, 'project_footer', true);

        //Header Content
        /* if($header_assign!=""){
          $hedaer_content = get_post($header_assign);
          $project_content .= $hedaer_content->post_content;
          } */


        //Body Content
        $content_post = get_post($project_id);
        $project_content .= $content_post->post_content;
        $project_title = $content_post->post_title;
        $project_slug = strtolower(str_replace(" ", '-', $project_title));


        //Footer Content
        /* if($footer_assign!=""){
          $footer_content = get_post($footer_assign);
          $project_content .= $footer_content->post_content;
          } */

        //End Sync Content
        //Current User		
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $conn_site = $wpdb->prefix . 'connected_sites';
        $project_query = "SELECT * FROM $conn_site where siteblox_user_id='$user_id' and `is_connect`='1' order by id desc limit 1";


        $connected_sites = $wpdb->get_results($project_query);
        $count_connected = count($connected_sites);

        if ($count_connected == 1) {
            $con_details = $wpdb->get_row($project_query);

            $curl_url = $con_details->site_url . "/wp-json/builder-page/sync_page";

            $builder_page_array = array(
                'project_title' => $project_title,
                'project_content' => $project_content,
                'server_userid' => $server_userid,
                'project_slug' => $project_slug
            );

            $builder_create_json = json_encode($builder_page_array);

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $curl_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $builder_create_json,
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
                $curl_decode = json_decode($response, true);

                $result = array(
                    'code' => 200,
                    'message' => $project_title . " " . $curl_decode['message']
                );
            }
        } else {
            $result = array(
                'code' => 202,
                'message' => "Your connection has been lost, Please try again"
            );
        }
        echo json_encode($result);
        die();
    }

}

$builder_apihandle = new Builder_apihandle();
