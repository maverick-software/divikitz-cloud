<?php
/**
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.0.0
 * @package BWF Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to Enqueue CSS of all the blocks.
 *
 * @category class
 */
class WFACP_Blocks_Frontend_CSS {
	/**
	 * Instance of this class
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Google fonts to enqueue
	 *
	 * @var array
	 */
	public static $gfonts = array();

	/**
	 * Instance Control
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Class Constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_inline_css' ) );
		// add_action( 'wp_head', array( $this, 'frontend_gfonts' ), 90 );
	}
	
	/**
	 * Outputs extra css for blocks.
	 */
	public function frontend_inline_css() {
		if ( function_exists( 'has_blocks' ) && has_blocks( get_the_ID() ) ) {
			global $post;
			if ( ! is_object( $post ) ) {
				return;
			}
			if( ! class_exists( 'WFACP_Common' ) || WFACP_Common::get_post_type_slug() !== $post->post_type ) {
				return;
			}
			$this->frontend_build_css( $post );
		}
	}

	/**
	 * Render Inline CSS helper function
	 *
	 * @param array  $css the css for each rendered block.
	 * @param string $style_id the unique id for the rendered style.
	 * @param bool   $in_content the bool for whether or not it should run in content.
	 */
	public function render_inline_css( $css, $style_id, $in_content = false ) {
		if ( ! is_admin() ) {
			wp_register_style( $style_id, false );
			wp_enqueue_style( $style_id );
			wp_add_inline_style( $style_id, $css );
			if ( 1 === did_action( 'wp_head' ) && $in_content ) {
				wp_print_styles( $style_id );
			}
		}
	}

	/**
	 * Gets the parsed blocks, need to use this becuase wordpress 5 doesn't seem to include gutenberg_parse_blocks
	 *
	 * @param string $content string of page/post content.
	 */
	public function bwf_parse_blocks( $content ) {
		$parser_class = apply_filters( 'block_parser_class', 'WP_Block_Parser' );
		if ( class_exists( $parser_class ) ) {
			$parser = new $parser_class();
			return $parser->parse( $content );
		} elseif ( function_exists( 'gutenberg_parse_blocks' ) ) {
			return gutenberg_parse_blocks( $content );
		} else {
			return false;
		}
	}
	
	
	/**
	 * Outputs extra css for blocks.
	 *
	 * @param $post_object object of WP_Post.
	 */
	public function frontend_build_css( $post_object ) {
		if ( ! is_object( $post_object ) ) {
			return;
		}
		if ( ! method_exists( $post_object, 'post_content' ) ) {
			$blocks = $this->bwf_parse_blocks( $post_object->post_content );
			if ( ! is_array( $blocks ) || empty( $blocks ) ) {
				return;
			}
			$this->compute_bwf_blocks($blocks);

			
		}
	}

	public function compute_bwf_blocks( $blocks ) {
		foreach ( $blocks as $indexkey => $block ) {
			$block = apply_filters( 'bwf_blocks_frontend_build_css', $block );
			if ( ! is_object( $block ) && is_array( $block ) && isset( $block['blockName'] ) ) {
				
				if ( 'bwfblocks/checkout-form' === $block['blockName'] ) {
					if ( isset( $block['attrs'] ) && is_array( $block['attrs'] ) ) {
						$blockattr = $block['attrs'];
						if ( isset( $blockattr['uniqueID'] ) ) {
							$unique_id = $blockattr['uniqueID'];
							$style_id  = 'bwfblocks-' . esc_attr( $unique_id );
							if ( ! wp_style_is( $style_id, 'enqueued' ) ) {
								$css = $this->render_checkout_form_css_head( $blockattr, $unique_id );
								if ( ! empty( $css ) ) {
									$this->render_inline_css( $css, $style_id );
								}
							}
						}
					}
				}

				if ( 'bwfblocks/mini-cart' === $block['blockName'] ) {
					if ( isset( $block['attrs'] ) && is_array( $block['attrs'] ) ) {
						$blockattr = $block['attrs'];
						if ( isset( $blockattr['uniqueID'] ) ) {
							$unique_id = $blockattr['uniqueID'];
							$style_id  = 'bwfblocks-' . esc_attr( $unique_id );
							if ( ! wp_style_is( $style_id, 'enqueued' ) ) {
								$css = $this->render_mini_cart_css_head( $blockattr, $unique_id );
								if ( ! empty( $css ) ) {
									$this->render_inline_css( $css, $style_id );
								}
							}
						}
					}
				}
				
				if ( 'core/block' === $block['blockName'] ) {
					if ( isset( $block['attrs'] ) && is_array( $block['attrs'] ) ) {
						$blockattr = $block['attrs'];
						if ( isset( $blockattr['ref'] ) ) {
							$reusable_block = get_post( $blockattr['ref'] );
							if ( $reusable_block && 'wp_block' == $reusable_block->post_type ) {
								$reuse_data_block = $this->bwf_parse_blocks( $reusable_block->post_content );
								$this->compute_bwf_blocks( $reuse_data_block );
								
								// { make testing reusable block inside itself. }
							}
						}
					}
				}
				if ( isset( $block['innerBlocks'] ) && ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
					$this->compute_bwf_blocks( $block['innerBlocks'] );
				}
			}
		}
	}

	/**
	 * @param mixed $attr 
	 * @param string $indexkey - check whether indexkey is set in $attr[] array or not
	 * @param mixed $default - function return default value which you passed as a 3rd parameter eg. you need 'inherit' value when $indexkey value is true
	 * @return void
	 */
	public function has_attr( $attr, $indexkey, $screen = '', $default_val = null, $misc_val = '' ) {
		$value = null;
		if( empty( $screen ) ) {
			if( isset( $attr[ $indexkey ] ) ) {
				$value = $attr[ $indexkey ];
			}
		} else {
			if( isset( $attr[ $indexkey ] ) && isset( $attr[ $indexkey ][ $screen ] ) ) {
				$value = $attr[ $indexkey ][ $screen ];
			}
		}
		return ! is_null( $default_val ) && ! empty( $value ) ? $default_val : $value;
	}

	public function render_checkout_form_css_head( $attr, $unique_id ) {

		$css = new BWF_Blocks_CSS();
		$media_query                = array();
		$media_query['mobile']      = apply_filters( 'bwf_blocks_mobile_media_query', '(max-width: 767px)' );
		$media_query['tablet']      = apply_filters( 'bwf_blocks_tablet_media_query', '(max-width: 1024px)' );

		$unique_class = '.wfacp-checkout-form-block.wfacp-' . $unique_id ;
		$screens = array( 'desktop', 'tablet', 'mobile' );

		$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce #payment button#place_order, 
		{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-checkout .wfacp-next-btn-wrap button', $unique_class ) );
		$css->add_property( 'font-size', '25px' );

		foreach ($screens as $screen) {
			if( 'desktop' !== $screen ) {
				$css->start_media_query( $media_query[ $screen ] );
			}

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}}', $unique_class ) );
			$css->add_property( 'background', $this->has_attr( $attr, 'background', $screen ) );
			$css->add_property( 'margin', $this->has_attr( $attr, 'margin', $screen ) );
			$css->add_property( 'padding', $this->has_attr( $attr, 'padding', $screen ) );
			$css->add_property( 'border', $this->has_attr( $attr, 'border', $screen ) );
			$css->add_property( 'box-shadow', $this->has_attr( $attr, 'boxShadow', $screen ) );

			
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp-payment-tab-list .wfacp-order2StepHeaderText', $unique_class ) );
			$css->add_property( 'text-align', $this->has_attr( $attr, 'stepAlignment', $screen ) );

			
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_form_steps .wfacp-order2StepTitle.wfacp-order2StepTitleS1', $unique_class ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'stepHeadingLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'stepHeadingLetterSpacing', $screen ), true );
			$css->add_property( 'font', $this->has_attr( $attr, 'stepHeadingFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'stepHeadingTextStyle', $screen ) );

			
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_form_steps .wfacp-order2StepSubTitle.wfacp-order2StepSubTitleS1', $unique_class ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'stepSubHeadingLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'stepSubHeadingLetterSpacing', $screen ), true );
			$css->add_property( 'font', $this->has_attr( $attr, 'stepSubHeadingFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'stepSubHeadingTextStyle', $screen ) );

			
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_steps_sec ul li a ', $unique_class ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'brdHeadingLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'brdHeadingLetterSpacing', $screen ), true );
			$css->add_property( 'font', $this->has_attr( $attr, 'brdHeadingFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'brdHeadingTextStyle', $screen ) );

			
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp-form .wfacp_main_form.woocommerce .wfacp_steps_sec ul li a', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'brdColor', $screen ) ? $this->has_attr( $attr, 'brdColor', $screen ) . ' !important' : '' );

			
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp-form .wfacp_main_form.woocommerce .wfacp_steps_sec ul li a:hover', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'brdColorHover', $screen ) ? $this->has_attr( $attr, 'brdColorHover', $screen ) . ' !important' : '' );

			
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list.wfacp-active', $unique_class ) );
			$css->add_property( 'background-color', $this->has_attr( $attr, 'stepBackground', $screen ) ? $this->has_attr( $attr, 'stepBackground', $screen ) . ' !important' : '' );

			
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list.wfacp-active .wfacp_tcolor', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'stepColor', $screen ) ? $this->has_attr( $attr, 'stepColor', $screen ) . ' !important' : '' );

			
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp-payment-tab-list.wfacp-active', $unique_class ) );
			$css->add_property( 'border-color', $this->has_attr( $attr, 'stepBorderColor', $screen ) ? $this->has_attr( $attr, 'stepBorderColor', $screen ) . ' !important' : '' );

			
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list.wfacp-active .wfacp-order2StepNumber', $unique_class ) );
			$css->add_property( 'background-color', $this->has_attr( $attr, 'stepCountBackground', $screen ) ? $this->has_attr( $attr, 'stepCountBackground', $screen ) . ' !important' : '' );
			$css->add_property( 'color', $this->has_attr( $attr, 'stepCountColor', $screen ) ? $this->has_attr( $attr, 'stepCountColor', $screen ) . ' !important' : '' );
			$css->add_property( 'border-color', $this->has_attr( $attr, 'stepCountBorderColor', $screen ) ? $this->has_attr( $attr, 'stepCountBorderColor', $screen ) . ' !important' : '' );

			
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list', $unique_class ) );
			$css->add_property( 'background-color', $this->has_attr( $attr, 'stepBackgroundInactive', $screen ) ? $this->has_attr( $attr, 'stepBackgroundInactive', $screen ) . ' !important' : '' );

			
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list .wfacp_tcolor', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'stepColorInactive', $screen ) ? $this->has_attr( $attr, 'stepColorInactive', $screen ) . ' !important' : '' );

			
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp-payment-tab-list', $unique_class ) );
			$css->add_property( 'border-color', $this->has_attr( $attr, 'stepBorderColorInactive', $screen ) ? $this->has_attr( $attr, 'stepBorderColorInactive', $screen ) . ' !important' : '' );

			
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list .wfacp-order2StepNumber', $unique_class ) );
			$css->add_property( 'background-color', $this->has_attr( $attr, 'stepCountBackgroundInactive', $screen ) ? $this->has_attr( $attr, 'stepCountBackgroundInactive', $screen ) . ' !important' : '' );
			$css->add_property( 'color', $this->has_attr( $attr, 'stepCountColorInactive', $screen ) ? $this->has_attr( $attr, 'stepCountColorInactive', $screen ) . ' !important' : '' );
			$css->add_property( 'border-color', $this->has_attr( $attr, 'stepCountBorderColorInactive', $screen ) ? $this->has_attr( $attr, 'stepCountBorderColorInactive', $screen ) . ' !important' : '' );

			
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list ', $unique_class ) );
			$css->add_property( 'border', $this->has_attr( $attr, 'stepBorder', $screen ) );

			
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .tab', $unique_class ) );
//			$css->add_property( 'margin-bottom', '15px' );
			$css->add_property( 'margin', $this->has_attr( $attr, 'stepMargin', $screen ) );


			/**Form Heading Styling */
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_section_title', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'headingColor', $screen ) ? $this->has_attr( $attr, 'headingColor', $screen ) . ' !important' : '' );
			$css->add_property( 'font', $this->has_attr( $attr, 'headingFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'headingTextStyle', $screen ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'headingLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'headingLetterSpacing', $screen ), true );
			
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-comm-title h4', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'subHeadingColor', $screen ) ? $this->has_attr( $attr, 'subHeadingColor', $screen ) . ' !important' : '' );
			$css->add_property( 'font', $this->has_attr( $attr, 'subHeadingFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'subHeadingTextStyle', $screen ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'subHeadingLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'subHeadingLetterSpacing', $screen ), true );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-section .wfacp-comm-title', $unique_class ) );
			$css->add_property( 'margin', $this->has_attr( $attr, 'headingMargin', $screen ) );
			$css->add_property( 'padding', $this->has_attr( $attr, 'headingPadding', $screen ) );
			$css->add_property( 'border', $this->has_attr( $attr, 'headingBorder', $screen ) );
			$css->add_property( 'background', $this->has_attr( $attr, 'headingBackground', $screen ) );

			/* Form Field Styling */
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce p.wfacp-form-control-wrapper label.wfacp-form-control-label,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce p.wfacp-form-control-wrapper label.wfacp-form-control-label span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce p.wfacp-form-control-wrapper.wfacp_checkbox_field label,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .create-account label,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .create-account label span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce p.wfacp-form-control-wrapper.wfacp_checkbox_field label span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce p.wfacp-form-control-wrapper.wfacp_custom_field_radio_wrap > label ,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce p.wfacp-form-control-wrapper:not(.wfacp-anim-wrap) label.wfacp-form-control-label abbr,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li p,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li p,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li label,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li .wfacp_shipping_price span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li .wfacp_shipping_price bdi,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li .wfacp_shipping_price,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_subscription_count_wrap p ', $unique_class ) );
			$css->add_property( 'font', $this->has_attr( $attr, 'inpLabelFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'inpLabelTextStyle', $screen ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'inpLabelLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'inpLabelLetterSpacing', $screen ), true );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-form-control-label,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-form-control-label abbr', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'inpLabelColor', $screen ) ? $this->has_attr( $attr, 'inpLabelColor', $screen ) . ' !important' : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce input[type="text"],
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce input[type="email"],
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce input[type="tel"],
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce input[type="password"],
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce input[type="number"],
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce select,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce textarea,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce number,
			{{WRAPPER}} #wfacp-e-form .woocommerce-input-wrapper .wfacp-form-control,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .select2-container .select2-selection--single .select2-selection__rendered,
			body:not(.wfacp_pre_built) .select2-results__option,
			body:not(.wfacp_pre_built) .select2-container--default .select2-search--dropdown .select2-search__field ', $unique_class ) );

			'desktop' == $screen &&	$css->add_property( 'font-size', '14px' );
			'mobile' == $screen &&	$css->add_property( 'font-size', '16px' );

			$css->add_property( 'font', $this->has_attr( $attr, 'inpFieldFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'inpFieldTextStyle', $screen ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'inpFieldLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'inpFieldLetterSpacing', $screen ), true );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-input-wrapper .wfacp-form-control,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .select2-container .select2-selection--single .select2-selection__rendered,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce select', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'inpFieldColor', $screen ) ? $this->has_attr( $attr, 'inpFieldColor', $screen ) . ' !important' : '' );

            $css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-input-wrapper .wfacp-form-control:not(.input-checkbox), {{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .select2-container .select2-selection--single .select2-selection__rendered, {{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce select, {{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-login-wrapper input[type=email], {{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-login-wrapper input[type=number], {{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-login-wrapper input[type=password], {{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-login-wrapper input[type=tel], {{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-login-wrapper select, {{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-login-wrapper input[type=text], {{WRAPPER}} #wfacp-e-form .wfacp-form.wfacp-inside .form-row > label.wfacp-form-control-label:not(.checkbox), {{WRAPPER}} #shipping_country_field label.wfacp-form-control-label ', $unique_class ) );
            $css->add_property( 'background', $this->has_attr( $attr, 'wfacpInputBackgroundColor', $screen ) ? $this->has_attr( $attr, 'wfacpInputBackgroundColor', $screen ) : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce input[type="text"],
			{{WRAPPER}} #wfacp-e-form .wfacp-form .wfacp_main_form.woocommerce input[type="email"],
			{{WRAPPER}} #wfacp-e-form .wfacp-form .wfacp_main_form.woocommerce input[type="tel"],
			{{WRAPPER}} #wfacp-e-form .wfacp-form .wfacp_main_form.woocommerce input[type="password"],
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce select,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce textarea,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .woocommerce-input-wrapper input[type="number"].wfacp-form-control,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .woocommerce-input-wrapper input[type="text"].wfacp-form-control,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .woocommerce-input-wrapper input[type="emal"].wfacp-form-control,
			{{WRAPPER}} #wfacp-e-form .wfacp_allowed_countries strong,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .select2-container .select2-selection--single .select2-selection__rendered', $unique_class ) );
			$css->add_property( 'border', $this->has_attr( $attr, 'inpFieldBorder', $screen ) );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .form-row:not(.woocommerce-invalid-email) .wfacp-form-control:focus', $unique_class ) );
			$css->add_property( 'border-color', $this->has_attr( $attr, 'inpFieldFocusColor', $screen ) ? $this->has_attr( $attr, 'inpFieldFocusColor', $screen ) . ' !important' : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce p.woocommerce-invalid-required-field .wfacp-form-control,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce p.woocommerce-invalid-email .wfacp-form-control,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_coupon_failed .wfacp_coupon_code,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce p.woocommerce-invalid-required-field:not(.wfacp_select2_country_state):not(.wfacp_state_wrap) .woocommerce-input-wrapper .select2-container .select2-selection--single .select2-selection__rendered', $unique_class ) );
			$css->add_property( 'border-color', $this->has_attr( $attr, 'inpFieldErrorColor', $screen ) ? $this->has_attr( $attr, 'inpFieldErrorColor', $screen ) . ' !important' : '' );
			$css->add_property( 'box-shadow', $this->has_attr( $attr, 'inpFieldErrorColor', $screen ) ? '0 0 0 1px ' . $this->has_attr( $attr, 'inpFieldErrorColor', $screen ) . ' !important' : '' );

			/* Form Section Styling */
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp-section ', $unique_class ) );
			$css->add_property( 'background', $this->has_attr( $attr, 'sectionBackground', $screen ) );
			$css->add_property( 'margin', $this->has_attr( $attr, 'sectionMargin', $screen ) );
			$css->add_property( 'padding', $this->has_attr( $attr, 'sectionPadding', $screen ) );
			$css->add_property( 'border', $this->has_attr( $attr, 'sectionBorder', $screen ) );
			$css->add_property( 'box-shadow', $this->has_attr( $attr, 'sectionBoxShadow', $screen ) );


			/* Order Summary Styling */
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody .wfacp_order_summary_item_name,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody .product-name .product-quantity,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody td.product-total,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody .cart_item .product-total span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody .cart_item .product-total span.amount,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody .cart_item .product-total span.amount bdi,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody .cart_item .product-total small,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody .wfacp_order_summary_container dl,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody .wfacp_order_summary_container dd,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody .wfacp_order_summary_container dt,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody .wfacp_order_summary_container p,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody tr span.amount,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody tr span.amount bdi,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody dl,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody dd,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody dt,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody p,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody tr td span:not(.wfacp-pro-count)', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'wfacpOrderSummaryProductColor', $screen ) ? $this->has_attr( $attr, 'wfacpOrderSummaryProductColor', $screen ) . ' !important' : '' );
			$css->add_property( 'font', $this->has_attr( $attr, 'wfacpOrderSummaryProductFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'wfacpOrderSummaryProductTextStyle', $screen ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'wfacpOrderSummaryProductLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'wfacpOrderSummaryProductLetterSpacing', $screen ), true );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form table.shop_table.woocommerce-checkout-review-order-table tr.cart_item .product-image img ', $unique_class ) );
			$css->add_property( 'border-color', $this->has_attr( $attr, 'wfacpOrderSummaryProductImageColor', $screen ) ? $this->has_attr( $attr, 'wfacpOrderSummaryProductImageColor', $screen ) . ' !important' : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.cart-subtotal th,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot .shipping_total_fee td,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.cart-subtotal td,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.cart-subtotal td span.woocommerce-Price-amount.amount,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.cart-subtotal td span.woocommerce-Price-amount.amount bdi,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.cart-subtotal td p,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.cart-subtotal td span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.shipping_total_fee td span.amount,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.shipping_total_fee td span.amount bdi,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.shipping_total_fee td span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.cart-discount td,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.cart-discount th,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.cart-discount td span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.cart-discount td span.amount,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.cart-discount td span.amount bdi,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.cart-discount td p,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total),
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) td,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) td span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) td small,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) td a,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) td p,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) th,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) th span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) th small,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) th a,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) ul,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) ul li,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) ul li label,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) td span.woocommerce-Price-amount.amount,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) td span.woocommerce-Price-amount.amount bdi', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'wfacpOrderSummaryProductMetaColor', $screen ) ? $this->has_attr( $attr, 'wfacpOrderSummaryProductMetaColor', $screen ) . ' !important' : '' );
			$css->add_property( 'font', $this->has_attr( $attr, 'wfacpOrderSummaryProductMetaFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'wfacpOrderSummaryProductMetaTextStyle', $screen ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'wfacpOrderSummaryProductMetaLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'wfacpOrderSummaryProductMetaLetterSpacing', $screen ), true );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total th,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total td,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total td span.woocommerce-Price-amount.amount,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total td span.woocommerce-Price-amount.amount bdi,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total td p,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total td span,
	
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total td,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total td span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total td small,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total td a,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total td p,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total th,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total th span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total th small,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total th a ', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'wfacpOrderSummaryTotalColor', $screen ) ? $this->has_attr( $attr, 'wfacpOrderSummaryTotalColor', $screen ) . ' !important' : '' );
			$css->add_property( 'font', $this->has_attr( $attr, 'wfacpOrderSummaryTotalFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'wfacpOrderSummaryTotalTextStyle', $screen ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'wfacpOrderSummaryTotalLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'wfacpOrderSummaryTotalLetterSpacing', $screen ), true );


			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody .wfacp_order_summary_item_name,
			{{WRAPPER}} #wfacp-e-form table.shop_table.woocommerce-checkout-review-order-table tr.cart_item,
			{{WRAPPER}} #wfacp-e-form table.shop_table.woocommerce-checkout-review-order-table tr.cart-subtotal,
			{{WRAPPER}} #wfacp-e-form table.shop_table.woocommerce-checkout-review-order-table tr.order-total', $unique_class ) );
			$css->add_property( 'border-color', $this->has_attr( $attr, 'wfacpOrderSummaryDividerColor', $screen ) ? $this->has_attr( $attr, 'wfacpOrderSummaryDividerColor', $screen ) . ' !important' : '' );

			/* Order Total Styling */
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_order_total_field table tr td,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_order_total_field table tr th,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_order_total_field table tr th *,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_order_total_field table tr td *,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_order_total_field table.wfacp_order_total_wrap tr td,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_order_total_field table.wfacp_order_total_wrap tr td strong > span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_order_total_field table.wfacp_order_total_wrap tr td strong > span span.woocommerce-Price-currencySymbol', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'wfacpOrderTotalColor', $screen ) ? $this->has_attr( $attr, 'wfacpOrderTotalColor', $screen ) . ' !important' : '' );
			$css->add_property( 'font', $this->has_attr( $attr, 'wfacpOrderTotalFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'wfacpOrderTotalTextStyle', $screen ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'wfacpOrderTotalLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'wfacpOrderTotalLetterSpacing', $screen ), true );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_order_total .wfacp_order_total_wrap', $unique_class ) );
			$css->add_property( 'background', $this->has_attr( $attr, 'wfacpOrderTotalBackground', $screen ) );
			$css->add_property( 'border', $this->has_attr( $attr, 'wfacpOrderTotalBorder', $screen ) );

			/* Coupon Styling */
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-coupon-section .wfacp-coupon-page .woocommerce-info > span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_woocommerce_form_coupon .wfacp-coupon-section .woocommerce-info .wfacp_showcoupon', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'wfacpCouponLinkColor', $screen ) ? $this->has_attr( $attr, 'wfacpCouponLinkColor', $screen ) . ' !important' : '' );
			$css->add_property( 'font', $this->has_attr( $attr, 'wfacpCouponLinkFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'wfacpCouponLinkTextStyle', $screen ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'wfacpCouponLinkLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'wfacpCouponLinkLetterSpacing', $screen ), true );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}}  #wfacp-e-form .wfacp_main_form .wfacp_coupon_field_box p.wfacp-form-control-wrapper label.wfacp-form-control-label,
			{{WRAPPER}}  #wfacp-e-form .wfacp_main_form .wfacp_coupon_field_box p.wfacp-form-control-wrapper.wfacp-anim-wrap label.wfacp-form-control-label', $unique_class ) );
			$css->add_property( 'font', $this->has_attr( $attr, 'wfacpCouponLabelFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'wfacpCouponLabelTextStyle', $screen ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'wfacpCouponLabelLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'wfacpCouponLabelLetterSpacing', $screen ), true );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}}  #wfacp-e-form .wfacp_main_form .wfacp_coupon_field_box p.wfacp-form-control-wrapper label.wfacp-form-control-label ', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'wfacpCouponLabelColor', $screen ) ? $this->has_attr( $attr, 'wfacpCouponLabelColor', $screen ) . ' !important' : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}}  #wfacp-e-form .wfacp_main_form .wfacp_coupon_field_box p.wfacp-form-control-wrapper .wfacp-form-control', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'wfacpCouponFieldColor', $screen ) ? $this->has_attr( $attr, 'wfacpCouponFieldColor', $screen ) . ' !important' : '' );
			$css->add_property( 'font', $this->has_attr( $attr, 'wfacpCouponFieldFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'wfacpCouponFieldTextStyle', $screen ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'wfacpCouponFieldLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'wfacpCouponFieldLetterSpacing', $screen ), true );
			$css->add_property( 'border', $this->has_attr( $attr, 'wfacpCouponFieldBorder', $screen ) );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_coupon_field_box p.wfacp-form-control-wrapper .wfacp-form-control:focus', $unique_class ) );
			$css->add_property( 'border-color', $this->has_attr( $attr, 'wfacpCouponFieldColorFocus', $screen ) ? $this->has_attr( $attr, 'wfacpCouponFieldColorFocus', $screen ) . ' !important' : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_woocommerce_form_coupon .wfacp-coupon-section .wfacp_coupon_field_box .wfacp-coupon-field-btn', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'wfacpCouponBtnColor', $screen ) ? $this->has_attr( $attr, 'wfacpCouponBtnColor', $screen ) . ' !important' : '' );
			$css->add_property( 'background-color', $this->has_attr( $attr, 'wfacpCouponBtnBackground', $screen ) ? $this->has_attr( $attr, 'wfacpCouponBtnBackground', $screen ) . ' !important' : '' );
			$css->add_property( 'font', $this->has_attr( $attr, 'wfacpCouponBtnFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'wfacpCouponBtnTextStyle', $screen ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'wfacpCouponBtnLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'wfacpCouponBtnLetterSpacing', $screen ), true );


			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_woocommerce_form_coupon .wfacp-coupon-section .wfacp_coupon_field_box .wfacp-coupon-field-btn:hover', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'wfacpCouponBtnColorHover', $screen ) ? $this->has_attr( $attr, 'wfacpCouponBtnColorHover', $screen ) . ' !important' : '' );
			$css->add_property( 'background-color', $this->has_attr( $attr, 'wfacpCouponBtnBackgroundHover', $screen ) ? $this->has_attr( $attr, 'wfacpCouponBtnBackgroundHover', $screen ) . ' !important' : '' );
			$css->add_property( 'font', $this->has_attr( $attr, 'wfacpCouponBtnFontHover', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'wfacpCouponBtnTextStyleHover', $screen ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'wfacpCouponBtnLineHeightHover', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'wfacpCouponBtnLetterSpacingHover', $screen ), true );

			/* Product Switching Styling */
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_sec .wfacp_product_name_inner *,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_sec .wfacp_product_attributes .wfacp_selected_attributes  *,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_quantity_selector input,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_price_sec span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_price_sec span bdi,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_switcher_col_2 .wfacp_product_subs_details > span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_subs_details span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_subs_details *,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_sec .wfacp_product_select_options .wfacp_qv-button', $unique_class ) );
			$css->add_property( 'font', $this->has_attr( $attr, 'wfacpSelectedItemFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'wfacpSelectedItemTextStyle', $screen ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'wfacpSelectedItemLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'wfacpSelectedItemLetterSpacing', $screen ), true );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-cart-form__cart-item.cart_item.wfacp-selected-product .wfacp_row_wrap .wfacp_product_choosen_label .wfacp_product_switcher_item,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-cart-form__cart-item.cart_item.wfacp-selected-product .wfacp_row_wrap .wfacp_product_choosen_label .wfacp_product_row_quantity', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'wfacpSelectedItemLabelColor', $screen ) ? $this->has_attr( $attr, 'wfacpSelectedItemLabelColor', $screen ) . ' !important' : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .shop_table.wfacp-product-switch-panel .wfacp-selected-product .product-price,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .shop_table.wfacp-product-switch-panel .wfacp-selected-product .product-price span', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'wfacpSelectedItemPriceColor', $screen ) ? $this->has_attr( $attr, 'wfacpSelectedItemPriceColor', $screen ) . ' !important' : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_selected_attributes .wfacp_pro_attr_single span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_selected_attributes .wfacp_pro_attr_single span:last-child,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce #product_switching_field .wfacp_product_switcher_col_2 .wfacp_product_subs_details,
			{{WRAPPER}}  #wfacp-e-form .wfacp_main_form.woocommerce #product_switching_field .wfacp_product_switcher_col_2 .wfacp_product_subs_details span', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'wfacpSelectedItemVariantColor', $screen ) ? $this->has_attr( $attr, 'wfacpSelectedItemVariantColor', $screen ) . ' !important' : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-cart-form__cart-item.cart_item.wfacp-selected-product', $unique_class ) );
			$css->add_property( 'background-color', $this->has_attr( $attr, 'wfacpSelectedItemBackground', $screen ) ? $this->has_attr( $attr, 'wfacpSelectedItemBackground', $screen ) . ' !important' : '' );
			$css->add_property( 'border', $this->has_attr( $attr, 'wfacpSelectedItemBorder', $screen ) );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_sec .wfacp_product_name_inner *,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_sec .wfacp_product_attributes .wfacp_selected_attributes  *,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_quantity_selector input,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_price_sec span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_price_sec span bdi,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_switcher_col_2 .wfacp_product_subs_details > span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_subs_details span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_subs_details *,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_sec .wfacp_product_select_options .wfacp_qv-button', $unique_class ) );
			$css->add_property( 'font', $this->has_attr( $attr, 'wfacpOptionalItemFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'wfacpOptionalItemTextStyle', $screen ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'wfacpOptionalItemLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'wfacpOptionalItemLetterSpacing', $screen ), true );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-cart-form__cart-item.cart_item .wfacp_row_wrap .wfacp_product_choosen_label .wfacp_product_switcher_item,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-cart-form__cart-item.cart_item .wfacp_row_wrap .wfacp_product_choosen_label .wfacp_product_row_quantity', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'wfacpOptionalItemLabelColor', $screen ) ? $this->has_attr( $attr, 'wfacpOptionalItemLabelColor', $screen ) . ' !important' : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .shop_table.wfacp-product-switch-panel .product-price,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .shop_table.wfacp-product-switch-panel .wfacp_product_price_sec span', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'wfacpOptionalItemPriceColor', $screen ) ? $this->has_attr( $attr, 'wfacpOptionalItemPriceColor', $screen ) . ' !important' : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} .woocommerce-cart-form__cart-item.cart_item:not(.wfacp-selected-product)', $unique_class ) );
			$css->add_property( 'background-color', $this->has_attr( $attr, 'wfacpOptionalItemBackground', $screen ) ? $this->has_attr( $attr, 'wfacpOptionalItemBackground', $screen ) . ' !important' : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} .wfacp-product-switch-panel .woocommerce-cart-form__cart-item.cart_item:not(.wfacp-selected-product):hover', $unique_class ) );
			$css->add_property( 'background-color', $this->has_attr( $attr, 'wfacpOptionalItemBackgroundHover', $screen ) ? $this->has_attr( $attr, 'wfacpOptionalItemBackgroundHover', $screen ) . ' !important' : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-cart-form__cart-item.cart_item:not(.wfacp-selected-product)', $unique_class ) );
			$css->add_property( 'border', $this->has_attr( $attr, 'wfacpOptionalItemBorder', $screen ) );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset .wfacp_you_save_text,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce #wfacp-e-form .wfacp_main_form .wfacp_row_wrap .wfacp_you_save_text span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_row_wrap .wfacp_you_save_text span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form #product_switching_field .wfacp_product_switcher_col_2 .wfacp_product_subs_details > span:not(.subscription-details):not(.woocommerce-Price-amount):not(.woocommerce-Price-currencySymbol),
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form #product_switching_field .wfacp_product_switcher_col_2 .wfacp_product_subs_details lebel,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form #product_switching_field .wfacp_product_switcher_col_2 .wfacp_product_subs_details span:not(.subscription-details):not(.woocommerce-Price-amount):not(.woocommerce-Price-currencySymbol)', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'wfacpSaveColor', $screen ) ? $this->has_attr( $attr, 'wfacpSaveColor', $screen ) . ' !important' : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset .wfacp_you_save_text,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_row_wrap .wfacp_you_save_text span', $unique_class ) );
			$css->add_property( 'font', $this->has_attr( $attr, 'wfacpSaveFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'wfacpSaveTextStyle', $screen ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'wfacpSaveLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'wfacpSaveLetterSpacing', $screen ), true );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce #product_switching_field fieldset .wfacp_best_value_container .wfacp_best_value,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_best_value.wfacp_top_left_corner,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_best_value.wfacp_top_right_corner', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'wfacpBestValueColor', $screen ) ? $this->has_attr( $attr, 'wfacpBestValueColor', $screen ) . ' !important' : '' );
			$css->add_property( 'background-color', $this->has_attr( $attr, 'wfacpBestValueBackground', $screen ) ? $this->has_attr( $attr, 'wfacpBestValueBackground', $screen ) . ' !important' : '' );
			$css->add_property( 'border-color', $this->has_attr( $attr, 'wfacpBestValueBorderColor', $screen ) ? $this->has_attr( $attr, 'wfacpBestValueBorderColor', $screen ) . ' !important' : '' );
			$css->add_property( 'border', $this->has_attr( $attr, 'wfacpBestValueBorder', $screen ) );


			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce #product_switching_field fieldset .wfacp_best_value', $unique_class ) );
			$css->add_property( 'font', $this->has_attr( $attr, 'wfacpBestValueFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'wfacpBestValueTextStyle', $screen ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'wfacpBestValueLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'wfacpBestValueLetterSpacing', $screen ), true );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_whats_included h3', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'whatIncludeHeadingColor', $screen ) ? $this->has_attr( $attr, 'whatIncludeHeadingColor', $screen ) . ' !important' : '' );
			$css->add_property( 'font', $this->has_attr( $attr, 'whatIncludeHeadingFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'whatIncludeHeadingTextStyle', $screen ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'whatIncludeHeadingLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'whatIncludeHeadingLetterSpacing', $screen ), true );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}}  #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_whats_included .wfacp_product_switcher_description h4', 
			$unique_class ) );
			$css->add_property( 'font', $this->has_attr( $attr, 'whatIncludeTitleFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'whatIncludeTitleTextStyle', $screen ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'whatIncludeTitleLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'whatIncludeTitleLetterSpacing', $screen ), true );


			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_whats_included .wfacp_product_switcher_description h4', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'whatIncludeTitle', $screen ) ? $this->has_attr( $attr, 'whatIncludeTitle', $screen ) . ' !important' : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}}  #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_whats_included .wfacp_product_switcher_description .wfacp_description p,
			{{WRAPPER}}  #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_whats_included .wfacp_product_switcher_description .wfacp_description a,
			{{WRAPPER}}  #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_whats_included .wfacp_product_switcher_description .wfacp_description', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'whatIncludeDescColor', $screen ) ? $this->has_attr( $attr, 'whatIncludeDescColor', $screen ) . ' !important' : '' );
			$css->add_property( 'font', $this->has_attr( $attr, 'whatIncludeDescFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'whatIncludeDescTextStyle', $screen ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'whatIncludeDescLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'whatIncludeDescLetterSpacing', $screen ), true );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_whats_included', $unique_class ) );
			$css->add_property( 'background', $this->has_attr( $attr, 'whatIncludeBackground', $screen ) );
			$css->add_property( 'border', $this->has_attr( $attr, 'whatIncludeBorder', $screen ) );

			/* Button Styling */
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-next-btn-wrap button,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce #payment button#place_order', $unique_class ) );
			$css->add_property( 'width', $this->has_attr( $attr, 'buttonWidth', $screen ), true );
			$css->add_property( 'border', $this->has_attr( $attr, 'buttonBorder', $screen ) );
			$css->add_property( 'margin', $this->has_attr( $attr, 'buttonMargin', $screen ) );
			$css->add_property( 'padding', $this->has_attr( $attr, 'buttonPadding', $screen ) );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-checkout .wfacp-order-place-btn-wrap,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-checkout .wfacp-next-btn-wrap', $unique_class ) );
			$css->add_property( 'text-align', $this->has_attr( $attr, 'buttonTextStyle', $screen ) ? $this->has_attr( $attr, 'buttonTextStyle', $screen )['align'] : '' );
		

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce #payment button#place_order,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-checkout button.button.button-primary.wfacp_next_page_button', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'buttonColor', $screen ) ? $this->has_attr( $attr, 'buttonColor', $screen ) . ' !important' : '' );
			$css->add_property( 'font', $this->has_attr( $attr, 'buttonFont', $screen ) );
			$css->add_typograpghy_property( 'text', $this->has_attr( $attr, 'buttonTextStyle', $screen ), ['align'] );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'buttonLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'buttonLetterSpacing', $screen ), true );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-next-btn-wrap button,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce #payment button#place_order,
			#wfacp_qr_model_wrap .wfacp_qr_wrap .wfacp_qv-summary .button', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'buttonColor', $screen ) ? $this->has_attr( $attr, 'buttonColor', $screen ) . ' !important' : '' );
			$css->add_property( 'background-color', $this->has_attr( $attr, 'buttonBackground', $screen ) ? $this->has_attr( $attr, 'buttonBackground', $screen ) . ' !important' : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-next-btn-wrap button:hover,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce #payment button#place_order:hover,
			#wfacp_qr_model_wrap .wfacp_qr_wrap .wfacp_qv-summary .button:hover', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'buttonColorHover', $screen ) ? $this->has_attr( $attr, 'buttonColorHover', $screen ) . ' !important' : '' );
			$css->add_property( 'background-color', $this->has_attr( $attr, 'buttonBackgroundHover', $screen ) ? $this->has_attr( $attr, 'buttonBackgroundHover', $screen ) . ' !important' : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-back-btn-wrap a,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .btm_btn_sec.wfacp_back_cart_link .wfacp-back-btn-wrap a,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-back-btn-wrap a.wfacp_back_page_button,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce form.woocommerce-checkout .place_order_back_btn a', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'returnLinkColor', $screen ) ? $this->has_attr( $attr, 'returnLinkColor', $screen ) . ' !important' : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-back-btn-wrap a:hover,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .btm_btn_sec.wfacp_back_cart_link .wfacp-back-btn-wrap a:hover,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-back-btn-wrap a.wfacp_back_page_button:hover,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-checkout .place_order_back_btn a:hover', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'returnLinkColorHover', $screen ) ? $this->has_attr( $attr, 'returnLinkColorHover', $screen ) . ' !important' : '' );
			

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-payment-dec', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'addtionalTextColor', $screen ) ? $this->has_attr( $attr, 'addtionalTextColor', $screen ) . ' !important' : '' );
			$css->add_property( 'background-color', $this->has_attr( $attr, 'addtionalTextBackground', $screen ) ? $this->has_attr( $attr, 'addtionalTextBackground', $screen ) . ' !important' : '' );

			/* Payment Gateways Styling*/
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment p,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment p span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment p a,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment label,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment ul,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment ul li,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment ul li input,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment #add_payment_method #payment div.payment_box,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment #add_payment_method #payment .payment_box p', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'paymentMethodColor', $screen ) ? $this->has_attr( $attr, 'paymentMethodColor', $screen ) . ' !important' : '' );
			$css->add_property( 'font', $this->has_attr( $attr, 'paymentMethodFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'paymentMethodTextStyle', $screen ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'paymentMethodLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'paymentMethodLetterSpacing', $screen ), true );

			/* Global Styling */
			$css->set_selector( $this->add_wrapper( 'body:not(.wfacpef_page) {{WRAPPER}} #wfacp-e-form .wfacp-form', $unique_class ) );
			$css->add_property( 'padding', $this->has_attr( $attr, 'formPadding', $screen ) );


			$css->set_selector( $this->add_wrapper( 'body.wfacp_main_wrapper,
			body #wfacp-e-form *:not(i),
			body .wfacp_qv-main *,
			{{WRAPPER}} #wfacp-e-form .wfacp_section_heading.wfacp_section_title,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_whats_included h3,
			{{WRAPPER}}  #wfacp-e-form .wfacp_main_form .wfacp_whats_included .wfacp_product_switcher_description .wfacp_description a,
			{{WRAPPER}}  #wfacp-e-form .wfacp_main_form .wfacp_whats_included .wfacp_product_switcher_description .wfacp_description,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-section h4,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form p.wfacp-form-control-wrapper label.wfacp-form-control-label,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form input[type="text"],
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form input[type="email"],
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form input[type="tel"],
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form input[type="number"],
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form select,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form textarea,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form p,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form a,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form label span a,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form a,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form button,
			{{WRAPPER}} #wfacp-e-form #payment button#place_order,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form label,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form ul li,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form ul li span,
			{{WRAPPER}} #wfacp-e-form .woocommerce-form-login-toggle .woocommerce-info ,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form ul li span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-payment-dec,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form label.checkbox,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-title > div,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_table ul li label,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_checkbox_field label,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .select2-container .select2-selection--single .select2-selection__rendered,
			{{WRAPPER}} #et-boc .et-l span.select2-selection.select2-selection--multiple,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_sec *,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_quantity_selector input,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_price_sec span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset .wfacp_product_sec *,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset .wfacp_quantity_selector input,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset .wfacp_product_price_sec span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form #product_switching_field fieldset .wfacp_best_value,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel .wfacp_product_switcher_col_2 .wfacp_you_save_text,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_whats_included .wfacp_product_switcher_description h4,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset .wfacp_product_sec .wfacp_product_select_options .wfacp_qv-button,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form #product_switching_field .wfacp_product_switcher_col_2 .wfacp_product_subs_details > span:not(.subscription-details):not(.woocommerce-Price-amount):not(.woocommerce-Price-currencySymbol),
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-coupon-section .wfacp-coupon-page .woocommerce-info > span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_woocommerce_form_coupon .wfacp-coupon-section .woocommerce-info .wfacp_showcoupon,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form label.woocommerce-form__label span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr th,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot .shipping_total_fee td,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr td,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr td span.woocommerce-Price-amount.amount,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr td span.woocommerce-Price-amount.amount bdi,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr td p,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset .wfacp_best_value,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody .wfacp_order_summary_item_name,
	
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) td small,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) th small,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total td small,
	
	
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody .wfacp_order_summary_item_name,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody .product-name .product-quantity,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody td.product-total,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody .wfacp_order_summary_container dl,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody .wfacp_order_summary_container dd,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody .wfacp_order_summary_container dt,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody .wfacp_order_summary_container p,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody tr span.amount,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody tr span.amount bdi,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody .wfacp_order_summary_item_name,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody .cart_item .product-total span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody .cart_item .product-total small,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody .cart_item .product-total span.amount,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody .cart_item .product-total span.amount bdi,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody .product-name .product-quantity,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody td.product-total,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table tbody dl,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table tbody dd,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table tbody dt,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table tbody p,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody tr span.amount,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody tr span.amount bdi,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset .wfacp_product_sec .wfacp_product_select_options .wfacp_qv-button,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form #product_switching_field .wfacp_product_switcher_col_2 .wfacp_product_subs_details > span:not(.subscription-details):not(.woocommerce-Price-amount):not(.woocommerce-Price-currencySymbol),
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset .wfacp_you_save_text,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_row_wrap .wfacp_you_save_text span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_whats_included .wfacp_product_switcher_description .wfacp_description p,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_coupon_field_msg,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-coupon-page .wfacp_coupon_remove_msg,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-coupon-page .wfacp_coupon_error_msg,
			body:not(.wfacp_pre_built) .select2-results__option,
			body:not(.wfacp_pre_built) .select2-container--default .select2-search--dropdown .select2-search__field,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_order_total_field table.wfacp_order_total_wrap tr td,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_order_total_field table.wfacp_order_total_wrap tr td span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_order_total .wfacp_order_total_wrap,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form #payment button#place_order,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .woocommerce-checkout button.button.button-primary.wfacp_next_page_button,
			{{WRAPPER}} #wfacp-e-form .wfacp-order2StepTitle.wfacp-order2StepTitleS1,
			{{WRAPPER}} #wfacp-e-form .wfacp-order2StepSubTitle.wfacp-order2StepSubTitleS1,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_steps_sec ul li a,
			{{WRAPPER}} #wfacp-e-form .wfacp_custom_breadcrumb ul li a,
			{{WRAPPER}}  #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr td span ,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form p.wfacp-form-control-wrapper:not(.wfacp-anim-wrap) label.wfacp-form-control-label abbr,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset .wfacp_you_save_text,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_row_wrap .wfacp_you_save_text span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_row_wrap .wfacp_product_subs_details span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form p.wfacp-form-control-wrapper.wfacp_checkbox_field label,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .create-account label,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .create-account label span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form p.wfacp-form-control-wrapper.wfacp_checkbox_field label span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form p.wfacp-form-control-wrapper.wfacp_custom_field_radio_wrap > label ,
	
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) ul,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) ul li,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) ul li label,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) td span.woocommerce-Price-amount.amount,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) td span.woocommerce-Price-amount.amount bdi,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_sec .wfacp_product_name_inner *,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_sec .wfacp_product_attributes .wfacp_selected_attributes  *,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_quantity_selector input,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_price_sec span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_switcher_col_2 .wfacp_product_subs_details > span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_subs_details span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_subs_details *,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_sec .wfacp_product_select_options .wfacp_qv-button,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_sec .wfacp_product_name_inner *,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_sec .wfacp_product_attributes .wfacp_selected_attributes  *,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_quantity_selector input,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_price_sec span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_switcher_col_2 .wfacp_product_subs_details > span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_subs_details span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_subs_details *,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_sec .wfacp_product_select_options .wfacp_qv-button,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_woocommerce_form_coupon .wfacp-coupon-section .wfacp-coupon-field-btn,
			{{WRAPPER}} #wfacp-e-form .wfacp_mb_mini_cart_sec_accordion_content form.checkout_coupon button.button.wfacp-coupon-btn,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_options,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_options ul li,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_options ul li p,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_options ul li .wfacp_shipping_price span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_options ul li .wfacp_shipping_price,
	
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_payment,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_payment p,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_payment p span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_payment p a,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_payment label,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_payment ul,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_payment ul li,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_payment ul li input,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_payment #add_payment_method #payment div.payment_box,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_payment #add_payment_method #payment .payment_box p', $unique_class ) );
			$css->add_property( 'font', $this->has_attr( $attr, 'formFont', $screen ) );


			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form .woocommerce-form-login-toggle .woocommerce-info,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form form.woocommerce-form.woocommerce-form-login.login p,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form label.woocommerce-form__label span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_checkbox_field label,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_checkbox_field span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_options label,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form ul li span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_table tr.shipping td p,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-title div,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .woocommerce-privacy-policy-text p,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_options ul li p,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .shop_table .wfacp-product-switch-title div,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .woocommerce-info .message-container,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form #wc_checkout_add_ons .description,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form ol li,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form ul li,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_table ul#shipping_method label,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_table ul#shipping_method span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .woocommerce-checkout-review-order h3,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .aw_addon_wrap label,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form p:not(.woocommerce-shipping-contents):not(.wfacp_dummy_preview_heading ),
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form label:not(.wfacp-form-control-label):not(.wfob_title):not(.wfob_span),
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form label:not(.wfob_title) span:not(.optional),
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .woocommerce-message,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .woocommerce-error,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_table ul li label,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_table ul li label span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_table ul li span,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr.recurring-totals > th,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form p.wfacp-form-control-wrapper:not(.wfacp-anim-wrap) label.wfacp-form-control-label abbr', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'formContentColor', $screen ) ? $this->has_attr( $attr, 'formContentColor', $screen ) . ' !important' : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-form-login-toggle .woocommerce-info a,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce a:not(.wfacp_close_icon):not(.button-social-login):not(.wfob_btn_add):not(.ywcmas_shipping_address_button_new):not(.wfob_qv-button):not(.wfob_read_more_link):not(.wfacp_step_text_have ),
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce a span:not(.wfob_btn_text_added):not(.wfob_btn_text_remove),
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce label a,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce ul li a:not(.wfacp_breadcrumb_link),
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table tr td a,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce a.wfacp_remove_coupon', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'formLinkColor', $screen ) ? $this->has_attr( $attr, 'formLinkColor', $screen ) . ' !important' : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-form-login-toggle .woocommerce-info a:hover,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce a:not(.wfacp_close_icon):not(.button-social-login):hover:not(.wfob_btn_add):hover:not(.ywcmas_shipping_address_button_new):hover,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce a span:not(.wfob_btn_text_added):not(.wfob_btn_text_remove):hover,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce label a:hover,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce ul li a:not(.wfacp_breadcrumb_link):hover,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table tr td a:hover,
			{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce a.wfacp_remove_coupon:hover', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'formLinkColorHover', $screen ) ? $this->has_attr( $attr, 'formLinkColorHover', $screen ) . ' !important' : '' );

			/* collapsible_order_summary styling */
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_mb_mini_cart_sec_accordion_content', $unique_class ) );
			$css->add_property( 'background-color', $this->has_attr( $attr, 'expandBackground', $screen ) ? $this->has_attr( $attr, 'expandBackground', $screen ) . ' !important' : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_mb_mini_cart_wrap .wfacp_mb_cart_accordian', $unique_class ) );
			$css->add_property( 'background-color', $this->has_attr( $attr, 'collapseBackground', $screen ) ? $this->has_attr( $attr, 'collapseBackground', $screen ) . ' !important' : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_mb_mini_cart_wrap .wfacp_mb_cart_accordian, {{WRAPPER}} #wfacp-e-form .wfacp_mb_mini_cart_wrap .wfacp_mb_mini_cart_sec_accordion_content', $unique_class ) );
			$css->add_property( 'border', $this->has_attr( $attr, 'collapseBorder', $screen ) );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_show_icon_wrap a span,
			{{WRAPPER}} #wfacp-e-form .wfacp_show_price_wrap span', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'collapseColor', $screen ) ? $this->has_attr( $attr, 'collapseColor', $screen ) . ' !important' : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} #wfacp-e-form .wfacp_collapsible_order_summary_wrap', $unique_class ) );
//			$css->add_property( 'margin-bottom', '15px' );
			$css->add_property( 'margin', $this->has_attr( $attr, 'collapseMargin', $screen ) );






			if( 'desktop' !== $screen ) {
				$css->stop_media_query();
			}
		}
		return $css->css_output();
	}

	public function add_wrapper( $string, $replace_selector ) {
		return preg_replace( '/{{WRAPPER}}/i', $replace_selector, $string );
	}


	public function render_mini_cart_css_head( $attr, $unique_id ) {

		$css = new BWF_Blocks_CSS();
		$media_query                = array();
		$media_query['mobile']      = apply_filters( 'bwf_blocks_mobile_media_query', '(max-width: 767px)' );
		$media_query['tablet']      = apply_filters( 'bwf_blocks_tablet_media_query', '(max-width: 1024px)' );

		$unique_class = '.wfacp-mini-cart-block.wfacp-' . $unique_id;
		$screens = array( 'desktop', 'tablet', 'mobile' );

		foreach ($screens as $screen) {
			if( 'desktop' !== $screen ) {
				$css->start_media_query( $media_query[ $screen ] );
			}
			
			$css->set_selector( $this->add_wrapper( '{{WRAPPER}}', $unique_class ) );
			$css->add_property( 'background', $this->has_attr( $attr, 'background', $screen ) );
			$css->add_property( 'margin', $this->has_attr( $attr, 'margin', $screen ) );
			$css->add_property( 'padding', $this->has_attr( $attr, 'padding', $screen ) );
			$css->add_property( 'border', $this->has_attr( $attr, 'border', $screen ) );
			$css->add_property( 'box-shadow', $this->has_attr( $attr, 'boxShadow', $screen ) );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp-order-summary-label', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'headingColor', $screen ) ? $this->has_attr( $attr, 'headingColor', $screen ) . ' !important' : '' );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'headingLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'headingLetterSpacing', $screen ), true );
			$css->add_property( 'font', $this->has_attr( $attr, 'headingFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'headingTextStyle', $screen ) );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container table.wfacp_mini_cart_items,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container table.wfacp_mini_cart_items .product-total,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container table.wfacp_mini_cart_items .product-total span,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container table.wfacp_mini_cart_items .product-total bdi,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container table.wfacp_mini_cart_items .product-total small,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container table.wfacp_mini_cart_items dl,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container table.wfacp_mini_cart_items dt,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container table.wfacp_mini_cart_items dd,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container table.wfacp_mini_cart_items dd p,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container tr.cart_item td .product-name,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container tr.cart_item td,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container tr.cart_item td small,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container tr.cart_item td p,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container tr.cart_item td .product-name span,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container tr.cart_item td .product-name', $unique_class ) );

			$css->add_property( 'color', $this->has_attr( $attr, 'productColor', $screen ) ? $this->has_attr( $attr, 'productColor', $screen ) . ' !important' : '' );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'productLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'productLetterSpacing', $screen ), true );
			$css->add_property( 'font', $this->has_attr( $attr, 'productFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'productTextStyle', $screen ) );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_sum .product-image', $unique_class ) );
			$css->add_property( 'border-color', $this->has_attr( $attr, 'productImageBorderColor', $screen ) ? $this->has_attr( $attr, 'productImageBorderColor', $screen ) . ' !important' : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container table.wfacp_mini_cart_reviews tr:not(.order-total),
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container table.wfacp_mini_cart_reviews tr:not(.order-total) td,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container table.wfacp_mini_cart_reviews tr:not(.order-total) th,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container table.wfacp_mini_cart_reviews tr:not(.order-total) td span,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container table.wfacp_mini_cart_reviews tr:not(.order-total) td bdi,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container table.wfacp_mini_cart_reviews tr:not(.order-total) td small,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container table.wfacp_mini_cart_reviews tr:not(.order-total) td a,
			{{WRAPPER}} .wfacp_mini_cart_start_h span.wfacp_coupon_code', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'subTotalColor', $screen ) ? $this->has_attr( $attr, 'subTotalColor', $screen ) . ' !important' : '' );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'subTotalLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'subTotalLetterSpacing', $screen ), true );
			$css->add_property( 'font', $this->has_attr( $attr, 'subTotalFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'subTotalTextStyle', $screen ) );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container table.wfacp_mini_cart_reviews tr.order-total td span.woocommerce-Price-amount.amount,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container table.wfacp_mini_cart_reviews tr.order-total td span.woocommerce-Price-amount.amount bdi,
			{{WRAPPER}} .wfacp_mini_cart_start_h table.shop_table tr.order-total td,
			{{WRAPPER}} .wfacp_mini_cart_start_h table.shop_table tr.order-total th,
			{{WRAPPER}} .wfacp_mini_cart_start_h table.shop_table tr.order-total td span,
			{{WRAPPER}} .wfacp_mini_cart_start_h table.shop_table tr.order-total td small', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'totalColor', $screen ) ? $this->has_attr( $attr, 'totalColor', $screen ) . ' !important' : '' );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'totalLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'totalLetterSpacing', $screen ), true );
			$css->add_property( 'font', $this->has_attr( $attr, 'totalFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'totalTextStyle', $screen ) );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_mini_cart_elementor .cart_item,
			{{WRAPPER}} .wfacp_mini_cart_start_h table.shop_table tr.cart-subtotal,
			{{WRAPPER}} .wfacp_mini_cart_start_h table.shop_table tr.order-total,
			{{WRAPPER}} .wfacp_mini_cart_start_h table.shop_table tr.wfacp_ps_error_state td,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp-coupon-section .wfacp-coupon-page', $unique_class ) );
			$css->add_property( 'border-color', $this->has_attr( $attr, 'dividerColor', $screen ) ? $this->has_attr( $attr, 'dividerColor', $screen ) . ' !important' : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp-coupon-section .wfacp-coupon-page .wfacp_main_showcoupon', $unique_class ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'couponLinkLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'couponLinkLetterSpacing', $screen ), true );
			$css->add_property( 'font', $this->has_attr( $attr, 'couponLinkFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'couponLinkTextStyle', $screen ) );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} .wfacp_mini_cart_start_h .woocommerce-info', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'couponLinkColor', $screen ) ? $this->has_attr( $attr, 'couponLinkColor', $screen ) . ' !important' : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} .wfacp_mini_cart_start_h form.checkout_coupon.woocommerce-form-coupon .wfacp-form-control-label', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'couponLabelColor', $screen ) ? $this->has_attr( $attr, 'couponLabelColor', $screen ) . ' !important' : '' );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'couponLabelLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'couponLabelLetterSpacing', $screen ), true );
			$css->add_property( 'font', $this->has_attr( $attr, 'couponLabelFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'couponLabelTextStyle', $screen ) );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} .wfacp_mini_cart_start_h form.checkout_coupon.woocommerce-form-coupon .wfacp-form-control', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'couponFieldColor', $screen ) ? $this->has_attr( $attr, 'couponFieldColor', $screen ) . ' !important' : '' );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'couponFieldLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'couponFieldLetterSpacing', $screen ), true );
			$css->add_property( 'font', $this->has_attr( $attr, 'couponFieldFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'couponFieldTextStyle', $screen ) );
			$css->add_property( 'border', $this->has_attr( $attr, 'couponFieldBorder', $screen ) );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} .wfacp_mini_cart_start_h form.checkout_coupon.woocommerce-form-coupon .wfacp-form-control:focus', $unique_class ) );
			$css->add_property( 'border-color', $this->has_attr( $attr, 'couponFieldColorFocus', $screen ) ? $this->has_attr( $attr, 'couponFieldColorFocus', $screen ) . ' !important' : '' );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} .wfacp_mini_cart_start_h button.wfacp-coupon-btn', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'couponButtonColor', $screen ) ? $this->has_attr( $attr, 'couponButtonColor', $screen ) . ' !important' : '' );
			$css->add_property( 'background', $this->has_attr( $attr, 'couponButtonBackground', $screen ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'couponButtonLineHeight', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'couponButtonLetterSpacing', $screen ), true );
			$css->add_property( 'font', $this->has_attr( $attr, 'couponButtonFont', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'couponButtonTextStyle', $screen ) );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} .wfacp_mini_cart_start_h button.wfacp-coupon-btn:hover', $unique_class ) );
			$css->add_property( 'color', $this->has_attr( $attr, 'couponButtonColorHover', $screen ) ? $this->has_attr( $attr, 'couponButtonColorHover', $screen ) . ' !important' : '' );
			$css->add_property( 'background', $this->has_attr( $attr, 'couponButtonBackgroundHover', $screen ) );
			$css->add_property( 'line-height', $this->has_attr( $attr, 'couponButtonLineHeightHover', $screen ), true );
			$css->add_property( 'letter-spacing', $this->has_attr( $attr, 'couponButtonLetterSpacingHover', $screen ), true );
			$css->add_property( 'font', $this->has_attr( $attr, 'couponButtonFontHover', $screen ) );
			$css->add_property( 'text', $this->has_attr( $attr, 'couponButtonTextStyleHover', $screen ) );

			$css->set_selector( $this->add_wrapper( '{{WRAPPER}} .wfacp_mini_cart_start_h *,
			{{WRAPPER}} .wfacp_mini_cart_start_h tr.order-total td span.woocommerce-Price-amount.amount,
			{{WRAPPER}} .wfacp_mini_cart_start_h tr.order-total td span.woocommerce-Price-amount.amount bdi,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container .wfacp_mini_cart_items,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container .wfacp_mini_cart_items .product-total,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container .wfacp_mini_cart_items .product-total span,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container .wfacp_mini_cart_items .product-total small,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container .wfacp_mini_cart_items dl,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container .wfacp_mini_cart_items dt,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container .wfacp_mini_cart_items dd,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container .wfacp_mini_cart_items dd p,
	
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container .wfacp_mini_cart_reviews,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container .wfacp_mini_cart_reviews tr:not(.order-total),
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container .wfacp_mini_cart_reviews tr:not(.order-total) td,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container .wfacp_mini_cart_reviews tr:not(.order-total) th,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container .wfacp_mini_cart_reviews tr:not(.order-total) td span,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container .wfacp_mini_cart_reviews tr:not(.order-total) td small,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container .wfacp_mini_cart_reviews tr:not(.order-total) td a,
			{{WRAPPER}} .wfacp_mini_cart_start_h span.wfacp_coupon_code,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container .wfacp_mini_cart_reviews tr.order-total td span.woocommerce-Price-amount.amount,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container .wfacp_mini_cart_reviews tr.order-total td span.woocommerce-Price-amount.amount bdi,
			{{WRAPPER}} .wfacp_mini_cart_start_h table.shop_table .order-total td,
			{{WRAPPER}} .wfacp_mini_cart_start_h table.shop_table .order-total th,
			{{WRAPPER}} .wfacp_mini_cart_start_h table.shop_table .order-total td span,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container tr.cart_item .product-name,
	
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container .cart_item td,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container .cart_item td small,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container .cart_item td p,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container .cart_item td .product-name span,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp_order_summary_container .cart_item td .product-name,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp-coupon-section .wfacp_main_showcoupon,
			{{WRAPPER}} .wfacp_mini_cart_start_h .shop_table tr.order-total td,
			{{WRAPPER}} .wfacp_mini_cart_start_h .shop_table tr.order-total th,
			{{WRAPPER}} .wfacp_mini_cart_start_h .shop_table tr.order-total td span,
			{{WRAPPER}} .wfacp_mini_cart_start_h .shop_table tr.order-total td small,
			{{WRAPPER}} .wfacp_mini_cart_start_h .checkout_coupon.woocommerce-form-coupon .wfacp-form-control-label,
			{{WRAPPER}} .wfacp_mini_cart_start_h .checkout_coupon.woocommerce-form-coupon .wfacp-form-control,
			{{WRAPPER}} .wfacp_mini_cart_start_h .wfacp-coupon-btn ', $unique_class ) );
			$css->add_property( 'font', $this->has_attr( $attr, 'defaultFontFamily', $screen ) );

			if( 'desktop' !== $screen ) {
				$css->stop_media_query();
			}
		}
		return $css->css_output();
	}


}
WFACP_Blocks_Frontend_CSS::get_instance();