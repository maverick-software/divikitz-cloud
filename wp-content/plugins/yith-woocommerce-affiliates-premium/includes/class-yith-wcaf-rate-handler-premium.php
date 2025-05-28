<?php
/**
 * Rate Handler class
 *
 * @author  YITH
 * @package YITH\Affiliates\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCAF' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCAF_Rate_Handler_Premium' ) ) {
	/**
	 * Affiliates Rate Handler
	 *
	 * @since 1.0.0
	 */
	class YITH_WCAF_Rate_Handler_Premium extends YITH_WCAF_Rate_Handler {

		/**
		 * Available click statuses
		 *
		 * @var array
		 */
		protected static $available_rule_types;

		/* === HELPER METHODS === */

		/**
		 * Get rate for an affiliate or a product
		 *
		 * @param int|YITH_WCAF_Affiliate $affiliate Affiliate ID or affiliate object.
		 * @param int|WC_Product          $product   Product id or product object.
		 * @param int|WC_Order            $order     Order id or order object.
		 *
		 * @return float Rate (product specific rate, if any; otherwise, affiliate specific rate, if any; otherwise, general rate)
		 * @since 1.0.0
		 */
		public static function get_rate( $affiliate = false, $product = false, $order = false ) {
			// retrieve affiliate data.
			$affiliate = YITH_WCAF_Affiliate_Factory::get_affiliate( $affiliate );

			// retrieve product data.
			$product = wc_get_product( $product );

			$rule = self::get_best_rule_matching( $affiliate, $product );

			if ( ( ! $rule || 'user_roles' === $rule->get_type() ) && $affiliate && $affiliate->get_rate() ) {
				// some rule types (specifically user_roles type) are weaker than affiliate own rate.
				$rate = $affiliate->get_rate();
			} elseif ( $rule ) {
				// when no affiliate own rate, or higher priority rules, use rule rate.
				$rate = $rule->get_rate();
			} else {
				// when cannot retrieve rate by other means, use default.
				$rate = self::get_default();
			}

			/**
			 * Let third party plugin filter rate
			 *
			 * @since 1.0.9
			 */
			/**
			 * APPLY_FILTERS: yith_wcaf_affiliate_rate
			 *
			 * Filters the affiliate rate.
			 *
			 * @param float               $rate      Affiliate rate.
			 * @param YITH_WCAF_Affiliate $affiliate Affiliate object.
			 * @param WC_Product          $product   Product object.
			 * @param WC_Order            $order     Order object.
			 */
			return apply_filters( 'yith_wcaf_affiliate_rate', $rate, $affiliate, $product, $order );
		}

		/**
		 * Return corrected rate for persistent commission calculation
		 *
		 * @param float                 $rate  Original rate.
		 * @param string                $token Affiliate token (optional, used only in filters).
		 * @param WC_Order_Item_Product $item Order item (optional, used only in filters).
		 *
		 * @return float Corrected rate
		 * @since 1.0.0
		 */
		public static function get_persistent_rate( $rate, $token = '', $item = false ) {
			/**
			 * APPLY_FILTERS: yith_wcaf_persistent_rate
			 *
			 * Filters the persistent rate.
			 *
			 * @param float                 $persistent_rate Persistent rate.
			 * @param string                $token           Affiliate token.
			 * @param WC_Order_Item_Product $item            Order item object.
			 */
			$persistent_rate = (float) apply_filters( 'yith_wcaf_persistent_rate', get_option( 'yith_wcaf_persistent_rate' ), $token, $item );

			return $persistent_rate * (float) $rate / 100;
		}

		/* === RATE RULE METHODS === */

		/**
		 * Returns an array of possible rate rule types
		 *
		 * @return array Array of supported rule types.
		 */
		public static function get_supported_rule_types() {
			if ( empty( self::$available_rule_types ) ) {
				self::$available_rule_types = array(
					'affiliate_ids'         => _x( 'User rate', '[ADMIN] Rate rule type', 'yith-woocommerce-affiliates' ),
					'product_ids'           => _x( 'Product rate', '[ADMIN] Rate rule type', 'yith-woocommerce-affiliates' ),
					'product_categories'    => _x( 'Product category rate', '[ADMIN] Rate rule type', 'yith-woocommerce-affiliates' ),
					'user_roles'            => _x( 'User role rate', '[ADMIN] Rate rule type', 'yith-woocommerce-affiliates' ),
					'affiliate_product_ids' => _x( 'User/product rate', '[ADMIN] Rate rule type', 'yith-woocommerce-affiliates' ),
				);
			}

			return self::$available_rule_types;
		}

		/**
		 * Get rule that bast fits search parameters
		 *
		 * Assuming both $affiliate and $product are not empty, it will follow this steps:
		 * * Search for rules matching Affiliate + Product;
		 * * Search for rules matching Affiliate + Product Parent;
		 * * Search for rules matching Product;
		 * * Search for rules matching Product Parent;
		 * * Search for rules matching any Product Category;
		 * * Search for Affiliate specific role
		 * * Search for rules matching any Affiliate role;
		 *
		 * If any of the previous steps returns a set of matching rules, method will return
		 * first rule of the match, highest in priority.
		 * If no rule is matched, false will be returned instead.
		 *
		 * @param YITH_WCAF_Affiliate|bool $affiliate Affiliate object.
		 * @param WC_Product|bool          $product   Product object.
		 *
		 * @return YITH_WCAF_Rate_Rule|bool Best matching rule or false.
		 */
		public static function get_best_rule_matching( $affiliate = false, $product = false ) {
			// retrieve search parameters.
			$affiliate_id       = $affiliate ? $affiliate->get_id() : false;
			$user_roles         = $affiliate ? $affiliate->get_user()->roles : false;
			$product            = $product instanceof WC_Product_Variation ? wc_get_product( $product->get_parent_id() ) : $product;
			$product_id         = $product ? $product->get_id() : false;
			$product_parent     = $product ? $product->get_parent_id() : false;
			$product_categories = $product ? $product->get_category_ids() : false;

			// prepare conditions sets.
			$conditions_sets = array(
				// Search for rules matching Affiliate + Product.
				array(
					'product_id'   => $product_id,
					'affiliate_id' => $affiliate_id,
				),

				// Search for rules matching Affiliate + Product Parent.
				array(
					'product_id'   => $product_parent,
					'affiliate_id' => $affiliate_id,
				),

				// Search for rules matching Product.
				array(
					'product_id' => $product_id,
				),

				// Search for rules matching Product Parent.
				array(
					'product_id' => $product_parent,
				),

				// Search for rules matching Product Categories.
				array(
					'product_cat' => $product_categories,
				),

				// Search for rules matching Affiliate.
				array(
					'affiliate_id' => $affiliate_id,
				),

				// Search for Affiliate specific role.
				array(
					'user_role' => $user_roles,
				),
			);

			// remove conditions that cannot be applied.
			$conditions_sets = array_values(
				array_filter(
					$conditions_sets,
					function ( $item ) {
						$res = true;

						// if any of the condition is empty, remove it.
						foreach ( $item as $value ) {
							if ( empty( $value ) ) {
								$res = false;
								break;
							}
						}

						return $res;
					}
				)
			);

			// if conditions are empty, return.
			if ( empty( $conditions_sets ) ) {
				return false;
			}

			// cycle conditions until it matches a set of rules.
			$index = 0;

			do {
				$conditions = $conditions_sets[ $index ];
				$rules      = YITH_WCAF_Rate_Rule_Factory::get_rules(
					array_merge(
						array(
							'enabled' => 1,
						),
						$conditions
					)
				);

				$index++;
			} while ( $rules && $rules->is_empty() && isset( $conditions_sets[ $index ] ) );

			$match = false;

			if ( ! $rules->is_empty() ) {
				$match = $rules->get_head();
			}

			/**
			 * APPLY_FILTERS: yith_wcaf_matched_rule
			 *
			 * Filters whether the rule fits search parameters.
			 *
			 * @param bool                $match     Whether the rule fits search parameters or not.
			 * @param YITH_WCAF_Affiliate $affiliate Affiliate object.
			 * @param WC_Product          $product   Product object.
			 */
			return apply_filters( 'yith_wcaf_matched_rule', $match, $affiliate, $product );
		}
	}
}
