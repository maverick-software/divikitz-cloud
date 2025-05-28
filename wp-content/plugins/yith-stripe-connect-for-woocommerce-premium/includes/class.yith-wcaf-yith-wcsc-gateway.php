<?php
/*
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * YITH Stripe Connect to Affiliates Gateway class
 *
 * @author  Francisco Javier Mateo <francisco.mateo@yithemes.com>
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCSC_VERSION' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCAF_YITH_WCSC' ) ) {
	/**
	 * YITH Stripe Connect to Affiliates Gateway class
	 *
	 * @since 1.0.0
	 */
	class YITH_WCAF_YITH_WCSC {

		/**
		 * Single instance of the class for each token
		 *
		 * @var \YITH_WCAF_YITH_WCSC
		 * @since 1.0.0
		 */
		protected static $instance = null;

		/* === PAYMENT METHODS === */

		/**
		 * Execute a mass payment
		 *
		 * @param array $payments_id Array of registered payments to credit to funds.
		 *
		 * @return mixed Array with operation status and messages
		 * @since 1.0.0
		 */
		public function pay( $payments_id ) {
			// skip if affiliates doesn't match requirements.
			if ( ! defined( 'YITH_WCAF::VERSION' ) || version_compare( YITH_WCAF::VERSION, '2.0.0', '<=' ) ) {
				return array(
					'status'   => false,
					'messages' => __( 'Please, update affiliates to latest version.', 'yith-stripe-connect-for-woocommerce' ),
				);
			}

			$api_handler               = YITH_Stripe_Connect_API_Handler::instance();
			$stripe_connect_commission = YITH_Stripe_Connect_Commissions::instance();
			$stripe_connect_gateway    = YITH_Stripe_Connect()->get_gateway( false );

			// if single payment id, convert it to array.
			if ( ! is_array( $payments_id ) ) {
				$payments_id = (array) $payments_id;
			}

			$currency       = get_woocommerce_currency();
			$status_message = array(
				'status' => true,
			);

			foreach ( $payments_id as $payment_id ) {

				$payment     = YITH_WCAF_Payment_Factory::get_payment( $payment_id );
				$user        = $payment->get_affiliate()->get_user();
				$destination = $user->stripe_user_id;
				$commissions = $payment->get_commissions();

				if ( ! empty( $destination ) ) {
					$args = array(
						'amount'      => yith_wcsc_get_amount( $payment['amount'] ),
						'currency'    => $currency,
						'destination' => $destination,
					);

					$transfer = $api_handler->create_transfer( $args );
					if ( isset( $transfer['error_transfer'] ) ) {

						// Prepare message.
						$error_message = __( 'Please take a look at Stripe Connect log file for more details.', 'yith-stripe-connect-for-woocommerce' );

						// Display messages on order note and log file.
						$stripe_connect_gateway->log( 'error', __( 'Affiliates\' payment: ', 'yith-stripe-connect-for-woocommerce' ) . $transfer['error_transfer'] );

						$payment->add_note( $transfer['error_transfer'] );

						return array(
							'status'   => false,
							'messages' => $error_message,
						);
					} elseif ( $transfer instanceof \Stripe\Transfer ) {
						$payment->add_note( __( 'Payment correctly issued to Stripe Connect', 'yith-stripe-connect-for-woocommerce' ) );
						$payment->set_status( 'completed' );
						$payment->save();

						/** DO_ACTION: yith_wcaf_payment_sent
						*
						* Adds an action when sending the payment.
						*
						* @param $payment Payment obj.
						*/
						do_action( 'yith_wcaf_payment_sent', $payment );

						foreach ( $commissions as $affiliate_commission ) {
							// Prepare affiliate items.
							$integration_item = array(
								'plugin_integration'      => 'affiliates',
								'payment_id'              => $payment_id,
								'affiliate_commission_id' => $affiliate_commission->get_id(),
							);
							// Prepare the notes to commssion.
							$notes = array(
								'transfer_id'         => $transfer->id,
								'destination_payment' => $transfer->destination,
							);

							$sc_commission = array(
								'user_id'           => $user->ID,
								'order_id'          => $affiliate_commission->get_order_id(),
								'order_item_id'     => $affiliate_commission->get_order_item_id(),
								'product_id'        => $affiliate_commission->get_product_id(),
								'commission'        => $affiliate_commission->get_amount(),
								'commission_status' => 'sc_transfer_success',
								'commission_type'   => 'percentage',
								'commission_rate'   => $affiliate_commission->get_rate(),
								'payment_retarded'  => 0,
								'purchased_date'    => $affiliate_commission->get_created_at(),
								'note'              => maybe_serialize( $notes ),
								'integration_item'  => maybe_serialize( $integration_item ),
							);
							$stripe_connect_commission->insert( $sc_commission );
						}
					}
				} else {
					$status_message['status']   = false;
					$status_message['messages'] = sprintf(
						// Translators: 1. User display name.
						__( 'The destination account is not connected to any Stripe account.', 'yith-stripe-connect-for-woocommerce' ),
						$user->display_name
					);
				}
			}

			if ( ! $status_message['status'] ) {
				$status_message['status']   = true;
				$status_message['messages'] = __( 'Payment sent', 'yith-stripe-connect-for-woocommerce' );
			}

			return $status_message;
		}

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCAF_YITH_WCSC
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
}

/**
 * Unique access to instance of YITH_WCAF_YITH_WCSC class
 *
 * @return \YITH_WCAF_YITH_WCSC
 * @since 1.0.0
 */
function YITH_WCAF_YITH_WCSC() {
	return YITH_WCAF_YITH_WCSC::get_instance();
}
