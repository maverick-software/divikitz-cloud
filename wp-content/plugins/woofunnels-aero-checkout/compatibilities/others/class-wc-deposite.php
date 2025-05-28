<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_WC_Deposit {
	public function __construct() {

		/* checkout page */
		add_action( 'woocommerce_checkout_update_order_meta', [ $this, 'save_data_to_parent_order' ], 99, 2 );
		add_action( 'wfacp_analytics_custom_order_status', [ $this, 'add_custom_order_status' ] );
		add_filter( 'wfacp_maybe_update_order', [ $this, 'maybe_update_parent_order' ] );
	}

	public function save_data_to_parent_order( $order_id, $data ) {
		if ( ! class_exists( '\Webtomizer\WCDP\WC_Deposits' ) ) {
			return;
		}
		$already_saved = get_post_meta( $order_id, '_wfacp_post_id', true );

		if ( absint( $already_saved ) > 0 ) {
			return;
		}

		$wfacp_id = absint( $data['wfacp_post_id'] );

		if ( $wfacp_id > 0 ) {
			update_post_meta( $order_id, '_wfacp_post_id', $wfacp_id );
			update_post_meta( $order_id, '_wfacp_source', $data['wfacp_source'] );

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

	public function add_custom_order_status( $status ) {
		if ( ! class_exists( '\Webtomizer\WCDP\WC_Deposits' ) ) {
			return $status;
		}
		$status[] = 'partially-paid';

		return $status;
	}

	public function maybe_update_parent_order( $order ) {

		if ( ! $order instanceof WC_Order ) {
			return $order;
		}

		if ( $order && $order->get_type() === 'wcdp_payment' ) {
			$order = wc_get_order( $order->get_parent_id() );
		}

		return $order;

	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_WC_Deposit(), 'wc_deposit' );
