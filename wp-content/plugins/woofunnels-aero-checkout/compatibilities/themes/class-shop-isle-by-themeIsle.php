<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Shop_Isle_By_ThemeIsle {
	public function __construct() {
		/* checkout page */
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'actions' ] );
	}

	public function actions() {

		/* Shop Isle Theme Compatabilty */
		remove_action( 'woocommerce_before_checkout_form', 'shop_isle_coupon_after_order_table_js' );
		remove_action( 'woocommerce_checkout_order_review', 'shop_isle_coupon_after_order_table' );


	}

}

if ( ! function_exists( 'shop_isle_setup' ) ) {
	return;
}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Shop_Isle_By_ThemeIsle(), 'wfacp-shop-isle-by-themeIsle' );
