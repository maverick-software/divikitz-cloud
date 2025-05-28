<?php
/**
 * Privacy class; added to let customer export personal data
 *
 * @author  Your Inspiration Themes
 * @package YITH Stripe Connect for WooCommerce
 * @version 1.1.4
 */

if ( ! defined( 'YITH_WCSC_VERSION' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_Stripe_Connect_Privacy' ) ) {
	/**
	 * YITH Stripe Connect Privacy class
	 *
	 * @since 1.1.4
	 */
	class YITH_Stripe_Connect_Privacy extends YITH_Privacy_Plugin_Abstract {

		/**
		 * Constructor method
		 *
		 * @return \YITH_Stripe_Connect_Privacy
		 * @since 1.1.4
		 */
		public function __construct() {
			parent::__construct( 'YITH Stripe Connect for WooCommerce' );

			// set up Stripe Connect data eraser
			add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_eraser' ) );
		}

		/**
		 * Retrieves privacy example text for stripe connect plugin
		 *
		 * @return string Privacy message
		 * @since 1.1.4
		 */
		public function get_privacy_message( $section ) {
			$content = '';

			switch ( $section ) {
				case 'collect_and_store':
					$content = '<p>' . __( 'While you visit our site, we’ll track:', 'yith-stripe-connect-for-woocommerce' ) . '</p>' .
							   '<ul>' .
							   '<li>' . __( 'User unique identifier: this ID is used to uniquely identify the user on Stripe platform and create charges/refunds.', 'yith-stripe-connect-for-woocommerce' ) . '</li>' .
							   '</ul>';
					break;
				case 'has_access':
					$content = '<p>' . __( 'Members of our team have access to the information you provide us. For example, both Administrators and Shop Managers can access:', 'yith-stripe-connect-for-woocommerce' ) . '</p>' .
							   '<ul>' .
							   '<li>' . __( 'All data returned by Stripe Connect, including users’ details.', 'yith-stripe-connect-for-woocommerce' ) . '</li>' .
							   '</ul>' .
							   '<p>' . __( 'Our team members have access to this information to track users’ identity on Stripe Connect’s server.', 'yith-stripe-connect-for-woocommerce' ) . '</p>';
					break;
				case 'payments':
					$content = '<p>' . __( 'We accept payments through Stripe Connect. When processing payments, some of your data will be passed to Stripe Connect, including information required to process or support the payment, such as the purchase total and billing information.', 'yith-stripe-connect-for-woocommerce' ) . '</p>' .
							   '<p>' . __( 'Please see the <a href="https://stripe.com/us/privacy/">Stripe Worldwide Privacy Policy</a> for more details.', 'yith-stripe-connect-for-woocommerce' ) . '</p>';
					break;
				case 'share':
				default:
					break;
			}

			/** APPLY_FILTERS: yith_wcsc_privacy_policy_content
			*
			* Filter the privacy content.
			*
			* @param $content Entire HTML content.
			* @param $section Section.
			*/
			return apply_filters( 'yith_wcsc_privacy_policy_content', $content, $section );
		}

		/**
		 * Register eraser for stripe connect plugin
		 *
		 * @param $erasers array Array of currently registered erasers
		 *
		 * @return array Array of filtered erasers
		 * @since 1.1.4
		 */
		public function register_eraser( $erasers ) {
			$erasers['yith_wcsc_eraser'] = array(
				'eraser_friendly_name' => __( 'Stripe Connect data', 'yith-stripe-connect-for-woocommerce' ),
				'callback'             => array( $this, 'stripe_data_eraser' )
			);

			return $erasers;
		}

		/**
		 * Deletes Stripe Connect data for the user
		 *
		 * @param $email_address string Email of the users that requested export
		 * @param $page          int Current page processed
		 *
		 * @return array Result of the operation
		 * @since 1.1.4
		 */
		public function stripe_data_eraser( $email_address, $page ) {
			$user     = get_user_by( 'email', $email_address ); // Check if user has an ID in the DB to load stored personal data.
			$response = array(
				'items_removed'  => false,
				'items_retained' => false,
				'messages'       => array(),
				'done'           => true,
			);

			if ( ! $user instanceof WP_User ) {
				return $response;
			}

			delete_user_meta( $user->ID, 'stripe_user_id' );
			delete_user_meta( $user->ID, 'stripe_access_token' );

			$response['messages'][]    = __( 'Removed Stripe Connect\'s customer data.', 'yith-stripe-connect-for-woocommerce' );
			$response['items_removed'] = true;

			return $response;
		}
	}
}