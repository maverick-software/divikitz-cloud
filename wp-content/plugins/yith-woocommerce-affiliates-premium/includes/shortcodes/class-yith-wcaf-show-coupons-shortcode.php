<?php
/**
 * Affiliate Dashboard shortcode - Clicks
 *
 * @author  YITH
 * @package YITH/Affiliates/Classes
 * @version 2.0.0
 */

if ( ! defined( 'YITH_WCAF' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCAF_Show_Coupons_Shortcode' ) ) {
	/**
	 * Offer methods for basic shortcode handling
	 *
	 * @since 2.0.0
	 */
	class YITH_WCAF_Show_Coupons_Shortcode extends YITH_WCAF_Show_Summary_Shortcode {

		/* === INIT === */

		/**
		 * Performs any required init operation
		 */
		public function init() {
			// configure shortcode basics.
			$this->tag         = 'yith_wcaf_show_coupons';
			$this->title       = _x( 'YITH Show Coupons', '[BUILDERS] Shortcode name', 'yith-woocommerce-affiliates' );
			$this->section     = 'coupons';
			$this->template    = "dashboard-{$this->section}.php";
			$this->description = _x( 'Show affiliate coupons to your affiliates', '[BUILDERS] Shortcode description', 'yith-woocommerce-affiliates' );
			$this->supports    = array();
		}

		/* === SECTION HANDLING === */

		/**
		 * Filters variable submitted to template, in order to add section-specific values
		 *
		 * @param array $atts General shortcode attributes, as entered for the shortcode, or as default values.
		 *
		 * @return array Array of filtered template variables.
		 */
		public function get_template_atts( $atts ) {
			$affiliate  = YITH_WCAF_Affiliate_Factory::get_current_affiliate();
			$query_args = array();

			// sets pagination.
			if ( yith_plugin_fw_is_true( $atts['pagination'] ) ) {
				$query_args = array_merge(
					$query_args,
					$this->get_pagination_atts( $atts )
				);
			}

			$coupons = $affiliate->get_coupons( $query_args );
			$count   = $coupons->get_total_items();

			/**
			 * APPLY_FILTERS: $tag_shortcode_template_atts
			 *
			 * Filters the array with the attritubes needed for the shortcode template.
			 * <code>$tag</code> will be replaced with the shortcode tag.
			 *
			 * @param array $shortcode_atts Attributes for the shortcode template.
			 */
			return apply_filters(
				"{$this->tag}_shortcode_template_atts",
				array_merge(
					$atts,
					compact( 'affiliate', 'coupons', 'count' ),
					// backward compatibility: these attributes are submitted only to support old versions of the templates.
					array(
						'user_id'                => $affiliate->get_user_id(),
						'user'                   => $affiliate->get_user(),
						'affiliate_id'           => $affiliate->get_id(),
						'dashboard_coupons_link' => YITH_WCAF_Dashboard()->get_dashboard_url( 'coupons', 1 ),
						/**
						 * APPLY_FILTERS: yith_wcaf_show_dashboard_links
						 *
						 * Filters whether to show the dashboard links in the Affiliate Dashboard.
						 *
						 * @param bool   $show_dashboard_links Whether to show the dashboard links or not.
						 * @param string $section              Affiliate dashboard section.
						 */
						'show_right_column'      => apply_filters( 'yith_wcaf_show_dashboard_links', yith_plugin_fw_is_true( $atts['show_dashboard_links'] ), 'dashboard_coupons' ),
						'dashboard_links'        => YITH_WCAF_Dashboard()->get_dashboard_navigation_menu(),
						'page_links'             => $this->get_paginate_links( $this->get_current_page( $atts ), ceil( $count / $atts['per_page'] ) ),
					)
				)
			);
		}
	}
}
