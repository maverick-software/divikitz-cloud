<?php

/**
 * WooCommerce Extended Coupon Features PRO
 * By Soft79
 * Class WFACP_Compatibility_Extended_Coupon_Pro
 */
class WFACP_Compatibility_Extended_Coupon_Pro {
	public function __construct() {
		remove_action( 'woocommerce_before_calculate_totals', 'WC_Subscriptions_Coupon::remove_coupons' );
	}
}

add_action( 'wfacp_before_coupon_apply', function () {
	if ( ! ( class_exists( 'WJECF_Bootstrap' ) && class_exists( 'WC_Subscriptions_Coupon' ) ) ) {
		return;
	}
	new WFACP_Compatibility_Extended_Coupon_Pro();
} );