<?php
/**
 * Handles subscription renewals.
 *
 * @package WooCommerce\PayPalCommerce\Subscription
 */

declare( strict_types = 1 );

use WooCommerce\PayPalCommerce\ApiClient\Endpoint\OrderEndpoint;
use WooCommerce\PayPalCommerce\ApiClient\Entity\Authorization;
use WooCommerce\PayPalCommerce\ApiClient\Entity\AuthorizationStatus;
use WooCommerce\PayPalCommerce\ApiClient\Entity\Order;
use WooCommerce\PayPalCommerce\ApiClient\Entity\PaymentToken;
use WooCommerce\PayPalCommerce\ApiClient\Exception\PayPalApiException;
use WooCommerce\PayPalCommerce\ApiClient\Factory\PayerFactory;
use WooCommerce\PayPalCommerce\ApiClient\Factory\PurchaseUnitFactory;
use WooCommerce\PayPalCommerce\ApiClient\Factory\ShippingPreferenceFactory;
use WooCommerce\PayPalCommerce\Onboarding\Environment;
use WooCommerce\PayPalCommerce\Vaulting\PaymentTokenRepository;
use Psr\Log\LoggerInterface;
use WooCommerce\PayPalCommerce\WcGateway\Exception\NotFoundException;
use WooCommerce\PayPalCommerce\WcGateway\Processor\AuthorizedPaymentsProcessor;
use WooCommerce\PayPalCommerce\WcGateway\Processor\OrderMetaTrait;
use WooCommerce\PayPalCommerce\WcGateway\Processor\PaymentsStatusHandlingTrait;
use WooCommerce\PayPalCommerce\WcGateway\Processor\TransactionIdHandlingTrait;
use WooCommerce\PayPalCommerce\WcGateway\Settings\Settings;

/**
 * Class RenewalHandler
 */
class YWSBS_WC_PayPal_Payments_Renewal_Handler {

	use OrderMetaTrait;
	use TransactionIdHandlingTrait;
	use PaymentsStatusHandlingTrait;

	/**
	 * The logger.
	 *
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * The payment token repository.
	 *
	 * @var PaymentTokenRepository
	 */
	private $repository;

	/**
	 * The order endpoint.
	 *
	 * @var OrderEndpoint
	 */
	private $order_endpoint;

	/**
	 * The purchase unit factory.
	 *
	 * @var PurchaseUnitFactory
	 */
	private $purchase_unit_factory;

	/**
	 * The shipping_preference factory.
	 *
	 * @var ShippingPreferenceFactory
	 */
	private $shipping_preference_factory;

	/**
	 * The payer factory.
	 *
	 * @var PayerFactory
	 */
	private $payer_factory;

	/**
	 * The environment.
	 *
	 * @var Environment
	 */
	protected $environment;

	/**
	 * The settings
	 *
	 * @var Settings
	 */
	protected $settings;

	/**
	 * The processor for authorized payments.
	 *
	 * @var AuthorizedPaymentsProcessor
	 */
	protected $authorized_payments_processor;

	/**
	 * RenewalHandler constructor.
	 *
	 * @param LoggerInterface             $logger                        The logger.
	 * @param PaymentTokenRepository      $repository                    The payment token repository.
	 * @param OrderEndpoint               $order_endpoint                The order endpoint.
	 * @param PurchaseUnitFactory         $purchase_unit_factory         The purchase unit factory.
	 * @param ShippingPreferenceFactory   $shipping_preference_factory   The shipping_preference factory.
	 * @param PayerFactory                $payer_factory                 The payer factory.
	 * @param Environment                 $environment                   The environment.
	 * @param Settings                    $settings                      The Settings.
	 * @param AuthorizedPaymentsProcessor $authorized_payments_processor The Authorized Payments Processor.
	 */
	public function __construct(
		LoggerInterface $logger,
		PaymentTokenRepository $repository,
		OrderEndpoint $order_endpoint,
		PurchaseUnitFactory $purchase_unit_factory,
		ShippingPreferenceFactory $shipping_preference_factory,
		PayerFactory $payer_factory,
		Environment $environment,
		Settings $settings,
		AuthorizedPaymentsProcessor $authorized_payments_processor
	) {

		$this->logger                        = $logger;
		$this->repository                    = $repository;
		$this->order_endpoint                = $order_endpoint;
		$this->purchase_unit_factory         = $purchase_unit_factory;
		$this->shipping_preference_factory   = $shipping_preference_factory;
		$this->payer_factory                 = $payer_factory;
		$this->environment                   = $environment;
		$this->settings                      = $settings;
		$this->authorized_payments_processor = $authorized_payments_processor;
	}

	/**
	 * Renew an order.
	 *
	 * @param \WC_Order $wc_order The WooCommerce order.
	 */
	public function renew( \WC_Order $wc_order ) {
		try {
			$this->process_order( $wc_order );
		} catch ( \Exception $exception ) {
			$error = $exception->getMessage();
			if ( is_a( $exception, PayPalApiException::class ) ) {
				$error = $exception->get_details( $error );
			}

			$wc_order->update_status(
				'failed',
				$error
			);

			$error_message = sprintf(
				'An error occurred while trying to renew the subscription for order %1$d: %2$s',
				$wc_order->get_id(),
				$error
			);
			$this->logger->error( $error_message );

			return;
		}

		$this->logger->info(
			sprintf(
				'Renewal for order %d is completed.',
				$wc_order->get_id()
			)
		);
	}

	/**
	 * Pay a renewal order.
	 *
	 * @param \WC_Order $renewal_order The WooCommerce order.
	 * @return boolean
	 * @throws Exception Failed payment errors.
	 */
	public function pay_renew( \WC_Order $renewal_order ): bool {
		if ( ! $renewal_order instanceof \WC_Order ) {
			return false;
		}

		$order_id     = $renewal_order->get_id();
		$is_a_renew   = $renewal_order->get_meta( 'is_a_renew' );
		$subscription = $this->get_subscription_from_renew_order( $renewal_order );

		if ( ! $subscription || 'yes' !== $is_a_renew ) {
			yith_subscription_log( sprintf( 'Sorry, any subscription was found for order #%s or order is not a renew.', $order_id ), 'subscription_payment' );
			return false;
		}

		try {

			$user_id  = (int) $renewal_order->get_customer_id();
			$customer = new \WC_Customer( $user_id );
			$token    = $this->get_token_for_customer( $customer, $renewal_order );
			if ( ! $token ) {
				// translators: %s stand for the user ID.
				throw new Exception( sprintf( __( 'No payment token found for customer #%s', 'yith-woocommerce-subscription' ), $user_id ) );
			}

			$amount = $renewal_order->get_total();
			if ( $amount <= 0 ) {
				$renewal_order->payment_complete();
				return true;
			}

			if ( ! $this->authorized_payments_processor->capture_authorized_payment( $renewal_order ) ) {
				// translators: %s stand for the order ID.
				throw new Exception( sprintf( __( 'Capture payment for order #%s failed', 'yith-woocommerce-subscription' ), $renewal_order->get_id() ) );
			}

			return true;

		} catch ( Exception $e ) {
			yith_subscription_log( sprintf( 'Cannot pay order #%s related to the subscription #%s: %s', $order_id, $subscription->id, $e->getMessage() ), 'subscription_payment' );
			ywsbs_register_failed_payment( $renewal_order, 'Error: ' . $e->getMessage() );

			return false;
		}
	}

	/**
	 * Force intent to AUTHORIZE for renews.
	 *
	 * @param array $data An array of request data.
	 * @return array
	 */
	public function force_authorize_for_renew( array $data ): array {
		return array_merge( $data, array( 'intent' => 'AUTHORIZE' ) );
	}

	/**
	 * Changes the order status, based on the authorization.
	 *
	 * @param Authorization $authorization The authorization.
	 * @param WC_Order      $wc_order      The WC order.
	 *
	 * @throws RuntimeException If payment denied.
	 */
	protected function handle_authorization_status( Authorization $authorization, WC_Order $wc_order ): void {
		$status  = $authorization->status();
		$details = $status->details();
		if ( $details ) {
			$this->add_status_details_note( $wc_order, $status->name(), $details->text() );
		}

		switch ( $status->name() ) {
			case AuthorizationStatus::CREATED:
			case AuthorizationStatus::PENDING:
				$status = 'on-hold';
				if ( $wc_order->get_meta( 'is_a_renew' ) ) {
					$subscription = $this->get_subscription_from_renew_order( $wc_order );
					$status       = YWSBS_Subscription_Order()->get_renew_order_status( $subscription );
				}

				$wc_order->update_status(
					$status,
					__( 'Awaiting payment.', 'woocommerce-paypal-payments' )
				);
				break;
			case AuthorizationStatus::DENIED:
				$wc_order->update_status(
					'failed',
					__( 'Could not get the payment authorization.', 'woocommerce-paypal-payments' )
				);
				throw new RuntimeException( __( 'Payment provider declined the payment, please use a different payment method.', 'woocommerce-paypal-payments' ) );
		}
	}

	/**
	 * Process a WooCommerce order.
	 *
	 * @param \WC_Order $wc_order The WooCommerce order.
	 *
	 * @throws \Exception If customer cannot be read/found.
	 */
	private function process_order( \WC_Order $wc_order ): void {

		$user_id  = (int) $wc_order->get_customer_id();
		$customer = new \WC_Customer( $user_id );
		$token    = $this->get_token_for_customer( $customer, $wc_order );
		if ( ! $token ) {
			return;
		}
		$purchase_unit       = $this->purchase_unit_factory->from_wc_order( $wc_order );
		$payer               = $this->payer_factory->from_customer( $customer );
		$shipping_preference = $this->shipping_preference_factory->from_state(
			$purchase_unit,
			'renewal'
		);

		if ( 'yes' === $wc_order->get_meta( 'is_a_renew' ) ) {
			add_filter( 'ppcp_create_order_request_body_data', array( $this, 'force_authorize_for_renew' ), 10, 1 );
		}

		$order = $this->order_endpoint->create(
			array( $purchase_unit ),
			$shipping_preference,
			$payer,
			$token
		);

		$this->add_paypal_meta( $wc_order, $order, $this->environment );

		if ( $order->intent() === 'AUTHORIZE' ) {
			$order = $this->order_endpoint->authorize( $order );
			$wc_order->update_meta_data( AuthorizedPaymentsProcessor::CAPTURED_META_KEY, 'false' );
		}

		$transaction_id = $this->get_paypal_order_transaction_id( $order );
		if ( $transaction_id ) {
			$this->update_transaction_id( $transaction_id, $wc_order );
		}

		$this->handle_new_order_status( $order, $wc_order );

		remove_filter( 'ppcp_create_order_request_body_data', array( $this, 'force_authorize_for_renew' ), 10 );
	}

	/**
	 * Returns a payment token for a customer.
	 *
	 * @param \WC_Customer $customer The customer.
	 * @param \WC_Order    $wc_order The current WooCommerce order we want to process.
	 *
	 * @return PaymentToken|null
	 */
	private function get_token_for_customer( \WC_Customer $customer, \WC_Order $wc_order ) {
		/**
		 * Returns a payment token for a customer, or null.
		 */
		$token = apply_filters( 'woocommerce_paypal_payments_subscriptions_get_token_for_customer', null, $customer, $wc_order );
		if ( null !== $token ) {
			return $token;
		}

		$tokens = $this->repository->all_for_user_id( (int) $customer->get_id() );
		if ( ! $tokens ) {
			$error_message = sprintf(
				'Payment failed. No payment tokens found for customer %d.',
				$customer->get_id()
			);

			$wc_order->update_status(
				'failed',
				$error_message
			);

			$this->logger->error( $error_message );
		}

		$subscriptions = $wc_order->get_meta( 'subscriptions' );
		if ( ! empty( $subscription ) ) {
			foreach ( $subscriptions as $subscription_id ) {
				$subscription = ywsbs_get_subscription( $subscription_id );
				$token_id     = $subscription->get( 'payment_token_id' );
				if ( $token_id ) {
					foreach ( $tokens as $token ) {
						if ( $token_id === $token->id() ) {
							return $token;
						}
					}
				}
			}
		}

		return current( $tokens );
	}

	/**
	 * Get subscription object from renew order
	 *
	 * @param \WC_Order $renewal_order The WooCommerce order.
	 * @return YWSBS_Subscription|bool
	 */
	private function get_subscription_from_renew_order( \WC_Order $renewal_order ) {
		$subscriptions   = $renewal_order->get_meta( 'subscriptions' );
		$subscription_id = ! empty( $subscriptions ) ? array_shift( $subscriptions ) : false; // $subscriptions is always an array of 1 element.

		return $subscription_id ? ywsbs_get_subscription( $subscription_id ) : false;
	}
}
