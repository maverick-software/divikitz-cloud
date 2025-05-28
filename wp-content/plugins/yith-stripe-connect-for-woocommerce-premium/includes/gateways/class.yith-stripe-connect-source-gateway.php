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
 * @class      YITH_Stripe_Connect_Sources_Gateway
 * @package    Yithemes
 * @since      Version 1.1.0
 * @author     Your Inspiration Themes
 *
 */

if ( ! class_exists( 'YITH_Stripe_Connect_Sources_Gateway' ) ) {

	/**
	 * Class YITH_Stripe_Connect_Sources_Gateway
	 *
	 * This class replace YITH_Stripe_Connect_Gateway when the plugin YITH WooCommerce Subscription Premium from 1.4.6 is installed.
	 *
	 * @since  1.1.0
	 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
	 */
	class YITH_Stripe_Connect_Source_Gateway extends YITH_Stripe_Connect_Gateway {

		/**
		 * Whether currently processing renew needs additional actions by the customer
		 * (An email will be sent when registering failed attempt, if this flag is true)
		 *
		 * @var bool
		 */
		protected $_renew_needs_action = false;

		/**
		 * Instance of YITH_Stripe_Connect_Source_Gateway
		 *
		 * @var YITH_Stripe_Connect_Source_Gateway
		 */
		protected static $_instance = null;

		/**
		 * Return the instance of Gateway
		 *
		 * @return null|YITH_Stripe_Connect_Gateway|YITH_Stripe_Connect_Source_Gateway
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
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
		 * @since  1.1.0
		 */
		public function __construct() {
			parent::__construct();

			$this->save_cards = 'yes';
			$this->supports   = array(
				'products',
				'tokenization',
				'yith_subscriptions',
				'yith_subscriptions_scheduling',
				'yith_subscriptions_pause',
				'yith_subscriptions_multiple',
				'yith_subscriptions_payment_date',
				'yith_subscriptions_recurring_amount',
				'yith_pre_orders',
			);

			// Pay the renew orders.
			add_action( 'ywsbs_pay_renew_order_with_' . $this->id, array( $this, 'pay_renew_order' ), 10, 2 );
			add_action( 'ywpo_process_pre_order_release_payment_' . $this->id, array( $this, 'process_pre_order_release_payment' ) );
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

			if ( class_exists( 'YITH_Pre_Order_Orders_Manager' ) && ywpo_is_upon_release_order( $order ) && ! ywpo_is_pay_later_order( $order ) ) {
				$this->log( 'info', 'Processing upon release pre-order for order ' . $order->get_order_number() . '.' );
				return $this->process_upon_release_pre_order( $order );
			} else {
				$this->log( 'info', 'Generating payment form for order ' . $order->get_order_number() . '.' );
				return $this->process_standard_payment();
			}
		}

		public function get_setup_intent( $order = false ) {
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
				$intent    = $this->get_session_setup_intent( $order->get_id() );
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
		 * Update session intent, registering new cart total and currency, and configuring a payment method if needed
		 *
		 * @param int|bool $token Selected token id, or null if new payment method is used.
		 * @param int|bool $order Current order id, or null if cart should be used.
		 *
		 * @return PaymentIntent|SetupIntent|bool Updated intent, or false on failure
		 * @throws Exception When a problem occurs with intent handling.
		 */
		public function update_session_setup_intent( $token = false, $order = false ) {
			// retrieve intent; this will automatically update total and currency.
			$intent = $this->get_session_setup_intent( $order );

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
		 *
		 */
		public function process_upon_release_pre_order( $order ) {
			try {

				// Initializate SDK and set private key.
				$this->init_stripe_connect_api();

				// retrieve payment intent.
				$intent = $this->get_setup_intent( $order );

				// no intent yet; return error.
				if ( ! $intent || ! $order ) {
					throw new Exception( __( 'Sorry, there was an error while processing payment; please, try again', 'yith-stripe-connect-for-woocommerce' ), null );
				}

				// intent refers to another transaction: return error.
				if ( $order->get_id() != $intent->metadata->order_id && yith_wcsc_get_cart_hash() != $intent->metadata->cart_hash ) {
					throw new Exception( __( 'Sorry, there was an error while processing payment; please, try again', 'yith-woocommerce-stripe' ), null );
				}

				$payment_method = isset( $_POST['stripe_connect_payment_method'] ) ? sanitize_text_field( $_POST['stripe_connect_payment_method'] ) : false;

				if ( ! $payment_method && isset( $_POST['wc-yith-stripe-connect-payment-token'] ) && 'new' !== $_POST['wc-yith-stripe-connect-payment-token'] ) {
					$token_id = intval( $_POST['wc-yith-stripe-connect-payment-token'] );
					$token    = WC_Payment_Tokens::get( $token_id );

					if ( $token && $token->get_user_id() == get_current_user_id() && $token->get_gateway_id() == $this->id ) {
						$payment_method = $token->get_token();
					}
				}

				$this->save_token( $payment_method );

				// if intent is missing payment method, or requires update, proceed with update.
				if (
					( 'requires_payment_method' == $intent->status && $payment_method ) ||
					(
						( yith_wcsc_get_amount( $order->get_total(), $order->get_currency() ) != $intent->amount || strtolower( $order->get_currency() ) != $intent->currency ) &&
						! in_array( $intent->status, array( 'requires_action', 'requires_capture', 'succeeded', 'canceled' ) )
					)
				) {
					// updates session intent.
					$intent = $this->update_session_setup_intent( $payment_method, $order->get_id() );
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

				$customer       = $this->get_customer( $order );
				$this->customer = $customer ? $customer->id : '';

				update_post_meta( $order->get_id(), 'yith_stripe_connect_customer_id', $this->customer );
				update_post_meta( $order->get_id(), 'yith_stripe_connect_source_id', $intent->payment_method );

				// Remove cart.
				WC()->cart->empty_cart();

				YITH_Pre_Order_Orders_Manager::set_as_pre_order_pending_payment( $order );

				$this->delete_session_setup_intent();

				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order ),
				);

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
		 * Pay the order.
		 *
		 * If on cart there are subscription products proceed with this class, otherwise call the parent class.
		 *
		 * @param WC_Order $order Order to pay.
		 *
		 * @return array|bool|WP_Error
		 * @throws Stripe\Exception\ApiErrorException|Exception When an error occurs with payments.
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 * @since  1.1.0
		 */
		public function pay( $order = null ) {
			if ( class_exists( 'YITH_Pre_Order_Orders_Manager' ) && ywpo_is_upon_release_order( $order ) && ! ywpo_is_pay_later_order( $order ) ) {
				$intent = $this->get_intent( $order );

				$customer       = $this->get_customer( $order );
				$this->customer = $customer ? $customer->id : '';
				$this->save_token( $intent->payment_method );

				update_post_meta( $order->get_id(), 'yith_stripe_connect_customer_id', $this->customer );
				update_post_meta( $order->get_id(), 'yith_stripe_connect_source_id', $intent->payment_method );

				// Remove cart.
				WC()->cart->empty_cart();

				YITH_Pre_Order_Orders_Manager::set_as_pre_order_pending_payment( $order );

				$this->delete_session_intent();

				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order ),
				);
			} else {
				$result = parent::pay( $order );

				$order_contains_subscription = $order ? $this->order_contains_subscription( $order ) : false;

				if ( $order_contains_subscription ) {
					$subscriptions = $order->get_meta( 'subscriptions' );

					if ( $result && ! is_wp_error( $result ) ) {
						// if we cannot retrieve subscriptions from order meta, check session.
						if ( empty( $subscriptions ) && ! is_null( WC()->session ) ) {
							$order_args = WC()->session->get( 'ywsbs_order_args', array() );
							if ( isset( $order_args['subscriptions'] ) ) {
								$subscriptions = $order_args['subscriptions'];
							}

							WC()->session->set( 'ywsbs_order_args', array() );
						}

						$intent = $this->get_intent( $order );

						if ( ! is_user_logged_in() ) {
							$this->attach_payment_method( $this->customer, $intent->payment_method );
						}

						foreach ( $subscriptions as $subscription_id ) {
							update_post_meta( $subscription_id, 'yith_stripe_connect_customer_id', $this->customer );
							update_post_meta( $subscription_id, 'yith_stripe_connect_source_id', $intent->payment_method );
						}

						$charge_id = $order->get_transaction_id();

						if ( $charge_id ) {
							foreach ( $subscriptions as $subscription_id ) {
								// translators: 1. Subscription id. 2. Order id. 3. Charge id.
								$this->log( 'info', sprintf( __( 'Stripe Connect processed successfully. Subscription %1$s. Order %2$s. (Transaction ID: %3$s)', 'yith-stripe-connect-for-woocommerce' ), $subscription_id, $order->get_id(), $charge_id ) );

								update_post_meta( $subscription_id, 'transaction_id', $charge_id );

							}
						}
					} else {
						ywsbs_register_failed_payment( $order, $result->get_error_message() );

						return $result;
					}
				}
			}

			return true;
		}

		/**
		 * Pay the renew order.
		 *
		 * It is triggered by ywsbs_pay_renew_order_with_{gateway_id} action
		 *
		 * @param WC_Order $order    Renew order to pay.
		 * @param bool     $manually Wheter renew is performed manually.
		 *
		 * @return array|bool|WP_Error
		 * @throws Stripe\Exception\ApiErrorException When an error occurs with payment.
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 * @since  1.1.0
		 */
		public function pay_renew_order( $order = null, $manually = false ) {

			if ( is_null( $order ) ) {
				return false;
			}

			$is_a_renew      = $order->get_meta( 'is_a_renew' );
			$subscriptions   = $order->get_meta( 'subscriptions' );
			$subscription_id = $subscriptions ? $subscriptions[0] : false;

			if ( ! $subscription_id ) {
				// translators: 1. Order id.
				$error_msg = sprintf( __( 'Sorry, any subscription is found for this order: %s', 'yith-stripe-connect-for-woocommerce' ), $order->get_id() );
				$this->log( 'error', $error_msg );

				return false;
			}

			$yith_stripe_connect_source_id = get_post_meta( $subscription_id, 'yith_stripe_connect_source_id' );

			if ( 'yes' !== $is_a_renew || ! $yith_stripe_connect_source_id ) {
				return false;
			}


			// Initialize SDK and set private key.
			$this->init_stripe_connect_api();

			$amount                 = $order->get_total();
			$order_id               = $order->get_id();
			// translators: 1. Order number.
			$general_failed_message = sprintf( __( 'Failed payment for order #%s', 'yith-stripe-connect-for-woocommerce' ), $order->get_order_number() );

			if ( 0 == $amount ) {
				// Payment complete.
				$order->payment_complete();

				return true;
			}

			if ( $amount * 100 < 50 ) {
				$error_msg = __( 'Sorry, the minimum order total allowed to use this payment method is 0.50.', 'yith-stripe-connect-for-woocommerce' );
				$this->log( 'error', $error_msg );
				if ( $manually ) {
					wc_add_notice( $general_failed_message, 'error' );
				} else {
					ywsbs_register_failed_payment( $order, $error_msg );
				}

				return false;
			}

			$user_id = get_post_meta( $subscription_id, 'user_id', true );

			if ( 0 != $user_id ) {
				$local_customer = YITH_Stripe_Connect_Customer()->get_usermeta_info( $user_id );
				$stripe_user_id = isset( $local_customer['id'] ) ? $local_customer['id'] : false;
				$source_id      = $this->get_valid_source_id( $user_id, $local_customer, $subscription_id );
			}

			if ( ! $stripe_user_id ) {
				$stripe_user_id = get_post_meta( $subscription_id, 'yith_stripe_connect_customer_id', true );
				$source_id      = $yith_stripe_connect_source_id;
			}

			if ( ! $stripe_user_id ) {
				// translators: 1. Renew order id. 2. Subscription id.
				$error_msg = sprintf( __( 'Sorry, couldn\'t find any user registered to pay the order renew %1$s for subscription %2$s .', 'yith-stripe-connect-for-woocommerce' ), $order_id, $subscription_id );

				if ( $manually ) {
					wc_add_notice( $general_failed_message, 'error' );
				} else {
					ywsbs_register_failed_payment( $order, $error_msg );
				}

				$this->log( 'warning', $error_msg );

				return false;
			}

			$source_id = is_array( $source_id ) ? array_shift( $source_id ) : $source_id;

			if ( ! $source_id ) {
				// translators: 1. Renew order id. 2. Subscription id.
				$error_msg = sprintf( __( 'Sorry, any card is registered to pay the order renew %1$s for subscription %2$s .', 'yith-stripe-connect-for-woocommerce' ), $order_id, $subscription_id );
				if ( $manually ) {
					wc_add_notice( $general_failed_message, 'error' );
				} else {
					ywsbs_register_failed_payment( $order, $error_msg );
				}

				$this->log( 'warning', $error_msg );

				return false;
			}

			$customer = $this->api_handler->get_customer( $stripe_user_id );

			$this->customer = $stripe_user_id;
			$this->token    = $source_id;

			try {
				$intent = $this->api_handler->create_intent(
					array(
						'amount'               => yith_wcsc_get_amount( $order->get_total() ),
						'currency'             => $order->get_currency(),

						/** APPLY_FILTERS: yith_stripe_connect_charge_description
						*
						* Filter the defaul plugin description in the charges.
						*
						* @param string sprintf       Default plugin text.
						* @param string $blog_name    Blog name.
						* @param string $order_number Order number.
						* @param string $order_id     Order ID.
						*/
						// translators: 1. Blog name. 2. Order number.
						'description'          => apply_filters( 'yith_stripe_connect_charge_description', sprintf( __( '%1$s - Order %2$s', 'yith-stripe-connect-for-woocommerce' ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() ),

						/** APPLY_FILTERS: yith_wcstripe_connect_metadata
						*
						* Filter metadata sent to Stripe.
						*
						* @param array  $order_id, $order_email, $instance.
						* @param string 'charge' by default.
						*/
						'metadata'             => apply_filters(
							'yith_wcstripe_connect_metadata',
							array(
								'order_id'    => $order_id,
								'order_email' => $order->get_billing_email(),
								'instance'    => $this->instance_url,
							),
							'charge'
						),
						'customer'             => $stripe_user_id,
						'payment_method_types' => array( 'card' ),
						'payment_method'       => $source_id,
						'off_session'          => true,
						'confirm'              => true,
						'transfer_group'       => $order->get_id(),
					)
				);
			} catch ( Stripe\Exception\ApiErrorException $e ) {
				$body = $e->getJsonBody();
				$err  = $body['error'];

				if (
					isset( $err['payment_intent'] ) &&
					isset( $err['payment_intent']['status'] ) &&
					in_array( $err['payment_intent']['status'], array( 'requires_action', 'requires_payment_method' ) ) &&
					(
						! empty( $err['payment_intent']['next_action'] ) && isset( $err['payment_intent']['next_action']->type ) && 'use_stripe_sdk' === $err['payment_intent']['next_action']->type ||
						'authentication_required' === $err['code']
					)
				) {
					$this->_renew_needs_action = true;

					$this->register_failed_renew( $order, __( 'Please, validate your payment method before proceeding further', 'yith-stripe-connect-for-woocommerce' ) );

					return false;
				} else {
					$this->register_failed_renew( $order, $err['message'] );

					return false;
				}
			} catch ( Exception $e ) {
				$this->register_failed_renew( $order, __( 'Sorry, There was an error while processing payment; please, try again', 'yith-stripe-connect-for-woocommerce' ) );

				return false;
			}

			// register intent for the order.
			$order->update_meta_data( 'intent_id', $intent->id );

			// retrieve charge to use for next steps.
			$charge = $intent->latest_charge;
			$charge = is_object( $charge ) ? $charge : $this->api_handler->retrieve_charge( $charge );

			// update renew order.
			$order->update_meta_data( 'yith_stripe_connect_source_id', $source_id );
			$order->update_meta_data( 'yith_stripe_connect_customer_id', $customer->id );
			$order->save();

			// update stored customer id.
			YITH_Stripe_Connect_Customer()->update_usermeta_info(
				$user_id,
				array(
					'id' => $customer->id,
				)
			);

			// Payment complete.
			$is_payment_complete = $order->payment_complete( $charge->id );

			if ( $is_payment_complete ) {

				/** DO_ACTION: yith_wcsc_payment_complete
				*
				* Adds an action when payment is completed.
				*
				* @param $order_id  ID of the order.
				* @param $charge_id ID of the charge.
				*/
				do_action( 'yith_wcsc_payment_complete', $order->get_id(), $charge->id );

				if ( $manually ) {
					// translators: 1. Order number.
					wc_add_notice( sprintf( __( 'Payment approved for order #%s', 'yith-stripe-connect-for-woocommerce' ), $order->get_order_number() ), 'success' );
				}

				// Add order note.
				// translators: 1. Charge id.
				$order->add_order_note( sprintf( __( 'Stripe Connect payment approved (ID: %s)', 'yith-stripe-connect-for-woocommerce' ), $charge->id ) );
			}

			// Return thank you page redirect.
			return true;
		}

		/**
		 * Pay the upon release pre-order.
		 * It is triggered by ywpo_process_pre_order_release_payment_{gateway_id} action
		 *
		 * @param WC_Order $order    Pre-order to pay.
		 *
		 * @return array|bool|WP_Error
		 * @throws Stripe\Exception\ApiErrorException When an error occurs with payment.
		 */
		public function process_pre_order_release_payment( $order = null ) {
			if ( ! $order instanceof WC_Order ) {
				// translators: %d: order ID.
				$error_msg = __( 'Process pre-order upon-release payment: the order does not exist.', 'yith-stripe-connect-for-woocommerce' );
				$this->log( 'error', $error_msg );
				return false;
			}

			$order_id = $order->get_id();
			$amount   = $order->get_total();

			if ( ! ywpo_is_upon_release_order( $order ) || ! ywpo_order_has_payment_token( $order ) ) {
				// translators: %d: order ID.
				$error_msg = sprintf( __( '(Order #%1$d) Process pre-order upon-release payment: order #%1$d not an upon release pre-order', 'yith-stripe-connect-for-woocommerce' ), $order->get_id() );
				$this->log( 'error', $error_msg );
				return false;
			}

			$yith_stripe_connect_source_id = get_post_meta( $order_id, 'yith_stripe_connect_source_id', true );
			$yith_stripe_connect_user_id   = get_post_meta( $order_id, 'yith_stripe_connect_customer_id', true );

			if ( ! $yith_stripe_connect_source_id ) {
				// translators: %d: order ID.
				$error_msg = sprintf( __( '(Order #%d) Process pre-order upon-release payment: no token ID.', 'yith-stripe-connect-for-woocommerce' ), $order->get_id() );
				$this->log( 'error', $error_msg );
				return false;
			}

			// Initialize SDK and set private key.
			$this->init_stripe_connect_api();

			// translators: 1. Order number.
			$general_failed_message = sprintf( __( 'Failed payment for order #%s', 'yith-stripe-connect-for-woocommerce' ), $order->get_order_number() );

			if ( $amount <= 0 ) {
				// Payment complete.
				$order->payment_complete();

				return false;
			}

			if ( $amount * 100 < 50 ) {
				// translators: %d: order ID.
				$error_msg = sprintf( __( '(Order #%d) Process pre-order upon-release payment: sorry, the minimum order total allowed to use this payment method is 0.50.', 'yith-stripe-connect-for-woocommerce' ), $order->get_id() );
				$this->log( 'error', $error_msg );

				return false;
			}

			$user_id = $order->get_user_id();

			if ( ! $yith_stripe_connect_user_id ) {
				// translators: %d: order ID.
				$error_msg = sprintf( __( '(Order #%d) Process pre-order upon-release payment: sorry, couldn\'t find any user registered to pay the pre-order.', 'yith-stripe-connect-for-woocommerce' ), $order_id );

				$this->log( 'warning', $error_msg );

				return false;
			}

			$customer = $this->api_handler->get_customer( $yith_stripe_connect_user_id );

			$this->customer = $yith_stripe_connect_user_id;
			$this->token    = $yith_stripe_connect_source_id;

			try {
				$intent = $this->api_handler->create_intent(
					array(
						'amount'               => yith_wcsc_get_amount( $order->get_total() ),
						'currency'             => $order->get_currency(),

						/** APPLY_FILTERS: yith_stripe_connect_charge_description
						*
						* Filter the defaul plugin description in the charges.
						*
						* @param string sprintf       Default plugin text.
						* @param string $blog_name    Blog name.
						* @param string $order_number Order number.
						* @param string $order_id     Order ID.
						*/
						// translators: 1. Blog name. 2. Order number.
						'description'          => apply_filters( 'yith_stripe_connect_charge_description', sprintf( __( '%1$s - Order %2$s', 'yith-stripe-connect-for-woocommerce' ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() ),

						/** APPLY_FILTERS: yith_wcstripe_connect_metadata
						*
						* Filter metadata sent to Stripe.
						*
						* @param array  $order_id, $order_email, $instance.
						* @param string 'charge' by default.
						*/
						'metadata'             => apply_filters(
							'yith_wcstripe_connect_metadata',
							array(
								'order_id'    => $order_id,
								'order_email' => yit_get_prop( $order, 'billing_email' ),
								'instance'    => $this->instance_url,
							),
							'charge'
						),
						'customer'             => $yith_stripe_connect_user_id,
						'payment_method_types' => array( 'card' ),
						'payment_method'       => $yith_stripe_connect_source_id,
						'off_session'          => true,
						'confirm'              => true,
						'transfer_group'       => $order->get_id(),
					)
				);
			} catch ( Stripe\Exception\ApiErrorException $e ) {
				$body = $e->getJsonBody();
				$err  = $body['error'];

				if (
					isset( $err['payment_intent'] ) &&
					isset( $err['payment_intent']['status'] ) &&
					in_array( $err['payment_intent']['status'], array( 'requires_action', 'requires_payment_method' ) ) &&
					(
						! empty( $err['payment_intent']['next_action'] ) && isset( $err['payment_intent']['next_action']->type ) && 'use_stripe_sdk' === $err['payment_intent']['next_action']->type ||
						'authentication_required' === $err['code']
					)
				) {
					$this->_renew_needs_action = true;

					$this->log( 'error', __( 'Process pre-order upon-release payment: Please, validate your payment method before proceeding further.', 'yith-stripe-connect-for-woocommerce' ) );

					return false;
				} else {
					$this->log( 'error', 'Process pre-order release payment: ' . $err['message'] );

					return false;
				}
			} catch ( Exception $e ) {
				$this->log( 'error', __( 'Process pre-order upon-release payment: There was an error while processing payment. Please, try again.', 'yith-stripe-connect-for-woocommerce' ) );

				return false;
			}

			// register intent for the order.
			$order->update_meta_data( 'intent_id', $intent->id );

			// retrieve charge to use for next steps.
			$charge = $intent->latest_charge;
			$charge = is_object( $charge ) ? $charge : $this->api_handler->retrieve_charge( $charge );

			// update renew order.
			$order->update_meta_data( 'yith_stripe_connect_source_id', $yith_stripe_connect_source_id );
			$order->update_meta_data( 'yith_stripe_connect_customer_id', $customer->id );
			$order->save();

			// update stored customer id.
			YITH_Stripe_Connect_Customer()->update_usermeta_info(
				$user_id,
				array(
					'id' => $customer->id,
				)
			);

			// Payment complete.
			$is_payment_complete = $order->payment_complete( $charge->id );

			if ( $is_payment_complete ) {

				/** DO_ACTION: yith_wcsc_payment_complete
				*
				* Adds an action when payment is completed.
				*
				* @param $order_id  ID of the order.
				* @param $charge_id ID of the charge.
				*/
				do_action( 'yith_wcsc_payment_complete', $order->get_id(), $charge->id );

				// Add order note.
				// translators: 1. Charge id.
				$order->add_order_note( sprintf( __( 'Stripe Connect payment approved (ID: %s)', 'yith-stripe-connect-for-woocommerce' ), $charge->id ) );
			}

			// Return thank you page redirect.
			return true;
		}

		/**
		 * Get a valid token useful to pay the renew order.
		 *
		 * @param int   $user_id         User id.
		 * @param array $local_customer  Local informations about Stripe customer.
		 * @param int   $subscription_id Subscription id.
		 *
		 * @return bool|string
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 * @since  1.1.0
		 */
		public function get_valid_source_id( $user_id, $local_customer, $subscription_id ) {

			// check first if the default payment token is valid.
			$default_payment_method = WC_Payment_Tokens::get_customer_default_token( $user_id );

			if ( $default_payment_method && $default_payment_method->get_gateway_id() == $this->id ) {
				$token = $default_payment_method->get_token();
				// update the default source on Stripe Connect Customer.
				if ( isset( $local_customer['default_source'] ) && ! empty( $local_customer['default_source'] ) && $token != $local_customer['default_source'] ) {
					YITH_Stripe_Connect_Customer()->update_usermeta_info(
						$user_id,
						array(
							'default_source' => $token,
						)
					);
				}

				return $token;
			}

			// check if in local customer there's registered a valid token.
			$registered_payments = WC_Payment_Tokens::get_customer_tokens( $user_id, $this->id );
			$source_id           = get_post_meta( $subscription_id, 'yith_stripe_connect_source_id', true );

			if ( isset( $local_customer['default_source'] ) ) {
				foreach ( $registered_payments as $registered_payment ) {
					$registered_token = $registered_payment->get_token();
					if ( $registered_token == $local_customer['default_source'] ) {
						return $registered_token;
					}
				}

				if ( $source_id == $local_customer['default_source'] ) {
					return false;
				}
			}

			// Check if in subscription there's registered a valid token.
			if ( ! empty( $source_id ) ) {
				foreach ( $registered_payments as $registered_payment ) {
					$registered_token = $registered_payment->get_token();
					if ( $registered_token == $source_id ) {
						if ( isset( $local_customer['default_source'] ) && ! empty( $local_customer['default_source'] ) && $registered_token != $local_customer['default_source'] ) {
							YITH_Stripe_Connect_Customer()->update_usermeta_info(
								$user_id,
								array(
									'default_source' => $registered_token,
								)
							);
						}

						return $registered_token;
					}
				}
			}

			return false;
		}

		/**
		 * Register failed renew attempt for an order, and related error message
		 *
		 * @param $order   \WC_Order Renew order.
		 * @param $message string Error message to log.
		 *
		 * @return void
		 */
		public function register_failed_renew( $order, $message ) {
			ywsbs_register_failed_payment( $order, $message );

			/**
			 * Required in order to make sure that the order object is up to date after
			 * subscription register failed attempt
			 */
			$order = wc_get_order( $order->get_id() );

			if ( $this->_renew_needs_action && ! $order->has_status( 'cancelled' ) ) {

				/** DO_ACTION: yith_stripe_connect_renew_intent_requires_action
				*
				* Adds an action when attempting to renew.
				*
				* @param $order Order obj.
				*/
				do_action( 'yith_stripe_connect_renew_intent_requires_action', $order );
			}

			$this->log( 'info', $message );
		}

		/**
		 * Get a link to the transaction on the 3rd party gateway site (if applicable).
		 *
		 * @param  WC_Order $order the order object.
		 * @return string transaction URL, or empty string.
		 */
		public function get_transaction_url( $order ) {
			$return_url     = '';
			$transaction_id = $order->get_transaction_id();

			if ( ! empty( $this->view_transaction_url ) && ! empty( $transaction_id ) && ! $order->get_meta( '_yith_wcstripe_alt_flow', true ) ) {
				$return_url = sprintf( $this->view_transaction_url, $transaction_id );
			}

			/** APPLY_FILTERS: woocommerce_get_transaction_url
			*
			* Filter the default transaction url.
			*
			* @param $return_url Default URL.
			* @param $order      Order obj.
			* @param $this       'YITH_Stripe_Connect_Sources_Gateway' Class.
			*/
			return apply_filters( 'woocommerce_get_transaction_url', $return_url, $order, $this );
		}
	}
}
