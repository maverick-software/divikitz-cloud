<?php

class WFACP_Divi_Form extends WFACP_Divi_HTML_BLOCK {
	public $slug = 'wfacp_checkout_form';
	public $form_sub_headings = [];
	protected $get_local_slug = 'wfacp_form';
	protected $id = 'wfacp_divi_checkout_form';
	private $custom_class_tab_id = '';

	public function __construct() {
		$this->name = __( 'Checkout Form', 'woofunnls-aero-checkout' );
		parent::__construct();
	}

	/**
	 * @param $template WFACP_Template_Common;
	 */
	public function setup_data( $template ) {
		$template->get_fieldsets();
		$this->register_sections();
		$this->register_styles();
	}

	protected function register_sections() {
		$this->breadcrumb_bar();
		$this->register_section_fields();
		$this->payment_method();
		$this->mobile_mini_cart();
	}

	private function register_section_fields() {
		$template                  = wfacp_template();
		$steps                     = $template->get_fieldsets();
		$do_not_show_fields        = WFACP_Common::get_html_excluded_field();
		$exclude_fields            = [];
		$this->custom_class_tab_id = $this->add_tab( __( 'Field Classes', 'woofunnels-aero-checkout' ), 3 );
		foreach ( $steps as $step_key => $fieldsets ) {
			foreach ( $fieldsets as $section_key => $section_data ) {
				if ( empty( $section_data['fields'] ) ) {
					continue;
				}
				$count            = count( $section_data['fields'] );
				$html_field_count = 0;
				if ( ! empty( $section_data['html_fields'] ) ) {
					foreach ( $do_not_show_fields as $h_key ) {
						if ( isset( $section_data['html_fields'][ $h_key ] ) ) {
							$html_field_count ++;
							$this->html_fields[ $h_key ] = true;
						}
					}
				}
				if ( $html_field_count == $count ) {
					continue;
				}
				if ( is_array( $section_data['fields'] ) && count( $section_data['fields'] ) > 0 ) {
					foreach ( $section_data['fields'] as $fkey => $fval ) {
						if ( isset( $fval['id'] ) && in_array( $fval['id'], $do_not_show_fields ) ) {
							$exclude_fields[]                 = $fval['id'];
							$this->html_fields[ $fval['id'] ] = true;
							continue;
						}
					}
				}
				if ( count( $exclude_fields ) == count( $section_data['fields'] ) ) {
					continue;
				}
				$title = $section_data['name'];
				if ( empty( $title ) ) {
					$title = $this->get_title();
				}
				if ( isset( $section_data['sub_heading'] ) && ! empty( $section_data['sub_heading'] ) ) {
					$this->form_sub_headings[] = $section_data['sub_heading'];
				}
				$tab_id = $this->add_tab( $title, 5 );
				$this->register_fields( $section_data['fields'], $tab_id );
			}
		}
	}

	private function register_fields( $temp_fields, $tab_id ) {
		$template               = wfacp_template();
		$template_slug          = $template->get_template_slug();
		$template_cls           = $template->get_template_fields_class();
		$default_cls            = $template->default_css_class();
		$do_not_show_fields     = WFACP_Common::get_html_excluded_field();
		$this->section_fields[] = $temp_fields;
		foreach ( $temp_fields as $loop_key => $field ) {
			if ( in_array( $loop_key, [ 'wfacp_start_divider_billing', 'wfacp_start_divider_shipping' ], true ) ) {
				$address_key_group = ( $loop_key == 'wfacp_start_divider_billing' ) ? __( 'Billing Address', 'woocommerce' ) : __( 'Shipping Address', 'woocommerce' );
				$this->add_heading( $tab_id, $address_key_group, 'none' );
			}
			if ( ! isset( $field['id'] ) || ! isset( $field['label'] ) ) {
				continue;
			}
			$field_key = $field['id'];
			if ( isset( $template_cls[ $field_key ] ) ) {
				$field_default_cls = $template_cls[ $field_key ]['class'];
			} else {
				$field_default_cls = $default_cls['class'];
			}
			if ( in_array( $field_key, $do_not_show_fields ) ) {
				$this->html_fields[ $field_key ] = true;
				continue;
			}
			$skipKey = [ 'billing_same_as_shipping', 'shipping_same_as_billing' ];
			if ( in_array( $field_key, $skipKey ) ) {
				continue;
			}
			if ( isset( $field['type'] ) && 'wfacp_html' === $field['type'] ) {
				$options           = [ 'wfacp-col-full' => __( 'Full', 'woofunnels-aero-checkout' ), ];
				$field_default_cls = 'wfacp-col-full';
			} else {
				$options = $this->get_class_options();
			}
			$this->add_select( $tab_id, 'wfacp_' . $template_slug . '_' . $field_key . '_field', $field['label'], $options, $field_default_cls );
			if ( ! empty( $this->custom_class_tab_id ) ) {
				$this->add_text( $this->custom_class_tab_id, 'wfacp_' . $template_slug . '_' . $field_key . '_field_class', __( $field['label'], 'woofunnel-aero-checkout' ), '', [], '', __( 'Custom Class', 'woofunnels-aero-checkout' ) );
			}
		}
	}

	private function breadcrumb_bar() {
		$template     = wfacp_template();
		$num_of_steps = $template->get_step_count();
		if ( $num_of_steps >= 1 ) {
			$stepsCounter          = 1;
			$tab_name              = __( 'Steps', 'woofunnels-aero-checkout' );
			$enable_condition_name = __( 'Enable Steps', 'woofunnel-aero-checkout' );
			$options               = [
				'tab'       => __( 'Tabs', 'woofunnels-aero-checkout' ),
				'bredcrumb' => __( 'Breadcrumb', 'woofunnels-aero-checkout' ),
			];
			$default               = "off";
			if ( $num_of_steps == 1 ) {
				$tab_name              = __( 'Header', 'woofunnels-aero-checkout' );
				$enable_condition_name = __( 'Enable', 'woofunnel-aero-checkout' );
				unset( $options['bredcrumb'] );
			}

		}
		$tab_id = $this->add_tab( $tab_name, 5 );
		$this->add_switcher( $tab_id, 'enable_progress_bar', $enable_condition_name, $default );
		$this->add_responsive_control( 'enable_progress_bar' );
		$enableOptions = [
			'enable_progress_bar' => 'on',
		];
		$this->add_select( $tab_id, 'select_type', __( "Select Type", 'woofunnels-aero-checkout' ), $options, 'tab', $enableOptions );
		$bredcrumb_controls = [
			'select_type'         => 'bredcrumb',
			'enable_progress_bar' => "on"
		];
		$progress_controls  = [
			'select_type'         => [
				'progress_bar'
			],
			'enable_progress_bar' => "on"
		];
		$labels             = [
			[
				'heading'     => __( 'SHIPPING', 'woofunnels-aero-checkout' ),
				'sub-heading' => '',
			],
			[
				'heading'     => __( 'PRODUCTS', 'woofunnels-aero-checkout' ),
				'sub-heading' => '',
			],
			[
				'heading'     => __( 'PAYMENT', 'woofunnels-aero-checkout' ),
				'sub-heading' => '',
			],
		];
		for ( $bi = 0; $bi < $num_of_steps; $bi ++ ) {
			$heading    = $labels[ $bi ]['heading'];
			$subheading = $labels[ $bi ]['sub-heading'];
			$label      = __( 'Step', 'woofunnels-aero-checkout' );
			if ( $num_of_steps > 1 ) {
				$this->add_heading( $tab_id, $label . " " . $stepsCounter, 'none', [ 'enable_progress_bar' => "on" ] );
			}
			$default_val = "Step " . $stepsCounter;
			$this->add_text( $tab_id, 'step_' . $bi . '_bredcrumb', __( "Title", 'woofunnels-aero-checkout' ), $default_val, $bredcrumb_controls );
			$this->add_text( $tab_id, 'step_' . $bi . '_progress_bar', __( "Heading", 'woofunnels-aero-checkout' ), "Step $stepsCounter", $progress_controls );
			$this->add_text( $tab_id, 'step_' . $bi . '_heading', __( "Heading", 'woofunnels-aero-checkout' ), $heading, [
				'select_type'         => 'tab',
				'enable_progress_bar' => "on"
			] );
			$this->add_text( $tab_id, 'step_' . $bi . '_subheading', __( "Sub Heading", 'woofunnels-aero-checkout' ), $subheading, [
				'select_type'         => 'tab',
				'enable_progress_bar' => "on"
			] );
			$stepsCounter ++;
		}
		if ( $num_of_steps > 1 ) {
			$condtion_control   = [
				'select_type'         => [
					'bredcrumb',
					'progress_bar',
				],
				'enable_progress_bar' => "on"
			];
			$cartTitle          = __( 'Title', 'woofunnels-aero-checkout' );
			$progresscartTitle  = __( 'Cart title', 'woofunnels-aero-checkout' );
			$settingDescription = __( 'Note: Cart settings will work for Global Checkout when user navigates from Product > Cart > Checkout', 'woofunnels-aero-checkout' );
			$cartText           = __( 'Cart', 'woocommerce' );
			$options            = [
				'yes' => __( 'Yes', 'woofunnels-aero-checkout' ),
				'no'  => __( 'No', 'woofunnels-aero-checkout' ),
			];
			$this->add_heading( $tab_id, 'Cart', 'none', $bredcrumb_controls );
			$this->add_select( $tab_id, 'step_cart_link_enable', __( "Add to Breadcrumb", 'woofunnels-aero-checkout' ), $options, 'yes', $condtion_control );
			$this->add_text( $tab_id, 'step_cart_progress_bar_link', $progresscartTitle, $cartText, $progress_controls, $settingDescription );
			$this->add_text( $tab_id, 'step_cart_bredcrumb_link', $cartTitle, $cartText, $bredcrumb_controls, $settingDescription );
		}
	}


	private function payment_method() {
		$tab_id = $this->add_tab( __( 'Payment Gateways', 'woofunnel-aero-checkout' ), 5 );
		$this->add_heading( $tab_id, __( 'Section', 'woofunnel-aero-checkout' ) );
		$this->add_text( $tab_id, 'wfacp_payment_method_heading_text', __( 'Heading', 'woofunnel-aero-checkout' ), esc_attr__( 'Payment Information', 'woofunnels-aero-checkout' ), [], '' );
		$this->add_textArea( $tab_id, 'wfacp_payment_method_subheading', __( __( 'Sub Heading', 'woofunnels-aero-checkout' ), 'woofunnel-aero-checkout' ), '' );
		$this->form_buttons( $tab_id );
	}

	private function form_buttons( $tab_id ) {
		$template    = wfacp_template();
		$count       = $template->get_step_count();
		$backLinkArr = [];
		$this->add_heading( $tab_id, __( 'Button Text', 'woofunnel-aero-checkout' ), 'none' );
		for ( $i = 1; $i <= $count; $i ++ ) {
			$button_default_text = __( 'NEXT STEP →', 'woofunnels-aero-checkout' );
			$button_key          = 'wfacp_payment_button_' . $i . '_text';
			if ( $i == $count ) {
				$button_key          = 'wfacp_payment_place_order_text';
				$button_default_text = __( 'PLACE ORDER NOW', 'woofunnels-aero-checkout' );
			}
			$this->add_text( $tab_id, $button_key, __( "Step {$i}", 'woofunnel-aero-checkout' ), esc_js( $button_default_text ), []);
			if ( $i > 1 ) {
				$backCount                                            = $i - 1;
				$backLinkArr[ 'payment_button_back_' . $i . '_text' ] = [
					'label' => __( "Return to Step {$backCount}", 'woofunnel-aero-checkout' ),
				];
			}
		}
		if ( is_array( $backLinkArr ) && count( $backLinkArr ) > 0 ) {
			$this->add_heading( $tab_id, __( 'Return Link Text', 'woofunnel-aero-checkout' ), 'none' );
			$cart_name = __( '« Return to Cart', 'woofunnels-aero-checkout' );
			$this->add_text( $tab_id, "return_to_cart_text", 'Return to Cart', $cart_name, [ 'step_cart_link_enable' => 'yes' ] );
			foreach ( $backLinkArr as $i => $val ) {
				$this->add_text( $tab_id, $i, $val['label'], '', [] );
			}
		}
		$this->add_text( $tab_id, 'text_below_placeorder_btn', __( "Text Below Place Order Button", 'woofunnel-aero-checkout' ) );
	}

	private function mobile_mini_cart() {
		$tab_id = $this->add_tab( __( 'Collapsible Order Summary', 'woofunnels-aero-checkout' ), 5 );
		$this->add_switcher( $tab_id, 'enable_callapse_order_summary', __( 'Enable', 'woofunnels-aero-checkout' ), 'off' );
		$this->add_responsive_control( 'enable_callapse_order_summary' );
		$this->add_text( $tab_id, 'cart_collapse_title', __( 'Collapsed View Text ', 'woofunnels-aero-checkout' ), __( 'Show Order Summary', 'woofunnels-aero-checkout' ) );
		$this->add_text( $tab_id, 'cart_expanded_title', __( 'Expanded View Text', 'woofunnels-aero-checkout' ), __( 'Hide Order Summary', 'woofunnels-aero-checkout' ) );

		$collapse_enable_coupon = [
			'collapse_enable_coupon' => 'on',
		];
		$this->add_switcher( $tab_id, 'collapse_enable_coupon', __( 'Enable Coupon', 'woofunnels-aero-checkout' ), 'on' );
		$this->add_switcher( $tab_id, 'collapse_enable_coupon_collapsible', __( 'Collapsible Coupon Field', 'woofunnels-aero-checkout' ), 'off', $collapse_enable_coupon );
		$this->add_text( $tab_id,'collapse_coupon_button_text', __( 'Coupon Button Text', 'woofunnels-aero-checkout' ), __( 'Apply Coupon', 'woocommerce' ), $collapse_enable_coupon );
		$this->add_switcher( $tab_id, 'collapse_order_quantity_switcher', __( 'Quantity Switcher', 'woofunnels-aero-checkout' ), 'on', $collapse_enable_coupon );
		$this->add_switcher( $tab_id, 'collapse_order_delete_item', __( 'Allow Deletion', 'woofunnels-aero-checkout' ), 'on', $collapse_enable_coupon );
	}

	protected function register_styles() {
		$this->get_progress_settings();
		$this->get_heading_settings();
		$this->fields_typo_settings();
		$this->section_typo_settings();
		foreach ( $this->html_fields as $key => $v ) {
			$this->generate_html_block( $key );
		}
		$this->payment_buttons_styling();
		$this->payment_method_styling();
		$this->global_typography();
		$this->collapsible_order_summary();
	}

	public function get_progress_settings() {
		$template        = wfacp_template();
		$number_of_steps = $template->get_step_count();
		if ( $number_of_steps < 1 ) {
			return;
		}
		$class     = '';
		$step_text = __( 'Steps', 'woofunnel-aero-checkout' );
		if ( $number_of_steps <= 1 ) {
			$class     = 'wfacp_elementor_hide_field';
			$step_text = __( 'Header', 'woofunnel-aero-checkout' );
		}
		$controlsCondition      = [
			'select_type' => [
				'bredcrumb',
				'progress_bar',
				'tab',
			],
		];
		$tab_condition          = [ 'select_type' => 'tab', 'enable_progress_bar' => 'on' ];
		$breadcrumb_condition   = [ 'select_type' => 'bredcrumb', 'enable_progress_bar' => 'on' ];
		$progress_bar_condition = [ 'select_type' => 'progress_bar', 'enable_progress_bar' => 'on' ];
		$tab_id                 = $this->add_tab( __( $step_text, 'woofunnel-aero-checkout' ), 2 );
		$this->add_heading( $tab_id, 'Heading Typography', '', $tab_condition );
		$font_side_default = [ 'default' => '17px', 'unit' => 'px' ];
		$this->add_typography( $tab_id, 'tab_heading_typography', '%%order_class%% #wfacp-e-form .wfacp_form_steps .wfacp-order2StepTitle.wfacp-order2StepTitleS1', 'Heading', [], $tab_condition, $font_side_default );
		$this->add_heading( $tab_id, 'Subheading Typography', '', $tab_condition );
		$this->add_typography( $tab_id, 'tab_subheading_typography', '%%order_class%% #wfacp-e-form .wfacp_form_steps .wfacp-order2StepSubTitle.wfacp-order2StepSubTitleS1', __( 'Sub Heading', 'woofunnels-aero-checkout' ), [], $tab_condition );
		$alignmentOption = [ '%%order_class%% #wfacp-e-form .wfacp-payment-tab-list .wfacp-order2StepHeaderText' ];
		$this->add_text_alignments( $tab_id, 'tab_text_alignment', $alignmentOption, '', 'center', $tab_condition );
		$this->add_typography( $tab_id, 'progress_bar_heading_typography', '%%order_class%% #wfacp-e-form .wfacp_custom_breadcrumb .wfacp_steps_sec ul li a', 'Heading', [], $progress_bar_condition );
		/* Breadcrumb */
		$this->add_heading( $tab_id, 'Heading Typography', '', $breadcrumb_condition );
		$this->add_typography( $tab_id, 'breadcrumb_heading_typography', '%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_steps_sec ul li a', 'Heading', [], $breadcrumb_condition );
		/* color setting */
		$controls_tabs_id      = $this->add_controls_tabs( $tab_id, "Colors", $breadcrumb_condition );
		$breadcrumb_text_color = $this->add_color( $tab_id, 'breadcrumb_text_color', [ '%%order_class%% #wfacp-e-form .wfacp-form .wfacp_main_form.woocommerce .wfacp_steps_sec ul li a' ], 'Color', '#000000 ' );
		$this->add_controls_tab( $controls_tabs_id, 'Normal', [ $breadcrumb_text_color ] );
		$breadcrumb_text_hover_color = $this->add_color( $tab_id, 'breadcrumb_text_hover_color', [ '%%order_class%% #wfacp-e-form .wfacp-form .wfacp_main_form.woocommerce .wfacp_steps_sec ul li a:hover' ], 'Color', '#000000' );
		$this->add_controls_tab( $controls_tabs_id, 'Hover', [ $breadcrumb_text_hover_color ] );
		/* Back link color setting End*/
		/*Progress Bar*/
		$activeColor = [
			'%%order_class%% #wfacp-e-form .wfacp_custom_breadcrumb .wfacp_steps_sec ul li.wfacp_bred_active:before',
			'%%order_class%% #wfacp-e-form .wfacp_custom_breadcrumb .wfacp_steps_sec ul li.wfacp_active_prev:before',
			'%%order_class%% #wfacp-e-form .wfacp_custom_breadcrumb .wfacp_steps_sec ul li.df_cart_link.wfacp_bred_visited:before'
		];
		$this->add_background_color( $tab_id, 'progress_bar_line_color', [ '%%order_class%% #wfacp-e-form .wfacp_custom_breadcrumb .wfacp_steps_sec ul:before' ], '', 'Line', $progress_bar_condition );
		$this->add_border_color( $tab_id, 'progress_bar_circle_color', [ '%%order_class%% #wfacp-e-form .wfacp_custom_breadcrumb .wfacp_steps_sec ul li:before' ], '', __( 'Circle Border', 'woofunnel-aero-checkout' ), false, $progress_bar_condition );
		$this->add_background_color( $tab_id, 'progress_bar_active_color', $activeColor, '', 'Active Step', $progress_bar_condition );
		$this->add_color( $tab_id, 'progressbar_text_color', [ '%%order_class%%  #wfacp-e-form .wfacp_custom_breadcrumb .wfacp_steps_sec ul li a' ], '', 'Text ', $progress_bar_condition );
		$this->add_color( $tab_id, 'progressbar_text_hover_color', [ '%%order_class%%  #wfacp-e-form .wfacp_custom_breadcrumb .wfacp_steps_sec ul li a:hover' ], '', 'Text Hover', $progress_bar_condition );
		/** Tab settings start completed */
		$wfacp_progress_bar_tabs = $this->add_controls_tabs( $tab_id, "Colors", $tab_condition );
		$field_keys              = [];
		$field_keys[]            = $this->add_background_color( $tab_id, 'active_step_bg_color', [ '%%order_class%% #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list.wfacp-active' ], '', 'Background Color', $tab_condition );
		$field_keys[]            = $this->add_color( $tab_id, 'active_step_text_color', [ '%%order_class%% #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list.wfacp-active .wfacp_tcolor' ], 'Text Color', '', $tab_condition );
		$field_keys[]            = $this->add_border_color( $tab_id, 'active_tab_border_bottom_color', [ '%%order_class%% #wfacp-e-form .wfacp-payment-tab-list.wfacp-active' ], '#000000', __( 'Tab Border Color', 'woofunnel-aero-checkout' ), false, $tab_condition );
		if ( $number_of_steps > 1 ) {
			$field_keys[] = $this->add_background_color( $tab_id, 'active_step_count_bg_color', [ '%%order_class%% #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list.wfacp-active .wfacp-order2StepNumber' ], '#000000', 'Count Background Color', $tab_condition );
			$field_keys[] = $this->add_border_color( $tab_id, 'active_step_count_border_color', [ '%%order_class%% #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list.wfacp-active .wfacp-order2StepNumber' ], '#000000', __( 'Count Border Color', 'woofunnel-aero-checkout' ), false, $tab_condition );
			$field_keys[] = $this->add_color( $tab_id, 'active_step_count_text_color', [ '%%order_class%% #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list.wfacp-active .wfacp-order2StepNumber' ], 'Count Text Color', '', $tab_condition );
		}
		//Put All active step Field to control Tab
		$this->add_controls_tab( $wfacp_progress_bar_tabs, __( 'Active Step', 'woofunnels-aero-checkout' ), $field_keys );
		$inactiveBgcolor = [
			'%%order_class%% #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list',
		];
		$field_keys      = [];
		$field_keys[]    = $this->add_background_color( $tab_id, 'inactive_step_bg_color', $inactiveBgcolor, '', __( 'Background Color', 'woofunnels-aero-checkout' ), $tab_condition );
		$field_keys[]    = $this->add_color( $tab_id, 'inactive_step_text_color', [ '%%order_class%% #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list .wfacp_tcolor' ], __( 'Text Color', 'woofunnels-aero-checkout' ), '', $tab_condition );
		$field_keys[]    = $this->add_border_color( $tab_id, 'inactive_tab_border_bottom_color', [ '%%order_class%% #wfacp-e-form .wfacp-payment-tab-list' ], '#000000', __( 'Tab Border Color', 'woofunnel-aero-checkout' ), false, $tab_condition );
		$field_keys[]    = $this->add_background_color( $tab_id, 'inactive_step_count_bg_color', [ '%%order_class%% #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list .wfacp-order2StepNumber' ], '#000000', 'Count Background Color', $tab_condition );
		$field_keys[]    = $this->add_border_color( $tab_id, 'inactive_step_count_border_color', [ '%%order_class%% #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list .wfacp-order2StepNumber' ], '#000000', __( 'Count Border Color', 'woofunnel-aero-checkout' ), false, $tab_condition );
		$field_keys[]    = $this->add_color( $tab_id, 'inactive_step_count_text_color', [ '%%order_class%% #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list .wfacp-order2StepNumber' ], 'Count Text Color', '', $tab_condition );
		//Put In Active step Field to control Tab
		$this->add_controls_tab( $wfacp_progress_bar_tabs, __( 'Inactive Step', 'woofunnels-aero-checkout' ), $field_keys );
		/** Tab settings completed */
		$this->add_heading( $tab_id, __( 'Border Radius', 'woofunnels-aero-checkout' ), '', $tab_condition );
		$this->add_border_radius_new( $tab_id, 'border_radius_steps', '%%order_class%% #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list', $tab_condition );
		$this->add_heading( $tab_id, 'Margin', '', $tab_condition );
		$default = '0px || 15px || 0px || 0px';
		$this->add_margin( $tab_id, 'wfacp_tab_margin', '%%order_class%% #wfacp-e-form .tab', $default, '', $tab_condition );
	}

	private function get_heading_settings() {
		/**
		 * @var $template WFACP_Elementor_Template
		 */
		$sectionTitleOption = [
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_section_heading.wfacp_section_title'
		];
		$extra_options      = [
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce #payment button#place_order'                       => 'font-weight: 700;font-size: 25px;',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-checkout .wfacp-next-btn-wrap button' => 'font-weight: 700;font-size: 25px;',
		];
		$alignment          = 'Left';
		if ( is_rtl() ) {
			$alignment = 'Right';
		}
		$tab_id            = $this->add_tab( __( 'Heading', 'woofunnel-aero-checkout' ), 2 );
		$font_side_default = [ 'default' => '18px', 'unit' => 'px' ];
		$this->add_heading( $tab_id, __( 'Heading', 'woofunnel-aero-checkout' ) );
		$this->add_typography( $tab_id, 'section_heading_typo', implode( ',', $sectionTitleOption ), '', '', [], $font_side_default );
		$this->add_color( $tab_id, 'form_heading_color', $sectionTitleOption, '', '#333333' );
		$this->add_text_alignments( $tab_id, 'form_heading_align', $sectionTitleOption, '', $alignment, [] );
		//Sub heading start here
		$this->add_heading( $tab_id, __( __( 'Sub Heading', 'woofunnels-aero-checkout' ), 'woofunnel-aero-checkout' ), 2 );
		$subheadingOption  = [ '%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-comm-title h4' ];
		$font_side_default = [ 'default' => '14px', 'unit' => 'px' ];
		$this->add_typography( $tab_id, 'section_sub_heading_typo', implode( ',', $subheadingOption ), '', '', [], $font_side_default );
		$this->add_color( $tab_id, 'form_sub_heading_color', $subheadingOption, '', '#737373' );
		$this->add_text_alignments( $tab_id, 'form_sub_heading_align', $subheadingOption );
		//Sub heading end here
		$advanceOption = [ '%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-section .wfacp-comm-title' ];
		$this->add_heading( $tab_id, __( 'Advanced', 'woofunnel-aero-checkout' ) );
		$this->add_background_color( $tab_id, 'form_heading_bg_color', $advanceOption, 'transparent' );
		$this->add_padding( $tab_id, 'form_heading_padding', implode( ',', $advanceOption ) );
		$this->add_margin( $tab_id, 'form_heading_margin', implode( ',', $advanceOption ), '', '', [] );
		$default_args = [
			'border_type'          => 'none',
			'border_width_top'     => '1',
			'border_width_bottom'  => '1',
			'border_width_left'    => '1',
			'border_width_right'   => '1',
			'border_radius_top'    => '0',
			'border_radius_bottom' => '0',
			'border_radius_left'   => '0',
			'border_radius_right'  => '0',
			'border_color'         => '#dddddd',
		];
		$this->add_border( $tab_id, 'form_heading_border', implode( ',', $advanceOption ), [], $default_args );
	}

	private function fields_typo_settings() {
		$tabs_id = $this->add_tab( __( 'Fields', 'woofunnel-aero-checkout' ), 2 );
		$this->add_heading( $tabs_id, __( 'Label', 'woofunnel-aero-checkout' ) );


        /* Label Position */
		$options = [
			'wfacp-top'    => __( 'Top of Field', 'woofunnel-aero-checkout' ),
			'wfacp-inside' => __( 'Inside Field', 'woofunnel-aero-checkout' ),

		];
		$this->add_select( $tabs_id, 'wfacp_label_position',__( 'Label Position', 'woofunnel-aero-checkout' ), $options, 'wfacp-inside' );



		$form_fields_label_typo = [
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce p.wfacp-form-control-wrapper:not(.wfacp-anim-wrap) label.wfacp-form-control-label',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce p.wfacp-form-control-wrapper.wfacp_checkbox_field label',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .create-account label',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .create-account label span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce p.wfacp-form-control-wrapper.wfacp_checkbox_field label span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce p.wfacp-form-control-wrapper.wfacp_custom_field_radio_wrap > label ',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce p.wfacp-form-control-wrapper:not(.wfacp-anim-wrap) label.wfacp-form-control-label abbr',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li p',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li p',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li label',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li .wfacp_shipping_price span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li .wfacp_shipping_price span bdi',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li .wfacp_shipping_price',
			'%%order_class%% #wfacp-e-form .wfacp-form.wfacp-top .form-row > label.wfacp-form-control-label',
		];
		$font_side_default      = [ 'default' => '13px', 'unit' => 'px' ];
		$this->add_typography( $tabs_id, 'wfacp_form_fields_label_typo', implode( ',', $form_fields_label_typo ), '', '', [], $font_side_default );
		$form_fields_label_color_opt = [
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-form-control-label',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-form-control-label abbr',
		];
		$this->add_color( $tabs_id, 'wfacp_form_fields_label_color', $form_fields_label_color_opt, '', '#777' );
		$fields_options = [
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce input[type="text"]',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce input[type="email"]',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce input[type="tel"]',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce input[type="password"]',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce input[type="number"]',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce select',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce textarea',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce number',
			'%%order_class%% #wfacp-e-form .woocommerce-input-wrapper .wfacp-form-control',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .select2-container .select2-selection--single .select2-selection__rendered',
			'body:not(.wfacp_pre_built) .select2-results__option',
			'body:not(.wfacp_pre_built) .select2-container--default .select2-search--dropdown .select2-search__field',
		];
		$optionString   = implode( ',', $fields_options );
		/* Input field typography */
		$this->add_heading( $tabs_id, __( 'Input', 'woofunnel-aero-checkout' ) );
		$font_side_default = [ 'default' => '14px', 'unit' => 'px' ];
		$this->add_typography( $tabs_id, 'wfacp_form_fields_input_typo', $optionString, '', '', [], $font_side_default );
		$inputColorOption = [
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-input-wrapper .wfacp-form-control',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .select2-container .select2-selection--single .select2-selection__rendered',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce select',
		];
		$this->add_color( $tabs_id, 'wfacp_form_fields_input_color', $inputColorOption, '', '#404040' );
		$fields_options = [
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce input[type="text"]',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce input[type="email"]',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce input[type="tel"]',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce input[type="password"]',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce select',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce textarea',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .woocommerce-input-wrapper input[type="number"].wfacp-form-control',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .woocommerce-input-wrapper input[type="text"].wfacp-form-control',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .woocommerce-input-wrapper input[type="emal"].wfacp-form-control',
			'%%order_class%% #wfacp-e-form .wfacp_allowed_countries strong',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .select2-container .select2-selection--single .select2-selection__rendered',
		];

		$inputbgColorOption = [
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-input-wrapper .wfacp-form-control:not(.input-checkbox)',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .select2-container .select2-selection--single .select2-selection__rendered',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce select',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-login-wrapper input[type=email]',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-login-wrapper input[type=number]',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-login-wrapper input[type=password]',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-login-wrapper input[type=tel]',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-login-wrapper select',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-login-wrapper input[type=text]',
			'%%order_class%% #wfacp-e-form .wfacp-form.wfacp-inside .form-row > label.wfacp-form-control-label:not(.checkbox)',
		];

		$this->add_background_color( $tabs_id,'wfacp_form_fields_input_bg_color', $inputbgColorOption, '' );

		$default_args   = [
			'border_type'          => 'solid',
			'border_width_top'     => '1',
			'border_width_bottom'  => '1',
			'border_width_left'    => '1',
			'border_width_right'   => '1',
			'border_radius_top'    => '4',
			'border_radius_bottom' => '4',
			'border_radius_left'   => '4',
			'border_radius_right'  => '4',
			'border_color'         => '#bfbfbf',
		];
		$this->add_border( $tabs_id, 'wfacp_form_fields_border', implode( ',', $fields_options ), [], $default_args );
		$validation_error = [
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce p.woocommerce-invalid-required-field .wfacp-form-control',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce p.woocommerce-invalid-email .wfacp-form-control',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_coupon_failed .wfacp_coupon_code',
		];
		$this->add_border_color( $tabs_id, 'wfacp_form_fields_focus_color', [ '%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .form-row:not(.woocommerce-invalid-email) .wfacp-form-control:focus' ], '#61bdf7', __( 'Focus Color', 'woofunnel-aero-checkout' ), true );
		$this->add_border_color( $tabs_id, 'wfacp_form_fields_validation_color', $validation_error, '#d50000', __( 'Error Validation Color', 'woofunnel-aero-checkout' ), true );
	}

	private function section_typo_settings() {
		$tab_id                = $this->add_tab( __( 'Section', 'woofunnel-aero-checkout' ), 2 );
		$form_section_bg_color = [
			'%%order_class%% #wfacp-e-form .wfacp-section',
		];
		$this->add_background_color( $tab_id, 'form_section_bg_color', $form_section_bg_color, '', __( 'Background Color', 'woofunnels-aero-checkout' ) );
		$this->add_padding( $tab_id, 'form_section_padding', '%%order_class%% #wfacp-e-form .wfacp-section', '', 'Padding' );

		$default = '0px || 15px || 0px || 0px';
		$this->add_margin( $tab_id, 'form_section_margin', '%%order_class%% #wfacp-e-form .wfacp-section', $default, 'Margin' );
		$default_args = [
			'border_type'          => 'none',
			'border_width_top'     => '1',
			'border_width_bottom'  => '1',
			'border_width_left'    => '1',
			'border_width_right'   => '1',
			'border_radius_top'    => '0',
			'border_radius_bottom' => '0',
			'border_radius_left'   => '0',
			'border_radius_right'  => '0',
			'border_color'         => '#dddddd',
		];
		$this->add_border( $tab_id, 'form_section_border', implode( ',', $form_section_bg_color ), [], $default_args );
		//$this->add_divider( "none" );
		//$this->add_border_shadow( 'form_section_box_shadow', '%%order_class%% #wfacp-e-form .wfacp-section' );
		//$this->add_divider( "none" );
		$default = [ 'top' => 0, 'right' => 0, 'bottom' => 10, 'left' => 0, 'unit' => 'px' ];
		//	$this->add_margin( $tab_id, 'form_section_margin', '%%order_class%% #wfacp-e-form .wfacp-section', $default, $default, [], $default );
		//	$this->end_tab();
	}

	private function payment_buttons_styling() {
		$tab_id    = $this->add_tab( __( 'Buttons', 'woofunnel-aero-checkout' ), 2 );
		$selector  = [
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-next-btn-wrap button',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce #payment button#place_order',
		];
		$selector1 = [
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-next-btn-wrap button',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce #payment button#place_order',
			'#wfacp_qr_model_wrap .wfacp_qr_wrap .wfacp_qv-summary .button'
		];
		$this->add_switcher( $tab_id, 'wfacp_make_button_sticky_on_mobile', __( 'Sticky on Mobile', 'woofunnels-aero-checkout' ), 'off', [] );
		$default = [ 'default' => '100%', 'unit' => '%' ];
		$this->add_width( $tab_id, 'wfacp_button_width', implode( ',', $selector ), 'Button Width (in %)', $default );
		$alignment = [
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-checkout .wfacp-order-place-btn-wrap',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-checkout .wfacp-next-btn-wrap',
		];
		$this->add_text_alignments( $tab_id, 'wfacp_form_button_alignment', $alignment );
		$button_selector   = [
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce #payment button#place_order',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-checkout button.button.button-primary.wfacp_next_page_button'
		];
		$font_side_default = [ 'default' => '25px', 'unit' => 'px' ];
		$this->add_typography( $tab_id, 'wfacp_form_payment_button_typo', implode( ',', $button_selector ), '', '', [], $font_side_default );
		$button_bg_hover_color = [
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-next-btn-wrap button:hover',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce #payment button#place_order:hover',
			'#wfacp_qr_model_wrap .wfacp_qr_wrap .wfacp_qv-summary .button:hover'
		];
		/* Button Background hover tab */
		$control_tab_id = $this->add_controls_tabs( $tab_id, "Color" );
		$field_keys     = [];
		$field_keys[]   = $this->add_background_color( $tab_id, 'wfacp_button_bg_color', $selector1, "", 'Background' );
		$field_keys[]   = $this->add_color( $tab_id, 'wfacp_button_label_color', $selector1, '', 'Label' );
		$this->add_controls_tab( $control_tab_id, __( 'Normal', 'woofunnels-aero-checkout' ), $field_keys );
		$field_keys   = [];
		$field_keys[] = $this->add_background_color( $tab_id, 'wfacp_button_bg_hover_color', $button_bg_hover_color, "", 'Background' );
		$field_keys[] = $this->add_color( $tab_id, 'wfacp_button_label_hover_color', $button_bg_hover_color, '', 'Label' );
		$this->add_controls_tab( $control_tab_id, __( 'Hover', 'woofunnels-aero-checkout' ), $field_keys );
		$this->add_divider( "none" );
		$default = '15px || 15px || 25px || 25px';
		$this->add_padding( $tab_id, 'wfacp_button_padding', implode( ',', $selector ), $default, 'Padding' );
		$this->add_margin( $tab_id, "wfacp_button_margin", implode( ',', $selector ) );
		$this->add_divider( "none" );
		$this->add_border( $tab_id, "wfacp_button_border", implode( ',', $selector ) );
		$this->add_divider( "none" );
		$stepBackLink      = [
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-back-btn-wrap a',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-checkout .place_order_back_btn a'
		];
		$stepBackLinkHover = [
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-back-btn-wrap a:hover',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-checkout .place_order_back_btn a:hover'
		];
		$this->add_heading( $tab_id, __( 'Return Link', 'woofunnel-aero-checkout' ), 'none' );
		/* Back Link color setting */
		$back_control_tab_id = $this->add_controls_tabs( $tab_id, '' );
		$field_keys          = [];
		$field_keys[]        = $this->add_color( $tab_id, 'step_back_link_color', $stepBackLink );
		$this->add_controls_tab( $back_control_tab_id, 'Normal', $field_keys );
		$field_keys   = [];
		$field_keys[] = $this->add_color( $tab_id, 'step_back_link_hover_color', $stepBackLinkHover );
		$field_keys[] = $this->add_controls_tab( $back_control_tab_id, 'Hover', $field_keys );
		/* Back link color setting End*/
		$this->add_heading( $tab_id, __( 'Additional Text', 'woofunnel-aero-checkout' ) );
		$this->add_color( $tab_id, 'additional_text_color', [ '%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-payment-dec' ], '', '#737373' );
		$this->add_background_color( $tab_id, 'additional_bg_color', [ '%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-payment-dec' ], "", 'Background' );
	}

	private function payment_method_styling() {
		$tab_id            = $this->add_tab( __( 'Payment Methods', 'woofunnel-aero-checkout' ), 2 );
		$btn_method_typo   = [
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment p',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment p span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment label:not(.wfob_title)',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment ul',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment ul li',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment ul li input',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment #add_payment_method #payment div.payment_box',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment #add_payment_method #payment .payment_box p',
		];
		$font_side_default = [ 'default' => '14px', 'unit' => 'px' ];
		$this->add_typography( $tab_id, 'wfacp_form_payment_method_typo', implode( ',', $btn_method_typo ), '', '', [], $font_side_default );
		$this->add_color( $tab_id, 'wfacp_form_payment_method_color', implode( ',', $btn_method_typo ), '', '#737373' );
	}

	private function global_typography() {
		$tab_id = $this->add_tab( __( 'Checkout Form', 'woofunnel-aero-checkout' ), 2 );
		$this->add_padding( $tab_id, 'wfacp_form_padding', '%%order_class%% #wfacp-e-form', '', 'Padding' );
		$globalSettingOptions = [
			'body.wfacp_main_wrapper',
			'body #wfacp-e-form *:not(i):not(.wfacp-order2StepNumber)',
			'body .wfacp_qv-main *',
			'%%order_class%% #wfacp-e-form .wfacp_section_heading.wfacp_section_title',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_whats_included h3',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_whats_included .wfacp_product_switcher_description .wfacp_description a',
			'%%order_class%%  #wfacp-e-form .wfacp_main_form .wfacp_whats_included .wfacp_product_switcher_description .wfacp_description',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-section h4',
			'%%order_class%% #wfacp-e-form .wfacp_main_form p.wfacp-form-control-wrapper:not(.wfacp-anim-wrap) label.wfacp-form-control-label',
			'%%order_class%% #wfacp-e-form .wfacp_main_form input[type="text"]',
			'%%order_class%% #wfacp-e-form .wfacp_main_form input[type="email"]',
			'%%order_class%% #wfacp-e-form .wfacp_main_form input[type="tel"]',
			'%%order_class%% #wfacp-e-form .wfacp_main_form input[type="number"]',
			'%%order_class%% #wfacp-e-form .wfacp_main_form select',
			'%%order_class%% #wfacp-e-form .wfacp_main_form textarea',
			'%%order_class%% #wfacp-e-form .wfacp_main_form p:not(.wfacp-anim-wrap)',
			'%%order_class%% #wfacp-e-form .wfacp_main_form label span a',
			'%%order_class%% #wfacp-e-form .wfacp_main_form button',
			'%%order_class%% #wfacp-e-form #payment button#place_order',
			'%%order_class%% #wfacp-e-form .wfacp_main_form span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form label',
			'%%order_class%% #wfacp-e-form .wfacp_main_form ul li',
			'%%order_class%% #wfacp-e-form .wfacp_main_form ul li span',
			'%%order_class%% #wfacp-e-form .woocommerce-form-login-toggle .woocommerce-info ',
			'%%order_class%% #wfacp-e-form .wfacp_main_form ul li span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-payment-dec',
			'%%order_class%% #wfacp-e-form .wfacp_main_form label.checkbox',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-title > div',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_shipping_table ul li label',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_checkbox_field label',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .select2-container .select2-selection--single .select2-selection__rendered',
			'%%order_class%% #et-boc .et-l span.select2-selection.select2-selection--multiple',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_sec *',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_quantity_selector input',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_price_sec span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset .wfacp_product_sec *',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset .wfacp_quantity_selector input',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset .wfacp_product_price_sec span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form #product_switching_field fieldset .wfacp_best_value',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel .wfacp_product_switcher_col_2 .wfacp_you_save_text',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_whats_included .wfacp_product_switcher_description h4',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset .wfacp_product_sec .wfacp_product_select_options .wfacp_qv-button',
			'%%order_class%% #wfacp-e-form .wfacp_main_form #product_switching_field .wfacp_product_switcher_col_2 .wfacp_product_subs_details > span:not(.subscription-details):not(.woocommerce-Price-amount):not(.woocommerce-Price-currencySymbol)',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-coupon-section .wfacp-coupon-page .woocommerce-info > span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_woocommerce_form_coupon .wfacp-coupon-section .woocommerce-info .wfacp_showcoupon',
			'%%order_class%% #wfacp-e-form .wfacp_main_form label.woocommerce-form__label span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr th',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot .shipping_total_fee td',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr td',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr td span.woocommerce-Price-amount.amount',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr td p',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset .wfacp_best_value',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table tbody .wfacp_order_summary_item_name',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) td small',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) th small',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total td small',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table tbody .wfacp_order_summary_item_name',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table tbody .product-name .product-quantity',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table tbody td.product-total',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table tbody .wfacp_order_summary_container dl',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table tbody .wfacp_order_summary_container dd',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table tbody .wfacp_order_summary_container dt',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table tbody .wfacp_order_summary_container p',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table tbody tr span.amount',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table tbody .wfacp_order_summary_item_name',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table tbody .cart_item .product-total span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table tbody .cart_item .product-total small',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table tbody .cart_item .product-total span.amount',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table tbody .product-name .product-quantity',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table tbody td.product-total',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table tbody dl',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table tbody dd',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table tbody dt',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table tbody p',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table tbody tr span.amount',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset .wfacp_product_sec .wfacp_product_select_options .wfacp_qv-button',
			'%%order_class%% #wfacp-e-form .wfacp_main_form #product_switching_field .wfacp_product_switcher_col_2 .wfacp_product_subs_details > span:not(.subscription-details):not(.woocommerce-Price-amount):not(.woocommerce-Price-currencySymbol)',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset .wfacp_you_save_text',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_row_wrap .wfacp_you_save_text span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_whats_included .wfacp_product_switcher_description .wfacp_description p',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_coupon_field_msg',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-coupon-page .wfacp_coupon_remove_msg',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-coupon-page .wfacp_coupon_error_msg',
			'body:not(.wfacp_pre_built) .select2-results__option',
			'body:not(.wfacp_pre_built) .select2-container--default .select2-search--dropdown .select2-search__field',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_order_total_field table.wfacp_order_total_wrap tr td',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_order_total_field table.wfacp_order_total_wrap tr td span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_order_total .wfacp_order_total_wrap',
			'%%order_class%% #wfacp-e-form .wfacp_main_form #payment button#place_order',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .woocommerce-checkout button.button.button-primary.wfacp_next_page_button',
			'%%order_class%% #wfacp-e-form .wfacp-order2StepTitle.wfacp-order2StepTitleS1',
			'%%order_class%% #wfacp-e-form .wfacp-order2StepSubTitle.wfacp-order2StepSubTitleS1',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_steps_sec ul li a',
			'%%order_class%% #wfacp-e-form .wfacp_custom_breadcrumb ul li a',
			'%%order_class%%  #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr td span ',
			'%%order_class%% #wfacp-e-form .wfacp_main_form p.wfacp-form-control-wrapper:not(.wfacp-anim-wrap) label.wfacp-form-control-label abbr',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset .wfacp_you_save_text',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_row_wrap .wfacp_you_save_text span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_row_wrap .wfacp_product_subs_details span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form p.wfacp-form-control-wrapper.wfacp_checkbox_field label',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .create-account label',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .create-account label span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form p.wfacp-form-control-wrapper.wfacp_checkbox_field label span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form p.wfacp-form-control-wrapper.wfacp_custom_field_radio_wrap > label ',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) ul',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) ul li',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) ul li label',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) td span.woocommerce-Price-amount.amount',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_sec .wfacp_product_name_inner *',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_sec .wfacp_product_attributes .wfacp_selected_attributes  *',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_quantity_selector input',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_price_sec span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_switcher_col_2 .wfacp_product_subs_details > span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_subs_details span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_subs_details *',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_sec .wfacp_product_select_options .wfacp_qv-button',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_sec .wfacp_product_name_inner *',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_sec .wfacp_product_attributes .wfacp_selected_attributes  *',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_quantity_selector input',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_price_sec span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_switcher_col_2 .wfacp_product_subs_details > span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_subs_details span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_subs_details *',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_sec .wfacp_product_select_options .wfacp_qv-button',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_woocommerce_form_coupon .wfacp-coupon-section .wfacp-coupon-field-btn',
			'%%order_class%% #wfacp-e-form .wfacp_mb_mini_cart_sec_accordion_content form.checkout_coupon button.button.wfacp-coupon-btn',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_shipping_options',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_shipping_options ul li',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_shipping_options ul li p',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_shipping_options ul li .wfacp_shipping_price span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_shipping_options ul li .wfacp_shipping_price',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_payment',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_payment p',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_payment p span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_payment p a',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_payment label',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_payment ul',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_payment ul li',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_payment ul li input',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_payment #add_payment_method #payment div.payment_box',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_payment #add_payment_method #payment .payment_box p',
			'%%order_class%% #wfacp-e-form .wfacp_collapsible_order_summary_wrap *',
		];
		$font_side_default    = [ 'default' => '14px', 'unit' => 'px' ];
		$this->add_typography( $tab_id, 'wfacp_font_family_typography', implode( ',', $globalSettingOptions ), '', '', [], $font_side_default );

		$spacing_tab_id = $this->add_tab( __( 'Spacing', 'woofunnel-aero-checkout' ), 2 );
		$this->add_margin( $spacing_tab_id, 'form_margin', '%%order_class%% #wfacp-e-form .wfacp-form' );
		$this->add_padding( $spacing_tab_id, 'form_padding', '%%order_class%% #wfacp-e-form .wfacp-form' );
		$border_tab_id = $this->add_tab( __( 'Border', 'woofunnel-aero-checkout' ), 2 );
		$default       = [ 'top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0, 'unit' => 'px' ];
		$default_args  = [
			'border_type'          => 'none',
			'border_width_top'     => '1',
			'border_width_bottom'  => '1',
			'border_width_left'    => '1',
			'border_width_right'   => '1',
			'border_radius_top'    => '0',
			'border_radius_bottom' => '0',
			'border_radius_left'   => '0',
			'border_radius_right'  => '0',
			'border_color'         => '#dddddd',
		];
		$this->add_border( $border_tab_id, 'form_border', '%%order_class%% .wfacp_form_divi_container', [], $default, [], $default_args );
		$this->add_background_color( $tab_id, 'form_background_color', '%%order_class%% .wfacp_form_divi_container', '#ffffff', __( 'Form Background Color', 'woofunnels-aero-checkout' ) );


		$fields_contentColor = [
			'%%order_class%% #wfacp-e-form .wfacp_main_form .woocommerce-message',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .woocommerce-error',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .woocommerce-form-login-toggle .woocommerce-info',
			'%%order_class%% #wfacp-e-form .wfacp_main_form form.woocommerce-form.woocommerce-form-login.login p',
			'%%order_class%% #wfacp-e-form .wfacp_main_form label.woocommerce-form__label span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_checkbox_field label',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_checkbox_field label span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_checkbox_field span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_shipping_options label',
			'%%order_class%% #wfacp-e-form .wfacp_main_form ul li span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_shipping_table tr.shipping td p',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp-product-switch-title div',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .woocommerce-privacy-policy-text p',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_shipping_options ul li p',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .shop_table .wfacp-product-switch-title div',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .woocommerce-info .message-container',
			'%%order_class%% #wfacp-e-form .wfacp_main_form #wc_checkout_add_ons .description',
			'%%order_class%% #wfacp-e-form .wfacp_main_form ol li',
			'%%order_class%% #wfacp-e-form .wfacp_main_form ul li',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_shipping_table ul#shipping_method label',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_shipping_table ul#shipping_method span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .woocommerce-checkout-review-order h3',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .aw_addon_wrap label',
			'%%order_class%% #wfacp-e-form .wfacp_main_form p:not(.woocommerce-shipping-contents)',
			'%%order_class%% #wfacp-e-form .wfacp_main_form p:not(.woocommerce-shipping-contents):not(.wfacp_dummy_preview_heading )',
			'%%order_class%% #wfacp-e-form .wfacp_main_form label:not(.wfacp-form-control-label):not(.wfob_title):not(.wfob_span)',
			'%%order_class%% #wfacp-e-form .wfacp_main_form label:not(.wfob_title) span:not(.optional)',
			'%%order_class%% #wfacp-e-form .wfacp_main_form',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_shipping_table ul li label',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_shipping_table ul li label span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form .wfacp_shipping_table ul li span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr.recurring-totals > th',
			'%%order_class%% #wfacp-e-form .wfacp_main_form p.wfacp-form-control-wrapper:not(.wfacp-anim-wrap) label.wfacp-form-control-label abbr',


		];

		$this->add_color( $tab_id, 'default_text_color', $fields_contentColor, __( "Content Color", 'woofunnels-aero-checkout' ), '#737373' );

		$default_link_color_option       = [
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-form-login-toggle .woocommerce-info a',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce a:not(.wfacp_close_icon):not(.ywcmas_shipping_address_button_new):not(.wfob_qv-button):not(.wfob_l3_f_btn)',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce a span',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce label a',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce ul li a',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce table tr td a',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce a.wfacp_remove_coupon',
		];
		$default_link_hover_color_option = [
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-form-login-toggle .woocommerce-info a:hover',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce a:not(.wfacp_close_icon):not(.button-social-login):hover:not(.ywcmas_shipping_address_button_new):hover:not(.wfob_l3_f_btn):hover',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce a span:hover',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce label a:hover',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce ul li a:hover',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce table tr td a:hover',
			'%%order_class%% #wfacp-e-form .wfacp_main_form.woocommerce a.wfacp_remove_coupon:hover',
		];

		$control_id = $this->add_controls_tabs( $tab_id, "Form Link Color" );
		$fields     = [];
		$fields[]   = $this->add_color( $tab_id, 'default_link_color', $default_link_color_option, __( '', 'woofunnels-aero-checkout' ) );
		$this->add_controls_tab( $control_id, "Normal", $fields );
		$fields   = [];
		$fields[] = $this->add_color( $tab_id, 'default_link_hover_color', $default_link_hover_color_option, __( '', 'woofunnels-aero-checkout' ) );
		$this->add_controls_tab( $control_id, 'Hover', $fields );


		//	$this->add_color( $tab_id, 'default_link_color', $default_link_color_option, __( "Form Links Color", 'woofunnels-aero-checkout' ), '#DD7575' );
	}

	private function collapsible_order_summary() {
		$tab_id = $this->add_tab( __( 'Collapsible Order Summary', 'woofunnel-aero-checkout' ), 2 );
		$this->add_switcher( $tab_id, 'order_summary_enable_product_image_collapsed', __( 'Enable Image', 'woofunnels-aero-checkout' ), 'yes' );
		$this->add_background_color( $tab_id, 'collapsible_order_summary_bg_color', '%%order_class%% #wfacp-e-form .wfacp_mb_mini_cart_wrap .wfacp_mb_cart_accordian', '#f7f7f7', __( 'Collapsed Background', 'woofunnels-aero-checkout' ) );
		$this->add_background_color( $tab_id, 'expanded_order_summary_bg_color', '%%order_class%% #wfacp-e-form .wfacp_mb_mini_cart_sec_accordion_content', '#f7f7f7', __( 'Expanded Background', 'woofunnels-aero-checkout' ) );
		$this->add_color( $tab_id, 'expanded_order_summary_link_color', [
			'%%order_class%% #wfacp-e-form .wfacp_show_icon_wrap a span',
			'%%order_class%% #wfacp-e-form .wfacp_show_price_wrap span'
		], __( 'Text Color', 'woofunnels-aero-checkout' ), '#323232' );
		$default = '0px || 10px || 0px || 0px';
		$this->add_margin( $tab_id, 'wfacp_collapsible_margin', '%%order_class%% #wfacp-e-form .wfacp_collapsible_order_summary_wrap', $default );

		$default_args = [
			'border_type'          => 'solid',
			'border_width_top'     => '1',
			'border_width_bottom'  => '0',
			'border_width_left'    => '1',
			'border_width_right'   => '1',
			'border_radius_top'    => '0',
			'border_radius_bottom' => '0',
			'border_radius_left'   => '0',
			'border_radius_right'  => '0',
			'border_color'         => '#dddddd',
		];

		$this->add_border( $tab_id, 'wfacp_collapsible_border', '%%order_class%% #wfacp-e-form .wfacp_mb_mini_cart_wrap .wfacp_mb_cart_accordian', [], $default_args);
		do_action( 'wfacp_elementor_collapsible_fields_settings', $this );
	}

	public function html( $attrs, $content = null, $render_slug = '' ) {
		$template = wfacp_template();
		if ( is_null( $template ) ) {
			return '';
		}
		$template->set_form_data( $this->props );
		ob_start();
		?>
        <div class='wfacp_form_divi_container'>
            <div class='wfacp_divi_forms' id='wfacp-e-form'><?php include $template->wfacp_get_form() ?></div>
        </div>
		<?php
		return ob_get_clean();
	}

	public function get_complete_fields() {
		add_filter( 'et_builder_module_general_fields', '__return_empty_array' );
		$fields = parent::get_complete_fields();
		remove_filter( 'et_builder_module_general_fields', '__return_empty_array' );

		return $fields;
	}
}

new WFACP_Divi_Form;