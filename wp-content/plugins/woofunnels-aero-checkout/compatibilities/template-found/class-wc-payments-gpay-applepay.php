<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WFACP_WC_Payments_GPAY_AND_APAY {
	private $instance = null;

	public function __construct() {
		add_filter( 'wfacp_smart_buttons', [ $this, 'add_buttons' ] );
		add_action( 'wfacp_smart_button_container_wc_payment_gpay_apay', [ $this, 'add_wc_payment_gpay_apay_buttons' ] );
		add_action( 'woocommerce_checkout_order_processed', [ $this, 'update_aero_field' ], 11, 2 );
		add_action( 'wfacp_internal_css', [ $this, 'add_internal_css' ], 11, 2 );
		add_action( 'wfacp_after_form', [ $this, 'display_smart_btn_faster' ] );

	}

	public function add_buttons( $buttons ) {

		if ( true == apply_filters( 'wfacp_disabled_google_apple_pay_button_on_desktop', false, $buttons ) ) {


			if ( ! class_exists( 'WFACP_Mobile_Detect' ) ) {
				return $buttons;
			}

			$detect = WFACP_Mobile_Detect::get_instance();
			if ( ! $detect->isMobile() || empty( $detect ) ) {
				return $buttons;
			}

			add_filter( 'wfacp_template_localize_data', [ $this, 'set_local_data' ] );
		}

		$this->instance                  = WFACP_Common::remove_actions( 'woocommerce_checkout_before_customer_details', 'WC_Payments_Payment_Request_Button_Handler', 'display_payment_request_button_html' );
		$this->instance                  = WFACP_Common::remove_actions( 'woocommerce_checkout_before_customer_details', 'WC_Payments_Payment_Request_Button_Handler', 'display_payment_request_button_separator_html' );
		$buttons['wc_payment_gpay_apay'] = [
			'iframe' => true,
			'name'   => __( 'Woocommerce Payment Request', 'woocommerce-payments' ),
		];

		return $buttons;
	}

	public function add_wc_payment_gpay_apay_buttons() {

		if ( $this->instance instanceof WC_Payments_Payment_Request_Button_Handler ) {
			$this->instance->display_payment_request_button_html();
		}
	}


	public function set_local_data( $data ) {
		$data['wc_payment_smart_show_on_desktop'] = 'no';

		return $data;
	}


	// this function only run when Order created via Google Pay or Apple Pay button
	public function update_aero_field( $order_id, $posted_data ) {


		$wfacp_id             = filter_input( INPUT_GET, 'wfacp_id', FILTER_SANITIZE_STRING );
		$payment_request_type = filter_input( INPUT_POST, 'payment_request_type', FILTER_SANITIZE_STRING );

		if ( ! is_null( $wfacp_id ) && ! is_null( $payment_request_type ) && ( 'payment_request_api' == $payment_request_type || 'apple_pay' == $payment_request_type ) ) {
			update_post_meta( $order_id, '_wfacp_post_id', $wfacp_id );
			$override = filter_input( INPUT_GET, 'wfacp_is_checkout_override', FILTER_SANITIZE_STRING );
			if ( ! is_null( $override ) ) {
				if ( 'yes' == $override ) {
					$link = wc_get_checkout_url();
				} else {
					$link = get_the_permalink( $wfacp_id );
				}
				if ( ! empty( $link ) ) {
					update_post_meta( $order_id, '_wfacp_source', $link );
				}
			}
		}
	}

	public function add_internal_css() {
		?>

        <style>
            #wfacp_smart_button_wc_payment_gpay_apay #wcpay-payment-request-button {
                width: 150px;
            }

            #wfacp_smart_button_wc_payment_gpay_apay #wcpay-payment-request-wrapper {
                padding: 0 !important;
            }
        </style>
		<?php
	}

	public function display_smart_btn_faster() {

		?>
        <script>
            (function () {
                var wfacp_apay_gpay = document.getElementById("wfacp_smart_button_wc_payment_gpay_apay");
                if (null != wfacp_apay_gpay) {
                    wfacp_apay_gpay.addEventListener('DOMNodeInserted', function () {
                        var smart_buttons = document.getElementById("wfacp_smart_buttons");
                        smart_buttons.style.display = 'block';
                        smart_buttons.classList.remove("wfacp-dynamic-checkout-loading");
                    });
                }
            })();
        </script>
		<?php
	}


}


if ( ! class_exists( 'WC_Payments' ) ) {
	return '';
}

WFACP_Plugin_Compatibilities::register( new WFACP_WC_Payments_GPAY_AND_APAY(), 'wc-payments-gpay_apay' );



