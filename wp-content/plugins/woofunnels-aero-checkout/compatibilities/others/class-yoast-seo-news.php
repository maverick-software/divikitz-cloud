<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Checkout_Yoast_Seo_News {
	public function __construct() {
		/* checkout page */
		$this->actions();
	}

	public function actions() {
		if ( WFACP_Common::is_theme_builder() ) {
			remove_action( 'plugins_loaded', '__wpseo_news_main' );
		}
	}

}

add_action( 'wfacp_loaded', function () {
	if ( ! function_exists( '__wpseo_news_main' ) ) {
		return;
	}
	WFACP_Plugin_Compatibilities::register( new WFACP_Checkout_Yoast_Seo_News(), 'yoas_seo_news' );
} );

