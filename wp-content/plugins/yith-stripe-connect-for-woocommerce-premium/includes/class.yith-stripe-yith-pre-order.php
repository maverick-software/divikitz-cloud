<?php
/*
* This file belongs to the YITH Framework.
*
* This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://www.gnu.org/licenses/gpl-3.0.txt
*/
if ( ! defined( 'YITH_WCSC_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 *
 *
 * @class      YITH_Stripe_YITH_Pre_Order
 * @package    Yithemes
 * @since      Version 1.1.0
 * @author     Your Inspiration Themes
 *
 */

if ( ! class_exists( 'YITH_Stripe_YITH_Pre_Order' ) ) {

	/**
	 * Class YITH_Stripe_YITH_Pre_Order
	 *
	 */
	class YITH_Stripe_YITH_Pre_Order {

		/**
		 * YITH_Stripe_YITH_Pre_Order Instance
		 *
		 * @var YITH_Stripe_YITH_Pre_Order
		 * @since  1.1.0
		 * @access protected
		 */
		protected static $instance = null;

		/**
		 * Main plugin Instance
		 *
		 * @return YITH_Stripe_YITH_Pre_Order instance
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Construct
		 *
		 * @since  1.1.0
		 */
		public function __construct() {
			add_filter( 'woocommerce_payment_gateways', array( $this, 'add_stripe_connect_sources_gateway' ), 10 );
		}

		/**
		 * Replace the main gateway with YITH_Stripe_Connect_Sources_Gateway.
		 *
		 * @param array $gateways Array of payment gateways.
		 *
		 * @return array
		 */
		public function add_stripe_connect_sources_gateway( $gateways ) {
			foreach ( $gateways as $key => $gateway ) {
				if ( 'YITH_Stripe_Connect_Gateway' === $gateway ) {
					$gateways[ $key ] = 'YITH_Stripe_Connect_Source_Gateway';
				}
			}

			return $gateways;
		}

	}
}
