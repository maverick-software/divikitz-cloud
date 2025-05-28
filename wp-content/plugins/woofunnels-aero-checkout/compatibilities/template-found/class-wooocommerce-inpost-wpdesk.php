<?php

/**
 * WooCommerce InPost
 * By WP Desk
 *
 */
class WFACP_Shipping_Packzkomaty_impost {
	public function __construct() {
		add_action( 'wfacp_after_template_found', [ $this, 'remove_action' ] );

	}

	public function remove_action() {
		$instance = WFACP_Common::remove_actions( 'woocommerce_review_order_after_shipping', 'WPDesk_Paczkomaty_Checkout', 'woocommerce_review_order_after_shipping' );
		if ( $instance instanceof WPDesk_Paczkomaty_Checkout ) {
			add_action( 'wfacp_woocommerce_review_order_after_shipping', array( $instance, 'woocommerce_review_order_after_shipping' ) );
		}
	}
}

if ( ! defined( 'WOOCOMMERCE_PACZKOMATY_INPOST_VERSION' ) ) {
	return;
}
WFACP_Plugin_Compatibilities::register( new WFACP_Shipping_Packzkomaty_impost(), 'woo_social_login' );

