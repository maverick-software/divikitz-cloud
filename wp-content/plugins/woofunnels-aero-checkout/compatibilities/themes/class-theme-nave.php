<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Theme_Nave {

	public function __construct() {
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'remove_styling' ] );
	}

	public function remove_styling() {
		WFACP_Common::remove_actions( 'woocommerce_before_checkout_form', 'Neve\Compatibility\Woocommerce', 'move_coupon' );
	}

}

if ( ! defined( 'NEVE_VERSION' ) ) {
	return;
}
WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Theme_Nave(), 'wfacp-theme-nave' );
