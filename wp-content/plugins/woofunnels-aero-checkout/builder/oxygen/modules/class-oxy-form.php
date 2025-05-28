<?php

class WFACP_OXY_Form extends WFACP_OXY_HTML_BLOCK {
	public $slug = 'wfacp_checkout_form';
	public $form_sub_headings = [];
	protected $get_local_slug = 'wfacp_form';
	protected $id = 'wfacp_oxy_checkout_form';
	private $custom_class_tab_id = '';

	public function __construct() {
		$this->name = __( 'Checkout Form', 'woofunnels-aero-checkout' );
		parent::__construct();
	}

	public function generate_id_css( $styles, $states, $selector, $class_obj, $defaults ) {//phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedParameter

		$slug = 'oxy-' . $this->slug();
		if ( $class_obj->options['tag'] !== $slug ) {
			return $styles;
		}

		global $oxygen_vsb_components;

		$params             = $states['original'];
		$params['selector'] = $selector;
		if ( ! is_null( $oxygen_vsb_components[ $slug ] ) ) {
			$selector_id = "#" . $params["selector"];
			if ( isset( $params['oxy-wfacp_checkout_form_tab_heading_alignment'] ) && ! empty( $params['oxy-wfacp_checkout_form_tab_heading_alignment'] ) ) {
				$alignment = $params['oxy-wfacp_checkout_form_tab_heading_alignment'];
				$styles    = $styles . $selector_id . " .wfacp-order2StepTitle.wfacp-order2StepTitleS1.wfacp_tcolor{text-align:$alignment}";
			}
		}


		return $styles;
	}


	public function name() {
		return $this->name;
	}

	/**
	 * @param $template WFACP_Template_Common;
	 */
	public function setup_data( $template ) {//phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedParameter
		$this->register_sections();
		$this->register_styles();
	}

	protected function register_sections() {

		$this->breadcrumb_bar();

		$this->get_heading_settings();
		$this->get_sub_heading_settings();
		$this->section_typo_settings();
		$this->register_section_fields();


		$this->input_setting();

	}

	private function breadcrumb_bar() {
		$template              = wfacp_template();
		$num_of_steps          = $template->get_step_count();
		$enable_condition_name = '';
		if ( $num_of_steps >= 1 ) {
			$stepsCounter          = 1;
			$tab_name              = __( 'Steps', 'woofunnels-aero-checkout' );
			$enable_condition_name = __( 'Enable Steps Desktop', 'woofunnels-aero-checkout' );
			$options               = [
				'tab'       => __( 'Tabs', 'woofunnels-aero-checkout' ),
				'bredcrumb' => __( 'Breadcrumb', 'woofunnels-aero-checkout' ),
			];
			$default               = "off";
			if ( absint( $num_of_steps ) === 1 ) {
				$tab_name              = __( 'Header', 'woofunnels-aero-checkout' );
				$enable_condition_name = __( 'Enable Header', 'woofunnels-aero-checkout' );
				unset( $options['bredcrumb'] );
			}

		}
		$tab_id = $this->add_tab( $tab_name );
		$this->add_switcher( $tab_id, 'enable_progress_bar', $enable_condition_name, $default );
		$enableOptions = [
			'enable_progress_bar' => 'on',
		];
		$this->add_select( $tab_id, 'select_type', __( "Select Type", 'woofunnels-aero-checkout' ), $options, 'tab', $enableOptions );
		$bredcrumb_controls = [
			'select_type'         => 'bredcrumb',
			'enable_progress_bar' => "on"
		];
		$tabs_controls      = [
			'select_type'         => 'tab',
			'enable_progress_bar' => "on"
		];
		$labels             = [
			[
				'heading'     => __( 'SHIPPING', 'woofunnels-aero-checkout' ),
				'sub-heading' => __( 'Where to ship it?', 'woofunnels-aero-checkout' ),
			],
			[
				'heading'     => __( 'PRODUCTS', 'woofunnels-aero-checkout' ),
				'sub-heading' => __( 'Select your product', 'woofunnels-aero-checkout' ),
			],
			[
				'heading'     => __( 'PAYMENT', 'woofunnels-aero-checkout' ),
				'sub-heading' => __( 'Confirm your order', 'woofunnels-aero-checkout' ),
			],
		];

		for ( $bi = 0; $bi < $num_of_steps; $bi ++ ) {
			$heading = $labels[ $bi ]['heading'];
			$label   = __( 'Step', 'woofunnels-aero-checkout' );
			if ( $num_of_steps > 1 ) {
				$this->add_heading( $tab_id, $label . " " . $stepsCounter, 'none', [ 'enable_progress_bar' => "on" ] );
			}
			$default_val = "Step " . $stepsCounter;
			$this->add_text( $tab_id, 'step_' . $bi . '_bredcrumb', __( "Title", 'woofunnels-aero-checkout' ), $default_val, $bredcrumb_controls );
			$this->add_text( $tab_id, 'step_' . $bi . '_heading', __( "Heading", 'woofunnels-aero-checkout' ), $heading, $tabs_controls );
			$this->add_text( $tab_id, 'step_' . $bi . '_subheading', __( "Sub Heading", 'woofunnels-aero-checkout' ), '', $tabs_controls );
			$stepsCounter ++;
		}
		if ( $num_of_steps > 1 ) {

			$cartTitle          = __( 'Title', 'woofunnels-aero-checkout' );
			$settingDescription = __( 'Note: Cart settings will work for Global Checkout when user navigates from Product > Cart > Checkout', 'woofunnels-aero-checkout' );
			$cartText           = __( 'Cart', 'woocommerce' );
			$options            = [
				'yes' => __( 'Yes', 'woofunnels-aero-checkout' ),
				'no'  => __( 'No', 'woofunnels-aero-checkout' ),
			];
			$this->add_heading( $tab_id, 'Cart', 'none', $bredcrumb_controls );
			$this->add_select( $tab_id, 'step_cart_link_enable', __( "Add to Breadcrumb", 'woofunnels-aero-checkout' ), $options, 'yes', $bredcrumb_controls );
			$this->add_text( $tab_id, 'step_cart_bredcrumb_link', $cartTitle, $cartText, $bredcrumb_controls, $settingDescription );
		}
		$this->get_progress_settings( $tab_id );
	}

	private function get_heading_settings() {

		$heading_tab_id = $this->add_tab( __( 'Heading', 'woofunnels-aero-checkout' ) );

		$this->add_heading( $heading_tab_id, __( 'Typography' ) );
		$default = [
			'font_size' => '16',
		];


		$heading_wrapper = '#wfacp-e-form .wfacp_main_form.woocommerce .wfacp-section .wfacp-comm-title';

		/* Typography */
		$heading_typography = '#wfacp-e-form .wfacp_main_form.woocommerce .wfacp_section_heading.wfacp_section_title';
		$this->custom_typography( $heading_tab_id, $this->slug . '_heading_typography', $heading_typography, '', $default );

		$this->add_heading( $heading_tab_id, __( 'Color' ) );
		$this->add_color( $heading_tab_id, $this->slug . '_heading_color', $heading_typography, 'Text Color', '#333333' );

		$this->add_heading( $heading_tab_id, __( 'Alignment' ) );
		$this->add_text_alignments( $heading_tab_id, $this->slug . '_heading_alignment', $heading_typography );


		$this->add_heading( $heading_tab_id, __( 'Advanced' ) );
		$this->add_background_color( $heading_tab_id, $this->slug . '_heading_section_bg_color', $heading_wrapper, 'transparent' );

		$this->add_padding( $heading_tab_id, $this->slug . '_form_heading_padding', $heading_wrapper );
		$this->add_margin( $heading_tab_id, $this->slug . '_form_heading_margin', $heading_wrapper );

		$this->add_border( $heading_tab_id, $this->slug . '_form_heading_border', $heading_wrapper );


	}

	public function get_sub_heading_settings() {

		//Sub heading start here

		$default           = [
			'font_size' => '16',
		];
		$subheading_tab_id = $this->add_tab( __( 'Sub Heading', 'woofunnels-aero-checkout' ) );

		$subheading_typography = "#wfacp-e-form .wfacp_main_form.woocommerce .wfacp-comm-title h4";

		$this->custom_typography( $subheading_tab_id, $this->slug . '_subheading_typography', $subheading_typography, '', $default );

		$this->add_heading( $subheading_tab_id, __( 'Color' ) );
		$this->add_color( $subheading_tab_id, $this->slug . '_subheading_color', $subheading_typography, 'Text Color', '#333333' );

		$this->add_heading( $subheading_tab_id, __( 'Alignment' ) );
		$this->add_text_alignments( $subheading_tab_id, $this->slug . '_subheading_alignment', $subheading_typography );


	}

	private function section_typo_settings() {


		$section_id = $this->add_tab( __( 'Section', 'woofunnels-aero-checkout' ) );


		$form_section_bg_color = '#wfacp-e-form .wfacp-section';

		$this->add_heading( $section_id, __( 'Color' ) );
		$this->add_background_color( $section_id, 'form_section_bg_color', $form_section_bg_color, '', __( 'Background Color', 'woofunnels-aero-checkout' ) );

		$this->add_heading( $section_id, __( 'Advanced' ) );
		$this->add_padding( $section_id, 'form_section_padding', '#wfacp-e-form .wfacp-section' );
		$this->add_margin( $section_id, 'form_section_margin', '#wfacp-e-form .wfacp-section' );


		$this->add_border( $section_id, 'form_section_border', $form_section_bg_color );
		$this->add_box_shadow( $section_id, 'form_section_box_shadow', '#wfacp-e-form .wfacp-section' );

	}

	private function register_section_fields() {
		$template = wfacp_template();
		$steps    = $template->get_fieldsets();

		$do_not_show_fields = WFACP_Common::get_html_excluded_field();
		$exclude_fields     = [];

		$tab_instance = $this->add_tab( __( 'Fields', 'woofunnels-aero-checkout' ) );
		foreach ( $steps as $fieldsets ) {
			foreach ( $fieldsets as $section_data ) {
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
				if ( $html_field_count === $count ) {
					continue;
				}
				if ( is_array( $section_data['fields'] ) && count( $section_data['fields'] ) > 0 ) {
					foreach ( $section_data['fields'] as $fval ) {
						if ( isset( $fval['id'] ) && in_array( $fval['id'], $do_not_show_fields ) ) {//phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
							$exclude_fields[]                 = $fval['id'];
							$this->html_fields[ $fval['id'] ] = true;
							continue;
						}
					}
				}
				if ( count( $exclude_fields ) === count( $section_data['fields'] ) ) {
					continue;
				}
				$title = $section_data['name'];
				if ( empty( $title ) ) {
					$title = $this->get_title();
				}
				if ( isset( $section_data['sub_heading'] ) && ! empty( $section_data['sub_heading'] ) ) {
					$this->form_sub_headings[] = $section_data['sub_heading'];
				}
				$this->add_heading( $tab_instance, $title );

				$this->register_fields( $section_data['fields'], $tab_instance );
			}
		}

		/* Register Field Typography Setting */
		$this->fields_typo_settings();
	}

	private function register_fields( $temp_fields, $tab_instance ) {

		$template           = wfacp_template();
		$template_slug      = $template->get_template_slug();
		$template_cls       = $template->get_template_fields_class();
		$default_cls        = $template->default_css_class();
		$do_not_show_fields = WFACP_Common::get_html_excluded_field();
		foreach ( $temp_fields as $loop_key => $field ) {
			if ( in_array( $loop_key, [ 'wfacp_start_divider_billing', 'wfacp_start_divider_shipping' ], true ) ) {
				$address_key_group = ( $loop_key === 'wfacp_start_divider_billing' ) ? __( 'Billing Address', 'woocommerce' ) : __( 'Shipping Address', 'woocommerce' );
				$this->add_heading( $tab_instance, $address_key_group, 'none' );
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
			if ( in_array( $field_key, $do_not_show_fields ) ) {//phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				$this->html_fields[ $field_key ] = true;
				continue;
			}
			$skipKey = [ 'billing_same_as_shipping', 'shipping_same_as_billing' ];
			if ( in_array( $field_key, $skipKey ) ) {//phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				continue;
			}
			if ( isset( $field['type'] ) && 'wfacp_html' === $field['type'] ) {
				$options           = [ 'wfacp-col-full' => __( 'Full', 'woofunnels-aero-checkout' ), ];
				$field_default_cls = 'wfacp-col-full';
			} else {
				$options = $this->get_class_options();
			}
			$this->add_select( $tab_instance, 'wfacp_' . $template_slug . '_' . $field_key . '_field', $field['label'], $options, $field_default_cls );
		}


	}

	private function fields_typo_settings() {

		$default = [
			'font_size' => '16',
		];

		/* Field Label typography */
		$label_tabs_id = $this->add_tab( __( 'Label', 'woofunnels-aero-checkout' ) );

		$options = [
			'wfacp-top'    => __( 'Top of Field', 'woofunnel-aero-checkout' ),
			'wfacp-inside' => __( 'Inside Field', 'woofunnel-aero-checkout' ),

		];
		$this->add_select( $label_tabs_id, 'wfacp_label_position', __( 'Label Position', 'woofunnel-aero-checkout' ), $options, 'wfacp-inside' );


		$form_fields_label_typo = [
			'#wfacp-e-form .wfacp_main_form.woocommerce p.wfacp-form-control-wrapper label.wfacp-form-control-label',
			'#wfacp-e-form .wfacp_main_form.woocommerce p.wfacp-form-control-wrapper.wfacp_checkbox_field label',
			'#wfacp-e-form .wfacp_main_form.woocommerce .create-account label',
			'#wfacp-e-form .wfacp_main_form.woocommerce .create-account label span',
			'#wfacp-e-form .wfacp_main_form.woocommerce p.wfacp-form-control-wrapper.wfacp_checkbox_field label span',
			'#wfacp-e-form .wfacp_main_form.woocommerce p.wfacp-form-control-wrapper.wfacp_custom_field_radio_wrap > label ',
			'#wfacp-e-form .wfacp_main_form.woocommerce p.wfacp-form-control-wrapper:not(.wfacp-anim-wrap) label.wfacp-form-control-label abbr',
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li p',
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options',
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul',
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li',
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li p',
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li label',
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li .wfacp_shipping_price span',
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li .wfacp_shipping_price span bdi',
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li .wfacp_shipping_price',
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp_subscription_count_wrap p',
		];

		$this->add_heading( $label_tabs_id, __( 'Typography', 'woofunnels-aero-checkout' ), 2 );

		$this->custom_typography( $label_tabs_id, $this->slug . '_label_typo', implode( ',', $form_fields_label_typo ), '', $default );

		$this->add_heading( $label_tabs_id, __( 'Color', 'woofunnels-aero-checkout' ), 2 );
		$form_fields_label_color_opt = [
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp-form-control-label',
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp-form-control-label abbr',
		];
		$this->add_color( $label_tabs_id, $this->slug . '_label_text_color', implode( ',', $form_fields_label_color_opt ), 'Text Color', '#333333' );


	}

	public function input_setting() {

		$default        = [
			'font_size' => '16',
		];
		$fields_options = [
			'#wfacp-e-form .wfacp_main_form.woocommerce input[type="text"]',
			'#wfacp-e-form .wfacp_main_form.woocommerce input[type="email"]',
			'#wfacp-e-form .wfacp_main_form.woocommerce input[type="tel"]',
			'#wfacp-e-form .wfacp_main_form.woocommerce input[type="password"]',
			'#wfacp-e-form .wfacp_main_form.woocommerce input[type="number"]',
			'#wfacp-e-form .wfacp_main_form.woocommerce select',
			'#wfacp-e-form .wfacp_main_form.woocommerce textarea',
			'#wfacp-e-form .wfacp_main_form.woocommerce number',
			'#wfacp-e-form .woocommerce-input-wrapper .wfacp-form-control',
			'#wfacp-e-form .wfacp_main_form.woocommerce .select2-container .select2-selection--single .select2-selection__rendered',
			'body:not(.wfacp_pre_built) .select2-results__option',
			'body:not(.wfacp_pre_built) .select2-container--default .select2-search--dropdown .select2-search__field',
		];


		$input_id = $this->add_tab( __( 'Input', 'woofunnels-aero-checkout' ) );

		/* Field Label typography */

		$this->add_heading( $input_id, __( 'Typography', 'woofunnels-aero-checkout' ), 2 );
		$this->custom_typography( $input_id, $this->slug . '_input_typo', implode( ',', $fields_options ), '', $default );


		$this->add_heading( $input_id, __( 'Color', 'woofunnels-aero-checkout' ), 2 );


		$this->add_color( $input_id, $this->slug . '_input_text_color', implode( ',', $fields_options ), __( 'Text Color', 'woofunnels-aero-checkout' ), '#404040' );

		$inputbgColorOption = [
			'#wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-input-wrapper .wfacp-form-control:not(.input-checkbox)',
			'#wfacp-e-form .wfacp_main_form.woocommerce .select2-container .select2-selection--single .select2-selection__rendered',
			'#wfacp-e-form .wfacp_main_form.woocommerce select',
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp-login-wrapper input[type=email]',
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp-login-wrapper input[type=number]',
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp-login-wrapper input[type=password]',
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp-login-wrapper input[type=tel]',
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp-login-wrapper select',
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp-login-wrapper input[type=text]',
			'#wfacp-e-form .wfacp-form.wfacp-inside .form-row > label.wfacp-form-control-label:not(.checkbox)',
		];


		$this->add_background_color( $input_id, $this->slug . '_input_bg_color', implode( ',', $inputbgColorOption ), '#ffffff', __( 'Background Color', 'woofunnels-aero-checkout' ) );
		$validation_error = [
			'#wfacp-e-form .wfacp_main_form.woocommerce p.woocommerce-invalid-required-field .wfacp-form-control',
			'#wfacp-e-form .wfacp_main_form.woocommerce p.woocommerce-invalid-email .wfacp-form-control',
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp_coupon_failed .wfacp_coupon_code',
			'#wfacp-e-form .wfacp_main_form.woocommerce p.woocommerce-invalid-required-field:not(.wfacp_select2_country_state):not(.wfacp_state_wrap) .woocommerce-input-wrapper .select2-container .select2-selection--single .select2-selection__rendered',
		];


		$this->add_border_color( $input_id, $this->slug . '_text_validation_color', implode( ',', $validation_error ), '#D50000', __( 'Error Validation Color', 'woofunnels-aero-checkout' ) );
		$this->add_border_color( $input_id, $this->slug . '_text_focus_color', '#wfacp-e-form .wfacp_main_form.woocommerce .form-row:not(.woocommerce-invalid-email) .wfacp-form-control:focus', '#61BDF7', __( 'Focus Color', 'woofunnels-aero-checkout' ), true );


		$fields_options = [
			'#wfacp-e-form .wfacp_main_form.woocommerce input[type="text"]',
			'#wfacp-e-form .wfacp_main_form.woocommerce input[type="email"]',
			'#wfacp-e-form .wfacp_main_form.woocommerce input[type="tel"]',
			'#wfacp-e-form .wfacp_main_form.woocommerce input[type="password"]',
			'#wfacp-e-form .wfacp_main_form.woocommerce input[type="number"]',
			'#wfacp-e-form .wfacp_main_form.woocommerce select',
			'#wfacp-e-form .wfacp_main_form.woocommerce textarea',
			'#wfacp-e-form .wfacp_main_form.woocommerce number',
			'#wfacp-e-form .woocommerce-input-wrapper .wfacp-form-control',
			'#wfacp-e-form .wfacp_main_form.woocommerce .select2-container .select2-selection--single .select2-selection__rendered',
			'body:not(.wfacp_pre_built) .select2-results__option',
			'body:not(.wfacp_pre_built) .select2-container--default .select2-search--dropdown .select2-search__field',
		];


		$this->add_heading( $input_id, __( 'Advanced', 'woofunnels-aero-checkout' ), 2 );

		$this->add_border( $input_id, $this->slug . '_field_border', implode( ',', $fields_options ) );

	}

	private function payment_method() {


		$tab_id = $this->add_tab( __( 'Payment Methods', 'woofunnels-aero-checkout' ) );
		$this->add_heading( $tab_id, __( 'Section', 'woofunnels-aero-checkout' ) );
		$this->add_text( $tab_id, 'wfacp_payment_method_heading_text', __( 'Heading', 'woofunnels-aero-checkout' ), '' );
		$this->add_textArea( $tab_id, 'wfacp_payment_method_subheading', __( 'Sub heading', 'woofunnels-aero-checkout' ), '' );
		$this->payment_method_styling( $tab_id );
		$this->payment_buttons_styling( $tab_id );

		$this->ajax_session_settings[] = 'wfacp_payment_method_heading_text';
		$this->ajax_session_settings[] = 'wfacp_payment_method_subheading';

	}

	private function form_buttons( $tab_id ) {
		$template    = wfacp_template();
		$count       = $template->get_step_count();
		$backLinkArr = [];
		$this->add_heading( $tab_id, __( 'Button Text', 'woofunnels-aero-checkout' ), 'none' );
		for ( $i = 1; $i <= $count; $i ++ ) {
			$button_default_text = __( 'NEXT STEP →', 'woofunnels-aero-checkout' );
			$button_key          = 'wfacp_payment_button_' . $i . '_text';
			if ( absint( $i ) === absint( $count ) ) {
				$button_key          = 'wfacp_payment_place_order_text';
				$button_default_text = __( 'PLACE ORDER NOW', 'woofunnels-aero-checkout' );
			}
			$this->ajax_session_settings[] = $button_key;
			$this->add_text( $tab_id, $button_key, __( "Step {$i}", 'woofunnels-aero-checkout' ), esc_js( $button_default_text ) );
			if ( $i > 1 ) {
				$backCount                                            = $i - 1;
				$backLinkArr[ 'payment_button_back_' . $i . '_text' ] = [
					'label' => __( "Return to Step {$backCount}", 'woofunnels-aero-checkout' ),
				];
			}
		}
		if ( is_array( $backLinkArr ) && count( $backLinkArr ) > 0 ) {
			$this->add_heading( $tab_id, __( 'Return Link Text', 'woofunnels-aero-checkout' ), 'none' );
			$cart_name                     = __( '« Return to Cart', 'woofunnels-aero-checkout' );
			$this->ajax_session_settings[] = "return_to_cart_text";
			$this->add_text( $tab_id, "return_to_cart_text", 'Return to Cart', $cart_name, [ 'step_cart_link_enable' => 'yes' ] );
			foreach ( $backLinkArr as $i => $val ) {
				$this->ajax_session_settings[] = $i;
				$this->add_text( $tab_id, $i, $val['label'], '', [] );
			}
		}
		$this->ajax_session_settings[] = 'text_below_placeorder_btn';
		$this->add_text( $tab_id, 'text_below_placeorder_btn', __( "Text Below Place Order Button", 'woofunnels-aero-checkout' ), esc_attr__( 'We Respect Your Privacy & Information', 'woofunnels-aero-checkout' ) );
	}

	private function mobile_mini_cart() {

		$tab_id = $this->add_tab( __( 'Collapsible Order Summary', 'woofunnels-aero-checkout' ) );


		$this->add_switcher( $tab_id, 'enable_callapse_order_summary', __( 'Enable', 'woofunnels-aero-checkout' ), 'off' );

		$this->add_heading( $tab_id, __( 'Collapsed', 'woofunnels-aero-checkout' ) );

		$this->add_text( $tab_id, 'cart_collapse_title', __( 'Collapsed View Text ', 'woofunnels-aero-checkout' ), __( 'Show Order Summary', 'woofunnels-aero-checkout' ) );

		$this->add_sub_heading( $tab_id, __( 'Color', 'woofunnels-aero-checkout' ) );

		$this->add_color( $tab_id, $this->slug . '_expanded_order_summary_link_color', '#wfacp-e-form .wfacp_show_icon_wrap a span,#wfacp-e-form .wfacp_show_price_wrap span', __( 'Text Color', 'woofunnels-aero-checkout' ), '#323232' );
		$this->add_background_color( $tab_id, $this->slug . '_collapsible_order_summary_bg_color', '#wfacp-e-form .wfacp_mb_mini_cart_wrap .wfacp_mb_cart_accordian', '#f7f7f7', __( 'Collapsed Background', 'woofunnels-aero-checkout' ) );


		$this->add_heading( $tab_id, __( 'Expanded', 'woofunnels-aero-checkout' ) );
		$this->add_text( $tab_id, 'cart_expanded_title', __( 'Expanded View Text', 'woofunnels-aero-checkout' ), __( 'Hide Order Summary', 'woofunnels-aero-checkout' ) );

		$collapse_enable_coupon = [
			'collapse_enable_coupon' => 'on',
		];

		$this->add_switcher( $tab_id, 'collapse_enable_coupon', __( 'Enable Coupon', 'woofunnels-aero-checkout' ), 'on' );
		$this->add_switcher( $tab_id, 'collapse_enable_coupon_collapsible', __( 'Collapsible Coupon Field', 'woofunnels-aero-checkout' ), 'on', $collapse_enable_coupon );
		$this->add_text( $tab_id, 'collapse_coupon_button_text', __( 'Coupon Button Text', 'woofunnels-aero-checkout' ), __( 'Apply Coupon', 'woocommerce' ), $collapse_enable_coupon );
		$this->add_switcher( $tab_id, 'collapse_enable_quantity_number', __( 'Quantity Count', 'woofunnels-aero-checkout' ), 'on' );

		$this->add_switcher( $tab_id, 'collapse_order_quantity_switcher', __( 'Quantity Switcher', 'woofunnels-aero-checkout' ), 'on', $collapse_enable_coupon );
		$this->add_switcher( $tab_id, 'collapse_order_delete_item', __( 'Allow Deletion', 'woofunnels-aero-checkout' ), 'on', $collapse_enable_coupon );

		$this->ajax_session_settings[] = 'enable_callapse_order_summary';
		$this->ajax_session_settings[] = 'enable_callapse_order_summary_tablet';
		$this->ajax_session_settings[] = 'enable_callapse_order_summary_phone';
		$this->ajax_session_settings[] = 'enable_callapse_order_summary_page_width';


		/* for Enable Progress Bar */
		$this->ajax_session_settings[] = 'enable_progress_bar_tablet';
		$this->ajax_session_settings[] = 'enable_progress_bar_phone';
		$this->ajax_session_settings[] = 'enable_progress_bar_page_width';

		/* for Enable Progress Bar */
		$this->ajax_session_settings[] = 'enable_progress_bar_tablet';
		$this->ajax_session_settings[] = 'enable_progress_bar_phone';
		$this->ajax_session_settings[] = 'enable_progress_bar_page_width';

		$this->ajax_session_settings[] = 'cart_collapse_title';
		$this->ajax_session_settings[] = 'cart_expanded_title';
		$this->ajax_session_settings[] = 'collapse_enable_coupon';
		$this->ajax_session_settings[] = 'collapse_enable_coupon_collapsible';
		$this->ajax_session_settings[] = 'collapse_enable_quantity_number';
		$this->ajax_session_settings[] = 'collapse_order_quantity_switcher';
		$this->ajax_session_settings[] = 'collapse_order_delete_item';
		$this->collapsible_order_summary( $tab_id );
	}

	protected function register_styles() {

		foreach ( $this->html_fields as $key => $v ) {
			$this->generate_html_block( $key );
		}
		$this->payment_method();

		$this->mobile_mini_cart();
		$this->global_typography();

	}

	public function get_progress_settings( $tab_id ) {
		$template        = wfacp_template();
		$number_of_steps = $template->get_step_count();
		if ( $number_of_steps < 1 ) {
			return;
		}


		$tab_condition        = [ 'select_type' => 'tab', 'enable_progress_bar' => 'on' ];
		$breadcrumb_condition = [ 'select_type' => 'bredcrumb', 'enable_progress_bar' => 'on' ];

		$this->add_heading( $tab_id, __( 'Breadcrumb Typography', 'woofunnels-aero-checkout' ), '', $breadcrumb_condition );
		$this->custom_typography( $tab_id, 'breadcrumb_heading_typography', '#wfacp-e-form .wfacp_main_form.woocommerce .wfacp_steps_sec ul li a', 'BreadCrumb Typography', '', $breadcrumb_condition );

		/* BreadCrumb color setting */

		$this->add_color( $tab_id, 'breadcrumb_text_color_1', '#wfacp-e-form .wfacp-form .wfacp_main_form.woocommerce .wfacp_steps_sec ul li a', 'Color', '#dd7575', $breadcrumb_condition );
		$this->add_color( $tab_id, 'breadcrumb_text_hover_color', '#wfacp-e-form .wfacp-form .wfacp_main_form.woocommerce .wfacp_steps_sec ul li a:hover', 'Hover Color', '#000000', $breadcrumb_condition );


		/* Back link color setting End*/

		/** Tab settings start completed */
		$this->add_heading( $tab_id, "Tab Colors Settings", '', $tab_condition );

		$this->add_heading( $tab_id, __( 'Active Step', 'woofunnels-aero-checkout' ), '', $tab_condition );
		$this->add_background_color( $tab_id, 'active_step_bg_color', '#wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list.wfacp-active', '', 'Background Color', $tab_condition );
		$this->add_color( $tab_id, 'active_step_text_color', '#wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list.wfacp-active .wfacp_tcolor', 'Text Color', '', $tab_condition );
		$this->add_border_color( $tab_id, 'active_tab_border_bottom_color', '#wfacp-e-form .wfacp-payment-tab-list.wfacp-active', '#000000', __( 'Tab Border Color', 'woofunnels-aero-checkout' ), false, $tab_condition );
		if ( $number_of_steps > 1 ) {
			$this->add_background_color( $tab_id, 'active_step_count_bg_color', '#wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list.wfacp-active .wfacp-order2StepNumber', '#000000', 'Count Background Color', $tab_condition );
			$this->add_border_color( $tab_id, 'active_step_count_border_color', '#wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list.wfacp-active .wfacp-order2StepNumber', '#000000', __( 'Count Border Color', 'woofunnels-aero-checkout' ), false, $tab_condition );
			$this->add_color( $tab_id, 'active_step_count_text_color', '#wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list.wfacp-active .wfacp-order2StepNumber', 'Count Text Color', '', $tab_condition );
		}


		//Put All active step Field to control Tab
		$this->add_heading( $tab_id, __( 'InActive Step', 'woofunnels-aero-checkout' ), '', $tab_condition );
		$this->add_background_color( $tab_id, 'inactive_step_bg_color', '#wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list', '', __( 'Background Color', 'woofunnels-aero-checkout' ), $tab_condition );
		$this->add_color( $tab_id, 'inactive_step_text_color', '#wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list .wfacp_tcolor', __( 'Text Color', 'woofunnels-aero-checkout' ), '', $tab_condition );
		$this->add_border_color( $tab_id, 'inactive_tab_border_bottom_color', '#wfacp-e-form .wfacp-payment-tab-list', '#000000', __( 'Tab Border Color', 'woofunnels-aero-checkout' ), false, $tab_condition );
		$this->add_background_color( $tab_id, 'inactive_step_count_bg_color', '#wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list .wfacp-order2StepNumber', '#000000', 'Count Background Color', $tab_condition );
		$this->add_border_color( $tab_id, 'inactive_step_count_border_color', '#wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list .wfacp-order2StepNumber', '#000000', __( 'Count Border Color', 'woofunnels-aero-checkout' ), false, $tab_condition );
		$this->add_color( $tab_id, 'inactive_step_count_text_color', '#wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list .wfacp-order2StepNumber', 'Count Text Color', '', $tab_condition );

		$this->add_heading( $tab_id, __( 'Heading Typography', 'woofunnels-aero-checkout' ), '', $tab_condition );
		$this->custom_typography( $tab_id, 'tab_heading', '#wfacp-e-form .wfacp_form_steps .wfacp-order2StepTitle.wfacp-order2StepTitleS1', '', [], $tab_condition );


		$this->add_heading( $tab_id, __( 'SubHeading Typography', 'woofunnels-aero-checkout' ), '', $tab_condition );
		$this->custom_typography( $tab_id, 'tab_subheading', '#wfacp-e-form .wfacp_form_steps .wfacp-order2StepSubTitle', '', [], $tab_condition );
		//Put In Active step Field to control Tab

		/** Tab settings completed */
		$this->add_border_radius( $tab_id, 'border_radius_steps', '#wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list', $tab_condition );
		$this->add_margin( $tab_id, 'wfacp_tab_margin', '#wfacp-e-form .tab', '', $tab_condition );
	}

	private function payment_buttons_styling( $tab_id ) {


		$tab_id = $this->add_tab( __( 'Payment Buttons', 'woofunnels-aero-checkout' ) );
		$this->form_buttons( $tab_id );

		$this->add_heading( $tab_id, __( 'Steps Buttons', 'woofunnels-aero-checkout' ), 2 );

		$selector        = '#wfacp-e-form .wfacp_main_form.woocommerce .wfacp-next-btn-wrap button,#wfacp-e-form .wfacp_main_form.woocommerce #payment button#place_order';
		$button_selector = '#wfacp-e-form .wfacp_main_form.woocommerce #payment button#place_order,#wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-checkout button.button.button-primary.wfacp_next_page_button';

		$this->add_switcher( $tab_id, 'wfacp_make_button_sticky_on_mobile', __( 'Sticky on Mobile', 'woofunnels-aero-checkout' ), 'off' );

		$this->add_width( $tab_id, 'wfacp_button_width', $selector, 'Button Width', 100 );


		$this->custom_typography( $tab_id, 'wfacp_form_payment_button_typo', $button_selector, __( 'Buttons Typography', 'woofunnels-aero-checkout' ) );
		$this->add_text_alignments( $tab_id, 'wfacp_form_payment_alignment', '#wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-checkout .wfacp-order-place-btn-wrap, #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-checkout .wfacp-next-btn-wrap', '', 'center' );

		$normal_color = [
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp-next-btn-wrap button',
			'#wfacp-e-form .wfacp_main_form.woocommerce #payment button#place_order',
			'#wfacp_qr_model_wrap .wfacp_qr_wrap .wfacp_qv-summary .button',
		];


		/* Color Setting */

		$this->add_heading( $tab_id, __( 'Color', 'woofunnels-aero-checkout' ) );
		$this->add_sub_heading( $tab_id, __( 'Normal', 'woofunnels-aero-checkout' ) );
		$this->add_color( $tab_id, $this->slug . '_buttons_text_color_1', implode( ',', $normal_color ), 'Text', '#ffffff' );
		$this->add_background_color( $tab_id, $this->slug . '_buttons_background_color_1', implode( ',', $normal_color ), "#24ae4e", 'Background' );

		$hover_color = [
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp-next-btn-wrap button:hover',
			'#wfacp-e-form .wfacp_main_form.woocommerce #payment button#place_order:hover',
			'#wfacp_qr_model_wrap .wfacp_qr_wrap .wfacp_qv-summary .button:hover',
		];

		$this->add_sub_heading( $tab_id, __( 'Hover', 'woofunnels-aero-checkout' ) );
		$this->add_color( $tab_id, $this->slug . '_buttons_text_hover_color', implode( ',', $hover_color ), 'Text', '#ffffff' );
		$this->add_background_color( $tab_id, $this->slug . '_buttons_background_hover_color', implode( ',', $hover_color ), "#7aa631", 'Background' );


		$this->add_heading( $tab_id, __( 'Typography', 'woofunnels-aero-checkout' ) );


		$this->add_heading( $tab_id, __( 'Advanced', 'woofunnels-aero-checkout' ) );
		$this->add_padding( $tab_id, $this->slug . '_button_padding', $selector );
		$this->add_margin( $tab_id, $this->slug . "_button_margin", $selector );
		$this->add_border( $tab_id, $this->slug . "_button_border", $selector, __( 'Button Border', 'woofunnels-aero-checkout' ) );

		/* Back Link color setting */
		$this->add_heading( $tab_id, __( 'Return Link', 'woofunnels-aero-checkout' ) );
		$stepBackLink      = '#wfacp-e-form .wfacp_main_form.woocommerce .wfacp-back-btn-wrap a,#wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-checkout .place_order_back_btn a';
		$stepBackLinkHover = '#wfacp-e-form .wfacp_main_form.woocommerce .wfacp-back-btn-wrap a:hover,#wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-checkout .place_order_back_btn a:hover';
		$this->add_color( $tab_id, 'step_back_link_color', $stepBackLink, 'Normal Color' );
		$this->add_color( $tab_id, 'step_back_link_hover_color', $stepBackLinkHover, 'Hover Color' );

		/* Back link color setting End*/
		$this->add_heading( $tab_id, __( 'Additional Text', 'woofunnels-aero-checkout' ) );
		$this->add_color( $tab_id, 'additional_text_color', '#wfacp-e-form .wfacp_main_form.woocommerce .wfacp-payment-dec', '', '#737373' );
		$this->add_background_color( $tab_id, 'additional_bg_color', '#wfacp-e-form .wfacp_main_form.woocommerce .wfacp-payment-dec', "", 'Background' );
		$this->ajax_session_settings[] = 'wfacp_make_button_sticky_on_mobile';
	}

	private function payment_method_styling( $tab_id ) {

		$btn_method_typo = [
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment',
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment p',
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment p span',
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment label',
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment ul',
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment ul li',
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment ul li input',
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment #add_payment_method #payment div.payment_box',
			'#wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment #add_payment_method #payment .payment_box p',
		];

		$default = [
			'font_size' => '14',
		];

		$this->add_heading( $tab_id, __( 'Typography', 'woofunnels-aero-checkout' ) );
		$this->custom_typography( $tab_id, 'wfacp_form_payment_method_typo', implode( ',', $btn_method_typo ), '', $default );


	}

	private function global_typography() {
		$tab_id = $this->add_tab( __( 'Checkout Form', 'woofunnels-aero-checkout' ) );

		$this->add_padding( $tab_id, 'wfacp_form_border_padding', '#wfacp-e-form .wfacp-form' );

		$globalSettingOptions = [
			'#wfacp-e-form  *',
		];

		$this->add_font_family( $tab_id, 'wfacp_font_family', implode( ',', $globalSettingOptions ) );


		$fields_contentColor = [
			'#wfacp-e-form .woocommerce-checkout #payment div.payment_box p',
			'#wfacp-e-form .wfacp_main_form .woocommerce-form-login-toggle .woocommerce-info',
			'#wfacp-e-form .wfacp_main_form form.woocommerce-form.woocommerce-form-login.login p',
			'#wfacp-e-form .wfacp_main_form label.woocommerce-form__label span',
			'#wfacp-e-form .wfacp_main_form .wfacp_checkbox_field label',
			'#wfacp-e-form .wfacp_main_form .wfacp_checkbox_field span',
			'#wfacp-e-form .wfacp_main_form .wfacp_shipping_options label',
			'#wfacp-e-form .wfacp_main_form ul li span',
			'#wfacp-e-form .wfacp_main_form .wfacp_shipping_table tr.shipping td p',
			'#wfacp-e-form .wfacp_main_form .wfacp-product-switch-title div',
			'#wfacp-e-form .wfacp_main_form .woocommerce-privacy-policy-text p',
			'#wfacp-e-form .wfacp_main_form .wfacp_shipping_options ul li p',
			'#wfacp-e-form .wfacp_main_form .shop_table .wfacp-product-switch-title div',
			'#wfacp-e-form .wfacp_main_form .woocommerce-info .message-container',
			'#wfacp-e-form .wfacp_main_form #wc_checkout_add_ons .description',
			'#wfacp-e-form .wfacp_main_form ol li',
			'#wfacp-e-form .wfacp_main_form ul li',
			'#wfacp-e-form .wfacp_main_form .wfacp_shipping_table ul#shipping_method label',
			'#wfacp-e-form .wfacp_main_form .wfacp_shipping_table ul#shipping_method span',
			'#wfacp-e-form .wfacp_main_form .woocommerce-checkout-review-order h3',
			'#wfacp-e-form .wfacp_main_form .aw_addon_wrap label',
			'#wfacp-e-form .wfacp_main_form p:not(.woocommerce-shipping-contents):not(.wfacp_dummy_preview_heading )',
			'#wfacp-e-form .wfacp_main_form label:not(.wfacp-form-control-label):not(.wfob_title):not(.wfob_span)',
			'#wfacp-e-form .wfacp_main_form label:not(.wfob_title) span:not(.optional)',
			'#wfacp-e-form .wfacp_main_form',
			'#wfacp-e-form .wfacp_main_form .woocommerce-message',
			'#wfacp-e-form .wfacp_main_form .woocommerce-error',
			'#wfacp-e-form .wfacp_main_form .wfacp_shipping_table ul li label',
			'#wfacp-e-form .wfacp_main_form .wfacp_shipping_table ul li label span',
			'#wfacp-e-form .wfacp_main_form .wfacp_shipping_table ul li span',
			'#wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr.recurring-totals > th',
			'#wfacp-e-form .wfacp_main_form p.wfacp-form-control-wrapper:not(.wfacp-anim-wrap) label.wfacp-form-control-label abbr',
		];

		$this->add_color( $tab_id, 'default_text_color1', implode( ',', $fields_contentColor ), __( 'Default Text Color', 'woofunnels-aero-checkout' ) );


		$default_link_color_option       = [
			'#wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-form-login-toggle .woocommerce-info a',
			'#wfacp-e-form .wfacp_main_form.woocommerce a:not(.wfacp_close_icon)',
			'#wfacp-e-form .wfacp_main_form.woocommerce a span',
			'#wfacp-e-form .wfacp_main_form.woocommerce label a',
			'#wfacp-e-form .wfacp_main_form.woocommerce ul li a',
			'#wfacp-e-form .wfacp_main_form.woocommerce table tr td a',
			'#wfacp-e-form .wfacp_main_form.woocommerce a.wfacp_remove_coupon',
			'#wfacp-e-form .wfacp_woocommerce_form_coupon .wfacp-coupon-section .woocommerce-info .wfacp_showcoupon',
		];
		$default_link_hover_color_option = [
			'#wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-form-login-toggle .woocommerce-info a:hover',
			'#wfacp-e-form .wfacp_main_form.woocommerce a:not(.wfacp_close_icon):not(.button-social-login):hover',
			'#wfacp-e-form .wfacp_main_form.woocommerce a span:hover',
			'#wfacp-e-form .wfacp_main_form.woocommerce label a:hover',
			'#wfacp-e-form .wfacp_main_form.woocommerce ul li a:hover',
			'#wfacp-e-form .wfacp_main_form.woocommerce table tr td a:hover',
			'#wfacp-e-form .wfacp_main_form.woocommerce a.wfacp_remove_coupon:hover',
			'#wfacp-e-form .wfacp_woocommerce_form_coupon .wfacp-coupon-section .woocommerce-info .wfacp_showcoupon:hover',
		];
		$this->add_color( $tab_id, $this->slug . '_default_link_color', implode( ',', $default_link_color_option ), __( 'Default Link Color', 'woofunnels-aero-checkout' ), '#dd7575' );
		$this->add_color( $tab_id, $this->slug . 'default_link_hover_color', implode( ',', $default_link_hover_color_option ), __( 'Default Link Hover Color', 'woofunnels-aero-checkout' ), '#965d5d' );
	}

	private function collapsible_order_summary( $tab_id ) {

		$this->add_switcher( $tab_id, 'order_summary_enable_product_image_collapsed', __( 'Enable Image', 'woofunnels-aero-checkout' ), 'on' );

		$this->add_sub_heading( $tab_id, __( 'Color', 'woofunnels-aero-checkout' ) );

		$this->add_border_color( $tab_id, $this->slug . '_expanded_cart_product_image_border_color', '#wfacp-e-form .wfacp_collapsible_order_summary_wrap .product-image .wfacp-pro-thumb img', '#e1e1e1', __( 'Product Image Border Color', 'woofunnel-aero-checkout' ) );
		$this->add_background_color( $tab_id, $this->slug . '_expanded_order_summary_bg_color', '#wfacp-e-form .wfacp_mb_mini_cart_sec_accordion_content', '#f7f7f7', __( 'Expanded Background', 'woofunnels-aero-checkout' ) );

		$divider_color = [
			'#wfacp-e-form table.shop_table.woocommerce-checkout-review-order-table_layout_9 tr.cart-subtotal',
			'#wfacp-e-form table.shop_table.woocommerce-checkout-review-order-table_layout_9 tr.order-total',
			'#wfacp-e-form .wfacp_mb_mini_cart_wrap .wfacp_woocommerce_form_coupon',
			'#wfacp-e-form table.shop_table.woocommerce-checkout-review-order-table_layout_9 tr.cart_item'
		];
		$this->add_border_color( $tab_id, $this->slug . '_expanded_cart_divider_color', implode( ',', $divider_color ), '#ddd', __( 'Divider Color', 'woofunnel-aero-checkout' ) );

		$this->add_sub_heading( $tab_id, __( 'Advanced', 'woofunnels-aero-checkout' ) );
		$this->add_margin( $tab_id, 'wfacp_collapsible_margin', '#wfacp-e-form .wfacp_collapsible_order_summary_wrap' );
		$this->add_border_radius( $tab_id, 'wfacp_collapsible_border', '#wfacp-e-form .wfacp_mb_mini_cart_wrap .wfacp_mb_cart_accordian' );

		$this->add_heading( $tab_id, __( 'Typography', 'woofunnels-aero-checkout' ) );

		/* Cart field start */
		$collapsed_cart_product_typo = [
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap tr.cart_item td:first-child.product-name-area .wfacp_mini_cart_item_title',
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap tr.cart_item td:first-child.product-name-area span strong.product-quantity',
		];

		$this->add_typography( $tab_id, 'collapsed_cart_product_typo', implode( ',', $collapsed_cart_product_typo ), __( 'Product', 'wooofunels-aero-checkout' ) );

		$collapsed_cart_product_price_typo = [
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap tr.cart_item td:last-child',
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap tr.cart_item td:last-child p',
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap tr.cart_item td:last-child span',
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap tr.cart_item td:last-child span.amount',
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap tr.cart_item td:last-child span bdi',
		];

		$this->add_typography( $tab_id, 'collapsed_cart_product_price_typo', implode( ', ', $collapsed_cart_product_price_typo ), __( 'Product Price', 'woofunnels-aero-checkout' ) );
		$collapsed_cart_product_variant_typo = [
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap tr.cart_item td:first-child.product-name-area dl',
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap tr.cart_item td:first-child.product-name-area dt',
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap tr.cart_item td:first-child.product-name-area dd',
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap tr.cart_item td:first-child.product-name-area dd p',
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap tr.cart_item td:first-child .subscription-details',
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap tr.cart_item td:first-child .wfacp_product_subs_details span',
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap tr.cart_item td:first-child .wfacp_product_subs_details span bdi',
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap tr.cart_item td:first-child .subscription-details span',
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap tr.cart_item td:first-child .subscription-details span.amount ',
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap tr.cart_item td:first-child .subscription-details span.amount bdi',
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap tr.cart_item td:first-child .subscription-details span p',
		];

		$this->add_typography( $tab_id, 'collapsed_cart_product_variant_typo', implode( ', ', $collapsed_cart_product_variant_typo ), __( 'Product Variant', 'woofunnels-aero-checkout' ) );


		/* Subtotal Fields */

		$subtotal_price_label_typo = [
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap table.wfacp_mini_cart_reviews tfoot tr:not(.order-total) td:first-child',
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap table.wfacp_mini_cart_reviews tfoot tr:not(.order-total) th:first-child',
		];
		$this->add_typography( $tab_id, $this->slug . '_collapsed_subtotal_price_label_typo', implode( ', ', $subtotal_price_label_typo ), __( 'Subtotal Label', 'woofunnels-aero-checkout' ) );

		$subtotal_price_typo = [
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap table.wfacp_mini_cart_reviews tfoot tr:not(.order-total) td:last-child',
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap table.wfacp_mini_cart_reviews tfoot tr:not(.order-total) td:last-child span.amount',
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap table.wfacp_mini_cart_reviews tfoot tr:not(.order-total) td:last-child span',
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap table.wfacp_mini_cart_reviews tfoot tr:not(.order-total) td:last-child span bdi',
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap table.wfacp_mini_cart_reviews tfoot tr:not(.order-total) td:last-child a',
		];

		$this->add_typography( $tab_id, $this->slug . '_collapsed_subtotal_price_typo', implode( ', ', $subtotal_price_typo ), __( 'Subtotal Price', 'woofunnels-aero-checkout' ) );

		/* Total Fields */
		$total_price_label_typo = [
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap table.wfacp_mini_cart_reviews tfoot tr.order-total td:first-child',
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap table.wfacp_mini_cart_reviews tfoot tr.order-total th:first-child',
		];
		$this->add_typography( $tab_id, $this->slug . '_collapsed_total_price_label_typo', implode( ', ', $total_price_label_typo ), __( 'Total Label', 'woofunnels-aero-checkout' ) );
		$total_price_typo = [
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap table.wfacp_mini_cart_reviews tfoot tr.order-total td:last-child',
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap table.wfacp_mini_cart_reviews tfoot tr.order-total td:last-child span.amount',
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap table.wfacp_mini_cart_reviews tfoot tr.order-total td:last-child span',
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap table.wfacp_mini_cart_reviews tfoot tr.order-total td:last-child span bdi',
			'#wfacp-e-form .wfacp_collapsible_order_summary_wrap table.wfacp_mini_cart_reviews tfoot tr.order-total td:last-child a',
		];
		$this->add_typography( $tab_id, $this->slug . '_collapsed_total_price_typo', implode( ', ', $total_price_typo ), __( 'Total Price', 'woofunnels-aero-checkout' ) );
		/* End */


		$this->ajax_session_settings[] = 'order_summary_enable_product_image_collapsed';

		do_action( 'wfacp_elementor_collapsible_fields_settings', $this, $tab_id );
	}

	public function html( $setting, $defaults, $content ) {//phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedParameter
		$template = wfacp_template();

		if ( is_null( $template ) ) {
			return '';
		}
		$data       = [];
		$keys_check = [
			'enable_progress_bar',
			'enable_callapse_order_summary',
			'enable_product_image_collapsed',
		];
		if ( isset( $setting['collapse_enable_quantity_number'] ) && "off" === $setting['collapse_enable_quantity_number'] ) {
			echo "<style>";
			echo ".wfacp_mb_mini_cart_sec_accordion_content .wfacp-qty-ball{display: none;}";
			echo ".wfacp_mb_mini_cart_sec_accordion_content strong.product-quantity{display: none;}";
			echo "</style>";
		}


		if ( is_array( $this->media_settings ) && count( $this->media_settings ) > 0 ) {
			foreach ( $this->media_settings as $key => $value ) {
				foreach ( $value['original'] as $key1 => $value1 ) {
					$new_key = str_replace( 'oxy-' . $this->slug() . "_", '', $key1 );
					if ( in_array( $new_key, $keys_check ) ) {//phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict

						if ( false !== strpos( $key, 'tablet' ) ) {
							$data[ $new_key . '_tablet' ] = $value1;
						} elseif ( false !== strpos( $key, 'phone' ) ) {
							$data[ $new_key . '_phone' ] = $value1;
						}

						if ( false !== strpos( $key, 'page-width' ) ) {
							$data[ $new_key . '_page_width' ] = $value1;
						}
					}
				}
			}
		}


		if ( is_array( $data ) && count( $data ) > 0 ) {
			$this->settings = array_merge( $setting, $data );
		}


		$this->save_ajax_settings();
		$template->set_form_data( $this->settings );
		if ( isset( $_COOKIE['wfacp_oxy_open_page'] ) && wp_doing_ajax() ) {
			$cookie = $_COOKIE['wfacp_oxy_open_page'];//phpcs:ignore
			$parts  = explode( '@', $cookie );
			$template->set_current_open_step( $parts[1] );
		}
		include $template->wfacp_get_form();
	}

	protected function preview_shortcode() {
		echo '[Checkout Form]';
	}


}

new WFACP_OXY_Form;