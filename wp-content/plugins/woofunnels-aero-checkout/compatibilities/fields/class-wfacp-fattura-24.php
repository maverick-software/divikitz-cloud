<?php

/**
 * WooCommerce Fattura24 by Fattura24.com (Version 5.0.9)
 * Plugin Path: http://www.fattura24.com
 */
class WFACP_Compatibility_Fattura_24 {

	private $add_fields = [
		'billing_checkbox',
		'billing_fiscalcode',
		'billing_vatcode',
		'billing_recipientcode',
		'billing_pecaddress',
	];
	private $new_fields = [];

	public function __construct() {
		/* Register Add field */
		add_action( 'init', [ $this, 'setup_fields_billing' ], 20 );
		add_filter( 'wfacp_html_fields_billing_fattura_24', '__return_false' );
		/* Process Html */
		add_action( 'process_wfacp_html', [ $this, 'call_fields_hook' ], 50, 2 );

		/* Get Billing Checkout fields */
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'action' ] );

		/* Add Default Styling  */
		add_filter( 'woocommerce_form_field_args', [ $this, 'add_default_wfacp_styling' ], 10, 2 );

		/* Add Internal Css for plugin */
		add_filter( 'wfacp_internal_css', [ $this, 'wfacp_internal_css' ] );
	}


	public function setup_fields_billing() {
		new WFACP_Add_Address_Field( 'fattura_24', [
			'type'         => 'wfacp_html',
			'label'        => __( 'Fattura 24', 'woofunnels-aero-checkout' ),
			'palaceholder' => __( 'Fattura 24', 'woofunnels-aero-checkout' ),
			'cssready'     => [ 'wfacp-col-left-third' ],
			'class'        => array( 'form-row-third first', 'wfacp-col-full' ),
			'required'     => false,
			'priority'     => 60,
		] );
	}

	public function checkout_fields( $fields ) {

		if ( ! is_array( $fields['billing'] ) || count( $fields['billing'] ) == 0 ) {
			return $fields;
		}

		foreach ( $this->add_fields as $field_key ) {
			if ( isset( $fields['billing'][ $field_key ] ) ) {
				$this->new_fields[ $field_key ] = $fields['billing'][ $field_key ];
			}
		}

		return $fields;
	}

	public function call_fields_hook( $field, $key ) {

		if ( empty( $key ) || 'billing_fattura_24' !== $key || 0 === count( $this->new_fields ) ) {
			return;
		}

		echo "<div id='wfacp_fattura' class='wfacp_clear'>";
		foreach ( $this->new_fields as $field_key => $field_val ) {
			woocommerce_form_field( $field_key, $field_val );
		}
		echo "</div>";

	}

	public function action() {
		add_action( 'woocommerce_checkout_fields', [ $this, 'checkout_fields' ], 100 );
	}

	public function add_default_wfacp_styling( $args, $key ) {

		if ( 0 === count( $this->new_fields ) || ! array_key_exists( $key, $this->new_fields ) ) {
			return $args;
		}

		if ( isset( $args['type'] ) && 'checkbox' !== $args['type'] ) {

			$args['input_class'] = array_merge( [ 'wfacp-form-control' ], $args['input_class'] );
			$args['label_class'] = array_merge( [ 'wfacp-form-control-label' ], $args['label_class'] );
			$args['class']       = array_merge( [ 'wfacp-form-control-wrapper wfacp-col-left-half ' ], $args['class'] );
			$args['cssready']    = [ 'wfacp-col-left-half' ];

		} else {
			$args['class']    = array_merge( [ 'wfacp-form-control-wrapper wfacp-col-full ' ], $args['class'] );
			$args['cssready'] = [ 'wfacp-col-full' ];
		}


		return $args;
	}

	public function wfacp_internal_css() {

		$instance = wfacp_template();
		if ( ! $instance instanceof WFACP_Template_Common ) {
			return;
		}

		$bodyClass = "body ";
		if ( 'pre_built' !== $instance->get_template_type() ) {

			$bodyClass = "body #wfacp-e-form ";
		}

		$cssHtml = "<style>";
		$cssHtml .= $bodyClass . "#wfacp_fattura .form-row label {text-align: left;position: relative;left: auto;margin: 0;right: auto;top: auto;bottom: auto;}";
		$cssHtml .= $bodyClass . "#wfacp_fattura .form-row.wfacp-anim-wrap label {width: 100%;font-size: 13px;}";
		$cssHtml .= $bodyClass . "#wfacp_fattura input[type='text'] {padding: 10px 12px;}";
		$cssHtml .= $bodyClass . "#wfacp_fattura label a {float: none !important;margin-left: 5px;pointer-events: auto;}";


		$cssHtml .= "</style>";
		echo $cssHtml;

	}
}

if ( ! defined( 'FATT_24_PLUGIN_DATA' ) ) {
	return;
}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_Fattura_24(), 'wfacp-_Fattura-24' );
