<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * YITH Multiple Shipping Addresses for WooCommerce
 * Plugin URI: https://yithemes.com/
 */
class WFACP_Compatibility_With_Yith_Multiple_Shipping_Address_WC {

	public function __construct() {
		add_action( 'wfacp_before_process_checkout_template_loader', [ $this, 'add_action' ], 99 );
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'add_action' ], 99 );
		add_action( 'wfacp_internal_css', [ $this, 'wfacp_internal_css' ], 99 );

	}

	public function add_action() {

		if ( class_exists( 'YITH_Multiple_Addresses_Shipping_Frontend' ) ) {
			$object = WFACP_Common::remove_actions( 'woocommerce_before_checkout_form', 'YITH_Multiple_Addresses_Shipping_Frontend', 'manage_addresses_cb' );

			if ( $object instanceof YITH_Multiple_Addresses_Shipping_Frontend ) {
				add_action( 'woocommerce_before_checkout_form', [ $object, 'manage_addresses_cb' ], 999 );
			}
		}

		if ( function_exists( 'yith_wcmas_init' ) ) {
			add_filter( 'wfacp_item_quantity', function ( $item_quantity, $cart_item ) {
				if ( isset( $cart_item['quantity'] ) && ! empty( $cart_item['quantity'] ) ) {
					return $cart_item['quantity'];
				}

				return $item_quantity;
			}, 10, 2 );
		}
	}

	public function wfacp_internal_css() {
		if ( ! class_exists( 'YITH_Multiple_Addresses_Shipping_Frontend' ) ) {
			return;
		}
		if ( function_exists( 'wfacp_template' ) ) {
			$instance = wfacp_template();
		}


		?>

        <style>
            <?php

                if($instance->get_template_type()!=='pre_built'){
                        echo ".pp_pic_holder.pp_woocommerce{top: 50px !important;}";
                    }

            ?>
            .wfacp_mini_cart_start_h table.shop_table.wfacp_mini_cart_reviews tr td, .wfacp_mini_cart_start_h table.shop_table.wfacp_mini_cart_reviews tr th {
                padding: 8px 0;
            }

            .pp_pic_holder.pp_woocommerce {
                top: 50% !important;
            }

            a.ywcmas_shipping_address_button_edit {
                display: inline-block;
                margin-right: 5px !important;
            }

            select.ywcmas_addresses_manager_address_select {
                margin-bottom: 5px !important;
            }

            td.ywcmas_addresses_manager_table_foot span.ywcmas_increase_qty_alert {
                font-size: 8pt !important;
                float: right;
            }

            table.ywcmas_addresses_manager_table.shop_table_responsive tfoot tr td {
                padding-bottom: 10px !important;
                border-bottom: 1px solid #ddd !important;
            }

            table.ywcmas_addresses_manager_table.shop_table_responsive tbody tr td {
                padding-top: 10px !important;

            }

            .pp_pic_holder.pp_woocommerce {
                top: 50% !important;
            }

        </style>
        <script>

            window.addEventListener('load', function () {
                (function ($) {

                    $(document.body).on('click', '.wfacp_increase_item,.wfacp_decrease_item', function () {

                        var cart_key = $(this).parents('.cart_item').find("input[type=number]").attr('cart_key');
                        if (typeof cart_key == "undefined") {
                            cart_key = $(this).parents('.cart_item').attr('cart_key');
                        }
                        setTimeout(function () {
                            if (cart_key != '' && typeof cart_key !== "undefined") {

                                if ($('.ywcmas_addresses_manager_table').length > 0) {
                                    $('.ywcmas_addresses_manager_table').each(function () {
                                        var cartkey = $(this).find('tbody').find('.ywcmas_addresses_manager_table_item_cart_id').val();
                                        if (cart_key == cartkey) {
                                            $(this).find('tbody').find('.ywcmas_addresses_manager_table_shipping_address_select').trigger('change');
                                        }
                                    });
                                }
                            }
                        }, 500);


                    });

                })(jQuery);
            });
        </script>
		<?php
	}


}

if ( function_exists( 'yith_wcmas_init' ) || class_exists( 'YITH_Multiple_Addresses_Shipping_Frontend' ) ) {
	WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Yith_Multiple_Shipping_Address_WC(), 'wfacp-ymsfw' );
}

