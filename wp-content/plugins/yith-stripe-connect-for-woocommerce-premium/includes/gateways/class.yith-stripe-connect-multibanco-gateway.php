<?php
/**
 * MultiBanco gateway class
 *
 * @class      YITH_Stripe_Connect_Gateway
 * @package    Yithemes
 * @since      Version 2.1.0
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

if ( ! class_exists( 'YITH_Stripe_Connect_Multibanco_Gateway' ) ) {

	/**
	 * Class YITH_Stripe_Connect_Multibanco_Gateway
	 *
	 * Multibanco payment gateway
	 */
	class YITH_Stripe_Connect_Multibanco_Gateway extends WC_Payment_Gateway {

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
		 * Instance of API handler
		 *
		 * @var \YITH_Stripe_Connect_API_Handler
		 */
		public $api_handler = null;

		/**
		 * Constructor for the gateway.
		 */
		public function __construct() {
			$this->id         = YITH_Stripe_Connect::$gateway_id . '-multibanco';
			$this->has_fields = false;

			/** APPLY_FILTERS: yith_wcsc_multibanco_order_button_text
			*
			* Filter the default multibanco button text.
			*/
			$this->order_button_text  = apply_filters( 'yith_wcsc_multibanco_order_button_text', _x( 'Proceed to MultiBanco', 'Label for the MultiBanco button', 'yith-stripe-connect-for-woocommerce' ) );
			$this->method_title       = _x( 'Stripe Connect - Multibanco', 'The Gateway title, no need translation :D', 'yith-stripe-connect-for-woocommerce' );
			$this->method_description = _x( 'Pay with MultiBanco', 'Stripe Connect Gateway description', 'yith-stripe-connect-for-woocommerce' );
			$this->instance_url       = preg_replace( '/http(s)?:\/\//', '', site_url() );
			$this->supports           = array(
				'products',
			);

			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			// Define user set variables.
			$this->title                = $this->get_option( 'title' );
			$this->description          = $this->get_option( 'description' );
			$this->description          = ! empty( $this->description ) ? $this->description : __( 'Multibanco', 'yith-stripe-connect-for-woocommerce' );
			$this->test_live            = 'yes' === $this->get_option( 'test-live', 'no' );
			$this->log_enabled          = 'yes' === $this->get_option( 'log', 'no' );
			$this->public_key           = $this->test_live ? $this->get_option( 'api-public-test-key' ) : $this->get_option( 'api-public-live-key' ); // Switch the plublic key between test and live mode.
			$this->view_transaction_url = 'https://dashboard.stripe.com/' . ( $this->test_live ? 'test/' : '' ) . 'payments/%s';

			if ( $this->log_enabled ) {
				$this->log = new WC_Logger();
			}

			// process options for the gatway.
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

			// print instructions where needed.
			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thank_you_page_instructions' ) );
			add_action( 'woocommerce_email_after_order_table', array( $this, 'email_instructions' ), 10, 3 );
		}

		/**
		 * Get options, eventually retrieving data from main gateway
		 *
		 * @param string $key         Option key.
		 * @param mixed  $empty_value Default value to use when option is empty.
		 *
		 * @return mixed Option value.
		 */
		public function get_option( $key, $empty_value = null ) {
			$parent_options = array(
				'test-live',
				'api-public-test-key',
				'api-public-live-key',
			);

			if ( in_array( $key, $parent_options ) ) {
				$parent_options_value = get_option( 'woocommerce_' . YITH_Stripe_Connect::$gateway_id . '_settings', array() );

				if ( isset( $parent_options_value[ $key ] ) ) {
					return $parent_options_value[ $key ];
				}

				return $empty_value;
			}

			return parent::get_option( $key, $empty_value );
		}

		/**
		 * Check if the gateway is available for use.
		 *
		 * @return bool
		 */
		public function is_available() {
			if ( function_exists( 'YITH_WC_Subscription' ) ) {
				$sbs_on_cart = is_callable( 'YWSBS_Subscription_Cart::cart_has_subscriptions' ) ? YWSBS_Subscription_Cart::cart_has_subscriptions() : YITH_WC_Subscription()->cart_has_subscriptions();
				if ( $sbs_on_cart ) {
					return false;
				}
			}

			return parent::is_available();
		}

		/**
		 * Handling payment and processing the order.
		 *
		 * @param int $order_id
		 *
		 * @return array
		 * @since 1.0.0
		 */
		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );

			try {
				if ( ! $order ) {
					throw new Exception( __( 'Unable to find order', 'yith-stripe-connect-for-woocommerce' ) );
				}

				$this->_current_order = $order;
				$this->log( 'info', 'Generating payment form for order ' . $order->get_order_number() . '.' );

				// init api handler.
				$this->init_stripe_connect_api();

				// create source.
				$source = $this->api_handler->create_source(
					[
						'type' => 'multibanco',
						'currency' => strtolower( $order->get_currency() ),
						'amount' => yith_wcsc_get_amount( $order->get_total(), $order->get_currency() ),

						/** APPLY_FILTERS: yith_wcstripe_connect_metadata
						*
						* Filter metadata sent to Stripe.
						*
						* @param array  $order_id, $order_email, $instance.
						* @param string 'multibanco_source' by default.
						*/
						'metadata' => apply_filters(
							'yith_wcstripe_connect_metadata',
							array(
								'order_id'    => $order->get_id(),
								'order_email' => yit_get_prop( $order, 'billing_email' ),
								'instance'    => $this->instance_url,
							),
							'multibanco_source'
						),
						'owner' => [
							'email'   => $order->get_billing_email(),
							'name'    => trim( sprintf( '%s %s', $order->get_billing_first_name(), $order->get_billing_last_name() ) ),
							'phone'   => $order->get_billing_phone(),
							'address' => [
								'city'        => $order->get_billing_city(),
								'country'     => $order->get_billing_country(),
								'line1'       => $order->get_billing_address_1(),
								'line2'       => $order->get_billing_address_2(),
								'postal_code' => $order->get_billing_postcode(),
								'state'       => $order->get_billing_state(),
							],
						],
						'redirect' => [
							'return_url' => $this->get_return_url( $order ),
						],
					]
				);

				if ( ! $source || ! $source->id ) {
					$this->log( 'error', 'Error while creating source for order' . $order->get_order_number() . '.' );

					throw new Exception( __( 'An error occurred while processing your payment, please try again later', 'yith-stripe-connect-for-woocommerce' ) );
				}

				// register source id within the order for future usage.
				$order->update_meta_data( 'yith_stripe_connect_source_id', $source->id );
				$order->update_meta_data( 'yith_stripe_connect_client_secret', $source->client_secret );
				$order->update_meta_data(
					'yith_stripe_connect_multibanco',
					[
						'amount'    => $order->get_formatted_order_total(),
						'entity'    => $source->multibanco->entity,
						'reference' => $source->multibanco->reference,
					]
				);

				$this->log( 'info', 'Created source ' . $source->id . ' for order ' . $order->get_order_number() . '.' );

				/** APPLY_FILTERS: yith_wcsc_multibanco_pending_order_status
				*
				* Filter the defaul multibanco order status.
				*
				* @param string           'on-hold' by default.
				* @param string $order_id Order ID.
				* @param string $order    Order obj.
				*/
				// switch order to on-hold and save order note.
				$order->set_status(
					apply_filters( 'yith_wcsc_multibanco_pending_order_status', 'on-hold', $order->get_id(), $order ),
					__( 'Awaiting for MultiBanco payment confirmation', 'yith-stripe-connect-for-woocommerce' )
				);
				$order->save();

				// finally, redirect to MB payment page.
				return array(
					'result' => 'success',
					'redirect' => $source->redirect['url'],
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
		 * Handle redirection from MultiBanco, processing chargeable sources
		 *
		 * @param int $order_id Order id.
		 * @return void
		 */
		public function process_order_redirect( $order_id ) {
			// retrieve transaction data.
			$secret = isset( $_GET['client_secret'] ) ? sanitize_text_field( wp_unslash( $_GET['client_secret'] ) ) : '';
			$source_id = isset( $_GET['source'] ) ? sanitize_text_field( wp_unslash( $_GET['source'] ) ) : '';

			if ( ! $secret || ! $source_id ) {
				return;
			}

			// retrieve order.
			$order = wc_get_order( $order_id );

			if ( ! $order || ! $order->has_status( 'on-hold' ) ) {
				return;
			}

			// check if transaction data match stored data.
			if ( $secret != $order->get_meta( 'yith_stripe_connect_client_secret', true ) || $source_id != $order->get_meta( 'yith_stripe_connect_source_id', true ) ) {
				return;
			}

			// init api.
			$this->init_stripe_connect_api();

			// retrieve source and verify if it can be charged.
			$source = $this->api_handler->get_source( $source_id );

			if ( ! $source ) {
				return;
			}

			switch ( $source->status ) {
				case 'cancelled':
				case 'failed':
					// order cannot be paid, switch to cancelled.
					$order->set_status( 'cancelled', __( 'Source payment failed', 'yith-stripe-connect-for-woocommerce' ) );

					$this->log( 'error', 'Source ' . $source->id . ' cannot be charged, switched order ' . $order->get_order_number() . ' to cancelled.' );

					wc_add_notice( __( 'Payment failed because MultiBanco transaction wasn\'t completed correctly; please, try again', 'yith-stripe-connect-for-woocommerce' ), 'error' );
					break;
				case 'chargeable':
					// source can be charged, try to proceed with payment.
					try {
						$this->process_chargeable_source( $order, $source->id );
					} catch ( Exception $e ) {
						$order->set_status( 'cancelled', __( 'Source payment failed', 'yith-stripe-connect-for-woocommerce' ) );

						$this->log( 'error', 'Error while collecting payment from source ' . $source->id . ' for order ' . $order->get_order_number() . '.' );

						wc_add_notice( __( 'Payment failed because MultiBanco transaction wasn\'t completed correctly; please, try again', 'yith-stripe-connect-for-woocommerce' ), 'error' );
					}
			}

			wp_safe_redirect( parent::get_return_url( $order ) );
			die;
		}

		/**
		 * Create charge from chargeable source
		 *
		 * @param \WC_Order $order     Order being processed.
		 * @param string    $source_id Source id.
		 *
		 * @return bool Status of the operation
		 * @throws Exception When something fails with API call.
		 */
		public function process_chargeable_source( $order, $source_id ) {
			$this->init_stripe_connect_api();

			$charge = $this->api_handler->create_charge(
				[
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
					'description' => apply_filters( 'yith_wcsc_charge_description', sprintf( __( '%1$s - Order %2$s', 'yith-stripe-connect-for-woocommerce' ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number(), $order->get_id() ),

					/** APPLY_FILTERS: yith_wcstripe_connect_metadata
					*
					* Filter metadata sent to Stripe.
					*
					* @param array  $order_id, $order_email, $instance.
					* @param string 'charge' by default.
					*/
					'metadata'    => apply_filters(
						'yith_wcstripe_connect_metadata',
						array(
							'order_id'    => $order->get_id(),
							'order_email' => yit_get_prop( $order, 'billing_email' ),
							'instance'    => $this->instance_url,
						),
						'charge'
					),
					'amount'   => yith_wcsc_get_amount( $order->get_total(), $order->get_currency() ),
					'currency' => strtolower( $order->get_currency() ),
					'source'   => $source_id,
				]
			);

			if ( isset( $charge->id ) ) {
				$this->log( 'info', 'Created charge ' . $charge->id . ' for order ' . $order->get_order_number() . '.' );

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
				}

				// Add order note.
				// translators: 1. Stripe charge id.
				$order->add_order_note( sprintf( __( 'Stripe Connect payment approved (ID: %s)', 'yith-stripe-connect-for-woocommerce' ), $charge->id ) );

				return true;
			}

			return false;
		}

		/* === MB INSTRUCTIONS METHODS === */

		/**
		 * Retrieve textual description of MultiBanco transaction, and instructions to proceed
		 *
		 * @param int  $order_id Order id.
		 * @param bool $plain_text Whether to show plain text content (for emails) or HTML one (emails/thank you page).
		 * @return bool|string Informations for the order; false on failure.
		 */
		public function retrieve_instructions( $order_id, $plain_text = false ) {
			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				return false;
			}

			$mb_information = $order->get_meta( 'yith_stripe_connect_multibanco', true );

			if ( ! $mb_information ) {
				return false;
			}

			$info = '';

			if ( $plain_text ) {
				$info .= esc_html__( 'MULTIBANCO TRANSACTIONS DETAILS:', 'yith-stripe-connect-for-woocommerce' ) . "\n\n";
				$info .= "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
				$info .= esc_html__( 'Amount:', 'yith-stripe-connect-for-woocommerce' ) . "\n\n";
				$info .= $mb_information['amount'] . "\n\n";
				$info .= esc_html__( 'Entity:', 'yith-stripe-connect-for-woocommerce' ) . "\n\n";
				$info .= $mb_information['entity'] . "\n\n";
				$info .= esc_html__( 'Reference:', 'yith-stripe-connect-for-woocommerce' ) . "\n\n";
				$info .= $mb_information['reference'] . "\n\n";
				$info .= "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
			} else {
				$info = sprintf(
					"<h3>%s</h3>
					<ul class='woocommerce-order-overview woocommerce-thankyou-order-details order_details'>
						<li class='woocommerce-order-overview__order order'>
							%s <strong>%s</strong>
						</li>
						<li class='woocommerce-order-overview__order order'>
							%s <strong>%s</strong>
						</li>
						<li class='woocommerce-order-overview__order order'>
							%s <strong>%s</strong>
						</li>
					</ul>",
					esc_html__( 'MULTIBANCO TRANSACTIONS DETAILS:', 'yith-stripe-connect-for-woocommerce' ),
					esc_html__( 'Amount:', 'yith-stripe-connect-for-woocommerce' ),
					$mb_information['amount'],
					esc_html__( 'Entity:', 'yith-stripe-connect-for-woocommerce' ),
					$mb_information['entity'],
					esc_html__( 'Reference:', 'yith-stripe-connect-for-woocommerce' ),
					$mb_information['reference']
				);
			}

			return $info;
		}

		/**
		 * Output for the order received page.
		 *
		 * @param int $order_id Order id.
		 */
		public function thank_you_page_instructions( $order_id ) {
			$order = wc_get_order( $order_id );

			if ( ! $order || ! $order->has_status( 'on-hold' ) ) {
				return;
			}

			$instructions = $this->retrieve_instructions( $order_id );

			if ( $instructions ) {
				echo wp_kses_post( $instructions );
			}
		}

		/**
		 * Output for the order received page.
		 *
		 * @param \WC_Order $order Order.
		 * @param bool      $sent_to_admin Whether email is sent to admin or to customer.
		 * @param bool      $plain_text Whether email contains plain text content or HTML one.
		 *
		 * @return void
		 */
		public function email_instructions( $order, $sent_to_admin, $plain_text ) {
			$order_id       = $order->get_id();
			$payment_method = $order->get_payment_method();

			if ( ! $sent_to_admin && $this->id === $payment_method && $order->has_status( 'on-hold' ) ) {
				echo wp_kses_post( $this->retrieve_instructions( $order_id, $plain_text ) );
			}
		}

		/* === UTILITY METHODS === */

		/**
		 * Get return url for payment intent
		 *
		 * @param \WC_Order $order Order.
		 *
		 * @return string Return url
		 */
		public function get_return_url( $order = null ) {
			$redirect = parent::get_return_url( $order );

			if ( ! $order ) {
				return $redirect;
			}

			$redirect = add_query_arg(
				array(
					'order_id' => $order->get_id(),
					'yith_wcsc_off_session_action' => 'multibanco',
				),
				$redirect
			);

			return $redirect;
		}

		/**
		 * Log to txt file
		 *
		 * @param string $level   Level of message (info/error/warning/etc...).
		 * @param string $message Message to log.
		 *
		 * @since 1.0.0
		 */
		public function log( $level, $message ) {
			if ( isset( $this->log, $this->log_enabled ) && $this->log_enabled ) {
				$this->log->log(
					$level,
					$message,
					array(
						'source' => 'stripe-connect-multibanco',
						'_legacy' => true,
					)
				);
			}
		}

		/**
		 * Initialise Gateway Settings Form Fields.
		 */
		public function init_form_fields() {
			$this->form_fields = include( YITH_WCSC_OPTIONS_PATH . 'settings-sc-multibanco-gateway.php' );
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

	}

}