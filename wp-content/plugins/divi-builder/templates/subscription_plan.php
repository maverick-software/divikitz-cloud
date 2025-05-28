<?php
class Subscription_plan {

    public function __construct() {
        add_shortcode('subscription_plan', array($this, 'my_subscription_plan'));
      
    }

    public function my_subscription_plan() {

        if (is_user_logged_in()) {
            global $current_user, $wp_roles;
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
			
            $user_plan=get_user_meta($user_id, "current_plan", true);
            if($user_plan==""){
                $user_plan=get_field("select_product", 'option');
            }

            
            $args = array(
                'post_type' => 'product',
                'showposts' => 2,
                'order' => 'asc',
                'exclude'=> array(84576),
                'tax_query' => array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'term_id',
                        'terms' => 866
                    )
                )
            );
            $query = new WP_Query($args);
            $product = get_posts($args);
            ?>

            <div class="contentWrapper user_actions" id="table-page">
                <!-- //sidebar  --> 
                <?php require_once 'builder_siderbar.php'; ?>
                <div class="wrapContent">
                    <!-- //Top Bar  --> 
                    <?php require_once 'builder_topnav.php'; ?>


                    <div class="wrapContainer">
                        <div class="rowWrap">
                            <div class="flex-12 dashboard_no mBottom">
                                <section class="subsc--plans--Page">
                                    <div class="subs--plans">
                                        <div class="container">            
                                           <!--  start plan  -->
                                           <h2>Plans and Pricing</h2>
                                           <!-- <p>*All plans include email ticketing support. Chat support will be available in the near future.</p> -->
                                            <div class="plans--flex--table">
                                                <?php foreach ($product as $pds) { ?>

                                                    <?php
                                                    $title= $pds->post_title;
                                                    $prd_id = $pds->ID;
                                                    $enable_features = get_field('enable_features', $prd_id);
                                                    $monthly_price = get_post_meta($prd_id, '_price', true);
                                                    //$monthly_period = get_post_meta($prd_id, '_ywsbs_price_is_per', true);
                                                    $monthly_rec = get_post_meta($prd_id, '_ywsbs_price_time_option', true);

                                                    $per_text="";
                                                    //echo $enable_features. "testRock";
                                                    if($enable_features=="yes"){
                                                        $trial_days     = get_field('trial_days', $prd_id);
                                                        $trial_text     = get_field('trial_text', $prd_id);
                                                        $access_text    = get_field('access_text', $prd_id);
                                                        $api_limit      = get_field('api_limit', $prd_id);
                                                        $api_text       = get_field('api_text', $prd_id);
                                                        $neo_builder    = get_field('neo_builder', $prd_id);

                                                        if ($monthly_rec == "years") {
                                                            $per_text = "$".$monthly_price."/year.";
                                                        } else if ($monthly_rec == "months") {
                                                            $per_text = "$".$monthly_price."/mo.";
                                                        } else if ($monthly_rec == "weeks") {
                                                            $per_text = "$".$monthly_price."/week.";
                                                        } else {
                                                            $per_text = "$".$monthly_price."/daily.";
                                                        }
                                                    } else {
                                                        $no_feature_text= get_field('lifetime_feature_text', $prd_id);;
                                                    }
                                                    ?>

                                                    <div class="plans--flex--tr">
                                                        <h3 class="title--free"><?= $title; ?></h3>

                                                        <?php if($enable_features=="yes"){ ?>
                                                            <ul>
                                                                <li>
                                                                    <span>
                                                                        <svg viewBox="0 0 24 24">
                                                                            <path class="cls-1" d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm5.707,8.707-7,7a1,1,0,0,1-1.414,0l-3-3a1,1,0,0,1,1.414-1.414L10,14.586l6.293-6.293a1,1,0,0,1,1.414,1.414Z"></path>
                                                                        </svg>
                                                                    </span>
                                                                    <?= $trial_days. $trial_text; ?></li>
                                                                <li>
                                                                    <span>
                                                                        <svg viewBox="0 0 24 24">
                                                                            <path class="cls-1" d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm5.707,8.707-7,7a1,1,0,0,1-1.414,0l-3-3a1,1,0,0,1,1.414-1.414L10,14.586l6.293-6.293a1,1,0,0,1,1.414,1.414Z"></path>
                                                                        </svg>
                                                                    </span>
                                                                    <?= $access_text ?></li>
                                                                <li>
                                                                    <span>
                                                                        <svg viewBox="0 0 24 24">
                                                                            <path class="cls-1" d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm5.707,8.707-7,7a1,1,0,0,1-1.414,0l-3-3a1,1,0,0,1,1.414-1.414L10,14.586l6.293-6.293a1,1,0,0,1,1.414,1.414Z"></path>
                                                                        </svg>
                                                                    </span>
                                                                    <?= $api_limit.$api_text ?>
                                                                </li>
                                                                
                                                                    <li>
                                                                        <span>
                                                                            <?php if($neo_builder=="yes"){ ?>
                                                                                <svg viewBox="0 0 24 24">
                                                                                    <path class="cls-1" d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm5.707,8.707-7,7a1,1,0,0,1-1.414,0l-3-3a1,1,0,0,1,1.414-1.414L10,14.586l6.293-6.293a1,1,0,0,1,1.414,1.414Z"></path>
                                                                                </svg> 
                                                                            <?php } else { ?>
                                                                                <svg viewBox="0 0 96 96">
                                                                                    <title/><g><path d="M48,0A48,48,0,1,0,96,48,48.0512,48.0512,0,0,0,48,0Zm0,84A36,36,0,1,1,84,48,36.0393,36.0393,0,0,1,48,84Z"/><path d="M64.2422,31.7578a5.9979,5.9979,0,0,0-8.4844,0L48,39.5156l-7.7578-7.7578a5.9994,5.9994,0,0,0-8.4844,8.4844L39.5156,48l-7.7578,7.7578a5.9994,5.9994,0,1,0,8.4844,8.4844L48,56.4844l7.7578,7.7578a5.9994,5.9994,0,0,0,8.4844-8.4844L56.4844,48l7.7578-7.7578A5.9979,5.9979,0,0,0,64.2422,31.7578Z"/></g>
                                                                                </svg>
                                                                            <?php } ?>
                                                                        </span>
                                                                    Divikitz Builder
                                                                </li>
                                                            </ul>
                                                        <?php } else { ?>
                                                            <p><?= $no_feature_text; ?></p>
                                                        <?php } ?>
                                                        <h3>$<?= $monthly_price; ?> <?php if($enable_features=="yes"){ "/".$per_text; } ?></h3>
                                                        <a href="<?php echo site_url(); ?>?add-to-cart=<?= $prd_id; ?>" class="neo--btn">Purchase</a>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="clearfix"></div>
                                        </div>
                                    </div>
                                </section>
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

$subscription_plan = new Subscription_plan();