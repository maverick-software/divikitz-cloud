<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Yoast SEO by Team Yoast
 * Plugin Path : https://yoa.st/1uj
 */
class WFACP_Checkout_Seo_WP {
	public function __construct() {

		/* checkout page */
		add_action( 'save_post', [ $this, 'save_post' ] );
	}

	public function save_post() {

		if ( class_exists( 'Yoast\WP\SEO\Integrations\Watchers\Indexable_Post_Watcher' ) && isset( $_POST['action'] ) && 'wfacp_import_template' === $_POST['action'] ) {
			WFACP_Common::remove_actions( 'wp_insert_post', 'Yoast\WP\SEO\Integrations\Watchers\Indexable_Post_Watcher', 'build_indexable' );
		}


	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Checkout_Seo_WP(), 'wfacp-seo-wp' );
