<?php

class Builderapi {

    public function __construct() {
        add_action('rest_api_init', array($this, 'siteblox_dashboard'));


        add_action('rest_api_init', array($this, 'siteblox_register_user'));

        add_action('rest_api_init', array($this, 'siteblox_librarysync_api'));
        add_action('rest_api_init', array($this, 'siteblox_connect_api'));
        
        add_action('rest_api_init', array($this, 'dropbox_connect_api'));
        
        add_action('rest_api_init', array($this, 'siteblox_check_connection'));
        add_action('rest_api_init', array($this, 'siteblox_disconnect_api'));
        add_action('rest_api_init', array($this, 'siteblox_check_update'));


        // For simple authentication
        add_action('rest_api_init', array($this, 'siteblox_check_bloxxaccount'));
       // add_action('rest_api_init', array($this, 'siteblox_connect_api_simple'));
       // add_action('rest_api_init', array($this, 'siteblox_disconnect_api_simple'));


        // insert non bloxx user
        add_action('rest_api_init', array($this, 'siteblox_insert_nonbloxxuser'));
        add_action('rest_api_init', array($this, 'siteblox_usertype_api'));


        // bloxx plugin update status
        add_action('rest_api_init', array($this, 'bloxx_update_plugin_status_api'));
    }

    // function check if user is paid or free based on user id, api key and api username

    
    // update bloxx plugin status
        function bloxx_update_plugin_status_api() {
            @register_rest_route(
                'siteblox-api', '/bloxx_update_plugin_status/', array(
                    'methods' => 'POST',
                    'callback' => array($this, 'bloxx_update_plugin_status_api_callback'),
                )
            );
        }


         function bloxx_update_plugin_status_api_callback($request) {
            $bloxx_term_id = $request['bloxx_term_id'];
            $plugin_status = $request['plugin_status'];
            $plugin_name = $request['plugin_name'];

//pre($request);
            update_term_meta($bloxx_term_id,$plugin_name.'_plugin_status',$plugin_status);
            

           // die('stop host api');
           
            $result = array(
                "code" => 200,
                "bloxx_term_id" => $bloxx_term_id,
                "message" => "Plugin ".$plugin_status                       
            );
     
            echo json_encode($result);
            die();
        }

    // end

    // insert user in divibloxx code starts (from third party domain)
     function siteblox_usertype_api() {
        @register_rest_route(
            'bloxx-user', '/check_usertype/', array(
                'methods' => 'POST',
                'callback' => array($this, 'siteblox_usertype_api_callback'),
            )
        );
    }


     function siteblox_usertype_api_callback($request) {
        $get_user_email = $request['user_email'];

        $user = get_user_by('email', $get_user_email);
        $get_user_id = $user->ID; // will get api_username(username) thru sitebloxx userid

        
        $section_id= $request['section_id'];
        $siteblox_key = $request['builder_key'];
        $website_url = $request['website_url'];

         $premium_section = get_post_meta($section_id,'premium_section',true);

         $siteblox_check_usertype_byapi = siteblox_check_usertype_byapi($get_user_id,$website_url,$siteblox_key);
        // echo '<pre>';
        // print_r($request);
        // echo '</pre>';

      // echo 'in host';
        //echo $siteblox_check_usertype_byapi;
     //  die('stop host 65'); 

        $current_plan = get_user_meta($get_user_id, 'current_plan',true); // check user current_plan 
        //if((isset($current_plan) && $current_plan=='75795') || $siteblox_check_usertype_byapi==0){
        if($siteblox_check_usertype_byapi==0){
            $usertype = 'free';
        }else{
            $usertype = 'paid';
        }


        // 1 is Free
        // 2 is Premium
        if (isset($premium_section) && $premium_section =="1") { 
            $sectiontype = 'Free';
        }else{
            $sectiontype = 'Premium';
        }

       // die('stop host api');
       
        $result = array(
            "code" => 200,
            "usertype"=> $usertype,
            "sectiontype"=>$sectiontype,
            "message" => "Connected Successfully"                       
        );
 
        return  $usertype;
      //  echo json_encode($result);
    }


    public function siteblox_insert_nonbloxxuser() {
        @register_rest_route(
            'siteblox-api', '/insert_nonbloxxuser/', array(
                    'methods' => 'POST',
                    'callback' => array($this, 'insert_nonbloxxuser_callback'),
            )
        );
    }



    public function insert_nonbloxxuser_callback($request) {
        global $wpdb;

        $api_table = $wpdb->prefix.'bloxx_apis';
        $conn_site = $wpdb->prefix.'connected_sites';
        // fetch requests
        $site_url = $request['site_url'];
        $site_name = $request['site_name'];
        $user_email = $request['user_email'];


        // check site url if user is registered in bloxx but not activated api. it means for bloxx registered users, when buildr plugin is activated then entry goes in wp_connected_sites table. Adnd after authentication an entry goes into wp_bloxx_apis table
        $site_url_with_slash = $site_url.'/';
        $sql_connected_sites = "SELECT * FROM `$conn_site` WHERE `site_url` = '$site_url_with_slash'";
        $getdata_connected_sites  = $wpdb->get_results($sql_connected_sites);
        if(count($getdata_connected_sites) > 0){
            $result = array(
                'code' => 201, // means site is registered through bloxx account
                'bloxxbuilder_connect'=>'no',
                'message' => "API Connection Pending",
            );
        }else{



           // $username = get_usename_by_email($user_email);
           //pre($request);
           
            $username = $user_email;
            $randompass = wp_generate_password( 10, true, true );
            $now = date('Y-m-d H:i:s');


            


          //  pre($request);

            $sql3 = "SELECT * FROM `$api_table` WHERE `website_url` = '$site_url'";
            $getdata3  = $wpdb->get_results($sql3);
           // echo $sql3;
          //  pre($getdata3);
            $website_count = count($getdata3);
           // echo '<br>website_count=>'.$website_count;
            if($website_count > 0){
                $is_website_url_exists = 'yes';
            }
            else{
                $is_website_url_exists = 'no'; // this url not exists in divibloxx
            }



            if ( !email_exists( $user_email ) && $is_website_url_exists=='no') {
                // this condition means user not exists already and domain is new
                // insert new user in divibloxx if not exists already
                //$user_id = wp_insert_user( $username, $randompass, $user_email );

                $userdata = array(
                    'user_login' =>  $user_email,
                    'user_email' =>  $user_email,
                    'user_pass'  => $randompass
                );
                 
                $user_id = wp_insert_user( $userdata ) ;
                $user = new WP_User( $user_id );
                $user->set_role( 'um_free' );

                $user_id = $user->ID;

               // pre($userdata);
               // pre($user);

              //  echo '<br>user_id inside=>'. $user_id;
               // $final_username = get_username_by_userid($user_id); //app username

               // echo 'line98';

                //die('stop101');
                
            }else{
               // echo 'line98';
                // this condition means user existing already but and domain is new
                if (   email_exists( $user_email ) && $is_website_url_exists=='no') {
                    
                    $random_email = generateRandomString(7).'@divibloxx.com';
                 
                    $userdata2 = array(
                        'user_login' =>  $random_email,
                        'user_email' =>  $random_email,
                        'user_pass'  => $randompass
                    );
                     
                    $user_id2 = wp_insert_user( $userdata2 ) ;
                    $user = new WP_User( $user_id2 );
                    $user->set_role( 'um_free' );

                    $user_id = $user->ID;


                    //echo '<br>user_id2=>'.$user_id2;
                  //  $final_username = get_username_by_userid($user_id); //app username
                    //echo 'line113';
                }
            }

             
              // echo '<br>user_id inside=>'.$user_id;
               // echo '<br>user_id2=>'.$user_id2;
             //  pre($user);
             //  echo '<br>final_username=>'.$final_username;
             
             

                // update user metas

                /*
                update_user_meta($user_id,'site_url', $site_url);
                update_user_meta($user_id, 'plugins_limit', 2);
                update_user_meta($user_id, 'project_limit', 2);
                update_user_meta($user_id, 'plugins_downgrade', "no");
                update_user_meta($user_id, 'current_plan', "75783");
                update_user_meta($user_id, 'current_plan_selected', "75783");
                update_user_meta($user_id, 'custom_email_verification', "no");
                update_user_meta($user_id, 'registered_plan', "free");

    */
                // insert data in connected sites
                $currentuser_id= $user_id;
                $bloxx_userid = $user_id; 
                $website_url = $site_url.'/';    

                // call this function to insert data in connected sites
               // $this->create_connection_automatic($api_key,$website_url,$bloxx_userid, $siteblox_termid, $now); 

                // end insert data in connected sites



                // insert data in bloxx apis table
                $website_count = count($getdata3);
                $app_name= $site_name;
              //  $siteblox_username = $final_username;
                $api_key = bloxx_encrypt($site_url);
                $api_token= md5($site_url);


                if($website_count==0)
                {
                    //echo 'line129';
                    $data = array(
                        'website_url' => $site_url,
                       // 'api_username' => $siteblox_username,
                        'api_key' => $api_key,
                        'user_id'=> $user_id,
                        'term_id'=> '', // term id not created so far
                        'api_token' => $api_token,
                        'status' => 2, // entry inserted in api table with inactive status
                        'is_external' => 1,
                        'prime_key'=> 2,
                        'created_at'=> $now,
                        'updated_at'=> $now
                    );
                    $wpdb->insert($api_table, $data); 
                    $last_id= $wpdb->insert_id;

                   // pre($data);
                    //echo 'inside last id=>'.$last_id;
                    
                    //insert data in connected sites
                    createExternalApp($user_id,$app_name,$site_url, $last_id); 
                }
                // end api insertion
           
    //echo 'line153';

                

            // get term id here. it was created from createExternalApp function
            $sql = "SELECT * FROM `$conn_site` WHERE `site_url` = '$website_url' AND `siteblox_user_id` = $user_id";
            $getdata  = $wpdb->get_results($sql);
            $siteblox_termid = $getdata[0]->siteblox_termid;
            //echo $sql;

          //  pre($getdata);

    //die('builder api 196');
            //pre($getdata);
            // update term_id in bloxx_apis table because it was not inserted before
            //$data_apis = array(
             //   'term_id'=> $siteblox_termid,
           // );
           // $wpdb->update($api_table, $data_apis, array('website_url' => $site_url));
            //pre($data_apis);



            $result = array(
                'code' => 200,
                'siteblox_key'=>$api_key,
                'builder_key'=>$api_key,
                'bloxx_api_token'=>$api_token,
                'bloxx_user_id'=>$user_id,
                'bloxx_term_id'=>$siteblox_termid,
                'bloxxbuilder_connect'=>'no',
                'message' => "Connected Successfully!",
            );
    } // end else if site is non-bloxx

      
    
        echo json_encode($result);
        die();
    }


    /*
    function create_connection_automatic($api_key,$website_url,$siteblox_user_id, $siteblox_termid, $now){
        // echo $term_id;die;
        global $wpdb;
        $conn_site = $wpdb->prefix . 'connected_sites';
        $web_query = "select * from $conn_site where site_url = '$website_url'";
        $web_result = $wpdb->get_results($web_query);
        $website_count = count($web_result);
        if($website_count==0){
            $data = array(
                'siteblox_key'=> $api_key,
                'site_url' => $website_url,
                'user_id' => 1,
                'siteblox_user_id' => $siteblox_user_id,
                'siteblox_termid' => $siteblox_termid,
                'is_connect'=> 1,
                'is_external' =>1,
                'date'=> $now
            );
            $wpdb->insert($conn_site, $data);
            $last_id= $wpdb->insert_id;;
        } else {

            $data = array(
                'siteblox_key'=> $api_key,
                'site_url' => $website_url,
                'user_id' => 1,
                'siteblox_user_id' => $siteblox_user_id,
                'siteblox_termid' => $siteblox_termid,
                'is_connect'=> 1,
                'is_external' =>1,
                'date'=> $now
            );
           
            $wpdb->update($conn_site, $data, array('site_url' => $website_url));
           // die('stop');
        }
    }
    */

    //  end non bloxx user insertion code




// simple authentication code starts

    public function siteblox_check_bloxxaccount() {
        @register_rest_route(
            'siteblox-app', '/check_bloxxaccount/', array(
                    'methods' => 'POST',
                    'callback' => array($this, 'check_bloxxaccount_callback'),
            )
        );
    }

    public function check_bloxxaccount_callback($request) {
        global $wpdb;
        $site_url = $request['site_url'];
        $conn_site = $wpdb->prefix . 'connected_sites';
        $user_query = "select * from $conn_site where site_url = '$site_url' AND siteblox_user_id='0'  AND siteblox_termid='0'";
        $my_query = $wpdb->get_results($user_query);
        $count_data = count($my_query);
        if ($count_data == 1) {
            $siteblox_termid = $my_query[0]->siteblox_termid;
            $result = array(
                'code' => 200,
                'siteblox_termid' =>$siteblox_termid,
                'message' => "This domain associated with Bloxx Site",
            );
        } else {
            $user_data = $wpdb->get_row($user_query);
            $userid = $user_data->ID;

            //Project count
            $result = array(
                'code' => 201,
                'siteblox_termid' =>0,
                'message' => "This domain is not associated with Bloxx Site",
            );
        }
        echo json_encode($result);
        die();
    }

    

    // simple authentication code ends

    public function siteblox_dashboard() {
        @register_rest_route(
            'siteblox-app', '/dashboard/', array(
                    'methods' => 'POST',
                    'callback' => array($this, 'sitebloxapi_dashboard'),
            )
        );
    }

    public function sitebloxapi_dashboard($request) {
        global $wpdb;
        $user_email = $request['user_email'];
        $conn_site = $wpdb->prefix . 'users';
        $user_query = "select * from $conn_site where user_email='$user_email'";
        $my_query = $wpdb->get_results($user_query);
        $count_data = count($my_query);
        if ($count_data == 0) {
            $result = array(
                'code' => 200,
                'app_project' => 0,
                'app_page' => 0,
                'app_section' => 0,
                'message' => "User not found on App Bloxx Site",
            );
        } else {
            $user_data = $wpdb->get_row($user_query);
            $userid = $user_data->ID;

            //Project count
            $result = array(
                'code' => 200,
                'app_project' => $this->user_bloxxproject_count($userid),
                'app_page' => $this->user_bloxxpage_count($userid),
                'app_section' => $this->user_bloxxsection_count($userid),
                'message' => "Data retrieve successfully",
            );
        }
        echo json_encode($result);
        die();
    }

    public function user_bloxxsection_count($current_user_id) {
        $args = array(
            'post_type' => 'project',
            'order' => 'DESC',
            'fields' => 'ids',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'builder_custom_cat_user',
                    'value' => $current_user_id,
                    'compare' => 'LIKE',
                )
            )
        );

        $totals = get_posts($args);
        $count_arr = count($totals);

        if (!empty($count_arr)) {
            return $count_arr;
        } else {
            return "0";
        }
    }

    public function user_bloxxpage_count($current_user_id) {

        $builder_projects = get_terms(
                array(
                    'taxonomy' => 'project_categories',
                    'hide_empty' => false,
                    'fields' => 'ids',
                    'meta_query' => array(
                        array(
                            'key' => 'builder_cat_user',
                            'value' => $current_user_id,
                            'compare' => '='
                        )
                    )
                )
        );

        if (isset($builder_projects) && !empty($builder_projects)) {

            foreach ($builder_projects as $key => $term_id) {
                $args = array(
                    'post_type' => 'customer_templates',
                    'order' => 'asc',
                    'fields' => 'ids',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        array(
                            'key' => 'template_user',
                            'value' => $current_user_id,
                            'compare' => '=',
                        )
                    ),
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'project_categories',
                            'terms' => $term_id,
                            'compare' => '='
                        )
                    )
                );

                $totals = get_posts($args);
                if ($totals) {
                    $count_arr[] = count($totals);
                }
            }

            if (!empty($count_arr)) {
                return count($count_arr);
            } else {
                return "0";
            }
        } else {
            return "0";
        }
    }

    public function user_bloxxproject_count($user_ID) {
        $builder_project = get_terms(
                array(
                    'taxonomy' => 'project_categories',
                    'hide_empty' => false,
                    'fields' => 'ids',
                    'meta_query' => array(
                        array(
                            'key' => 'builder_cat_user',
                            'value' => $user_ID,
                            'compare' => '='
                        )
                    )
                )
        );
        if (isset($builder_project) && !empty($builder_project)) {
            $count = count($builder_project);
        } else {
            $count = "0";
        }
        return $count;
    }

    public function siteblox_register_user() {
        @register_rest_route(
            'siteblox-app', '/registerapp/', array(
                'methods' => 'POST',
                'callback' => array($this, 'sitebloxapi_register'),
            )
        );
    }

    public function sitebloxapi_register($request) {
        global $wpdb;
        $user_email = $request['user_email'];
        $user_login = $request['user_login'];
        $user_pass = $request['user_pass'];
        $nick_name = $request['nick_name'];
        $first_name = $request['first_name'];
        $last_name = $request['last_name'];
        $description = $request['description'];
        $website = "";

        $conn_site = $wpdb->prefix . 'users';
        $user_query = "select * from $conn_site where user_email='$user_email'";
        $my_query = $wpdb->get_results($user_query);
        $count_data = count($my_query);
        if ($count_data == 0) {
            $userdata = array(
                'user_login' => $user_login,
                'user_email' => $user_email,
                'user_pass' => "test@123",
                'user_url' => $website,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'nickname' => $nick_name,
                'description' => $description
            );

            $userid = wp_insert_user($userdata);
        } else {
            $user_data = $wpdb->get_row($user_query);
            $userid = $user_data->ID;
        }

        $conn_site = $wpdb->prefix . 'users';
        $data = array(
            'user_pass' => $user_pass
        );
        $wpdb->update($conn_site, $data, array('ID' => $userid));

        $result = array(
            "code" => 200,
            "library_id" => $userid,
            'message' => "User Registerd Successfully"
        );
        echo json_encode($result);
        die();
    }

    // Create Page API
    function siteblox_librarysync_api() {
        @register_rest_route(
            'siteblox-api', '/librarysync/', array(
                'methods' => 'POST',
                'callback' => array($this, 'sitebloxlib_sync'),
            )
        );
    }

    function sitebloxlib_sync($request) {
        global $wpdb;
        $lib_nm = $request['filenm'];
        $lib_url = $request['fileurl'];
        $lib_content = $request['file_content'];
        $lib_imageurl = $request['feat_image'];

        $attach_id = "no_feature";

        if ($lib_imageurl != "") {
            $ext = strtolower(pathinfo($lib_imageurl, PATHINFO_EXTENSION));
            $get = wp_remote_get($lib_imageurl);
            $mirror = wp_upload_bits(basename($lib_imageurl), '', wp_remote_retrieve_body($get));
            $finalimage = $mirror['url'];

            $image_converted = $finalimage;
            $upload_dir = wp_upload_dir();
            $image_data = file_get_contents($image_converted);
            $filename = basename($image_converted);

            if (wp_mkdir_p($upload_dir['path'])) {
                $file = $upload_dir['path'] . '/' . $filename;
            } else {
                $file = $upload_dir['basedir'] . '/' . $filename;
            }

            file_put_contents($file, $image_data);

            $wp_filetype = wp_check_filetype($filename, null);

            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => sanitize_file_name($filename),
                'post_content' => '',
                'post_status' => 'inherit'
            );

            $attach_id = wp_insert_attachment($attachment, $file);
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            $attach_data = wp_generate_attachment_metadata($attach_id, $file);
            wp_update_attachment_metadata($attach_id, $attach_data);
        }



        $user_email = $request['user_email'];
        $user_login = $request['user_login'];
        $user_pass = $request['user_pass'];
        $nick_name = $request['nick_name'];
        $first_name = $request['first_name'];
        $last_name = $request['last_name'];
        $description = $request['description'];
        $account_status = $request['account_status'];
        $full_name = $request['full_name'];
        $website = "";


        $conn_site = $wpdb->prefix . 'users';
        $user_query = "select * from $conn_site where user_email='$user_email'";

        $my_query = $wpdb->get_results($user_query);
        $count_data = count($my_query);
        if ($count_data == 0) {
            $userdata = array(
                'user_login' => $user_login,
                'user_email' => $user_email,
                'user_pass' => "test@123",
                'user_url' => $website,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'nickname' => $nick_name,
                'description' => $description
            );
            $userid = wp_insert_user($userdata);

            $conn_site = $wpdb->prefix . 'users';
            $data = array(
                'user_pass' => $user_pass
            );
            $wpdb->update($conn_site, $data, array('ID' => $userid));

            $lib_status = $this->sync_library_section($lib_nm, $lib_content, $lib_imageurl, $attach_id, $userid);
            $result = array(
                "code" => 200,
                "library_id" => $lib_status,
                'message' => "Purchased item added on library successfully"
            );
        } else {
            $user_data = $wpdb->get_row($user_query);

            $user_id = $user_data->ID;


            $lib_status = $this->sync_library_section($lib_nm, $lib_content, $lib_imageurl, $attach_id, $user_id);
            $result = array(
                "code" => 200,
                "library_id" => $lib_status,
                'message' => "Purchased item added on library successfully"
            );
        }
        echo json_encode($result);
    }

    function sync_library_section($lib_nm, $lib_content, $lib_imageurl, $attach_id, $userid) {
        $lib_slug = str_replace(" ", "-", strtolower($lib_nm));
        $new_post = array(
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_title' => $lib_nm,
            'post_content' => $lib_content,
            'post_status' => 'publish',
            'post_name' => $lib_slug,
            'post_type' => 'project'
        );
        $pid = wp_insert_post($new_post);
        update_post_meta($pid, 'builder_custom_cat_user', $userid);
        update_post_meta($pid, '_et_pb_page_layout', 'et_no_sidebar');
        update_post_meta($pid, '_et_pb_use_builder', 'on');

        wp_set_object_terms($pid, intval(231), 'project_category');
        if ($attach_id != "no_feature") {
            set_post_thumbnail($pid, $attach_id);
        }
        return $pid;
    }

    // Get Plugin Update Notification
    function siteblox_check_update() {
        @register_rest_route(
            'siteblox-update', '/notification/', array(
                'methods' => 'GET',
                'callback' => array($this, 'sitebloxcheck_update'),
            )
        );
    }

    function sitebloxcheck_update($request) {
        if (get_option('sitebloxx_settings')) {
            $plugin_settings = get_option('sitebloxx_settings');
            $result = array(
                'code' => 200,
                'id' => $plugin_settings['id'],
                'slug' => $plugin_settings['slug'],
                'plugin' => $plugin_settings['plugin'],
                'new_version' => $plugin_settings['new_version'],
                'url' => $plugin_settings['url'],
                'package' => $plugin_settings['package'],
                'tested' => $plugin_settings['tested'],
                'requires_php' => $plugin_settings['requires_php'],
                'update-supported' => $plugin_settings['update_supported'],
                'Name' => $plugin_settings['Name'],
                'Version' => $plugin_settings['Version'],
                'Description' => $plugin_settings['Description'],
                'update' => $plugin_settings['update']
            );
        } else {
            $result = array(
                "code" => 202,
                "message" => "Connection failed"
            );
        }
        echo json_encode($result);
    }

    // Create Page API
    function siteblox_check_connection() {
        @register_rest_route(
            'siteblox-api', '/checkconnection/', array(
                'methods' => 'POST',
                'callback' => array($this, 'sitebloxcheck_connectionapi'),
            )
        );
    }

    function sitebloxcheck_connectionapi($request) {
        $siteblox_key = $request['siteblox_key'];
        global $wpdb;
        $conn_site = $wpdb->prefix . 'connected_sites';
        $my_query = $wpdb->get_results("select * from $conn_site where siteblox_key='$siteblox_key' and is_connect='1'");
        $count_data = count($my_query);
        if ($count_data == 1) {
            $result = array(
                "code" => 200,
                "message" => "Server connected"
            );
        } else {
            $result = array(
                "code" => 202,
                "message" => "Connection failed"
            );
        }
        echo json_encode($result);
    }
    
    // Create Page API
    function dropbox_connect_api() {
        @register_rest_route(
            'kitzdropbox', '/connect/', 
            array(
                'methods' => 'GET',
                'callback' => array($this, 'kitzdrop_function'),
            )
        );
    }
    
    function kitzdrop_function($request){
    	$drop_api= get_field('app_key', 'option');
        $drop_secret= get_field('app_secret', 'option');
        if($drop_api!="" && $drop_secret!="") {
            $result=array(
    			"code"=> 200,
            	"drop_api"=> $drop_api,
                "drop_secret"=> $drop_secret,
                "message"=> "Dropbox API retrieve successfully"
            );
        } else {
            $result=array(
                "code"=> 202,
                "drop_api"=> "no",
                "drop_secret"=> "no",
                "message"=> "Admin not configured dropbox settings"
            );
        }
        echo json_encode($result);
        die();
    }
    

    // Create Page API
    function siteblox_connect_api() {
        @register_rest_route(
            'siteblox-api', '/connect/', 
            array(
                'methods' => 'POST',
                'callback' => array($this, 'sitebloxconnectapi_data'),
            )
        );
    }



    function sitebloxconnectapi_data($request) {
        $website_url = $request["website_url"];
        $user_id = $request['server_userid'];
        $siteblox_username = $request['siteblox_username'];
        $siteblox_key = $request['siteblox_key'];
        $app_name = $request['website_nm'];
        global $wpdb;
        $bloxx_api_table = $wpdb->prefix . 'bloxx_apis';

        // get user_id by username 
        // check usern id and api key from bloxx_apis table
        $bloxx_userid =get_userid_by_email($siteblox_username);
        
        // 
       // pre($request);
       // die('stopp builderapi sitebloxx-sync plugin');

        //

        $conn_limit = "SELECT * FROM `$bloxx_api_table` WHERE `user_id` = '$bloxx_userid' AND `api_key` = '$siteblox_key' AND `prime_key` = '1' LIMIT 1";

        $conn_limit_query = $wpdb->get_results($conn_limit);
        $count_limit = count($conn_limit_query);
        // code 31.1.2023
        $exists = email_exists( $siteblox_username );
        if ( $exists ) {
           // echo "That E-mail is registered to user number " . $exists;
            if($siteblox_key=='' || $siteblox_key=='null'){
                $result = array(
                    "code" => 203,
                    "message" => "Please enter Divikitz Cloud Key!"
                );
            }else{
                // start
                    if($count_limit==1){
                        $conn_query = "select * from $bloxx_api_table where api_key='$siteblox_key' and status='1' and is_external='1'";
                        $my_query = $wpdb->get_results($conn_query);
                        $count_data = count($my_query);

                        $retrieve_sectionapi= site_url().'/retrieve-section-sidebar-api/';
                        $retrieve_layoutapi= site_url().'/retrieve-layout-sidebar-api/';

                        $bloxx_api = $wpdb->get_row($conn_limit);
                      //  pre($bloxx_api);
                        $bloxx_userid=$bloxx_api->user_id;
                        $plugin_limit=$bloxx_api->plugin_limit;
                        $term_id=$bloxx_api->term_id;
                        $is_external= $bloxx_api->is_external;

                        $connected_sites_table= $wpdb->prefix . 'connected_sites';
                        $api_con_query = "select * from $connected_sites_table where site_url='$website_url/'";
                        $con_result = $wpdb->get_row($api_con_query);
                        
                        $is_external= @$con_result->is_external;
                        $term_id = @$con_result->siteblox_termid;

                        $api_token= md5($website_url);
                        $now=date('Y-m-d H:i:s');

                        // if(@$is_external==2){
                        //    // die('if builder api');
                        //     $result=$this->create_connection($website_url,$siteblox_username, $siteblox_key, $bloxx_userid, $api_token, $now, $app_name, $retrieve_sectionapi,$retrieve_layoutapi, @$term_id);
                        // } else 

                        

                        if ($count_data < $plugin_limit) {
                            $result=$this->create_connection($website_url,$siteblox_username,  $siteblox_key, $bloxx_userid, $api_token, $now, $app_name, $retrieve_sectionapi,$retrieve_layoutapi, @$term_id);
                        } else {
                            $result = array(
                                "code" => 202,
                                "message" => "Connectivity failed, You have exceeded the maximum number of API limits, Please upgrade your plan limit"
                            );
                        }
                        //die('stop buildeapi');

                    } else {
                        $result = array(
                            "code" => 202,
                            "message" => "Connectivity failed, Please check your Divikitz Cloud Key!"
                        );
                    }
                // end
            }
            
        } else {
            //echo "That E-mail doesn't belong to any registered users on this site";
            $result = array(
                "code" => 204,
                "message" => "That E-mail does not belong to any registered users on this site!"
            );
        }
        // end
        
        echo json_encode($result);        
    }




    function create_connection($website_url, $siteblox_username, $siteblox_key, $bloxx_userid, $api_token, $now, $app_name, $retrieve_sectionapi,$retrieve_layoutapi, $term_id){
        // echo $term_id;die;
        global $wpdb;
        $conn_site = $wpdb->prefix . 'bloxx_apis';
        $web_query = "select * from $conn_site where website_url='$website_url'";
        $web_result = $wpdb->get_results($web_query);
        $website_count = count($web_result);
        if($website_count==0){
            $data = array(
                'website_url' => $website_url,
                //'api_username' => $siteblox_username,
                'api_key' => $siteblox_key,
                'user_id'=> $bloxx_userid,
                'term_id'=> $term_id,
                'api_token' => $api_token,
                'status' => 1,
                'is_external' => 1,
                'prime_key'=> 2,
                'updated_at'=> $now
            );
            $wpdb->insert($conn_site, $data);
            $last_id= $wpdb->insert_id;;
            
            //createExternalApp($bloxx_userid,$app_name,$website_url, $last_id);                    
        } else {

            $data = array(
               // 'api_username' => $siteblox_username,
                'api_key' => $siteblox_key,
                'user_id'=> $bloxx_userid,
                'api_token' => $api_token,
                'term_id'=> $term_id,
                'status' => 1,
                'updated_at'=> $now
            );
            update_term_meta($term_id,'is_deleted',0);
            update_term_meta($term_id,'builder_cat_user', $bloxx_userid);
            $wpdb->update($conn_site, $data, array('website_url' => $website_url));
        }
        
		$drop_box= get_field("enable_feature", "option");
		if($drop_box=="enable"){
			$dropbox="enable";
			$drop_api= get_field('app_key', 'option');
			$drop_secret= get_field('app_secret', 'option');
		} else {
			$dropbox="disable";
			$drop_api= "no";
			$drop_secret= "no";
		}
		
        $result = array(
            "code" => 200,
            'section_api' => $retrieve_sectionapi,
            'layout_api' => $retrieve_layoutapi, 
            'api_token' => $api_token,  
            'user_id' => $bloxx_userid,
            'term_id'=> $term_id,
            'bloxxbuilder_use_free_features'=>1,
			'dropbox'  => $dropbox,
			'drop_api'  => $drop_api,
			'drop_secret'  => $drop_secret,
            "message" => "Connection Created Successfully"
        );

        return $result;
    }





    //Disconnect API
    // Create Page API
    function siteblox_disconnect_api() {
        @register_rest_route(
            'siteblox-api', '/disconnect/', 
            array(
                'methods' => 'POST',
                'callback' => array($this, 'sitebloxdisconnectapi_data'),
            )
        );
    }

    function sitebloxdisconnectapi_data($request) {
        $website_url = $request["website_url"];
        $user_id = $request['server_userid'];
        $siteblox_key = $request['siteblox_key'];
        global $wpdb;
        $conn_site = $wpdb->prefix . 'bloxx_apis';
        $disconnect_query="select * from $conn_site where website_url='$website_url' order by id desc limit 1";       
        
        $my_query = $wpdb->get_results($disconnect_query);
        $count_data = count($my_query);
        if ($count_data == 1) {
            $bloxx_api = $wpdb->get_row($disconnect_query);
            $id = $bloxx_api->id;
            $data = array(                                
                'status' => 2
            );
            disconnectExternalApp($bloxx_api->user_id,$website_url);
            $wpdb->update($conn_site, $data, array('id' => $id));            
        } 

        $result = array(
            "code" => 200,
            'bloxxbuilder_use_free_features'=>1,
            "message" => "Connection Disconnect Successfully"
        );
        echo json_encode($result);
        die();
    }

}

$builderapi = new Builderapi();