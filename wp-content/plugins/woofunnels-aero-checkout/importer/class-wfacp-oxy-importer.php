<?php

/**
 * Elementor template library local source.
 *
 * Elementor template library local source handler class is responsible for
 * handling local Elementor templates saved by the user locally on his site.
 *
 * @since 1.0.0
 */


class WFACP_Oxy_Importer {
	private $slug = '';
	private $post_id = 0;
	private $settings_file = '';
	private $builder = 'oxy';
	public $delete_page_meta = true;

	public function __construct() {
	}


	public function import( $aero_id, $slug, $is_multi = 'no' ) {//phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedParameter
		$this->slug    = $slug;
		$this->post_id = $aero_id;


		$this->update_product_switcher_settings();

		$templates           = WFACP_Core()->template_loader->get_templates( $this->builder );
		$this->settings_file = $templates[ $slug ]['settings_file'];

		if ( $templates[ $slug ] && isset( $templates[ $slug ]['build_from_scratch'] ) ) {
			delete_post_meta( $aero_id, 'ct_other_template' );
			$this->save_data( $aero_id );
			update_post_meta( $aero_id, 'ct_builder_shortcodes', ' ' );

			return [ 'status' => true ];
		}

		$data = WFACP_Core()->importer->get_remote_template( $slug, 'oxy' );

		if ( isset( $data['error'] ) ) {
			return $data;
		}

		$content = $data['data'];

		if ( ! empty( $content ) && ( false == strpos( $content, '<script' ) ) ) {
			update_post_meta( $aero_id, 'ct_other_template', '-1' );
			update_post_meta( $aero_id, 'ct_builder_shortcodes', $content );
			$this->save_data( $this->post_id );

			return [ 'status' => true ];
		}


		return [ 'status' => true ];

	}


	public function update_product_switcher_settings() {
		if ( false !== strpos( $this->slug, 'oxy_' ) ) {
			$pageProductSetting = [
				'coupons'                             => '',
				'enable_coupon'                       => 'false',
				'disable_coupon'                      => 'false',
				'hide_quantity_switcher'              => 'false',
				'enable_delete_item'                  => 'false',
				'hide_product_image'                  => 'false',
				'is_hide_additional_information'      => 'true',
				'additional_information_title'        => WFACP_Common::get_default_additional_information_title(),
				'hide_quick_view'                     => 'false',
				'hide_you_save'                       => 'true',
				'hide_best_value'                     => 'false',
				'best_value_product'                  => '',
				'best_value_text'                     => 'Best Value',
				'best_value_position'                 => 'above',
				'enable_custom_name_in_order_summary' => 'false',
				'autocomplete_enable'                 => 'false',
				'autocomplete_google_key'             => '',
				'preferred_countries_enable'          => 'false',
				'preferred_countries'                 => '',
				'product_switcher_template'           => 'default',
			];

			$product_settings                     = [];
			$product_settings['settings']         = $pageProductSetting;
			$product_settings['products']         = [];
			$product_settings['default_products'] = [];
			if ( is_array( $product_settings ) && count( $product_settings ) > 0 ) {
				update_post_meta( $this->post_id, '_wfacp_product_switcher_setting', $product_settings );
			}
		}
	}

	private function save_data( $post_id, $content = '' ) {//phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedParameter
		if ( true === $this->delete_page_meta ) {
			$this->delete_template_data( $post_id );
		}

		$file_path = __DIR__ . '/checkout-settings/' . $this->settings_file;

		WFACP_Common::import_checkout_settings( $post_id, $file_path );

	}

	private function delete_template_data( $post_id ) {
		WFACP_Common::delete_page_layout( $post_id );
	}

}

if ( class_exists( 'WFACP_Template_Importer' ) ) {

	WFACP_Template_Importer::register( 'oxy', new WFACP_OXY_Importer() );
}