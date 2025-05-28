<?php

if ( class_exists( 'WFACP_CartFlows_Compatibility' ) ) {
	return;
}

class WFACP_CartFlows_Compatibility {
	public function __construct() {
		$this->remove_template_redirect();
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'remove_render_cart_flows_inline_js' ] );
		add_filter( 'wfacp_skip_checkout_page_detection', [ $this, 'disable_aero_checkout_on_cart_flows_template' ] );
	}

	public function remove_template_redirect() {
		WFACP_Common::remove_actions( 'template_redirect', 'Cartflows_Checkout_Markup', 'global_checkout_template_redirect' );
	}

	public function remove_render_cart_flows_inline_js() {
		if ( ! class_exists( 'Cartflows_Tracking' ) ) {
			return;
		}
		WFACP_Common::remove_actions( 'wp_head', 'Cartflows_Tracking', 'wcf_render_gtag' );
	}

	public function disable_aero_checkout_on_cart_flows_template( $status ) {
		global $post;
		if ( $post instanceof WP_Post && 'cartflows_step' === $post->post_type ) {
			return true;
		}

		return $status;
	}
}

if ( ! class_exists( 'Cartflows_Checkout_Markup' ) ) {
	return;
}

return new WFACP_CartFlows_Compatibility();