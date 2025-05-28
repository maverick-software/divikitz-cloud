<?php
/**
 * Checkout class; used to validate checkout before submitting payment
 *
 * @author  Your Inspiration Themes
 * @package YITH WooCommerce Stripe
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCSC_VERSION' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_Stripe_Connect_Checkout' ) ) {
	/**
	 * YITH WooCommerce Checkout class
	 *
	 * @since 1.0.0
	 */
	class YITH_Stripe_Connect_Checkout extends WC_Checkout {

		/**
		 * Validates checkout and return true if there is no error, false otherwise
		 * Checkout data should be provided via post request
		 *
		 * @return bool Whether checkout is valid or not
		 */
		public function is_checkout_valid() {

			/** DO_ACTION: woocommerce_checkout_process
			*
			* Adds an action before processing the checkout.
			*/
			do_action( 'woocommerce_checkout_process' );

			$errors      = new WP_Error();
			$posted_data = $this->get_posted_data();

			$this->validate_checkout( $posted_data, $errors );

			if ( count( $errors->get_error_messages() ) ) {
				return false;
			}

			return true;
		}

	}
}