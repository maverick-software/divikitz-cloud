<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined( 'YITH_WCSC_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 *
 *
 * @class      YITH_Stripe_Connect_WC_API
 * @package    Yithemes
 * @since      Version 1.0.0
 * @author     Francsico Mateo
 *
 */

if ( ! class_exists( 'YITH_Stripe_Connect_WC_API' ) ) {
	/**
	 * Class YITH_Stripe_Connect_WC_API
	 *
	 * @author Francsico Mateo
	 */
	class YITH_Stripe_Connect_WC_API {

		/**
		 * Constructor method
		 *
		 * @return void
		 */
		public function __construct() {
			add_action( 'woocommerce_api_sc_webhook_event', array( $this, 'manage_webhook' ) );
		}

		/**
		 * Generic webhook handling
		 *
		 * @return void
		 */
		public function manage_webhook() {
			$input      = @ file_get_contents( 'php://input' );
			$event_json = json_decode( $input );

			if ( empty( $event_json ) ) {
				$this->_send_failure( 'Failed to decode event' );
			}

			$type_method = str_replace( '.', '_', $event_json->type );

			if ( method_exists( $this, $type_method ) ) {
				call_user_func( array( $this, $type_method ), $event_json );
			} else {
				$this->_send_success( 'No handler found' );
			}

			$this->_send_success( 'No further action required' );
		}

		/* === WEBHOOK HANDLING METHODS === */

		/**
		 * Handle users de-authorized from Stripe dashboard
		 *
		 * @param object $event Event object.
		 * @return void
		 */
		public function account_application_deauthorized( $event ) {
			$stripe_user_id           = $event->account;
			$stripe_connect_receivers = YITH_Stripe_Connect_Receivers::instance();
			$stripe_connect_gateway   = YITH_Stripe_Connect()->get_gateway( false );

			$users = get_users(
				array(
					'meta_key'   => 'stripe_user_id',
					'meta_value' => $stripe_user_id,
				)
			);

			$user = ! empty( $users ) ? $users[0] : '';

			if ( ! empty( $user ) ) {
				$user_id                    = $user->id;
				$acc_deleted_from_site      = delete_user_meta( $user_id, 'stripe_user_id' );
				$acc_deleted_access_token   = delete_user_meta( $user_id, 'stripe_access_token' );
				$acc_deleted_from_receivers = $stripe_connect_receivers->update_by_user_id(
					$user_id,
					array(
						'stripe_id'       => '',
						'status_receiver' => 'disconnect',
					)
				);

				if ( ! $acc_deleted_from_site && ! $acc_deleted_access_token && ! $acc_deleted_from_receivers ) {
					$stripe_connect_gateway->log(
						'error',
						sprintf(
							// translators: 1. User display name.
							__( 'account.application.deauthorized Stripe Webhook event is disconnect for %s, but it could not be removed from the server', 'yith-stripe-connect-for-woocommerce' ),
							$user->display_name
						)
					);
				} else {
					$stripe_connect_gateway->log(
						'info',
						sprintf(
							// translators: 1. User display name.
							__( 'account.application.deauthorized Stripe Webhook event is disconnect for %s', 'yith-stripe-connect-for-woocommerce' ),
							$user->display_name
						)
					);
				}
			}
		}

		/**
		 * Handle source.chargeable event
		 *
		 * @param object $event Event object.
		 * @return void
		 */
		public function source_chargeable( $event ) {
			$source  = $event->data->object;
			$gateway = YITH_Stripe_Connect::instance()->get_gateway( true, 'multibanco' );

			if ( ! $gateway ) {
				$this->_send_failure( 'Missing gateway' );
			}

			// retrieve metadata.
			$order_id = isset( $source->metadata->order_id ) ? $source->metadata->order_id : null;
			$instance = isset( $source->metadata->instance ) ? $source->metadata->instance : null;

			if ( ! $order_id || ! $instance ) {
				$this->_send_success( 'Missing required metadata' );
			}

			$this->_check_instance( $instance, 'multibanco' );

			// check order.
			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				$this->_send_success( 'Missing order #' . $order_id );
			}

			$gateway->process_chargeable_source( $order, $source->id );
		}

		/**
		 * Handle source.failed event
		 *
		 * @param object $event Event object.
		 * @return void
		 */
		public function source_failed( $event ) {
			$source  = $event->data->object;

			// retrieve metadata.
			$order_id = isset( $source->metadata->order_id ) ? $source->metadata->order_id : null;
			$instance = isset( $source->metadata->instance ) ? $source->metadata->instance : null;

			if ( ! $order_id || ! $instance ) {
				$this->_send_success( 'Missing required metadata' );
			}

			$this->_check_instance( $instance, 'multibanco' );

			// check order.
			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				$this->_send_success( 'Missing order #' . $order_id );
			}

			$order->set_status( 'cancelled', __( 'Source payment failed', 'yith-stripe-connect-for-woocommerce' ) );
		}

		/**
		 * Handle source.canceled event
		 *
		 * @param object $event Event object.
		 * @return void
		 */
		public function source_canceled( $event ) {
			$source  = $event->data->object;

			// retrieve metadata.
			$order_id = isset( $source->metadata->order_id ) ? $source->metadata->order_id : null;
			$instance = isset( $source->metadata->instance ) ? $source->metadata->instance : null;

			if ( ! $order_id || ! $instance ) {
				$this->_send_success( 'Missing required metadata' );
			}

			$this->_check_instance( $instance, 'multibanco' );

			// check order.
			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				$this->_send_success( 'Missing order #' . $order_id );
			}

			$order->set_status( 'cancelled', __( 'Source payment cancelled', 'yith-stripe-connect-for-woocommerce' ) );
		}

		/* === UTILS === */

		/**
		 * Returns success status for the webhook
		 *
		 * @param string $msg Additional msg to show.
		 * @return void
		 */
		private function _send_success( $msg = '' ) {
			status_header( 200 );
			header( 'Content-Type: text/plain' );

			if ( ! empty( $msg ) ) {
				echo $msg;
			}

			exit( 0 );
		}

		/**
		 * Returns an error status for the webhook
		 *
		 * @param string $msg Additional msg to show.
		 * @return void
		 */
		private function _send_failure( $msg = '' ) {
			status_header( 500 );
			header( 'Content-Type: plain/text' );

			if ( ! empty( $msg ) ) {
				echo $msg;
			}

			exit( 0 );
		}

		/**
		 * Check instance before proceeding with event handling
		 *
		 * @param string $instance Instance retrieved from event object.
		 * @param string $gateway  Specific gateway to use, or empty string to use base gateway.
		 *
		 * @return void
		 */
		private function _check_instance( $instance, $gateway = '' ) {
			$gateway = YITH_Stripe_Connect::instance()->get_gateway( true, $gateway );

			if ( ! $gateway ) {
				$this->_send_failure( 'Missing gateway' );
			}

			if ( ! $gateway || is_null( $instance ) || $instance != $gateway->instance_url ) {
				$this->_send_success( 'Instance does not match -> ' . $instance . ' : ' . $gateway->instance_url );
			}
		}
	}
}