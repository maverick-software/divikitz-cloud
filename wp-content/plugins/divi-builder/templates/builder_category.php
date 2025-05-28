<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Builder_category {

    public function __construct() {
        add_shortcode('my-projects', array($this, 'my_projects_function'));

        //Builder Cat Saved
        add_action("wp_ajax_builder_cat_saved", array($this, "builder_cat_saved"));
        add_action("wp_ajax_nopriv_builder_cat_saved", array($this, "builder_cat_saved"));

        //Builder Cat Saved and assign into page
        add_action("wp_ajax_project_assign_page", array($this, "project_assign_page"));
        add_action("wp_ajax_nopriv_project_assign_page", array($this, "project_assign_page"));

        // custom category

        add_action("wp_ajax_builder_custom_cat_saved", array($this, "builder_custom_cat_saved"));
        add_action("wp_ajax_nopriv_builder_custom_cat_saved", array($this, "builder_custom_cat_saved"));

        add_action("wp_ajax_builder_custom_cat_edit", array($this, "builder_custom_cat_edit"));
        add_action("wp_ajax_nopriv_builder_custom_cat_edit", array($this, "builder_custom_cat_edit"));

        add_action("wp_ajax_builder_custom_cat_delete", array($this, "builder_custom_cat_delete"));
        add_action("wp_ajax_nopriv_builder_custom_cat_delete", array($this, "builder_custom_cat_delete"));

        //Bloxx Hosting
        add_action("wp_ajax_bloxx_hosting", array($this, "bloxx_hosting"));
        add_action("wp_ajax_nopriv_bloxx_hosting", array($this, "bloxx_hosting"));



        //EL Connector
        add_action("wp_ajax_elconnector", array($this, "elconnector"));
        add_action("wp_ajax_nopriv_elconnector", array($this, "elconnector"));
    }

    public function elconnector() {
        //echo builder_path.'assets/elFinder/php/elFinderConnector.class.php';
        //error_reporting(0);
        require_once builder_path . 'assets/elFinder/php/elFinderConnector.class.php';
        require_once builder_path . 'assets/elFinder/php/elFinder.class.php';
        require_once builder_path . 'assets/elFinder/php/elFinderVolumeDriver.class.php';

        require_once builder_path . 'assets/elFinder/php/elFinderVolumeFTP.class.php';
        require_once builder_path . 'assets/elFinder/php/elFinderVolumeSFTPphpseclib.class.php';
        extract($_REQUEST);
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $user_email = $current_user->user_email;
        $coll_item = get_term_meta($term_id, "allow_collaborate", true);


        if ($coll_item == $user_email) {
            $user_id = get_term_meta($term_id, 'request_collaborate', true);
        }

        $app_id = get_term_meta($term_id, 'bloxx_app_id', true);
        $meta_key = "website_" . $app_id;
        $user_meta = get_user_meta($user_id, $meta_key, true);

        

        $maseter_usernm= $user_meta->sftp_user;
        $maseter_pass= $user_meta->sftp_pass;


        $sys_user = $user_meta->sys_user;
        $website_link = "https://" . $user_meta->app_fqdn;
        $public_ip = $user_meta->public_ip;
        //$filepath= str_replace('\', '/', ABSPATH);
        $filepath = "/applications/$sys_user/public_html/";

        $local_path = builder_path . "assets/elFinder/files/.trash/";


        $hide_htaccess = array(
            'pattern' => '/.htaccess/',
            'read' => false,
            'write' => false,
            'hidden' => true,
            'locked' => false
        );

        $opts = array(
            'debug' => false,
            'roots' => array(
                // Items volume
                array(
                    'driver' => 'SFTPphpseclib', // driver for accessing file system (REQUIRED)
                    'host' => $public_ip,
                    'user' => $maseter_usernm,
                    'pass' => $maseter_pass,
                    'port' => 22,
                    'path' => $filepath, // path to files (REQUIRED)
                    'dirMode' => 0755, // new dirs mode (default 0755)
                    'fileMode' => 0644, // new files mode (default 0644)
                    'URL' => $website_link, // URL to files (REQUIRED)
                    'trashHash' => 't1_Lw', // elFinder's hash of trash folder
                    'winHashFix' => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                    'uploadDeny' => array('all'), // All Mimetypes not allowed to upload
                    'uploadAllow' => array('all'), // Mimetype `image` and `text/plain` allowed to upload
                    'uploadOrder' => array('deny', 'allow'), // allowed Mimetype `image` and `text/plain` only
                    'accessControl' => 'access', // disable and hide dot starting files (OPTIONAL)
                    'attributes' => array(
                        array(
                            'pattern' => '/.tmb/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        ),
                        array(
                            'pattern' => '/.quarantine/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        ),
                        array(
                            'pattern' => '/.gitkeep/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        ),
                        $hide_htaccess
                    )
                ),
                // Trash volume
                array(
                    'id' => '1',
                    'driver' => 'Trash',
                    'path' => $local_path,
                    'tmbURL' => site_url() . '/assets/elFinder/files/.trash/',
                    'winHashFix' => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                    'uploadDeny' => array(''), // Recomend the same settings as the original volume that uses the trash
                    'uploadAllow' => array('all'), // Same as above
                    'uploadOrder' => array('deny', 'allow'), // Same as above
                    'accessControl' => 'access', // Same as above					
                    'attributes' => array(
                        array(
                            'pattern' => '/.tmb/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        ),
                        array(
                            'pattern' => '/.quarantine/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        ),
                        array(
                            'pattern' => '/.gitkeep/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        )
                    )
                ),
            )
        );

        // run elFinder
        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();
        die();
    }

    public function bloxx_hosting() {
        global $wpdb;
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $conn_site = $wpdb->prefix . 'usermeta';
        $meta_query = "SELECT * FROM $conn_site WHERE `user_id` = '$user_id' AND `meta_key` LIKE '%website%' LIMIT 50";
        $bloxx_apps_details = $wpdb->get_results($meta_query);
        $count_bloxx = count($bloxx_apps_details);
        if ($count_bloxx == 1) {
            $result_array = array();
            foreach ($bloxx_apps_details as $bloxx_details):
                $meta_value = $bloxx_details->meta_value;
                $meta_unsearlize = unserialize($meta_value);
                $label_nm = $meta_unsearlize->label;
                $website_url = $meta_unsearlize->app_fqdn;
                $meta_key = $bloxx_details->meta_key;

                $result_array[] = array(
                    'website_label' => $label_nm,
                    'website_url' => $website_url,
                    'meta_key' => $meta_key
                );
            endforeach;
            $result = array(
                'code' => 200,
                'bloxx_details' => $result_array,
                'message' => "Bloxx hosted app data retrive successfully"
            );
        } else {
            $result = array(
                'code' => 202,
                'message' => "No app found, Please create app from app menu"
            );
        }

        echo json_encode($result);
        die();
    }

    public function project_assign_page() {
        extract($_REQUEST);
        $project_catnm = strip_tags(htmlspecialchars($_POST['project_catnm']));
        $pid = $_REQUEST['change_page_id'];

        if ($project_catnm == -1) {
            wp_delete_object_term_relationships($pid, 'project_categories');
            wp_set_object_terms($pid, intval($builder_pagecat), 'project_categories');
            $result = array(
                'code' => 200,
                'project_id' => $builder_pagecat,
                'pageid' => $pid,
                'message' => 'Project change successfully'
            );
        } else {

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
                $term_id = $cid['term_taxonomy_id'];
                $user = wp_get_current_user();
                $current_user_id = $user->ID;
                add_term_meta($term_id, "builder_cat_user", $current_user_id);

                wp_delete_object_term_relationships($pid, 'project_categories');
                wp_set_object_terms($pid, intval($term_id), 'project_categories');

                $result = array(
                    'code' => 200,
                    'project_id' => $term_id,
                    'pageid' => $pid,
                    'message' => 'Project change successfully'
                );
            }
        }
        echo json_encode($result);
        die();
    }

    public function builder_cat_saved() {
        extract($_REQUEST);

        global $current_user, $wp_roles;
        $user_id = $current_user->ID;
        $project_catnm = strip_tags(htmlspecialchars($app_name));
        update_user_meta($user_id, 'current_project_name', $project_catnm);

        if($action_data=="paid_app"){
            if(isset($username) && isset($api_key)){
                update_user_meta($user_id, 'divi_username', $username);
                update_user_meta($user_id, 'divi_api_key', $api_key);
            }
            $result = array(
                'code' => 200,
                'plan_select' => $plan,
                'redirect_url'=> $selected_plan,
                'message' => $project_catnm . ' created successfully'
            );
        } else {
            global $apps;
            $apps->schedulenewapp_free();

            if(isset($username) && isset($api_key)){
                update_user_meta($user_id, 'divi_username', $username);
                update_user_meta($user_id, 'divi_api_key', $api_key);
            }
            $site_url=site_url();
            $result = array(
                'code' => 200,
                'redirect_url'=>$site_url,
                'message' => $project_catnm . ' created successfully'
            ); 
        }                
        echo json_encode($result);
        die();
    }

    // custom category

    public function builder_custom_cat_saved() {
        extract($_REQUEST);
        $project_catnm = strip_tags(htmlspecialchars($_POST['project_catnm']));
        $cid = wp_insert_term($project_catnm, 'project_category', array(
            'description' => '',
        ));
        if (is_wp_error($cid)) {
            $error_message = $cid->get_error_message();
            $result = array(
                'code' => 202,
                'message' => $error_message
            );
        } else {
            $term_id = $cid['term_taxonomy_id'];
            $user = wp_get_current_user();
            $current_user_id = $user->ID;
            add_term_meta($term_id, "builder_custom_cat_user", $current_user_id);
            $result = array(
                'code' => 200,
                'pid' => $term_id,
                'message' => $project_catnm . ' created successfully'
            );
        }
        echo json_encode($result);
        die();
    }

    public function builder_custom_cat_edit() {
        extract($_REQUEST);
        $project_catnm = strip_tags(htmlspecialchars($_POST['project_catnm']));
        $project_catid = $_POST['id'];
        $cid = wp_update_term($project_catid, 'project_category', array(
            'name' => $project_catnm,
        ));
        if (is_wp_error($cid)) {
            $error_message = $cid->get_error_message();
            $result = array(
                'code' => 202,
                'message' => $error_message
            );
        } else {
            $term_id = $cid['term_taxonomy_id'];
            $user = wp_get_current_user();
            $current_user_id = $user->ID;
            add_term_meta($term_id, "builder_custom_cat_user", $current_user_id);
            $result = array(
                'code' => 200,
                'pid' => $term_id,
                'message' => $project_catnm . ' updated successfully'
            );
        }
        echo json_encode($result);
        die();
    }

    public function builder_custom_cat_delete() {
        extract($_REQUEST);

        $project_catid = $_POST['id'];
        $cid = wp_delete_term($project_catid, 'project_category');
        if (is_wp_error($cid)) {
            $error_message = $cid->get_error_message();
            $result = array(
                'code' => 202,
                'pid' => 1,
                'message' => $error_message
            );
        } else {

            $result = array(
                'code' => 200,
                'message' => 'Category deleted successfully'
            );
        }
        echo json_encode($result);
        die();
    }

    public function my_projects_function() {

        if (is_user_logged_in()) {
            global $current_user, $wp_roles, $wpdb;
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;

            $current_plan = get_user_meta($user_id, 'current_plan', true);
            if ($current_plan != "") {
                $plan_title = get_the_title($current_plan);
            } else {
                $plan_title = "Free";
            }
            ?>

            <script>
                jQuery(function ($) {
                    $('title').html("Manage APP | <?php echo get_option('blogdescription'); ?>");
                });
            </script>

            <div class="contentWrapper user_actions" id="table-page">
                <!-- //sidebar  --> 
                <?php require_once 'builder_siderbar.php'; ?>

                <div class="wrapContent">
                    <!-- //Top Bar  --> 
                    <?php require_once 'builder_topnav.php'; ?>


                    <div class="wrapContainer">
                        <div class="rowWrap">
                            <div class="flex-12 dashboard_no mBottom">
                                <div class="boxbg-blue d-flexinner">
                                    <div id="accordion" class="accordion-container">
                                    <?php if (isset($builder_project) && !empty($builder_project)) { ?>
                                        <h4 class="filter-options">
                                            <p><span class="accordion-title js-accordion-title"><i class="fas fa-angle-down"></i></span> Apps 
                                                <span class="plusSign">
                                                    <a href="javascript:void(0)" class="project-btn" id="add_prj" data-user="" data-user="<?php echo $free_user_flag; ?>"><i class="fas fa-plus"></i> New</a>
                                                </span>
                                            </p>
                                            <ul class="projectOptions">
                                                <li>
                                                    <button class="buttonView"><i class="fas fa-sort-amount-down"></i></button>
                                                    <ul class="dropdownList">									                              		
                                                        <li><a href="javascript:;"><i class="fas fa-text-width"></i> By Name</a></li>
                                                        <li><a href="javascript:;"><i class="far fa-calendar-check"></i> By Date</a></li>
                                                        <li><a href="javascript:;"><i class="far fa-edit"></i> Last Modified</a></li>
                                                    </ul>
                                                </li>
                                                <li>
                                                    <button class="buttonView"><i class="fas fa-th-large"></i></button>
                                                    <ul class="dropdownList">									                              		
                                                        <li class="list-view-button"><a href="javascript:;"><i class="fas fa-bars"></i> List View</a></li>
                                                        <li class="grid-view-button active"><a href="javascript:;"><i class="fas fa-th-large"></i> Grid View</a></li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </h4>
                                        <?php } ?>
                                        <div class="accordion-content">			               		
                                            <div class="list grid-view-filter">
                                                <div class="rowWrap">
                                                    <?php
                                                    $builder_project = get_terms(
                                                            array(
                                                                'taxonomy' => 'project_categories',
                                                                'hide_empty' => false,
                                                                'orderby' => 'date',
                                                                'sort_order' => 'desc',
                                                                'number' => 12,
                                                                'meta_query' => array(
                                                                    'relation' => 'OR',
                                                                    array(
                                                                        'key' => 'builder_cat_user',
                                                                        'value' => $user_id,
                                                                        'compare' => '='
                                                                    ),
                                                                    array(
                                                                        'key' => "accept_invitation_$user_id",
                                                                        'value' => 'yes',
                                                                        'compare' => '='
                                                                    )
                                                                )
                                                            )
                                                    );
                                                    ?>

                                                    <?php if (isset($builder_project) && !empty($builder_project)) { ?>
                                                        <?php 

                                                            // $allcrons =  _get_cron_array() ;
                                                            // foreach($allcrons as $key =>$val){
                                                            //     foreach($val as $k =>$v){
                                                            //         //print_r($v);die;
                                                            //         foreach($v as $a =>$b){
                                                            //             $cronarr[$k] = $b['args']; 
                                                            //         }
                                                                    
                                                            //     }
                                                            // }
                                                            // echo "<pre>";print_r($cronarr);
                                                            $tablebo = $wpdb->prefix . 'bloxx_operations';
                                                        ?>

                                                        <?php foreach ($builder_project as $builder_cats): ?>
                                                            <?php 
                                                                if(get_term_meta($builder_cats->term_id,'is_deleted',true) == '1'){
                                                                    continue;
                                                                }
                                                            ?>
                                                            <?php $term_id = $builder_cats->term_id; ?>

                                                            <?php $sync_connected = connected_status($term_id); ?>
                                                            <?php 
                                                            $bloxxapp_id = get_term_meta($term_id,'bloxx_app_id',true);
                                                            $websdata = get_user_meta($user_id,trim('website_'.$bloxxapp_id),true); 

                                                            ?>

                                                            <?php $coll_data = apply_filters('collaborat_result', $term_id); ?>

                                                            <?php $coll_item = get_term_meta($term_id, "allow_collaborate", true); ?>
                                                            <?php $term_appid = get_term_meta($term_id, "bloxx_app_id", true); ?>

                                                            <?php $term_nm = ucfirst($builder_cats->name); ?>
                                                            <?php $term_img = get_term_meta($term_id, "term_image", true); ?>
                                                            <?php
                                                            if ($term_img == "") {
                                                                $term_img = builder_url . "images/placeholder.png";
                                                            }
                                                            $is_external = get_term_meta($term_id,'is_external',true);
                                                            if (!metadata_exists('user', $current_user_id, trim('website_' . $term_appid)) && $is_external != 'yes') {
                                                                $pending_app = 'pending_app';
                                                            }else if ($wpdb->get_var("SELECT COUNT(*) FROM $tablebo WHERE app_id = '".$term_appid."' AND status = 'processing' AND operation_type='cron'") > 0  && $is_external != 'yes') {
                                                                $pending_app = 'pending_app';
                                                            }else {
                                                                $pending_app = '';
                                                            }

                                                            ?>
                                                            <?php $term_link = site_url() . "/apps/?term_id=" . $term_id."&panel=page"; ?>
                                                            <div class="flex-3 mBottom dashboard_no <?php echo $pending_app; ?>" data-term-id="<?= $term_id; ?>">
                                                                <div class="box bg-white p-2">
                                                                    <?php if ($coll_item != $current_email) { ?>
                                                                        <div class="starred_action">
                                                                            <a href="javascript:void(0)" id="<?= $term_id; ?>" data-id="add" data-name="You want to add <?= $term_nm; ?> from Starred Project"><i class="far fa-star"></i></a>
                                                                        </div>  
                                                                    <?php } else { ?>
                                                                        <div class="defaultstarred_action">
                                                                            <a href="javascript:void(0)"><i class="far fa-star"></i></a>
                                                                        </div>
                                                                    <?php } ?>
                                                                    <a href="<?= $term_link; ?>"><img src="<?= $term_img; ?>" /></a>
                                                                    <div class="d-flexinner">
                                                                        <h4 class="mb-0">
                                                                            <a href="<?= $term_link; ?>"><?= $term_nm; ?>
                                                                                <?php if ($pending_app != ''): ?>
                                                                                    <div class="loader"></div>
                                                                                <?php endif; ?>
                                                                            </a>
                                                                                                                                                      
                                                                            <div class="userBottom">
                                                                                <?php if ($coll_data['coll_resp'] != "no_coll") { ?>
                                                                                    <span class="userImgs">                             
                                                                                        <?php foreach ($coll_data['col_data'] as $coll_res): ?>
                                                                                            <img src="<?= $coll_res['coll_img']; ?>" title="<?= $coll_res['coll_nm']; ?>" alt="<?= $coll_res['coll_nm']; ?>"/>
                                                                                        <?php endforeach; ?>
                                                                                        <span class="lastNmbr"><?= $coll_data['total_call']; ?></span>
                                                                                    </span>
                                                                                <?php } ?>
                                                                            </div>
                                                                        </h4>
                                                                        <?php if ($coll_item != $current_email) { ?>
                                                                            <div class="projectOptions">
                                                                                <button class="buttonView"><i class="fas fa-ellipsis-v"></i></button>
                                                                                <ul class="dropdownList">
                                                                                    <li><a href="<?= $term_link; ?>"><i class="far fa-eye"></i> Preview</a></li>
                                                                                    <div class="dividerLine"></div>
                                                                                    <li><a href="#"><i class="fas fa-share-alt"></i> Share</a></li>
<!--                                                                                    <li><a href="javascript:void(0)" title="Duplicate" data-id="<?= $term_id; ?>" data-name="<?= $term_nm; ?>" data-title="You want to duplicate <?= $term_nm; ?> Project"><i class="far fa-copy"></i> Duplicate</a></li>-->
                                                                                    <div class="dividerLine"></div>
                                                                                    <li class="project_rename_btn"><a href="javascript:void(0)" title="Rename" id="rename_project" data-id="<?= $term_id; ?>" data-name="<?= $term_nm; ?>" data-title="You want to rename <?= $term_nm; ?> project name"><i class="fas fa-text-width"></i> Rename</a></li>
                                                                                    <li class="builder_delete_project"><a href="javascript:void(0)" title="Move to Trash" id="del_project" data-id="<?= $term_id; ?>" data-name="<?= $term_nm; ?> Project, This action will remove all pages"><i class="far fa-trash-alt"></i> Move to Trash</a></li>
                                                                                </ul>
                                                                            </div>
                                                                        <?php } ?>  
                                                                        <p><a href="<?= $term_link; ?>"><?= @$websdata->app_fqdn; ?></a></p>
                                                                    </div>  
                                                                    <?php if($pending_app!=''){ echo '<div class="pending_app_overlay">
                                                                <img src="'.plugins_url().'/divi-builder/images/loader.gif"/>
                                                                    <span>Hang tight! We are creating your app.</span></div>';} ?>                                                                  
                                                                </div>

                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php } else { ?>
                                                        <div class="flex-12 dashboard_no">
                                                            <div class="box bg-white p-2 emptyBox">
                                                                <img src="<?php echo plugins_url(); ?>/divi-builder/images/empty-pages.png"/>
                                                                <h3>Oh, The apps you will create!</h3>
                                                                <p>Start something right away to see this space fill with your beautiful work</p>
                                                                <p class="plusSign">
                                                                    <a href="javascript:void(0)" class="project-btn" id="add_prj" data-user="" data-user="<?php echo $free_user_flag; ?>">Create New <i class="fas fa-plus"></i></a>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Project Assign Modal -->
                    <?= do_shortcode('[add_project_popup]'); ?>
                    <!-- End Modal LightBox -->
                </div>	
                <?php require_once 'builder_footer.php'; ?>
            </div>



            <?php
        } else {
            restricate_page_content();
        }
        ?>
        <?php
    }

    public function project_count($term_id) {
        $user = wp_get_current_user();
        $current_user_id = $user->ID;
        $args = array(
            'post_type' => 'customer_templates',
            'order' => 'asc',
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
                    'field' => 'term_id',
                    'terms' => $term_id,
                    'compare' => '='
                )
            )
        );
        $query = new WP_Query($args);
        return (int) $query->post_count;
    }

    public function connected_status($term_id) {
        global $wpdb;
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $conn_site = $wpdb->prefix . 'connected_sites';
        $project_query = "SELECT * FROM $conn_site where siteblox_user_id='$user_id' and `is_connect`='1' and siteblox_termid='$term_id' order by id desc limit 1";
        $connected_sites = $wpdb->get_results($project_query);
        $count_connected = count($connected_sites);
        if ($count_connected == 1) {
            $con_details = $wpdb->get_row($project_query);
            $get_data = array(
                'website_url' => $con_details->site_url,
                'server_userid' => $con_details->user_id,
                'siteblox_key' => $con_details->siteblox_key,
                'count_data' => $count_connected
            );
        } else {
            $get_data = array(
                'website_url' => '',
                'count_data' => $count_connected
            );
        }
        return $get_data;
    }

}

$builder_category = new Builder_category();