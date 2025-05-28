<?php
/**
 * The subscription module.
 *
 * @package WooCommerce\PayPalCommerce\Subscription
 */

declare( strict_types = 1 );

use WooCommerce\PayPalCommerce\Subscription\Helper\SubscriptionHelper;

/**
 * Class SubscriptionModule
 */
class YWSBS_WC_PayPal_Payments_Helper extends SubscriptionHelper {

	/**
	 * Whether the current product is a subscription.
	 *
	 * @return bool
	 */
	public function current_product_is_subscription(): bool {
		$product = wc_get_product();
		return $product && ywsbs_is_subscription_product( $product );
	}

	/**
	 * Whether the current cart contains subscriptions.
	 *
	 * @return bool
	 */
	public function cart_contains_subscription(): bool {
		return ! ! YWSBS_Subscription_Cart::cart_has_subscriptions();
	}

	/**
	 * Checks if order contains subscription.
	 *
	 * @param  int $order_id The order ID.
	 * @return boolean Whether order is a subscription or not.
	 */
	public function has_subscription( $order_id ): bool {
		return YITH_WC_Subscription()->order_has_subscription( $order_id );
	}

	/**
	 * Whether only automatic payment gateways are accepted.
	 *
	 * @return bool
	 */
	public function accept_only_automatic_payment_gateways(): bool {
		return 'no' === get_option( 'ywsbs_enable_manual_renews', 'yes' );
	}

	/**
	 * Whether the subscription plugin is active or not.
	 *
	 * @return bool
	 */
	public function plugin_is_active(): bool {
		return true;
	}
}
