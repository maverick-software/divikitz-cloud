<?php
if ( ! function_exists( 'wfacp_is_elementor' ) ) {
	function wfacp_is_elementor() {

		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			return \Elementor\Plugin::$instance->db->is_built_with_elementor( WFACP_Common::get_id() );
		}

		return false;
	}

}
if ( ! function_exists( 'wfacp_elementor_edit_mode' ) ) {
	function wfacp_elementor_edit_mode() {
		$status = false;
		if ( isset( $_REQUEST['elementor-preview'] ) || ( isset( $_REQUEST['action'] ) && ( 'elementor' == $_REQUEST['action'] || 'elementor_ajax' == $_REQUEST['action'] ) ) ) {
			$status = true;

		}
		if ( ( isset( $_REQUEST['preview_id'] ) && isset( $_REQUEST['preview_nonce'] ) ) || isset( $_REQUEST['elementor-preview'] ) ) {
			$status = true;
		}

		return $status;
	}
}

if ( ! function_exists( 'wfacp_template' ) ) {
	/**
	 * Return instance of Current Template Class
	 * @return WFACP_Template_Common
	 */
	function wfacp_template() {
		if ( is_null( WFACP_Core()->template_loader ) ) {
			return null;
		}

		return WFACP_Core()->template_loader->get_template_ins();
	}
}