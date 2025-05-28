<?php

class WFACP_OXY_Summary extends WFACP_OXY_HTML_BLOCK {


	public $slug = 'wfacp_checkout_form_summary';
	protected $id = 'wfacp_order_summary_widget';
	protected $get_local_slug = 'order_summary';

	public function __construct() {
		$this->name = __( 'Mini Cart', 'woofunnels-aero-checkout' );
		parent::__construct();

	}

	function name() {
		return $this->name;
	}


	/**
	 * @param $template WFACP_Template_Common;
	 */
	public function setup_data( $template ) {//phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedParameter
		$this->mini_cart();
	}


	protected function mini_cart() {
		$tab_id = $this->add_tab( __( 'Heading', 'woofunnels-aero-checkout' ) );
		$this->add_text( $tab_id, 'mini_cart_heading', __( 'Title', 'woofunnels-aero-checkout' ), __( 'Order Summary', 'woofunnels-aero-checkout' ) );
		$this->add_typography( $tab_id, 'mini_cart_section_typo', '.wfacp_mini_cart_start_h .wfacp-order-summary-label', __( 'Heading Typography', 'woofunnels-aero-checkout' ) );


		$cart_id = $this->add_tab( __( 'Cart', 'woocommerce' ) );
		$this->add_switcher( $cart_id, 'enable_product_image', __( 'Image', 'woofunnels-aero-checkout' ), 'on' );
		$this->add_switcher( $cart_id, 'enable_quantity_number', __( 'Quantity Count', 'woofunnels-aero-checkout' ), 'on' );
		$this->add_switcher( $cart_id, 'enable_quantity_box', __( 'Quantity Switcher', 'woofunnels-aero-checkout' ), 'on' );
		$this->add_switcher( $cart_id, 'enable_delete_item', __( 'Allow Deletion', 'woofunnels-aero-checkout' ), 'on' );

		$this->ajax_session_settings[] = 'mini_cart_heading';
		$this->ajax_session_settings[] = 'enable_product_image';
		$this->ajax_session_settings[] = 'enable_quantity_number';
		$this->ajax_session_settings[] = 'enable_quantity_box';
		$this->ajax_session_settings[] = 'enable_delete_item';

		$mini_cart_product_typo = [
			'.wfacp_mini_cart_start_h .wfacp_order_summary_container tr.cart_item td:first-child .product-name .wfacp_mini_cart_item_title',
			'.wfacp_mini_cart_start_h .wfacp_order_summary_container tr.cart_item td:first-child .product-name .wfacp_mini_cart_item_title span',
		];

		$this->add_typography( $cart_id, 'mini_cart_product_typo', implode( ',', $mini_cart_product_typo ), __( 'Product Typography', 'wooofunels-aero-checkout' ) );
		$mini_cart_product_price_typo = [
			'.wfacp_mini_cart_start_h .wfacp_order_summary_container tr.cart_item td:last-child',
			'.wfacp_mini_cart_start_h .wfacp_order_summary_container tr.cart_item td:last-child p',
			'.wfacp_mini_cart_start_h .wfacp_order_summary_container tr.cart_item td:last-child span',
			'.wfacp_mini_cart_start_h .wfacp_order_summary_container tr.cart_item td:last-child span.amount',
			'.wfacp_mini_cart_start_h .wfacp_order_summary_container tr.cart_item td:last-child span bdi',

		];

		$this->add_typography( $cart_id, 'mini_cart_product_price_typo', implode( ', ', $mini_cart_product_price_typo ), __( 'Price Typography', 'woofunnels-aero-checkout' ) );


		$mini_cart_product_variant_typo = [
			'.wfacp_mini_cart_start_h .wfacp_order_summary_container tr.cart_item td:first-child .product-name dl',
			'.wfacp_mini_cart_start_h .wfacp_order_summary_container tr.cart_item td:first-child .product-name dt',
			'.wfacp_mini_cart_start_h .wfacp_order_summary_container tr.cart_item td:first-child .product-name dd',
			'.wfacp_mini_cart_start_h .wfacp_order_summary_container tr.cart_item td:first-child .product-name dd p',
			'.wfacp_mini_cart_start_h .wfacp_order_summary_container tr.cart_item td:first-child .subscription-details',
			'.wfacp_mini_cart_start_h .wfacp_order_summary_container tr.cart_item td:first-child .wfacp_product_subs_details span',
			'.wfacp_mini_cart_start_h .wfacp_order_summary_container tr.cart_item td:first-child .wfacp_product_subs_details span bdi',
			'.wfacp_mini_cart_start_h .wfacp_order_summary_container tr.cart_item td:first-child .subscription-details span',
			'.wfacp_mini_cart_start_h .wfacp_order_summary_container tr.cart_item td:first-child .subscription-details span.amount ',
			'.wfacp_mini_cart_start_h .wfacp_order_summary_container tr.cart_item td:first-child .subscription-details span.amount bdi',
			'.wfacp_mini_cart_start_h .wfacp_order_summary_container tr.cart_item td:first-child .subscription-details span p',
		];

		$this->add_typography( $cart_id, 'mini_cart_product_variant_typo', implode( ', ', $mini_cart_product_variant_typo ), __( 'Variant Typography', 'woofunnels-aero-checkout' ) );


		$this->add_border_color( $cart_id, 'mini_cart_product_image_border_color', '.wfacp_mini_cart_start_h .wfacp_order_sum .product-image', '', __( 'Image Border Color', 'woofunnel-aero-checkout' ) );


		$this->add_heading( $cart_id, __( 'Divider', 'woocommerce' ) );

		$border_color = [
			'.wfacp_mini_cart_start_h .wfacp_mini_cart_divi .cart_item',
			'.wfacp_mini_cart_start_h table.shop_table tr.cart-subtotal',
			'.wfacp_mini_cart_start_h table.shop_table tr.order-total',
			'.wfacp_mini_cart_start_h table.shop_table tr.wfacp_ps_error_state td',
			'.wfacp_wrapper_start.wfacp_mini_cart_start_h .wfacp-coupon-section .wfacp-coupon-page',
			'.wfacp_wrapper_start.wfacp_mini_cart_start_h .wfacp_mini_cart_elementor .cart_item',
			'.wfacp_mini_cart_start_h .wfacp-coupon-section .wfacp-coupon-page',
		];
		$this->add_border_color( $cart_id, 'mini_cart_divider_color', implode( ',', $border_color ), '', __( 'Color', 'woofunnel-aero-checkout' ) );


		$enable_coupon = [
			'enable_coupon' => 'on'
		];

		/* Subtotal Fields */
		$subtotal_id               = $this->add_tab( __( 'Subtotal', 'woocommerce' ) );
		$subtotal_price_label_typo = [
			'.wfacp_mini_cart_start_h .wfacp_order_summary_container table.wfacp_mini_cart_reviews tr:not(.order-total) td:first-child',
			'.wfacp_mini_cart_start_h .wfacp_order_summary_container table.wfacp_mini_cart_reviews tr:not(.order-total) th:first-child',
		];
		$this->add_typography( $subtotal_id, $this->slug . '_subtotal_price_label_typo', implode( ', ', $subtotal_price_label_typo ), __( 'Label Typography', 'woofunnels-aero-checkout' ) );

		$subtotal_price_typo = [
			'.wfacp_mini_cart_start_h .wfacp_order_summary_container table.wfacp_mini_cart_reviews tr:not(.order-total) td:last-child',
			'.wfacp_mini_cart_start_h .wfacp_order_summary_container table.wfacp_mini_cart_reviews tr:not(.order-total) td:last-child span.amount',
			'.wfacp_mini_cart_start_h .wfacp_order_summary_container table.wfacp_mini_cart_reviews tr:not(.order-total) td:last-child span',
			'.wfacp_mini_cart_start_h .wfacp_order_summary_container table.wfacp_mini_cart_reviews tr:not(.order-total) td:last-child span bdi',
			'.wfacp_mini_cart_start_h .wfacp_order_summary_container table.wfacp_mini_cart_reviews tr:not(.order-total) td:last-child a',

		];

		$this->add_typography( $subtotal_id, $this->slug . '_subtotal_price_typo', implode( ', ', $subtotal_price_typo ), __( 'Price Typography', 'woofunnels-aero-checkout' ) );
		/* End */


		/* Total Fields */
		$total_id               = $this->add_tab( __( 'Total', 'woocommerce' ) );
		$total_price_label_typo = [
			'.wfacp_mini_cart_start_h table.shop_table tr.order-total td:first-child',
			'.wfacp_mini_cart_start_h table.shop_table tr.order-total th:first-child',
		];
		$this->add_typography( $total_id, $this->slug . '_total_price_label_typo', implode( ', ', $total_price_label_typo ), __( 'Label Typography', 'woofunnels-aero-checkout' ) );

		$total_price_typo = [
			'.wfacp_mini_cart_start_h table.shop_table tr.order-total td span *',
			'.wfacp_mini_cart_start_h table.shop_table tr.order-total td span bdi',
			'.wfacp_mini_cart_start_h table.shop_table tr.order-total td span.amount',
			'.wfacp_mini_cart_start_h table.shop_table tr.order-total td small',
			'.wfacp_mini_cart_start_h table.shop_table tr.order-total td:last-child',
			'.wfacp_mini_cart_start_h table.shop_table tr.order-total th:last-child',
		];
		$this->add_typography( $total_id, $this->slug . '_total_price_typo', implode( ', ', $total_price_typo ), __( 'Price Typography', 'woofunnels-aero-checkout' ) );
		/* End */


		$coupon_tab_id = $this->add_tab( __( 'Coupon', 'woofunnels-aero-checkout' ) );
		$this->add_switcher( $coupon_tab_id, 'enable_coupon', __( 'Enable Coupon', 'woofunnels-aero-checkout' ), 'off' );
		$this->add_switcher( $coupon_tab_id, 'enable_coupon_collapsible', __( 'Collapsible Coupon', 'woofunnels-aero-checkout' ), 'off', [ 'enable_coupon' => 'on' ] );

		$this->add_text( $coupon_tab_id, 'mini_cart_coupon_button_text', __( 'Coupon Button Text', 'woofunnels-aero-checkout' ), __( 'Apply Coupon', 'woocommerce' ), [ 'enable_coupon' => 'on' ] );
		$this->ajax_session_settings[] = 'enable_coupon';
		$this->ajax_session_settings[] = 'enable_coupon_collapsible';
		$this->ajax_session_settings[] = 'mini_cart_coupon_button_text';

		$this->add_typography( $coupon_tab_id, 'mini_cart_coupon_heading_typo', '.wfacp_mini_cart_start_h .wfacp-coupon-section .wfacp-coupon-page .wfacp_main_showcoupon', __( 'Link Typography' ) );


		$this->add_typography( $coupon_tab_id, 'wfacp_form_mini_cart_coupon_label_typo', '.wfacp_mini_cart_start_h form.checkout_coupon.woocommerce-form-coupon p .wfacp-form-control-label', __( 'Label Typography', 'woofunnels-aero-checkout' ) );
		$this->add_typography( $coupon_tab_id, 'wfacp_form_mini_cart_coupon_input_typo', '.wfacp_mini_cart_start_h form.checkout_coupon.woocommerce-form-coupon .wfacp-form-control', __( 'Coupon Field Typography' ) );
		$this->add_border_color( $coupon_tab_id, 'wfacp_form_mini_cart_coupon_focus_color', '.wfacp_mini_cart_start_h form.checkout_coupon.woocommerce-form-coupon .wfacp-form-control:focus', '#61bdf7', __( 'Focus Color', 'woofunnel-aero-checkout' ), false, $enable_coupon );
		$this->add_border( $coupon_tab_id, 'wfacp_form_mini_cart_coupon_border', '.wfacp_mini_cart_start_h form.checkout_coupon.woocommerce-form-coupon .wfacp-form-control', __( 'Coupon Field Border' ) );

		/* Button Typography*/

		$this->add_heading( $coupon_tab_id, __( 'Button', 'woocommerce' ) );

		$this->add_sub_heading( $coupon_tab_id, __( 'Typography', 'woocommerce' ) );
		$default = [
			'font_size' => '16',
		];
		$this->custom_typography( $coupon_tab_id, $this->slug . '_coupon_button_typo', '.wfacp_mini_cart_start_h button.wfacp-coupon-btn', '', $default );


		/* Button color setting */
		$this->add_sub_heading( $coupon_tab_id, __( 'Color', 'woocommerce' ) );

		$this->add_background_color( $coupon_tab_id, 'mini_cart_coupon_btn_color', '.wfacp_mini_cart_start_h button.wfacp-coupon-btn', '#999', __( 'Button Background', 'woofunnels-aero-checkout' ) );
		$this->add_color( $coupon_tab_id, 'mini_cart_coupon_btn_lable_color', '.wfacp_mini_cart_start_h button.wfacp-coupon-btn', __( 'Button Label Color', 'woofunnels-aero-checkout' ), '#fff' );


		$this->add_background_color( $coupon_tab_id, 'mini_cart_coupon_btn__bg_hover_color', '.wfacp_mini_cart_start_h button.wfacp-coupon-btn:hover', '#878484', __( 'Button Hover Background', 'woofunnels-aero-checkout' ) );
		$this->add_color( $coupon_tab_id, 'mini_cart_coupon_btn_hover_label_color', '.wfacp_mini_cart_start_h button.wfacp-coupon-btn:hover', __( 'Button Label Hover Color', 'woofunnels-aero-checkout' ), '#fff' );


	}


	public function html( $setting, $defaults, $content ) {//phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedParameter

		$template = wfacp_template();
		if ( is_null( $template ) ) {
			return '';
		}

		if ( isset( $setting['enable_quantity_number'] ) && "off" === $setting['enable_quantity_number'] ) {

			echo "<style>";
			echo ".wfacp_mini_cart_start_h .wfacp-qty-ball{display: none;}";
			echo ".wfacp_mini_cart_start_h strong.product-quantity{display: none;}";
			echo "</style>";
		}

		$this->save_ajax_settings();
		$key     = 'wfacp_mini_cart_widgets_' . $template->get_template_type();
		$widgets = WFACP_Common::get_session( $key );
		if ( ! in_array( $key, $widgets ) ) {//phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			$widgets[] = $this->get_id();
		}
		WFACP_Common::set_session( $key, $widgets );
		$template->get_mini_cart_widget( $this->get_id() );
	}

	protected function preview_shortcode() {
		echo '[Mini Cart]';
	}

}

new WFACP_OXY_Summary;