<?php
/**
 * Plugin Name: YITH Stripe Connect for WooCommerce
 * Plugin URI: https://yithemes.com/themes/plugins/yith-woocommerce-stripe-connect/
 * Description: <code><strong>YITH Stripe Connect for WooCommerce</strong></code> allows you to manage a complete commission system directly from your site. Accept payments with Stripe's reliability and send immediately part of your incomes to your suppliers or middlemen. <a href="https://yithemes.com/" target="_blank">Get more plugins for your e-commerce on <strong>YITH</strong></a>
 * Author: YITH
 * Text Domain: yith-stripe-connect-for-woocommerce
 * Version: 2.23.0
 * Author URI: https://yithemes.com/
 * WC requires at least: 7.6
 * WC tested up to: 7.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$wp_upload_dir = wp_upload_dir();

/* === DEFINE === */
! defined( 'YITH_WCSC_VERSION' ) && define( 'YITH_WCSC_VERSION', '2.23.0' );
! defined( 'YITH_WCSC_API_VERSION' ) && define( 'YITH_WCSC_API_VERSION', '2022-11-15' );
! defined( 'YITH_WCSC_DB_VERSION' ) && define( 'YITH_WCSC_DB_VERSION', '1.0.1' );
! defined( 'YITH_WCSC_INIT' ) && define( 'YITH_WCSC_INIT', plugin_basename( __FILE__ ) );
! defined( 'YITH_WCSC_SLUG' ) && define( 'YITH_WCSC_SLUG', 'yith-stripe-connect-for-woocommerce' );
! defined( 'YITH_WCSC_SECRET_KEY' ) && define( 'YITH_WCSC_SECRET_KEY', 'pZmz9Or1s7fsecXBTQdt' );
! defined( 'YITH_WCSC_FILE' ) && define( 'YITH_WCSC_FILE', __FILE__ );
! defined( 'YITH_WCSC_PATH' ) && define( 'YITH_WCSC_PATH', plugin_dir_path( __FILE__ ) );
! defined( 'YITH_WCSC_TEMPLATE_PATH' ) && define( 'YITH_WCSC_TEMPLATE_PATH', YITH_WCSC_PATH . 'templates/' );
! defined( 'YITH_WCSC_OPTIONS_PATH' ) && define( 'YITH_WCSC_OPTIONS_PATH', YITH_WCSC_PATH . 'plugin-options/' );
! defined( 'YITH_WCSC_VENDOR_PATH' ) && define( 'YITH_WCSC_VENDOR_PATH', YITH_WCSC_PATH . 'vendor/' );
! defined( 'YITH_WCSC_URL' ) && define( 'YITH_WCSC_URL', plugins_url( '/', __FILE__ ) );
! defined( 'YITH_WCSC_ASSETS_URL' ) && define( 'YITH_WCSC_ASSETS_URL', YITH_WCSC_URL . 'assets/' );
! defined( 'YITH_WCSC_PREMIUM' ) && define( 'YITH_WCSC_PREMIUM', '1' );

/* Plugin Framework Version Check */
if ( ! function_exists( 'yit_maybe_plugin_fw_loader' ) && file_exists( YITH_WCSC_PATH . 'plugin-fw/init.php' ) ) {
	require_once( YITH_WCSC_PATH . 'plugin-fw/init.php' );
}
yit_maybe_plugin_fw_loader( YITH_WCSC_PATH );

if ( ! function_exists( 'yith_plugin_onboarding_registration_hook' ) ) {
	include_once 'plugin-upgrade/functions-yith-licence.php';
}
register_activation_hook( __FILE__, 'yith_plugin_onboarding_registration_hook' );

/* Load WCSC text domain */
load_plugin_textdomain( 'yith-stripe-connect-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

if ( ! function_exists( 'YITH_Stripe_Connect' ) ) {
	/**
	 * Unique access to instance of YITH_Vendors class
	 *
	 * @return YITH_Stripe_Connect
	 * @since 1.0.0
	 */
	function YITH_Stripe_Connect() {
		// Load required classes and functions.

		require_once( YITH_WCSC_PATH . 'includes/class.yith-stripe-connect.php' );

		if ( ! class_exists( 'mPDF' ) ) {
			require_once( YITH_WCSC_VENDOR_PATH . 'autoload.php' );
		}

		return YITH_Stripe_Connect::instance();
	}
}

if ( ! function_exists( 'yith_wcsc_install' ) ) {
	/**
	 * Check if WooCommerce is activated
	 *
	 * @author Carlos Rodriguez <carlos.rodriguez@yourinspiration.it>
	 * @since 2.0.3
	 * @return void
	 * @use admin_notices hooks
	 */
	function yith_wcsc_install() {

		if ( ! function_exists( 'WC' ) ) {
			add_action( 'admin_notices', 'yith_wcsc_install_woocommerce_admin_notice' );
		} else {
			YITH_Stripe_Connect();
		}
	}

	add_action( 'plugins_loaded', 'yith_wcsc_install', 11 );
}

if ( ! function_exists( 'yith_wcsc_install_woocommerce_admin_notice' ) ) {
	/**
	 * Prints notice when plugin is activated without WooCommerce
	 *
	 * @return void
	 * @since 1.0.0
	 */
	function yith_wcsc_install_woocommerce_admin_notice() {
		?>
		<div class="error">
			<p>
				<?php
				// translators: 1. Plugin name.
				echo esc_html( sprintf( __( '%s is enabled but not effective. It requires WooCommerce in order to work.', 'yith-stripe-connect-for-woocommerce' ), 'YITH Stripe Connect for WooCommerce' ) );
				?>
			</p>
		</div>
		<?php
	}
}