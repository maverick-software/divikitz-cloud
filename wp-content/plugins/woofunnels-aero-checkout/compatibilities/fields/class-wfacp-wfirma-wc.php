<?php

/**
 * WooCommerce wFirma By WP Desk
 * Plugin URI: https://www.wpdesk.pl/sklep/wfirma-woocommerce/
 * Version: 2.2.6
 */
class WFACP_Compatibility_wfirma_wc {
	public function __construct() {
		/* Register Add field */
		$this->setup_fields_billing();
	}

	public function setup_fields_billing() {
		new WFACP_Add_Address_Field( 'nip', array(
			'type'         => 'text',
			'label'        => __( 'NIP', 'woocommerce-wfirma' ),
			'palaceholder' => __( 'NIP', 'woocommerce-wfirma' ),
			'cssready'     => [ 'wfacp-col-full' ],
			'class'        => array( 'form-row-third first', 'wfacp-col-full' ),
			'required'     => false,
			'priority'     => 60,
		) );
	}
}


if ( ! class_exists( 'WPDesk\WooCommerceWFirma\WoocommerceIntegration' ) ) {
	return;
}
WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_wfirma_wc(), 'woocommerce-wfirma' );


