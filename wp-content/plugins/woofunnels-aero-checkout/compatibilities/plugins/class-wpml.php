<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WFACP_Compatibility_With_WPML {

	public function __construct() {

		/* checkout page */
		add_filter( 'wfacp_wpml_checkout_page_id', [ $this, 'wfacp_wpml_checkout_page_id_function' ], 10, 1 );
		add_action( 'admin_head', [ $this, 'add_admin_css' ] );
		add_filter( 'wfacp_disabled_elementor_duplicate_template', '__return_true' );
		add_action( 'wfacp_disabled_elementor_duplicate_template_placeholder', [ $this, 'duplicate_template' ], 10, 2 );

	}

	public function wfacp_wpml_checkout_page_id_function( $override_checkout_page_id ) {

		if ( ! class_exists( 'WPML_TM_Records' ) ) {
			return $override_checkout_page_id;
		}

		global $wpdb, $wpml_post_translations, $wpml_term_translations;
		$tm_records = new WPML_TM_Records( $wpdb, $wpml_post_translations, $wpml_term_translations );

		try {
			$translations = $tm_records->icl_translations_by_element_id_and_type_prefix( $override_checkout_page_id, 'post_wfacp_checkout' );
			if ( $translations->language_code() !== ICL_LANGUAGE_CODE ) {
				$element_id                = $tm_records->icl_translations_by_trid_and_lang( $translations->trid(), ICL_LANGUAGE_CODE )->element_id();
				$override_checkout_page_id = empty( $element_id ) ? $override_checkout_page_id : $element_id;
			}
		} catch ( Exception $e ) {
			//echo $e->getMessage();
		}


		return $override_checkout_page_id;
	}

	public function duplicate_template( $post_id, $new_post_id ) {
		WFACP_Common::copy_meta( $post_id, $new_post_id );
	}

	public function add_admin_css() {

		echo "<style>";
		echo "body.woofunnels_page_wfacp{position: initial;}";
		echo "</style>";

	}
}

if ( ! class_exists( 'SitePress' ) ) {
	return;
}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_WPML(), 'wfacp_wpml' );
