<?php

class WFACP_handle_billing_address {

	private $temp_field = [
		'first_name',
		'last_name',
		'address_1',
		'address_2',
		'city',
		'postcode',
		'state',
		'house_number',
		'street_name',
		'house_number_suffix',
	];
	private $add_fields = [
		'address_1',
		'address_2',
		'city',
		'postcode',
		'state',
	];

	public function __construct() {
		add_action( 'wfacp_outside_header', [ $this, 'attach_hooks' ] );
	}

	public function attach_hooks() {
		if ( ! WFACP_Core()->pay->is_order_pay() ) {
			add_action( 'woocommerce_checkout_after_customer_details', [ $this, 'print_billing_fields' ] );
		}
	}

	public function print_billing_fields() {
		$instance = wfacp_template();
		$fields   = $instance->get_checkout_fields();

		if ( false == $instance->have_billing_address() && $instance->have_shipping_address() ) {
			$address_prefix = 'billing_';
			if ( isset( $fields['billing']['billing_first_name'] ) ) {
				unset( $this->temp_field[0] );
			}
			if ( isset( $fields['billing']['billing_last_name'] ) ) {
				unset( $this->temp_field[1] );
			}
			foreach ( $this->temp_field as $item ) {
				echo sprintf( '<input type="hidden" name="%s" id="%s" class="wfacp_hidden_fields">', $address_prefix . $item, $address_prefix . $item );
				echo "\n";
			}
		} elseif ( $instance->have_shipping_address() && $instance->have_billing_address() ) {

			if ( isset( $fields['shipping']['shipping_first_name'] ) && ! isset( $fields['billing']['billing_first_name'] ) ) {
				echo sprintf( '<input type="hidden" name="%s" id="%s" class="wfacp_hidden_fields">', 'billing_first_name', 'billing_first_name' );
				echo "\n";
			}
			if ( isset( $fields['shipping']['shipping_last_name'] ) && ! isset( $fields['billing']['billing_last_name'] ) ) {
				echo sprintf( '<input type="hidden" name="%s" id="%s" class="wfacp_hidden_fields">', 'billing_last_name', 'billing_last_name' );
				echo "\n";
			}
			if ( isset( $fields['billing']['billing_first_name'] ) && ! isset( $fields['shipping']['shipping_first_name'] ) && ! isset( $fields['advanced']['shipping_first_name'] ) ) {
				echo sprintf( '<input type="hidden" name="%s" id="%s" class="wfacp_hidden_fields">', 'shipping_first_name', 'shipping_first_name' );
				echo "\n";
			}
			if ( isset( $fields['billing']['billing_last_name'] ) && ! isset( $fields['shipping']['shipping_last_name'] ) && ! isset( $fields['advanced']['shipping_last_name'] ) ) {
				echo sprintf( '<input type="hidden" name="%s" id="%s" class="wfacp_hidden_fields">', 'shipping_last_name', 'shipping_last_name' );
				echo "\n";
			}

			foreach ( $this->add_fields as $key ) {
				$b_key = 'billing_' . $key;
				$s_key = 'shipping_' . $key;
				if ( ! isset( $fields['billing'][ $b_key ] ) ) {
					echo sprintf( '<input type="hidden" name="%s" id="%s" class="wfacp_hidden_fields">', $b_key, $b_key );
					echo "\n";
				}
				if ( ! isset( $fields['shipping'][ $s_key ] ) ) {
					echo sprintf( '<input type="hidden" name="%s" id="%s" class="wfacp_hidden_fields">', $s_key, $s_key );
					echo "\n";
				}
			}
		}
	}
}

add_action( 'wfacp_after_template_found', function () {
	new WFACP_handle_billing_address();
} );

