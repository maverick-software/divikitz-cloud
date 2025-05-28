<?php
/*
 * Bridge Core
 * https://qodeinteractive.com/
 */

class  WFACP_Compatibility_Bridge_Core {
	public function __construct() {
		add_action( 'init', [ $this, 'register_elementor_widget' ], 9 );
	}

	public function register_elementor_widget() {
		if ( class_exists( 'BridgeCore' ) && class_exists( 'Elementor\Plugin' ) && class_exists( 'WFACP_Core' ) ) {
			if ( is_admin() ) {
				return;
			}

			remove_action( 'init', 'bridge_core_load_elementor_shortcodes' );
			add_action( 'elementor/widgets/widgets_registered', 'bridge_core_load_elementor_shortcodes' );

			if ( WFACP_Common::get_id() > 0 ) {
				$instance = WFACP_Elementor::get_instance();
				add_action( 'elementor/widgets/widgets_registered', [ $instance, 'initialize_widgets' ] );
			}
		}
	}

}


if ( ! class_exists( 'BridgeCore' ) ) {
	return;
}
WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_Bridge_Core(), 'bridge-core' );


