<?php
/*
* This file belongs to the YITH Framework.
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
 * @class      YITH_Stripe_Connect_Cron_Job
 * @package    Yithemes
 * @since      Version 1.0.0
 * @author     Your Inspiration Themes
 *
 */
if ( ! class_exists( 'YITH_Stripe_Connect_Cron_Job' ) ) {

	/**
	 * Class YITH_Stripe_Connect_Cron_Job
	 *
	 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
	 */
	class YITH_Stripe_Connect_Cron_Job {

		/**
		 * YITH_Stripe_Connect_Cron_Job Instance
		 *
		 * @var StripeObject
		 * @since  1.0
		 * @access protected
		 */
		protected static $_instance = null;

		/**
		 * Stripe Connect Handler Instance
		 *
		 * @var YITH_Stripe_Connect_Commissions
		 * @since  1.0.0
		 * @access protected
		 */
		protected $_stripe_connect_commissions = null;

		/**
		 * Construct
		 *
		 * @author Francisco Mateo
		 * @since  1.0
		 */
		public function __construct() {
			add_action( 'yith_wcsc_after_commission_recorded', array( $this, 'schedule_transfer_commission' ) );
			add_action( 'yith_wcsc_proceed_transfer_scheduled', array( $this, 'proceed_transfer' ) );
			$this->_stripe_connect_commissions = YITH_Stripe_Connect_Commissions::instance();
		}

		/**
		 * Schedule transfer commissions
		 *
		 * @return void
		 * @author Francisco Mateo
		 */
		public function schedule_transfer_commission( $commission ) {
			// Check that status of the commission requires transfer.
			if ( ! in_array( $commission['commission_status'], array( 'sc_pending', 'sc_transfer_error' ) ) ) {
				return;
			}

			// Check before that commission not belong to Affiliates to avoid duplicated pays.
			if ( ! empty( $commission['integration_item'] ) ) {
				return;
			}

			if ( 0 == $commission['payment_retarded'] ) {
				$this->proceed_transfer( $commission );

				return;
			}

			$timestamp = strtotime( $commission['purchased_date'] );

			/** APPLY_FILTERS: yith_wcsc_schedule_timestamp_change_format
			*
			* Filter the format of the time when scheduling the comissions.
			*
			* @param strtotime   Timestamp formatted.
			* @param $timestamp  Timestamp of $commission['purchased_date'].
			* @param $commission Commission obj.
			*/
			$timestamp = apply_filters( 'yith_wcsc_schedule_timestamp_change_format', strtotime( sprintf( '+%d days', $commission['payment_retarded'] ), $timestamp ), $timestamp, $commission );

			/** APPLY_FILTERS: yith_wcsc_set_schedule_timestamp
			*
			* Filter the scheduled time when scheduling the comissions.
			*
			* @param $timestamp  Timestamp of $commission['purchased_date'].
			* @param $commission Commission obj.
			*/
			$timestamp = apply_filters( 'yith_wcsc_set_schedule_timestamp', $timestamp, $commission );
			$scheduled = wp_schedule_single_event( $timestamp, 'yith_wcsc_proceed_transfer_scheduled', array( $commission ) );
		}

		/**
		 * Proceed transfer
		 *
		 * @return YITH_Stripe_Connect_API_Handler Main instance
		 * @author Francisco Mateo
		 */
		public function proceed_transfer( $commission ) {

			// Before to proceed with the transfer we change the commission status to sc_transfer_processing
			$commission['commission_status'] = 'sc_transfer_processing';

			$this->_stripe_connect_commissions->update( $commission['ID'], $commission );

			$result = $this->_stripe_connect_commissions->process_transfer( $commission );
		}

		/**
		 * Main plugin Instance
		 *
		 * @return YITH_Stripe_Connect_API_Handler Main instance
		 * @author Francisco Mateo
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
		}
	}
}