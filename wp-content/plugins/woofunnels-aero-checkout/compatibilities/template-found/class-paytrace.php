<?php

class WFACP_Paytrace {
	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'action' ], 7 );
	}

	public function action() {
		if ( WFACP_Common::is_theme_builder() && class_exists( 'WC_Paytrace_Scripts' ) ) {
			WFACP_Common::remove_actions( 'wp_enqueue_scripts', 'WC_Paytrace_Scripts', 'frontend_scripts' );
		}
	}
}


if ( ! class_exists( 'WC_Paytrace' ) ) {
	return;
}
WFACP_Plugin_Compatibilities::register( new WFACP_Paytrace(), 'wfacp-paytrace' );




