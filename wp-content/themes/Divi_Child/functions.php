<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


//use TwilioRestClient;
add_action('wp_enqueue_scripts', 'theme_enqueue_styles');

function theme_enqueue_styles() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
}


function remove_footer_admin() {
    echo "Divi Child Theme";
}

add_filter('admin_footer_text', 'remove_footer_admin');

add_action('woocommerce_checkout_create_order', 'update_order_first_and_last_names', 30, 2);

function update_order_first_and_last_names($order, $posted_data) {
    $user_id = $order->get_customer_id(); // Get user ID

    if (empty($user_id) || $user_id == 0)
        return; // exit

    $first_name = $order->get_billing_first_name(); // Get first name (checking)

    if (empty($first_name)) {
        $first_name = get_user_meta($user_id, 'billing_first_name', true);
        if (empty($first_name))
            $first_name = get_user_meta($user_id, 'first_name', true);

        $order->set_billing_first_name($first_name); // Save first name
    }

    $last_name = $order->get_billing_last_name(); // Get last name (checking)

    if (empty($last_name)) {
        $last_name = get_user_meta($user_id, 'billing_last_name', true);
        if (empty($last_name))
            $last_name = get_user_meta($user_id, 'last_name', true);

        $order->set_billing_last_name($last_name); // Save last name
    }
}


if (!function_exists('project_count')) {

    function project_count($term_id) {
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

}




if (!function_exists('restricate_page_content')) {

    function restricate_page_content() {
        $url = site_url('/login');
        wp_redirect($url);
        exit;
    }

}


if (!function_exists('connected_status')) {

    function connected_status($term_id) {
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

function user_meta() {
    $user = wp_get_current_user();
    $current_user_id = $user->ID;
    $display_array = get_user_meta($current_user_id, "submitted", true);
    if (isset($display_array) && !empty($display_array)) {
        $display_nm = @$display_array['display_name'];
        $full_name = explode(" ", $display_nm);
        update_user_meta($current_user_id, "display_name", $display_nm);
    }
}

add_action('wp_head', 'user_meta');


function wp_kama_woocommerce_form_field_filter( $field,$key, $args, $value = null ) {

    if($key == 'billing_email' || $key == 'billing_first_name' || $key == 'billing_last_name' || $key == 'billing_address_1' || $key == 'billing_country' || $key == 'billing_city' || $key == 'billing_state' || $key == 'billing_postcode' || $key == 'billing_phone' && is_user_logged_in()){
        $value = get_user_meta(get_current_user_id(),$key,true);
    }


    $defaults = array(
        'type'              => 'text',
        'label'             => '',
        'description'       => '',
        'placeholder'       => '',
        'maxlength'         => false,
        'required'          => false,
        'autocomplete'      => false,
        'id'                => $key,
        'class'             => array(),
        'label_class'       => array(),
        'input_class'       => array(),
        'return'            => false,
        'options'           => array(),
        'custom_attributes' => array(),
        'validate'          => array(),
        'default'           => '',
        'autofocus'         => '',
        'priority'          => '',
    );
 
    $args = wp_parse_args( $args, $defaults );
    $args = apply_filters( 'woocommerce_form_field_args', $args, $key, $value );
 
    if ( $args['required'] ) {
        $args['class'][] = 'validate-required';
        $required        = '&nbsp;<abbr class="required" title="' . esc_attr__( 'required', 'woocommerce' ) . '">*</abbr>';
    } else {
        $required = '&nbsp;<span class="optional">(' . esc_html__( 'optional', 'woocommerce' ) . ')</span>';
    }
 
    if ( is_string( $args['label_class'] ) ) {
        $args['label_class'] = array( $args['label_class'] );
    }
 
    if ( is_null( $value ) ) {
        $value = $args['default'];
    }
 
    // Custom attribute handling.
    $custom_attributes         = array();
    $args['custom_attributes'] = array_filter( (array) $args['custom_attributes'], 'strlen' );
 
    if ( $args['maxlength'] ) {
        $args['custom_attributes']['maxlength'] = absint( $args['maxlength'] );
    }
 
    if ( ! empty( $args['autocomplete'] ) ) {
        $args['custom_attributes']['autocomplete'] = $args['autocomplete'];
    }
 
    if ( true === $args['autofocus'] ) {
        $args['custom_attributes']['autofocus'] = 'autofocus';
    }
 
    if ( $args['description'] ) {
        $args['custom_attributes']['aria-describedby'] = $args['id'] . '-description';
    }
 
    if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
        foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
            $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
        }
    }
 
    if ( ! empty( $args['validate'] ) ) {
        foreach ( $args['validate'] as $validate ) {
            $args['class'][] = 'validate-' . $validate;
        }
    }
 
    $field           = '';
    $label_id        = $args['id'];
    $sort            = $args['priority'] ? $args['priority'] : '';
    $field_container = '<p class="form-row %1$s" id="%2$s" data-priority="' . esc_attr( $sort ) . '">%3$s</p>';
 
    switch ( $args['type'] ) {
        case 'country':
            $countries = 'shipping_country' === $key ? WC()->countries->get_shipping_countries() : WC()->countries->get_allowed_countries();
 
            if ( 1 === count( $countries ) ) {
 
                $field .= '<strong>' . current( array_values( $countries ) ) . '</strong>';
 
                $field .= '<input type="hidden" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="' . current( array_keys( $countries ) ) . '" ' . implode( ' ', $custom_attributes ) . ' class="country_to_state" readonly="readonly" />';
 
            } else {
 
                $field = '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="country_to_state country_select ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . '><option value="">' . esc_html__( 'Select a country / region&hellip;', 'woocommerce' ) . '</option>';
 
                foreach ( $countries as $ckey => $cvalue ) {
                    $field .= '<option value="' . esc_attr( $ckey ) . '" ' . selected( $value, $ckey, false ) . '>' . esc_html( $cvalue ) . '</option>';
                }
 
                $field .= '</select>';
 
                $field .= '<noscript><button type="submit" name="woocommerce_checkout_update_totals" value="' . esc_attr__( 'Update country / region', 'woocommerce' ) . '">' . esc_html__( 'Update country / region', 'woocommerce' ) . '</button></noscript>';
 
            }
 
            break;
        case 'state':
            /* Get country this state field is representing */
            $for_country = isset( $args['country'] ) ? $args['country'] : WC()->checkout->get_value( 'billing_state' === $key ? 'billing_country' : 'shipping_country' );
            $states      = WC()->countries->get_states( $for_country );
 
            if ( is_array( $states ) && empty( $states ) ) {
 
                $field_container = '<p class="form-row %1$s" id="%2$s" style="display: none">%3$s</p>';
 
                $field .= '<input type="hidden" class="hidden" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="" ' . implode( ' ', $custom_attributes ) . ' placeholder="' . esc_attr( $args['placeholder'] ) . '" readonly="readonly" data-input-classes="' . esc_attr( implode( ' ', $args['input_class'] ) ) . '"/>';
 
            } elseif ( ! is_null( $for_country ) && is_array( $states ) ) {
 
                $field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="state_select ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ? $args['placeholder'] : esc_html__( 'Select an option&hellip;', 'woocommerce' ) ) . '"  data-input-classes="' . esc_attr( implode( ' ', $args['input_class'] ) ) . '">
                    <option value="">' . esc_html__( 'Select an option&hellip;', 'woocommerce' ) . '</option>';
 
                foreach ( $states as $ckey => $cvalue ) {
                    $field .= '<option value="' . esc_attr( $ckey ) . '" ' . selected( $value, $ckey, false ) . '>' . esc_html( $cvalue ) . '</option>';
                }
 
                $field .= '</select>';
 
            } else {
 
                $field .= '<input type="text" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( $value ) . '"  placeholder="' . esc_attr( $args['placeholder'] ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" ' . implode( ' ', $custom_attributes ) . ' data-input-classes="' . esc_attr( implode( ' ', $args['input_class'] ) ) . '"/>';
 
            }
 
            break;
        case 'textarea':
            $field .= '<textarea name="' . esc_attr( $key ) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" ' . ( empty( $args['custom_attributes']['rows'] ) ? ' rows="2"' : '' ) . ( empty( $args['custom_attributes']['cols'] ) ? ' cols="5"' : '' ) . implode( ' ', $custom_attributes ) . '>' . esc_textarea( $value ) . '</textarea>';
 
            break;
        case 'checkbox':
            $field = '<label class="checkbox ' . implode( ' ', $args['label_class'] ) . '" ' . implode( ' ', $custom_attributes ) . '>
                    <input type="' . esc_attr( $args['type'] ) . '" class="input-checkbox ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="1" ' . checked( $value, 1, false ) . ' /> ' . $args['label'] . $required . '</label>';
 
            break;
        case 'text':
        case 'password':
        case 'datetime':
        case 'datetime-local':
        case 'date':
        case 'month':
        case 'time':
        case 'week':
        case 'number':
        case 'email':
        case 'url':
        case 'tel':
            $field .= '<input type="' . esc_attr( $args['type'] ) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" ' . implode( ' ', $custom_attributes ) . ' />';
 
            break;
        case 'select':
            $field   = '';
            $options = '';
 
            if ( ! empty( $args['options'] ) ) {
                foreach ( $args['options'] as $option_key => $option_text ) {
                    if ( '' === $option_key ) {
                        // If we have a blank option, select2 needs a placeholder.
                        if ( empty( $args['placeholder'] ) ) {
                            $args['placeholder'] = $option_text ? $option_text : __( 'Choose an option', 'woocommerce' );
                        }
                        $custom_attributes[] = 'data-allow_clear="true"';
                    }
                    $options .= '<option value="' . esc_attr( $option_key ) . '" ' . selected( $value, $option_key, false ) . '>' . esc_html( $option_text ) . '</option>';
                }
 
                $field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="select ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ) . '">
                        ' . $options . '
                    </select>';
            }
 
            break;
        case 'radio':
            $label_id .= '_' . current( array_keys( $args['options'] ) );
 
            if ( ! empty( $args['options'] ) ) {
                foreach ( $args['options'] as $option_key => $option_text ) {
                    $field .= '<input type="radio" class="input-radio ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( $option_key ) . '" name="' . esc_attr( $key ) . '" ' . implode( ' ', $custom_attributes ) . ' id="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '"' . checked( $value, $option_key, false ) . ' />';
                    $field .= '<label for="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '" class="radio ' . implode( ' ', $args['label_class'] ) . '">' . esc_html( $option_text ) . '</label>';
                }
            }
 
            break;
    }
 
    if ( ! empty( $field ) ) {
        $field_html = '';
 
        if ( $args['label'] && 'checkbox' !== $args['type'] ) {
            $field_html .= '<label for="' . esc_attr( $label_id ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>';
        }
 
        $field_html .= '<span class="woocommerce-input-wrapper">' . $field;
 
        if ( $args['description'] ) {
            $field_html .= '<span class="description" id="' . esc_attr( $args['id'] ) . '-description" aria-hidden="true">' . wp_kses_post( $args['description'] ) . '</span>';
        }
 
        $field_html .= '</span>';
 
        $container_class = esc_attr( implode( ' ', $args['class'] ) );
        $container_id    = esc_attr( $args['id'] ) . '_field';
        $field           = sprintf( $field_container, $container_class, $container_id, $field_html );
    }
 
    /**
     * Filter by type.
     */
    $field = apply_filters( 'woocommerce_form_field_' . $args['type'], $field, $key, $args, $value );
 
    /**
     * General filter on form fields.
     *
     * @since 3.4.0
     */
    //$field = apply_filters( 'woocommerce_form_field', $field, $key, $args, $value );
 
    if ( $args['return'] ) {
        return $field;
    } else {
        echo $field; // WPCS: XSS ok.
        //die('here');
    }
}

add_action("wp_ajax_getstate",  "getstatebycountry");
add_action("wp_ajax_nopriv_getstate",  "getstatebycountry");

function getstatebycountry(){
    extract($_REQUEST);
    $countries_obj   = new WC_Countries();
    $default_county_states = $countries_obj->get_states( $country );
    echo json_encode(['status'=>200, 'states' =>$default_county_states]);die;
}


if (function_exists('acf_add_options_page')) {
    acf_add_options_page(array(
        'page_title' => 'Kitz Settings',
        'menu_title' => 'Kitz Settings',
        'menu_slug' => 'kitz-settings',
        'capability' => 'edit_posts',
        'redirect' => false
    ));
}









add_action('woocommerce_product_after_variable_attributes', 'variation_settings_fields', 10, 3);
add_action('woocommerce_product_after_variable_attributes', 'variation_settings_hostingfields', 10, 3);
add_action('woocommerce_save_product_variation', 'save_variation_settings_fields', 10, 2);
add_filter('woocommerce_available_variation', 'load_variation_settings_fields');

function variation_settings_fields($loop, $variation_data, $variation) {
    woocommerce_wp_text_input(
            array(
                'id' => "project_limit{$loop}",
                'name' => "project_limit[{$loop}]",
                'value' => get_post_meta($variation->ID, 'project_limit', true),
                'label' => __('Project Limit', 'woocommerce'),
                'desc_tip' => true,
                'description' => __('Add Project Limit Here', 'woocommerce'),
                'wrapper_class' => 'form-row form-row-full',
            )
    );
}

function variation_settings_hostingfields($loop, $variation_data, $variation) {
    woocommerce_wp_text_input(
            array(
                'id' => "hosting_limit{$loop}",
                'name' => "hosting_limit[{$loop}]",
                'value' => get_post_meta($variation->ID, 'hosting_limit', true),
                'label' => __('Hosting Limit', 'woocommerce'),
                'desc_tip' => true,
                'description' => __('Add Hosting Limit Here', 'woocommerce'),
                'wrapper_class' => 'form-row form-row-full',
            )
    );
}

function save_variation_settings_fields($variation_id, $loop) {
    $text_field = $_POST['project_limit'][$loop];
    $hosting_limit = $_POST['hosting_limit'][$loop];

    if (!empty($text_field)) {
        update_post_meta($variation_id, 'project_limit', esc_attr($text_field));
    }

    if (!empty($hosting_limit)) {
        update_post_meta($variation_id, 'hosting_limit', esc_attr($hosting_limit));
    }
}

function load_variation_settings_fields($variation) {
    $variation['project_limit'] = get_post_meta($variation['variation_id'], 'project_limit', true);
    $variation['hosting_limit'] = get_post_meta($variation['variation_id'], 'hosting_limit', true);

    return $variation;
}

//Thank You hook for increase project limit
add_action('woocommerce_thankyou', 'wh_test_1', 10, 1);


function wh_test_1($order_id) { 
    if ( ! $order_id )
        return;

    global $wpdb;
    global $current_user, $wp_roles;
    $user_id = $current_user->ID;

    $api_order_id = get_user_meta(get_current_user_id(), "SMMAPI_order_$order_id", true);
    $order = wc_get_order($order_id);
    $order_items = $order->get_items();
    $cats_id=[];
    
    

    foreach ($order_items as $item_id => $item_data) {
        global $wpdb;
        $product_id = $item_data->get_product_id(); 
        $post_title=get_the_title($product_id);
        $current_catsid = wp_get_post_terms( $product_id, 'product_cat', array('fields' => 'ids') );
        $conn_site = $wpdb->prefix . 'bloxx_apis';
        $cats_id = array_merge($cats_id, $current_catsid);

        if(in_array(866, $cats_id)){   //Plan Registered

            update_user_meta($user_id, "registered_plan", $post_title);
            update_user_meta($user_id, "current_plan", $product_id);
            update_user_meta($user_id, "current_plan_selected", $post_title);


            if($product_id==104698){
                //$user_limit=get_post_meta($product_id, "assets", true);
                $api_connection= 100000;
                //$fragments=get_post_meta($product_id, "fragplugin_limitments", true);
                $neo_builder= "yes";
                //$neo_writer=get_post_meta($product_id, "neo_writer", true);

                $apis_data = array('plugin_limit' => $api_connection);
            } else {
                //$user_limit=get_post_meta($product_id, "assets", true);
                $api_connection=get_post_meta($product_id, "api_limit", true);
                //$fragments=get_post_meta($product_id, "fragplugin_limitments", true);
                $neo_builder=get_post_meta($product_id, "neo_builder", true);
                //$neo_writer=get_post_meta($product_id, "neo_writer", true);
                $apis_data = array('plugin_limit' => $api_connection);
            }

            $wpdb->update($conn_site, $apis_data, array('user_id' => $user_id, 'plugin_limit'=> 1));
            update_user_meta($user_id, 'assets_limit', 5000);
            update_user_meta($user_id, 'plan_purchased', "yes");
            update_user_meta($user_id, 'api_limit', $api_connection);
            //update_user_meta($user_id, 'fragment_limit', $fragments);
            update_user_meta($user_id, 'neo_builder', $neo_builder);
            //update_user_meta($user_id, 'neo_writter', $neo_writer);
            update_user_meta($user_id, 'neo_orderid', $order_id);
            update_user_meta($user_id, "is_purchase_plan", "yes");
        }
    }
}


add_action('ywsbs_analytics_update_order_stats', 'update_user_subs', 99, 2);

function update_user_subs($order_id, $subscription_id){
    $user_id = get_post_meta($order_id, '_customer_user', true);
    update_user_meta($user_id, "subs_id", $subscription_id);
    return;
}



function plan_payment($redirect, $args) {
    //echo "<pre>";print_r($args);die;
    global $wpdb;
    $invoice_key = $args['invoice_key'];
    $invoice = $wpdb->prefix . 'getpaid_invoices';
    $invoice_item = $wpdb->prefix . 'getpaid_invoice_items';

    $invoice_query = "select iitem.item_id from $invoice i join $invoice_item iitem on i.post_id=iitem.post_id where i.key='$invoice_key'";

    $item_result = $wpdb->get_row($invoice_query);

    $item_id = $item_result->item_id;

    $terms = get_the_terms($item_id, 'subjects');
    $subs_type ='';

    foreach ($terms as $selected_terms):
        if ($selected_terms->term_id == 370) {
            $subs_type = "hosting";
        } else if ($selected_terms->term_id == 367) {
            $subs_type = "subscription";
        }
    endforeach;

    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    if ($subs_type == "subscription") {
        $project_limit = get_field('project_limit', $item_id);
        $page_limit = get_field('page_limit', $item_id);
        $section_limit = get_field('section_limit', $item_id);
        $plugin_limit = get_field('plugin_limit', $item_id);
        update_user_meta($user_id, 'project_limit', $project_limit);
        update_user_meta($user_id, 'page_limit', $page_limit);
        update_user_meta($user_id, 'section_limit', $section_limit);
        update_user_meta($user_id, 'plugins_limit', $plugin_limit);
        update_user_meta($user_id, 'current_plan', $item_id);
        $redirect=site_url()."/plans";
        return $redirect;
    } else if($subs_type == "hosting") {
        $last_limit = get_user_meta($user_id, 'project_limit', true);
        @$project_limit = @$last_limit + 1;
        update_user_meta($user_id, 'project_limit', $project_limit);
        update_user_meta($user_id, 'hosting_planid', $item_id);
        if(isset($args['invoice_key']) && !empty($args['invoice_key'])){
            global $apps;
            $apps->schedulenewapp();
        }
    }else{
        $redirect=$_SERVER['HTTP_REFERER'];
       return $redirect; 
    }   

    return $redirect;
}

add_filter('wpinv_send_to_success_page_url', 'plan_payment', 99, 2);

function profile_details() {
    ob_start();
    global $wp_roles;
    global $ultimatemember;
    $user = wp_get_current_user();
    $current_user_id = $user->ID;

    $current_plan = get_user_meta($current_user_id, 'current_plan_selected', true);

    if ($current_plan != "") {
        $plan_title = str_replace("Yearly Plan", "", str_replace("Monthly Plan", "", get_the_title($current_plan)));
    } else {
        $plan_title = "7-days trial";
    }

    $display_nm = get_user_meta($current_user_id, "display_name", true);
    $timestemp = strtotime(date("Y-m-d H:i:s"));
    $nonce = wp_create_nonce('um_upload_nonce-' . $timestemp);

    um_fetch_user($current_user_id);

    $user_profile = get_user_meta($current_user_id, "profile_photo", true);

    $avatar_uri = um_get_avatar_uri(um_profile('profile_photo'), 32);
    if ($user_profile == "") {
        $avatar_uri = builder_url . "images/profile-icon.png";
    }
    ?>
    <?php if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
 
    $count = WC()->cart->cart_contents_count;

    if ( $count == 0 ) {
        $cart_url=site_url("/plans");
    } else {
        $cart_url= wc_get_checkout_url();
    }

    ?>
    <a class="cart-contents" href="<?php echo $cart_url; ?>" title="<?php _e( 'View your shopping cart' ); ?>"><?php 
    //if ( $count > 0 ) {
        ?>
        <i class="fas fa-shopping-cart"></i><span class="cart-contents-count"><?php echo esc_html( $count ); ?></span>
        <?php
    //}
        ?>
    </a>
 
<?php } ?>
    <a class="buttonView" href="javascript:void(0)">
        <img src="<?= $avatar_uri; ?>" />
    </a>
    <ul class="dropdownList">
        <div class="um-header">                         
            <div class="um-profile-photo um-trigger-menu-on-click" data-user_id="<?= $current_user_id ?>">
                <a href="<?php echo site_url(); ?>/user/<?= $display_nm; ?>/" class="um-profile-photo-img" title="<?= $display_nm; ?>" style="margin:0">
                    <span class="um-profile-photo-overlay">
                        <span class="um-profile-photo-overlay-s">
                            <ins><i class="um-faicon-camera"></i></ins>
                        </span>
                    </span>
                    <img width="190" height="190" alt="<?= $display_nm; ?>" data-default="<?= $avatar_uri; ?>" data-src="<?= $avatar_uri; ?>" class="gravatar avatar avatar-190 um-avatar um-avatar-default lazyloaded" src="<?= $avatar_uri; ?>">
                </a>
                <?= $display_nm; ?>

                <div style="display: none !important;">
                    <div id="um_field__profile_photo" class="um-field um-field-image  um-field-profile_photo um-field-image um-field-type_image" data-key="profile_photo" data-mode="" data-upload-label="Upload">
                        <input type="hidden" name="profile_photo" id="profile_photo" value="profile_photo.png">

                        <div class="um-field-label">
                            <label for="profile_photo">Change your profile photo</label>
                            <div class="um-clear"></div>
                        </div>

                        <div class="um-field-area" style="text-align: center;">

                            <div class="um-single-image-preview crop" data-crop="square" data-key="profile_photo" style="display: block;">
                                <a href="javascript:void(0);" class="cancel"><i class="um-icon-close"></i></a>
                                <img src="<?php echo site_url(); ?>/wp-content/uploads/ultimatemember/<?= $current_user_id ?>/profile_photo.png?1629268323315?1629268326415" alt="">
                                <div class="um-clear"></div>
                            </div>

                            <a href="javascript:void(0);" data-modal="um_upload_single" data-modal-size="normal" data-modal-copy="1" class="um-button um-btn-auto-width">Change photo</a>
                        </div>

                        <div class="um-modal-hidden-content">
                            <div class="um-modal-header"> Change your profile photo</div>
                            <div class="um-modal-body">
                                <div class="um-single-image-preview crop" data-crop="square" data-ratio="1" data-min_width="190" data-min_height="190" data-coord="">
                                    <a href="javascript:void(0);" class="cancel"><i class="um-icon-close"></i></a>
                                    <img src="" alt="">
                                    <div class="um-clear"></div>
                                </div>

                                <div class="um-clear"></div>

                                <div class="um-single-image-upload" data-user_id="<?= $current_user_id ?>" data-nonce="<?= $nonce; ?>" data-timestamp="<?= $timestemp; ?>" data-icon="um-faicon-camera" data-set_id="0" data-set_mode="" data-type="image" data-key="profile_photo" data-max_size="999999999" data-max_size_error="This image is too large!" data-min_size_error="This image is too small!" data-extension_error="Sorry this is not a valid image." data-allowed_types="gif,jpg,jpeg,png" data-upload_text="Upload your profile image" data-max_files_error="You can only upload one image" data-upload_help_text="">Upload</div>

                                <div class="um-modal-footer">
                                    <div class="um-modal-right">
                                        <a href="javascript:void(0);" class="um-modal-btn um-finish-upload image disabled" data-key="profile_photo" data-change="Change photo" data-processing="Processing..."> Apply</a>
                                        <a href="javascript:void(0);" class="um-modal-btn alt" data-action="um_remove_modal"> Cancel</a>
                                    </div>
                                    <div class="um-clear"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="um-dropdown" data-element="div.um-profile-photo" data-position="bc" data-trigger="click" style="top: 43.5px; width: 200px; left: -25px; right: auto; text-align: center; display: none;">
                    <div class="um-dropdown-b">
                        <div class="um-dropdown-arr" style="top: -17px; left: 87px; right: auto;"><i class="um-icon-arrow-up-b"></i>
                        </div>

                        <ul>
                            <li><a href="javascript:void(0);" class="um-manual-trigger" data-parent=".um-profile-photo" data-child=".um-btn-auto-width">Upload photo</a></li>
                            <li><a href="javascript:void(0);" class="um-dropdown-hide">Cancel</a></li>
                        </ul>
                    </div>
                </div>

                



            </div>  
            <div class="the_coin_img">
                    <a href="/writer" target="_blank">
                    
                   <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/coin_img.png" style="max-width: 25px;">
                   <?php 
    
                   $user_credit = get_user_meta(get_current_user_id(),'writesonic_credit',true);//print_r($user_credit);
                    if(empty($user_credit)){
                        $user_credit = 0;
                    }
                    echo $user_credit;
                   ?>
               </a>
                </div>                       
        </div>

        <li><a href="<?= site_url() . '/kitz-account/'; ?>">Kitz Settings</a></li>
        <div class="dividerLine"></div>
        <!-- <li><a href="<?php // echo builder_url.'assets/addons/bloxx.zip'; ?>" download class="default-btn">Download Bloxx Plugin <i class="fas fa-download"></i></a></li> -->
        <!--<li><a href="javascript:void(0)" class="modalBtn">Hosting <span class="badge">Lite</span></a></li>-->

        <li class="hideo"><a href="<?php echo site_url(); ?>/plans/">Plan <span class="badge"><?= $plan_title; ?></span></a></li>
        <!-- <li><a href="javascript:;">Billing History</a></li> -->
        <div class="dividerLine"></div>
        <?php if (isset($current_plan)) { ?>
            <li><a href="<?php echo site_url(); ?>/billing/">Billing History</a></li>
        <?php } ?>
<!--        <li><a href="javascript:;">Language</a></li>-->
        <li><a href="javascript:void(0)" onclick="jQuery('#support_modal').show();">Support</a></li>
        <!-- <li><a href="<?php //echo site_url(); ?>/bloxx-account/?tab=apikey">API Keys</a></li> -->
        <!-- <li><a href="javascript:;" class="modalBtn">Upgrade</a></li> -->
        <li><a href="<?php echo site_url(); ?>/logout">Logout</a></li>
        
    </ul>
    <?php
    $html = ob_get_contents();
    ob_clean();
    return $html;
}

add_shortcode('profile_details', 'profile_details');

function buildproject_popup() {
    global $current_user;
    $user_id = $current_user->ID;
    $register_plan=get_user_meta($user_id, 'registered_plan', true);
    $divi_username = get_user_meta($current_user->ID,'divi_username',true);
    $divi_apikey = get_user_meta($current_user->ID,'divi_api_key',true);
    $count_free_app= countfreeApp($user_id);

   // echo 'app created=>'.$count_free_app;
    $current_plan_id=get_user_meta($user_id, 'current_plan', true);
    $get_project_limit = get_user_meta($user_id,'project_limit',$current_plan_id);


    // echo '<br>get_project_limit=>'.$get_project_limit; 
    ?>
    <!-- Project Assign Modal -->    
    <div id="sync_tool_modal" class="modal sync_app_created" style="display: none;">
        <div class="modal-content modal-lg">   
            <form method="post" id="app_step_forms" autocomplete="nope">
                <input type="hidden" id="siteblox_used_termid">
                <button class="bloxx_hosting"></button> 

                <div class="form_steps text-center" id="sync_step1">
                    <div class="modal-close">
                        <span class="closebtn" onclick="jQuery('#sync_tool_modal').hide();"><i class="fas fa-times"></i></span>         
                    </div>
                    <ul class="stepsProgress">
                        <li class="activeStep" id="step_1">App Name</li> 
                        <!-- <li id="step_2">Choose Child Theme</li>                   
                        <li id="step_3">Addons</li> -->
                        <!-- <li id="step_2">License Check</li> -->
                        <li id="step_4">Plan</li>
                        <li id="step_5">Payment</li>                        
                    </ul>

                    <div class="modal-header">
                        <!-- <img src="<?php //echo builder_url; ?>images/pen-icon.png" alt="..." /> -->
                        <h3 id="page_nm">Create New Application</h3>
                    </div>
                    <input id="divi" type="hidden" name="builder_option" id="divi" value="divi">
                    <input id="app_name" type="text" name="app_name" placeholder="Name your Application" required="true"/>
                    <input id="the_siteurl" type="hidden" value="<?php echo get_site_url(); ?>" />
                    <input id="the_current_userid" type="hidden" value="<?php echo get_current_user_id(); ?>" />

                    <?php if(in_array('administrator', $current_user->roles) || in_array('broker', $current_user->roles)) { ?>
                    <div class="the_app_creation">
                        <div class="form-inner">
                            <label>Is this for you or your agent?</label><br>
                            <div class="checkbox_row">
                                <div class="checkbox_col">
                                    <input type="radio" name="app_creating_for" class="app_creating_for" checked="" value="broker">Myself
                                </div>
                                <div class="checkbox_col">    
                                    <input type="radio" name="app_creating_for" class="app_creating_for" value="agent">Agent
                                </div>
                            </div>
                        </div>

                        <div class="form-sec">
                            <div class="form-inner"  id="the_app_agent_id" style="display: none;">
                                <label>Choose Agent:</label>
                                <select name="agent_id" id="agent_id" >
                                    <option value="">--Select Agent--</option>
                                    <?php 
                                        $args = array(
                                            'role'    => 'um_agent',
                                            'orderby' => 'ID',
                                            'order'   => 'DESC',
                                            'meta_query' => array(
                                                array(
                                                    'key' => 'registered_by',
                                                    'value' => 'broker',
                                                    'compare' => '='
                                                ),
                                                
                                            )
                                        );
                                        $users = get_users( $args );
                                        foreach ( $users as $user ) {
                                            $userid = $user->ID;
                                            $first_name = get_user_meta($userid,'first_name',true);
                                            $last_name = get_user_meta($userid,'last_name',true);
                                            $user_value = $first_name.' '.$last_name.' ('.$user->user_email.')';
                                            echo '<option value="'.$user->ID.'">' . $user_value . '</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="form-inner" id="the_app_payment"  style="display: none;">
                                <div class="checkbox_row">
                                    <div class="checkbox_col">
                                        <label>Payment Selection?</label>
                                    </div>
                                    <div class="checkbox_col">
                                        <input type="radio" name="app_payer" class="app_payer" value="broker" checked="checked" required="true">I am paying
                                    </div>
                                    <div class="checkbox_col">
                                        <input type="radio" name="app_payer" class="app_payer" value="agent" required="true">Agent is Paying
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php } ?> 

                    <a href="javascript:void(0)" class="default-btn app_steps" data-id="2">Next</a>
                    <a href="javascript:void(0)" class="default-btn app_steps_agent" data-id="2" style="display:none;">Next</a>
                    <a href="javascript:void(0)" class="default-btn send_email_agent" style="display: none;">Submit</a>
                </div>


                <div class="form_steps text-center" id="sync_step2" style="display:none;">
                    <div class="modal-close">
                        <span class="closebtn" onclick="jQuery('#sync_tool_modal').hide();"><i class="fas fa-times"></i></span>         
                    </div>

                    <ul class="stepsProgress">
                        <li class="activeStep" id="step_1">App Name <i class="fas fa-check-circle"></i></li>
                        <li class="activeStep" id="step_3">Plan</li>
                        <li id="step_4">Payment</li>
                    </ul>
                    <div class="modal-header">                    
                        <h3 id="page_nm">Choose your Hosting Plan</h3>
                    </div>

                    <?= do_shortcode('[agent_plan_modal_checkboxes]'); ?>
                    <a href="javascript:void(0)" class="back-btn app_back_steps" data-id="1">Back</a>

                    <input type="hidden" name="action_data" id="action_data" value="paid_app"/>

                    <!-- <a href="javascript:void(0)" data-href="< ?php echo get_site_url();?>?/add-to-cart=75783" class="default-btn skipfor_now">Skip for now</a> -->
                    <input type="hidden" name="action" value="builder_cat_saved"/>
                    <input type="hidden" name="selected_plan" value="">
                    <input type="hidden" id="card_selected" name="card_selected" value="default"/>
                    <button id="app_btn_submit" type="submit" class="default-btn" style="display:none;">Checkout</button>
                    <button class="btn btn-block upgradeplan_a getpaid-payment-button" type="button" data-item="" style="visibility: hidden; position: absolute;">Pay Now</button> 
                    
                </div>            
            </form>

        </div>
    </div>
    <!-- End Modal LightBox -->
    <?php
}

add_shortcode('add_project_popup', 'buildproject_popup');

function collaborat_result($term_4id) {
    global $wpdb;
    global $ultimatemember;

    $table_nm = $wpdb->prefix . 'collaborator_manage';
    $mta_query = "select * from $table_nm where term_id='$term_4id' and invite='2'";
    $meta_result = $wpdb->get_results($mta_query);
    $count_meta = count($meta_result);

    $collaborat_data = "";

    if ($count_meta != 0) {
        if ($count_meta < 6) {
            $totla_coll = $count_meta;
        } else {
            $totla_coll = "5+";
        }

        foreach ($meta_result as $coll_users):
            $collaborat = $coll_users->collab_user_id;
            $display_nm = get_user_meta($collaborat, "display_name", true);
            um_fetch_user($collaborat);
            $user_image = um_get_avatar_uri(um_profile('profile_photo'), 32);
            //$user_image="?1632530376";            
            $upload_image = explode("?", $user_image);

            if (@$upload_image[0] == "") {
                $user_image = builder_url . "images/profile-icon.png";
            }
            $coll_data[] = array(
                "coll_img" => $user_image,
                "coll_nm" => $display_nm,
                'total_coll_count' => $count_meta
            );
        endforeach;

        $result = array(
            'coll_resp' => 'yes_coll',
            'total_call' => $totla_coll,
            'col_data' => $coll_data
        );
    } else {
        $result = array(
            'coll_resp' => 'no_coll',
            'col_data' => ""
        );
    }
    return $result;
}

add_filter('collaborat_result', 'collaborat_result');

function users_all_collaborators($user_id) {
    global $wpdb;
    global $ultimatemember;

    $builder_project = get_terms(
            array(
                'taxonomy' => 'project_categories',
                'hide_empty' => false,
                'orderby' => 'date',
                'sort_order' => 'desc',
                'number' => 20,
                'meta_query' => array(
                    array(
                        'key' => 'builder_cat_user',
                        'value' => $user_id,
                        'compare' => '='
                    )
                )
            )
    );

    if (isset($builder_project) && !empty($builder_project)) {
        $total_coll = 0;
        $coll_data = $collab_data = $term_ids = array();

        foreach ($builder_project as $builder_cats):
            $term_id = $builder_cats->term_id;
            $term_ids[] = $term_id;
        endforeach;

        if (isset($term_ids) && !empty($term_ids)) {
            $table_nm = $wpdb->prefix . 'collaborator_manage';
            $my_terms = implode("','",$term_ids);
            $mta_query = "select * from $table_nm where term_id in ('" . $my_terms . "') and invite='2'";
            $meta_result = $wpdb->get_results($mta_query);

            $count_meta = count($meta_result);
            $collaborat_data = "";




            if ($count_meta != 0) {
                if ($count_meta < 6) {
                    $totla_coll = $count_meta;
                } else {
                    $totla_coll = "5+";
                }

                foreach ($meta_result as $coll_users):
                    $collaborat = $coll_users->collab_user_id;
                    $display_nm = get_user_meta($collaborat, "display_name", true);
                    um_fetch_user($collaborat);
                    $user_image = um_get_avatar_uri(um_profile('profile_photo'), 32);
                    //$user_image="?1632530376";            
                    $upload_image = explode("?", $user_image);

                    if (@$upload_image[0] == "") {
                        $user_image = builder_url . "images/profile-icon.png";
                    }
                    $collab_data[] = array(
                        'coll_images' => $user_image,
                        'coll_nm' => $display_nm,
                        'coll_total' => $count_meta,
                        'message' => 'Collaborators Team retrieve successfully'
                    );
                endforeach;
                $insert_array = array(
                    'coll_resp' => 'yes_coll',
                    'testRock' => "Bloxx",
                );
                $coll_data['team_collaborat'] = $collab_data;
                $final_array = array_merge($insert_array, $coll_data);
            } else {
                $final_array = array(
                    'coll_resp' => 'no_coll',
                    'col_data' => ""
                );
            }
        } else {
            $final_array = array(
                'coll_resp' => 'no_coll',
                'col_data' => ""
            );
        }
    } else {
        $final_array = array(
            'coll_resp' => 'no_coll',
            'col_data' => ""
        );
    }

//    echo "<pre>";
//    print_r($final_array);
//    echo "</pre>";


    return $final_array;
}

add_filter('collaborat_users_result', 'users_all_collaborators');





function users_all_collaborators_info($user_id) {
    global $wpdb;
    global $ultimatemember;
    $builder_project = get_terms(
            array(
                'taxonomy' => 'project_categories',
                'hide_empty' => false,
                'orderby' => 'date',
                'sort_order' => 'desc',
                'number' => 20,
                'meta_query' => array(
                    array(
                        'key' => 'builder_cat_user',
                        'value' => $user_id,
                        'compare' => '='
                    )
                )
            )
    );

    if (isset($builder_project) && !empty($builder_project)) {
        $total_coll = 0;
        $coll_data = $collab_data = $term_ids = array();

        foreach ($builder_project as $builder_cats):
            $term_id = $builder_cats->term_id;
            $term_ids[] = $term_id;
        endforeach;

        


        if (isset($term_ids) && !empty($term_ids)) {
            $table_nm = $wpdb->prefix . 'collaborator_manage';
            $my_terms = implode("','",$term_ids);
            $mta_query = "select * from $table_nm where term_id in ('" . $my_terms . "') and invite='2'";
            $meta_result = $wpdb->get_results($mta_query);

            $count_meta = count($meta_result);
            $collaborat_data = "";
            $term_name=array();
            $site_url=site_url('builder-projects/')."";
            if ($count_meta != 0) {
                if ($count_meta < 6) {
                    $totla_coll = $count_meta;
                } else {
                    $totla_coll = "5+";
                }

                foreach ($meta_result as $coll_users):
                    $collaborat = $coll_users->collab_user_id;
                    $display_nm = get_user_meta($collaborat, "display_name", true);
                    $author_obj = get_user_by('id', $collaborat);
                    $collab_email = $author_obj->user_email;
                    um_fetch_user($collaborat);
                    $user_image = um_get_avatar_uri(um_profile('profile_photo'), 32);
                    //$user_image="?1632530376";            
                    $upload_image = explode("?", $user_image);


                    //Assigned Project List
                    $collab_table = $wpdb->prefix . 'collaborator_manage';
                    $project_query = "select * from $collab_table where collab_user_id='$collaborat' and invite='2'";
                    $project_result = $wpdb->get_results($project_query);
                    $count_meta = count($project_result);
                    if($count_meta!=0){

                        foreach ($project_result as $assign_projects):
                            $terms_id=$assign_projects->term_id;

                            $category_data = get_term_by('id', $terms_id, 'project_categories');
                            $project_link=$site_url."?term_id=".$terms_id;
                            $term_name[] = "<a href='".$project_link."'>".ucwords($category_data->name). "</a>";
                        endforeach;
                        $assing_projectnm=implode(', ', $term_name);
                    } else {
                        $assing_projectnm= "-N/A-";
                    }
                    //End Assigned Project List



                    if (@$upload_image[0] == "") {
                        $user_image = builder_url . "images/profile-icon.png";
                    }
                    $collab_data[] = array(
                        'coll_images' => $user_image,
                        'coll_nm' => $display_nm,
                        'coll_email' => $collab_email,
                        'terms_nm' => $assing_projectnm,
                        'coll_total' => $count_meta,
                        'message' => 'Collaborators Team retrieve successfully'
                    );
                endforeach;
                $insert_array = array(
                    'coll_resp' => 'yes_coll',
                    'testRock' => "Bloxx",
                );
                $coll_data['team_collaborat'] = $collab_data;
                $final_array = array_merge($insert_array, $coll_data);
            } else {
                $final_array = array(
                    'coll_resp' => 'no_coll',
                    'col_data' => ""
                );
            }
        } else {
            $final_array = array(
                'coll_resp' => 'no_coll',
                'col_data' => ""
            );
        }
    } else {
        $final_array = array(
            'coll_resp' => 'no_coll',
            'col_data' => ""
        );
    }

    return $final_array;
}

add_filter('collaborat_users_result_info', 'users_all_collaborators_info');



//Payment Getway Setup
function payment_listing_html($html, $data) {
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $card_selected = get_user_meta($user_id, 'card_selected', true);
    $user_card_details = get_user_meta($user_id, 'getpaid_stripe_tokens', true);

    if($card_selected=="none" || empty($card_selected)){
        ?>
        <script>
            jQuery(function($){
                $("ul.getpaid-saved-payment-methods li:last-child").find("input").trigger("click");
            });
        </script>
        <?php
    } else {
        if (isset($user_card_details) && !empty($user_card_details)) {
            $card_data = $user_card_details[$card_selected];
            $last_four = substr($card_data['name'], -4);
            ?>
            <script>
                jQuery(function ($) {
                    $("ul.getpaid-saved-payment-methods li:last-child").find("input").trigger("click");

                    $(".getpaid-saved-payment-methods li.getpaid-payment-method.form-group").each(function () {
                        var card_text = $(this).find("span").text();
                        card_text = parseInt(card_text.replace(/[^0-9.]/g, ""));
                        if (card_text == "<?php echo $last_four ?>") {
                            $(this).addClass("testRock").find("label").trigger("click");
                        }
                    });
                });
            </script>
            <?php
        } else {
            ?>
            <script>
            jQuery(function($){
                $("ul.getpaid-saved-payment-methods li:last-child").find("input").trigger("click");
            });
            </script>
            <?php
        } 
    }
    return $html;
}

add_filter('getpaid_payment_gateway_form_saved_payment_methods_html', 'payment_listing_html', 99, 2);


add_filter('redirect_canonical','pif_disable_redirect_canonical');

function pif_disable_redirect_canonical($redirect_url) {
    if (is_singular()) $redirect_url = false;
    return $redirect_url;
}


function child_et_pb_register_posttypes() { $labels = array( 'add_new' => __( 'Add New', 'Divi' ),
    'add_new_item' => __( 'Add New Section', 'Divi' ),
    'all_items' => __( 'All Section', 'Divi' ),
    'edit_item' => __( 'Edit Section', 'Divi' ),
    'menu_name' => __( 'Sections', 'Divi' ),
    'name' => __( 'Section', 'Divi' ),
    'new_item' => __( 'New Section', 'Divi' ),
    'not_found' => __( 'Nothing found', 'Divi' ),
    'not_found_in_trash' => __( 'Nothing found in Trash', 'Divi' ),
    'parent_item_colon' => '',
    'search_items' => __( 'Search Section', 'Divi' ),
    'singular_name' => __( 'Section', 'Divi' ),
    'view_item' => __( 'View Section', 'Divi' ),
);

$args = array(
    'can_export' => true,
    'capability_type' => 'post',
    'has_archive' => true,
    'hierarchical' => false,
    'labels' => $labels,
    'menu_icon' => 'dashicons-admin-home',
    'menu_position' => 5,
    'public' => true,
    'publicly_queryable' => true,
    'query_var' => true,
    'show_in_nav_menus' => true,
    'show_ui' => true,
    'rewrite' => apply_filters( 'et_project_posttype_rewrite_args', array(
    'feeds' => true,
    'slug' => 'project',
    'with_front' => false,
)),
'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'revisions', 'custom-fields' ),
);

register_post_type( 'project', apply_filters( 'et_project_posttype_args', $args ) );

$labels = array(
    'name' => _x( 'Categories', 'Section category name', 'Divi' ),
    'singular_name' => _x( 'Category', 'Section category singular name', 'Divi' ),
    'search_items' => __( 'Search Categories', 'Divi' ),
    'all_items' => __( 'All Categories', 'Divi' ),
    'parent_item' => __( 'Parent Category', 'Divi' ),
    'parent_item_colon' => __( 'Parent Category:', 'Divi' ),
    'edit_item' => __( 'Edit Category', 'Divi' ),
    'update_item' => __( 'Update Category', 'Divi' ),
    'add_new_item' => __( 'Add New Category', 'Divi' ),
    'new_item_name' => __( 'New Category Name', 'Divi' ),
    'menu_name' => __( 'Categories', 'Divi' ),
);

register_taxonomy( 'project_category', array( 'project' ), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_admin_column' => true,
    'query_var' => true,
) );

$labels = array(
    'name' => _x( 'Tags', 'Section Tag name', 'Divi' ),
    'singular_name' => _x( 'Tag', 'Section tag singular name', 'Divi' ),
    'search_items' => __( 'Search Tags', 'Divi' ),
    'all_items' => __( 'All Tags', 'Divi' ),
    'parent_item' => __( 'Parent Tag', 'Divi' ),
    'parent_item_colon' => __( 'Parent Tag:', 'Divi' ),
    'edit_item' => __( 'Edit Tag', 'Divi' ),
    'update_item' => __( 'Update Tag', 'Divi' ),
    'add_new_item' => __( 'Add New Tag', 'Divi' ),
    'new_item_name' => __( 'New Tag Name', 'Divi' ),
    'menu_name' => __( 'Tags', 'Divi' ),
);

register_taxonomy( 'project_tag', array( 'project' ), array(
    'hierarchical' => false,
    'labels' => $labels,
    'show_ui' => true,
    'show_admin_column' => true,
    'query_var' => true,
) );

$labels = array(
    'name' => _x( 'Layouts', 'Layout type general name', 'Divi' ),
    'singular_name' => _x( 'Layout', 'Layout type singular name', 'Divi' ),
    'add_new' => _x( 'Add New', 'Layout item', 'Divi' ),
    'add_new_item' => __( 'Add New Layout', 'Divi' ),
    'edit_item' => __( 'Edit Layout', 'Divi' ),
    'new_item' => __( 'New Layout', 'Divi' ),
    'all_items' => __( 'All Layouts', 'Divi' ),
    'view_item' => __( 'View Layout', 'Divi' ),
    'search_items' => __( 'Search Layouts', 'Divi' ),
    'not_found' => __( 'Nothing found', 'Divi' ),
    'not_found_in_trash' => __( 'Nothing found in Trash', 'Divi' ),
    'parent_item_colon' => '',
);


$labels = array(
    'name' => _x( 'Vendors', 'Section Vendor name', 'Divi' ),
    'singular_name' => _x( 'Vendor', 'Section Vendor singular name', 'Divi' ),
    'search_items' => __( 'Search Vendors', 'Divi' ),
    'all_items' => __( 'All Vendors', 'Divi' ),
    'parent_item' => __( 'Parent Vendor', 'Divi' ),
    'parent_item_colon' => __( 'Parent Vendor:', 'Divi' ),
    'edit_item' => __( 'Edit Vendor', 'Divi' ),
    'update_item' => __( 'Update Vendor', 'Divi' ),
    'add_new_item' => __( 'Add New Vendor', 'Divi' ),
    'new_item_name' => __( 'New Vendor Name', 'Divi' ),
    'menu_name' => __( 'Vendors', 'Divi' ),
);

register_taxonomy( 'vendor', array( 'layouts' ), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_admin_column' => true,
    'query_var' => true,
) );


$args = array(
    'labels' => $labels,
    'public' => true,
    'can_export' => true,
    'query_var' => false,
    'has_archive' => false,
    'capability_type' => 'post',
    'hierarchical' => false,
    'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'revisions', 'custom-fields' ),
);

register_post_type( 'et_pb_layout', apply_filters( 'et_pb_layout_args', $args ) );
}

function remove_et_pb_actions() {
    remove_action( 'init', 'et_pb_register_posttypes', 15 );
}

add_action( 'init', 'remove_et_pb_actions');
add_action( 'init', 'child_et_pb_register_posttypes', 20 );




function reload_cssjs(){
    global $wpdb;
    global $wp_query;
    @$page_id= $wp_query->post->ID;
    
    if($page_id!=""){
        @$page_refresh=get_post_meta($page_id, 'page_referesh', true);
        if(@$page_refresh=="yes"){
            $posts = $wpdb->prefix . 'posts';
            $page_query = "SELECT * FROM $posts where ID='$page_id' limit 1";
            $page_data = $wpdb->get_row($page_query);
            $page_content=$page_data->post_content;

            $update = wp_update_post(
                array(
                    'ID' => $page_id,
                    'post_content' => $page_content,
                    'post_status' => "publish",
                )
            );
            update_post_meta($page_id,"page_referesh", "no");
            ?>
            <script>
                window.location.href="";
            </script>
            <?php
        }
    }
    return true;    
};

add_action('wp_head', "reload_cssjs");

add_action("wp_ajax_check_license",  "check_divilicense");
add_action("wp_ajax_nopriv_check_license", "check_divilicense");

function check_divilicense(){
global $wp_version;
global $current_user;
extract($_REQUEST);
    // Prepare settings for API request
    $options = array(
        'timeout'    => 30,
        'body'       => array(
            'action'   => 'check_hosting_card_status',
            'username' => $_POST['username'],
            'api_key'  => $_POST['api_key'],
        ),
        'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url( '/' ),
    );

    $request               = wp_remote_post( 'https://www.elegantthemes.com/api/api.php', $options );
    $request_response_code = wp_remote_retrieve_response_code( $request );
    $response_body         = wp_remote_retrieve_body( $request );
    $response = json_decode($response_body,true);
    if($response['success'] === 1 || $response['success'] === true){
        update_user_meta($current_user->ID,'divi_username',$username);
        update_user_meta($current_user->ID,'divi_api_key',$api_key);
        $result = array(
            'code' => 200,
            'message' => "Divi License Activated"
        );
    }
    echo $response_body;die;
    
}


function so_validate_add_cart_item( $passed, $product_id, $quantity, $variation_id = '', $variations= '' ) {

    // Set HERE your targeted product ID
    $target_product_id = 83076;
    // Initialising some variables
    $has_item = false;
    $is_product_id = false;

    foreach( WC()->cart->get_cart() as $key => $item ){
        
        
        // Check if we add to cart the targeted product ID
        if( $item['product_id'] == $target_product_id ){
            $is_product_id = true;
             $key_to_remove = $key;
        }
    }

    if( $is_product_id ){
        $passed = true;
        WC()->cart->remove_cart_item($key_to_remove);

    }
    return $passed;

}
add_filter( 'woocommerce_add_to_cart_validation', 'so_validate_add_cart_item', 10, 5 );


add_action("wp_ajax_cart_item",  "cart_item");
add_action("wp_ajax_nopriv_cart_item", "cart_item");

function cart_item(){
    extract($_REQUEST);    
    $product_cart_id = WC()->cart->generate_cart_id( $cart_url );
    if(!WC()->cart->find_product_in_cart( $product_cart_id )) {      
       WC()->cart->add_to_cart( $cart_url);
        $result=array(
            'code' => 200,
            'message' => 'Addon added successfully'
        );
    } else {
        $result=array(
            'code' => 202,
            'message' => 'Failed to add addons on cart'
        );
    }
    echo json_encode($result);
    die();
}



      //  echo "<pre>";print_r($request);die;


add_action('woocommerce_order_status_completed','updating_order_status_completed_with_subscription',10,1);
function updating_order_status_completed_with_subscription($order_id) {

        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id(); 
        if( wcs_order_contains_subscription( $order ) ){
             // Get an array of WC_Subscription objects
             $subscriptions = wcs_get_subscriptions_for_order( $order_id );
             foreach( $subscriptions as $subscription_id => $subscription ){
               $subscriptionid = $subscription_id;
             }
             $subscription_i = new WC_Subscription( $subscriptionid );
             $subscription_items = $subscription_i->get_items();
             foreach ( $subscription_items as $item_id => $item ) {
               $product_id = wcs_get_canonical_product_id( $item );
            }
            // check product is not hosting or subscriptions
            $term_ids = wp_get_post_terms( $product_id, 'product_cat', array('fields' => 'ids') ); // array
            if(in_array(184,$term_ids)){
                update_user_meta($user_id,"current_plan_selected",$product_id);
            }
            if(in_array(356,$term_ids)){
                update_user_meta($user_id,"hosting_planid",$product_id);
            }
         }
}



add_filter('manage_project_posts_columns', 'regenerate_imagefunction');

function regenerate_imagefunction($columns){
// Remove Author and Comments from Columns and Add custom column 1, custom column 2 and Post Id
    unset(
        $columns['author'],
        $columns['taxonomy-project_tag']
    );
    return array(
        'cb' => '<input type="checkbox" />',
        'title' => __('Title'),
        'taxonomy-project_category' => __('Categories'),
        'regenerate_image' => __('Regenerate Feature Image'),
        'date' =>__( 'Date')
    );    
}



add_action( 'manage_project_posts_custom_column', 'fill_regenerate_image_columns', 10, 2 );
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'dashicons' );
} );



function fill_regenerate_image_columns( $column, $post_id ) {
    if($column=="regenerate_image"){
        $feat_image = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'large' );
        if(isset($feat_image) && !empty($feat_image)){
            echo "<a href='javascript:void(0)' title='Generate Feature Image' class='regenerate_thumbnails thumbnail_$post_id' id='$post_id'><img src='$feat_image[0]' style='width: 260px;'/></a>";
        } else {
            echo "<a href='javascript:void(0)' title='Generate Feature Image' class='regenerate_thumbnails thumbnail_$post_id' id='$post_id'><span class='dashicons dashicons-admin-media'></span></a>";
        }
    }
    
}


function my_custom_add_to_cart_redirect( $url ) {
    $url = WC()->cart->get_checkout_url();
    if(isset($_REQUEST['ex_term_id'])){
        if (parse_url($url, PHP_URL_QUERY)){
            $url = $url.'&ex_term_id='.$_REQUEST['ex_term_id'];
        }else{
            $url = $url.'?ex_term_id='.$_REQUEST['ex_term_id'];
        }
    }
    return $url;
}
add_filter( 'woocommerce_add_to_cart_redirect', 'my_custom_add_to_cart_redirect' );


function get_order_itemid($order_id){
    global $wpdb;
    $wp_order = $wpdb->prefix . 'woocommerce_order_items';
    $order_item_query = "SELECT * FROM $wp_order where order_id='$order_id' order by order_item_id desc limit 1";
    $order_item_row = $wpdb->get_row($order_item_query);
    $order_itemid = @$order_item_row->order_item_id;
    return $order_itemid;
}

function get_plan_details($order_id, $current_plan, $current_user_id){
    global $wpdb;

    // echo '123';

    
    //$subscriptions = @WC_Subscriptions_Manager::get_users_subscriptions( $current_user_id );

    $subscriptions= array(
        "interval" => 1,
        "period" => "month",
        "status" => "active",
        "start_date" => "2022-03-25 06:52:18"
    );

  //  echo '5555';
    $plan_desc=$order_id."_".$current_plan;
    $plan_info= @$subscriptions[$plan_desc];

    //pre($plan_info);
    //Plan_price
    $order_itemid= get_order_itemid($order_id);
    $woo_price=wc_get_order_item_meta($order_itemid, '_line_total', true );
    if($woo_price==""){
        $woo_price= 0.00;
    }

    $plan_period= @$plan_info['period'];
    $plan_interval= @$plan_info['interval'];
    $plan_status= @$plan_info['status'];
    $plan_startdt= @$plan_startdt= date('M d, Y', strtotime($plan_info['start_date']));
    
    if(@$plan_period!=""){ 
        $get_end_date= date('M d, Y', strtotime(@$plan_startdt. " + $plan_interval $plan_period"));
    } else {
        $get_end_date="-N/A-";
    }


    // get subcription price // added by ak
    $wpposts = $wpdb->prefix . 'posts';
    $subs_post="select ID from $wpposts where post_parent='$order_id'";
    $subs_res = $wpdb->get_row($subs_post);
    $subs_id= @$subs_res->ID;
    $plan_subscription_price=get_post_meta($subs_id, '_order_total', true);



    $result_plan=array(
        "plan_period" => $plan_period,
        "plan_interval" => $plan_interval,
        "plan_status" => $plan_status,
        "plan_startdt" => $plan_startdt,
        "plan_enddt" => $get_end_date,
        "plan_price" => $woo_price,
        "plan_subscription_price" => $plan_subscription_price, // added by ak
    );

    return $result_plan;    
    
}


add_action( 'woocommerce_add_to_cart', function ()
{
    if(isset($_GET['app_term_id'])):
        $cookie_val = $_GET['app_term_id'];
        setcookie('app_purchase_plan', $cookie_val, time() + 300, "/");
    else:
        $cookie_val = 'new';
        setcookie('app_purchase_plan', $cookie_val, time() + 300, "/");
    endif;
});


// function createExternalApp($user_id,$app_name,$app_url, $last_id){
//     global $wpdb;
//     $conn_site = $wpdb->prefix . 'connected_sites';
//     $app_url = trim($app_url,'/').'/';
//     $sql = "SELECT * FROM `$conn_site` WHERE `site_url` = '$app_url' AND `siteblox_user_id` = '$user_id'";
//    // echo $sql;
//     $record = $wpdb->get_results($sql);

//     //pre($record);
//     $count = count($record);


//     //die('createExternalApp'.$count);
    
//     if($count==0){
//         $slug = sanitize_title($app_name);
//         $args = array('name' => $app_name, 'taxonomy' => 'project_categories');
//         $termslug = wp_unique_term_slug($slug, (object) $args);
        
//         $cid = wp_insert_term($app_name, 'project_categories', array(
//             'description' => '', 'slug' => $termslug
//         ));
//         $term_id = $cid['term_taxonomy_id'];


        

//         update_term_meta($term_id, "builder_cat_user", $user_id);
//         $subs_plan = get_user_meta($user_id, 'current_plan', true);
//         update_term_meta($term_id, 'current_plan', $subs_plan);
//         if($subs_plan==FREE_SUBSCRIPTION_PLAN_ID){
//              update_term_meta($term_id, "hosting_planid", FREE_HOSTING_PLAN_ID); // added by ak
//         }
//         // else{
//         //     update_term_meta($term_id, "hosting_planid", PAID_HOSTING_PLAN_ID); // added by ak
//         // }
       

//         update_term_meta($term_id, 'bloxx_app_id', $term_id);
//         update_term_meta($term_id, 'is_external', 'yes');
//         $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
//         $randomString = '';
      
//         for ($i = 0; $i < 10; $i++) {
//             $index = rand(0, strlen($characters) - 1);
//             $randomString .= $characters[$index];
//         }

//         $micro_time= round(microtime(true));
            
//         $sitebox_key= $randomString."==".$micro_time;

//         //wp_connected_sites Table
//         $data=array(
//             'siteblox_key'     => $sitebox_key,
//             'siteblox_user_id' => $user_id,
//             'siteblox_termid'  => $term_id,
//             'site_url'         => $app_url,
//             'is_connect'       => 1,
//             'is_external'      => 1,
//             'user_id'          => 1
//         );
//         $wpdb->insert($conn_site, $data);


//         $metaarr = (object) [
//             'label' => $app_name,
//             'app_fqdn' => trim(str_replace(['https://','http://'],'',$app_url),'/'),
//             'cname' => trim(str_replace(['https://','http://'],'',$app_url),'/')
//         ];
//         update_user_meta($user_id,'website_'.$term_id, $metaarr);



//         //wp_bloxx_apis Table
//         $bloxx_apis = $wpdb->prefix.'bloxx_apis';
//         $api_data=array(
//             'is_external'=> 1,
//             'term_id' => $term_id
//         );
//         $wpdb->update($bloxx_apis, $api_data, array('id'=>$last_id));
        
//     }else{
//         @update_term_meta($record->siteblox_termid,'is_deleted',0);
//     }
//     return true;
// }


//subscription get by order id
function order_data($term_id){
    global $wpdb;
    $order_id=get_term_meta($term_id, "hosting_orderid", true);
    $wpposts = $wpdb->prefix . 'posts';
    $subs_post="select ID from $wpposts where post_parent='$order_id'";
    //$subs_res = $wpdb->get_results($subs_post);
    $subs_res = $wpdb->get_row($subs_post);
    //$subs_id= $subs_res[0]->ID;
   // pre($subs_res);
    
    $subs_id= @$subs_res->ID;
   // echo 'subs_id=>'.$subs_id;
    $sch_start=get_post_meta($subs_id, '_schedule_start', true);
    $sch_date= date('M d, Y', strtotime($sch_start));

    $next_payment=get_post_meta($subs_id, '_schedule_next_payment', true);
    $renewal_date= date('M d, Y', strtotime($next_payment));

    $bill_period=get_post_meta($subs_id, '_billing_period', true);
    $bill_interval=get_post_meta($subs_id, '_billing_interval', true);

    // $order_price=get_post_meta($order_id, '_order_total', true); // it was order_id before
    $order_price=get_post_meta($subs_id, '_order_total', true);

    $return_array=array(
        'purchased_date'=> $sch_date, 
        'ren_date'=> $renewal_date,
        'period'=> $bill_period,
        'interval'=> $bill_interval,
        'plan_price'=> $order_price
    );
    return $return_array;
}

function shortcode_my_orders( $atts ) {
    $args= shortcode_atts( 
        array('order_counts' => -1), 
        $atts
    );
    $order_count= esc_attr( $args['order_counts'] );
    ob_start();
    wc_get_template( 'myaccount/my-orders.php', array(
        'current_user'  => get_user_by( 'id', get_current_user_id() ),
        'order_count'   => $order_count
    ) );
    return ob_get_clean();
}
add_shortcode('my_orders', 'shortcode_my_orders');



function subscription_query($term1, $term2){
    $args = array(
        'post_type' => 'product',
        'showposts' => 3,
        'order' => 'asc',
        'tax_query' => array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $term1
            ),
            array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $term2
            )
        )
    );
    $query = new WP_Query($args);
    $product = get_posts($args);
    return $product;
}

function disconnectExternalApp($user_id,$app_url){
     global $wpdb;
    $conn_site = $wpdb->prefix . 'connected_sites';
    $app_url = trim($app_url,'/').'/';
    $sql = "SELECT siteblox_termid,site_url,is_external FROM `$conn_site` WHERE `site_url` = '$app_url' AND `siteblox_user_id` = $user_id limit 1";
    $record = $wpdb->get_row($sql);
    
    
    
    if(!empty($record) && $record->site_url == $app_url && $record->is_external == "1"){
       update_term_meta($record->siteblox_termid,'is_deleted',1);
    }
    
    return true;
}


add_action( 'user_register', 'updatemeta_after_register', 10, 1 ); 
function updatemeta_after_register( $user_id ) { 
    $user_role = get_user_role_name($user_id);
    $user_limit= get_current_user_limits($user_role);
    update_user_meta($user_id, "is_purchase_plan", "no");
    $product_id= get_field('select_product', 'options');
    $prd_title= get_the_title($product_id);
    //update_user_meta($user_id, 'assets_limit', $user_limit['assets']);
    update_user_meta($user_id, 'api_limit', 1);
    //update_user_meta($user_id, 'fragment_limit', $user_limit['fragment']);
    update_user_meta($user_id, 'neo_builder', $user_limit['neo_builder']);
    //update_user_meta($user_id, 'neo_writter', $user_limit['neo_writter']);

    update_user_meta($user_id, "registered_plan", $prd_title);
    update_user_meta($user_id, "current_plan", $product_id);
    update_user_meta($user_id, "current_plan_selected", $prd_title);

    create_apis_key($user_id);
}


function create_apis_key($user_id){
    global $wpdb;
    $api_limit= get_user_meta($user_id, 'api_limit', true);
    $bloxx_apis_tb = $wpdb->prefix . 'bloxx_apis';
    $api_query = "Select * from $bloxx_apis_tb where user_id='$user_id'";
    $connected_api = $wpdb->get_results($api_query);
    $count_api = count($connected_api);
    $userdata = get_user_by('id', $user_id);
    $bloxx_username = $userdata->user_login;
    $now=date('Y-m-d H:i:s');
    //pre($connected_api);
    
    if($count_api < $api_limit){            
        $bloxx_generated_key = password_hash("bloxx_builder", PASSWORD_DEFAULT);
        $now=date('Y-m-d H:i:s');
        $data = array(
            //'api_username' => $bloxx_username,
            'api_key' => $bloxx_generated_key,
            'status' => 1,
            'user_id' => $user_id,                
            'api_token' => md5("bloxx_builder"),
            'prime_key' => 1,
            'plugin_limit'=> $api_limit,
            'created_at'=> $now
        );
        $id = $wpdb->insert($bloxx_apis_tb, $data);  

    } else {
        $id= -1;
    }
    return $id;
}


function woocommerce_order_review_custom(){
    wc_get_template(
      'checkout/review-order_custom.php',
      array(
        'checkout' => WC()->checkout(),
      )
    ); 
}

function customcart(){
    wc_get_template(
      'checkout/custom_cart.php',
      array(
        'checkout' => WC()->checkout(),
      )
    ); 
}

add_action( 'woocommerce_cart_coupon', 'woocommerce_empty_cart_button' );
function woocommerce_empty_cart_button() {
    echo '<a href="' . esc_url( add_query_arg( 'empty_cart', 'yes' ) ) . '" class="empty-cart-btn" title="' . esc_attr( 'Empty Cart', 'woocommerce' ) . '">' . esc_html( 'Empty Cart', 'woocommerce' ) . '</a>';
}

add_action( 'wp_loaded', 'woocommerce_empty_cart_action', 20 );
function woocommerce_empty_cart_action() {
    if ( isset( $_GET['empty_cart'] ) && 'yes' === esc_html( $_GET['empty_cart'] ) ) {

        WC()->cart->empty_cart();
        $plans= site_url('/plans');
        wp_redirect($plans);
        die();
        //wp_redirect($plans);
        //$referer  = wp_get_referer() ? esc_url( remove_query_arg( 'empty_cart' ) ) : wc_get_cart_url();
        //wp_safe_redirect( $referer );
        
    }
}


// function countPaidHostingApps($user_id){
//     $builder_project = get_terms(
//         array(
//             'taxonomy' => 'project_categories',
//             'hide_empty' => false,
//             'orderby' => 'date',
//             'sort_order' => 'desc',
//             'number' => 12,
//             'meta_query'=> array(
//                 'relation' => 'AND',
//                 array(
//                     'key' => 'hosting_planid',
//                     'value' => PAID_HOSTING_PLAN_ID, //IT IS PAID WORDPRESS HOSTING I.E. 75787
//                     'compare' => '='
//                 ),

//                 'meta_query' => array(
//                     'relation' => 'OR',
//                     array(
//                         'key' => 'builder_cat_user',
//                         'value' => $user_id,
//                         'compare' => '='
//                     ),
//                     array(
//                         'key' => "accept_invitation_$user_id",
//                         'value' => 'yes',
//                         'compare' => '='
//                     )
//                 )
//             )
//         )
//     );

//     return count($builder_project);
// }


function countfreeApp($user_id){
    $builder_project = get_terms(
        array(
            'taxonomy' => 'project_categories',
            'hide_empty' => false,
            'orderby' => 'date',
            'sort_order' => 'desc',
            'number' => 12,
            'meta_query'=> array(
                'relation' => 'AND',
                array(
                    'key' => 'hosting_planid',
                    'value' => FREE_HOSTING_PLAN_ID, //IT IS FREE WORDPRESS HOSTING I.E. 75783
                    'compare' => '='
                ),

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
        )
    );

    return count($builder_project);
}


function url_screenshot($url, $term_id){
    //Screenshot_capture    
    $shot_nm = "project_$term_id.png";
    $version = $url . "?screenshot=" . time();
    
    $scriptpath = "node " . siteblox_path . "/builder_nodescript.js {$version} {$shot_nm}";                    
    exec($scriptpath, $output);

    $myJSON = $output;
    $pepe = implode($myJSON);
    
    if ($pepe == "screenshot_captured") {
        $siteblox_url = siteblox_url . "project_shots/" . $shot_nm . "?v=" . time();
        //update_term_meta($term_id, 'term_image', $siteblox_url);      
    }
    return true;
}


// get all posts related to industry_id
// and get all terms related to posts (which are fetched by industry_id)
function get_bloxx_terms_filter_industry_id($industry_id, $usrs_cats, $key_user){

    global $wpdb;
    if($industry_id=='all'){
        $args = array( 'hide_empty=0', 'include' => $usrs_cats );
        $terms = get_terms( 'bloxx_categories', $args );
        $terms_array = array();
       // pre($terms);
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
            foreach ( $terms as $term ) {
                $terms_array[] = $term->term_id;
            }
           
        }else{
            $terms_array[] = array();
        }
           
    } else {

        $cat_args = array(
            'post_type' => 'layouts',
            //'meta_key' => 'premium_section',
            'orderby' => 'meta_value_num',
            //'include' => $usrs_cats,
            'order' => 'ASC',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            // 'meta_query'    => array(
            //     array(
            //         'key'       => 'builder_custom_cat_user',
            //         'value'     => $key_user,
            //         'compare'   => 'LIKE',
            //     )
            // ),

            'tax_query' => array(
                array(
                    'taxonomy' => 'service_type',
                    'field'     => 'term_id',
                    'terms' => $industry_id
                )
            )
        );


        $layout_posts = new WP_Query($cat_args);

        if ($layout_posts->have_posts()) {
            $terms_array = array();
            while ($layout_posts->have_posts()) {
                $layout_posts->the_post();
                $post_id = get_the_id();  //'include' => $usrs_cats,

                $term_list = wp_get_post_terms( $post_id, 'bloxx_categories', array( 'fields' => 'all') );

                foreach ($term_list as $key => $term) {
                    if(in_array($term->term_id,$terms_array)){

                    } else {
                        $terms_array[] = $term->term_id;
                    }
                    
                }
            }
        } 
    }

    return $terms_array; 
}



function get_bloxx_terms_by_industry_id($industry_id){

    global $wpdb;
    if($industry_id=='all'){
            $args = array( 'hide_empty=0');
            $terms = get_terms( 'bloxx_categories', $args );
            $terms_array = array();
           // pre($terms);
            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                foreach ( $terms as $term ) {
                    $terms_array[] = $term->term_id;
                }
               
            }else{
                $terms_array[] = array();
            }
           
    }else{
        $cat_args = array(
            'post_type' => 'layouts',
            //'meta_key' => 'premium_section',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            //'offset' => $ajax_offset,
            'tax_query' => array(
                array(
                    'taxonomy' => 'service_type',
                    'terms' => $industry_id
                )
            )
        );


        $layout_posts = new WP_Query($cat_args);

        if ($layout_posts->have_posts()) 
        {
            $terms_array = array();
            while ($layout_posts->have_posts()) 
            {
                $layout_posts->the_post();
                $post_id = get_the_id();
                //echo '<br>------start------<br>';
               // echo $post_id.'__'.get_the_title($post_id).'<br>';
                $term_list = wp_get_post_terms( $post_id, 'bloxx_categories', array( 'fields' => 'all' ) );
               // pre( $term_list );

                foreach ($term_list as $key => $term) {
                    if(in_array($term->term_id,$terms_array)){

                    }else{
                        $terms_array[] = $term->term_id;
                    }
                    
                }
            }
        } 
    }
    return $terms_array; 
}



add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar() {
    if (current_user_can('administrator')) {
        show_admin_bar(true);
     }
   }

function redirect_homepage() {
    if(!is_user_logged_in() && (is_home() || is_front_page() )){
       // die('1');
        wp_redirect(get_site_url().'/home',301 );
        exit;
    }
}
 


add_filter( 'show_admin_bar', '__return_false' );


@ini_set( 'upload_max_size' , '64M' );
@ini_set( 'post_max_size', '64M');
@ini_set( 'max_execution_time', '300' );



add_action("wp_ajax_send_email_agent_ajax",  "send_email_agent_ajax");
add_action("wp_ajax_nopriv_send_email_agent_ajax", "send_email_agent_ajax");

function send_email_agent_ajax(){
    //pre($_REQUEST);
    global $wpdb;
    extract($_REQUEST);
    // Prepare settings for API request
    
    $user = get_user_by('id',$agent_id);
    $agent_email =  $user->user_email;

    $first_name =  $user->first_name;
    $last_name =  $user->last_name;
    $fullname =  $first_name.' '.$last_name;

    

    // insert in separate table with pending status
    $table = $wpdb->prefix . 'bloxx_agent_apps'; 
    $data=array(
        
        'agent_id' => $agent_id,
        'app_name' => $app_name,
        'status'  => 'pending',
        'created_by'  => get_current_user_id(),
        'created_date' => date('Y-m-d H:i:s')
    );
    $inserted_temp_agent = $wpdb->insert($table, $data);
    if($inserted_temp_agent){
        $invited_agent_temp_app_id = $wpdb->insert_id;

        // send user metas in agent to terack that agent is paying when clicked link from email
        update_user_meta($agent_id,'invited_agent_id',$agent_id);
        update_user_meta($agent_id,'invited_agent_app_name',$app_name);
        update_user_meta($agent_id,'invited_agent_temp_app_id',$invited_agent_temp_app_id );
    }


    $multiple_recipients = array(
        $agent_email,
        //'infuginashish@gmail.com',
       // 'mail@enspyredigital.com'
    );

    $site_title = get_bloginfo( 'name' );
    $site_url = get_site_url();
    $reset_pass_link = $site_url.'/password-reset/';
    $subject = 'Email notification for App Payment on '.$site_title;
    $headers = array('Content-Type: text/html; charset=UTF-8');

    $purchase_plan_btn = get_site_url().'/agent-plans/?app_invite_id='.$invited_agent_temp_app_id;

    $body = 'Hi '.$fullname.',<br><br>';
    $body.= 'Your broker has registered you for a new account at '.$site_url.'. Please create your password using the link below, and select a plan for your account. <br><br>';

    $body.= 'Here is your email: '.$agent_email.'<br>';
    $body.= 'Here is link for reset password '.$reset_pass_link.'<br><br><br>';

    $body.= 'Click below button to purchase plan.<br>';
    $body.= '<a style="padding:10px 15px; color:#fff; text-decoration:none; margin-top:20px; display:inline-block; background:#b40101;" href="'.$purchase_plan_btn.'">Purchase Plan<a><br><br><br>';


     $body.= 'Sincerely,<br>
     My Broker Sites Team';
    

    $sent = wp_mail( $multiple_recipients, $subject, $body, $headers );

    if($sent) {
        $result = array(
            'code' => 200,
            'message' => "Agent notified successfully."
        );
    }else{
        $result = array(
            'code' => 201,
            'message' => "Some Error Occured!"
        );
    }
    echo json_encode($result);
    die;
}



// add agent id and app name io cart data when broker creating app for agent and paying himself as well
function ak_custom_add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {

   // pre($_REQUEST);
    if( isset( $_REQUEST['app_name'] ) ) {
        $cart_item_data['app_name'] = sanitize_text_field( $_REQUEST['app_name'] );
    }
    
    if( isset( $_REQUEST['agent_id'] ) ) {
        $cart_item_data['agent_id'] = sanitize_text_field( $_REQUEST['agent_id'] );
    }
    return $cart_item_data;
}

add_filter( 'woocommerce_add_cart_item_data', 'ak_custom_add_cart_item_data', 10, 3 );



// display cart data on cart page
function ak_custom_get_item_data( $item_data, $cart_item_data ) {

    if( isset( $cart_item_data['app_name'] ) ) {
        $item_data[] = array(
            'key' => __( 'Website', 'kws' ),
            'value' => wc_clean( $cart_item_data['app_name'] )
        );
    }

    if( isset( $cart_item_data['agent_id'] ) ) {
        $item_data[] = array(
            'key' => __( 'Agent', 'kws' ),
            'value' => wc_clean( $cart_item_data['agent_id'] )
        );
    }

    return $item_data;
}

add_filter( 'woocommerce_get_item_data', 'ak_custom_get_item_data', 10, 2 );



if(!function_exists('ak_custom_add_values_to_order_item_meta')) {
    
    add_action('woocommerce_new_order_item','ak_custom_add_values_to_order_item_meta',1,2);

    function ak_custom_add_values_to_order_item_meta($item_id, $values) {

        global $woocommerce,$wpdb;

        if(isset($values['app_name'])){
            $app_name = $values['app_name'];
            if(!empty($app_name)) {
                wc_add_order_item_meta($item_id,'Website',$app_name);  
            }
        }

        if(isset($values['agent_id'])){
            $agent_id = $values['agent_id'];
            if(!empty($agent_id)) {
                wc_add_order_item_meta($item_id,'Agent',$agent_id);  
            }
        }
    }
}


add_action('wp_head','ak_frontend_js_css_callback');
function ak_frontend_js_css_callback(){
    ?>
    <style type="text/css">
        .topMenuUser li.support{
            display: none;
        }
    </style>

    <?php
    if(is_user_logged_in()){
        $invited_agent_id = get_user_meta(get_current_user_id(),'invited_agent_id',true);
        $invited_agent_app_name = get_user_meta(get_current_user_id(),'invited_agent_app_name',true);
        $invited_agent_temp_app_id = get_user_meta(get_current_user_id(),'invited_agent_temp_app_id',true);

        if($invited_agent_id!='' && $invited_agent_temp_app_id!=''){
            ?>
            <script type="text/javascript">
                var app_name = '<?php echo $invited_agent_app_name ?>';
                var agent_id = '<?php echo $invited_agent_id ?>';
                var the_siteurl = '<?php echo get_site_url() ?>';
                
                setTimeout(function(){ 
                    
                    jQuery('.et_pb_pricing_table_wrap .et_pb_pricing_table').each(function(){
                        var str = '';
                    var res = '';

                         str = jQuery(this).find('.et_pb_pricing_table_button').attr('href');
                         res = str.split("=");
                       console.log('res=>'+res);
                        var product_id =  res[1];
                         console.log('product_id=>'+product_id);
                      //  alert(product_id);
                        var updated_href = the_siteurl+'/checkout?add-to-cart='+product_id+'&app_name='+app_name+'&agent_id='+agent_id;
                        jQuery(this).find('.et_pb_pricing_table_button').attr('href',updated_href);
                    });

                },1000);

                
            </script>

            <?php
        }

    }
}

function rename_sold_by($text) {
  $return = str_replace('Sold by', 'By', $text);
    return $return;
}
add_filter('wcvendors_sold_by_in_loop', 'rename_sold_by'); // Product Loop



add_filter( 'woocommerce_product_tabs', 'woo_custom_product_tabs' );
function woo_custom_product_tabs( $tabs ) {

    // 1) Removing tabs

    unset( $tabs['description'] );              // Remove the description tab
    // unset( $tabs['reviews'] );               // Remove the reviews tab
    unset( $tabs['additional_information'] );   // Remove the additional information tab


    // 2 Adding new tabs and set the right order

    //Attribute Description tab
    $tabs['attrib_desc_tab'] = array(
        'title'     => __( 'Item Details', 'woocommerce' ),
        'priority'  => 100,
        'callback'  => 'woo_attrib_desc_tab_content'
    );

    return $tabs;

}

// New Tab contents

function woo_attrib_desc_tab_content() {
    echo '<p>'.get_the_content().'</p>';
}


add_filter( 'woocommerce_product_tabs', 'sb_woo_move_description_tab', 98);
function sb_woo_move_description_tab($tabs) {
    $tabs['attrib_desc_tab']['priority'] = 5;
    $tabs['reviews']['priority'] = 20;
    return $tabs;
}

add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
if ( ! function_exists( 'woocommerce_template_single_rating' ) ) {

    /**
     * Output the product rating.
     */
    function woocommerce_template_single_rating() {
        if ( post_type_supports( 'product', 'comments' ) ) {
            wc_get_template( 'single-product/rating.php' );
        }
    }
}

// add_action('woocommerce_template_single_add_to_cart',function(){
//     woocommerce_template_single_rating();
// },20);

function woocommerce_after_cart_item_name(){
    global $product;
    $rating_count = $product->get_rating_count();
    $review_count = $product->get_review_count();
    $average      = $product->get_average_rating();
    $html = '';
    //if ( $rating_count > 0 ) : 
        $html .= '<div class="woocommerce-product-rating">';
        $html .= wc_get_rating_html( $average, $rating_count );
        $html .= '<div class="count">'. esc_html( $review_count ).' Reviews</div></div>';
    //endif;
    return $html;
}


// Remove product in the cart using ajax
function warp_ajax_product_remove()
{
    global $woocommerce;
    $cart = $woocommerce->cart;
    $result=array(
        'code' => 202,
        'message' => 'Please try again'
    );
   
    foreach ($woocommerce->cart->get_cart() as $cart_item_key => $cart_item){
        if($cart_item['product_id'] == $_POST['product_id'] ){
            // Remove product in the cart using  cart_item_key.
            $cart->remove_cart_item($cart_item_key);
            $result=array(
                'code' => 200,
                'message' => 'Product removed successfully'
            );
        }
    }
    echo json_encode($result);
    die();
}

add_action( 'wp_ajax_addon_product_remove', 'warp_ajax_product_remove' );
add_action( 'wp_ajax_nopriv_addon_product_remove', 'warp_ajax_product_remove' );

add_filter( 'woocommerce_available_payment_gateways', 'woocommerce_available_payment_gateways' );
function woocommerce_available_payment_gateways( $available_gateways ) {
    if (! is_checkout() ) return $available_gateways;  // stop doing anything if we're not on checkout page.
    if (array_key_exists('yith-stripe-connect',$available_gateways)) {
        // Gateway ID for Paypal is 'paypal'. 
         $available_gateways['yith-stripe-connect']->order_button_text = __( 'Pay Now', 'woocommerce' );
    }
    return $available_gateways;
}


function custom_mini_cart() { 
    echo '<a href="#" class="dropdown-back" data-toggle="dropdown"> ';
    echo '<i class="fa fa-shopping-cart" aria-hidden="true"></i>';
    echo '<div class="basket-item-count" style="display: inline;">';
        echo '<span class="cart-items-count count">';
            echo WC()->cart->get_cart_contents_count();
        echo '</span>';
    echo '</div>';
    echo '</a>';
    echo '<ul class="dropdown-menu dropdown-menu-mini-cart">';
        echo '<li> <div class="widget_shopping_cart_content">';
                  woocommerce_mini_cart();
            echo '</div></li></ul>';

            if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'custom_update_mini_cart'){
                die;
            }

      }
      add_action( 'wp_ajax_custom_update_mini_cart', 'custom_mini_cart' );
add_action( 'wp_ajax_nopriv_custom_update_mini_cart', 'custom_mini_cart' );
       add_shortcode( 'custom-techno-mini-cart', 'custom_mini_cart' );
       add_filter( 'woocommerce_add_to_cart_fragments', 'header_add_to_cart_fragment', 30, 1 );
function header_add_to_cart_fragment( $fragments ) {
    global $woocommerce;

    ob_start();

    ?>
    <a class="cart-customlocation" href="<?php echo esc_url(wc_get_cart_url()); ?>" title="<?php _e('View your shopping cart', 'woothemes'); ?>"><?php echo sprintf(_n('%d item', '%d items', $woocommerce->cart->cart_contents_count, 'woothemes'), $woocommerce->cart->cart_contents_count);?> - <?php echo $woocommerce->cart->get_cart_total(); ?></a>
    <?php
    $fragments['a.cart-customlocation'] = ob_get_clean();

    return $fragments;
}

add_filter('acf/location/rule_values/post_type', 'acf_location_rule_values_Post');
function acf_location_rule_values_Post( $choices ) {
    $choices['product_variation'] = 'Product Variation';
    //print_r($choices);
    return $choices;
}

/**
 * Show products from specific product categories on the Shop page.
 */
add_action( 'woocommerce_product_query', 'wpdd_limit_shop_categories' );

function wpdd_limit_shop_categories( $q ) {
    $tax_query = (array) $q->get( 'tax_query' );

    $tax_query[] = array(
        'taxonomy' => 'product_cat',
        'field' => 'slug',
        'terms' => array( 'services' ),
        'include_children' => true,
    );

    $q->set( 'tax_query', $tax_query );
}




//add_action( 'um_submit_form_errors_hook_login', 'my_submit_form_errors_hook_logincheck', 7, 1 );

function my_submit_form_errors_hook_logincheck( $args ) {
    $is_email = false;

    $form_id = $args['form_id'];
    $mode = $args['mode'];
    $user_password = $args['user_password'];


    if ( isset( $args['username'] ) && $args['username'] == '' ) {
        UM()->form()->add_error( 'username', __( 'Please enter your username or email', 'ultimate-member' ) );
    }

    if ( isset( $args['user_login'] ) && $args['user_login'] == '' ) {
        UM()->form()->add_error( 'user_login', __( 'Please enter your username', 'ultimate-member' ) );
    }

    if ( isset( $args['user_email'] ) && $args['user_email'] == '' ) {
        UM()->form()->add_error( 'user_email', __( 'Please enter your email', 'ultimate-member' ) );
    }

    if ( isset( $args['username'] ) ) {
        $authenticate = $args['username'];
        $field = 'username';
        if ( is_email( $args['username'] ) ) {
            $is_email = true;
            $data = get_user_by('email', $args['username'] );
            $user_name = isset( $data->user_login ) ? $data->user_login : null;
        } else {
            $user_name  = $args['username'];
        }
    } elseif ( isset( $args['user_email'] ) ) {
        $authenticate = $args['user_email'];
        $field = 'user_email';
        $is_email = true;
        $data = get_user_by('email', $args['user_email'] );
        $user_name = isset( $data->user_login ) ? $data->user_login : null;
    } else {
        $field = 'user_login';
        $user_name = $args['user_login'];
        $authenticate = $args['user_login'];
    }

    if ( $args['user_password'] == '' ) {
        UM()->form()->add_error( 'user_password', __( 'Please enter your password', 'ultimate-member' ) );
    }

    $user = get_user_by( 'login', $user_name );
    if ( $user && wp_check_password( $args['user_password'], $user->data->user_pass, $user->ID ) ) {
        
        $data = get_user_by('email', $args['username'] );
        $user_id=$data->ID;
        $is_blocked= get_user_meta($user_id, 'is_blocked', true);

        if($is_blocked=="yes"){
            UM()->form()->add_error( 'user_password', __( 'Sorry your account has been blocked', 'ultimate-member' ) );    
        } else {
            UM()->login()->auth_id = username_exists( $user_name );
        }        
    } else {
        UM()->form()->add_error( 'user_password', __( 'Password is incorrect. Please try again.', 'ultimate-member' ) );
    }

    // @since 4.18 replacement for 'wp_login_failed' action hook
    // see WP function wp_authenticate()
    $ignore_codes = array( 'empty_username', 'empty_password' );

    $user = apply_filters( 'authenticate', null, $authenticate, $args['user_password'] );
    if ( is_wp_error( $user ) && ! in_array( $user->get_error_code(), $ignore_codes ) ) {
        UM()->form()->add_error( $user->get_error_code(), __( 'Password is incorrect. Please try again.', 'ultimate-member' ) );
    }

    $user = apply_filters( 'wp_authenticate_user', $user, $args['user_password'] );
    if ( is_wp_error( $user ) && ! in_array( $user->get_error_code(), $ignore_codes ) ) {
        UM()->form()->add_error( $user->get_error_code(), __( 'Password is incorrect. Please try again.', 'ultimate-member' ) );
    }

    // if there is an error notify wp
    if ( UM()->form()->has_error( $field ) || UM()->form()->has_error( $user_password ) || UM()->form()->count_errors() > 0 ) {
        do_action( 'wp_login_failed', $user_name, UM()->form()->get_wp_error() );
    }
}


add_filter( 'woocommerce_email_recipient_new_order', 'filter_woocommerce_email_recipient', 10, 3 );
add_filter( 'woocommerce_email_recipient_customer_on_hold_order', 'filter_woocommerce_email_recipient', 10, 3 );
add_filter( 'woocommerce_email_recipient_customer_processing_order', 'filter_woocommerce_email_recipient', 10, 3 );
add_filter( 'woocommerce_email_recipient_customer_pending_order',  'filter_woocommerce_email_recipient', 10, 3 );

function filter_woocommerce_email_recipient( $recipient, $order, $email ) {
        if(!isset($_REQUEST['empty_cart'])){
            if ( ! $order || ! is_a( $order, 'WC_Order' ) ) return $recipient;
            // Has order status
            if ( $order->has_status( 'processing' ) ) {
                $recipient = '';
            }
        
            
        }
        return $recipient;
    }


function resend_wooemail($order_id){
global $woocommerce;


// Allow resending new order email
add_filter('woocommerce_new_order_email_allows_resend', '__return_true' );
// Resend new order email
WC()->mailer()->get_emails()['WC_Email_New_Order']->trigger( $order_id );


//Resend Email to customer
$allmails = WC()->mailer()->emails;         
$email = $allmails['WC_Email_Customer_Processing_Order'];
$email->trigger( $order_id );

}

// Disable resending new order email
add_filter('woocommerce_new_order_email_allows_resend', '__return_false' );

add_filter( 'default_checkout_billing_country', 'change_default_checkout_country' );

function change_default_checkout_country() {
  return 'GB'; // country code
}



add_filter( 'woocommerce_registration_error_email_exists', function( $html ) {
    $url =  site_url()."/login";
    $url = add_query_arg( 'redirect_checkout', 1, $url );
    $html = str_replace( 'Please log in', '<a href="'.$url.'">Please log in</a>', $html );
    return $html;
} );

add_filter('woocommerce_return_to_shop_text', 'prefix_store_button');

function prefix_store_button() {
        $store_button = "Back to Dashboard"; // Change text as required

        return $store_button;
}


function wc_empty_cart_redirect_url() {
    return esc_url( site_url().'/dashboard/' );
}
add_filter( 'woocommerce_return_to_shop_redirect', 'wc_empty_cart_redirect_url' );



add_filter('woocommerce_get_myaccount_page_permalink','getdashboardurl',15);

function getdashboardurl($permalink){
    return get_permalink(66);
}



add_action("template_redirect", 'redirection_function');
function redirection_function(){
    global $woocommerce;
    if( is_cart() && WC()->cart->cart_contents_count == 0){
        wp_safe_redirect(site_url('/plans'));
    }
}



add_action('restrict_manage_posts', 'ditt_filter_by_author');
function ditt_filter_by_author() {
	 $screen = get_current_screen();
	 global $post_type;
    // pre($screen);
	 if ( $screen->id == 'edit-project' || $screen->id == 'edit-layouts' ) 
     {
	   $users_with_role = implode(",", get_users ( array ( 'fields' => 'id', 'has_published_posts' => array('layouts','project') ) ) );
        $params = array(
            'include' => $users_with_role
        );
        
        if ( isset($_GET['user']) ) {
            $params['selected'] = $_GET['user'];
        }
        wp_dropdown_users( $params );
    }
}

function my_author_filter_results($query){
        if(isset($_GET['user'])){
            $author_id = sanitize_text_field($_GET['user']);
            if($author_id != 0){
                $query->query_vars['author'] = $author_id;
            }
        }
}
add_action('pre_get_posts','my_author_filter_results');
