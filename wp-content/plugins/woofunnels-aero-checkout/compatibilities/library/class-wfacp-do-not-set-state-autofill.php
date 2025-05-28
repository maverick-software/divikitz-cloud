<?php

class WFACP_Do_Not_Fill_State {
	public function __construct() {
		if ( is_user_logged_in() ) {
			return;
		}
		add_filter( 'wfacp_default_values', [ $this, 'do_no_set_default_value' ], 10, 2 );
		add_filter( 'woocommerce_form_field_args', [ $this, 'unset_state_default_value' ], 10, 2 );
		add_action( 'wp_footer', [ $this, 'js' ] );
	}

	public function js() {
		wp_add_inline_script( 'wfacp_checkout_js', $this->get_script() );
	}

	private function get_script() {
		$js = "(function () {
        wfacp_frontend.hooks.addFilter('wfacp_skip_keys_before_localize', function (keys) {
        keys.push('shipping_state');
        keys.push('billing_state');
        return keys;
        });
        })(jQuery)";

		return $js;
	}

	public function do_no_set_default_value( $field_value, $key ) {
		if ( did_action( 'wfacp_checkout_preview_form_start' ) > 0 ) {
			return $field_value;
		}
		if ( $this->match( $key ) ) {
			$field_value = null;
		}

		return $field_value;
	}

	public function unset_state_default_value( $args, $key ) {
		if ( did_action( 'wfacp_checkout_preview_form_start' ) > 0 ) {
			return $args;
		}
		if ( isset( $args['default'] ) && $this->match( $key ) ) {
			$args['default'] = '';
		}

		return $args;
	}

	private function match( $key ) {
		return ( false !== strpos( $key, '_state' ) );
	}
}

add_action( 'wfacp_after_checkout_page_found', function () {
	new WFACP_Do_Not_Fill_State();
} );


