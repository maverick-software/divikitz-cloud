<?php
/*
  Plugin Name: Membership Custom
  Plugin URI:
  Description:
  Version: 1.0.0
  Author: Rakesh Kumar
  Author URI:
  License: GPLv2 or later
  Text Domain:
 */

if (!function_exists('wp_get_current_user')) {
    include(ABSPATH . "wp-includes/pluggable.php");
}

include 'custom_register.php';

function wpdocs_theme_name_scripts() {
    wp_enqueue_style('membership-custom-css', plugins_url('/inc/css/custom-admin.css?v=' . time(), __FILE__));
}

add_action('wp_enqueue_scripts', 'wpdocs_theme_name_scripts');

// Shortcode to display Memberships 


function memberships_custom_plans() {
    ob_start();

    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $user_id = $user->ID;
        $user = get_userdata($user_id);
        $user_roles = $user->roles;

        $currentrole = get_user_role_name($user_id);
        ?>
        <section class="pricing-table-membership-custom">
            <div class="container">
                <div class="block-heading text-center">
                    <h2 class="color-theme ">Subscription Plans</h2>
                </div>
                <!--h3 class="memberinfo text-center">Current Plan: ' . $currentrole . '</!h3-->
            </div>

            <!-- Subscription Yearly Plan --> 
            <div class="block-plans bg-theme-gradient">
                <div class="container">
                    <div class="switch-button" style="margin:0 auto">
                        <input class="switch-button-checkbox" type="checkbox" value="Yearly"></input>
                        <label class="switch-button-label" for=""><span class="switch-button-label-span">Monthly</span></label>
                    </div>


                    <div class="row justify-content-md-center" id="monthly_view" style="padding-top: 40px;">
                        <?php
                        $args = array(
                            'post_type' => 'wpi_item',
                            'showposts' => 3,
                            'order' => 'asc',
                            'tax_query' => array(
                                'relation' => 'AND',
                                array(
                                    'taxonomy' => 'subjects',
                                    'field' => 'term_id',
                                    'terms' => 367
                                ),
                                array(
                                    'taxonomy' => 'subjects',
                                    'field' => 'term_id',
                                    'terms' => 368
                                )
                            )
                        );
                        $query = new WP_Query($args);
                        $monthly_plan = get_posts($args);

                        $classes=array('freePlan', 'recommendedPlan', 'agencyPlan');

                        $i=0;
                        ?>


                        <?php foreach($monthly_plan as $monthly_view){ ?>
                        	<?php
                        	$mv_title= $monthly_view->post_title;      						
  							$mv_post_id= $monthly_view->ID;
  							$monthly_price=get_post_meta($mv_post_id, '_wpinv_price', true);
  							$monthly_period=get_post_meta($mv_post_id, '_wpinv_recurring_period', true);
  							$monthly_rec=get_post_meta($mv_post_id, '_wpinv_recurring_interval', true);
  							if($monthly_period=="Y"){
  								$mon_per="year";
  							} else if($monthly_period=="M") {
  								$mon_per="month";
  							} else {
  								$mon_per="daily";
  							}
                        	?>
		                        <div class="col-md-4 col-lg-4">
		                            <div class="item <?php echo $classes[$i]; ?>">
		                            	<?php if($i==1){ ?>
		                            		<div class="ribbon">Recommended</div>
		                            	<?php } ?>
		                                <div class="heading">
		                                    <h3 class="title-theme"><?= $mv_title ?></h3>
		                                    <div class="price title-theme">
		                                    	<?php if($monthly_rec==1){ ?>
		                                        	<h4 class="plan_rate_m">$<?= $monthly_price; ?> <span>per <?= $mon_per; ?></span></h4>
		                                        <?php } else { ?>
		                                        	<h4 class="plan_rate_m">$<?= $monthly_price; ?></h4>
		                                        <?php } ?>
		                                    </div>
		                                </div>
		                                <?php echo $monthly_view->post_excerpt; ?>
		                                <div class="getpaid bsui sdel-23f8cc6c">
											<button class="btn btn-block upgradeplan_a getpaid-payment-button" type="button" data-item="<?= $mv_post_id; ?>">Upgrade</button>
										</div>
		                            </div>
		                        </div>
		                        <?php $i++; ?>
                    	<?php } ?>
                        
                    </div>

            		<!-- Subscription Yearly Plan -->             
                    <div class="row justify-content-md-center" id="yearly_view" style="padding-top: 40px; display: none;">
                        <?php
                        $args = array(
                            'post_type' => 'wpi_item',
                            'showposts' => 3,
                            'order' => 'asc',
                            'tax_query' => array(
                                'relation' => 'AND',
                                array(
                                    'taxonomy' => 'subjects',
                                    'field' => 'term_id',
                                    'terms' => 367
                                ),
                                array(
                                    'taxonomy' => 'subjects',
                                    'field' => 'term_id',
                                    'terms' => 369
                                )
                            )
                        );
                        $query = new WP_Query($args);
                        $monthly_plan = get_posts($args);

                        $classes=array('freePlan', 'recommendedPlan', 'agencyPlan');

                        $i=0;
                        ?>


                        <?php foreach($monthly_plan as $monthly_view){ ?>
                        	<?php
                        	$mv_title= $monthly_view->post_title;      						
  							$mv_post_id= $monthly_view->ID;
  							$monthly_price=get_post_meta($mv_post_id, '_wpinv_price', true);
  							$monthly_period=get_post_meta($mv_post_id, '_wpinv_recurring_period', true);
  							$monthly_rec=get_post_meta($mv_post_id, '_wpinv_recurring_interval', true);
  							if($monthly_period=="Y"){
  								$mon_per="year";
  							} else if($monthly_period=="M") {
  								$mon_per="month";
  							} else {
  								$mon_per="daily";
  							}
                        	?>
		                        <div class="col-md-4 col-lg-4">
		                            <div class="item <?php echo $classes[$i]; ?>">
		                            	<?php if($i==1){ ?>
		                            		<div class="ribbon">Recommended</div>
		                            	<?php } ?>
		                                <div class="heading">
		                                    <h3 class="title-theme"><?= $mv_title ?></h3>
		                                    <div class="price title-theme">
		                                    	<?php if($monthly_rec==1){ ?>
		                                        	<h4 class="plan_rate_m">$<?= $monthly_price; ?> <span>per <?= $mon_per; ?></span></h4>
		                                        <?php } else { ?>
		                                        	<h4 class="plan_rate_m">$<?= $monthly_price; ?></h4>
		                                        <?php } ?>
		                                    </div>
		                                </div>
		                                <?php echo $monthly_view->post_excerpt; ?>
		                                <div class="getpaid bsui sdel-23f8cc6c">
											<button class="btn btn-block upgradeplan_a getpaid-payment-button" type="button" data-item="<?= $mv_post_id; ?>">Upgrade</button>
										</div>
		                            </div>
		                        </div>
		                        <?php $i++; ?>
                    	<?php } ?>
                        
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </section>

        <script>
            jQuery(document).ready(function () {
                jQuery(".switch-button-checkbox").on("change", function () {
                    if (jQuery(".switch-button-checkbox:checked").val() == "Yearly") {
                        jQuery("#yearly_view").show();
                        jQuery("#monthly_view").hide();
                    } else {
                        jQuery("#yearly_view").hide();
                        jQuery("#monthly_view").show();
                    }
                });
            });
        </script>

        <?php
        $html = ob_get_contents();
        ob_clean();
        return $html;
    }
}

add_shortcode('memberships-custom-plans', 'memberships_custom_plans');





/*  ================================ Hosting Membership =========================== */

// Shortcode to display Memberships 


function memberships_hosting_plans() {
    ob_start();

    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $user_id = $user->ID;
        $user = get_userdata($user_id);
        $user_roles = $user->roles;
        $currentrole = get_user_role_name($user_id);

        global $wp_query;
        $page_id = $wp_query->post->ID;
        $page_title = get_field('page_title', $page_id);
        $monthly_plan = get_field('hosting_plan_monthly', $page_id);
        $yearly_plan = get_field('hosting_plan_yearly', $page_id);
        ?>
        <section class="pricing-table-membership-custom">
            <div class="container">
                <div class="block-heading text-center">
                    <h2 class="color-theme "><?= $page_title; ?></h2>
                </div>				
            </div>

            <div class="block-plans bg-theme-gradient">
                <div class="container">
                    <div class="switch-button" style="margin:0 auto">
                        <input class="switch-button-checkbox" type="checkbox" value="Yearly"></input>
                        <label class="switch-button-label" for=""><span class="switch-button-label-span">Monthly</span></label>
                    </div>

                    <div id="monthly_view" class="row justify-content-md-center" style="padding-top: 40px;">
                        <?php foreach ($monthly_plan as $mv) { ?>
                            <div class="col-md-4 col-lg-4 hostingPlan">
                                <div class="item">
                                    <div class="heading">
                                        <h3 class="title-theme"><?php echo $mv['plan_title']; ?></h3>
                                        <div class="price title-theme">
                                            <h4 class="plan_rate_m">$<?php echo $mv['plan_price']; ?> <span>per month</span></h4>                                            
                                        </div>
                                    </div>

                                    <div class="features">
                                        <?php $plan_content = $mv['plan_content']; ?>
                                        <?php foreach ($plan_content as $plan_info) { ?>
                                            <h4>
                                                <span class="feature"><?php echo $plan_info['plan_description'] ?></span>  
                                                <span class="value" title="<?= $plan_info['plan_tip_text']; ?>">
                                                    <div class="hovertip"><i class="fa fa-question"></i>
                                                        <span class="hovertiptext"><?= $plan_info['plan_tip_text']; ?></span>
                                                    </div>
                                                </span>
                                            </h4>
                                        <?php } ?>
                                    </div>
                                    <a href="<?php echo $mv['button_url']; ?>" class="btn btn-block upgradeplan_m"><?php echo $mv['button_text']; ?></a>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <div id="yearly_view" class="row justify-content-md-center" style="padding-top: 40px; display:none;">
                        <?php foreach ($yearly_plan as $my) { ?>
                            <div class="col-md-4 col-lg-4 freePlan">
                                <div class="item">
                                    <div class="heading">
                                        <h3 class="title-theme"><?php echo $my['plan_title_yearly']; ?></h3>
                                        <div class="price title-theme">
                                            <h4 class="plan_rate_m">$<?php echo $my['plan_price_yearly']; ?> <span>per month</span></h4>                                            
                                        </div>
                                    </div>

                                    <div class="features">
                                        <?php $plan_content = $my['plan_content_yearly']; ?>
                                        <?php foreach ($plan_content as $plan_info) { ?>
                                            <h4><span class="feature"><?php echo $plan_info['plan_description_yearly'] ?></span>  <span class="value" title="<?= $plan_info['plan_tip_text']; ?>">?</span></h4>
                                        <?php } ?>
                                    </div>
                                    <a href="<?= $my['button_url_yearly']; ?>" class="btn btn-block upgradeplan_m"><?= $my['button_text_yearly']; ?></a>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                </div>
        </section>


        <script>
            jQuery(document).ready(function () {
                jQuery(".switch-button-checkbox").on("change", function () {
                    if (jQuery(".switch-button-checkbox:checked").val() == "Yearly") {
                        jQuery("#yearly_view").show();
                        jQuery("#monthly_view").hide();
                    } else {
                        jQuery("#yearly_view").hide();
                        jQuery("#monthly_view").show();
                    }
                });
            });
        </script>
        <?php
        $html = ob_get_contents();
        ob_clean();
        return $html;
    }
}

add_shortcode('memberships_hosting_plans', 'memberships_hosting_plans');


/* ================================= End Hosting Membership =======================  */










/* * ********** custom welcome email when new user register ******************** */

function send_welcome_email_to_new_user($user_id) {

    $user = get_user_by('id', $user_id);
    $firstname = $user->first_name;
    $email = $user->user_email;
    $adt_rp_key = get_password_reset_key($user);
    $user_login = $user->user_login;
    $rp_link = '<a href="' . get_site_url() . "/password-reset/?act=reset_password&hash=$adt_rp_key&user_id=" . $user_id . '" style="background: #555555 ; color: #fff ; padding: 12px 30px ; text-decoration: none ; border-radius: 3px ; letter-spacing: 0.3px">Reset your password</a>';

    if ($firstname == "")
        $firstname = "Bloxx";
    $message = "Hi " . $firstname . ",<br><br>";
    $message .= "An account has been created on " . get_bloginfo('name') . "<br/><br/>";

    $message .= 'Login Url : <a href="' . site_url() . '/login/">' . site_url() . '/login/</a><br><br>';

    $message .= "Your email id : " . $email . "<br>";
//$message .= "Your password : ".$password."<br><br><br>";

    $message .= "Click the link below to get your password:<br><br>" . $rp_link;



//deze functie moet je zelf nog toevoegen. 
    $subject = __("Welcome on " . get_bloginfo('name'));
    $headers = array();

    $headers[] = 'From: Bloxx <noreply@sitebloxx.com>' . "\r\n";
    $headers[] = 'Content-Type: text/html; charset=UTF-8';

    if (wp_mail($email, $subject, $message, $headers)) {
        error_log("email has been successfully sent to user whose email is " . $user_email);
    } else {
        error_log("email failed to sent to user whose email is " . $user_email);
    }
}

// THE ONLY DIFFERENCE IS THIS LINE
//add_action('user_register', 'send_welcome_email_to_new_user');
// THE ONLY DIFFERENCE IS THIS LINE


/* * ********** restrict for create resourse based on role ********************* */

$freelancer_projects_limit = '2';
$freelancer_pages_limit = '10';
$freelancer_custom_section_limit = '12';
$freelancer_custom_pages_limit = '10';

$agency_projects_limit = '25';
$agency_pages_limit = '300';
$agency_custom_section_limit = '50';
$agency_custom_pages_limit = '40';

$team_projects_limit = '5';
$team_pages_limit = '100';
$team_custom_section_limit = '30';
$team_custom_pages_limit = '20';

/* $current_user = wp_get_current_user();
  if(in_array('um_freelancer', (array) $current_user)) {
  echo "FREE";
  } if(in_array('um_team', (array) $current_user)) {
  echo "teaM";
  } if(in_array('um_agency', (array) $current_user)) {
  echo "agency";
  } */



add_action('admin_menu', 'divi_restriction_menu');

function divi_restriction_menu() {

//create new top-level menu
    add_menu_page('Divi Restriction', 'Divi Restriction', 'administrator', __FILE__, 'divi_restriction_settings_page', 'dashicons-admin-settings');

//call register settings function
    add_action('admin_init', 'register_divi_restriction_settings');
}

function register_divi_restriction_settings() {
//register our settings
    register_setting('divi_restriction_settings', 'freelancer');
    register_setting('divi_restriction_settings', 'agency');
    register_setting('divi_restriction_settings', 'team');
    register_setting('divi_restriction_settings', 'subscriber');
}

function divi_restriction_settings_page() {
    ?>
    <div class="wrap">
        <h1>Divi Restriction</h1>
        <?php
        $freelancer = get_option('freelancer');
        $agency = get_option('agency');
        $team = get_option('team');
        $subscriber = get_option('subscriber');

        $spp_data = get_option('spp_webhook_data');
        $spp_sub_data = get_option('spp_subscription_data');
        /* echo "<pre>";
          print_r($spp_data);
          echo "<br/>===============<br/>";
          print_r($spp_sub_data);
          echo "</pre>";
          exit; */
//$spp_user_data = get_user_meta('45', 'spp_order_created', true);
//$spp_client_data = get_user_meta('45', 'spp_client_information', true);

        $spp_user_data = get_option('subscription_cancel_data');
        $spp_client_data = get_option('subscription_cancel');

//$user_data = json_decode($spp_user_data);
//$client_data = unserialize($spp_client_data, true);
        echo "<pre>";
        print_r($spp_user_data);
        echo "<br>============<br/>";
        print_r($spp_client_data);
        ?>
        <form method="post" action="options.php">
            <?php settings_fields('divi_restriction_settings'); ?>
            <?php do_settings_sections('divi_restriction_settings'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Freelancer</th>
                    <td><input type="text" name="freelancer[projects]" value="<?php echo isset($freelancer['projects']) ? $freelancer['projects'] : ''; ?>" /><i>Projects</i></td>
                    <td><input type="text" name="freelancer[pages]" value="<?php echo isset($freelancer['pages']) ? $freelancer['pages'] : ''; ?>" /><i>Pages</i></td>
                    <td><input type="text" name="freelancer[custom_section]" value="<?php echo isset($freelancer['custom_section']) ? $freelancer['custom_section'] : ''; ?>" /><i>Custom Section</i></td>
                    <td><input type="text" name="freelancer[custom_pages]" value="<?php echo isset($freelancer['custom_pages']) ? $freelancer['custom_pages'] : ''; ?>" /><i>Custom Pages</i></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Agency</th>
                    <td><input type="text" name="agency[projects]" value="<?php echo isset($agency['projects']) ? $agency['projects'] : ''; ?>" /><i>Projects</i></td>
                    <td><input type="text" name="agency[pages]" value="<?php echo isset($agency['pages']) ? $agency['pages'] : ''; ?>" /><i>Pages</i></td>
                    <td><input type="text" name="agency[custom_section]" value="<?php echo isset($agency['custom_section']) ? $agency['custom_section'] : ''; ?>" /><i>Custom Section</i></td>
                    <td><input type="text" name="agency[custom_pages]" value="<?php echo isset($agency['custom_pages']) ? $agency['custom_pages'] : ''; ?>" /><i>Custom Pages</i></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Team</th>
                    <td><input type="text" name="team[projects]" value="<?php echo isset($team['projects']) ? $team['projects'] : ''; ?>" /><i>Projects</i></td>
                    <td><input type="text" name="team[pages]" value="<?php echo isset($team['pages']) ? $team['pages'] : ''; ?>" /><i>Pages</i></td>
                    <td><input type="text" name="team[custom_section]" value="<?php echo isset($team['custom_section']) ? $team['custom_section'] : ''; ?>" /><i>Custom Section</i></td>
                    <td><input type="text" name="team[custom_pages]" value="<?php echo isset($team['custom_pages']) ? $team['custom_pages'] : ''; ?>" /><i>Custom Pages</i></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Subscriber</th>
                    <td><input type="text" name="subscriber[projects]" value="<?php echo isset($subscriber['projects']) ? $subscriber['projects'] : ''; ?>" /><i>Projects</i></td>
                    <td><input type="text" name="subscriber[pages]" value="<?php echo isset($subscriber['pages']) ? $subscriber['pages'] : ''; ?>" /><i>Pages</i></td>
                    <td><input type="text" name="subscriber[custom_section]" value="<?php echo isset($subscriber['custom_section']) ? $subscriber['custom_section'] : ''; ?>" /><i>Custom Section</i></td>
                    <td><input type="text" name="subscriber[custom_pages]" value="<?php echo isset($subscriber['custom_pages']) ? $subscriber['custom_pages'] : ''; ?>" /><i>Custom Pages</i></td>
                </tr>
            </table>

            <?php submit_button(); ?>

        </form>
    </div>
    <?php
}

function theme_wc_setup() {
    remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
    add_action('woocommerce_after_order_notes', 'woocommerce_checkout_payment', 21);
    remove_action('woocommerce_checkout_order_review', 'woocommerce_order_review', 10);
    add_action('woocommerce_after_order_notes', 'woocommerce_order_review_head', 19);
    add_action('woocommerce_after_order_notes', 'woocommerce_order_review', 20);

    add_filter('woocommerce_enable_order_notes_field', '__return_false', 9999);
}

add_action('after_setup_theme', 'theme_wc_setup');

function woocommerce_order_review_head() {
    echo '<h3 id="order_review_heading">Your order</h3>';
}

function theme_user_limits_settings_page() {
    add_submenu_page("users.php", "Users Limits", "Users Limits", "manage_options", "users-limits", "theme_user_limits_settings_page_callback", null, 70);
}

function theme_user_limits_settings_page_callback() {
    global $wp_roles;
    $userRoles = array('um_freelancer', 'um_agency', 'um_team', 'um_free');
    ?>

    <div class="wrap admin-userole-wrapper">
        <h1>Users Limits of Projects , Pages and Sections</h1>
        <form method="post" action="users.php">
            <label>Select Role to set limits : </label>
            <select name="role">
                <option>Selec User Role</option>
                <?php foreach ($wp_roles->roles as $key => $value): ?>
                    <?php
                    if (in_array($key, $userRoles)) {
                        ?><option value="<?php echo $key; ?>"><?php echo $value['name']; ?></option><?php
                    }
                    ?>

                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <div class="display-user-field-wrapper"></div>
    <?php
}

add_action("admin_menu", "theme_user_limits_settings_page");


/* custom code */

function admin_enqueue_script() {

    wp_enqueue_style('custom-css', plugin_dir_url(__FILE__) . 'inc/css/custom.css');
    wp_enqueue_script('my_custom_script', plugin_dir_url(__FILE__) . 'inc/js/custom-admin.js');
    wp_localize_script('my_custom_script', 'site_info', array(
        'site_url' => site_url(),
        'admin_ajax' => admin_url('admin-ajax.php')
    ));
}

add_action('admin_enqueue_scripts', 'admin_enqueue_script');




add_action('wp_ajax_display_user_role_limit', 'display_user_role_limit');
add_action('wp_ajax_nopriv_display_user_role_limit', 'display_user_role_limit');

function display_user_role_limit() {
    $role = $_POST['role'];
    if ($role == "um_agency") {
        $result = display_field_of_user_limit(true, $role, 'um_agency_project_limit', 'um_agency_page_limit', 'um_agency_section_limit');
    } elseif ($role == "um_freelancer") {
        $result = display_field_of_user_limit(true, $role, 'um_freelancer_project_limit', 'um_freelancer_page_limit', 'um_freelancer_section_limit');
    } elseif ($role == "um_team") {
        $result = display_field_of_user_limit(true, $role, 'um_team_project_limit', 'um_team_page_limit', 'um_team_section_limit');
    } elseif ($role == "um_free") {
        $result = display_field_of_user_limit(true, $role, 'um_free_project_limit', 'um_free_page_limit', 'um_free_section_limit');
    }
    echo json_encode(array('result' => $result));
    exit;
}

add_action('display_field_of_user_limit', 'display_field_of_user_limit', 10, 5);
if (!function_exists('display_field_of_user_limit')) {

    function display_field_of_user_limit($print = false, $role = '', $projectLimit = '', $pagelimit = '', $sectionLimit = '') {
        $result = '';
        $result .= '<div class="wrapper-user-form-listing">';
        $result .= '<div class="user-limit-item"><input type="hidden" name="user_role_limit" class="user_role_limit" value="' . $role . '"></div>';
        $result .= '<div class="user-limit-item"><label>Page Limit</label><input type="text" name="page_limit" class="page_limit" value="' . get_option($pagelimit) . '"></div>';
        $result .= '<div class="user-limit-item"><label>Project Limit</label><input type="text" name="project_limit" class="project_limit" value="' . get_option($projectLimit) . '"></div>';
        $result .= '<div class="user-limit-item"><label>Section Limit</label><input type="text" name="section_limit" class="section_limit" value="' . get_option($sectionLimit) . '"></div>';
        $result .= '<div class="user-limit-item"><input type="button" name="submit_user_role" id="submit_user_role" value="Submit"></div>';
        $result .= '<div class="update_status"></div>';
        $result .= '</div>';

        if (!$print) {
            echo $result;
        } else {
            return $result;
        }
    }

}


add_action('wp_ajax_update_user_role_limit', 'update_user_role_limit');
add_action('wp_ajax_nopriv_update_user_role_limit', 'update_user_role_limit');

function update_user_role_limit() {
    $role = $_POST['role'];
    // print_r($_POST);
    // die;
    if ($role == "um_agency") {
        $result = update_field_of_user_limit(true, 'um_agency_project_limit', $_POST['projectLimit'], 'um_agency_page_limit', $_POST['pageLimit'], 'um_agency_section_limit', $_POST['sectionLimit']);
    } elseif ($role == "um_freelancer") {
        $result = update_field_of_user_limit(true, 'um_freelancer_project_limit', $_POST['projectLimit'], 'um_freelancer_page_limit', $_POST['pageLimit'], 'um_freelancer_section_limit', $_POST['sectionLimit']);
    } elseif ($role == "um_team") {
        $result = update_field_of_user_limit(true, 'um_team_project_limit', $_POST['projectLimit'], 'um_team_page_limit', $_POST['pageLimit'], 'um_team_section_limit', $_POST['sectionLimit']);
    } elseif ($role == "um_free") {
        $result = update_field_of_user_limit(true, 'um_free_project_limit', $_POST['projectLimit'], 'um_free_page_limit', $_POST['pageLimit'], 'um_free_section_limit', $_POST['sectionLimit']);
    }
    echo json_encode(array('result' => $result));
    exit;
}

add_action('update_field_of_user_limit', 'update_field_of_user_limit', 10, 5);
if (!function_exists('update_field_of_user_limit')) {

    function update_field_of_user_limit($print = true, $projectLimit = '', $projectLimitVal = '', $pagelimit = '', $pagelimitVal = '', $sectionLimit = '', $sectionLimitVal = '') {

        update_option($pagelimit, $pagelimitVal);
        update_option($projectLimit, $projectLimitVal);
        update_option($sectionLimit, $sectionLimitVal);
        $result = '<p style="font-size:15px;color:green;">Record Update successfully....</p>';

        if (!$print) {
            echo $result;
        } else {
            return $result;
        }
    }

}