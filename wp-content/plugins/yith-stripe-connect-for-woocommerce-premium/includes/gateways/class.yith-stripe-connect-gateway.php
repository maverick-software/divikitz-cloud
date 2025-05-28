<?php
/**
 * Main gateway class
 *
 * @class      YITH_Stripe_Connect_Gateway
 * @package    Yithemes
 * @since      Version 1.0.0
 * @author     Francisco Javier Mateo <francisco.mateo@yithemes.com>
 */

if ( ! defined( 'YITH_WCSC_PATH' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

use \Stripe\PaymentIntent;
use \Stripe\SetupIntent;

if ( ! class_exists( 'YITH_Stripe_Connect_Gateway' ) ) {
	/**
	 * Class YITH_Stripe_Connect_Gateway
	 *
	 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
	 */
	class YITH_Stripe_Connect_Gateway extends WC_Payment_Gateway_CC {

		/**
		 * The domain of this site used to identifier the website from Stripe.
		 *
		 * @var string $instance_url
		 */
		public $instance_url = '';

		/**
		 * Whether log is enabled or not
		 *
		 * @var bool
		 */
		public $log_enabled = false;

		/**
		 * Logger instance
		 *
		 * @var \WC_Logger
		 */
		public $log = false;

		/**
		 * Api handler class
		 *
		 * @var \YITH_Stripe_Connect_API_Handler
		 */
		public $api_handler = null;

		/**
		 * Constructor for the gateway.
		 */
		public function __construct() {
			$this->id         = YITH_Stripe_Connect::$gateway_id;
			$this->has_fields = false;

			/** APPLY_FILTERS: yith_wcsc_order_button_text
			*
			* Filter the text of the order button.
			*/
			$this->order_button_text  = apply_filters( 'yith_wcsc_order_button_text', _x( 'Proceed to Stripe Connect', 'Order button text on Stripe Connect Gateway', 'yith-stripe-connect-for-woocommerce' ) );
			$this->method_title       = _x( 'Stripe Connect', 'The Gateway title, no need translation :D', 'yith-stripe-connect-for-woocommerce' );
			$this->method_description = _x( 'Stripe Connect Gateway for WooCommerce', 'Stripe Connect Gateway description', 'yith-stripe-connect-for-woocommerce' );
			$this->instance_url       = preg_replace( '/http(s)?:\/\//', '', site_url() );
			$this->supports           = array(
				'products',
			);

			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			// Define user set variables.
			$this->title                = $this->get_option( 'label-title' );
			$this->description          = $this->get_option( 'label-description' );
			$this->description          = ! empty( $this->description ) ? $this->description : __( 'Stripe Connect Gateway', 'yith-stripe-connect-for-woocommerce' );  // @since 1.0.3
			$this->test_live            = 'yes' === $this->get_option( 'test-live', 'no' );
			$this->log_enabled          = 'yes' === $this->get_option( 'log', 'no' );
			$this->public_key           = $this->test_live ? $this->get_option( 'api-public-test-key' ) : $this->get_option( 'api-public-live-key' ); // Switch the plublic key between test and live mode.
			$this->credit_cards_logo    = $this->get_option( 'credit-cards-logo', array() );
			$this->show_name_on_card    = $this->get_option( 'show-name-on-card', 'no' );
			$this->save_cards           = $this->get_option( 'save-cards', 'no' );
			$this->enable_alt_flows     = $this->get_option( 'enable-alternative-flows', 'no' );
			$this->alt_flow             = $this->get_option( 'alternative-flow', 'none' );
			$this->view_transaction_url = 'https://dashboard.stripe.com/' . ( $this->test_live ? 'test/' : '' ) . 'payments/%s';

			if ( 'yes' === $this->save_cards ) {
				$this->supports[] = 'tokenization';
			}

			if ( $this->log_enabled ) {
				$this->log = new WC_Logger();
			}

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

			// token hooks - Update token when the customer edit them from My Account Page.
			add_filter( 'woocommerce_credit_card_form_fields', array( $this, 'credit_form_add_fields' ), 10, 2 );

			// scripts.
			add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
		}

		/**
		 * Gateway supports feature
		 *
		 * @param string $feature Feature to test.
		 * @return bool Whether method supports feature or not.
		 */
		public function supports( $feature ) {
			$supports = parent::supports( $feature );

			if ( 'tokenization' == $feature ) {
				$supports = ! $this->is_direct_charges();
			}

			$supports = apply_filters( 'yith_wcsc_gateway_supports', $supports, $feature );

			return $supports;
		}

		/**
		 * Load required scripts on Checkout page and wherever the gateway is needed
		 *
		 * @return void
		 */
		public function payment_scripts() {
			global $wp;

			if ( ! $this->is_available() || ! ( is_checkout() || is_wc_endpoint_url( 'add-payment-method' ) ) ) {
				return;
			}

			$debug_enabled   = defined( 'WP_DEBUG' ) ? WP_DEBUG : false;
			$prefix          = ! $debug_enabled ? '.min' : '';
			$js_dependencies = array( 'jquery', 'stripe-js', 'wc-credit-card-form' );

			wp_register_script( 'stripe-js', 'https://js.stripe.com/v3/', array( 'jquery' ), YITH_WCSC_VERSION, true );
			wp_register_script( 'yith-stripe-connect-js', YITH_WCSC_ASSETS_URL . 'js/script-yith-sc-checkout' . $prefix . '.js', $js_dependencies, YITH_WCSC_VERSION, true );

			if ( ( $commissions = $this->is_alt_flow() ) && 'direct_charges' === $this->alt_flow ) {
				$commission  = array_shift( $commissions );
				$receiver_id = $commission['receiver_id'];
				$receiver    = YITH_Stripe_Connect_Receivers()->get_receiver( $receiver_id );
				$account_id  = $receiver->stripe_id;
			}

			wp_localize_script(
				'yith-stripe-connect-js',
				'yith_stripe_connect_info',
				array(
					'public_key'        => $this->public_key,
					'is_checkout'       => is_checkout(),
					'account_id'        => isset( $account_id ) ? $account_id : false,
					'order'             => isset( $wp->query_vars['order-pay'] ) ? $wp->query_vars['order-pay'] : false,
					'refresh_intent'    => wp_create_nonce( 'refresh-intent' ),
					'ajaxurl'           => admin_url( 'admin-ajax.php' ),
					'card.name'         => __( 'A valid Name on Card is required.', 'yith-stripe-connect-for-woocommerce' ),
					'card.number'       => __( 'The credit card number seems to be invalid.', 'yith-stripe-connect-for-woocommerce' ),
					'card.cvc'          => __( 'The CVC number seems to be invalid.', 'yith-stripe-connect-for-woocommerce' ),
					'card.expire'       => __( 'The expiration date seems to be invalid.', 'yith-stripe-connect-for-woocommerce' ),
					'billing.fields'    => __( 'You have to add extra information to checkout.', 'yith-stripe-connect-for-woocommerce' ),
					/**
					 * APPLY_FILTERS: yith_wcsc_gateway_background_color
					 *
					 * Filters Stripe Elements checkout background color.
					 *
					 * @param string Default value: 'none'.
					 */
					'background_color'  => apply_filters( 'yith_wcsc_gateway_background_color', 'none' ),
					/**
					 * APPLY_FILTERS: yith_wcsc_gateway_font_size
					 *
					 * Filters Stripe Elements checkout font size.
					 *
					 * @param string Default value: '16px'.
					 */
					'font_size'         => apply_filters( 'yith_wcsc_gateway_font_size', '16px' ),
					/**
					 * APPLY_FILTERS: yith_wcsc_gateway_color
					 *
					 * Filters Stripe Elements checkout text color.
					 *
					 * @param string Default value: '#333'.
					 */
					'color'             => apply_filters( 'yith_wcsc_gateway_color', '#333' ),
					/**
					 * APPLY_FILTERS: yith_wcsc_gateway_font_family
					 *
					 * Filters Stripe Elements checkout font family.
					 *
					 * @param string Default value: 'sans-serif'.
					 */
					'font_family'       => apply_filters( 'yith_wcsc_gateway_font_family', 'sans-serif' ),
					/**
					 * APPLY_FILTERS: yith_wcsc_gateway_placeholder_color
					 *
					 * Filters Stripe Elements checkout text placeholder color.
					 *
					 * @param string Default value: '#000000'.
					 */
					'placeholder_color' => apply_filters( 'yith_wcsc_gateway_placeholder_color', '#000000' ),
				)
			);

			wp_register_style( 'yith-stripe-connect-css', YITH_WCSC_ASSETS_URL . 'css/style-yith-sc-checkout.css', null, YITH_WCSC_VERSION );

			wp_enqueue_script( 'yith-stripe-connect-js' );
			wp_enqueue_style( 'yith-stripe-connect-css' );
		}

		/**
		 * Handling payment and processing the order.
		 *
		 * @param int $order_id Order id.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		public function process_payment( $order_id ) {
			$order                = wc_get_order( $order_id );
			$this->_current_order = $order;
			$this->log( 'info', 'Generating payment form for order ' . $order->get_order_number() . '.' );

			return $this->process_standard_payment();
		}

		/**
		 * Performs the payment on Stripe
		 *
		 * @param \WC_Order|null $order Order to pay.
		 *
		 * @return bool|WP_Error
		 * @throws Stripe\Exception\ApiErrorException|Exception Throws this exception when an error occurs with API call.
		 * @since 1.0.0
		 */
		public function pay( $order = null ) {
			$order_id     = $order->get_id();
			$order_number = $order->get_order_number();

			// Initializate SDK and set private key.
			$this->init_stripe_connect_api();

			// get amount.
			$amount = $order->get_total();

			if ( 0 == $amount ) {
				// Payment complete.
				$order->payment_complete();

				return true;
			}

			// retrieve payment intent.
			$intent = $this->get_intent( $order );

			if ( ! $intent || 0 === strpos( $intent->id, 'seti' ) ) {
				$this->log( 'error', 'No intent found for order ' . $order ? $order_id : 'N/A' );

				return new WP_Error( 'stripe_error', __( 'Sorry, There was an error while processing payment; please, try again', 'yith-stripe-connect-for-woocommerce' ) );
			}

			if ( 'requires_confirmation' == $intent->status ) {
				$intent->confirm();
			}

			if ( 'requires_action' == $intent->status ) {

				/** DO_ACTION: yith_wcstripe_intent_requires_action
				*
				* Adds an action when attmpting the payment.
				*
				* @param $intent Attempt obj from current order.
				* @param $order  Order obj.
				*/
				do_action( 'yith_wcstripe_intent_requires_action', $intent, $order );

				$this->log( 'info', 'Intent requires actions ' . $intent->id );

				return new WP_Error( 'stripe_error', __( 'Please, validate your payment method before proceeding further; in order to do this, refresh the page and proceed at checkout as usual', 'yith-stripe-connect-for-woocommerce' ) );
			} elseif ( ! in_array( $intent->status, array( 'succeeded', 'requires_capture' ) ) ) {
				$this->log( 'error', sprintf( 'Intent doesn\'t have a valid status %s (%s)', $intent->id, $intent->status ) );

				return new WP_Error( 'stripe_error', __( 'Sorry, There was an error while processing payment; please, try again', 'yith-stripe-connect-for-woocommerce' ) );
			}

			// retrieve api options.
			$api_options = $this->_retrieve_api_option();

			// register intent for the order.
			$order->update_meta_data( 'intent_id', $intent->id );

			// update intent data.
			$this->api_handler->update_intent(
				$intent->id,
				array(

					/** APPLY_FILTERS: yith_wcsc_charge_description
					*
					* Filter the defaul plugin description in the charges.
					*
					* @param string sprintf       Default plugin text.
					* @param string $blog_name    Blog name.
					* @param string $order_number Order number.
					* @param string $order_id     Order ID.
					*/
					// translators: 1. Blog name. 2. Order number.
					'description'    => apply_filters( 'yith_wcsc_charge_description', sprintf( __( '%1$s - Order %2$s', 'yith-stripe-connect-for-woocommerce' ), esc_html( get_bloginfo( 'name' ) ), $order_number ), esc_html( get_bloginfo( 'name' ) ), $order_number, $order_id ),

					/** APPLY_FILTERS: yith_wcstripe_connect_metadata
					*
					* Filter metadata sent to Stripe.
					*
					* @param array $order_id , $order_email, $instance.
					* @param string 'carge' by default.
					*/
					'metadata'       => apply_filters(
						'yith_wcstripe_connect_metadata',
						array(
							'order_id'    => $order_id,
							'order_email' => $order->get_billing_email(),
							'instance'    => $this->instance_url,
						),
						'charge'
					),
					'transfer_group' => $order_id,
				),
				$api_options
			);

			// retrieve charge to use for next steps.
			$charge = $intent->latest_charge;
			$charge = is_object( $charge ) ? $charge : $this->api_handler->retrieve_charge( $charge, $api_options );

			// attach payment method to customer.
			if ( ! $this->is_direct_charges( $order_id ) ) {
				$customer       = $this->get_customer( $order );
				$this->customer = $customer ? $customer->id : '';

				$order->update_meta_data( 'yith_stripe_connect_customer_id', $customer->id );
			}

			// save card token.
			if ( ! empty( $customer ) ) {
				$token = $this->save_token( $intent->payment_method );

				if ( $token ) {
					$order->add_payment_token( $token );
					$this->token = $intent->payment_method;
				}
			}

			// register flag if commissions where registered during checkout.
			if ( $this->is_alt_flow( $order_id ) ) {
				$order->update_meta_data( '_yith_wcstripe_alt_flow', true );
			}

			// Payment complete.
			$is_payment_complete = $order->payment_complete( $charge->id );

			if ( $is_payment_complete ) {

				/** DO_ACTION: yith_wcsc_payment_complete
				*
				* Adds an action when compliting the payment.
				*
				* @param $order_id  ID of the order.
				* @param $charge_id ID of the charge.
				*/
				do_action( 'yith_wcsc_payment_complete', $order_id, $charge->id );
			}

			// Add order note.
			// translators: 1. Stripe charge id.
			$order->add_order_note( sprintf( __( 'Stripe Connect payment approved (ID: %s)', 'yith-stripe-connect-for-woocommerce' ), $charge->id ) );

			// save all changes we done to the order.
			$order->save();

			// Remove cart.
			WC()->cart->empty_cart();

			// delete session.
			$this->delete_session_intent();

			// Return thank you page redirect.
			return true;
		}

		/**
		 * Handling payment and processing the order.
		 *
		 * @param WC_Order $order Order object.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		protected function process_standard_payment( $order = null ) {
			if ( empty( $order ) ) {
				$order = $this->_current_order;
			}

			try {

				// Initializate SDK and set private key.
				$this->init_stripe_connect_api();

				// retrieve payment intent.
				$intent = $this->get_intent( $order );

				// no intent yet; return error.
				if ( ! $intent || ! $order || 0 === strpos( $intent->id, 'seti' ) && (float) $order->get_total() ) {
					throw new Exception( __( 'Sorry, There was an error while processing payment; please, try again', 'yith-stripe-connect-for-woocommerce' ), null );
				}

				// intent refers to another transaction: return error.
				if ( $order->get_id() != $intent->metadata->order_id && yith_wcsc_get_cart_hash() != $intent->metadata->cart_hash ) {
					throw new Exception( __( 'Sorry, There was an error while processing payment; please, try again', 'yith-woocommerce-stripe' ), null );
				}

				$payment_method = isset( $_POST['stripe_connect_payment_method'] ) ? sanitize_text_field( $_POST['stripe_connect_payment_method'] ) : false;

				if ( ! $payment_method && isset( $_POST['wc-yith-stripe-connect-payment-token'] ) && 'new' !== $_POST['wc-yith-stripe-connect-payment-token'] ) {
					$token_id = intval( $_POST['wc-yith-stripe-connect-payment-token'] );
					$token    = WC_Payment_Tokens::get( $token_id );

					if ( $token && $token->get_user_id() == get_current_user_id() && $token->get_gateway_id() == $this->id ) {
						$payment_method = $token->get_token();
					}
				}

				// it intent is missing payment method, or requires update, proceed with update.
				if (
					( 'requires_payment_method' == $intent->status && $payment_method ) ||
					(
						( yith_wcsc_get_amount( $order->get_total(), $order->get_currency() ) != $intent->amount || strtolower( $order->get_currency() ) != $intent->currency ) &&
						! in_array( $intent->status, array( 'requires_action', 'requires_capture', 'succeeded', 'canceled' ) )
					)
				) {
					// updates session intent.
					$intent = $this->update_session_intent( $payment_method, $order->get_id() );
				}

				// if intent is still missing payment method, return an error.
				if ( 'requires_payment_method' == $intent->status ) {
					throw new Exception( __( 'No payment method could be applied to this payment; please try again selecting another payment method', 'yith-stripe-connect-for-woocommerce' ) );
				}

				// intent requires confirmation; try to confirm it.
				if ( 'requires_confirmation' == $intent->status ) {
					$intent->confirm();
				}

				// register intent for the order.
				$order->update_meta_data( 'intent_id', $intent->id );

				// confirmation requires additional action; return to customer.
				if ( 'requires_action' == $intent->status ) {
					$order->save();

					// manual confirm after checkout.
					$this->_current_intent_secret = $intent->client_secret;

					return array(
						'result'   => 'success',
						'redirect' => $this->get_return_url( $order ),
					);
				}

				// pay.
				$response = $this->pay( $order );

				if ( true === $response ) {
					$response = array(
						'result'   => 'success',
						'redirect' => $this->get_return_url( $order ),
					);

				} elseif ( is_a( $response, 'WP_Error' ) ) {
					throw Stripe\Exception\UnknownApiErrorException::factory( $response->get_error_message( 'stripe_error' ) );
				}

				return $response;

			} catch ( Stripe\Exception\UnknownApiErrorException $e ) {
				$body    = $e->getJsonBody();
				$message = $e->getMessage();

				if ( $body ) {
					$err = $body['error'];
					if ( isset( $this->errors[ $err['code'] ] ) ) {
						$message = $this->errors[ $err['code'] ];
					}

					$this->log( 'info', 'Stripe Error: ' . $e->getHttpStatus() . ' - ' . print_r( $e->getJsonBody(), true ) );

					// add order note.
					$order->add_order_note( 'Stripe Error: ' . $e->getHttpStatus() . ' - ' . $e->getMessage() );

					// add block if there is an error on card.
					if ( 'card_error' == $err['type'] ) {
						WC()->session->refresh_totals = true;
					}
				}

				wc_add_notice( $message, 'error' );

				return array(
					'result'   => 'fail',
					'redirect' => '',
				);
			} catch ( Exception $e ) {
				wc_add_notice( $e->getMessage(), 'error' );

				return array(
					'result'   => 'fail',
					'redirect' => '',
				);
			}
		}

		/**
		 * Retrieve source selected for current subscription
		 *
		 * @return string
		 */
		protected function get_source() {
			$card_id = ( isset( $_POST['wc-yith-stripe-connect-payment-token'] ) && 'new' != $_POST['wc-yith-stripe-connect-payment-token'] ) ? sanitize_text_field( wp_unslash( $_POST['wc-yith-stripe-connect-payment-token'] ) ) : false;

			if ( $card_id ) {
				$token = WC_Payment_Tokens::get( $card_id );
				if ( $token && $token->get_user_id() === get_current_user_id() ) {
					$card_id = $token->get_token();
				}
			}

			return $card_id;
		}

		/**
		 * Get token card from post
		 *
		 * @access protected
		 * @return string
		 * @author Francisco Javier Mateo
		 */
		protected function get_token() {
			$card_id = $this->get_source();

			if ( ! $card_id ) {
				if ( isset( $_POST['stripe_connect_token'] ) ) {
					$card_id = sanitize_text_field( wp_unslash( $_POST['stripe_connect_token'] ) );
				} else {
					return 'new';
				}
			}

			/** APPLY_FILTERS: yith_stripe_connect_selected_card
			*
			* Filter when getting the token.
			*
			* @param $card_id Default card ID.
			*/
			return apply_filters( 'yith_stripe_connect_selected_card', $card_id );
		}

		/* === PAYMENT INTENT MANAGEMENT === */

		/**
		 * Retrieve intent for current operation; if none, creates one
		 *
		 * @param \WC_Order|bool $order Current order.
		 *
		 * @return \Stripe\PaymentIntent|bool Payment intent or false on failure
		 */
		public function get_intent( $order = false ) {
			$intent_id = false;

			// check order first.
			if ( $order ) {
				$intent_id = $order->get_meta( 'intent_id', true );

				if ( empty( $this->_current_account ) && 'direct_charges' == $this->alt_flow && $commissions = $this->is_alt_flow( $order->get_id() ) ) {
					$commission  = array_shift( $commissions );
					$receiver_id = $commission['receiver_id'];
					$receiver    = YITH_Stripe_Connect_Receivers()->get_receiver( $receiver_id );
					$account_id  = $receiver->stripe_id;

					$this->_current_account = $account_id;
				}
			}

			// then $_POST.
			if ( ! $intent_id && isset( $_POST['stripe_connect_intent'] ) ) {
				$intent_id = sanitize_text_field( wp_unslash( $_POST['stripe_connect_intent'] ) );
			}

			// and finally session.
			if ( ! $intent_id && $order ) {
				$intent    = $this->get_session_intent( $order->get_id() );
				$intent_id = $intent ? $intent->id : false;
			}

			if ( ! $intent_id ) {
				return false;
			}

			// retrieve intent from id.
			if ( ! isset( $intent ) ) {
				$intent = $this->api_handler->get_correct_intent( $intent_id, $this->_retrieve_api_option() );
			}

			if ( ! $intent ) {
				return false;
			}

			return $intent;
		}

		/**
		 * Get intent for current session
		 *
		 * @param int|bool $order_id Order id when relevant.
		 *
		 * @return \Stripe\PaymentIntent|bool Session payment intent or false on failure
		 */
		public function get_session_intent( $order_id = false ) {
			global $wp;

			// Initialize SDK and set private key.
			$this->init_stripe_connect_api();

			$session = WC()->session;

			if ( ! $session ) {
				return false;
			}

			$intent_id = $session->get( 'yith_stripe_connect_intent' );

			if ( ! $order_id && is_checkout_pay_page() ) {
				$order_id = isset( $wp->query_vars['order-pay'] ) ? $wp->query_vars['order-pay'] : false;
			}

			if ( $order_id ) {
				$order       = wc_get_order( $order_id );
				$currency    = strtolower( $order->get_currency() );
				$total       = yith_wcsc_get_amount( $order->get_total(), $currency );

				/** APPLY_FILTERS: yith_wcsc_charge_description
				*
				* Filter the defaul plugin description in the charges.
				*
				* @param string sprintf       Default plugin text.
				* @param string $blog_name    Blog name.
				* @param string $order_number Order number.
				* @param string $order_id     Order ID.
				*/
				// translators: 1. Blog name. 2. Order number.
				$description = apply_filters( 'yith_wcsc_charge_description', sprintf( __( '%1$s - Order %2$s', 'yith-stripe-connect-for-woocommerce' ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number(), $order->get_id() );
				$metadata    = array(
					'cart_hash'   => '',
					'order_id'    => $order_id,
					'order_email' => $order->get_billing_email(),
				);
			} else {
				$cart = WC()->cart;
				$cart && $cart->calculate_totals();
				$total       = $cart ? yith_wcsc_get_amount( $cart->total ) : 0;
				$currency    = strtolower( get_woocommerce_currency() );
				// translators: 1. Cart hash.
				$description = $cart ? sprintf( __( 'Payment intent for cart %s', 'yith-stripe-connect-for-woocommerce' ), $cart->get_cart_hash() ) : '';
				$metadata    = array(
					'cart_hash'   => $cart ? $cart->get_cart_hash() : '',
					'order_id'    => '',
					'order_email' => '',
				);
			}

			$is_checkout = is_checkout() || ( defined( 'YITH_STRIPE_CONNECT_DOING_CHECKOUT' ) && YITH_STRIPE_CONNECT_DOING_CHECKOUT );

			if ( ! $total || ! $is_checkout ) {
				return $this->get_session_setup_intent( $order_id );
			}

			// if total don't match requirements, skip intent creation.
			if ( ! $total || $total > 99999999 ) {
				$this->delete_session_intent();

				return false;
			}

			if ( $intent_id ) {
				$intent = $this->api_handler->get_intent( $intent_id, $this->_retrieve_api_option() );

				if ( $intent ) {

					// if intent isn't longer available, generate a new one.
					if ( ! in_array( $intent->status, array( 'requires_payment_method', 'requires_confirmation', 'requires_action' ) ) && ! defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
						$this->delete_session_intent( $intent );

						return $this->create_session_intent( array( 'order_id' => $order_id ) );
					}

					if ( $intent->amount != $total || $intent->currency != $currency ) {
						$intent = $this->api_handler->update_intent(
							$intent->id,
							array(
								'amount'      => $total,
								'currency'    => $currency,
								'description' => $description,

								/** APPLY_FILTERS: yith_wcstripe_connect_metadata
								*
								* Filter metadata sent to Stripe.
								*
								* @param array  $instance.
								* @param string 'create_payment_intent' by default.
								*/
								'metadata'    => apply_filters( 'yith_wcstripe_connect_metadata', array_merge( array( 'instance' => $this->instance_url ), $metadata ), 'create_payment_intent' ),
							),
							$this->_retrieve_api_option()
						);
					}

					return $intent;
				}
			}

			return $this->create_session_intent( array( 'order_id' => $order_id ) );
		}

		/**
		 * Get setup intent for current session
		 *
		 * @param int $order_id Id of the order being paid, if any.
		 *
		 * @return \Stripe\SetupIntent|bool Session setup intent or false on failure
		 */
		public function get_session_setup_intent( $order_id = false ) {
			$session   = WC()->session;
			$intent_id = $session->get( 'yith_stripe_connect_setup_intent' );

			if ( $intent_id ) {
				$intent = $this->api_handler->get_setup_intent( $intent_id );

				if ( $intent ) {
					// if intent isn't longer available, generate a new one.
					if ( ! in_array( $intent->status, array( 'requires_payment_method', 'requires_confirmation', 'requires_action' ) ) ) {
						$this->delete_session_setup_intent( $intent );

						return $this->create_session_setup_intent( array( 'order_id' => $order_id ) );
					}

					return $intent;
				}
			}

			return $this->create_session_setup_intent( array( 'order_id' => $order_id ) );
		}

		/**
		 * Create a new intent for current session
		 *
		 * @param array $args array of argument to use for intent creation. Following a list of accepted params<br/>
		 *              [
		 *              'amount' // total to pay
		 *              'currency' // order currency
		 *              'description' // transaction description; will be modified after confirm
		 *              'metadata' // metadata for the transaction; will be modified after confirm
		 *              'setup_future_usage' // default to 'off_session', to reuse in renews when needed
		 *              'customer' // stripe customer id for current user, if any
		 *              ].
		 *
		 * @return \Stripe\PaymentIntent|bool Generate payment intent, or false on failure
		 */
		public function create_session_intent( $args = array() ) {
			global $wp;

			$customer_id = false;
			$order_id    = false;

			if ( is_user_logged_in() ) {
				$customer_id = YITH_Stripe_Connect_Customer()->get_customer_id( get_current_user_id() );
			}

			if ( isset( $args['order_id'] ) ) {
				$order_id = $args['order_id'];
				unset( $args['order_id'] );
			} elseif ( is_checkout_pay_page() ) {
				$order_id = isset( $wp->query_vars['order-pay'] ) ? $wp->query_vars['order-pay'] : false;
			}

			if ( $order_id ) {
				$order       = wc_get_order( $order_id );
				$currency    = $order->get_currency();
				$total       = yith_wcsc_get_amount( $order->get_total(), $currency );

				/** APPLY_FILTERS: yith_wcsc_charge_description
				*
				* Filter the defaul plugin description in the charges.
				*
				* @param string sprintf       Default plugin text.
				* @param string $blog_name    Blog name.
				* @param string $order_number Order number.
				* @param string $order_id     Order ID.
				*/
				// translators: 1. Blog name 2. Order number.
				$description = apply_filters( 'yith_wcsc_charge_description', sprintf( __( '%1$s - Order %2$s', 'yith-stripe-connect-for-woocommerce' ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number(), $order->get_id() );
				$metadata    = array(
					'order_id'    => $order_id,
					'order_email' => $order->get_billing_email(),
					'cart_hash'   => '',
				);
			} else {
				$cart        = WC()->cart;
				$total       = $cart ? yith_wcsc_get_amount( $cart->get_total() ) : 0;
				$currency    = strtolower( get_woocommerce_currency() );
				// translators: 1. Cart hash.
				$description = $cart ? sprintf( __( 'Payment intent for cart %s', 'yith-stripe-connect-for-woocommerce' ), $cart->get_cart_hash() ) : '';
				$metadata    = array(
					'cart_hash'   => $cart ? $cart->get_cart_hash() : '',
					'order_id'    => '',
					'order_email' => '',
				);
			}

			// process alternative payment flows.
			if ( $commissions = $this->is_alt_flow( $order_id ) ) {
				$object      = isset( $order ) ? $order : $cart;
				$commission  = current( $commissions );
				$receiver_id = $commission['receiver_id'];
				$receiver    = YITH_Stripe_Connect_Receivers()->get_receiver( $receiver_id );
				$account_id  = $receiver->stripe_id;
				$currency    = method_exists( $object, 'get_currency' ) ? $object->get_currency() : get_woocommerce_currency();

				/** APPLY_FILTERS: yith_wcsc_commission_fee_base
				*
				* Filter the default fee base.
				*
				* @param string $subtotal       Object Subtotal.
				* @param string $total_discount Object total discount.
				* @param string $objet          Order/Cart.
				*/
				$fee_base       = yith_wcsc_get_amount( apply_filters( 'yith_wcsc_commission_fee_base', $object->get_subtotal() - $object->get_discount_total(), $object ) );
				$commission_amt = yith_wcsc_get_amount( array_sum( wp_list_pluck( $commissions, 'commission' ) ), $currency );
				$fee            = max( 0, $fee_base - $commission_amt );

				switch ( $this->alt_flow ) {
					case 'direct_charges':
						if ( $fee ) {
							$args['application_fee_amount'] = $fee;
						}

						$this->_current_account = $account_id;
						break;
					case 'destination_charges':
						if ( $fee ) {
							$args['application_fee_amount'] = $fee;
						}

						$args['on_behalf_of'] = $account_id;
						$args['transfer_data'] = array(
							'destination' => $account_id,
						);
						break;
				}
			}

			// Guest user.
			if ( ! $customer_id && $order_id ) {
				$order    = wc_get_order( $order_id );
				$customer = $this->get_customer( $order );
				if ( $customer ) {
					$customer_id = $customer->id;
				}
			}

			/** APPLY_FILTERS: yith_stripe_connect_create_payment_intent
			*
			* Filter the payment intent.
			*/
			$defaults = apply_filters(
				'yith_stripe_connect_create_payment_intent',
				array_merge(
					array(
						'amount'              => $total,
						'currency'            => $currency,
						'description'         => $description,

						/** APPLY_FILTERS: yith_wcstripe_connect_metadata
						*
						* Filter metadata sent to Stripe.
						*
						* @param array  $instance.
						* @param string 'create_payment_intent' by default.
						*/
						'metadata'            => apply_filters( 'yith_wcstripe_connect_metadata', array_merge( array( 'instance' => $this->instance_url ), $metadata ), 'create_payment_intent' ),
						'setup_future_usage'  => 'off_session',
						'capture_method'      => 'automatic',
						'confirmation_method' => 'manual',
					),
					$customer_id && ! $this->is_direct_charges() ? array( 'customer' => $customer_id ) : array()
				),
				$order_id
			);

			$args = wp_parse_args( $args, $defaults );

			// Initialize SDK and set private key.
			$this->init_stripe_connect_api();

			$session = WC()->session;

			try {
				$intent = $this->api_handler->create_intent( $args, $this->_retrieve_api_option() );
			} catch ( Exception $e ) {
				return false;
			}

			if ( ! $intent ) {
				return false;
			}

			if ( $session ) {
				$session->set( 'yith_stripe_connect_intent', $intent->id );
			}

			return $intent;
		}

		/**
		 * Create a new setup intent for current session
		 *
		 * @param array $args array of argument to use for intent creation. Following a list of accepted params<br/>
		 *              [
		 *              'metadata' // metadata for the transaction; will be modified after confirm
		 *              'usage' // default to 'off_session', to reuse in renews when needed
		 *              'customer' // stripe customer id for current user, if any
		 *              ].
		 *
		 * @return \Stripe\PaymentIntent|bool Generate payment intent, or false on failure
		 */
		public function create_session_setup_intent( $args = array() ) {
			$customer_id = false;

			if ( isset( $args['order_id'] ) ) {
				$order_id = $args['order_id'];
				unset( $args['order_id'] );
			} elseif ( is_checkout_pay_page() ) {
				$order_id = isset( $wp->query_vars['order-pay'] ) ? $wp->query_vars['order-pay'] : false;
			}

			if ( is_user_logged_in() ) {
				$customer_id = YITH_Stripe_Connect_Customer()->get_customer_id( get_current_user_id() );
			}

			/** APPLY_FILTERS: yith_wcstripe_connect_create_payment_intent
			*
			* Filter the payment intent.
			*/
			$defaults = apply_filters(
				'yith_wcstripe_connect_create_payment_intent',
				array_merge(
					array(

						/** APPLY_FILTERS: yith_wcstripe_connect_metadata
						*
						* Filter metadata sent to Stripe.
						*
						* @param array  $instance.
						* @param string 'create_setup_intent' by default.
						*/
						'metadata' => apply_filters(
							'yith_wcstripe_connect_metadata',
							array_merge(
								array(
									'instance' => $this->instance_url,
								),
								isset( $order_id ) ? array( 'order_id' => $order_id ) : array()
							),
							'create_setup_intent'
						),
						'usage'    => 'off_session',
					),
					$customer_id ? array(
						'customer' => $customer_id,
					) : array()
				)
			);

			$args = wp_parse_args( $args, $defaults );

			// Initialize SDK and set private key.
			$this->init_stripe_connect_api();

			$session = WC()->session;

			$intent = $this->api_handler->create_setup_intent( $args );

			if ( ! $intent ) {
				return false;
			}

			$session->set( 'yith_stripe_connect_setup_intent', $intent->id );

			return $intent;
		}

		/**
		 * Update session intent, registering new cart total and currency, and configuring a payment method if needed
		 *
		 * @param int|bool $token Selected token id, or null if new payment method is used.
		 * @param int|bool $order Current order id, or null if cart should be used.
		 *
		 * @return PaymentIntent|SetupIntent|bool Updated intent, or false on failure
		 * @throws Exception When a problem occurs with intent handling.
		 */
		public function update_session_intent( $token = false, $order = false ) {
			// retrieve intent; this will automatically update total and currency.
			$intent = $this->get_session_intent( $order );

			if ( ! $intent ) {
				throw new Exception( __( 'There was an error with payment process; please try again later', 'yith-stripe-connect-for-woocommerce' ) );
			}

			if ( ! $token ) {
				return $intent;
			}

			// prepare payment method to use for update.
			if ( is_int( $token ) ) {
				if ( ! is_user_logged_in() ) {
					throw new Exception( __( 'You must login before using a registered card', 'yith-stripe-connect-for-woocommerce' ) );
				}

				$token = WC_Payment_Tokens::get( $token );

				if ( ! $token || $token->get_user_id() != get_current_user_id() ) {
					throw new Exception( __( 'The card you\'re trying to use isn\'t valid; please, try again with another payment method', 'yith-stripe-connect-for-woocommerce' ) );
				}

				$payment_method = $token->get_token();
			} elseif ( is_string( $token ) ) {
				$payment_method = $token;
			}

			// if a payment method was provided, try to bind it to payment intent.
			if ( $payment_method ) {
				$result = $this->api_handler->update_correct_intent(
					$intent->id,
					array(
						'payment_method' => $payment_method,
						'transfer_group' => $order,
					),
					$this->_retrieve_api_option()
				);

				// check if update was successful.
				if ( ! $result ) {
					throw new Exception( __( 'The card you\'re trying to use isn\'t valid; please, try again with another payment method', 'yith-stripe-connect-for-woocommerce' ) );
				}

				// update intent object that will be returned.
				$intent = $result;
			}

			return $intent;
		}

		/**
		 * Removes intent from current session
		 * Method is intended to cancel session, but will also cancel PaymentIntent on Stripe, if object is passed as param
		 *
		 * @param \Stripe\PaymentIntent|bool $intent Payment intent to cancel, or false if it is not required.
		 *
		 * @return void
		 */
		public function delete_session_intent( $intent = false ) {
			// Initialize SDK and set private key.
			$this->init_stripe_connect_api();

			$session = WC()->session;
			$session->set( 'yith_stripe_connect_intent', '' );

			if ( $intent && isset( $intent->status ) && ! in_array( $intent->status, array( 'succeeded', 'cancelled' ) ) ) {
				try {
					$intent->cancel();
				} catch ( Exception $e ) {
					return;
				}
			}
		}

		/**
		 * Removes intent from current session
		 * Method is intended to cancel session, but will also cancel SetupIntent on Stripe, if object is passed as param
		 *
		 * @param \Stripe\setupIntent|bool $intent Setup intent to cancel, or false if it is not required.
		 *
		 * @return void
		 */
		public function delete_session_setup_intent( $intent = false ) {
			// Initialize SDK and set private key.
			$this->init_stripe_connect_api();

			$session = WC()->session;
			$session->set( 'yith_stripe_connect_setup_intent', '' );

			if ( $intent && isset( $intent->status ) && ! in_array( $intent->status, array( 'succeeded', 'cancelled' ) ) ) {
				try {
					$intent->cancel();
				} catch ( Exception $e ) {
					return;
				}
			}
		}

		/* === TOKENS HANDLING === */

		/**
		 * Get customer of Stripe account or create a new one if not exists
		 *
		 * @param WC_Order $order Order to use to retrieve customer.
		 *
		 * @return \Stripe\Customer|bool
		 *
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 * @since  1.1.0
		 */
		public function get_customer( $order ) {

			$this->init_stripe_connect_api();

			if ( is_int( $order ) ) {
				$order = wc_get_order( $order );
			}

			$current_order_id = ( isset( $this->_current_order ) && $this->_current_order instanceof WC_Order ) ? $this->_current_order->get_id() : false;
			$order_id         = $order->get_id();

			// in case we're using direct charges, skip customer creation, as we can't use record stored on connected account.
			if ( $this->is_direct_charges( $order_id ) ) {
				return false;
			}

			if ( $current_order_id == $order_id && ! empty( $this->_current_customer ) ) {
				return $this->_current_customer;
			}

			$user    = $order->get_user();
			$user_id = $order->get_user_id();

			if ( ! $user && is_user_logged_in() ) {
				$user    = wp_get_current_user();
				$user_id = $user->ID;
			}

			$local_customer = $user_id ? YITH_Stripe_Connect_Customer()->get_usermeta_info( $user_id ) : false;

			try {
				$customer = isset( $local_customer['id'] ) ? $this->api_handler->get_customer( $local_customer['id'], $this->_retrieve_api_option() ) : false;
			} catch ( Exception $e ) {
				$customer = false;
			}

			// get existing.
			if ( $customer ) {
				if ( $current_order_id == $order_id ) {
					$this->_current_customer = $customer;
				}

				return $customer;
			}

			// create new one.

			$billing_email = $order->get_billing_email();
			$billing_first_name = $order->get_billing_first_name();
			$billing_last_name = $order->get_billing_last_name();
			$billing_full_name = trim( "{$billing_first_name} {$billing_last_name}" );

			if ( ! $billing_email ) {
				$billing_email = $user ? $user->user_email : false;
			}

			if ( ! $billing_email ) {
				return false;
			}

			if ( $user ) {
				$description = $user->user_login . ' (#' . $user_id . ' - ' . $user->user_email . ')';
			} else {
				$description = __( 'Guest', 'yith-stripe-connect-for-woocommerce' ) . ' (N/A  - ' . $billing_email . ')';
			}

			$description .= ' ' . $billing_full_name;

			$params = array(
				'email'       => $billing_email,
				'description' => trim( $description ),

				/** APPLY_FILTERS: yith_wcstripe_connect_metadata
				*
				* Filter metadata sent to Stripe.
				*
				* @param array  $user_id, $instance.
				* @param string 'create_customer' by default.
				*/
				'metadata'    => apply_filters(
					'yith_wcstripe_connect_metadata',
					array(
						'user_id'  => $user ? $user_id : false,
						'instance' => $this->instance_url,
					),
					'create_customer'
				),
			);

			try {
				$customer    = $this->api_handler->create_customer( $params, $this->_retrieve_api_option() );
				$this->token = $customer->invoice_settings->default_payment_method;
			} catch ( Exception $e ) {
				return false;
			}

			// update user meta.
			if ( is_user_logged_in() && ! $this->is_direct_charges() ) {
				YITH_Stripe_Connect_Customer()->update_usermeta_info(
					$user_id,
					array(
						'id'             => $customer->id,
						'default_source' => $customer->invoice_settings->default_payment_method,
					)
				);
			}

			if ( $current_order_id == $order_id ) {
				$this->_current_customer = $customer;
			}

			return $customer;

		}

		/**
		 * Save the token on db.
		 *
		 * @param string $payment_method_id Payment method id.
		 *
		 * @return bool|WC_Payment_Token|WC_Payment_Token_CC
		 * @throws Exception When something fails with API handling.
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 * @since  1.1.0
		 */
		public function save_token( $payment_method_id = null ) {

			if ( ! is_user_logged_in() || 'yes' !== $this->save_cards || $this->is_direct_charges() ) {
				return false;
			}

			$this->init_stripe_connect_api();

			$user           = wp_get_current_user();
			$local_customer = YITH_Stripe_Connect_Customer()->get_usermeta_info( $user->ID );
			$customer       = ! empty( $local_customer['id'] ) ? $this->api_handler->get_customer( $local_customer['id'] ) : false;
			$payment_method = $this->api_handler->get_payment_method( $payment_method_id );

			if ( ! $payment_method ) {
				return false;
			}

			if ( $customer && $payment_method->customer != $customer->id ) {
				try {
					$payment_method->attach(
						array(
							'customer' => $customer->id,
						)
					);
				} catch ( Exception $e ) {
					return false;
				}

				$this->api_handler->update_customer(
					$customer,
					array(
						'invoice_settings' => array(
							'default_payment_method' => $payment_method_id,
						),
					)
				);

				$customer->sources->data[] = $payment_method->card;
			} elseif ( ! $customer ) {
				$params = array(
					'payment_method' => $payment_method_id,
					'email'          => $user->billing_email,
					'description'    => substr( $user->user_login . ' (#' . $user->ID . ' - ' . $user->user_email . ') ' . $user->billing_first_name . ' ' . $user->billing_last_name, 0, 350 ),

					/** APPLY_FILTERS: yith_wcstripe_connect_metadata
					*
					* Filter metadata sent to Stripe.
					*
					* @param array  $user_id, $instance.
					* @param string 'create_customer' by default.
					*/
					'metadata'       => apply_filters(
						'yith_wcstripe_metadata',
						array(
							'user_id'  => $user->ID,
							'instance' => $this->instance_url,
						),
						'create_customer'
					),
				);

				$customer = $this->api_handler->create_customer( $params );
			}

			$already_registered        = false;
			$already_registered_tokens = WC_Payment_Tokens::get_customer_tokens( $user->ID, $this->id );
			$registered_token          = false;

			if ( ! empty( $already_registered_tokens ) ) {
				foreach ( $already_registered_tokens as $registered_token ) {
					/**
					 * @var $registered_token \WC_Payment_Token
					 */
					$registered_fingerprint = $registered_token->get_meta( 'fingerprint', true );

					if ( $registered_fingerprint == $payment_method->card->fingerprint || $registered_token->get_token() == $payment_method_id ) {
						$already_registered = true;
						break;
					}
				}
			}

			/** APPLY_FILTERS: yith_wcstripe_allow_save_different_cards
			*
			* Filter to allow save different cards.
			*/
			if ( apply_filters( 'yith_wcstripe_allow_save_different_cards' , ! $already_registered ) ) {
				// save card.
				$token = new WC_Payment_Token_CC();
				$token->set_token( $payment_method_id );
				$token->set_gateway_id( $this->id );
				$token->set_user_id( $user->ID );
				$token->set_card_type( strtolower( $payment_method->card->brand ) );
				$token->set_last4( $payment_method->card->last4 );
				$token->set_expiry_month( ( str_pad( $payment_method->card->exp_month, 2, '0', STR_PAD_LEFT ) ) );
				$token->set_expiry_year( $payment_method->card->exp_year );
				$token->set_default( true );
				$token->add_meta_data( 'fingerprint', $payment_method->card->fingerprint );
				$token->add_meta_data( 'confirmed', true );

				if ( ! $token->save() ) {
					throw Stripe\Exception\UnknownApiErrorException::factory( __( 'Credit card info not valid', 'yith-stripe-connect-for-woocommerce' ) );
				}

				// backward compatibility.
				if ( $customer ) {
					YITH_Stripe_Connect_Customer()->update_usermeta_info(
						$customer->metadata->user_id,
						array(
							'id'             => $customer->id,
							'default_source' => $customer->invoice_settings->default_payment_method,
						)
					);
				}

				/** DO_ACTION: yith_wcstripe_connect_created_card
				*
				* Adds an action after that the cart is created.
				*
				* @param $cart_id  ID of the cart.
				* @param $customer Customer obj.
				*/
				do_action( 'yith_wcstripe_connect_created_card', $payment_method_id, $customer );

				return $token;
			} else {
				$registered_token->set_default( true );
				$registered_token->save();

				return $registered_token;
			}
		}

		/**
		 * Attach payment method to customer
		 *
		 * @param string|Stripe\Customer $customer          Customer to update.
		 * @param string                 $payment_method_id Payment method to save.
		 *
		 * @throws Exception When an error occurs with API handling.
		 * @return bool Status of the operation
		 */
		public function attach_payment_method( $customer, $payment_method_id ) {

			if ( $this->is_direct_charges() ) {
				return false;
			}

			try {
				$customer       = $this->api_handler->get_customer( $customer );
				$payment_method = $this->api_handler->get_payment_method( $payment_method_id );

				$payment_method->attach(
					array(
						'customer' => $customer->id,
					)
				);
			} catch ( Exception $e ) {
				return false;
			}

			$this->api_handler->update_customer(
				$customer,
				array(
					'invoice_settings' => array(
						'default_payment_method' => $payment_method_id,
					),
				)
			);

			return true;
		}

		/**
		 * Add payment method via my account page.
		 *
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 * @since  1.1.0
		 */
		public function add_payment_method() {
			try {
				// Initializate SDK and set private key.
				$this->init_stripe_connect_api();

				$intent = $this->get_intent();

				if ( ! $intent ) {
					throw new Exception( __( 'Sorry, There was an error while registering payment method; please, try again', 'yith-stripe-connect-for-woocommerce' ) );
				} elseif ( 'requires_action' === $intent->status ) {

					/** DO_ACTION: yith_stripe_connect_setup_intent_requires_action
					*
					* Adds an action when adding the payment method.
					*
					* @param $intent  Attempt obj.
					* @param $user_id ID of the user.
					*/
					do_action( 'yith_stripe_connect_setup_intent_requires_action', $intent, get_current_user_id() );

					throw new Exception( __( 'Please, validate your payment method before proceeding further; in order to do this, refresh the page and proceed at checkout as usual', 'yith-stripe-connect-for-woocommerce' ) );
				} elseif ( ! in_array( $intent->status, array( 'succeeded', 'requires_capture' ) ) ) {
					throw new Exception( __( 'Sorry, There was an error while registering payment method; please, try again', 'yith-stripe-connect-for-woocommerce' ) );
				}

				$token = $this->save_token( $intent->payment_method );

				/** APPLY_FILTERS: yith_stripe_connect_add_payment_method_result
				*
				* Filter the payment method result.
				*
				* @param array  $result, $redirect.
				* @param $token  Default token.
				*/
				return apply_filters(
					'yith_stripe_connect_add_payment_method_result',
					array(
						'result'   => 'success',
						'redirect' => wc_get_endpoint_url( 'payment-methods' ),
					),
					$token
				);

			} catch ( Exception $e ) {
				wc_add_notice( $e->getMessage(), 'error' );

				return false;
			}
		}

		/* === UTILITY METHODS === */

		/**
		 * Get return url for payment intent
		 *
		 * @param \WC_Order $order Order object, when relevant.
		 *
		 * @return string Return url
		 */
		public function get_return_url( $order = null ) {
			$redirect = parent::get_return_url( $order );

			if ( ! $order || empty( $this->_current_intent_secret ) ) {
				return $redirect;
			}

			// Put the final thank you page redirect into the verification URL.
			$verification_url = add_query_arg(
				array(
					'order'       => $order->get_id(),
					'redirect_to' => rawurlencode( $redirect ),
				),
				WC_AJAX::get_endpoint( 'yith_stripe_connect_verify_intent' )
			);

			// Combine into a hash.
			$redirect = sprintf( '#yith-stripe-connect-confirm-pi-%s:%s', $this->_current_intent_secret, $verification_url );

			return $redirect;
		}

		/**
		 * Add custom fields to CC form on checkout
		 *
		 * @param array  $fields Array of available fields.
		 * @param string $id     Gateway ID.
		 *
		 * @return array Array of filtered fields
		 */
		public function credit_form_add_fields( $fields, $id ) {
			if ( ! $this->is_available() || $id != $this->id ) {
				return $fields;
			}

			/** APPLY_FILTERS: yith_wcsc_placeholder_card_number
			*
			* Filter the default placeholder card number.
			*/
			$placeholder_card = apply_filters( 'yith_wcsc_placeholder_card_number', '&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;' );

			/** APPLY_FILTERS: yith_wcsc_card_code
			*
			* Filter the card code label.
			*/
			$cvc_field = '<p class="form-row form-row-last validate-required" >
			<label for="' . esc_attr( $this->id ) . '-card-cvc">' . apply_filters( 'yith_wcsc_card_code', esc_html__( 'Card code', 'woocommerce' ) ) . ' <span class="required">*</span></label>
			<input id="' . esc_attr( $this->id ) . '-card-cvc" class="input-text wc-credit-card-form-card-cvc" inputmode="numeric" autocomplete="off" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" maxlength="4" placeholder="' . esc_attr__( 'CVC', 'woocommerce' ) . '" ' . $this->field_name( 'card-cvc' ) . ' style="width:100px" />
			</p>';

			$default_fields = array(
				'card-number-field' => '<p class="form-row form-row-wide validate-required ">
				<label for="' . esc_attr( $this->id ) . '-card-number">' . esc_html__( 'Card number', 'woocommerce' ) . ' <span class="required">*</span></label>
				<input id="' . esc_attr( $this->id ) . '-card-number" class="input-text wc-credit-card-form-card-number" inputmode="numeric" autocomplete="cc-number" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" placeholder="' . $placeholder_card . '" ' . $this->field_name( 'card-number' ) . ' />
				</p>',
				'card-expiry-field' => '<p class="form-row form-row-first validate-required">
				<label for="' . esc_attr( $this->id ) . '-card-expiry">' . esc_html__( 'Expiry (MM/YY)', 'woocommerce' ) . ' <span class="required">*</span></label>
				<input id="' . esc_attr( $this->id ) . '-card-expiry" class="input-text wc-credit-card-form-card-expiry" inputmode="numeric" autocomplete="cc-exp" autocorrect="no" autocapitalize="no" maxlength="7" spellcheck="no" type="tel" placeholder="' . esc_attr__( 'MM / YY', 'woocommerce' ) . '" ' . $this->field_name( 'card-expiry' ) . ' />
				</p>',
			);

			if ( 'yes' === $this->show_name_on_card ) {
				/** APPLY_FILTERS: yith_wccs_name_on_card_label
				*
				* Filter the name on card label.
				*/
				$default_fields = array_merge(
					array(
						'card-name-field' => '<p class="form-row form-row-wide">
							<label for="' . esc_attr( $this->id ) . '-card-name">' . apply_filters( 'yith_wccs_name_on_card_label', __( 'Name on Card', 'yith-stripe-connect-for-woocommerce' ) ) . ' <span class="required">*</span></label>
							<input id="' . esc_attr( $this->id ) . '-card-name" class="input-text wc-credit-card-form-card-name" type="text" autocomplete="off" placeholder="' . __( 'Name on Card', 'yith-stripe-connect-for-woocommerce' ) . '" ' . $this->field_name( 'card-name' ) . ' />
							</p>',
					),
					$default_fields
				);
			}

			if ( ! $this->supports( 'credit_card_form_cvc_on_saved_method' ) ) {
				$default_fields['card-cvc-field'] = $cvc_field;
			}

			return $default_fields;
		}

		/**
		 * Return the gateway icons.
		 *
		 * @return string
		 */
		public function get_icon() {

			/** APPLY_FILTERS: yith_wc_stripe_connect_credit_cards_logos
			*
			* Filter cards logo.
			*/
			$icon_html = apply_filters( 'yith_wc_stripe_connect_credit_cards_logos', '', $this->credit_cards_logo );

			/** APPLY_FILTERS: yith_wc_stripe_connect_credit_cards_logos_width
			*
			* Filter cards logo width.
			*/
			$width = apply_filters( 'yith_wc_stripe_connect_credit_cards_logos_width', '40px' );

			foreach ( $this->credit_cards_logo as $logo_card ) {
				$icon_html .= '<img class="yith_wcsc_icon" src="' . YITH_WCSC_ASSETS_URL . 'images/' . esc_attr( $logo_card ) . '.svg" alt="' . $logo_card . '" width="' . $width . '" />';
			}

			return $icon_html;
		}

		/**
		 * Log to txt file
		 *
		 * @param string $level   Type of log to add.
		 * @param string $message Log message.
		 *
		 * @since 1.0.0
		 */
		public function log( $level, $message ) {
			if ( isset( $this->log, $this->log_enabled ) && $this->log_enabled ) {
				$this->log->log(
					$level,
					$message,
					array(
						'source' => 'stripe-connect',
						'_legacy' => true,
					)
				);
			}
		}

		/**
		 * Initialise Gateway Settings Form Fields.
		 */
		public function init_form_fields() {
			$this->form_fields = include( YITH_WCSC_OPTIONS_PATH . 'settings-sc-gateway.php' );
		}

		/**
		 * Init api class
		 *
		 * @return void
		 */
		public function init_stripe_connect_api() {
			if ( is_a( $this->api_handler, 'YITH_Stripe_Connect_API_Handler' ) ) {
				return;
			}
			$this->api_handler = YITH_Stripe_Connect_API_Handler::instance();
		}

		/**
		 * Remove the checkbox from checkout.
		 *
		 * @return bool
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 * @since  1.1.0
		 */
		public function save_payment_method_checkbox() {
			return false;
		}

		/**
		 * Checks whether we will use alternative flow for current order
		 *
		 * @param int|bool $order_id Optional order id to check.
		 * @return bool|array False or commissions that will be generated for current cart/order
		 */
		public function is_alt_flow( $order_id = false ) {
			global $wp;

			// if direct_charges and order contains subscriptions, skip directly.
			if ( 'direct_charges' === $this->alt_flow && $this->order_contains_subscription( $order_id ) ) {
				return false;
			}

			// choose among cart and order.
			if ( $order_id ) {
				$object = wc_get_order( $order_id );
			} elseif ( is_checkout_pay_page() ) {
				$order_id = isset( $wp->query_vars['order-pay'] ) ? $wp->query_vars['order-pay'] : false;
				$object = wc_get_order( $order_id );
			} else {
				$object = WC()->cart;
			}

			if ( $object && 'yes' == $this->enable_alt_flows && 'none' != $this->alt_flow ) {
				$commissions = YITH_Stripe_Connect_Commissions()->retrieve_commissions_records( $object );
				$receivers   = $commissions ? wp_list_pluck( $commissions, 'user_id' ) : array();
				$receivers   = array_unique( $receivers );

				// if there's only one receiver, we can proceed with alternative flow.
				if ( count( $receivers ) == 1 ) {
					return $commissions;
				}
			}

			return false;
		}

		/**
		 * Check whether we will use Direct Charges as alternative payment flow or not
		 *
		 * @param int|bool $order_id Order id.
		 * @return bool Whether Direct charges will be used for current order.
		 */
		public function is_direct_charges( $order_id = false ) {
			if ( ! empty( $this->_current_account ) ) {
				return true;
			}

			return $this->is_alt_flow( $order_id ) && 'direct_charges' == $this->alt_flow && ! $this->order_contains_subscription( $order_id );
		}

		/**
		 * Check if order contains subscriptions.
		 *
		 * @param int|bool $order_id Order id or false, when we need to check cart.
		 *
		 * @return bool
		 */
		public function order_contains_subscription( $order_id = false ) {
			if ( ! $order_id ) {
				return function_exists( 'YITH_WC_Subscription' ) && YWSBS_Subscription_Cart::cart_has_subscriptions();
			}

			return function_exists( 'YITH_WC_Subscription' ) && YITH_WC_Subscription()->order_has_subscription( $order_id );
		}

		/**
		 * Returns an array of options to be used for tha API call
		 *
		 * @return array Array of options
		 */
		protected function _retrieve_api_option() {
			$options = array();

			if ( ! empty( $this->_current_account ) ) {
				$options['stripe_account'] = $this->_current_account;
			}

			return $options;
		}

	}

}
