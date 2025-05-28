<?php

/**
 * @author woofunnels
 * @package WooFunnels
 */
if ( ! class_exists( 'WooFunnels_Funnel_Builder_Commons' ) ) {
	class WooFunnels_Funnel_Builder_Commons {

		protected static $instance;

		/**
		 * WooFunnels_Cache constructor.
		 */
		public function __construct() {
			add_action( 'manage_shop_order_posts_custom_column', array( $this, 'show_woofunnels_total_in_order_listings' ), 99, 2 );
			add_action( 'admin_init', function () {
				if ( class_exists( 'WFOCU_Admin' ) ) {
					remove_filter( 'woocommerce_get_formatted_order_total', array( WFOCU_Core()->admin, 'show_upsell_total_in_order_listings' ), 999, 2 );
				}

				if ( class_exists( 'WFOB_Admin' ) ) {

					remove_filter( 'woocommerce_get_formatted_order_total', array( WFOB_Core()->admin, 'show_bump_total_in_order_listings' ), 9999, 2 );
				}
			} );

		}

		/**
		 * Creates an instance of the class
		 * @return WooFunnels_Cache
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}


		public function show_woofunnels_total_in_order_listings( $column_name, $post_id ) {


			$total_woofunnels = 0;

			$order = wc_get_order( $post_id );

			$html = '';

			if ( 'order_total' === $column_name ) {

				$show_combined = true;
				if ( class_exists( 'WFOCU_Admin' ) ) {

					$result_in_order_currency = $order->get_meta( '_wfocu_upsell_amount_currency', true );


					if ( true === $show_combined && ! empty( $result_in_order_currency ) ) {
						$total_woofunnels = $total_woofunnels + $result_in_order_currency;
					}


				}
				if ( class_exists( 'WFOB_Admin' ) ) {
					if ( true === $show_combined ) {

						$line_total = 0;
						$have_bumps = 0;
						if ( $order instanceof WC_Order ) {
							$line_items = $order->get_items();
							/**
							 * @var $item WC_Order_Item_Product
							 */
							foreach ( $line_items as $item ) {
								$_bump_purchase = $item->get_meta( '_bump_purchase' );
								if ( '' !== $_bump_purchase ) {
									$have_bumps ++;
									$line_total = $line_total + $item->get_total();
								}
							}
						}
						$total_woofunnels = $total_woofunnels + $line_total;

					}
				}


				if ( ! empty( $total_woofunnels ) ) {
					$html = '<br/>
<p style="font-size: 12px;"><em> ' . sprintf( esc_html__( 'WooFunnels: %s' ), wc_price( $total_woofunnels, array( 'currency' => get_option( 'woocommerce_currency' ) ) ) ) . '</em></p>';


				}
			}

			echo $html;

		}


	}


}
