<?php
/**
 * The subscription module.
 *
 * @package WooCommerce\PayPalCommerce\Subscription
 */

declare( strict_types = 1 );

use WooCommerce\PayPalCommerce\Vendor\Dhii\Container\ServiceProvider;
use WooCommerce\PayPalCommerce\Vendor\Dhii\Modular\Module\ModuleInterface;
use Psr\Log\LoggerInterface;
use WooCommerce\PayPalCommerce\ApiClient\Exception\RuntimeException;
use WooCommerce\PayPalCommerce\Subscription\Helper\SubscriptionHelper;
use WooCommerce\PayPalCommerce\Vaulting\PaymentTokenRepository;
use WooCommerce\PayPalCommerce\WcGateway\Gateway\PayPalGateway;
use WooCommerce\PayPalCommerce\WcGateway\Gateway\CreditCardGateway;
use WooCommerce\PayPalCommerce\Vendor\Interop\Container\ServiceProviderInterface;
use WooCommerce\PayPalCommerce\Vendor\Psr\Container\ContainerInterface;
use WooCommerce\PayPalCommerce\WcGateway\Settings\Settings;
use WooCommerce\PayPalCommerce\WcGateway\Exception\NotFoundException;

/**
 * Class SubscriptionModule
 */
class YWSBS_WC_PayPal_Payments_Module implements ModuleInterface {

	/**
	 * {@inheritDoc}
	 */
	public function setup(): ServiceProviderInterface {
		return new ServiceProvider(
			array(
				'subscription.helper'                => static function ( ContainerInterface $container ): SubscriptionHelper {
					return new YWSBS_WC_PayPal_Payments_Helper();
				},
				'ywsbs-subscription.renewal-handler' => static function ( ContainerInterface $container ): YWSBS_WC_PayPal_Payments_Renewal_Handler {
					$logger                        = $container->get( 'woocommerce.logger.woocommerce' );
					$repository                    = $container->get( 'vaulting.repository.payment-token' );
					$endpoint                      = $container->get( 'api.endpoint.order' );
					$purchase_unit_factory         = $container->get( 'api.factory.purchase-unit' );
					$payer_factory                 = $container->get( 'api.factory.payer' );
					$environment                   = $container->get( 'onboarding.environment' );
					$settings                      = $container->get( 'wcgateway.settings' );
					$authorized_payments_processor = $container->get( 'wcgateway.processor.authorized-payments' );

					return new YWSBS_WC_PayPal_Payments_Renewal_Handler(
						$logger,
						$repository,
						$endpoint,
						$purchase_unit_factory,
						$container->get( 'api.factory.shipping-preference' ),
						$payer_factory,
						$environment,
						$settings,
						$authorized_payments_processor
					);
				},
			),
			array()
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function run( ContainerInterface $c ): void {

		// Add integration for yith_subscription.
		add_filter( 'woocommerce_payment_gateway_supports', array( $this, 'register_supports' ), 10, 3 );

		add_action(
			'ywsbs_renew_subscription',
			function ( $order_id, $subscription_id ) use ( $c ) {
				$subscription = ywsbs_get_subscription( $subscription_id );
				if ( ! in_array( $subscription->get_payment_method(), array( PayPalGateway::ID, CreditCardGateway::ID ), true ) ) {
					return;
				}

				$order = wc_get_order( $order_id );
				$this->renew( $order, $c );
			},
			10,
			2
		);

		add_action(
			'ywsbs_pay_renew_order_with_' . PayPalGateway::ID,
			function( $renew_order ) use ( $c ) {
				$this->pay_renew( $renew_order, $c );
			},
			10,
			1
		);

		add_action(
			'ywsbs_pay_renew_order_with_' . CreditCardGateway::ID,
			function( $renew_order ) use ( $c ) {
				$this->pay_renew( $renew_order, $c );
			},
			10,
			1
		);

		add_action(
			'ywsbs_subscription_payment_complete',
			function ( $subscription ) use ( $c ) {
				$payment_token_repository = $c->get( 'vaulting.repository.payment-token' );
				$logger                   = $c->get( 'woocommerce.logger.woocommerce' );

				$this->add_payment_token_id( $subscription, $payment_token_repository, $logger );
			}
		);
	}

	/**
	 * Handles a Subscription product renewal.
	 *
	 * @param \WC_Order               $order WooCommerce order.
	 * @param ContainerInterface|null $container The container.
	 * @return void
	 */
	protected function renew( $order, $container ) {
		if ( ! ( $order instanceof \WC_Order ) ) {
			return;
		}

		$handler = $container->get( 'ywsbs-subscription.renewal-handler' );
		$handler->renew( $order );
	}

	/**
	 * Handles a Subscription product renewal.
	 *
	 * @param \WC_Order               $order WooCommerce order.
	 * @param ContainerInterface|null $container The container.
	 * @return void
	 */
	protected function pay_renew( $order, $container ) {
		if ( ! ( $order instanceof \WC_Order ) ) {
			return;
		}

		$handler = $container->get( 'ywsbs-subscription.renewal-handler' );
		$handler->pay_renew( $order );
	}

	/**
	 * Adds Payment token ID to subscription.
	 *
	 * @param \YWSBS_Subscription    $subscription             The subscription.
	 * @param PaymentTokenRepository $payment_token_repository The payment repository.
	 * @param LoggerInterface        $logger                   The logger.
	 */
	protected function add_payment_token_id( \YWSBS_Subscription $subscription, PaymentTokenRepository $payment_token_repository, LoggerInterface $logger ) {
		try {
			$tokens = $payment_token_repository->all_for_user_id( $subscription->get_user_id() );
			if ( $tokens ) {
				$latest_token_id = end( $tokens )->id() ? end( $tokens )->id() : '';
				$subscription->set( 'payment_token_id', $latest_token_id );
			}
		} catch ( RuntimeException $error ) {
			$message = sprintf(
			// translators: %1$s is the payment token Id, %2$s is the error message.
				__(
					'Could not add token Id to subscription %1$s: %2$s',
					'yith-woocommerce-subscription'
				),
				$subscription->get_id(),
				$error->getMessage()
			);

			$logger->log( 'warning', $message );
		}
	}


	/**
	 * Add yith_subscriptions support to PayPal Standard.
	 *
	 * @param bool          $support Tell if the feature is support.
	 * @param string        $feature Feature to check.
	 * @param object|string $gateway Current gateway.
	 *
	 * @return bool
	 */
	public function register_supports( $support, $feature, $gateway ) {
		$gateway = is_object( $gateway ) ? $gateway->id : $gateway;

		if ( ! in_array( $gateway, array( PayPalGateway::ID, CreditCardGateway::ID ), true ) ) {
			return $support;
		}

		$supports = array( 'yith_subscriptions',
			'yith_subscriptions_scheduling',
			'yith_subscriptions_pause',
			'yith_subscriptions_multiple',
			'yith_subscriptions_payment_date',
			'yith_subscriptions_recurring_amount', );
		return in_array( $feature, $supports, true ) ? true : $support;
	}
}
