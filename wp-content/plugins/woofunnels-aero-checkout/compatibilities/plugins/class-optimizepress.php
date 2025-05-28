<?php

class WFACP_Compatibility_With_Optimize_press {
	public function __construct() {
		add_filter( 'wfacp_do_not_allow_shortcode_printing', [ $this, 'do_not_execute' ] );
	}

	public function do_not_execute( $status ) {
		if ( isset( $_REQUEST['page'] ) && false !== strpos( $_REQUEST['page'], 'optimizepress' ) ) {
			$status = true;
		}

		return $status;
	}
}


WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Optimize_press(), 'optimize_press' );