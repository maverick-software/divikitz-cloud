<?php
/**
 * Compatibility Name: WooCommerce Checkout Add-Ons
 * Plugin URI: http://www.woocommerce.com/products/woocommerce-checkout-add-ons/
 *
 */


class WFACP_Checkout_addons {
	private $label_separator = ' - ';
	private $is_checkout_order_review = false;

	public function __construct() {

		add_filter( 'wfacp_advanced_fields', [ $this, 'add_fields' ] );
		add_action( 'process_wfacp_html', [ $this, 'call_checkout_add_on' ], 10, 3 );
		add_action( 'wfacp_internal_css', [ $this, 'wfacp_internal_css' ] );
		add_filter( 'wfacp_html_fields_wc_checkout_add_on', '__return_false' );

		add_action( 'woocommerce_review_order_after_cart_contents', function () {
			$this->is_checkout_order_review = true;
		} );

		add_action( 'woocommerce_review_order_after_order_total', function () {
			$this->is_checkout_order_review = false;
		} );

		add_action( 'wfacp_before_order_total_field', function () {
			$this->is_checkout_order_review = true;
		} );

		add_action( 'wfacp_after_order_total_field', function () {
			$this->is_checkout_order_review = false;
		} );

		add_action( 'wfacp_after_template_found', [ $this, 'after_template_load' ] );
		add_filter( 'wfacp_checkout_fields', [ $this, 'remove_addons_field' ] );
		add_filter( 'woocommerce_update_order_review_fragments', [ $this, 'refresh_checkout_fields_frag' ], 11 );
		add_filter( 'wp_footer', [ $this, 'add_js' ], 11 );
	}

	public function after_template_load() {
		add_action( 'woocommerce_before_checkout_form', [ $this, 'actions' ] );
		add_filter( 'esc_html', array( $this, 'display_add_on_value_in_checkout_order_review' ), 10, 2 );
	}

	public function add_js() {

		if ( ! $this->is_enable() ) {
			return;
		}
		?>
        <script>
            window.addEventListener('bwf_checkout_load', function () {
                (function ($) {
                    $(document.body).on('wfacp_step_switching', function () {
                        let wfacp_add_select21 = $('.form-row');
                        wfacp_add_select21.each(function () {
                            var wfacp_add_select2 = $(this);
                            if (wfacp_add_select2.length > 0 && wfacp_add_select2.find('select').hasClass('select2-hidden-accessible')) {
                                setTimeout(function () {
                                    wfacp_add_select2.find('select').select2();
                                }, 600);
                            }
                        });

                    });

                    $(document.body).on('wfacp_coupon_apply', function () {
                        $(document.body).trigger('update_checkout');
                    });

                    $(document.body).on('wfacp_coupon_form_removed', function () {
                        $(document.body).trigger('update_checkout');
                    });
                })(jQuery);
            });
        </script>
		<?php

	}

	public function actions() {

		$position = apply_filters( 'wc_checkout_add_ons_position', get_option( 'wc_checkout_add_ons_position', 'woocommerce_checkout_after_customer_details' ) );
		if ( class_exists( 'SkyVerge\WooCommerce\Checkout_Add_Ons\Frontend\Frontend' ) ) {
			WFACP_Common::remove_actions( $position, 'SkyVerge\WooCommerce\Checkout_Add_Ons\Frontend\Frontend', 'render_add_ons' );
			WFACP_Common::remove_actions( 'esc_html', 'SkyVerge\WooCommerce\Checkout_Add_Ons\Frontend\Frontend', 'display_add_on_value_in_checkout_order_review' );
		}
	}

	private function is_enable() {
		return function_exists( 'wc_checkout_add_ons' );
	}

	public function add_fields( $field ) {

		if ( ! $this->is_enable() ) {
			return $field;
		}

		$field['wc_checkout_add_on'] = [
			'type'       => 'wfacp_html',
			'class'      => [ 'wc_checkout_add_on' ],
			'id'         => 'wc_checkout_add_on',
			'field_type' => 'advanced',
			'label'      => __( 'Checkout Addons', 'woocommerce' ),
		];

		return $field;
	}

	public function call_checkout_add_on( $field, $key, $args ) {
		if ( ! $this->is_enable() ) {
			return;
		}
		if ( ! empty( $key ) && $key == 'wc_checkout_add_on' ) {
			$this->render_add_ons();
		}
	}


	private function render_add_ons() {

		$checkout_add_on_fields = isset( WC()->checkout()->checkout_fields['add_ons'] ) ? WC()->checkout()->checkout_fields['add_ons'] : null;
		if ( is_array( $checkout_add_on_fields ) && count( $checkout_add_on_fields ) > 0 ) {
			foreach ( $checkout_add_on_fields as $key => $field ) {
				$type = $field['type'];

				if ( $type == 'wc_checkout_add_ons_checkbox' || $type == 'wc_checkout_add_ons_multicheckbox' ) {
					$field['type'] = 'checkbox';
				}

				if ( $type == 'wc_checkout_add_ons_radio' ) {
					$field['type'] = 'wc_checkout_add_ons_radio';

				}
				if ( $type == 'select' ) {
					$field['class'] = [ 'wfacp_custom_wrap' ];
				}


				if ( $type == 'text' || $type == 'select' || $type == 'textarea' ) {
					$class = [ 'wfacp_checkout_addon_wrap' ];
					if ( isset( $field['default'] ) && $field['default'] != '' ) {
						$class[] = 'wfacp-anim-wrap';
					}

					if ( isset( $field['description'] ) && $field['description'] != '' ) {
						$class[] = 'wfacp_default_checkout_addon';
					}
					if ( count( $class ) > 0 ) {
						$field['class'] = $class;
					}
					if ( isset( $field['custom_attributes'] ) && ( is_array( $field['custom_attributes'] ) && count( $field['custom_attributes'] ) > 0 ) ) {
						if ( $field['custom_attributes']['data-description'] ) {
							$field['class'] [] = 'wfacp_label_normal';
						}
					}

				} elseif ( $type == 'wc_checkout_add_ons_multicheckbox' ) {
					$field['class'] = [ 'wfacp_default_checkout_addon_multicheckbox', 'wfacp_checkout_addon_wrap' ];
				} elseif ( $type == 'wc_checkout_add_ons_file' ) {
					$class          = [ 'wc_checkout_add_ons_fileupload', 'wfacp_checkout_addon_wrap' ];
					$field['class'] = $class;
				} elseif ( $type == 'wc_checkout_add_ons_multiselect' ) {
					$field['class'] = [ 'wc_checkout_add_ons_multiselect', 'wfacp_checkout_addon_wrap' ];
				} elseif ( $type == 'wc_checkout_add_ons_radio' ) {
					$field['class'] = [ 'wc_checkout_add_ons_radio', 'wfacp_checkout_addon_wrap' ];
				}


				$field = apply_filters( 'wfacp_forms_field', $field, $key );

				if ( $type == 'wc_checkout_add_ons_checkbox' || $type == 'wc_checkout_add_ons_multicheckbox' ) {
					$field['type'] = $type;

				}

				$checkout_add_on_fields[ $key ] = $field;
			}
		}
		echo '<div id="wc_checkout_add_ons">';
		if ( is_array( $checkout_add_on_fields ) && count( $checkout_add_on_fields ) > 0 ) {
			foreach ( $checkout_add_on_fields as $key => $field ) :
				woocommerce_form_field( $key, $field, WC()->checkout()->get_value( $key ) );
			endforeach;
		}
		echo '</div>';
	}

	public function display_add_on_value_in_checkout_order_review( $safe_text, $text ) {

		if ( ! $this->is_enable() ) {
			return $safe_text;
		}

		// Bail out if not in checkout order review area
		if ( ! $this->is_checkout_order_review ) {
			return $safe_text;
		}
		$text = sanitize_title( $text );

		if ( isset( WC()->session->checkout_add_ons['fees'][ $text ] ) ) {

			$session_data = WC()->session->checkout_add_ons['fees'][ $text ];

			// Get add-on value from session and set it for add-on
			$add_on = SkyVerge\WooCommerce\Checkout_Add_Ons\Add_Ons\Add_On_Factory::get_add_on( $session_data['id'] );

			// removes our own filtering to account for the rare possibility that an option value is named the same way as the add on
			remove_filter( 'esc_html', array( $this, 'display_add_on_value_in_checkout_order_review' ), 10 );

			// Format add-on value
			$value = $add_on ? $add_on->normalize_value( $session_data['value'], true ) : null;

			// re-add back our filter after normalization is done
			add_filter( 'esc_html', array( $this, 'display_add_on_value_in_checkout_order_review' ), 10, 2 );

			// Append value to add-on name
			if ( $value ) {

				if ( 'text' === $add_on->get_type() || 'textarea' === $add_on->get_type() ) {
					$value = $add_on->truncate_label( $value );
				}

				$safe_text .= $this->label_separator . $value;
			}
		}

		return $safe_text;
	}

	public function remove_addons_field( $fields ) {
		if ( ! $this->is_enable() ) {
			return $fields;
		}
		if ( ! isset( $fields['advanced']['wc_checkout_add_on'] ) ) {
			WFACP_Common::remove_actions( 'woocommerce_checkout_fields', 'SkyVerge\WooCommerce\Checkout_Add_Ons\Frontend\Frontend', 'add_checkout_fields' );
		}

		return $fields;
	}

	public function refresh_checkout_fields_frag( $fragments ) {

		if ( ! $this->is_enable() ) {
			return $fragments;
		}

		ob_start();

		$this->render_add_ons();

		$fragments['#wc_checkout_add_ons'] = ob_get_clean();

		return $fragments;
	}

	public function wfacp_internal_css( $slug ) {
		if ( ! $this->is_enable() ) {
			return;
		}


		?>

        <style>

            body .wfacp_main_form.woocommerce .wfacp_error_border {
                transition: all .4s ease-out !important;
                box-shadow: 0 0 0 1px #d50000 !important;
                border-color: #d50000 !important;
            }


            body:not(.wfacp_cls_layout_1) .wfacp_main_form #wc_checkout_add_ons p.wfacp_label_normal:not(.wfacp-anim-wrap) input[type=text],
            body:not(.wfacp_cls_layout_1) .wfacp_main_form #wc_checkout_add_ons p.wfacp_label_normal:not(.wfacp-anim-wrap) input[type=number],
            body:not(.wfacp_cls_layout_1) .wfacp_main_form #wc_checkout_add_ons p.wfacp_label_normal:not(.wfacp-anim-wrap) input[type=email] {
                padding-top: 12px;
                padding-bottom: 10px;
            }

            body:not(.wfacp_cls_layout_1) .wfacp_main_form #wc_checkout_add_ons p.wfacp_label_normal:not(.wfacp-anim-wrap) label {
                top: 19px;
                bottom: auto;
                margin: 0;
                line-height: 1.5;
            }

            body.wfacp_cls_layout_2 .wfacp_main_form #wc_checkout_add_ons p.wfacp_label_normal:not(.wfacp-anim-wrap) label,
            body.wfacp_cls_layout_4 .wfacp_main_form #wc_checkout_add_ons p.wfacp_label_normal:not(.wfacp-anim-wrap) label {
                top: 14px;

            }

        </style>
		<?php
	}

}

add_action( 'plugins_loaded', function () {
	if ( ! class_exists( 'WC_Checkout_Add_Ons_Loader' ) ) {
		return;
	}
	WFACP_Plugin_Compatibilities::register( new WFACP_Checkout_addons(), 'checkout_addons' );
} );

