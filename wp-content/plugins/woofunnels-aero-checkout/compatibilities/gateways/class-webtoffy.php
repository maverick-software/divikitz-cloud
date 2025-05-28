<?php

/**
 * Compatibility  for 'PayPal Express Checkout Payment Gateway for WooCommerce ( Basic )' plugin
 * By webtoffee
 */
class WFACP_EH_PAYPAL_Express {
	public function __construct() {
		add_action( 'woocommerce_checkout_update_order_meta', [ $this, 'update_custom_fields' ], 10, 2 );
	}

	public function update_custom_fields( $order_id, $posted_data ) {

		if ( ! isset( $posted_data['_wfacp_post_id'] ) || ( ! isset( $posted_data['payment_method'] ) || $posted_data['payment_method'] !== 'eh_paypal_express' ) ) {
			return;
		}

		$wfacp_id = absint( $posted_data['_wfacp_post_id'] );
		if ( $wfacp_id > 0 ) {
			update_post_meta( $order_id, '_wfacp_post_id', $wfacp_id );
			update_post_meta( $order_id, '_wfacp_source', $posted_data['wfacp_source'] );
			if ( isset( $_POST['wfacp_timezone'] ) ) {
				update_post_meta( $order_id, '_wfacp_timezone', wc_clean( $_POST['wfacp_timezone'] ) );
			}

			$cfields = WFACP_Common::get_page_custom_fields( $wfacp_id );
			if ( ! isset( $cfields['advanced'] ) ) {
				return;
			}
			$advancedFields = $cfields['advanced'];
			if ( ! is_array( $advancedFields ) || count( $advancedFields ) == 0 ) {
				return;
			}
			foreach ( $advancedFields as $field_key => $field ) {
				if ( isset( $_REQUEST[ $field_key ] ) ) {
					$field_value = $_REQUEST[ $field_key ];
					if ( ! empty( $field_value ) && $field['type'] == 'date' ) {
						$field_value = date( 'Y-m-d', strtotime( $field_value ) );
					} elseif ( ! empty( $field_value ) && $field['type'] == 'wfacp_dob' ) {
						$field_value = $_REQUEST[ $field_key ]['year'] . '-' . $_REQUEST[ $field_key ]['month'] . '-' . $_REQUEST[ $field_key ]['day'];
					}
					if ( $field['type'] != 'multiselect' ) {
						$field_value = wc_clean( $field_value );
					}
					update_post_meta( $order_id, $field_key, $field_value );
				}
			}
		}
	}

}

if ( ! defined( 'EH_PAYPAL_VERSION' ) ) {

	return;
}
new WFACP_EH_PAYPAL_Express();
