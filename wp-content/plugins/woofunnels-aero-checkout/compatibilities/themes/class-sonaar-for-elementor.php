<?php

/**
 * Elementor Sonaar Addons
 * https://sonaar.io/
 * Sonaar Music
 */
class WFACP_Compatibility_With_Soonar_El {

	public function __construct() {
		$this->register_elementor_widget();

	}

	public function register_elementor_widget() {

		if ( is_admin() ) {
			return;
		}
		if ( true == wfacp_elementor_edit_mode() ) {
			return;
		}
		$r_instance = WFACP_Common::remove_actions( 'init', 'Elementor_Sonaar', 'sr_init_extensions' );
		if ( $r_instance instanceof Elementor_Sonaar ) {
			add_action( 'wp', array( $r_instance, 'sr_init_extensions' ), 100 );
		}


	}
}

if ( ! class_exists( 'Elementor_Sonaar' ) || ! class_exists( 'Elementor\Plugin' ) ) {
	return;
}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Soonar_El(), 'soonar_el' );
