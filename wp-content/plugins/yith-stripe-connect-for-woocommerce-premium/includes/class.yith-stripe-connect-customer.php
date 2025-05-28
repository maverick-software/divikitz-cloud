<?php
/*
* This file belongs to the YITH Framework.
*
* This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://www.gnu.org/licenses/gpl-3.0.txt
*/

use Stripe\StripeObject;

if ( ! defined( 'YITH_WCSC_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 *
 *
 * @class      YITH_Stripe_Connect_Customer
 * @package    Yithemes
 * @since      Version 1.0.0
 * @author     Your Inspiration Themes
 *
 */

if ( ! class_exists( 'YITH_Stripe_Connect_Customer' ) ) {
	/**
	 * Class YITH_Stripe_Connect_Customer
	 *
	 *
	 */
	class YITH_Stripe_Connect_Customer {

		/**
		 * Main Instance
		 *
		 * @var YITH_Stripe_Connect_Customer
		 * @since  1.0.0
		 * @access protected
		 */
		protected static $_instance = null;


		/**
		 * Main plugin Instance
		 *
		 * @return YITH_Stripe_Connect_Customer Main instance
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Construct
		 *
		 * @since  1.0.0
		 */
		public function __construct() {

		}

		/**
		 * Constructor.
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function get_env() {
			if ( empty( $this->env ) ) {
				// Load form_field settings
				$settings  = get_option( 'woocommerce_' . YITH_Stripe_Connect::$gateway_id . '_settings', null );
				$this->env = isset( $settings['test-live'] ) && $settings['test-live'] == 'yes' ? 'test' : 'live';
			}

			return $this->env;
		}

		/**
		 * Returns customer id (if any) for provided user id
		 *
		 * @param $user_id int User id
		 *
		 * @return string|bool Customer id or false, on failure
		 */
		public function get_customer_id( $user_id ) {
			$info = $this->get_usermeta_info( $user_id );

			return ! empty( $info['id'] ) ? $info['id'] : false;
		}

		/**
		 * Returns customer object (if any) for provided user id
		 *
		 * @param $user_id int User id
		 *
		 * @return StripeObject|bool Customer or false on failure
		 */
		public function get_customer( $user_id ) {
			$customer_id = $this->get_customer_id( $user_id );

			if ( ! $customer_id ) {
				return false;
			}

			try {
				return YITH_Stripe_Connect_API_Handler::instance()->get_customer( $customer_id );
			} catch ( Exception $e ) {
				return false;
			}
		}

		/**
		 * Get customer info for a user into DB
		 *
		 * @since 1.0.0
		 */
		public function get_usermeta_info( $user_id ) {
			return get_user_meta( $user_id, $this->get_customer_usermeta_key(), true );
		}

		/**
		 * Update customer info for a user into DB
		 *
		 * @since 1.0.0
		 */
		public function update_usermeta_info( $user_id, $params = array() ) {
			$meta = $this->get_usermeta_info( $user_id );
			$meta = $meta ? $meta : array();
			$meta = array_merge( $meta, $params );

			return update_user_meta( $user_id, $this->get_customer_usermeta_key(), $meta );
		}

		/**
		 * Delete customer info for a user into DB
		 *
		 * @since 1.0.0
		 */
		public function delete_usermeta_info( $user_id ) {
			return delete_user_meta( $user_id, $this->get_customer_usermeta_key() );
		}


		/**
		 * Return the name of user meta for the customer info
		 *
		 * @return string
		 * @since 1.0.0
		 */
		protected function get_customer_usermeta_key() {
			return '_' . $this->get_env() . '_stripe_customer_id';
		}
	}
}

/**
 * Unique access to instance of YITH_WCStripe_Customer class
 *
 * @return \YITH_Stripe_Connect_Customer
 * @since 1.0.0
 */
function YITH_Stripe_Connect_Customer() {
	return YITH_Stripe_Connect_Customer::instance();
}

