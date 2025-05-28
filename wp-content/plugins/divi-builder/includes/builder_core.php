<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Builder_core {

    public function __construct() {

        add_action('wp_enqueue_scripts', array($this, 'plugin_css_jsscripts'));
        add_action('init', array($this, 'create_posttype'));
        //add_action('init', array($this, 'create_import_section'));
        add_action('admin_init', array($this, 'check_ultimate_plugin'));
        add_action('admin_init', array($this, 'check_divi_theme'));

        //Meta Box
        add_action("add_meta_boxes", array($this, "add_custom_meta_box"));
        add_action("save_post", array($this, "save_custom_meta_box"));

        register_activation_hook(__FILE__, array($this, 'builder_pages'));

        add_action('admin_menu', array($this, 'builder_remove_menus'));

        add_action('admin_menu', array($this, 'builder_add_bloxx_menus'));


        add_action('init', array($this, 'wpb_custom_new_menu'));

        add_action('init', array($this, 'template_taxonomy'));
    }

    function wpb_custom_new_menu() {
        register_nav_menus(
            array(
                'my-custom-menu' => __('Builder Menu 1'),
                'mobile-menu' => __('Mobile Menu'),
                'extra-menu' => __('Builder Menu 2')
            )
        );
    }

    function builder_add_bloxx_menus(){
        //add_submenu_page('bloxx-app','Bloxx ACF Settings', 'Bloxx ACF Settings','manage_options','bloxx-settings', array($this,''));

    }

    function plugin_css_jsscripts() {
        global $wpdb, $wp_query;
        @$page_id = $wp_query->post->ID;


        //Style css
        wp_enqueue_style('style-css', builder_url . "css/style.css?v=" . time());

        if (!isset($_GET['et_fb'])) {
            //Sweet Alret
            wp_enqueue_script('sweer-alert', builder_url . "js/sweetalert.min.js", array('jquery'), '', true);

            // JavaScript
            wp_enqueue_script('jquery-validator', builder_url . 'js/jquery.validate.min.js', array('jquery'), '', true);

            // JavaScript
            wp_enqueue_script('script-js', builder_url . 'js/script.js?v=' . time(), array('jquery'), '', true);

        } 

        
        

        

        if(is_user_logged_in()){
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
            $assets_limit = get_user_meta($user_id, "assets_limit", true);
            $api_limit= get_user_meta($user_id, "api_limit", true);
            $fragment= get_user_meta($user_id, "fragment_limit", true);
            $neo_builder= get_user_meta($user_id, "neo_builder", true);
            $neo_writter= get_user_meta($user_id, "neo_writter", true);

            $user_role = get_user_role_name($user_id);

            if($user_role=="Administrator"){
                $result= get_current_user_limits("Administrator");
                $api_limitation=array(
                    "user_id"        => $user_id,
                    "assets"         => $result['assets'],
                    "api"            => $result['api_limit'],
                    "fragment"       => $result['fragment'],
                    "neo_builder"    => $result['neo_builder'],
                    "neo_writter"    => $result['neo_writter'],
                );
            } else {
                $api_limitation=array(
                    "user_id"        => $user_id,
                    "assets"         => $assets_limit,
                    "api"            => $api_limit,
                    "fragment"       => $fragment,
                    "neo_builder"    => $neo_builder,
                    "neo_writter"    => $neo_writter
                );
            } 



            $count_assets=0;
            $section_post_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_author = '" . $user_id . "' AND post_type = 'project' AND post_status = 'publish'");
            $count_assets +=$section_post_count;
            
            $layout_post_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_author = '" . $user_id . "' AND post_type = 'layouts' AND post_status = 'publish'");
            $count_assets+= $layout_post_count;
            $uses_limitations= array(
                "use_assets" => $count_assets
            );

        } else {
            $api_limitation=array(
                "user_id"        => 0,
                "assets"         => 0,
                "api"            => 0,
                "fragment"       => 0,
                "neo_builder"    => "disable",
                "neo_writter"    => "disable"
            );

            $uses_limitations= array(
                "use_assets" => 100
            );
        }

        // Pass ajax_url to script.js
        if (!isset($_GET['et_fb'])) {
            wp_localize_script('script-js', 'builder', array('ajax_url' => admin_url('admin-ajax.php'), "api_limit"=> $api_limitation, "uses_limitations"=> $uses_limitations));
        }
    }

    function check_ultimate_plugin() {
        if (!is_plugin_active('ultimate-member/ultimate-member.php')) {
            $error_message = "Please activate ultimate member plugin for continue...  Custom Divi Builder Plugin";
            $this->error_message($error_message);
        }
    }

    function check_divi_theme() {
        $theme = wp_get_theme();
        $er = 0;
        if ('Divi' == $theme->name) {
            $er = 0;
        } else if ('Divi Child Theme' == $theme->name) {
            $er = 0;
        } else {
            $er = 1;
        }

        if ($er == 1) {
            $error_message = "Please activate Divi theme for continue...  Custom Divi Builder Plugin";
            $this->error_message($error_message);
        }
    }

    function error_message($error_message) {
        ?>
        <div class="updated error divi_builder">
            <p><?php esc_html_e($error_message); ?></p>
        </div>

        <?php
    }

    //Create Template
    function create_posttype() {
        register_post_type('customer_templates', array(
            'labels' => array(
                'name' => __('Bloxx Pages'),
                'singular_name' => __('Template')
            ),
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-admin-page',
            'supports' => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions',),
            'rewrite' => array('slug' => 'customer_templates'),
            'show_in_rest' => true,
                )
        );


        register_post_type('layouts', array(
            'labels' => array(
                'name' => __('Layouts'),
                'singular_name' => __('Layout')
            ),
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-admin-page',
            'supports' => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions',),
            'rewrite' => array('slug' => 'bloxx_layouts'),
            'show_in_rest' => true,
                )
        );


    }

    


    //Texonomy

    function template_taxonomy() {

        $labels = array(
            'name' => _x('Categories', 'taxonomy general name'),
            'singular_name' => _x('Category', 'taxonomy singular name'),
            'search_items' => __('Search Category'),
            'popular_items' => __('Popular Category'),
            'all_items' => __('All Category'),
            'parent_item' => null,
            'parent_item_colon' => null,
            'edit_item' => __('Edit Category'),
            'update_item' => __('Update Category'),
            'add_new_item' => __('Add New Category'),
            'new_item_name' => __('New Category Name'),
            'separate_items_with_commas' => __('Separate Category with commas'),
            'add_or_remove_items' => __('Add or remove topics'),
            'choose_from_most_used' => __('Choose from the most used topics'),
            'menu_name' => __('Categories'),
        );

        register_taxonomy('project_categories', 'customer_templates', array(
            'hierarchical' => false,
            'labels' => $labels,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var' => true,
            'rewrite' => array('slug' => 'project_categories'),
        ));


        $labels = array(
            'name' => _x('Categories', 'taxonomy general name'),
            'singular_name' => _x('Category', 'taxonomy singular name'),
            'search_items' => __('Search Category'),
            'popular_items' => __('Popular Category'),
            'all_items' => __('All Category'),
            'parent_item' => null,
            'parent_item_colon' => null,
            'edit_item' => __('Edit Category'),
            'update_item' => __('Update Category'),
            'add_new_item' => __('Add New Category'),
            'new_item_name' => __('New Category Name'),
            'separate_items_with_commas' => __('Separate Category with commas'),
            'add_or_remove_items' => __('Add or remove topics'),
            'choose_from_most_used' => __('Choose from the most used topics'),
            'menu_name' => __('Categories'),
        );

        register_taxonomy('bloxx_categories', 'layouts', array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var' => true,
            'rewrite' => array('slug' => 'bloxx_categories'),
        ));



        $labels_service_type = array(
            'name' => _x('Industry', 'taxonomy general name'),
            'singular_name' => _x('Industry', 'taxonomy singular name'),
            'search_items' => __('Search Industry'),
            'popular_items' => __('Popular Industry'),
            'all_items' => __('All Industries'),
            'parent_item' => null,
            'parent_item_colon' => null,
            'edit_item' => __('Edit Industry'),
            'update_item' => __('Update Industry'),
            'add_new_item' => __('Add New Industry'),
            'new_item_name' => __('New Industry'),
            'separate_items_with_commas' => __('Separate Category with commas'),
            'add_or_remove_items' => __('Add or remove topics'),
            'choose_from_most_used' => __('Choose from the most used topics'),
            'menu_name' => __('Industry'),
        );

        register_taxonomy('service_type', 'layouts', array(
            'hierarchical' => true,
            'labels' => $labels_service_type,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var' => true,
            'rewrite' => array('slug' => 'service_type'),
        ));
    }

    //Custom Meta Box 



    function add_custom_meta_box() {
        add_meta_box("post_create_by", "Published By User Info", [$this, 'package_meta_callback'], "customer_templates", "side", "high", null);
    }

    function package_meta_callback($post) {
        wp_nonce_field(basename(__FILE__), "meta-box-nonce");
        $post_id = get_the_id();
        $template_user = get_post_meta($post_id, 'template_user', true);
        ?>	    
        <select name="template_user_id" id="template_user_id" class="postbox">
        <?php
        $args1 = array(
            array('role__in' => array('author', 'subscriber')),
            'orderby' => 'user_nicename',
            'order' => 'ASC'
        );

        $customers = get_users($args1);
        ?>
            <option>Select User</option>
        <?php foreach ($customers as $user): ?>
                <option value="<?php echo $user->ID; ?>" <?php if ($template_user == $user->ID) {
                echo "selected";
            } ?>><?php echo $user->display_name . ' [' . $user->user_email . ']'; ?></option>
            <?php endforeach; ?>	        
        </select>
            <?php
        }

        function save_custom_meta_box($post) {
            if (!isset($_POST["meta-box-nonce"]) || !wp_verify_nonce($_POST["meta-box-nonce"], basename(__FILE__)))
                return $post;


            if (isset($_POST['template_user_id'])) {
                update_post_meta($post, 'template_user', sanitize_text_field($_POST['template_user_id']));
            }
        }

        //Builder Pages

        function builder_pages() {
            if (!current_user_can('activate_plugins'))
                return;

            global $wpdb;
            $current_user = wp_get_current_user();

            if (null === $wpdb->get_row("SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name = 'builder-profile'", 'ARRAY_A')) {

                $profile = array(
                    'post_content' => '[builder_profile]',
                    'post_title' => 'Builder Profile',
                    'post_status' => 'publish',
                    'post_author' => $current_user->ID,
                    'post_type' => 'page'
                );
                wp_insert_post($profile);
            }


            if (null === $wpdb->get_row("SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name = 'builder-projects'", 'ARRAY_A')) {

                $projects = array(
                    'post_content' => '[builder_projects]',
                    'post_title' => 'Builder Projects',
                    'post_status' => 'publish',
                    'post_author' => $current_user->ID,
                    'post_type' => 'page'
                );

                wp_insert_post($projects);
            }

            if (null === $wpdb->get_row("SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name = 'builder-library'", 'ARRAY_A')) {
                $create_project = array(
                    'post_content' => '[builder_library]',
                    'post_title' => 'Builder Library',
                    'post_status' => 'publish',
                    'post_author' => $current_user->ID,
                    'post_type' => 'page'
                );

                wp_insert_post($create_project);
            }
        }

        function builder_remove_menus() {
            remove_menu_page('edit.php');                   //Posts 
            remove_menu_page( 'edit.php?post_type=customer_templates' );

            //remove_menu_page( 'admin.php?page=bloxx-settings' );
        }

    }

    $builder_core = new Builder_core();

    function wpb_cat_list_func() {
        $current_user = wp_get_current_user();
        $current_user_id = $current_user->ID;
        $builder_projects = get_terms(array(
            'taxonomy' => 'project_category',
            'hide_empty' => false,
        ));
        $project_category = array();
        if (!empty($builder_projects)) {
            foreach ($builder_projects as $builder_cats):
                $builder_custom_cat_user = get_term_meta($builder_cats->term_id, 'builder_custom_cat_user', true);
                if ($builder_custom_cat_user == $current_user_id || $builder_custom_cat_user == "") {
                    $project_category[$builder_cats->term_id] = $builder_cats->name;
                }
            endforeach;
        }

        return json_encode($project_category);
    }

    add_shortcode("login_member_redirect", "after_payment_login_member_func");

    function after_payment_login_member_func() {
        if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] == "https://pageberry.spp.io/" && isset($_COOKIE['diviuser_login']) && !empty($_COOKIE['diviuser_login'])) {
            wp_set_current_user($_COOKIE['diviuser_login']);
            wp_set_auth_cookie($_COOKIE['diviuser_login']);
            ob_start();
            wp_redirect(get_site_url() . '/dashboard');
            exit;
        }
        if (isset($_COOKIE['diviuser_r3eg']) && !empty($_COOKIE['diviuser_r3eg'])) {
            unset($_COOKIE['diviuser_r3eg']);
            setcookie('diviuser_r3eg', null, -1, '/');
            return '<div class="um-notice success" role="alert">Registration successful. Please check your email to confirm and reset your password.</div>';
        }
    }

    if (!function_exists('get_user_role_name')) {

        function get_user_role_name($user_ID) {
            global $wp_roles;

            $user_data = get_userdata($user_ID);
            $user_role_slug = @$user_data->roles[0];
            return translate_user_role($wp_roles->roles[@$user_role_slug]['name']);
        }

    }


    function login_cookie($user_login, $user) {
        $id = $user->ID;
        setcookie("diviuser_login", $id, time() + 3600, COOKIEPATH, COOKIE_DOMAIN);
    }

    add_action('wp_login', 'login_cookie', 10, 2);



    add_action('clear_auth_cookie', 'clear_auth_cookie_func', 10);

    function clear_auth_cookie_func() {
        setcookie("diviuser_login", ' ', time() - YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);
    }

    function login_redirect($redirect_to, $request, $user) {
        return get_site_url() . '/dashboard';
    }

    add_filter('login_redirect', 'login_redirect', 10, 3);



    add_filter('woocommerce_add_to_cart_redirect', 'sitebloxx_skip_cart_redirect_to_checkout_page');

    function sitebloxx_skip_cart_redirect_to_checkout_page() {
        global $woocommerce;
        $checkout_url = wc_get_checkout_url();
        return $checkout_url;
    }




    add_filter('woocommerce_add_to_cart_validation', 'bbloomer_only_one_in_cart', 9999, 2);

    function bbloomer_only_one_in_cart($passed, $added_product_id) {
        wc_empty_cart();
        return $passed;
    }




    add_action('validate_password_reset', 'rsm_redirect_after_rest', 10, 2);

    function rsm_redirect_after_rest($errors, $user) {
        if ((!$errors->get_error_code() ) && isset($_POST['user_password']) && !empty($_POST['user_password'])) {
            reset_password($user, $_POST['user_password']);
            list( $rp_path ) = explode('?', wp_unslash($_SERVER['REQUEST_URI']));
            $rp_cookie = 'wp-resetpass-' . COOKIEHASH;
            setcookie($rp_cookie, ' ', time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true);
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID);
            do_action('wp_login', $user->user_login, $user); //`[Codex Ref.][1]
            wp_redirect(home_url() . '/dashboard');
            exit;
        }
    }

//Get Paid Taxonomy 
//hook into the init action and call create_book_taxonomies when it fires

    add_action('init', 'create_subjects_hierarchical_taxonomy', 0);

//create a custom taxonomy name it subjects for your posts

    function create_subjects_hierarchical_taxonomy() {

// Add new taxonomy, make it hierarchical like categories
//first do the translations part for GUI

        $labels = array(
            'name' => _x('types', 'Types'),
            'singular_name' => _x('Type', 'Type'),
            'search_items' => __('Search types'),
            'all_items' => __('All types'),
            'parent_item' => __('Parent Type'),
            'parent_item_colon' => __('Parent Type:'),
            'edit_item' => __('Edit Type'),
            'update_item' => __('Update Type'),
            'add_new_item' => __('Add New Type'),
            'new_item_name' => __('New Type Name'),
            'menu_name' => __('Plan Types'),
        );

// Now register the taxonomy
        register_taxonomy('subjects', array('wpi_item'), array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'subject'),
        ));
    }
    