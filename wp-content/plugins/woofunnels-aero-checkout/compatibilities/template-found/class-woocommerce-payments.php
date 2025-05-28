<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_WooCommerce_Payments {
	public function __construct() {
		add_action( 'wfacp_internal_css', [ $this, 'enqueue_scripts' ] );
	}

	public function enqueue_scripts() {
		if ( is_null( WC()->cart ) || WC()->cart->needs_payment() ) {
			return;
		}
		$gateways = WC()->payment_gateways()->get_available_payment_gateways();

		if ( ! isset( $gateways['woocommerce_payments'] ) ) {
			return;
		}

		$gateway = $gateways['woocommerce_payments'];

		/**
		 * @var $gateway WC_Payment_Gateway_WCPay
		 */
		if ( method_exists( $gateway, 'get_payment_fields_js_config' ) ) {
			wp_localize_script( 'wcpay-checkout', 'wcpay_config', $gateway->get_payment_fields_js_config() );
			wp_enqueue_script( 'wcpay-checkout' );
			wp_enqueue_style( 'wcpay-checkout', plugins_url( 'dist/checkout.css', WCPAY_PLUGIN_FILE ), [], WC_Payments::get_file_version( 'dist/checkout.css' ) );
		}

	}
}


if ( ! function_exists( 'wcpay_init' ) ) {
	return;
}
WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_WooCommerce_Payments(), 'woocommerce_checkout' );

