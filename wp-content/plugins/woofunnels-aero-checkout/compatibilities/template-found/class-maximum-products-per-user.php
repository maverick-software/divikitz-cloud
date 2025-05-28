<?php

/**
 * By Algoritmika
 * Class WFACP_Maximum_Products_Per_User
 */
class WFACP_Maximum_Products_Per_User {
	public function __construct() {
		WFACP_Common::remove_actions( 'wp', 'Alg_WC_MPPU_Core', 'block_checkout' );
	}
}


if ( ! class_exists( 'Alg_WC_MPPU_Core' ) ) {
	return;
}
WFACP_Plugin_Compatibilities::register( new WFACP_Maximum_Products_Per_User(), 'mppu' );