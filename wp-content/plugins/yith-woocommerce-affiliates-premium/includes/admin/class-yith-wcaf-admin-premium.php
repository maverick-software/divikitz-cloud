<?php
/**
 * Admin class premium
 *
 * @author  YITH
 * @package YITH\Affiliates\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCAF' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCAF_Admin_Premium' ) ) {
	/**
	 * WooCommerce Affiliates Admin Premium
	 *
	 * @since 1.0.0
	 */
	class YITH_WCAF_Admin_Premium extends YITH_WCAF_Admin {

		/**
		 * Constructor method
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_filter( 'yith_wcaf_available_admin_tabs', array( $this, 'filter_admin_tabs' ) );
			add_filter( 'yith_wcaf_affiliates_settings', array( $this, 'filter_settings' ) );

			parent::__construct();
		}

		/**
		 * Startup admin panel
		 *
		 * @return void
		 */
		public function init() {
			// do startup operations.
			YITH_WCAF_Admin_Profile_Premium::init();
			YITH_WCAF_Admin_Meta_Boxes_Premium::init();
			YITH_WCAF_Admin_Coupons::init();
			YITH_WCAF_Admin_Orders::init();

			// load current tab.
			$this->load_tab();
		}

		/* === PLUGIN PANEL METHODS === */

		/**
		 * Returns name of the class that mangas passed admin tab
		 *
		 * @param string|bool $tab Tab to print; false to use current tab.
		 * @return string|bool Class name; false when provided tab is invalid.
		 */
		public function get_tab_class_name( $tab = false ) {
			if ( ! $tab ) {
				$tab = $this->get_current_tab();
			}

			if ( ! $tab ) {
				return false;
			}

			$class_name = 'YITH_WCAF_' . ucfirst( $tab ) . '_Admin_Panel_Premium';

			if ( class_exists( $class_name ) ) {
				return $class_name;
			}

			return parent::get_tab_class_name( $tab );
		}

		/**
		 * Filters tabs for admin section
		 *
		 * @param mixed $tabs Array of available tabs.
		 *
		 * @return mixed Filtered array of tabs
		 * @since 1.0.0
		 */
		public function filter_admin_tabs( $tabs ) {
			// add dashboard tab.
			$tabs = yith_wcaf_append_items(
				$tabs,
				'affiliates',
				array(
					'dashboard' => _x( 'Dashboard', '[ADMIN] Panel tabs', 'yith-woocommerce-affiliates' ),
				),
				'before'
			);

			// remove premium tab.
			unset( $tabs['premium'] );

			return $tabs;
		}

		/**
		 * Filers plugin options to add premium-specific data
		 *
		 * @param array $options Array of options.
		 * @return array Filtered array of options.
		 */
		public function filter_settings( $options ) {
			$current_action = current_action();

			if ( 'yith_wcaf_affiliates_settings' === $current_action ) {
				$options['affiliates'] = array(
					'affiliates_options' => array(
						'type'     => 'multi_tab',
						'sub-tabs' => array_merge(
							array(
								'affiliates-list'  => array(
									'title' => _x( 'Affiliates List', '[ADMIN] Affiliate tab title', 'yith-woocommerce-affiliates' ),
								),
								'affiliates-rates' => array(
									'title' => _x( 'Rates', '[ADMIN] Affiliate tab title', 'yith-woocommerce-affiliates' ),
								),
							),
							YITH_WCAF_Clicks()->are_hits_registered() ? array(
								'affiliates-clicks' => array(
									'title' => _x( 'Visits', '[ADMIN] Affiliate tab title', 'yith-woocommerce-affiliates' ),
								),
							) : array()
						),
					),
				);
			}

			return $options;
		}

		/* === PLUGIN LINK METHODS === */

		/**
		 * Adds plugin row meta
		 *
		 * @param array  $new_row_meta_args Array of data to filter.
		 * @param array  $plugin_meta       Array of plugin meta.
		 * @param string $plugin_file       Path to init file.
		 * @param array  $plugin_data       Array of plugin data.
		 * @param string $status            Not used.
		 * @param string $init_file         Constant containing plugin int path.
		 *
		 * @return array Filtered array of plugin meta
		 * @since 1.0.0
		 */
		public function add_plugin_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YITH_WCAF_PREMIUM_INIT' ) {
			$new_row_meta_args = parent::add_plugin_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file );

			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['is_premium'] = true;
			}

			return $new_row_meta_args;
		}
	}
}
