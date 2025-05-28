<?php

/**
 * WooCommerce Fakturownia By WP Desk
 * Author URI: https://www.wpdesk.pl
 * Version: 1.4.3
 */
class WFACP_Compatibility_WC_fakturownia {
	private $add_fields = [ 'billing_faktura', 'billing_nip' ];
	private $new_fields = [];


	public function __construct() {

		/* Register Add field */
		add_action( 'init', [ $this, 'setup_fields_billing' ], 20 );
		add_filter( 'wfacp_html_fields_billing_wfacp_nip', '__return_false' );
		add_action( 'process_wfacp_html', [ $this, 'call_fields_hook' ], 50, 3 );
		add_action( 'woocommerce_billing_fields', function ( $fields ) {
			if ( is_array( $fields ) && count( $fields ) > 0 ) {
				foreach ( $this->add_fields as $i => $field_key ) {
					if ( isset( $fields[ $field_key ] ) ) {
						$this->new_fields[ $field_key ] = $fields[ $field_key ];
					}
				}
			}

			return $fields;
		}, 100 );


		add_filter( 'woocommerce_form_field_args', [ $this, 'add_default_wfacp_styling' ], 10, 2 );

	}


	public function setup_fields_billing() {
		new WFACP_Add_Address_Field( 'wfacp_nip', array(
			'type'         => 'wfacp_html',
			'label'        => __( 'NIP', 'woocommerce-fakturownia' ),
			'palaceholder' => __( 'NIP', 'woocommerce-fakturownia' ),
			'cssready'     => [ 'wfacp-col-left-third' ],
			'class'        => array( 'form-row-third first', 'wfacp-col-full' ),
			'required'     => false,
			'priority'     => 60,
		) );


	}

	public function call_fields_hook( $field, $key, $args ) {

		if ( ( ! empty( $key ) && ( 'billing_wfacp_nip' === $key ) ) ) {
			if ( empty( $this->new_fields ) ) {
				return;
			}
			foreach ( $this->new_fields as $field_key => $field_val ) {
				woocommerce_form_field( $field_key, $field_val );
			}

		}
	}

	public function add_default_wfacp_styling( $args, $key ) {
		if ( array_key_exists( $key, $this->new_fields ) ) {
			$all_cls          = array_merge( [ 'wfacp-form-control-wrapper wfacp-col-full ' ], $args['class'] );
			$args['class']    = $all_cls;
			$args['cssready'] = [ 'wfacp-col-full' ];
		}

		if ( $key === 'billing_nip' ) {
			$input_class         = array_merge( [ 'wfacp-form-control' ], $args['input_class'] );
			$args['input_class'] = $input_class;

			$label_class         = array_merge( [ 'wfacp-form-control-label' ], $args['label_class'] );
			$args['label_class'] = $label_class;

		} elseif ( $key === 'faktura_field' ) {


			$args['label_class'] = [ 'checkbox' ];
			$args['label_class'] = [ 'checkbox' ];
		}

		return $args;
	}

}


if ( ! class_exists( 'FakturowniaVendor\WPDesk\Invoices\Field\FormField' ) ) {
	return;
}
WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_WC_fakturownia(), 'wc-fakturownia' );

