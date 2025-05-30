<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Send Email to Customer when the subscription is suspended.
 *
 * @class   YITH_WC_Customer_Subscription_Suspended
 * @package YITH WooCommerce Subscription
 * @since   1.0.0
 * @author  YITH <plugins@yithemes.com>
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'YITH_YWSBS_VERSION' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YITH_WC_Customer_Subscription_Suspended' ) ) {
	/**
	 * YITH_WC_Customer_Subscription_Suspended
	 *
	 * @since 1.0.0
	 */
	class YITH_WC_Customer_Subscription_Suspended extends YITH_WC_Customer_Subscription {

		/**
		 * Constructor method, used to return object of the class to WC
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			// Call parent constructor.
			$this->id          = 'ywsbs_customer_subscription_suspended';
			$this->title       = __( 'Subscription Suspended', 'yith-woocommerce-subscription' );
			$this->description = __( 'This email is sent to the customer when subscription is suspended', 'yith-woocommerce-subscription' );
			$this->email_type  = 'html';
			$this->heading     = __( 'Your subscription has been suspended', 'yith-woocommerce-subscription' );
			$this->subject     = __( 'Your subscription has been suspended', 'yith-woocommerce-subscription' );

			parent::__construct();
		}

		/**
		 * Method triggered to send email
		 *
		 * @param   YWSBS_Subscription  $subscription  Subscription.
		 *
		 * @return void
		 * @since  1.0
		 */
		public function trigger( $subscription ) {

			$this->recipient = $subscription->get_billing_email();

			if ( 'yes' === $this->send_to_admin ) {
				$this->recipient .= ',' . get_option( 'admin_email' );
			}

			// Check if this email type is enabled, recipient is set.
			if ( ! $this->is_enabled() || ! $this->get_recipient() || $subscription->get_renew_order_id() === 0 ) {
				return;
			}

			$order = $subscription->get_renew_order();

			if ( ! $order ) {
				return;
			}

			$this->object = $subscription;
			$this->order  = $order;

			$this->placeholders['{order_number}'] = $order->get_order_number();


			$next_activity_date = $subscription->get_payment_due_date() + ywsbs_get_suspension_time();

			$this->template_variables = array(
				'subscription'       => $this->object,
				'order'              => $this->order,
				'email_heading'      => $this->get_heading(),
				'sent_to_admin'      => false,
				'next_activity'      => __( 'cancelled', 'yith-woocommerce-subscription' ),
				'next_activity_date' => $next_activity_date,
				'email'              => $this,
			);

			$return = $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content_html(), $this->get_headers(), $this->get_attachments() );
		}

		/**
		 * Get HTML content for the mail
		 *
		 * @return string HTML content of the mail.
		 * @since  1.0
		 */
		public function get_content_html() {
			ob_start();
			wc_get_template( $this->template_html, $this->template_variables, '', $this->template_base );

			return ob_get_clean();
		}
	}
}


// returns instance of the mail on file include.
return new YITH_WC_Customer_Subscription_Suspended();
