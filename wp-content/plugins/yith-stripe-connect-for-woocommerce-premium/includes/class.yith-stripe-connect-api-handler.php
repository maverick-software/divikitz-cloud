<?php
/**
 * API Handler class
 *
 * This file belongs to the YITH Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @class      YITH_Stripe_Connect_API_Handler
 * @package    YITH Stripe Connect for WooCommerce
 * @since      1.0.0
 * @author     YITH
 */

if ( ! defined( 'YITH_WCSC_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

use \Stripe\Stripe;
use \Stripe\Charge;
use \Stripe\Account;
use \Stripe\OAuth;
use \Stripe\Customer;
use Stripe\StripeObject;
use \Stripe\PaymentIntent;
use \Stripe\PaymentMethod;
use \Stripe\SetupIntent;
use \Stripe\Source;

if ( ! class_exists( 'YITH_Stripe_Connect_API_Handler' ) ) {
	/**
	 * Class YITH_Stripe_Connect_API_Handler
	 *
	 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
	 */
	class YITH_Stripe_Connect_API_Handler {

		/**
		 * Plugin version
		 *
		 * @var string
		 * @since 1.0
		 */
		public $version = YITH_WCSC_VERSION;

		/**
		 * StripeObject Instance
		 *
		 * @var YITH_Stripe_Connect_API_Handler
		 * @since  1.0
		 * @access protected
		 */
		protected static $_instance = null;

		/**
		 * Current environment (yes -> dev, no -> prod)
		 *
		 * @var string
		 */
		public $_test_live = null;

		/**
		 * Current environment (dev|prod)
		 *
		 * @var string
		 */
		public $_env = null;

		/**
		 * Main Instance
		 *
		 * @var YITH_Stripe_Connect_Gateway
		 * @since  1.0
		 * @access protected
		 */
		protected $_stripe_connect_gateway = null;

		/**
		 * Construct
		 *
		 * @author Francisco Mateo
		 * @since  1.0
		 */
		public function __construct() {
			require_once( YITH_WCSC_VENDOR_PATH . 'autoload.php' );

			// Retrieve gateway object.
			$this->_stripe_connect_gateway = YITH_Stripe_Connect()->get_gateway( false );

			if ( ! $this->_stripe_connect_gateway ) {
				return;
			}

			$this->_test_live = $this->_stripe_connect_gateway->get_option( 'test-live' );
			$this->_env       = ( 'yes' === $this->_test_live ) ? 'dev' : 'prod';

			$this->init_handler();
		}

		/**
		 * Main plugin Instance
		 *
		 * @return YITH_Stripe_Connect_API_Handler Main instance
		 * @author Francisco Mateo
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Set correct API key for current configuration
		 *
		 * @return void
		 */
		public function init_handler() {
			$secret_api_key = ( 'yes' == $this->_test_live ) ? $this->_stripe_connect_gateway->get_option( 'api-secret-test-key' ) : $this->_stripe_connect_gateway->get_option( 'api-secret-live-key' );

			Stripe::setAppInfo( 'YITH Stripe Connect for WooCommerce', YITH_WCSC_VERSION, 'https://yithemes.com' );
			Stripe::setApiVersion( YITH_WCSC_API_VERSION );
			Stripe::setApiKey( $secret_api_key );
		}

		/* === ACCOUNT RELATED API === */

		/**
		 * Creates a connected account on Stripe
		 *
		 * @param array $args Array of parameters to use for account creation.
		 *
		 * @return StripeObject|bool Created account or false on failure
		 */
		public function create_account( $args = array() ) {
			try {
				$acct = Account::create( $args );
			} catch ( Exception $e ) {
				return false;
			}

			return $acct;
		}

		/**
		 * Retrieves a connected account by ID
		 *
		 * @param string $id Account id.
		 *
		 * @return StripeObject|bool Retrieved account or false on failure
		 */
		public function retrieve_account( $id ) {
			try {
				$acct = Account::retrieve( $id );

				return $acct;
			} catch ( Exception $e ) {
				return false;
			}
		}

		/**
		 * Authorizes an account for the application
		 *
		 * @param string $stripe_user_email Email used to register account on Stripe.
		 *
		 * @return StripeObject|bool Connected account or false on failure
		 */
		public function authorize_account( $stripe_user_email ) {
			try {
				$client_id       = $this->_stripe_connect_gateway->get_option( 'api-' . $this->_env . '-client-id' );
				$user_authorized = OAuth::authorizeUrl(
					array(
						'client_id'   => $client_id,
						'stripe_user' => $stripe_user_email,
					)
				);
				$this->_stripe_connect_gateway->log( 'info', 'Authorize Account: Account with client_id:"' . $client_id . '" and stripe_user_email:"' . $stripe_user_email . '" authorized' );

				return $user_authorized;
			} catch ( Exception $e ) {
				$this->_stripe_connect_gateway->log( 'error', 'Authorize Account: Could not be authorize account...' . $e->getMessage() );

				return false;
			}
		}

		/**
		 * Deauthorize the account
		 *
		 * @param string $stripe_user_id Id of the user to deauthorize.
		 *
		 * @return StripeObject|bool Stripe customer or false on failure
		 */
		public function deauthorize_account( $stripe_user_id ) {
			try {
				$client_id         = $this->_stripe_connect_gateway->get_option( 'api-' . $this->_env . '-client-id' );
				$user_deauthorized = OAuth::deauthorize(
					array(
						'client_id'      => $client_id,
						'stripe_user_id' => $stripe_user_id,
					),
					array()
				);

				$this->_stripe_connect_gateway->log( 'info', 'Deauthorize Account: Account with client_id:"' . $client_id . '" deauthorized' );
			} catch ( \Stripe\Exception\OAuth\InvalidClientException $e ) {
				$this->_stripe_connect_gateway->log( 'warning', 'Deauthorize Account: Account with client_id:"' . $client_id . '" have been deauthorized previously' );

				return false;
			} catch ( Exception $e ) {
				$this->_stripe_connect_gateway->log( 'error', 'Deauthorize Account: Could not be deauthorize account...' . $e->getMessage() );

				return false;
			}

			return $user_deauthorized;
		}

		/**
		 * Retrieves link for OAuth connection
		 *
		 * @return string|bool Connection url or false on failure
		 */
		public function get_OAuth_link() {
			try {
				/** APPLY_FILTERS: yith_wcsc_oauth_link_args
				*
				* Filter the args when getting the OAuth link.
				*
				* @param array Default args.
				*/
				$args       = apply_filters(
					'yith_wcsc_oauth_link_args',
					array(
						'client_id'    => $this->_stripe_connect_gateway->get_option( 'api-' . $this->_env . '-client-id' ),
						'redirect_uri' => trailingslashit( wc_get_page_permalink( 'myaccount' ) ) . 'stripe-connect',
						'scope'        => 'read_write',
					)
				);
				$oauth_link = OAuth::authorizeUrl( $args );
			} catch ( Exception $e ) {
				return false;
			}

			return $oauth_link;
		}

		/**
		 * Retrieves unique token after OAuth connection
		 *
		 * @param string $code Code returned by Stripe after OAuth connection.
		 *
		 * @return string|bool Unique authorization code for the user, or false on failure
		 */
		public function get_OAuth_token( $code ) {
			try {
				$client_id = $this->_stripe_connect_gateway->get_option( 'api-' . $this->_env . '-client-id' );
				$args      = array(
					'client_id'  => $client_id,
					'code'       => $code,
					'grant_type' => 'authorization_code',
				);
				$token     = OAuth::token( $args );
			} catch ( Exception $e ) {
				return false;
			}

			return $token;
		}

		/* === CHARGE RELATED API === */

		/**
		 * Create a charges
		 *
		 * @param array $args Array of parameters to use for charge creation.
		 * @param array $options Array of options for the API call.
		 *
		 * @return StripeObject|bool Charge object or false on failure
		 */
		public function create_charge( $args = array(), $options = array() ) {
			try {
				return Charge::create( $args, $options );
			} catch ( Exception $e ) {
				return false;
			}
		}

		/**
		 * Update a charge
		 *
		 * @param string $charge_id Charge id.
		 * @param array  $params    Array of parameters to use for charge update.
		 * @param array  $options   Array of options for the API call.
		 * @param bool   $return    Whether to return Charge object; if set to false, method will return a bool representing status of the operation.
		 *
		 * @return StripeObject|bool Charge object or bool representing status of the operation.
		 */
		public function update_charge( $charge_id, $params = array(), $options = array(), $return = true ) {
			$allowed_properties = array(
				'customer',
				'description',
				'metadata',
				'receipt_email',
				'shipping',
				'transfer_group',
				'fraud_details',
			);

			$to_update = array_intersect_key( $params, array_flip( $allowed_properties ) );

			try {
				Charge::update( $charge_id, $to_update, $options );

				if ( $return ) {
					return $this->retrieve_charge( $charge_id, $options );
				}
			} catch ( Exception $e ) {
				return false;
			}

			return true;
		}

		/**
		 * Retrieves a charge object
		 *
		 * @param string $id Charge id.
		 * @param array  $options Array of options for the API call.
		 *
		 * @return StripeObject|bool Retrieved charge or false on failure
		 */
		public function retrieve_charge( $id, $options = array() ) {
			try {
				$charge = Charge::retrieve( $id, $options );

				return $charge;
			} catch ( Exception $e ) {
				return false;
			}
		}

		/**
		 * Create a transfer
		 *
		 * @param array $args Array of parameters to use for Transfer creation.
		 *
		 * @return StripeObject|bool Transfer created or false on failure
		 */
		public function create_transfer( $args = array() ) {
			try {
				$transfer = \Stripe\Transfer::create( $args );
			} catch ( Exception $e ) {
				return array( 'error_transfer' => $e->getMessage() );
			}

			return $transfer;
		}

		/* === CUSTOMER RELATED API === */

		/**
		 * New customer
		 *
		 * @param array $params  Array of parameters for customer creation.
		 * @param array $options Array of options for the API call.
		 *
		 * @return Customer
		 * @throws \Stripe\Exception\ApiErrorException Throws this exception when there is an error with the API call.
		 * @since 1.0.0
		 */
		public function create_customer( $params, $options = array() ) {
			return Customer::create( $params, $options );
		}

		/**
		 * Retrieve customer
		 *
		 * @param string|Customer $customer Customer object or ID.
		 * @param array           $options  Array of options for the API call.
		 *
		 * @return Customer
		 * @throws \Stripe\Exception\ApiErrorException Throws this exception when there is an error with the API call.
		 * @since 1.0.0
		 */
		public function get_customer( $customer, $options = array() ) {
			if ( is_a( $customer, '\Stripe\Customer' ) ) {
				return $customer;
			}

			return Customer::retrieve(
				array(
					'id'     => $customer,
					'expand' => array( 'sources' ),
				),
				$options
			);
		}

		/**
		 * Update customer
		 *
		 * @param \Stripe\Customer|string $customer Customer object or ID.
		 * @param array                   $params   Array of parameters to update.
		 * @param bool                    $return   Whether to return Customer object or bool representing status of the operation.
		 *
		 * @return \Stripe\Customer|bool Customer object or bool representing status of the operation.
		 * @throws \Stripe\Exception\ApiErrorException Throws error when customer can't be updated.
		 * @since 1.0.0
		 */
		public function update_customer( $customer, $params, $return = true ) {
			$allowed_properties = array(
				'address',
				'description',
				'email',
				'metadata',
				'name',
				'phone',
				'shipping',
				'balance',
				'cash_balance',
				'coupon',
				'default_source',
				'invoice_prefix',
				'invoice_settings',
				'next_invoice_sequence',
				'preferred_locales',
				'promotion_code',
				'source',
				'tax',
				'tax_exempt',
			);

			$customer_id = $customer instanceof Customer ? $customer->id : $customer;
			$to_update   = array_intersect_key( $params, array_flip( $allowed_properties ) );

			Customer::update( $customer_id, $to_update );

			if ( $return ) {
				return $this->get_customer( $customer );
			}

			// save.
			return true;
		}

		/* === CARD RELATED API === */

		/**
		 * Create a card
		 *
		 * @param \Stripe\Customer|string $customer Customer object or ID.
		 * @param string                  $token    Token to create.
		 * @param string                  $type     Type of item to create.
		 *
		 * @return \Stripe\StripeObject
		 * @depreacted
		 *
		 * @throws \Stripe\Exception\ApiErrorException Throws exception when card cannot be created for customer.
		 * @since 1.0.0
		 */
		public function create_card( $customer, $token, $type = 'card' ) {
			$customer = $this->get_customer( $customer );

			$result = $customer->sources->create(
				array(
					$type => $token,
				)
			);

			/** DO_ACTION: yith_wcstripe_connect_card_created
			*
			* Adds an action before retrieve the result of creating a new card.
			*
			* @param $customer Current customer.
			* @param $token    Token to create.
			* @param $type     Type of item to create.
			*/
			do_action( 'yith_wcstripe_connect_card_created', $customer, $token, $type );

			return $result;
		}

		/**
		 * Retrieve a card object for the customer
		 *
		 * @param \Stripe\Customer| string $customer Customer object or ID.
		 * @param string                   $card_id  Card id.
		 * @param array                    $params   Additional parameters.
		 *
		 * @return \Stripe\StripeObject
		 * @depreacted
		 *
		 * @since 1.0.0
		 */
		public function get_card( $customer, $card_id, $params = array() ) {
			$card = $customer->sources->retrieve( $card_id, $params );

			return $card;
		}

		/**
		 * Se the default card for the customer
		 *
		 * @param \Stripe\Customer|string $customer Customer object or ID.
		 * @param string                  $card_id  Card to set as default.
		 *
		 * @return \Stripe\StripeObject
		 * @depreacted
		 *
		 * @throws \Stripe\Exception\ApiErrorException Throws exception when card or customer cannot be found.
		 * @since 1.0.0
		 */
		public function set_default_card( $customer, $card_id ) {
			$result = $this->update_customer(
				$customer,
				array(
					'default_source' => $card_id,
				)
			);

			/** DO_ACTION: yith_wcstripe_connect_card_set_default
			*
			* Adds an action before setting a default card for a customer.
			*
			* @param $customer Current customer.
			* @param $card_id  ID of the card.
			*/
			do_action( 'yith_wcstripe_connect_card_set_default', $customer, $card_id );

			return $result;
		}

		/* === SOURCE RELATED API === */

		/**
		 * Retrieve source object
		 *
		 * @param string $source Source id.
		 *
		 * @return \Stripe\StripeObject|bool
		 */
		public function get_source( $source ) {
			try {
				return Source::retrieve( $source );
			} catch ( Exception $e ) {
				return false;
			}
		}

		/**
		 * Create a source
		 *
		 * @param array $params Array of parameters for source creation.
		 *
		 * @return \Stripe\StripeObject
		 *
		 * @throws Exception Throws an exception when an error occurs with api handling.
		 * @since 1.0.0
		 */
		public function create_source( $params ) {
			$result = Source::create( $params );

			/** DO_ACTION: yith_wcstripe_connect_card_created
			*
			* Adds an action before retrieve the result of creating a source.
			*
			* @param $result Resutl of Source::create.
			* @param $params Array of parameters for source creation.
			*/
			do_action( 'yith_wcstripe_connect_card_created', $result, $params );

			return $result;
		}

		/**
		 *  Remove a source from a customer.
		 *
		 * @param \Stripe\Customer|string $customer_id Customer object or ID.
		 * @param string                  $source_id   Source ID.
		 *
		 * @return \Stripe\Customer
		 *
		 * @throws Exception Throws exception when source or customer cannot be found.
		 * @since 1.1.0
		 */
		public function delete_source( $customer_id, $source_id ) {
			$customer = $this->get_customer( $customer_id );
			/**
			 * Retrieve source object from stripe
			 *
			 * @var \Stripe\Source $source
			 */
			$source = $customer->sources->retrieve( $source_id );
			$source->detach();

			return $customer;
		}

		/* === PAYMENT INTENTS METHODS === */

		/**
		 * Retrieve a payment intent object on stripe, using id passed as argument
		 *
		 * @param string $payment_intent_id Payment intent id.
		 * @param array  $options           Array of optionss to use for API call.
		 *
		 * @return \Stripe\StripeObject|bool Payment intent or false
		 */
		public function get_intent( $payment_intent_id, $options = array() ) {
			try {
				return PaymentIntent::retrieve( $payment_intent_id, $options );
			} catch ( \Stripe\Exception\ApiErrorException $e ) {
				return false;
			}
		}

		/**
		 * Create a payment intent object on stripe, using parameters passed as argument
		 *
		 * @param array $params Array of parameters used to create Payment intent.
		 * @param array $options Array of options for the API call.
		 *
		 * @return \Stripe\StripeObject|bool Brand new payment intent or false on failure
		 * @throws \Stripe\Exception\ApiErrorException Throws this exception when API error occurs.
		 */
		public function create_intent( $params, $options = array() ) {
			return PaymentIntent::create(
				$params,
				array_merge(
					$options,
					array(
						'idempotency_key' => self::generate_random_string( 24, $params ),
					)
				)
			);
		}

		/**
		 * Update a payment intent object on stripe, using parameters passed as argument
		 *
		 * @param string $payment_intent_id Payment Intent to update.
		 * @param array  $params            Array of parameters used to update Payment intent.
		 * @param array  $options           Array of options for the api call.
		 *
		 * @return \Stripe\StripeObject|bool Updated payment intent or false on failure
		 */
		public function update_intent( $payment_intent_id, $params, $options = array() ) {
			try {
				return PaymentIntent::update( $payment_intent_id, $params, $options );
			} catch ( \Stripe\Exception\ApiErrorException $e ) {
				return false;
			}
		}

		/**
		 * Retrieve a payment method object on stripe, using id passed as argument
		 *
		 * @param string $payment_method_id Payment method id.
		 * @param array  $options           Array of optionss to use for API call.
		 *
		 * @return \Stripe\StripeObject|bool Payment intent or false
		 */
		public function get_payment_method( $payment_method_id, $options = array() ) {
			try {
				return PaymentMethod::retrieve( $payment_method_id, $options );
			} catch ( \Stripe\Exception\ApiErrorException $e ) {
				return false;
			}
		}

		/**
		 * Detach a payment method from the customer
		 *
		 * @param string $payment_method_id Payment method id.
		 * @param array  $options           Array of optionss to use for API call.
		 *
		 * @return StripeObject|bool Detached payment method, or false on failure
		 */
		public function delete_payment_method( $payment_method_id, $options = array() ) {
			try {
				return PaymentMethod::retrieve( $payment_method_id, $options )->detach();
			} catch ( \Stripe\Exception\ApiErrorException $e ) {
				return false;
			}
		}

		/**
		 * Retrieve a setup intent object on stripe, using id passed as argument
		 *
		 * @param string $setup_intent_id Setup intent id.
		 * @param array  $options         Array of options to use for API call.
		 *
		 * @return \Stripe\StripeObject|bool Setup intent or false
		 */
		public function get_setup_intent( $setup_intent_id, $options = array() ) {
			try {
				return SetupIntent::retrieve( $setup_intent_id, $options );
			} catch ( \Stripe\Exception\ApiErrorException $e ) {
				return false;
			}
		}

		/**
		 * Create a payment intent object on stripe, using parameters passed as argument
		 *
		 * @param array $params  Array of parameters used to create Payment intent.
		 * @param array $options Array of options to use for API call.
		 *
		 * @return \Stripe\StripeObject|bool Brand new payment intent or false on failure
		 */
		public function create_setup_intent( $params, $options = array() ) {
			try {
				return SetupIntent::create(
					$params,
					array_merge(
						array( 'idempotency_key' => self::generate_random_string( 24, $params ) ),
						$options
					)
				);
			} catch ( \Stripe\Exception\ApiErrorException $e ) {
				return false;
			}
		}

		/**
		 * Update a setup intent object on stripe, using parameters passed as argument
		 *
		 * @param string $setup_intent_id Intent id.
		 * @param array  $params          Array of parameters used to update Payment intent.
		 * @param array  $options         Array of options to use for API call.
		 *
		 * @return \Stripe\StripeObject|bool Updated payment intent or false on failure
		 */
		public function update_setup_intent( $setup_intent_id, $params, $options = array() ) {
			try {
				return SetupIntent::update( $setup_intent_id, $params, $options );
			} catch ( \Stripe\Exception\ApiErrorException $e ) {
				return false;
			}
		}

		/**
		 * Retrieve a PaymentIntent or a SetupIntent, depending on the id that it receives
		 *
		 * @param string $id      Id of the intent that method should retrieve.
		 * @param array  $options Array of options to use for API call.
		 *
		 * @return \Stripe\StripeObject|bool Intent or false on failure
		 */
		public function get_correct_intent( $id, $options = array() ) {
			try {
				if ( strpos( $id, 'seti' ) !== false ) {
					return $this->get_setup_intent( $id, $options );
				} else {
					return $this->get_intent( $id, $options );
				}
			} catch ( Exception $e ) {
				return false;
			}
		}

		/**
		 * Update a PaymentIntent or a SetupIntent, depending on the id that it receives
		 *
		 * @param string $id      Id of the intent that method should retrieve.
		 * @param array  $params  Array of parameters that should be used to update intent.
		 * @param array  $options Array of parameters that should be used to update intent.
		 *
		 * @return \Stripe\StripeObject|bool Intent or false on failure
		 */
		public function update_correct_intent( $id, $params, $options = array() ) {
			try {
				if ( strpos( $id, 'seti' ) !== false ) {
					return $this->update_setup_intent( $id, $params, $options );
				} else {
					return $this->update_intent( $id, $params, $options );
				}
			} catch ( Exception $e ) {
				return false;
			}
		}

		/* === BALANCE RELATED API === */

		/**
		 * Retrieve currently active balance
		 *
		 * @param array|null $options Array of options.
		 *
		 * @return \Stripe\Balance|bool Balance, or false on failure
		 * @throws \Stripe\Exception\ApiErrorException Throws exception when balance cannot be retrieved.
		 * @since 2.0.4
		 */
		public function get_balance( $options = null ) {
			try {
				return \Stripe\Balance::retrieve( $options );
			} catch ( \Stripe\Exception\ApiConnectionException $e ) {
				return false;
			}
		}

		/**
		 * Get balance transaction
		 *
		 * @param int $transaction_id Balance transaction id.
		 *
		 * @return \Stripe\BalanceTransaction|bool Object
		 * @throws \Stripe\Exception\ApiErrorException Throws exception when balance transaction cannot be retrieved.
		 * @since 2.0.4
		 */
		public function get_balance_transaction( $transaction_id ) {

			try {
				return \Stripe\BalanceTransaction::retrieve( $transaction_id );
			} catch ( \Stripe\Exception\ApiConnectionException $e ) {
				return false;
			}
		}

		/* === UTILS === */

		/**
		 * Generate a semi-random string
		 *
		 * @param int   $length Length of the random string.
		 * @param array $params Array of additional params.
		 *
		 * @return string Semi-randomic string
		 *
		 * @since 1.0.0
		 */
		protected static function generate_random_string( $length = 24, $params = array() ) {
			$characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTU';
			$characters_length = strlen( $characters );
			$random_string     = '';
			for ( $i = 0; $i < $length; $i ++ ) {
				$random_string .= $characters[ rand( 0, $characters_length - 1 ) ];
			}

			return $random_string;
		}
	}
}
