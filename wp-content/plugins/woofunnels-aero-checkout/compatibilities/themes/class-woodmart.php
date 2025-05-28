<?php

class WFACP_Compatibility_WoodMart_Theme {
	public function __construct() {
		add_action( 'after_setup_theme', [ $this, 'register_elementor_widget' ], 20 );

		add_action( 'wfacp_after_checkout_page_found', [ $this, 'action' ], 20 );
		add_action( 'wfacp_internal_css', [ $this, 'internal_css' ] );

	}

	public function register_elementor_widget() {
		if ( defined( 'WOODMART_THEME_DIR' ) && class_exists( 'Elementor\Plugin' ) && class_exists( 'WFACP_Core' ) ) {
			if ( is_admin() ) {
				return;
			}
			if ( false == wfacp_elementor_edit_mode() ) {

				$r_instance = WFACP_Common::remove_actions( 'init', 'WOODMART_Theme', 'elementor_files_include' );

				if ( $r_instance instanceof WOODMART_Theme ) {
					add_action( 'wp', array( $r_instance, 'elementor_files_include' ), 9 );

				}
			}


		}
	}

	public function action() {
		add_filter( 'body_class', [ $this, 'remove_class' ] );
	}

	public function remove_class( $body_class ) {

		$notification_key = array_search( "notifications-sticky", $body_class );
		if ( isset( $body_class[ $notification_key ] ) ) {
			unset( $body_class[ $notification_key ] );
		}


		return $body_class;
	}

	public function enable() {
		if ( ! defined( 'WOODMART_THEME_DIR' ) ) {
			return false;
		}

		return true;
	}

	public function internal_css() {
		if ( ! $this->enable() ) {
			return;
		}
		$instance = wfacp_template();
		if ( ! $instance instanceof WFACP_Template_Common ) {
			return;
		}

		$bodyClass = "body";
		if ( 'pre_built' !== $instance->get_template_type() ) {

			$bodyClass = "body #wfacp-e-form ";
		}


		$cssHtml = "<style>";
		$cssHtml .= $bodyClass . ".select2-container--default .select2-selection--single .select2-selection__rendered{padding-right: 12px !important;padding-left: 12px !important;}";
		$cssHtml .= "</style>";
		echo $cssHtml;

	}
}

if ( ! function_exists( 'woodmart_load_classes' ) ) {
	return;
}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_WoodMart_Theme(), 'woodmart' );
