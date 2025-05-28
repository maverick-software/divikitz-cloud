<?php

/**
 * German Market By MarketPress
 * Plugin URI: https://marketpress.de/shop/plugins/woocommerce-german-market/
 */
class WFACP_Compatibility_WC_German_Market {

	public function __construct() {
		add_filter( 'init', [ $this, 'add_field' ], 20 );
		add_action( 'wfacp_checkout_preview_form_start', [ $this, 're_display_payment_section' ] );
	}

	public function add_field() {
		new WFACP_Add_Address_Field( 'vat', array(
			'label'    => get_option( 'vat_options_label', __( 'EU VAT Identification Number (VATIN)', 'woocommerce-german-market' ) ),
			'cssready' => [ 'wfacp-col-left-half' ],
			'required' => false,
			'priority' => apply_filters( 'wcvat_vat_field_priority', 49 ),
		) );
	}

	public function re_display_payment_section() {
		add_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
	}

}


if ( ! class_exists( 'Woocommerce_German_Market' ) ) {
	return;
}
WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_WC_German_Market(), 'wfacp-wc-german-market' );
