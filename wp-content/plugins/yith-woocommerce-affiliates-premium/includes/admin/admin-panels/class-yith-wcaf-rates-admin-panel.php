<?php
/**
 * Rule Rates admin panel handling
 *
 * @author  YITH
 * @package YITH\Affiliates\Classes
 * @version 2.0.0
 */

if ( ! defined( 'YITH_WCAF' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCAF_Rates_Admin_Panel' ) ) {
	/**
	 * Affiliates admin panel handling
	 *
	 * @since 1.0.0
	 */
	class YITH_WCAF_Rates_Admin_Panel extends YITH_WCAF_Abstract_Admin_Panel {

		/**
		 * Current tab name
		 *
		 * @var string
		 */
		protected $tab = 'rates';

		/**
		 * Url to doc page that describes rate rules working and priority system
		 *
		 * @var string
		 */
		public $rules_doc = 'https://docs.yithemes.com/yith-woocommerce-affiliates/premium-version-settings/affiliates-menu/rates/';

		/**
		 * Init Rate Rules admin panel
		 */
		public function __construct() {
			// init screen options.
			$this->screen_options = array(
				'per_page' => array(
					'label'    => _x( 'Rates', '[ADMIN] Rates pagination label', 'yith-woocommerce-affiliates' ),
					'default'  => 20,
					'option'   => 'edit_rates_per_page',
					'sanitize' => 'intval',
				),
			);

			// enqueue tab assets.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

			// add rule modal.
			add_action( 'yit_framework_after_print_wc_panel_content', array( $this, 'render_add_modal' ) );

			// call parent constructor.
			parent::__construct();
		}

		/**
		 * Get current tab url
		 *
		 * @param array $args Arguments to add to the url.
		 * @return string Current tab url.
		 */
		public function get_tab_url( $args = array() ) {
			/**
			 * APPLY_FILTERS: yith_wcaf_admin_tab_url
			 *
			 * Filters the url for the current tab in the backend.
			 *
			 * @param string $url Tab url.
			 */
			return apply_filters( 'yith_wcaf_admin_tab_url', add_query_arg( $args, YITH_WCAF_Admin()->get_tab_url( 'affiliates', 'affiliates-rates', $args ) ) );
		}

		/* === PANEL HANDLING METHODS === */

		/**
		 * Returns variable to localize for current panel
		 *
		 * @return array Array of variables to localize.
		 */
		public function get_localize() {
			$change_rate_rule_status_nonce = wp_create_nonce( 'change_rate_rule_status' );

			return array(
				'labels' => array(
					'add_rate_rule_title'     => _x( 'Add a rate rule', '[ADMIN] Add rate rule modal', 'yith-woocommerce-affiliates' ),
					'edit_rate_rule_title'    => _x( 'Edit rate rule', '[ADMIN] Add rate rule modal', 'yith-woocommerce-affiliates' ),
					'generic_save_button'     => _x( 'Save', '[ADMIN] Add rate rule modal', 'yith-woocommerce-affiliates' ),
					'generic_confirm_title'   => _x( 'Confirm', '[ADMIN] Confirm modal', 'yith-woocommerce-affiliates' ),
					'generic_confirm_message' => _x( 'This operation cannot be undone. Are you sure you want to proceed?', '[ADMIN] Confirm modal', 'yith-woocommerce-affiliates' ),
				),
				'nonces' => array(
					'enable_rate_rule'  => $change_rate_rule_status_nonce,
					'disable_rate_rule' => $change_rate_rule_status_nonce,
					'save_rate_rule'    => wp_create_nonce( 'save_rate_rule' ),
					'delete_rate_rule'  => wp_create_nonce( 'delete_rate_rule' ),
					'clone_rate_rule'   => wp_create_nonce( 'clone_rate_rule' ),
					'sort_rate_rules'   => wp_create_nonce( 'sort_rate_rules' ),
				),
			);
		}

		/**
		 * Render template for "Add rule" modal
		 *
		 * @return void.
		 */
		public function render_add_modal() {
			$product_categories = get_terms(
				array(
					'taxonomy'   => 'product_cat',
					'limit'      => 0,
					'hide_empty' => false,
					'fields'     => 'id=>name',
				)
			);

			include YITH_WCAF_DIR . 'views/rates/add-modal.php';
		}
	}
}
