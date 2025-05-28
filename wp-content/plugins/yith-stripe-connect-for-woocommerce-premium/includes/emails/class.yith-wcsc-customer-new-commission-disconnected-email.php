<?php
/**
 * New Stripe Connect class
 *
 * @author  Your Inspiration Themes
 * @package YITH WooCommerce Affiliates
 * @version 1.0.0
 */

/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'YITH_WCSC_VERSION' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCSC_New_Commission_Disconnected_Email' ) ) {
	/**
	 * New affiliate email
	 *
	 * @since 1.0.0
	 */
	class YITH_WCSC_New_Commission_Disconnected_Email extends WC_Email {

		/**
		 * Constructor method, used to return object of the class to WC
		 *
		 * @return \YITH_WCSC_New_Commission_Disconnected_Email
		 * @since 1.0.0
		 */
		public function __construct() {
			$this->id          = 'new_commission_disconnected';
			$this->title       = __( 'Commission created for Stripe disconnected account', 'yith-stripe-connect-for-woocommerce' );
			$this->description = __( 'This email is sent to customers each time a new commission is created for users who have disconnected their
			Stripe account', 'yith-stripe-connect-for-woocommerce' );

			$this->heading = __( 'New commission has been created, but needs your Stripe Account to be connected', 'yith-stripe-connect-for-woocommerce' );
			$this->subject = __( 'New commission has been created, but needs your Stripe Account to be connected', 'yith-stripe-connect-for-woocommerce' );

			$this->content_html = $this->get_option( 'content_html' );
			$this->content_text = $this->get_option( 'content_text' );

			$this->template_html  = 'emails/customer-new-commission-disconnected-email.php';
			$this->template_plain = 'emails/plain/customer-new-commission-disconnected-email.php';

			// Triggers for this email
			add_action( 'yith_wcsc_new_commission_notification', array( $this, 'trigger' ), 10, 1 );

			// Call parent constructor
			parent::__construct();

			// Other settings
			$this->recipient = $this->get_option( 'recipient' );

			if ( ! $this->recipient ) {
				$this->recipient = get_option( 'admin_email' );
			}
		}

		/**
		 * Method triggered to send email
		 *
		 * @param $commission array
		 *
		 * @return void
		 */
		public function trigger( $commission ) {
			$this->object = $commission;

			if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
				return;
			}

			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		/**
		 * Check if mail is enabled
		 *
		 * @return bool Whether email notification is enabled or not
		 * @since 1.0.0
		 */
		public function is_enabled() {
			$commission_notification = get_option( 'yith_wcsc_mail_commission_notification' );

			return $commission_notification == 'yes';
		}

		/**
		 * Get HTML content for the mail
		 *
		 * @return string HTML content of the mail
		 * @since 1.0.0
		 */
		public function get_content_html() {
			ob_start();


			/*yith_wcaf_get_template( $this->template_html, array(
				'affiliate' 	=> $this->object,
				'affiliate_referral_url' => YITH_WCAF()->get_referral_url( $this->object['token'] ),
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => true,
				'plain_text'    => false
			) );*/

			return ob_get_clean();
		}

		/**
		 * Get plain text content of the mail
		 *
		 * @return string Plain text content of the mail
		 * @since 1.0.0
		 */
		public function get_content_plain() {
			ob_start();

			/*yith_wcaf_get_template( $this->template_plain, array(
				'affiliate'		=> $this->object,
				'affiliate_referral_url' => YITH_WCAF()->get_referral_url( $this->object['token'] ),
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => true,
				'plain_text'    => true
			) );*/

			return ob_get_clean();
		}

		/**
		 * Init form fields to display in WC admin pages
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'recipient'  => array(
					'title'       => __( 'Recipient(s)', 'woocommerce' ),
					'type'        => 'text',
					'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to <code>%s</code>.', 'woocommerce' ), esc_attr( get_option( 'admin_email' ) ) ),
					'placeholder' => '',
					'default'     => ''
				),
				'subject'    => array(
					'title'       => __( 'Subject', 'woocommerce' ),
					'type'        => 'text',
					'description' => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'woocommerce' ), $this->subject ),
					'placeholder' => '',
					'default'     => ''
				),
				'heading'    => array(
					'title'       => __( 'Email heading', 'woocommerce' ),
					'type'        => 'text',
					'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'woocommerce' ), $this->heading ),
					'placeholder' => '',
					'default'     => ''
				),
				'email_type' => array(
					'title'       => __( 'Email type', 'woocommerce' ),
					'type'        => 'select',
					'description' => __( 'Choose which format of email to send.', 'woocommerce' ),
					'default'     => 'html',
					'class'       => 'email_type wc-enhanced-select',
					'options'     => $this->get_email_type_options()
				)
			);
		}
	}
}

return new YITH_WCAF_Admin_New_Affiliate_Email();