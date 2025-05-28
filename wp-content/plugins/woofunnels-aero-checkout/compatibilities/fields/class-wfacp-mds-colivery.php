<?php

/**
 * MDS Collivery By MDS Technologies
 * Plugin URI: https://collivery.net/integration/woocommerce
 */
class WFACP_Compatibility_Colivery {

	private $checkout_keys = [];

	public function __construct() {

		add_action( 'init', [ $this, 'setup_fields_billing' ], 20 );
		add_action( 'init', [ $this, 'setup_fields_shipping' ], 20 );
		add_action( 'wfacp_forms_field', [ $this, 'wfacp_forms_field' ], 20, 2 );
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'assign_data' ], 20 );
	}

	public function assign_data() {

		if ( false === $this->is_enabled() ) {
			return;
		}

		if ( ! class_exists( '\MdsSupportingClasses\MdsCheckoutFields' ) ) {
			return;
		}

		$mdsCheckoutFields = new \MdsSupportingClasses\MdsCheckoutFields( [] );

		if ( ! $mdsCheckoutFields instanceof MdsSupportingClasses\MdsCheckoutFields ) {
			return;
		}
		$address_fields['billing']  = $mdsCheckoutFields->getCheckoutFields( 'billing' );
		$address_fields['shipping'] = $mdsCheckoutFields->getCheckoutFields( 'shipping' );
		$data                       = [ 'billing', 'shipping' ];

		$finalData = [];
		if ( is_array( $data ) && count( $data ) > 0 ) {
			foreach ( $data as $key => $value ) {
				$_city          = $value . "_city";
				$_suburb        = $value . "_suburb";
				$_location_type = $value . "_location_type";
				if ( isset( $address_fields[ $value ] ) && isset( $address_fields[ $value ][ $_city ] ) ) {
					$finalData[ $_city ] = $address_fields[ $value ][ $_city ];
				}
				if ( isset( $address_fields[ $value ] ) && isset( $address_fields[ $value ][ $_suburb ] ) ) {
					$finalData[ $_suburb ] = $address_fields[ $value ][ $_suburb ];
				}
				if ( isset( $address_fields[ $value ] ) && isset( $address_fields[ $value ][ $_location_type ] ) ) {
					$finalData[ $_location_type ] = $address_fields[ $value ][ $_location_type ];
				}


			}
			$this->checkout_keys = $finalData;
		}


	}


	public function setup_fields_billing() {

		if ( false === $this->is_enabled() ) {
			return;
		}

		new WFACP_Add_Address_Field( 'suburb', array(
			'label'    => __( 'Suburb', 'woocommerce' ),
			'cssready' => [ 'wfacp-col-left-third' ],
			'class'    => apply_filters( 'colivery_custom_address_field_class', array( 'form-row-third first', 'wfacp-col-full' ) ),
			'required' => true,

			'priority' => 60,
		) );
		new WFACP_Add_Address_Field( 'location_type', array(
			'label'    => __( 'Location Type', 'woocommerce' ),
			'cssready' => [ 'wfacp-col-left-third' ],
			'class'    => apply_filters( 'colivery_custom_address_field_class', array( 'form-row-third first', 'wfacp-col-full' ) ),
			'required' => true,

			'priority' => 60,
		) );

	}

	public function setup_fields_shipping() {

		if ( false === $this->is_enabled() ) {
			return;
		}

		new WFACP_Add_Address_Field( 'suburb', array(
			'label'    => __( 'Suburb', 'woocommerce' ),
			'cssready' => [ 'wfacp-col-left-third' ],
			'class'    => apply_filters( 'colivery_custom_address_field_class', array( 'form-row-third first', 'wfacp-col-full' ) ),
			'required' => true,

			'priority' => 60,
		), 'shipping' );
		new WFACP_Add_Address_Field( 'location_type', array(
			'label'    => __( 'Location Type', 'woocommerce' ),
			'cssready' => [ 'wfacp-col-left-third' ],
			'class'    => apply_filters( 'colivery_custom_address_field_class', array( 'form-row-third first', 'wfacp-col-full' ) ),
			'required' => true,

			'priority' => 60,
		), 'shipping' );

	}

	public function wfacp_forms_field( $field, $key ) {

		if ( false === $this->is_enabled() ) {
			return $field;
		}
		if ( ! is_array( $this->checkout_keys ) || count( $this->checkout_keys ) == 0 ) {
			return $field;
		}

		if ( ! isset( $this->checkout_keys[ $key ]['options'] ) ) {
			return $field;
		}

		if ( $key === 'billing_city' || $key === 'shipping_city' ) {
			$field['type']    = 'select';
			$field['options'] = $this->checkout_keys[ $key ]['options'];
		}

		if ( $key === 'billing_location_type' || $key === 'billing_location_type' ) {

			$field['type']    = 'select';
			$field['options'] = $this->checkout_keys[ $key ]['options'];
		}
		if ( $key === 'billing_suburb' || $key === 'billing_suburb' ) {
			$field['type']    = 'select';
			$field['options'] = $this->checkout_keys[ $key ]['options'];

		}


		return $field;

	}

	public function is_enabled() {
		return class_exists( 'MdsColliveryService' );
	}

}


if ( ! class_exists( 'MdsColliveryService' ) ) {
	return;
}
WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_Colivery(), 'mds-colivery' );

