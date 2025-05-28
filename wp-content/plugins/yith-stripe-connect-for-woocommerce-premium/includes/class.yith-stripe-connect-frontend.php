<?php

/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'YITH_WCSC_PATH' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Frontend class
 *
 * @class      YITH_Stripe_Connect_Frontend
 * @package    Yithemes
 * @since      Version 1.0.0
 * @author     Francisco Javier Mateo <francisco.mateo@yithemes.com>
 */

if ( ! class_exists( 'YITH_Stripe_Connect_Frontend' ) ) {
	/**
	 * Class YITH_Stripe_Connect_Frontend
	 *
	 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
	 */
	class YITH_Stripe_Connect_Frontend {

		/**
		 * Stripe Connect Handler Instance
		 *
		 * @var YITH_Stripe_Connect_API_Handler
		 * @since  1.0.0
		 * @access protected
		 */
		protected $_stripe_connect_api_handler = null;

		/**
		 * Stripe Connect Handler Instance
		 *
		 * @var YITH_Stripe_Connect_Receivers
		 * @since  1.0.0
		 * @access protected
		 */
		protected $_stripe_connect_receivers = null;

		/**
		 * Stripe Connect Handler Instance
		 *
		 * @var YITH_Stripe_Connect_Commissions
		 * @since  1.0.0
		 * @access protected
		 */
		protected $_stripe_connect_commissions = null;

		/**
		 * Construct
		 *
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 * @since  1.0.0
		 */
		public function __construct() {

			// We just add our custom menu item 'Stripe Connect' on Account page...
			add_filter( 'woocommerce_account_menu_items', array( $this, 'add_account_menu_item' ) );

			// We define the content for our Stripe Connect Account Page...
			add_action( 'woocommerce_account_stripe-connect_endpoint', array( $this, 'print_account_page' ) );

			// Ajax calls.
			add_action( 'wp_ajax_disconnect_stripe_connect', array( $this, 'disconnect' ) );

			$this->_stripe_connect_api_handler = YITH_Stripe_Connect_API_Handler::instance();
			$this->_stripe_connect_receivers   = YITH_Stripe_Connect_Receivers::instance();
			$this->_stripe_connect_commissions = YITH_Stripe_Connect_Commissions::instance();
		}

		/**
		 * Adds item to My Account page navigation menu
		 *
		 * @param array $items Array of defined items.
		 * @return array Filtered array of items.
		 */
		public function add_account_menu_item( $items ) {
			$items['stripe-connect'] = _x( 'Stripe Connect', 'No need translation...', 'yith-stripe-connect-for-woocommerce' );

			/** APPLY_FILTERS: yith_wcsc_account_menu_item
			*
			* Filter menú item in my-account.
			*
			* @param $items Array of defined items.
			*/
			return apply_filters( 'yith_wcsc_account_menu_item', $items );
		}

		/**
		 * Prints content for Stripe Connect account page
		 *
		 * @return void
		 */
		public function print_account_page() {
			$this->enqueue_scripts_for_account_page();

			// The page has loaded from Stripe Platform, some user want connect with us.
			if ( isset( $_GET['scope'] ) && isset( $_GET['code'] ) ) {
				$code    = sanitize_text_field( wp_unslash( $_GET['code'] ) );
				$user_id = get_current_user_id();

				$this->_stripe_connect_receivers->connect_by_user_id_and_access_code( $user_id, $code );
			}

			// retrieve connections parameters.
			$current_status = yith_wcsc_get_stripe_user_status( get_current_user_id() );
			$oauth_link     = '';

			if ( 'disconnect' == $current_status ) {
				$oauth_link = $this->_stripe_connect_api_handler->get_OAuth_link();
			}

			$button_text  = '';
			$button_class = '';

			if ( 'connect' == $current_status ) {

				/** APPLY_FILTERS: yith_wcsc_disconnect_from_stripe_button_text
				*
				* Filter the message when disconnected from Stripe.
				*/
				$button_text  = apply_filters( 'yith_wcsc_disconnect_from_stripe_button_text', __( 'Disconnect from Stripe', 'yith-stripe-connect-for-woocommerce' ) );
				$button_class = 'yith-sc-disconnect';
			} else if ( 'disconnect' == $current_status ) {

				/** APPLY_FILTERS: yith_wcsc_connect_with_stripe_button_text
				*
				* Filter the button text to connect to Stripe.
				*/
				$button_text = apply_filters( 'yith_wcsc_connect_with_stripe_button_text', __( 'Connect with Stripe', 'yith-stripe-connect-for-woocommerce' ) );
			}

			// retrieve commissions.
			$commissions_args = array(
				'user_id'    => get_current_user_id(),
				'product_id' => isset( $_GET['yith_wcs_product'] ) ? intval( $_GET['yith_wcs_product'] ) : '',
				'date_from'  => isset( $_GET['yith_wcsc_date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['yith_wcsc_date_from'] ) ) : '',
				'date_to'    => isset( $_GET['yith_wcsc_date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['yith_wcsc_date_to'] ) ) : '',
				'orderby'    => isset( $_GET['yith_wcsc_orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['yith_wcsc_orderby'] ) ) : 'ID',
				'order'      => isset( $_GET['yith_wcsc_order'] ) ? sanitize_text_field( wp_unslash( $_GET['yith_wcsc_order'] ) ) : 'DESC',
			);

			$commissions = $this->_stripe_connect_commissions->get_commissions( $commissions_args, true );

			$args = array(
				'current_status'    => $current_status,
				'oauth_link'        => $oauth_link,
				'button_text'       => $button_text,
				'button_class'      => $button_class,
				'count_commissions' => $this->_stripe_connect_commissions->get_commissions_count( $commissions_args ),
				'current_page'      => isset( $_GET['current_page'] ) ? abs( (int) $_GET['current_page'] ) : 1,
				'items_per_page'    => $this->_stripe_connect_commissions->items_per_page,
				'commissions'       => $commissions,
			);

			/** APPLY_FILTERS: yith_wcsc_connect_account_template_args
			*
			* Filter call to the template in my account section.
			*
			* @param $args Array of arguments to call the template.
			*/
			$args = apply_filters( 'yith_wcsc_connect_account_template_args', $args );

			// include template.
			yith_wcsc_get_template( 'stripe-connect-account', $args, 'frontend' );
		}

		/**
		 * Enqueue scripts required on My Account page
		 *
		 * @return void
		 */
		public function enqueue_scripts_for_account_page() {
			$debug_enabled   = defined( 'WP_DEBUG' ) ? WP_DEBUG : false;
			$prefix          = ! $debug_enabled ? '.min' : '';
			$js_dependencies = array(
				'jquery',
				'jquery-blockui',
			);
			$data_to_js      = array(
				'ajaxurl'                          => admin_url( 'admin-ajax.php' ),
				'disconnect_stripe_connect_action' => 'disconnect_stripe_connect',
				'oauth_link'                       => $this->_stripe_connect_api_handler->get_OAuth_link(),
				'messages'                         => array(

					/** APPLY_FILTERS: yith_wcsc_connect_with_stripe_button_text
					*
					* Filter the button text to connect to Stripe.
					*/
					'connect_to'    => apply_filters( 'yith_wcsc_connect_with_stripe_button_text', __( 'Connect with Stripe', 'yith-stripe-connect-for-woocommerce' ) ),

					/** APPLY_FILTERS: yith_wcsc_disconnect_from_stripe_button_text
					*
					* Filter the button text to disconnect to Stripe.
					*/
					'disconnect_to' => apply_filters( 'yith_wcsc_disconnect_from_stripe_button_text', __( 'Disconnect from Stripe', 'yith-stripe-connect-for-woocommerce' ) ),
				),
			);

			/** APPLY_FILTERS: yith_wcsc_account_page_script_data
			*
			* Filter data sent to the js.
			*
			* @param $data_to_js Array of the data sent to the js.
			*/
			$data_to_js = apply_filters( 'yith_wcsc_account_page_script_data', $data_to_js );

			wp_register_style( 'yith-wcsc-account-page-style', YITH_WCSC_ASSETS_URL . 'css/style-yith-sc-account.css', null, YITH_WCSC_VERSION );
			wp_register_script( 'yith-wcsc-account-page-script', YITH_WCSC_ASSETS_URL . 'js/script-yith-sc-account' . $prefix . '.js', $js_dependencies, YITH_WCSC_VERSION, true );

			wp_localize_script( 'yith-wcsc-account-page-script', 'yith_wcsc_account_page_script', $data_to_js );

			wp_enqueue_style( 'yith-wcsc-account-page-style' );
			wp_enqueue_script( 'yith-wcsc-account-page-script' );
		}

		/**
		 * Disconnect user from My Account page
		 *
		 * @return void
		 */
		public function disconnect() {
			$user_id = get_current_user_id();

			$result = $this->_stripe_connect_receivers->disconnect_by_user_id( $user_id );

			wp_send_json( $result );
		}

		/* === DEPRECATED === */

		/**
		 * Prints content for Stripe Connect account page
		 *
		 * @deprecated
		 * @return void
		 */
		public function stripe_connect_account_page() {
			_deprecated_function( '\YITH_Stripe_Connect_Frontend::stripe_connect_account_page', '2.1.1', '\YITH_Stripe_Connect_Frontend::print_account_page' );
			self::print_account_page();
		}
	}

}