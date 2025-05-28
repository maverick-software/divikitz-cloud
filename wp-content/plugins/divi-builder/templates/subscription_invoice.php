<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Subscription_invoice {

    public function __construct() {
        add_shortcode('my_invoice', array($this, 'subsc_invoice'));
    }

    public function subsc_invoice() {
        if (is_user_logged_in()) {
            global $current_user, $wp_roles;
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
            $current_plan=get_user_meta($user_id, 'current_plan', true);
            if(@$current_plan!=""){
                $plan_title=get_the_title($current_plan);                
            } else {
                $plan_title="Free";                
            }
            ?>


            <div class="contentWrapper user_actions" id="table-page">
                <!-- //sidebar  --> 
                <?php require_once 'builder_siderbar.php'; ?>

                <div class="wrapContent">
                    <?php require_once 'builder_topnav.php'; ?>


                    <div class="wrapContainer">
                        <div class="rowWrap">
                            <div class="flex-12 dashboard_no mBottom">
                                <?php echo do_shortcode('[my_orders order_counts=10]'); ?>
                            </div>
                        </div>
                    </div>

                </div>  
                <?php require_once 'builder_footer.php'; ?>
            </div>
            <?php
        } else {
            restricate_page_content();
        }
    }

}

$subscription_invoice = new Subscription_invoice();
    