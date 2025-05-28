<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Active_plan {

    public function __construct() {
        add_shortcode('my_active_plan', array($this, 'my_active_plan'));
        add_shortcode('yith_user_subscription',array($this,'user_account_subscription_list'));
        add_action( 'wp_ajax_ywsbs_cancel_subscription', array( $this, 'cancel_subscription' ) );
        add_action( 'wp_ajax_nopriv_ywsbs_cancel_subscription', array( $this, 'cancel_subscription') );
    }

    /**
         * Cancel subscription
         */
    public function cancel_subscription() {
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        check_ajax_referer( 'ywsbs_cancel_subscription', 'security' );

        $posted       = $_POST;
        $subscription = false;

        if ( ! empty( $posted['subscription_id'] ) ) {
            $subscription = ywsbs_get_subscription( $posted['subscription_id'] );
        }

        
        

        if ( ! $subscription || empty( $posted['change_status'] ) ) {
            wp_send_json(
                array(
                    'error' => sprintf( __( 'Error: Subscription not found or it is not possible complete your request.', 'yith-woocommerce-subscription' ) ),
                )
            );
        }

        if ( get_current_user_id() !== $subscription->get_user_id() ) {
            wp_send_json(
                array(
                    'error' => sprintf( __( 'You cannot change the status of this subscription.', 'yith-woocommerce-subscription' ) ),
                )
            );
        }

        YITH_WC_Subscription()->manual_change_status( 'cancelled', $subscription, 'customer' );
        $sub_order_id = $subscription->get_order_id();
        $builder_terms = get_terms(
            array(
                'taxonomy' => 'project_categories',
                'hide_empty' => false,
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => 'builder_cat_user',
                        'value' => $user_id,
                        'compare' => '='
                    ),
                    array(
                        'key' => 'hosting_orderid',
                        'value' => $sub_order_id ,
                        'compare' => '='
                    ),
                ),
            )
        );

        if(!empty($builder_terms)){
            $termid = $builder_terms[0]->term_id;
            $ordermeta = get_term_meta($termid,'hosting_orderid',true);
            if((int) $ordermeta === (int) $sub_order_id ){
                $appid = get_term_meta($termid,'bloxx_app_id',true);
                $appdata = get_user_meta($user_id,'website_'.$appid,true);
                if(!empty($appdata) && isset($appdata->server_id)){
                    $time = strtotime(date('d-m-Y h:i:s', strtotime(date('d-m-Y h:i:s'). ' +30 days')));
                    wp_schedule_single_event($time, 'deleteserverapp', [$appdata->server_id,$appdata->id,$user_id]);
                    
                }
            }

        }



        wp_send_json(
            array(
                // translators: subscription number.
                'success' => sprintf( esc_html__( 'The subscription %s has been cancelled.', 'yith-woocommerce-subscription' ), $subscription->get_number() ),
            )
        );
    }

    public function user_account_subscription_list($atts, $content = null ){
        $args  = shortcode_atts(
            array(
                'page' => (isset($_GET['page_no']))? $_GET['page_no']:1,
            ),
            $atts
        );
        $num_of_subscription_on_a_page_my_account = apply_filters( 'ywsbs_num_of_subscription_on_a_page_my_account', 10 );
        $all_subs                                 = YWSBS_Subscription_Helper()->get_subscriptions_by_user( get_current_user_id(), -1 );
        $max_pages                                = ceil( count( $all_subs ) / 10 );
        $subscriptions                            = YWSBS_Subscription_Helper()->get_subscriptions_by_user( get_current_user_id(), $args['page'] );
        ob_start();
        wc_get_template('myaccount/my-subscriptions-view.php', array(
                'subscriptions' => $subscriptions,
                'max_pages'     => $max_pages,
                'current_page'  => $args['page'],
            ),
            '',
            YITH_YWSBS_TEMPLATE_PATH . '/'
        );
        return ob_get_clean();
    }

    public function my_active_plan() {
        if (is_user_logged_in()) {
            global $current_user, $wp_roles;
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;

            ?>
			<style>
				/*.wrapContent{
					margin-left:0px !important;
				}
				body{
					max-width:1100px !important;
				}*/
			</style>

            <div class="contentWrapper user_actions" id="table-page">
                <!-- //sidebar  --> 
                <?php require_once 'builder_siderbar.php'; ?>

                <div class="wrapContent">
                    <?php require_once 'builder_topnav.php'; ?>


                    <div class="wrapContainer">
                        <a href="<?= site_url(); ?>/dashboard" class="link-btn">
                            <img src="<?php echo plugins_url(); ?>/divi-builder/images/arrow-alt-circle-left.png" alt="..." /> Go Back to Dashboard</span>
                        </a>
                        <div class="rowWrap">
                            <div class="flex-12">
                                <div class="dashboard_no mBottom hostingTables">
                                    <h3>Subscriptions </h3>
                                    <div class="table-responsive">
                                        <?= do_shortcode('[yith_user_subscription]');?>
                                         <!-- Plan Modal -->

                                        <div class="custom-model-main project_plan_modal" id="termid_up_down_modal">
                                            <div class="custom-model-inner"> 
                                                <div class="modalClose">×</div>
                                                <div class="contentWrapper user_actions" id="table-page">
                                                    <div class="wrapContainer">
                                                        <div class="rowWrap">
                                                            <div class="flex-12 dashboard_no mBottom">
                                                                <section class="pricing-table-membership-custom hostingPage">
                                                                    <!-- Subscription Yearly Plan --> 
                                                                    <div class="block-plans bg-theme-gradient">
                                                                        <div class="container">
                                                                            <?php 
                                                                                echo do_shortcode('[agent_plan_modal_checkboxes]');

                                                                            ?>
                                                                        </div>
                                                                    </div>  
                                                                </section>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>  
                                            <div class="bg-overlay"></div>
                                        </div>

                                        <!-- End Plan Modal -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        jQuery(document).ready(function ($) {
                            $(document).on('click','div.bloxx-plan-selector',function(){
                                // $(".radio-custom-label").removeClass('label-selected');
                                // $(this).children('.radio-custom-label').addClass('label-selected');
                                $(".featureList").hide();
                                $("#feature-"+$(this).attr('data-plan_id')).show();
                                $("#plan_"+$(this).attr('data-plan_id')).prop('checked',true);
                                $("input[name=selected_plan]").val($(this).attr('data-href'));
                                var btnid = $(this).attr('id');
                            });
                            
                            $(".block-plans .col-md-3 .item").click(function(){
                                $(".block-plans .col-md-3 .item").removeClass("planActive");
                                $(this).addClass("planActive");
                            });
                           jQuery(document).on('click','.purchase-bttn',function() {
                                 window.location.href = jQuery(this).data('href');
                            });
                            $("body").on("click", ".plan_modal", function(){
                                var $this=$(this);
                                
                                var hosting_plan = $this.attr('data-plan_id');
                                $("a.purchase-bttn").html('Order Now').css('pointer-events','all');
                                $("a[data-product_id='"+hosting_plan+"']").html('Selected').css('pointer-events','none');
                                $('.user_actions #monthly_view .hosting_plan_item .purchase-bttn').each(function(){

                                    jQuery(this).attr('href',$(".plan_link_"+$this.attr('data-sub_id')+"_"+$(this).attr('data-product_id')).attr('data-href'));
                                });

                                $(".project_plan_modal").removeClass("model-open");
                                $("#termid_up_down_modal").addClass("model-open");
                            });
                            jQuery(document).on('click','.buttonVieww',function () {
                                if(jQuery(this).next('.dropdownListt').is(":visible")){
                                    jQuery(this).next('.dropdownListt').hide();
                                }else{
                                    jQuery(this).next('.dropdownListt').show();
                                }
                            });
                             jQuery("div.bloxx-plan-selector").eq(0).trigger('click');
                            $(".switch-button-checkbox").on("change", function () {
                                if ($(".switch-button-checkbox:checked").val() == "Yearly") {
                                    $(".yearly_view").show();
                                    $(".monthly_view").hide();
                                    jQuery("#yearly_view").children().find('div.bloxx-plan-selector').eq(0).trigger('click');
                                } else {
                                    $(".yearly_view").hide();
                                    $(".monthly_view").show();
                                    jQuery("#monthly_view").children().find('div.bloxx-plan-selector').eq(0).trigger('click');
                                }
                            });
                            /**
                                 * MODAL
                                 */
                                var modal = false,
                                    modal_wrapper = false;

                                var openModal = function(){
                                    modal.fadeIn('slow');
                                };

                                var closeModal = function(){
                                    modal.fadeOut('slow');
                                };


                                var modalWrapperPosition = function () {
                                    var modalWidth = modal_wrapper.width(),
                                        modalHeigth = modal_wrapper.width(),
                                        window_w = $(window).width(),
                                        window_h = $(window).height(),
                                        margin = ((window_h - modalHeigth) / 2) + 'px',
                                        width = ((window_w - 100) > modalWidth) ? modalWidth + 'px' : 'auto';

                                    modal_wrapper.css({
                                        'margin-top': margin,
                                        'margin-bottom': margin,
                                        'width': width,
                                    });
                                };

                            $(document).on('click', '.open-modal_btn', function (e) {
                                e.stopPropagation();
                                var $t = $(this);
                                var modalToOpenID = $t.data('target');

                                modal = $('#' + modalToOpenID);
                                modal_wrapper = modal.find('.ywsbs-modal-wrapper');
                                modal.find('.ywsbs-action-button_new').attr('data-id',$t.attr('data-id'));
                                modalWrapperPosition();
                                openModal();
                            });

                            $(document).on('click', '.ywsbs-modal .close', function (e) {
                                e.preventDefault();
                                closeModal();
                            });

                            $(window).on( 'click', function (e) {
                                var target = e.target;
                                if( $(target).hasClass('ywsbs-modal-container')){
                                    closeModal();
                                }
                            });
                            var $body = $('body');

                            var blockParams = {
                                message        : null,
                                overlayCSS     : { background: '#fff', opacity: 0.7 },
                                ignoreIfBlocked: true
                            };
                            $(document).on('click','.ywsbs-action-button_new', function(e){
                                e.preventDefault();
                                var $t = $(this),
                                    container = $t.closest('.ywsbs-action-button-wrap'),
                                    modalWrapper = $t.closest('.ywsbs-modal-body'),
                                    modalBody = modalWrapper.find('.ywsbs-content-text'),
                                    closeButton = modalWrapper.find('.close-modal-wrap'),
                                    status = $t.data('action'),
                                    sbs_id = $t.data('id'),
                                    security = $t.data('nonce');

                                container.block( blockParams );
                                var data = {
                                    subscription_id: sbs_id,
                                    action: 'ywsbs_'+status+'_subscription',
                                    change_status: status,
                                    security: security,
                                    context:'frontend'
                                };

                                $.ajax({
                                    url: yith_ywsbs_frontend.ajaxurl,
                                    data: data,
                                    type: 'POST',
                                    success: function (response) {
                                        if( response.success ){
                                            modalBody.html( response.success );
                                        }

                                        if( response.error){
                                            modalBody.html( '<span class="error">'+response.error+'</span>' );
                                        }

                                        $t.fadeOut();
                                        closeButton.fadeOut();
                                        setTimeout( function(){ closeModal(); reloadSubscriptionView( sbs_id ); }, 2500);
                                    },
                                    complete: function () {
                                        container.unblock();
                                    }
                                });
                            });
                        });
                    </script>
                    <!-- <div class="custom-model-main subscription_plan_modal">
                        <div class="custom-model-inner">   
                            <div class="modalClose">×</div>
                            <?php //echo do_shortcode('[subscription_plan]'); ?>
                        </div>
                    </div> -->

                </div>  
                <?php require_once 'builder_footer.php'; ?>
            </div>



            <?php
        } else {
            restricate_page_content();
        }
    }

}

$active_plan = new Active_plan();