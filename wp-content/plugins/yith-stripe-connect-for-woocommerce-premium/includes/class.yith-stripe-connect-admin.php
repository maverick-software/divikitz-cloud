<?php
/**
 * Admin Class
 *
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @class      YITH_Stripe_Connect_Admin
 * @package    YITH Stripe Connect for WooCommerce
 * @since      1.0.0
 * @author     YITH
 */

if ( ! defined( 'YITH_WCSC_PATH' ) ) {
	exit( 'Direct access forbidden.' );
}

if ( ! class_exists( 'YITH_Stripe_Connect_Admin' ) ) {
	/**
	 * Class YITH_Stripe_Connect_Admin
	 *
	 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
	 */
	class YITH_Stripe_Connect_Admin {
		/**
		 * Panel page slug
		 *
		 * @var string
		 */
		protected $_panel_page = 'yith_wcsc_panel';

		/**
		 * Instance of the panel object
		 *
		 * @var YIT_Plugin_Panel_WooCommerce
		 */
		protected $_panel = null;

		/**
		 * Documentation url
		 *
		 * @var string
		 */
		protected $doc_url = 'https://docs.yithemes.com/yith-stripe-connect-for-woocommerce/';

		/**
		 * Official plugin documentation
		 *
		 * @var string
		 */
		protected $_official_documentation = 'https://docs.yithemes.com/yith-stripe-connect-for-woocommerce/';

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
		 * Stripe Connect Gateway Instance
		 *
		 * @var YITH_Stripe_Connect_Gateway
		 * @since  1.0.0
		 * @access protected
		 */
		protected $_stripe_connect_gateway = null;

		/**
		 * Construct
		 *
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 * @since  1.0.0
		 */
		public function __construct() {
			$this->_stripe_connect_receivers   = YITH_Stripe_Connect_Receivers::instance();
			$this->_stripe_connect_commissions = YITH_Stripe_Connect_Commissions::instance();
			$this->_stripe_connect_gateway     = YITH_Stripe_Connect()->get_gateway( false );

			// Set admin Ajax calls.
			add_action( 'wp_ajax_print_receiver_row_action', array( $this, 'print_receiver_row_action' ) );
			add_action( 'wp_ajax_save_receivers_action', array( $this, 'save_receivers_action' ) );
			add_action( 'wp_ajax_redirect_uri_done', array( $this, 'save_redirect_uri_done' ) );
			add_action( 'wp_ajax_webhook_done', array( $this, 'save_webhook_done' ) );

			// Action links and meta.
			add_filter( 'plugin_action_links_' . plugin_basename( YITH_WCSC_PATH . 'init.php' ), array( $this, 'action_links' ) );
			add_filter( 'yith_show_plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 5 );

			// Register panel.
			add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );
			add_action( 'yith_wcsc_receiver_panel', array( $this, 'get_receiver_panel' ) );
			add_action( 'yith_wcsc_commissions_panel', array( $this, 'get_commissions_panel' ) );
			add_action( 'yith_wcsc_premium', array( $this, 'premium_tab' ) );

			// Print YITH Settings.
			add_action( 'yith_wcsc_gateway_advanced_settings_tab', array( $this, 'print_panel' ) );

			// Meta Box.
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10 );

			// Enqueue Scripts.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// Checks for required fields, an print appropriate messages.
			add_action( 'admin_notices', array( $this, 'print_missing_key_messages' ) );
			add_action( 'admin_notices', array( $this, 'print_wc_stripe_connect_uri_webhook_message' ) );
		}

		/**
		 * Add action links for current plugin
		 *
		 * @param mixed $links Array of links available for the plugin.
		 *
		 * @return array
		 */
		public function action_links( $links ) {
			$links = yith_add_action_links( $links, 'yith_wcsc_panel', true, YITH_WCSC_SLUG );

			return $links;
		}

		/**
		 * Adds plugin action links to plugin row, in plugins.php page
		 *
		 * @param array  $new_row_meta_args Array of existing plugin meta.
		 * @param array  $plugin_meta       Array of existing plugin meta.
		 * @param string $plugin_file       Plugin init file.
		 * @param array  $plugin_data       Plugin data.
		 * @param string $status            Plugin status.
		 * @param string $init_file         Constant name where to find plugin init path.
		 *
		 * @return   array
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use      plugin_row_meta
		 */
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YITH_WCSC_INIT' ) {
			if ( defined( $init_file ) && constant( $init_file ) == $plugin_file ) {
				$new_row_meta_args['slug']       = YITH_WCSC_SLUG;
				$new_row_meta_args['is_premium'] = true;
			}

			return $new_row_meta_args;
		}

		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @return   void
		 * @since    1.0.0
		 * @author   Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 * @use      /Yit_Plugin_Panel class
		 * @see      plugin-fw/lib/yit-plugin-panel.php
		 */
		public function register_panel() {

			if ( ! empty( $this->_panel ) ) {
				return;
			}

			/** APPLY_FILTERS: yith_wcsc_admin_tabs
			*
			* Filter the default plugin tabs.
			*
			* @param array Default plugin tabs.
			*/
			$admin_tabs = apply_filters(
				'yith_wcsc_admin_tabs',
				array(
					'settings'    => _x( 'Settings', 'tab name', 'yith-stripe-connect-for-woocommerce' ),
					'multibanco'  => _x( 'Multibanco', 'tab name', 'yith-stripe-connect-for-woocommerce' ),
					'receiver'    => _x( 'Receivers', 'tab name', 'yith-stripe-connect-for-woocommerce' ),
					'commissions' => _x( 'Commissions', 'tab name', 'yith-stripe-connect-for-woocommerce' ),
				)
			);

			$args = array(
				'create_menu_page' => true,
				'parent_slug'      => '',
				'page_title'       => __( 'Stripe Connect', 'yith-stripe-connect-for-woocommerce' ),
				'menu_title'       => __( 'Stripe Connect', 'yith-stripe-connect-for-woocommerce' ),
				'capability'       => 'manage_options',
				'parent'           => '',
				'parent_page'      => 'yith_plugin_panel',
				'page'             => $this->_panel_page,
				'admin-tabs'       => $admin_tabs,
				'options-path'     => YITH_WCSC_OPTIONS_PATH,
			);

			// Fixed: not updated theme.
			if ( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
				require_once( YITH_WACP_DIR . '/plugin-fw/lib/yit-plugin-panel-wc.php' );
			}

			// Fixed: not updated theme/old plugin framework.
			if ( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
				require_once( 'plugin-fw/lib/yit-plugin-panel-wc.php' );
			}

			$this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );
		}

		/**
		 * Prints receivers panel
		 *
		 * @return void
		 */
		public function get_receiver_panel() {
			global $pagenow;

			$this->_stripe_connect_receivers->enqueue_scripts();

			$receivers_args = array();
			$context        = '';
			$is_new_product = false;

			if ( isset( $_GET['post'] ) && ( 'post.php' === $pagenow && 'product' === get_post_type( intval( $_GET['post'] ) ) || isset( $_GET['post_type'] ) && 'post-new.php' === $pagenow && 'product' === $_GET['post_type'] ) ) {
				$context                        = 'product_edit_page';
				$product_id                     = array( intval( $_GET['post'] ) );
				$receivers_args['product_id']   = $product_id;
				$receivers_args['all_products'] = true;
			}

			if ( isset( $_GET['post_type'] ) && 'post-new.php' === $pagenow && 'product' === $_GET['post_type'] ) {
				$is_new_product = true;
			}

			/** APPLY_FILTERS: yith_wcsc_get_receiver_panel_args
			*
			* Filter the default plugin args in the receiver panel.
			*
			* @param array Default plugin panel args.
			*/
			$args = apply_filters(
				'yith_wcsc_get_receiver_panel_args',
				array(
					'context'         => $context,
					'count_receivers' => $this->_stripe_connect_receivers->get_receivers_count( $receivers_args ),
					'current_page'    => isset( $_GET['current_page'] ) ? abs( (int) $_GET['current_page'] ) : 1,
					'items_per_page'  => $this->_stripe_connect_receivers->items_per_page,
					'receivers'       => $this->_stripe_connect_receivers->get_receivers( $receivers_args, true ),
					'is_new_product'  => $is_new_product,
				),
				$receivers_args
			);

			yith_wcsc_get_template( 'receivers-panel', $args, 'admin' );
		}

		/**
		 * Prints commissions panel
		 *
		 * @return void
		 */
		public function get_commissions_panel() {
			$this->_stripe_connect_commissions->enqueue_scripts();

			$commissions_args = array(
				'product_id' => isset( $_GET['yith_wcs_product'] ) ? intval( $_GET['yith_wcs_product'] ) : '',
				'user_id'    => isset( $_GET['yith_wcs_user'] ) ? intval( $_GET['yith_wcs_user'] ) : '',
				'date_from'  => isset( $_GET['yith_wcsc_date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['yith_wcsc_date_from'] ) ) : '',
				'date_to'    => isset( $_GET['yith_wcsc_date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['yith_wcsc_date_to'] ) ) : '',
				'day'        => isset( $_GET['yith_wcsc_day'] ) ? sanitize_text_field( wp_unslash( $_GET['yith_wcsc_day'] ) ) : '',
				'month_year' => isset( $_GET['yith_wcsc_month_year'] ) ? sanitize_text_field( wp_unslash( $_GET['yith_wcsc_month_year'] ) ) : '',
				'orderby'    => isset( $_GET['yith_wcsc_orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['yith_wcsc_orderby'] ) ) : 'ID',
				'order'      => isset( $_GET['yith_wcsc_order'] ) ? sanitize_text_field( wp_unslash( $_GET['yith_wcsc_order'] ) ) : 'DESC',
			);

			$args = array(
				'count_commissions' => $this->_stripe_connect_commissions->get_commissions_count( $commissions_args ),
				'current_page'      => isset( $_GET['current_page'] ) ? abs( (int) $_GET['current_page'] ) : 1,
				'items_per_page'    => $this->_stripe_connect_commissions->items_per_page,
				'commissions'       => $this->_stripe_connect_commissions->get_commissions( $commissions_args, true ),
			);

			yith_wcsc_get_template( 'commissions-panel', $args, 'common' );
		}

		/**
		 * Print custom tab of settings for Stripe Connect sub panel
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function print_panel() {
			$panel_template = YITH_WCSC_PATH . 'templates/admin/settings-tab.php';

			if ( ! file_exists( $panel_template ) ) {
				return;
			}

			global $current_section;

			$current_section = YITH_Stripe_Connect::$gateway_id;
			$current_tab     = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';

			switch ( $current_tab ) {
				case 'multibanco':
					$current_section = $current_section . '-multibanco';
					break;
				// space for other integrations.
			}

			WC_Admin_Settings::get_settings_pages();

			if ( ! empty( $_POST ) ) { // phpcs:ignore WordPress.Security
				$gateways = WC()->payment_gateways()->payment_gateways();
				$gateways[ $current_section ]->process_admin_options();
			}

			include_once( $panel_template );
		}

		/**
		 * Add our custom metaboxes that allows us add our custom receivers on product page
		 *
		 * @param string $post_type Current post's type.
		 * @return void
		 */
		public function add_meta_boxes( $post_type ) {

			/** APPLY_FILTERS: yith_wcsc_meta_box_available_roles
			*
			* Filter the default roles to modify the metabox.
			*
			* @param string Default plugin role.
			*/
			if ( 'product' == $post_type && apply_filters( 'yith_wcsc_meta_box_available_roles', current_user_can( 'administrator' ) ) ) { // Only administrator can see the metabox on product edit page.
				$title = __( 'Stripe Connect Receivers', 'yith-stripe-connect-for-woocommerce' ); // @since 1.0.0
				add_meta_box(
					'stripe-connect-receiver',
					$title,
					array(
						$this,
						'get_receiver_panel',
					),
					$post_type,
					'normal',
					'default'
				);
			}
		}

		/**
		 * Enqueue Scripts
		 *
		 * Register and enqueue scripts for Admin
		 *
		 * @return void
		 * @since      1.0
		 * @author     Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 */
		public function enqueue_scripts() {
			$debug_enabled = defined( 'WP_DEBUG' ) ? WP_DEBUG : false;
			$prefix        = ! $debug_enabled ? '.min' : '';

			$data_to_js = array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			);

			$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
			$section      = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : '';

			wp_register_script( 'yith-wcsc-admin', YITH_WCSC_ASSETS_URL . 'js/script-yith-sc-admin' . $prefix . '.js', array( 'jquery', 'select2' ), YITH_WCSC_VERSION, true );
			wp_localize_script( 'yith-wcsc-admin', 'yith_wcsc_admin', $data_to_js );

			if ( 'yith_wcsc_panel' == $current_page || 'yith-stripe-connect' == $section ) {
				wp_enqueue_script( 'yith-wcsc-admin' );
			}
		}

		/**
		 * Print new receiver row, for ajax requests only
		 *
		 * @return void
		 */
		public function print_receiver_row_action() {
			if ( isset( $_REQUEST['index'] ) ) {
				$context    = '';
				$product_id = '';

				if ( isset( $_REQUEST['context'] ) && isset( $_REQUEST['product_id'] ) ) {
					$context    = sanitize_text_field( wp_unslash( $_REQUEST['context'] ) );
					$product_id = intval( $_REQUEST['product_id'] );

					$receivers_args['product_id'] = $product_id;
				}

				$args = array(
					'context'      => $context,
					'index'        => intval( $_REQUEST['index'] ),
					'receiver_row' => array(
						'product_id' => $product_id,
					),
				);

				yith_wcsc_get_template( 'receiver-row', $args, 'admin' );
			}
			die();
		}

		/**
		 * Save new receivers, and remove delete one, for ajax requets only
		 *
		 * @return void
		 */
		public function save_receivers_action() {
			$receivers_to_save   = array_map( 'intval', explode( ',', $_POST['_receivers_to_save'] ) ); // phpcs:ignore WordPress.Security
			$receivers_to_remove = array_map( 'intval', explode( ',', $_POST['_receivers_to_remove'] ) ); // phpcs:ignore WordPress.Security

			$created = array();
			foreach ( $receivers_to_save as $receiver_to_save ) {
				$receiver = isset( $_POST['_receivers'][ $receiver_to_save ]['ID'] ) ? $_POST['_receivers'][ $receiver_to_save ] : array(); // phpcs:ignore WordPress.Security

				if ( empty( $receiver ) || ! isset( $receiver['user_id'] ) ) {
					continue;
				}

				$stripe_user_id              = get_user_meta( $receiver['user_id'], 'stripe_user_id', true );
				$receiver['status_receiver'] = $stripe_user_id ? 'connect' : 'disconnect';
				$receiver['stripe_id']       = $stripe_user_id;

				if ( 'new' != $receiver['ID'] ) {
					$this->_stripe_connect_receivers->update( $receiver['ID'], $receiver );
				} else {
					$inserted  = $this->_stripe_connect_receivers->insert( $receiver );
					$created[] = array(
						'index'           => $receiver_to_save,
						'id'              => $inserted,
						'stripe_id'       => $stripe_user_id,
						'status_receiver' => $receiver['status_receiver'],
					);
				}
			}

			foreach ( $receivers_to_remove as $receiver_to_remove ) {
				$this->_stripe_connect_receivers->delete( $receiver_to_remove );
			}

			wp_send_json( $created );
			die();
		}

		/**
		 * Saves customer preference, when Redirect Uri notice is dismissed
		 *
		 * @return void
		 */
		public function save_redirect_uri_done() {
			$value = update_option( 'yith_wcsc_redirected_uri', 'yes' );
			wp_send_json_success( $value );
		}

		/**
		 * Saves customer preference, when Webhook notice is dismissed
		 *
		 * @return void
		 */
		public function save_webhook_done() {
			$value = update_option( 'yith_wcsc_webhook_defined', 'yes' );
			wp_send_json_success( $value );
		}

		/**
		 * Prints notices, when gateway is enabled but not properly configured
		 *
		 * @return void
		 */
		public function print_missing_key_messages() {
			if ( ! $this->_stripe_connect_gateway ) {
				return;
			}

			$mode = $this->_stripe_connect_gateway->test_live ? __( 'Test', 'yith-stripe-connect-for-woocommerce' ) : __( 'Live', 'yith-stripe-connect-for-woocommerce' );
			$options = $this->_stripe_connect_gateway->test_live ? array(
				'api-dev-client-id' => __( 'Test Client ID', 'yith-stripe-connect-for-woocommerce' ),
				'api-public-test-key' => __( 'Test Public Key', 'yith-stripe-connect-for-woocommerce' ),
				'api-secret-test-key' => __( 'Test Secret Key', 'yith-stripe-connect-for-woocommerce' ),
			) : array(
				'api-prod-client-id' => __( 'Live Client ID', 'yith-stripe-connect-for-woocommerce' ),
				'api-public-live-key' => __( 'Live Public Key', 'yith-stripe-connect-for-woocommerce' ),
				'api-secret-live-key' => __( 'Live Secret Key', 'yith-stripe-connect-for-woocommerce' ),
			);

			foreach ( $options as $option => $option_label ) {
				$option_value = $this->_stripe_connect_gateway->get_option( $option );

				if ( empty( $option_value ) ) :
					?>
					<div class="notice notice-warning is-dismissible">
						<p>
							<?php
							// translators: 1. Stripe Connect for WooCommerce. 2. Mode (Live/Test). 3. Missing field.
							echo wp_kses_post( sprintf( __( '<b>%1$s -</b> you have enable %2$s mode. This field is required: %3$s', 'yith-stripe-connect-for-woocommerce' ), 'YITH Stripe Connect for WooCommerce', $mode, $option_label ) );
							?>
						</p>
					</div>
					<?php
				endif;
			}
		}

		/**
		 * Prints notices to remind customer basic configuration
		 *
		 * @return void
		 */
		public function print_wc_stripe_connect_uri_webhook_message() {
			$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
			$section      = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : '';

			if ( 'yith_wcsc_panel' == $current_page || 'yith-stripe-connect' == $section ) {

				if ( 'yes' != get_option( 'yith_wcsc_webhook_defined' ) ) {
					?>
					<div class="notice notice-warning yith_wcsc_message yith_wcsc_message_webhook">
						<p>
							<?php
							// translators: 1. YITH Stripe Connect for WooCommerce. 2. Webhook uri 3. Url to Stripe Dashboard.
							echo wp_kses_post( sprintf( __( '<b>%1$s -</b> Define the following <b>Webhook</b> <code>%2$s</code> in <a href="%3$s" target="_blank">Stripe Dashboard > Developers > Webhooks</a> (Endpoints receiving events from Connect applications) section.', 'yith-stripe-connect-for-woocommerce' ), 'YITH Stripe Connect for WooCommerce', esc_url( site_url( '/wc-api/sc_webhook_event' ) ), 'https://dashboard.stripe.com/account/webhooks' ) );
							?>
						</p>
						<p>
							<a class="button-primary"> <?php esc_html_e( 'Done', 'yith-stripe-connect-for-woocommerce' ); ?> </a>
						</p>
					</div>
					<?php
				}
				if ( 'yes' != get_option( 'yith_wcsc_redirected_uri' ) ) {
					?>
					<div class="notice notice-warning yith_wcsc_message yith_wcsc_message_redirect_uri">
						<p>
							<?php
							// translators: 1. YITH Stripe Connect for WooCommerce. 2. Redirect uri 3. Url to Stripe Dashboard.
							echo wp_kses_post( sprintf( __( '<b>%1$s -</b> Define the following <b>Redirect URI</b> <code>%2$s</code> in <a href="%3$s" target="_blank">Stripe Dashboard > Settings > Connect > Integration ></a> <b>Redirects</b> section.', 'yith-stripe-connect-for-woocommerce' ), 'YITH Stripe Connect for WooCommerce', esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ) . 'stripe-connect', 'https://dashboard.stripe.com/account/applications/settings' ) );
							?>
						</p>
						<p>
							<a class="button-primary"> <?php esc_html_e( 'Done', 'yith-stripe-connect-for-woocommerce' ); ?> </a>
						</p>
					</div>
					<?php
				}
			}
		}
	}

}
