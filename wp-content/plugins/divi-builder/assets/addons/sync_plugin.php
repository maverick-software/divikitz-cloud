<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



class Sync_plugin{

    public function __construct(){
        //Bloxx Plugin Move functionality
        add_action("wp_ajax_bloxx_plugin_process", array($this, "bloxx_plugin_process"));
        add_action("wp_ajax_nopriv_bloxx_plugin_process", array($this, "bloxx_plugin_process"));
         add_action("bloxx_plugin_process_cron", array($this, "bloxx_plugin_processnew"),10,2);
    }


    public function bloxx_plugin_process(){
        global $wpdb;
        include "Net/SFTP.php";

        extract($_REQUEST);
        $current_user = wp_get_current_user();
        $user_id= $current_user->ID;

        $app_id= get_term_meta($cats_id, 'bloxx_app_id', true);
        
        $category_data = get_term_by('id', $cats_id, 'project_categories');

        $meta_key= "website_".$app_id;

        $user_meta=get_user_meta($user_id, $meta_key, true);
        

        $website_url="https://".$user_meta->app_fqdn."/";

        $sys_user= $user_meta->sys_user;


        //SFTP
        $public_ip = $user_meta->public_ip;
        $sftp_user = $user_meta->sftp_user;
        $sftp_pass = $user_meta->sftp_pass;


        //Mysql Connectiviety 
        $servername = $public_ip;
        $username = $user_meta->mysql_user;
        $password = $user_meta->mysql_password;
        $dbname = $user_meta->mysql_db_name;

       
        //External DB connectivity
        @$conn = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        
        if ($conn->connect_error) {
            $result=array(
                'code'          => 202,             
                'message'       => "Database connectivity failed"
            );          
        } else {

            //Create Key
            $current_user = wp_get_current_user();
            $currentuser_id= $current_user->ID;
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $randomString = '';
          
            for ($i = 0; $i < 10; $i++) {
                $index = rand(0, strlen($characters) - 1);
                $randomString .= $characters[$index];
            }

            $micro_time= round(microtime(true));
                
            $sitebox_key= $randomString."==".$micro_time;

            $conn_site = $wpdb->prefix.'connected_sites';
            $data=array(
                'siteblox_key'     => $sitebox_key,
                'siteblox_user_id' => $currentuser_id,
                'siteblox_termid'  => $cats_id,
                'site_url'         => $website_url,
                'is_connect'       => 1,
                'user_id'          => 1
            );
            $id = $wpdb->insert($conn_site, $data);
            $sitebloxx_key=$sitebox_key;
            //End Create Key

            // update server's term meta to update plugin status that its active
            update_term_meta($cats_id,'buildr_plugin_status','active');
            
            //$active_plugin=array('breeze/breeze.php', 'malcare-security/malcare.php', 'sitebloxx-sync/wp-rest-api-controller.php');
            $active_plugin=array('malcare-security/malcare.php', 'sitebloxx-sync/wp-rest-api-controller.php');
            
            $serial_active_plugin=serialize($active_plugin);

            $sql = "UPDATE wp_options SET option_value='$serial_active_plugin' WHERE option_name='active_plugins'";

            $sql_theme_template = "UPDATE wp_options SET option_value='Divi' WHERE option_name='template'";
            $sql_theme_stylesheet = "UPDATE wp_options SET option_value='Divi' WHERE option_name='stylesheet'";

            // $user_update_sql = "UPDATE wp_users SET user_login ='admin', user_email = '', user_nicename = 'admin' WHERE id= 1";
            //  $conn->query($user_update_sql);
            if ($conn->query($sql) === TRUE) {
                
                $conn->query($sql_theme_template);
                $conn->query($sql_theme_stylesheet);
                
                if (!empty($category_data)) {
                    $term_name = ucwords($category_data->name);
                    $sql_blog_nm = "UPDATE wp_options SET option_value='blogname' WHERE option_name='$term_name'";
                    $sql_blog_desc = "UPDATE wp_options SET option_value='blogdescription' WHERE option_name=''";
                    $conn->query($sql_blog_nm);
                    $conn->query($sql_blog_desc);
                }

                $key_insert = "insert into wp_options(option_name, option_value)values('siteblox_key', '$sitebloxx_key')";
                $conn->query($key_insert);

                $sftp = new Net_SFTP($public_ip);
                if (!$sftp->login($sftp_user, $sftp_pass)) {
                    $result=array(
                        'code'          => 202,             
                        'message'       => "SFTP login failed, Please try again later. For instant help please contact support."
                    ); 
                }
                

                $local_plugin= builder_path."/assets/addons/bloxx.zip";
                $server_path= "/applications/$sys_user/public_html/wp-content/plugins/bloxx.zip";
                $unzip_path= "/home/master/applications/$sys_user/public_html/wp-content/plugins/bloxx.zip";
                $plugin_path="/home/master/applications/$sys_user/public_html/wp-content/plugins/";


                $local_theme= builder_path."/assets/addons/Divi.zip";
                $server_theme_path= "/applications/$sys_user/public_html/wp-content/themes/Divi.zip";
                $unzip_them_path= "/home/master/applications/$sys_user/public_html/wp-content/themes/Divi.zip";
                $theme_path="/home/master/applications/$sys_user/public_html/wp-content/themes/";


                
                if($sftp->put($server_path, $local_plugin, NET_SFTP_LOCAL_FILE)){
                    $sftp->exec("unzip $unzip_path -d $plugin_path");
                }


                if($sftp->put($server_theme_path, $local_theme, NET_SFTP_LOCAL_FILE)){
                    $sftp->exec("unzip $unzip_them_path -d $theme_path");
                }

                $result=array(
                    'code'          => 200,             
                    'message'       => "Your server on sitebloxx has been created successfully."
                ); 

            } else {
                $result=array(
                    'code'          => 202,             
                    'message'       => "Query execution failed. Please contact support."
                );  
            }
        }

        echo json_encode($result);
        die();
    }


    public function bloxx_plugin_processnew($user_id,$cats_id){
        global $wpdb;
        $tablebo = $wpdb->prefix . 'bloxx_operations';
        include "Net/SFTP.php";
        $app_id= get_term_meta($cats_id, 'bloxx_app_id', true);
        
        $category_data = get_term_by('id', $cats_id, 'project_categories'); 
        
        $meta_key= "website_".$app_id;

        $user_meta=get_user_meta($user_id, $meta_key, true);
        

        $website_url="https://".$user_meta->app_fqdn."/";

        $sys_user= $user_meta->sys_user;


        //SFTP
        $public_ip = trim($user_meta->public_ip);
        $sftp_user = trim($user_meta->sftp_user);
        $sftp_pass = trim($user_meta->sftp_pass);


        //Mysql Connectiviety 
        $servername = $public_ip;
        $username = $user_meta->mysql_user;
        $password = $user_meta->mysql_password;
        $dbname = $user_meta->mysql_db_name;

       
        //External DB connectivity
        @$conn = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        
        if ($conn->connect_error) {
            $result=array(
                'code'          => 202,             
                'message'       => "Database connectivity failed"
            );   
                 
        } else {
 
            //Create Key
            $current_user = wp_get_current_user();
            $currentuser_id= $user_id;
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $randomString = '';
          
            for ($i = 0; $i < 10; $i++) {
                $index = rand(0, strlen($characters) - 1);
                $randomString .= $characters[$index];
            }

            $micro_time= round(microtime(true));
                
            $sitebox_key= $randomString."==".$micro_time;

            $conn_site = $wpdb->prefix.'connected_sites';
            $data=array(
                'siteblox_key'     => $sitebox_key,
                'siteblox_user_id' => $currentuser_id,
                'siteblox_termid'  => $cats_id,
                'site_url'         => $website_url,
                'is_connect'       => 1,
                'user_id'          => 1
            );
            $id = $wpdb->insert($conn_site, $data);
            $sitebloxx_key=$sitebox_key;
            //End Create Key


            // update server's term meta to update plugin status that its active
            update_term_meta($cats_id,'buildr_plugin_status','active');
            
            //$active_plugin=array('breeze/breeze.php', 'malcare-security/malcare.php', 'bloxx/bloxx_builder.php');
            $active_plugin=array('malcare-security/malcare.php', 'buildr/bloxx_builder.php');
            
            $serial_active_plugin=serialize($active_plugin);

            $sql = "UPDATE wp_options SET option_value='$serial_active_plugin' WHERE option_name='active_plugins'";

            // $sql_theme_template = "UPDATE wp_options SET option_value='Divi' WHERE option_name='template'";
            // $sql_theme_stylesheet = "UPDATE wp_options SET option_value='Divi_Child' WHERE option_name='stylesheet'";

            //Set by Default Sample Page
            $sql_setpage = "UPDATE wp_options SET option_value='page' WHERE option_name='show_on_front'";
            $sql_samplepage = "UPDATE wp_options SET option_value='2' WHERE option_name='page_on_front'";
            $sql_deletecontent_samplepage = "UPDATE wp_posts SET post_content='' WHERE ID='2'";
            
            $the_user = get_user_by( 'id', $user_id ); 
            $usermail = $the_user->user_email;


            $sql_user_update = "UPDATE wp_users SET user_nicename='".$usermail."',user_email='".$usermail."',user_login = '".$usermail."',display_name='".$usermail."' WHERE ID='1'";
            $conn->query($sql_user_update);
            if ($conn->query($sql) === TRUE) {
                
                // $conn->query($sql_theme_template);
                // $conn->query($sql_theme_stylesheet);

                $conn->query($sql_setpage);
                $conn->query($sql_samplepage);
                $conn->query($sql_deletecontent_samplepage);

                if (!empty($category_data)) {
                    $term_name = ucwords($category_data->name);
                    update_term_meta($cats_id, "term_nm", $term_name);
                    $sql_blog_nm = "UPDATE wp_options SET option_value='$term_name' WHERE option_name='blogname'";
                    $sql_blog_desc = "UPDATE wp_options SET option_value='' WHERE option_name='blogdescription'";
                    $conn->query($sql_blog_nm);
                    $conn->query($sql_blog_desc);
                }

                // Permalink query
                $permalink_query = "UPDATE wp_options SET option_value = '/%postname%/' WHERE wp_options.option_name = 'permalink_structure'";
                $conn->query($permalink_query);

                $key_insert = "insert into wp_options(option_name, option_value)values('siteblox_key', '$sitebloxx_key')";
                $conn->query($key_insert);

                $sftp = new Net_SFTP($public_ip);
                if (!$sftp->login($sftp_user, $sftp_pass)) {
                    $result=array(
                        'code'          => 202,             
                        'message'       => "SFTP login failed, Please try again later. For instant help please contact support."
                    ); 
                }
                

                //$local_plugin= builder_path."/assets/addons/buildr.zip";
                $local_plugin= ABSPATH."/repository/buildr.zip";
                $server_path= "/applications/$sys_user/public_html/wp-content/plugins/buildr.zip";
                $unzip_path= "/home/master/applications/$sys_user/public_html/wp-content/plugins/buildr.zip";
                $plugin_path="/home/master/applications/$sys_user/public_html/wp-content/plugins/";

                // For writrAI plugin
                // $local_writeplugin= ABSPATH."/repository/WritrAI.zip";
                // $server_writeplugin_path= "/applications/$sys_user/public_html/wp-content/plugins/WritrAI.zip";
                // $unzip_writeplugin_path = "/home/master/applications/$sys_user/public_html/wp-content/plugins/WritrAI.zip";
                //$plugin_path="/home/master/applications/$sys_user/public_html/wp-content/plugins/";

                // $local_theme= builder_path."/assets/addons/Divi.zip";
                // $server_theme_path= "/applications/$sys_user/public_html/wp-content/themes/Divi.zip";
                // $unzip_them_path= "/home/master/applications/$sys_user/public_html/wp-content/themes/Divi.zip";
                // $theme_path="/home/master/applications/$sys_user/public_html/wp-content/themes/";
                
                
                // $local_child_theme= builder_path."/assets/addons/Divi_Child.zip";
                // $server_childtheme_path= "/applications/$sys_user/public_html/wp-content/themes/Divi_Child.zip";
                // $unzip_childtheme_path= "/home/master/applications/$sys_user/public_html/wp-content/themes/Divi_Child.zip";
                // $theme_child_path="/home/master/applications/$sys_user/public_html/wp-content/themes/";


                
                if($sftp->put($server_path, $local_plugin, NET_SFTP_LOCAL_FILE)){
                    $sftp->exec("unzip $unzip_path -d $plugin_path");
                }

                // if($sftp->put($server_writeplugin_path, $local_writeplugin, NET_SFTP_LOCAL_FILE)){
                //     $sftp->exec("unzip $unzip_writeplugin_path -d $plugin_path");
                // }


                // if($sftp->put($server_theme_path, $local_theme, NET_SFTP_LOCAL_FILE)){
                //     $sftp->exec("unzip $unzip_them_path -d $theme_path");
                // }
                
                // if($sftp->put($server_childtheme_path, $local_child_theme, NET_SFTP_LOCAL_FILE)){
                //     $sftp->exec("unzip $unzip_childtheme_path -d $theme_child_path");
                // }

                $wpdb->delete( $tablebo, array( 'operation_type' => 'cron','app_id'=>$app_id,'user_id'=>$user_id ) );




                //Sync Page Curl API

                $curl_url= $website_url."wp-json/builder-page/sync";
                $sync_pages_data = array(
                    'sync_page' => "yes"
                );

                $sync_json = json_encode($sync_pages_data);

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
                    CURLOPT_POSTFIELDS => $sync_json,
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
                    $page_resp = json_decode($response, true);
                    $page_array=$page_resp['page_data'];
                    update_term_meta($cats_id, 'term_hosted_pages', $page_array);
                }
                //End Sync Page Curl API




                $result=array(
                    'code'          => 200,             
                    'message'       => "Your server on sitebloxx has been created successfully."
                ); 

            } else {
                $result=array(
                    'code'          => 202,             
                    'message'       => "Query execution failed. Please contact support."
                );  
            }
        }

        echo json_encode($result);
        die();
    }

}


$sync_plugin = new Sync_plugin();
?>