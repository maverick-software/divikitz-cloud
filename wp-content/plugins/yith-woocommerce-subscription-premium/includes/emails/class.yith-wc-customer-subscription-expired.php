<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Send Email to Customer when the subscription expires
 *
 * @class   YITH_WC_Customer_Subscription_Expired
 * @package YITH WooCommerce Subscription
 * @since   1.0.0
 * @author  YITH <plugins@yithemes.com>
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'YITH_YWSBS_VERSION' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YITH_WC_Customer_Subscription_Expired' ) ) {
	/**
	 * YITH_WC_Customer_Subscription_Expired
	 *
	 * @since 1.0.0
	 */
	class YITH_WC_Customer_Subscription_Expired extends YITH_WC_Customer_Subscription {

		/**
		 * Constructor method, used to return object of the class to WC
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			$this->id          = 'ywsbs_customer_subscription_expired';
			$this->title       = __( 'Subscription Expired', 'yith-woocommerce-subscription' );
			$this->description = __( 'This email is sent to the customer when subscription expires', 'yith-woocommerce-subscription' );
			$this->email_type  = 'html';
			$this->heading     = __( 'Your subscription has expired', 'yith-woocommerce-subscription' );
			$this->subject     = __( 'Your {site_title} subscription has expired', 'yith-woocommerce-subscription' );

			// Call parent constructor.
			parent::__construct();

		}
	}
}

// returns instance of the mail on file include.
return new YITH_WC_Customer_Subscription_Expired();
