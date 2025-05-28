<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Xlwcty {
	public function __construct() {
		add_filter( 'wfacp_default_custom_field_print_hook_for_thankyou', [ $this, 'change_hook_to_next_move_hook' ] );
	}

	public function change_hook_to_next_move_hook( $hook ) {
		if ( ! is_null( XLWCTY_Core()->public ) ) {
			if ( method_exists( XLWCTY_Core()->public, 'is_xlwcty_page' ) ) {
				$hook = 'xlwcty_woocommerce_after_customer_information';
			}
		}

		return $hook;
	}


}

if ( ! class_exists( 'XLWCTY_Core' ) ) {
	return;
}
WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Xlwcty(), 'xlwcty' );
