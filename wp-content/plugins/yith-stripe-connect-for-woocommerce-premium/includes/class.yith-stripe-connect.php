<?php
/**
 * Main plugin class
 *
 * This file belongs to the YITH Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @class      YITH_Stripe_Connect
 * @package    YITH Stripe Connect for WooCommerce
 * @since      1.0.0
 * @author     YITH
 */

if ( ! defined( 'YITH_WCSC_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

if ( ! class_exists( 'YITH_Stripe_Connect' ) ) {
	/**
	 * Class YITH_Stripe_Connect
	 *
	 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
	 */
	class YITH_Stripe_Connect {
		/**
		 * Plugin version
		 *
		 * @var string
		 * @since 1.0.0
		 */
		public $version = YITH_WCSC_VERSION;

		/**
		 * Plugin DB version
		 *
		 * @const string
		 * @since 1.0.0
		 */
		const YITH_WCSC_DB_VERSION = '1.0.1';

		/**
		 * Main Instance
		 *
		 * @var YITH_Stripe_Connect
		 * @since  1.0.0
		 * @access protected
		 */
		protected static $_instance = null;

		/**
		 * Main Admin Instance
		 *
		 * @var YITH_Stripe_Connect_Admin
		 * @since 1.0.0
		 */
		public $admin = null;

		/**
		 * Main Frontpage Instance
		 *
		 * @var YITH_Stripe_Connect_Frontend
		 * @since 1.0.0
		 */
		public $frontend = null;

		/**
		 * Stripe Connect WC API
		 *
		 * @var YITH_Stripe_Connect_WC_API
		 * @since 1.0.0
		 */
		public $stripe_connect_wc_api = null;

		/**
		 * Stripe Connect Cron Job
		 *
		 * @var YITH_Stripe_Connect_Cron_Job
		 * @since 1.0.0
		 */
		public $stripe_connect_cron_job = null;

		/**
		 * Stripe gateway id
		 *
		 * @var string ID of specific gateway
		 * @since 1.0
		 */
		public static $gateway_id = 'yith-stripe-connect';

		/**
		 * The gateway object
		 *
		 * @var WC_Payment_Gateway_CC
		 * @since 1.0
		 */
		protected $gateway = null;

		/**
		 * Construct
		 *
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 * @since  1.0.0
		 */
		public function __construct() {

			/** APPLY_FILTERS: yith_wcsc_require_class
			*
			* Filter the required clasees for the plugin to work.
			*
			* @param array Array of the needed classes.
			*/
			// Require Main Files.
			$require = apply_filters(
				'yith_wcsc_require_class',
				array(
					'common'   => array(
						'includes/functions.yith-wcsc.php',
						'includes/class.yith-stripe-connect-customer.php',
						'includes/class.yith-stripe-connect-api-handler.php',
						'includes/class.yith-stripe-connect-receivers.php',
						'includes/class.yith-stripe-connect-commissions.php',
						'includes/class.yith-stripe-connect-wc-api.php',
						'includes/class.yith-stripe-connect-cron-job.php',
						'includes/gateways/class.yith-stripe-connect-gateway.php',
						'includes/gateways/class.yith-stripe-connect-multibanco-gateway.php',
					),
					'frontend' => array(
						'includes/class.yith-stripe-connect-frontend.php',
					),
					'admin'    => array(
						'includes/class.yith-stripe-connect-admin.php',
					),
				)
			);

			$this->_require( $require );

			// Load Plugin Framework.
			add_action( 'plugins_loaded', array( $this, 'plugin_fw_loader' ), 15 );
			add_action( 'plugins_loaded', array( $this, 'privacy_loader' ), 20 );

			// register plugin to licence/update system.
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );

			// Load Plugin Integrations.
			add_action( 'plugins_loaded', array( $this, 'load_integrations' ), 15 );

			// Plugins Init.
			add_action( 'init', array( $this, 'init' ), 5 );

			// Stripe Connect Gateway.
			add_filter( 'woocommerce_payment_gateways', array( $this, 'add_stripe_connect_gateway' ) );

			// Redirect Order Handling.
			add_action( 'template_redirect', array( $this, 'process_order_redirect' ) );

			// Ajax Handling.
			add_action( 'wp_ajax_yith_stripe_connect_refresh_intent', array( $this, 'refresh_intent' ) );
			add_action( 'wp_ajax_nopriv_yith_stripe_connect_refresh_intent', array( $this, 'refresh_intent' ) );

			add_action( 'wc_ajax_yith_stripe_connect_verify_intent', array( $this, 'verify_intent' ) );

			// Token Method.
			add_action( 'woocommerce_payment_token_deleted', array( $this, 'delete_token_from_stripe' ), 10, 2 );
			add_action( 'woocommerce_payment_token_set_default', array( $this, 'set_default_token_on_stripe' ), 10, 2 );

			// Emails init.
			add_filter( 'woocommerce_email_classes', array( $this, 'register_email_classes' ) );
			add_filter( 'woocommerce_email_actions', array( $this, 'register_email_actions' ) );
			add_filter( 'woocommerce_locate_core_template', array( $this, 'register_woocommerce_template' ), 10, 3 );
		}

		/**
		 * Main plugin Instance
		 *
		 * @return YITH_Stripe_Connect Main instance
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/* === MAIN PLUGIN METHODS === */

		/**
		 * Add the main classes file
		 *
		 * Include the admin and frontend classes
		 *
		 * @param array $main_classes The require classes file path.
		 *
		 * @return void
		 * @access protected
		 * @since  1.0.0
		 *
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 */
		protected function _require( $main_classes ) {
			foreach ( $main_classes as $section => $classes ) {
				foreach ( $classes as $class ) {
					if ( 'common' == $section || ( 'frontend' == $section && ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) || ( 'admin' == $section && is_admin() ) && file_exists( YITH_WCSC_PATH . $class ) ) {
						require_once( YITH_WCSC_PATH . $class );
					}
				}
			}

			/** DO_ACTION: yith_wcsc_require
			*
			* Adds an action when requiring plugin classes.
			*/
			do_action( 'yith_wcsc_require' );
		}

		/**
		 * Class Initialization
		 *
		 * Instance the admin class
		 *
		 * @return void
		 * @access protected
		 * @since  1.0.0
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 */
		public function init() {
			$this->_install_tables();

			$this->_install_main_features();

			$this->stripe_connect_wc_api   = new YITH_Stripe_Connect_WC_API();
			$this->stripe_connect_cron_job = new YITH_Stripe_Connect_Cron_Job();

			if ( is_admin() ) {
				$this->admin = new YITH_Stripe_Connect_Admin();
			}

			if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				$this->frontend = new YITH_Stripe_Connect_Frontend();
			}
		}

		/**
		 * Add Stripe Connect gateway to available list
		 *
		 * @param array $methods Array of available gateways.
		 *
		 * @return array Filtered array of gateways
		 */
		public function add_stripe_connect_gateway( $methods ) {
			$methods[] = 'YITH_Stripe_Connect_Gateway';
			$methods[] = 'YITH_Stripe_Connect_Multibanco_Gateway';

			return $methods;
		}

		/* === FRAMEWORK LOADING === */

		/**
		 * Load plugin framework
		 *
		 * @return void
		 * @since  1.0.0
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 */
		public function plugin_fw_loader() {
			if ( ! defined( 'YIT_CORE_PLUGIN' ) ) {
				global $plugin_fw_data;
				if ( ! empty( $plugin_fw_data ) ) {
					$plugin_fw_file = array_shift( $plugin_fw_data );
					require_once( $plugin_fw_file );
				}
			}
		}

		/**
		 * Load plugin framework
		 *
		 * @return void
		 * @since  1.0.0
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 */
		public function privacy_loader() {
			if ( class_exists( 'YITH_Privacy_Plugin_Abstract' ) ) {
				require_once( YITH_WCSC_PATH . 'includes/class.yith-stripe-connect-privacy.php' );
				new YITH_Stripe_Connect_Privacy();
			}
		}

		/* === GATEWAY UTILS === */

		/**
		 * Get the gateway object
		 *
		 * @param bool   $available Whether you want to retrieve gateway only when it's available or not.
		 * @param string $gateway   Gateway you want to retrieve.
		 *
		 * @return WC_Payment_Gateway_CC|bool
		 * @since 1.0.0
		 */
		public function get_gateway( $available = true, $gateway = '' ) {
			$gateway_id = self::$gateway_id;

			if ( ! empty( $gateway ) ) {
				$gateway_id .= '-' . $gateway;
			}

			if ( ! isset( $this->gateway[ $gateway_id ] ) || ! $this->gateway[ $gateway_id ] instanceof WC_Payment_Gateway ) {
				$gateways = $available ? WC()->payment_gateways()->get_available_payment_gateways() : WC()->payment_gateways()->payment_gateways();

				if ( ! isset( $gateways[ $gateway_id ] ) ) {
					return false;
				}

				$this->gateway[ $gateway_id ] = $gateways[ $gateway_id ];
			}

			return $this->gateway[ $gateway_id ];
		}

		/* === ORDER PROCESSING UTILS === */

		/**
		 * Process requests coming back from gateway after payment method verification (for 3D secure and SCA)
		 *
		 * @return void
		 */
		public function process_order_redirect() {
			if ( ! isset( $_GET['order_id'] ) || ! isset( $_GET['yith_wcsc_off_session_action'] ) ) {
				return;
			}

			$order_id = intval( $_GET['order_id'] );
			$action = sanitize_text_field( wp_unslash( $_GET['yith_wcsc_off_session_action'] ) );

			if ( ! $action || ! $order_id ) {
				return;
			}

			$gateway = $this->get_gateway( true, $action );

			if ( ! $gateway || ! method_exists( $gateway, 'process_order_redirect' ) ) {
				return;
			}

			try {
				$gateway->process_order_redirect( $order_id );
			} catch ( Exception $e ) {
				// do nothing.
			}
		}

		/* === AJAX HANDLING === */

		/**
		 * Refresh intent before moving forward with checkout process
		 *
		 * @return void
		 */
		public function refresh_intent() {
			check_ajax_referer( 'refresh-intent', 'yith_stripe_connect_refresh_intent', true );

			/** DO_ACTION: yith_wcsc_before_refresh_intent
			*
			* Adds an action before the refresh intent.
			*/
			do_action( 'yith_wcsc_before_refresh_intent' );

			$token       = isset( $_POST['selected_token'] ) ? intval( $_POST['selected_token'] ) : false;
			$is_checkout = isset( $_POST['is_checkout'] ) ? intval( $_POST['is_checkout'] ) : false;
			$order       = isset( $_POST['order'] ) ? $_POST['order'] : false;
			$gateway     = $this->get_gateway();

			wc_maybe_define_constant( 'YITH_STRIPE_CONNECT_DOING_CHECKOUT', $is_checkout );

			if ( $is_checkout && ! $order ) {
				if ( ! class_exists( 'YITH_Stripe_Connect_Checkout' ) ) {
					include_once( YITH_WCSC_PATH . 'includes/class-yith-stripe-connect-checkout.php' );
				}

				$checkout = new YITH_Stripe_Connect_Checkout();

				if ( ! $checkout->is_checkout_valid() ) {
					wp_send_json(
						array(
							'res'            => false,
							'checkout_valid' => false,
						)
					);
				}
			}

			try {
				$intent = $gateway->update_session_intent( $token, $order );
			} catch ( Exception $e ) {
				wp_send_json(
					array(
						'res'   => false,
						'error' => array(
							'code'    => $e->getCode(),
							'message' => $e->getMessage(),
						),
					)
				);
			}

			if ( ! $intent ) {
				wp_send_json(
					array(
						'res'   => false,
						'error' => array(
							'code'    => 0,
							'message' => __( 'There was an error during payment; please, try again later', 'yith-stripe-connect-for-woocommerce' ),
						),
					)
				);
			}

			wp_send_json(
				array(
					'res'           => true,
					'amount'        => isset( $intent->amount ) ? $intent->amount : 0,
					'currency'      => isset( $intent->currency ) ? $intent->currency : '',
					'intent_secret' => $intent->client_secret,
					'is_setup'      => $intent instanceof SetupIntent,
				)
			);
		}

		/**
		 * Verify intent after customer authentication
		 * Process actions required after authentication; if everything was fine redirect to thank you page, otherwise redirects
		 * to checkout with an error message
		 *
		 * @return void
		 */
		public function verify_intent() {
			$gateway  = $this->get_gateway();
			$order_id = isset( $_GET['order'] ) ? intval( $_GET['order'] ) : false;
			try {
				if ( ! $gateway ) {
					throw new Exception( __( 'Error while initializing gateway', 'yith-stripe-connect-for-woocommerce' ) );
				}

				// Retrieve the order.
				$order = wc_get_order( $order_id );

				if ( ! $order ) {
					throw new Exception( __( 'Missing order ID for payment confirmation', 'yith-stripe-connect-for-woocommerce' ) );
				}

				wc_maybe_define_constant( 'YITH_STRIPE_CONNECT_DOING_CHECKOUT', true );

				$result = $gateway->pay( $order );

				if ( is_wp_error( $result ) ) {
					throw new Exception( $result->get_error_message() );
				}

				if ( ! isset( $_GET['is_ajax'] ) ) {
					$redirect_url = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : $gateway->get_return_url( $order );

					wp_safe_redirect( $redirect_url );
				}

				exit;

			} catch ( Exception $e ) {
				// translators: 1. Specific error message.
				wc_add_notice( sprintf( __( 'Payment verification error: %s', 'woocommerce-gateway-stripe' ), $e->getMessage() ), 'error' );

				$redirect_url = WC()->cart->is_empty() ? wc_get_cart_url() : wc_get_checkout_url();

				if ( isset( $_GET['is_ajax'] ) ) {
					exit;
				}

				wp_safe_redirect( $redirect_url );
				exit;
			}
		}

		/* === INTEGRATION METHODS === */

		/**
		 * Set plugins integrations...
		 *
		 * Stripe Connect is integrated with YITH Affiliates for WooCommerce and YITH Multivendor for WooCommerce
		 */
		public function load_integrations() {
			// Integration for YITH WooCommerce Affiliates plugin.
			if ( class_exists( 'YITH_WCAF' ) ) {
				add_filter( 'yith_wcaf_available_gateways', array( $this, 'add_wcaf_stripe_connect_gateway' ) );
			}

			// Integration for YITH WooCommerce Subscription plugin.
			if ( defined( 'YITH_YWSBS_PREMIUM' ) && version_compare( YITH_YWSBS_VERSION, '1.4.5', '>' ) ) {
				require_once( YITH_WCSC_PATH . 'includes/class.yith-stripe-ywsbs-subscription.php' );
				require_once( YITH_WCSC_PATH . 'includes/gateways/class.yith-stripe-connect-source-gateway.php' );

				YITH_Stripe_YWSBS_Subscription::instance();
			}

			if ( defined( 'YITH_WCPO_PREMIUM' ) && class_exists( 'YITH_Pre_Order_Orders_Manager' ) ) {
				require_once YITH_WCSC_PATH . 'includes/class.yith-stripe-yith-pre-order.php';
				require_once YITH_WCSC_PATH . 'includes/gateways/class.yith-stripe-connect-source-gateway.php';

				YITH_Stripe_YITH_Pre_Order::instance();
			}
		}

		/**
		 * Add Stripe Connect gateway to list of available WCAF payment gateway
		 *
		 * @param array $available_gateways Array of available gateways.
		 *
		 * @return array Filtered array of gateways
		 */
		public function add_wcaf_stripe_connect_gateway( $available_gateways ) {

			$wcaf_wcsc = array(
				'path'     => YITH_WCSC_PATH . 'includes/class.yith-wcaf-yith-wcsc-gateway.php',
				'label'    => __( 'Stripe Connect', 'yith-stripe-connect-for-woocommerce' ),
				'class'    => 'YITH_WCAF_YITH_WCSC',
				'mass_pay' => true,
			);

			$available_gateways['yith-stripe-connect'] = $wcaf_wcsc;

			return $available_gateways;
		}

		/* === TOKEN METHODS === */

		/**
		 * Handle the card removing from stripe databases for the customer
		 *
		 * @param string              $token_id Token id.
		 * @param WC_Payment_Token_CC $token    Token object.
		 *
		 * @return bool
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 * @since  1.1.0
		 */
		public function delete_token_from_stripe( $token_id, $token ) {
			if ( $token->get_gateway_id() != self::$gateway_id ) {
				return false;
			}

			$gateway = $this->get_gateway();

			if ( ! $gateway ) {
				return false;
			}

			try {
				// Initialize SDK and set private key.
				$gateway->init_stripe_connect_api();

				$user_id     = $token->get_user_id();
				$customer_sc = YITH_Stripe_Connect_Customer()->get_customer( $user_id );

				// Delete card.
				$gateway->api_handler->delete_payment_method( $token->get_token() );

				if ( $customer_sc ) {
					// ensure the default card is the same on stripe.
					$default_token  = $customer_sc->invoice_settings->default_payment_method;
					$payment_tokens = WC_Payment_Tokens::get_customer_tokens( $user_id );

					/**
					 * @var WC_Payment_Token_CC $payment_token
					 */
					if ( $payment_tokens ) {
						foreach ( $payment_tokens as $payment_token ) {
							if ( $payment_token->get_token() === $default_token && ! $payment_token->is_default() ) {
								$payment_token->set_default( true );
								$payment_token->save();
								break;
							}
						}
					}

					YITH_Stripe_Connect_Customer()->update_usermeta_info(
						$user_id,
						array(
							'id'             => $customer_sc->id,
							'default_source' => $customer_sc->invoice_settings->default_payment_method,
						)
					);
				}

				/** DO_ACTION: yith_wcstripe_connect_deleted_card
				*
				* Adds an action when deleting a card.
				*
				* @param $token           Token.
				* @param $customer_sc     User obj.
				*/
				do_action( 'yith_wcstripe_connect_deleted_card', $token->get_token(), $customer_sc );

				return true;

			} catch ( Stripe\Exception\ApiErrorException $e ) {
				return false;
			}
		}

		/**
		 * Handle setting a token as default on Stripe
		 *
		 * @param string              $token_id Token id.
		 * @param WC_Payment_Token_CC $token    Token object.
		 *
		 * @return bool
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 * @since  1.1.0
		 */
		public function set_default_token_on_stripe( $token_id, $token = null ) {
			if ( $token->get_gateway_id() != self::$gateway_id ) {
				return false;
			}

			if ( empty( $token ) ) {
				$token = WC_Payment_Tokens::get( $token_id );
			}

			$gateway = $this->get_gateway();

			if ( ! $gateway ) {
				return false;
			}

			try {

				// Initialize SDK and set private key.
				$gateway->init_stripe_connect_api();

				$user_id     = $token->get_user_id();
				$customer_sc = YITH_Stripe_Connect_Customer()->get_usermeta_info( $user_id );

				if ( empty( $customer_sc ) ) {
					return false;
				}

				// Delete card.
				$customer = $gateway->api_handler->update_customer(
					$customer_sc['id'],
					array_merge(
						array(
							'invoice_settings' => array(
								'default_payment_method' => $token->get_token(),
							),
						),
						strpos( $token->get_token(), 'card' ) === 0 ? array(
							'default_source' => $token->get_token(),
						) : array()
					)
				);

				// Backward compatibility.
				YITH_Stripe_Connect_Customer()->update_usermeta_info(
					$user_id,
					array(
						'id'             => $customer->id,
						'default_source' => $customer->invoice_settings->default_payment_method,
					)
				);

				return true;

			} catch ( Stripe\Exception\ApiErrorException $e ) {
				return false;
			}
		}

		/* === WC EMAILS === */

		/**
		 * Register email classes for stripe
		 *
		 * @param mixed $classes Array of email class instances.
		 *
		 * @return mixed Filtered array of email class instances
		 * @since 1.0.0
		 */
		public function register_email_classes( $classes ) {
			$classes['YITH_Stripe_Connect_Renew_Needs_Action_Email'] = include_once( YITH_WCSC_PATH . 'includes/emails/class.yith-wcsc-renew-needs-action-email.php' );

			return $classes;
		}

		/**
		 * Register email action for stripe
		 *
		 * @param mixed $emails Array of registered actions.
		 *
		 * @return mixed Filtered array of registered actions
		 * @since 1.0.0
		 */
		public function register_email_actions( $emails ) {
			$emails = array_merge(
				$emails,
				array(
					'yith_stripe_connect_renew_intent_requires_action',
				)
			);

			return $emails;
		}

		/**
		 * Locate default templates of woocommerce in plugin, if exists
		 *
		 * @param string $core_file     Location of the template.
		 * @param string $template      Template to be included.
		 * @param string $template_base Subpath where to search template.
		 *
		 * @return string
		 * @since  1.0.0
		 */
		public function register_woocommerce_template( $core_file, $template, $template_base ) {
			$located = yith_wcsc_locate_template( $template );

			if ( $located && file_exists( $located ) ) {
				return $located;
			} else {
				return $core_file;
			}
		}

		/* === INSTALL METHODS === */

		/**
		 * Install plugin tables
		 *
		 * @return void
		 */
		protected function _install_tables() {
			global $wpdb;

			// adds tables name in global $wpdb.
			$wpdb->yith_wcsc_receivers   = $wpdb->prefix . 'yith_wcsc_receivers';
			$wpdb->yith_wcsc_commissions = $wpdb->prefix . 'yith_wcsc_commissions';

			// skip if current db version is equal to plugin db version.
			$current_db_version = get_option( 'yith_wcsc_db_version' );
			if ( version_compare( $current_db_version, self::YITH_WCSC_DB_VERSION, '>=' ) ) {
				return;
			}

			// assure dbDelta function is defined.
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			// retrieve table charset.
			$charset_collate = $wpdb->get_charset_collate();

			// *ID* | user_id | user_email | disabled | product_id | stripe_id | commission_value | commission_type | status_receiver | order_receiver.
			// adds wcsc_receivers table
			$sql_receivers = "CREATE TABLE $wpdb->yith_wcsc_receivers (
                    ID bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    disabled INT,
                    user_id bigint(20) NOT NULL,
                    user_email VARCHAR (120) NOT NULL,
                    all_products INT,
                    product_id bigint(20) NOT NULL,
                    stripe_id VARCHAR (120),
                    commission_value DECIMAL (10,2),
                    commission_type VARCHAR (20),
                    status_receiver VARCHAR (120),
                    order_receiver bigint(20)
                ) $charset_collate;";
			dbDelta( $sql_receivers );

			// *ID* | receiver_id | user_id | order_id | order_item_id | product_id | commission | commission_status | pay_in | purchased_date.
			$sql_commissions = "CREATE TABLE $wpdb->yith_wcsc_commissions (
                    ID bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    receiver_id bigint(20) NOT NULL,
                    user_id bigint (20) NOT NULL,
                    order_id bigint (20) NOT NULL,
                    order_item_id bigint(20) NOT NULL,
                    product_id bigint(20) NOT NULL,
                    commission DECIMAL(10,2) ,
                    commission_status VARCHAR (120) NOT NULL,
                    commission_type VARCHAR (20),
                    commission_rate DECIMAL(10,2) ,
                    payment_retarded bigint (20),
                    purchased_date DATETIME,
                    note LONGTEXT,
                    integration_item LONGTEXT
                ) $charset_collate;";
			dbDelta( $sql_commissions );

			update_option( 'yith_wcsc_db_version', self::YITH_WCSC_DB_VERSION );
		}

		/**
		 * Add plugin endpoints
		 *
		 * @return void
		 */
		protected function _install_main_features() {

			/** APPLY_FILTERS: yith_wcsc_installed_plugin
			*
			* Filter the items per page in the receivers table.
			*
			* @param 'yith_wcsc_installed' option.
			*/
			$installed_plugin = apply_filters( 'yith_wcsc_installed_plugin', get_option( 'yith_wcsc_installed' ) );

			// We add our endpoint each time that plugin is loaded.
			add_rewrite_endpoint( 'stripe-connect', EP_PAGES );

			if ( 'yes' != $installed_plugin ) {
				// Flush Rewrite Rules must run once time when plugin is installed.
				flush_rewrite_rules();
				update_option( 'yith_wcsc_installed', 'yes' );
			}
		}

		/* === LICENCE HANDLING METHODS === */

		/**
		 * Register plugins for activation tab
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function register_plugin_for_activation() {
			if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
				require_once YITH_WCSC_PATH . 'plugin-fw/licence/lib/yit-licence.php';
				require_once YITH_WCSC_PATH . 'plugin-fw/licence/lib/yit-plugin-licence.php';
			}

			YIT_Plugin_Licence()->register( YITH_WCSC_INIT, YITH_WCSC_SECRET_KEY, YITH_WCSC_SLUG );
		}

		/**
		 * Register plugins for update tab
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function register_plugin_for_updates() {
			if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
				require_once( YITH_WCSC_PATH . 'plugin-fw/lib/yit-upgrade.php' );
			}

			YIT_Upgrade()->register( YITH_WCSC_SLUG, YITH_WCSC_INIT );
		}
	}
}

