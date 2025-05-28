<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * this official WooCommerce MyParcel plugin by MyParcel
 * URL: https://www.myparcel.nl
 */
class WFACP_Compatibility_With_WC_MyParcel_2_1_4 {
	private static $instance = null;
	private $page_version = null;

	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		$this->page_version = WFACP_Common::get_checkout_page_version();
		if ( ! version_compare( $this->page_version, '2.1.3', '>' ) ) {
			return;
		}
		$this->setup_fields_billing();
	}

	public function setup_fields_billing() {
		new WFACP_Add_Address_Field( 'street_name', array(
			'label'       => __( 'Street name', 'woocommerce-myparcel' ),
			'placeholder' => __( 'Street name', 'woocommerce-myparcel' ),
			'class'       => [ 'form-row-first', 'address-field', 'wfacp_street_name', 'wfacp-draggable' ],
			'cssready'    => [ 'wfacp-col-full' ],
			'clear'       => false,
			'required'    => false,
			'priority'    => 90,
		) );

		new WFACP_Add_Address_Field( 'house_number', array(
			'label'       => __( 'No.', 'woocommerce-myparcel' ),
			'placeholder' => __( 'No.', 'woocommerce-myparcel' ),
			'class'       => [ 'form-row-first', 'address-field', 'wfacp_house_number', 'wfacp-draggable' ],
			'cssready'    => [ 'wfacp-col-full' ],
			'clear'       => false,
			'required'    => false,
			'priority'    => 90,
		) );

		new WFACP_Add_Address_Field( 'house_number_suffix', array(
			'label'       => __( 'Suffix', 'woocommerce-myparcel' ),
			'placeholder' => __( 'Suffix', 'woocommerce-myparcel' ),
			'class'       => [ 'form-row-first', 'address-field', 'wfacp_house_number_suffix', 'wfacp-draggable' ],
			'cssready'    => [ 'wfacp-col-full' ],
			'clear'       => false,
			'required'    => false,
			'priority'    => 90,
		) );

		new WFACP_Add_Address_Field( 'street_name', array(
			'label'       => __( 'Street name', 'woocommerce-myparcel' ),
			'placeholder' => __( 'Street name', 'woocommerce-myparcel' ),
			'class'       => [ 'form-row-first', 'address-field', 'wfacp_street_name', 'wfacp-draggable' ],
			'cssready'    => [ 'wfacp-col-full' ],
			'clear'       => false,
			'required'    => false,
			'priority'    => 60,
		), 'shipping' );

		new WFACP_Add_Address_Field( 'house_number', array(
			'label'       => __( 'No.', 'woocommerce-myparcel' ),
			'placeholder' => __( 'No.', 'woocommerce-myparcel' ),
			'class'       => [ 'form-row-first', 'address-field', 'wfacp_house_number', 'wfacp-draggable' ],
			'cssready'    => [ 'wfacp-col-full' ],
			'clear'       => false,
			'required'    => false,
			'priority'    => 60,
		), 'shipping' );

		new WFACP_Add_Address_Field( 'house_number_suffix', array(
			'label'       => __( 'Suffix', 'woocommerce-myparcel' ),
			'placeholder' => __( 'Suffix', 'woocommerce-myparcel' ),
			'class'       => [ 'form-row-first', 'address-field', 'wfacp_house_number_suffix', 'wfacp-draggable' ],
			'cssready'    => [ 'wfacp-col-full' ],
			'clear'       => false,
			'required'    => false,
			'priority'    => 60,
		), 'shipping' );
	}
}


if ( ! class_exists( 'WCMYPA' ) ) {
	return;
}
WFACP_Compatibility_With_WC_MyParcel_2_1_4::get_instance();



