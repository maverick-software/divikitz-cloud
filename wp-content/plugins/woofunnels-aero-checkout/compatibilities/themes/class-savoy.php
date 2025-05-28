<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme: Savoy
 * Theme URI: http://themeforest.net/item/savoy-minimalist-ajax-woocommerce-theme/12537825
 * Class WFACP_Compatibility_With_Active_Savoy
 */
class WFACP_Compatibility_With_Active_Savoy {

	public function __construct() {
		/* checkout page */
		add_action( 'wfacp_before_coupon_apply', [ $this, 'remove_actions' ] );
	}

	public function remove_actions() {

		remove_action( 'woocommerce_applied_coupon', 'wc_coupon_yu' );

	}

}

if ( ! defined( 'NM_THEME_DIR' ) ) {
	return;
}
WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Active_Savoy(), 'Savoy' );
