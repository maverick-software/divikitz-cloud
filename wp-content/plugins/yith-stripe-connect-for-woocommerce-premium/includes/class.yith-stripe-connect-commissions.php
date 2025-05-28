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
 * @class      YITH_Stripe_Connect_Commissions
 * @package    Yithemes
 * @since      Version 1.0.0
 * @author     Francisco Mateo
 *
 */

if ( ! class_exists( 'YITH_Stripe_Connect_Commissions' ) ) {
	/**
	 * Class YITH_Stripe_Connect_Commissions
	 *
	 * @author Francisco Mateo
	 */
	class YITH_Stripe_Connect_Commissions {

		/**
		 * YITH_Stripe_Connect_Commissions Instance
		 *
		 * @since  1.0
		 * @access protected
		 */
		protected static $_instance = null;

		public $api_handler = null;
		public $stripe_connect_gateway = null;
		public $items_per_page = '';

		/**
		 * Construct
		 *
		 * @author Francisco Mateo
		 * @since  1.0
		 */
		public function __construct() {
			$this->api_handler            = YITH_Stripe_Connect_API_Handler::instance();
			$this->stripe_connect_gateway = YITH_Stripe_Connect()->get_gateway( false );

			/** APPLY_FILTERS: yith_wcsc_commissions_items_per_page
			*
			* Filter the default items per page in the commissions table.
			*
			* @param float Default number.
			*/
			$this->items_per_page = apply_filters( 'yith_wcsc_commissions_items_per_page', 20 );

			// Load Actions that our Commissions class will use.
			add_action( 'wp_ajax_wcsc_export_csv_action', array( $this, 'export_csv' ) );
			add_action( 'wp_ajax_wcsc_export_pdf_action', array( $this, 'export_pdf' ) );
			add_action( 'wp_ajax_load_json_commission', array( $this, 'load_json_commission' ) );
			add_action( 'wp_ajax_manual_transfer', array( $this, 'manual_transfer' ) );
			add_action( 'wp_ajax_print_commission', array( $this, 'print_commission' ) );

			/* === Create Commission from Receivers === */
			add_action( 'woocommerce_order_status_completed', array( $this, 'create_commission_from_order' ), 10, 1 );
			add_action( 'woocommerce_order_status_processing', array( $this, 'create_commission_from_order' ), 10, 1 );

		}

		/**
		 * Enqueue Script
		 *
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 * @since  1.0.0
		 */
		public function enqueue_scripts() {
			$debug_enabled   = defined( 'WP_DEBUG' ) ? WP_DEBUG : false;
			$prefix          = ! $debug_enabled ? '.min' : '';
			$js_dependencies = array(
				'jquery',
				'jquery-ui-sortable',
				'jquery-ui-datepicker',
				'backbone',
			);

			wp_register_script( 'yith-wc-backbone-modal', YITH_WCSC_ASSETS_URL . 'js/backbone-modal' . $prefix . '.js', array( 'wp-backbone' ), YITH_WCSC_VERSION );

			// My scripts and styles
			wp_register_style( 'yith-wcsc-commissions-style', YITH_WCSC_ASSETS_URL . 'css/style-yith-sc-commissions.css', null, YITH_WCSC_VERSION );
			wp_register_script( 'yith-wcsc-commissions-script', YITH_WCSC_ASSETS_URL . 'js/script-yith-sc-commissions' . $prefix . '.js', $js_dependencies, YITH_WCSC_VERSION, true );

			wp_localize_script( 'yith-wcsc-commissions-script', 'yith_wcsc_commissions', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'message' => array(
					'delay_time_confirm'                => __( 'There are still days left before the automatic payment. Do you want to do it
					now?', 'yith-stripe-connect-for-woocommerce' ),
					'cant_process_payment_order_status' => _x( "Can't process the payment with the current order status", 'Window message that can be
					displayed with manual payments', 'yith-stripe-connect-for-woocommerce' ),
					'disconnected_stripe_account'       => __( 'Users have disconnected their Stripe Account from your website!',
						'yith-stripe-connect-for-woocommerce' )
				)

			) );

			wp_enqueue_style( 'dashicons' );
			wp_enqueue_style( 'yith-wcsc-commissions-style' );
			wp_enqueue_script( 'yith-wc-backbone-modal' );
			wp_enqueue_script( 'yith-wcsc-commissions-script' );

		}

		/**
		 * Instance
		 *
		 * To use Singleton pattern design.
		 *
		 * @return null|YITH_Stripe_Connect_Commissions
		 * @since  1.0.0
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 *
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/** **** STANDARD CRUD METHODS **** **/

		/**
		 * Insert
		 *
		 * @param $commission
		 *
		 * @return false|int
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 * @since  1.0.0
		 *
		 */
		public function insert( $commission ) {
			global $wpdb;
			//*ID* | receiver_id | user_id | order_id | order_item_id | product_id | commission | commission_status | pay_in | purchased_date | status_receiver
			$inserted = $wpdb->insert(
				$wpdb->yith_wcsc_commissions,
				array(
					'receiver_id'       => ( isset( $commission['receiver_id'] ) ) ? $commission['receiver_id'] : '',
					'user_id'           => ( isset( $commission['user_id'] ) ) ? $commission['user_id'] : '',
					'order_id'          => ( isset( $commission['order_id'] ) ) ? $commission['order_id'] : '',
					'order_item_id'     => ( isset( $commission['order_item_id'] ) ) ? $commission['order_item_id'] : '',
					'product_id'        => ( isset( $commission['product_id'] ) ) ? $commission['product_id'] : '',
					'commission'        => ( isset( $commission['commission'] ) ) ? $commission['commission'] : '',
					'commission_status' => ( isset( $commission['commission_status'] ) ) ? $commission['commission_status'] : '',
					'commission_type'   => ( isset( $commission['commission_type'] ) ) ? $commission['commission_type'] : '',
					'commission_rate'   => ( isset( $commission['commission_rate'] ) ) ? $commission['commission_rate'] : '',
					'payment_retarded'  => ( isset( $commission['payment_retarded'] ) ) ? $commission['payment_retarded'] : '',
					'purchased_date'    => ( isset( $commission['purchased_date'] ) ) ? $commission['purchased_date'] : '',
					'note'              => ( isset( $commission['note'] ) ) ? $commission['note'] : '',
					'integration_item'  => ( isset( $commission['integration_item'] ) ) ? $commission['integration_item'] : '',
				)
			);

			if ( $inserted ) {
				$commission['ID'] = $wpdb->insert_id;

				/** APPLY_FILTERS: yith_wcsc_process_commission_after_created
				*
				* Filter the comissions just after creation.
				*
				* @param $commission Default commision obj.
				*/
				$commission = apply_filters( 'yith_wcsc_process_commission_after_created', $commission );

				/** DO_ACTION: yith_wcsc_after_commission_recorded
				*
				* Adds an action before record the comission.
				*
				* @param $commission Comission obj.
				*/
				do_action( 'yith_wcsc_after_commission_recorded', $commission );
			}

			return $inserted;
		}

		/**
		 * Update
		 *
		 * @param $id_commission
		 * @param $commissions
		 *
		 * @return false|int
		 * @since  1.0.0
		 *
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 */
		public function update( $id_commission, $commissions ) {
			global $wpdb;
			$updated = $wpdb->update(
				$wpdb->yith_wcsc_commissions,
				array(
					'receiver_id'       => ( isset( $commissions['receiver_id'] ) ) ? $commissions['receiver_id'] : '',
					'user_id'           => ( isset( $commissions['user_id'] ) ) ? $commissions['user_id'] : '',
					'order_id'          => ( isset( $commissions['order_id'] ) ) ? $commissions['order_id'] : '',
					'order_item_id'     => ( isset( $commissions['order_item_id'] ) ) ? $commissions['order_item_id'] : '',
					'product_id'        => ( isset( $commissions['product_id'] ) ) ? $commissions['product_id'] : '',
					'commission'        => ( isset( $commissions['commission'] ) ) ? $commissions['commission'] : '',
					'commission_status' => ( isset( $commissions['commission_status'] ) ) ? $commissions['commission_status'] : '',
					'commission_type'   => ( isset( $commissions['commission_type'] ) ) ? $commissions['commission_type'] : '',
					'commission_rate'   => ( isset( $commissions['commission_rate'] ) ) ? $commissions['commission_rate'] : '',
					'payment_retarded'  => ( isset( $commissions['payment_retarded'] ) ) ? $commissions['payment_retarded'] : '',
					'note'              => ( isset( $commissions['note'] ) ) ? $commissions['note'] : '',
					'integration_item'  => ( isset( $commissions['integration_item'] ) ) ? $commissions['integration_item'] : '',
				),
				array( 'ID' => $id_commission )
			);

			return $updated;
		}

		/**
		 * Delete
		 *
		 * @param $id_commission
		 *
		 * @return false|int
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 * @since  1.0.0
		 *
		 */
		public function delete( $id_commission ) {
			global $wpdb;

			$deleted = $wpdb->delete(
				$wpdb->yith_wcsc_commissions,
				array( 'ID' => $id_commission )
			);

			return $deleted;
		}

		/**
		 * Get simple commission
		 *
		 * @param $id_commission
		 *
		 * @return array|null|object|void
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 * @since  1.0.0
		 *
		 */
		public function get_commission( $id_commission ) {
			global $wpdb;
			$query  = $wpdb->prepare( "SELECT * FROM $wpdb->yith_wcsc_commissions WHERE ID = %d", $id_commission );
			$is_get = $wpdb->get_row(
				$query,
				OBJECT
			);

			return $is_get;
		}

		/** **** ADVANCED CRUD METHODS **** **/

		/**
		 * Update by user id
		 *
		 * @param $user_id
		 * @param $commissions
		 *
		 * @return false|int
		 * @since  1.0.0
		 *
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 */
		public function update_by_user_id( $user_id, $commissions ) {
			global $wpdb;

			$data = array();
			foreach ( $commissions as $key => $commission_column ) {
				$data[ $key ] = $commission_column;
			}

			$updated = $wpdb->update(
				$wpdb->yith_wcsc_commissions,
				$data,
				array( 'user_id' => $user_id )
			);

			return $updated;
		}

		/**
		 * Get Commission count
		 *
		 * Count all commissions created on the store.
		 *
		 * @param array $args
		 *
		 * @return null|string
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 * @since  1.0.0
		 *
		 */
		public function get_commissions_count( $args = array() ) {
			global $wpdb;
			$default_args = array(
				'user_id'    => '',
				'day'        => '',
				'month_year' => '',
			);
			$commissions  = wp_parse_args( $args, $default_args );

			$query     = "select count(ID) from  $wpdb->yith_wcsc_commissions";
			$query_arg = array();

			$where_query = $this->build_where_query( $query, $query_arg, $commissions );
			$query       = $where_query['where_query'];
			$query_arg   = $where_query ['where_query_args'];

			$prepared_query = ! empty( $query_arg ) ? $wpdb->prepare( $query, $query_arg ) : $query;

			$result = $wpdb->get_var( $prepared_query );

			return $result;
		}

		/**
		 * Get list commissions
		 *
		 * Retrieve a list commissions defined by args array. You can enable pagination with $paged value.
		 *
		 * @param array $args
		 * @param bool  $paged
		 *
		 * @return array|null|object
		 * @since  1.0.0
		 *
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 */
		public function get_commissions( $args = array(), $paged = false ) {
			global $wpdb;

			$items_per_page = '';
			$offset         = '';

			if ( $paged ) {
				$items_per_page = $this->items_per_page;
				$page           = isset( $_GET['current_page'] ) ? abs( (int) $_GET['current_page'] ) : 1;
				$offset         = ( $page * $items_per_page ) - $items_per_page;
			}

			$default_args = array(
				'receiver_id'       => '',
				'user_id'           => '',
				'order_id'          => '',
				'order_item_id'     => '',
				'product_id'        => '',
				'commission'        => '',
				'commission_status' => '',
				'commission_type'   => '',
				'commission_rate'   => '',
				'payment_retarded'  => '',
				'date_from'         => '',
				'date_to'           => '',
				'day'               => '',
				'month_year'        => '',
				'limit'             => $items_per_page,
				'offset'            => $offset
			);

			$commissions = wp_parse_args( $args, $default_args );
			$query_arg   = array();
			$query       = "SELECT * FROM $wpdb->yith_wcsc_commissions";

			$where_query = $this->build_where_query( $query, $query_arg, $commissions );
			$query       = $where_query['where_query'];
			$query_arg   = $where_query ['where_query_args'];

			if ( ! empty( $commissions['orderby'] ) ) {
				$query .= sprintf( ' ORDER BY %s %s', $commissions['orderby'], $commissions['order'] );
			}
			if ( ! empty ( $commissions['limit'] ) ) {
				$query .= sprintf( ' LIMIT %d, %d', ! empty( $commissions['offset'] ) ? $commissions['offset'] : 0, $commissions['limit'] );
			}


			$prepared_query = ! empty( $query_arg ) ? $wpdb->prepare( $query, $query_arg ) : $query;
			$res            = $wpdb->get_results( $prepared_query, ARRAY_A );

			return $res;
		}

		/**
		 * Create Commission from Receiver
		 *
		 * Create commissions from an order. Loops all order items and gets the receiver with this product assigned
		 *
		 * @param int $order_id Order_id.
		 *
		 * @since  1.0.0
		 *
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 */
		public function create_commission_from_order( $order_id ) {
			$order = wc_get_order( $order_id );

			/** APPLY_FILTERS: yith_wcsc_process_order_commissions
			*
			* Filter the possibility to disable the creation of comissions.
			*
			* @param bool      Bool.
			* @param $order_id Default order ID.
			*/
			if ( ! apply_filters( 'yith_wcsc_process_order_commissions', true, $order_id ) ) {
				return;
			}

			$pre_commissions_to_record = $this->retrieve_commissions_records( $order );

			if ( ! empty( $pre_commissions_to_record ) ) {
				$commissions_to_record = $this->check_commissions( $pre_commissions_to_record, $order );

				if ( is_wp_error( $commissions_to_record ) ) {
					$order->add_order_note( $commissions_to_record->get_error_message() );
					$this->stripe_connect_gateway->log( 'warning', $commissions_to_record->get_error_message() );
				} else {
					foreach ( $commissions_to_record as $commission_to_record ) {
						$inserted = $this->insert( $commission_to_record );

						if ( $inserted ) {
							$commission_text      = $commission_to_record['commission'] . get_woocommerce_currency_symbol();
							$commission_type_text = ( 'percentage' == $commission_to_record['commission_type'] ) ? $commission_to_record['commission_rate'] . '%' : __( 'fixed commission', 'yith-stripe-connect-for-woocommerce' );
							$user                 = get_userdata( $commission_to_record['user_id'] );

							// translators: 1. Commission amount 2. Commission type 3. Receiver email.
							$message = sprintf( __( 'A commission with %1$s (from %2$s) has been created for %3$s', 'yith-stripe-connect-for-woocommerce' ), $commission_text, $commission_type_text, $user->display_name );

							$order->add_order_note( $message );
							$this->stripe_connect_gateway->log( 'info', $message );
						}
					}
				}
			}
		}

		/**
		 * Retrieve receivers for a specific order
		 *
		 * @param int $order_id Order id.
		 * @return array Array or receivers, as read on db
		 */
		public function retrieve_receivers_from_order( $order_id ) {
			$order = wc_get_order( $order_id );

			$receivers = $this->retrieve_receivers_from_object( $order );

			return $receivers;
		}

		/**
		 * Retrieves receivers for an order or a cart
		 *
		 * @param \WC_Cart|\WC_Order $object Order or cart object.
		 * @return array Array of receivers
		 */
		public function retrieve_receivers_from_object( $object ) {
			if ( ! $object instanceof WC_Order && ! $object instanceof WC_Cart ) {
				return array();
			}

			$receivers = array();
			$stripe_connect_receivers = YITH_Stripe_Connect_Receivers::instance();

			if ( $object instanceof WC_Order ) {
				$order = $object;
				$order_id = $object->get_id();
				$order_items = $order->get_items();

				$cache_key    = "order_{$order_id}_receivers";
				$cached_value = wp_cache_get( $cache_key, 'yith_wcsc' );

				if ( ! $cached_value ) {
					foreach ( $order_items as $order_item ) {
						/**
						 * @var \WC_Order_Item_Product $order_item
						 */

						// Check if the order items its free...
						$free_order_item = yith_wcsc_check_free_order_item( $order_item );

						if ( $free_order_item ) {
							continue;
						}

						$product_id       = $order_item->get_product_id();
						$variation_id     = $order_item->get_variation_id();
						$receivers_result = [];

						/** APPLY_FILTERS: yith_wcsc_process_product_commissions
						*
						* Filter the process when creating the comissions.
						*
						* @param bool              Bool.
						* @param float $product_id Default product ID.
						* @param obj $order_item   Default order item.
						* @param float $order_id   Default order ID.
						*/
						if ( ! apply_filters( 'yith_wcsc_process_product_commissions', true, $product_id, $order_item, $order_id ) ) {
							continue;
						}

						// first search for current variation, if any.

						/** APPLY_FILTERS: yith_wcsc_process_product_commissions
						*
						* Filter the process when creating the comissions.
						*
						* @param bool              Bool.
						* @param float $product_id Default product ID.
						* @param obj $order_item   Default order item.
						* @param float $order_id   Default order ID.
						*/
						if ( $variation_id && apply_filters( 'yith_wcsc_process_product_commissions', true, $variation_id, $order_item, $order_id ) ) {
							$products_id = array(
								$product_id,
								$variation_id,
							);

							$receiver_data = array(
								'product_id'   => $products_id,
								'all_products' => '1', // We will ask for Receivers with all products too.
								'disabled'     => '1', // We filter to avoid get disabled receivers.
							);

							// Gets all receivers that have been added for specific products...

							/** APPLY_FILTERS: yith_wcsc_receivers_result
							*
							* Filter the result when getting the receiver on comissions creation.
							*
							* @param $variation_id Default variation ID.
							* @param $receiver     Default receiver obj.
							*/
							$receivers_result = apply_filters( 'yith_wcsc_receivers_result', $stripe_connect_receivers->get_receivers( $receiver_data ), $variation_id );
						}

						// then search for general product
						if ( empty( $receivers_result ) ) {
							$products_id = array(
								$product_id,
								$variation_id,
							);

							$receiver_data = array(
								'product_id'   => $products_id,
								'all_products' => '1', // We will ask for Receivers with all products too.
								'disabled'     => '1', // We filter to avoid get disabled receivers.
							);

							// Gets all receivers that have been added for specific products...

							/** APPLY_FILTERS: yith_wcsc_receivers_result
							*
							* Filter the result when getting the receiver on comissions creation.
							*
							* @param $variation_id Default variation ID.
							* @param $receiver     Default receiver obj.
							*/
							$receivers_result = apply_filters( 'yith_wcsc_receivers_result', $stripe_connect_receivers->get_receivers( $receiver_data ), $product_id );
						}

						if ( ! empty( $receivers_result ) ) {
							foreach ( $receivers_result as & $receiver_result ) {
								$receiver_result['product_id'] = $product_id;
								$receiver_result['item'] = $order_item;
							}
						}

						$receivers = array_merge( $receivers, $receivers_result );
					}

					wp_cache_set( $cache_key, $receivers, 'yith_wcsc' );
				} else {
					$receivers = $cached_value;
				}
			} elseif ( $object instanceof WC_Cart ) {
				$cart = $object;
				$cart_hash = $cart->get_cart_hash();
				$cart_contents = $cart->get_cart_contents();

				$cache_key    = "cart_{$cart_hash}_receivers";
				$cached_value = wp_cache_get( $cache_key, 'yith_wcsc' );

				if ( ! $cached_value ) {
					foreach ( $cart_contents as $cart_item ) {
						/**
						 * @var \WC_Order_Item_Product $order_item
						 */

						// Check if the order items its free...
						$free_order_item = yith_wcsc_check_free_order_item( $cart_item );

						if ( $free_order_item ) {
							continue;
						}

						$variation_id = ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $cart_item['product_id'];
						$product_id   = ! empty( $cart_item['product_id'] ) ? $cart_item['product_id'] : 0;

						$product_ids = array(
							$product_id,
							$variation_id,
						);

						/** APPLY_FILTERS: yith_wcsc_process_cart_item_commissions
						*
						* Filter the comissions based on each cart item.
						*
						* @param bool        Bool.
						* @param $product_id Default porduct ID.
						* @param $cart_item  Default cart item obj.
						* @param $cart_hash  Default hash.
						*/
						if ( ! apply_filters( 'yith_wcsc_process_cart_item_commissions', true, $product_id, $cart_item, $cart_hash ) ) {
							continue;
						}

						$receiver_data = array(
							'product_id'   => $product_ids,
							'all_products' => '1', // We will ask for Receivers with all products too.
							'disabled'     => '1', // We filter to avoid get disabled receivers.
						);

						// Gets all receivers that have been added for specific products...
						$receivers_result = $stripe_connect_receivers->get_receivers( $receiver_data );

						if ( ! empty( $receivers_result ) ) {
							foreach ( $receivers_result as & $receiver_result ) {
								$receiver_result['product_id'] = $product_id;
								$receiver_result['item']       = $cart_item;
							}

							$receivers = array_merge( $receivers, $receivers_result );
						}
					}

					wp_cache_set( $cache_key, $receivers, 'yith_wcsc' );
				} else {
					$receivers = $cached_value;
				}
			}

			return $receivers;
		}

		/**
		 * Create commission records to enter in db for a specific order
		 *
		 * @param \WC_Order|\WC_Cart $object Order or cart.
		 * @return array Array of commission records.
		 */
		public function retrieve_commissions_records( $object ) {
			$order_receivers           = $this->retrieve_receivers_from_object( $object );
			$pre_commissions_to_record = array();

			if ( ! empty( $order_receivers ) ) {
				foreach ( $order_receivers as $key => $receiver_result ) {
					$record = $this->retrieve_commissions_record( $receiver_result, $object );

					if ( $record ) {
						$pre_commissions_to_record[] = $record;
					}
				}
			}

			return $pre_commissions_to_record;
		}

		/**
		 * Retrieve records to register in commissions table for a specific receiver
		 *
		 * @param array              $receiver Array of parameters for the receiver.
		 * @param \WC_Order|\WC_cart $object Object related to the receiver (cart or order).
		 *
		 * @return array Array representing the commission record.
		 */
		public function retrieve_commissions_record( $receiver, $object ) {
			$stripe_connect_settings = get_option( 'woocommerce_yith-stripe-connect_settings' );
			$payment_retarded        = ! empty( $stripe_connect_settings['payment-delay'] ) ? $stripe_connect_settings['payment-delay'] : 0;
			$date_time               = gmdate( 'Y-m-d H:i:s' );
			$record                  = false;
			$receivers               = $this->retrieve_receivers_from_object( $object );

			// We only creates commission for users that have their Stripe Connect accounts connected with our application.
			$create_commission = ( 'connect' == $receiver['status_receiver'] ) ? true : false;

			/** APPLY_FILTERS: yith_wcsc_create_commission
			*
			* Filter to third parties if wants force the commission creation.
			*
			* @param $create_commission Bool.
			* @param $receivers         Default $receivers.
			* @param $receiver['item']  Default $receivers item.
			* @param $receiver          Array of parameters for the receiver.
			* @param $object            Object related to the receiver (cart or order).
			*/
			$create_commission = apply_filters( 'yith_wcsc_create_commission', $create_commission, $receivers, $receiver['item'], $receiver, $object );
			if ( $create_commission ) {
				// Prepare the commission to record...
				// I keep the commission type and commission rate from current receiver. This its because receiver data could be changes and the commission should be have the data from current receiver.
				$commission = $this->calculate_commission( $receiver['commission_value'], $receiver['commission_type'], $object, $receiver['item'] );

				$record = array_merge(
					array(
						'receiver_id'       => $receiver['ID'],
						'user_id'           => $receiver['user_id'],
						'product_id'        => $receiver['product_id'],

						/** APPLY_FILTERS: yith_wcsc_commission_value
						*
						* Filter to value of the comission.
						*
						* @param $commission       Default comission.
						* @param $receiver         Array of parameters for the receiver.
						* @param $object           Object related to the receiver (cart or order).
						* @param $receiver['item'] Default $receivers item.
						*/
						'commission'        => apply_filters( 'yith_wcsc_commission_value', $commission, $receiver, $object, $receiver['item'] ),
						'commission_status' => 'sc_pending',
						'commission_type'   => $receiver['commission_type'],
						'commission_rate'   => $receiver['commission_value'],
						'payment_retarded'  => $payment_retarded,
						'purchased_date'    => $date_time,
					),
					method_exists( $object, 'get_id' ) ? array( 'order_id' => $object->get_id() ) : array(),
					is_object( $receiver['item'] ) && method_exists( $receiver['item'], 'get_id' ) ? array( 'order_item_id' => $receiver['item']->get_id() ) : array()
				);
			}

			return $record;
		}

		/**  **** UTILS FOR CRUD METHODS **** **/

		/**
		 * Some methods used the same where query structure, for this reason I grouped on one method.
		 * WHERE clauses for...:
		 * AND: user_id, order_id, order_item_id, product_id, purchased_date (with formated day and month_year)
		 *
		 * @param $query
		 * @param $query_arg
		 * @param $commissions
		 *
		 * @return array
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 * @since  1.0.0
		 *
		 */
		private function build_where_query( $query, $query_arg, $commissions ) {
			$query .= ' WHERE 1=1';

			if ( ! empty( $commissions['receiver_id'] ) ) {
				$query       .= ' AND receiver_id = %d';
				$query_arg[] = $commissions['receiver_id'];
			}
			if ( ! empty( $commissions['user_id'] ) ) {
				$query       .= ' AND user_id = %d';
				$query_arg[] = $commissions['user_id'];
			}
			if ( ! empty( $commissions['order_id'] ) ) {
				$query       .= ' AND order_id = %d';
				$query_arg[] = $commissions['order_id'];
			}
			if ( ! empty( $commissions['order_item_id'] ) ) {
				$query       .= ' AND order_item_id = %d';
				$query_arg[] = $commissions['order_item_id'];
			}
			if ( ! empty( $commissions['product_id'] ) ) {
				$query       .= ' AND product_id = %d';
				$query_arg[] = $commissions['product_id'];
			}
			if ( ! empty( $commissions['commission_status'] ) ) {
				$query       .= ' AND commission_status LIKE %s';
				$query_arg[] = $commissions['commission_status'];
			}

			if ( ! empty( $commissions['date_from'] ) & ! empty( $commissions['date_to'] ) ) {
				$date_from = strtotime( $commissions['date_from'] );
				$date_to   = strtotime( $commissions['date_to'] );

				$date_from_parsed = date_i18n( 'Y-m-d', $date_from );
				$date_to_parsed   = date_i18n( 'Y-m-d', $date_to );

				$query       .= ' AND (date(purchased_date) BETWEEN %s AND %s)';
				$query_arg[] = $date_from_parsed;
				$query_arg[] = $date_to_parsed;

			} elseif ( ! empty( $commissions['date_from'] ) ) {
				$date_from        = strtotime( $commissions['date_from'] );
				$date_from_parsed = date_i18n( 'Y-m-d', $date_from );

				$query       .= ' AND (date(purchased_date) BETWEEN %s AND CURDATE())';
				$query_arg[] = $date_from_parsed;

			} elseif ( ! empty( $commissions['date_to'] ) ) {
				$date_to        = strtotime( $commissions['date_to'] );
				$date_to_parsed = date_i18n( 'Y-m-d', $date_to );

				$query       .= ' AND (date(purchased_date) <= %s )';
				$query_arg[] = $date_to_parsed;
			}

			// Check that day and month/year are filled...
			if ( ! empty( $commissions['day'] ) & ! empty( $commissions['month_year'] ) ) {
				$raw_date    = strtotime( $commissions['day'] . '-' . $commissions['month_year'] ); // Unify day with month/year
				$date_parsed = date_i18n( 'Y-m-d', $raw_date ); // Parse on formatted date

				$query       .= ' AND date(purchased_date) like %s'; //Compare with date MySql method, only extract the date y-m-d
				$query_arg[] = $date_parsed;

			} elseif ( ! empty ( $commissions['day'] ) ) { // Checks that only day is filled...
				$query       .= ' AND day(purchased_date) = %d'; // Compare with day MySql method, only extract the day...
				$query_arg[] = $commissions['day'];

			} elseif ( ! empty( $commissions['month_year'] ) ) { // Checks that only month/year is filled
				$raw_month_year    = strtotime( '01-' . $commissions['month_year'] ); // Parsing to a right date to make the comparisons...
				$month_year_parsed = date_i18n( 'Y-m-d', $raw_month_year );
				$array_date        = date_parse( $month_year_parsed ); // Get associative array from standard date, with this way we can get the year and month.

				$query       .= ' AND month(purchased_date) = %d AND year(purchased_date) = %d'; // Compare with month and year MySQL method...
				$query_arg[] = $array_date['month'];
				$query_arg[] = $array_date['year'];
			}

			return array( 'where_query' => $query, 'where_query_args' => $query_arg );
		}

		/**  **** DEFAULTS METHODS FOR COMMISSIONS **** **/

		/**
		 * Process transfer
		 *
		 * @param $commission
		 *
		 * @return array|\Stripe\Transfer|WP_Error
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 * @since  1.0.0
		 *
		 */
		public function process_transfer( $commission ) {
			if ( $commission instanceof stdClass ) {
				$commission = (array) $commission;
			}

			/**
			 * We avoid duplicated payments checking before that the current commission had been payed...
			 */
			if ( 'sc_transfer_success' == $commission['commission_status'] ) {
				return new WP_Error( 'commission_payed', __( 'The current commissions have been paid', 'yith-stripe-connect-for-woocommerce' ) );
			}

			$status_receiver = yith_wcsc_get_stripe_user_status( $commission['user_id'] );
			$order           = wc_get_order( $commission['order_id'] );
			$status_order    = yit_get_prop( $order, 'status' );

			/**
			 * Before to proceed with the payment to Stripe Connect.
			 */

			/**
			 * Checks their Stripe Account connected with us. Their Stripe ID is must required to proceed with the transfer.
			 */
			if ( 'disconnect' == $status_receiver ) {
				return new WP_Error( 'stripe_account_disconnected', __( "The user's Stripe account has been disconnected",
					'yith-stripe-connect-for-woocommerce' ) );
			}
			/**
			 * We checks that order still be Completed, processing. Probably for example the order has been refunded for example.
			 */
			if ( 'completed' == $status_order || 'processing' == $status_order ) {
				$stripe_receiver = get_user_meta( $commission['user_id'], 'stripe_user_id', true );

				/** APPLY_FILTERS: yith_wcstripe_connect_transfer_args
				*
				* Prepare Transfer args... 'amount' 'currency' and 'destination' are required.
				*
				* @param array       Default args.
				* @param $commission Default comission.
				* @param $order      Object of the order.
				*/
				$args = apply_filters( 'yith_wcstripe_connect_transfer_args', array(
					'amount'         => yith_wcsc_get_amount( $commission['commission'] ),
					'currency'       => yit_get_prop( $order, 'currency' ),
					'destination'    => $stripe_receiver,

					/** APPLY_FILTERS: yith_wcstripe_connect_transfer_metadata
					*
					* Filter the default metadata sent to Stripe.
					*
					* @param array       Default args.
					* @param string      'comission' by default.
					* @param $commission Default comission.
					* @param $order      Object of the order.
					*/
					'metadata'       => apply_filters( 'yith_wcstripe_connect_transfer_metadata', array(), 'commission', $commission, $order ),
					'transfer_group' => $order->get_id()
				), $commission, $order );

				$transaction_id = $order->get_transaction_id();

				if ( ( $transaction_id && YITH_Stripe_Connect::$gateway_id === $order->get_payment_method() ) || ( $transaction_id && apply_filters( 'yith_wcsc_force_transaction_id', false ) ) ) {
					$args['source_transaction'] = $transaction_id;
				}

				// We use our api_handler controller to create the transfer.
				$transfer = $this->api_handler->create_transfer( $args );

				// Now we handle the $transfer returns...
				$notes = array();
				$order = wc_get_order( $commission['order_id'] );
				$user  = get_userdata( $commission['user_id'] );

				if ( isset( $transfer['error_transfer'] ) ) {
					// Prepare message
					$error_message = sprintf( __( 'Can\'t transfer the commissions %s for %s. Please take a look at the log file for more details.',
						'yith-stripe-connect-for-woocommerce' ), $commission['commission'] . get_woocommerce_currency_symbol(), $user->display_name );

					// Display messages on order note and log file
					$this->stripe_connect_gateway->log( 'error', $error_message . __( 'Stripe Connect message: ', 'yith-stripe-connect-for-woocommerce' ) . $transfer['error_transfer'] );
					$order->add_order_note( $error_message );

					// Prepare the notes to commission
					$notes['error_transfer'] = $error_message;

					// Update Commission
					$commission['commission_status'] = 'sc_transfer_error';
					$commission['note']              = maybe_serialize( $notes );
					$this->update( $commission['ID'], $commission );

					return new WP_Error( 'error_transfer', $error_message );

				} elseif ( $transfer instanceof \Stripe\Transfer ) {
					// Prepare message
					$success_message = sprintf( __( 'Commissions %s for %s have been transferred correctly. Transfer ID: "%s". Destination Payment:
					"%s".', 'yith-stripe-connect-for-woocommerce' ), $commission['commission'] . get_woocommerce_currency_symbol(), $user->display_name, $transfer->id, $transfer->destination_payment );

					// Display messages on order note and log file
					$this->stripe_connect_gateway->log( 'info', $success_message );
					$order->add_order_note( $success_message );

					// Prepare the notes to commssion
					$notes['transfer_id']         = $transfer->id;
					$notes['destination_payment'] = $transfer->destination_payment;

					// Update Commission
					$commission['commission_status'] = 'sc_transfer_success';
					$commission['note']              = maybe_serialize( $notes );

					$this->update( $commission['ID'], $commission );

					// try to set transfer metadata.
					$destination_payment = ! empty( $transfer->destination_payment ) ? $transfer->destination_payment : false;

					if ( $destination_payment ) {
						$this->api_handler->update_charge(
							$destination_payment,

							/** APPLY_FILTERS: yith_wcstripe_update_charge_params
							*
							* Filter the default metadata sent to Stripe (charges).
							*
							* @param array       Default metadata structure.
							* @param string      'update_destination_payment' by default.
							* @param $commission Default comission.
							* @param $order      Order.
							*/
							apply_filters(
								'yith_wcstripe_update_charge_params',
								array(

									/** APPLY_FILTERS: yith_wcstripe_connect_metadata
									*
									* Filter metadata sent to Stripe.
									*
									* @param array $order_id , $instance.
									* @param string 'update_destination_payment' by default.
									*/
									'metadata' => apply_filters(
										'yith_wcstripe_connect_metadata',
										array(
											'instance' => preg_replace( '/http(s)?:\/\//', '', site_url() ),
											'order_id' => $order->get_id(),
										),
										'update_destination_payment'
									),
								),
								'update_destination_payment',
								$commission,
								$order
							),
							array(
								'stripe_account' => $stripe_receiver,
							)
						);
					}

					return $transfer;
				}
			}
		}

		/**
		 * @param string $id_commission
		 *
		 * @since  1.0.0
		 *
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 */
		public function manual_transfer( $id_commission = '' ) {
			$id_commission = isset( $_POST['id_commission'] ) ? $_POST['id_commission'] : $id_commission;

			if ( ! empty( $id_commission ) & is_admin() ) {
				$commission = $this->get_commission( $id_commission );
				$this->process_transfer( $commission );

				$this->load_json_commission( $id_commission );
			}

		}

		/**
		 * Calculate commission
		 *
		 * @param $commission_value
		 * @param $commission_type
		 * @param $order
		 * @param $order_item
		 *
		 * @return mixed|void
		 * @since  1.0.0
		 *
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 */
		public function calculate_commission( $commission_value, $commission_type, $object, $item ) {
			$commission = array(
				'qty'                 => 0,
				'commission_per_item' => 0,
				'result'              => 0,
			);

			$item_qty   = $item['quantity'];
			$item_total = 0;

			if ( is_object( $item ) && method_exists( $item, 'get_total' ) ) {
				$item_total = $item->get_total();
			} elseif ( isset( $item['line_total'] ) ) {
				$item_total = $item['line_total'];
			}

			if ( 'percentage' == $commission_type ) {

				/** APPLY_FILTERS: yith_wcsc_add_tax_to_commission
				*
				* Filter the comissions to add tax to them.
				*
				* @param $item_total Total amount of the item.
				* @param $object     The order.
				* @param $item       Item of the order.
				*/
				$total_item            = apply_filters( 'yith_wcsc_add_tax_to_commission', $item_total, $object, $item );
				$commission_value      = ( 100 > $commission_value ) ? $commission_value : 100; // Checks before that our commission value don´t be higher than 100.
				$commission_percentage = ( $total_item * $commission_value ) / 100;
				$commission            = $commission_percentage;

			} elseif ( 'fixed' == $commission_type ) {
				$commission = $commission_value * $item_qty;
			}

			/** APPLY_FILTERS: yith_wcsc_calculate_commission
			*
			* Filter the return when generating the comissions.
			*
			* @param $commission       Percentage of the comission.
			* @param $commission_value Value of the comission (not higher than 100).
			* @param $commission_type  Type of the comission.
			* @param $item             Item of the order.
			*/
			return apply_filters( 'yith_wcsc_calculate_commission', $commission, $commission_value, $commission_type, $item );
		}

		/**
		 * Calculate commissions total
		 *
		 * Loops all commission from each commission table and return their total sum.
		 *
		 * @param $commissions
		 *
		 * @return float|int
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 * @since  1.0.0
		 *
		 */
		public function calculate_commissions_total( $commissions ) {
			return array_sum( wp_list_pluck( $commissions, 'commission' ) );
		}

		/** **** CHECKS METHODS FOR COMMISSIONS **** */

		/**
		 *    Search all Commissions with sc_transfer_processing status this status are defined when we try to process the transfer and something happens on the process (Disconnected account for example)
		 *
		 * @param string $user_id
		 *
		 * @since  1.0.0
		 *
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 */
		public function check_commissions_status_by_user_id( $user_id = '' ) {
			$args        = array(
				'user_id'           => $user_id,
				'commission_status' => 'sc_transfer_processing'
			);
			$commissions = $this->get_commissions( $args );
			foreach ( $commissions as $commission ) {
				$this->process_transfer( $commission );
			}
		}

		/**
		 * Check Commissions
		 *
		 * Checks the commission with order total and that not been created.
		 *
		 * @param array     $commissions Array of commissions to register.
		 * @param \WC_order $order Order that generated commissions.
		 *
		 * @return array|WP_Error
		 * @since  1.0.0
		 *
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 */
		public function check_commissions( $commissions, $order ) {
			/*
			 * First checks that Commissions Total not exceed the order total...
			 */
			$commissions_total = $this->calculate_commissions_total( $commissions );

			/** APPLY_FILTERS: yith_wcsc_order_total_with_tax
			*
			* Filter the order total when checking for comissions.
			*
			* @param $subtotal subtotal of the order.
			* @param $order    Order obj.
			*/
			$order_total          = apply_filters( 'yith_wcsc_order_total_with_tax', yit_get_prop( $order, 'subtotal' ), $order );
			$commissions_exceeded = $this->stripe_connect_gateway->get_option( 'commissions-exceeded' ); // Get option that disable the commission exceeded restriction.

			if ( $commissions_total > $order_total && 'yes' != $commissions_exceeded ) {
				return new WP_Error( 'commissions exceeded', __( 'Couldn\'t create commission because it exceeded the order total', 'yith-stripe-connect-for-woocommerce' ) );
			}

			// Now Loops commission to verify if have not been created on Commissions table.
			$returned_commissions = array();
			foreach ( $commissions as $commission ) {
				$exist_commission = $this->check_exist_commission( $commission );
				if ( ! $exist_commission ) {
					$returned_commissions [] = $commission;
				}
			}

			// Alter commission status.
			if ( $order->get_meta( '_yith_wcstripe_alt_flow', true ) ) {
				foreach ( $returned_commissions as & $commission ) {
					$notes = array(
						__( 'N/A; This commission was processed using Alternative Payment Flow', 'yith-stripe-connect-for-woocommerce' ), // transfer id
					);

					$commission['commission_status'] = 'sc_transfer_success';
					$commission['note'] = serialize( $notes );
				}
			}

			return $returned_commissions;
		}

		/**
		 * Check exist Commission
		 *
		 * @param $commission
		 *
		 * @return bool
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 * @since  1.0.0
		 *
		 */
		public function check_exist_commission( $commission ) {
			$stripe_connect_commissions = YITH_Stripe_Connect_Commissions::instance();

			/** APPLY_FILTERS: yith_wcsc_check_exist_commission_args
			*
			* Filter the comission data when checking for existing comissions.
			*
			* @param array       Comission array data.
			* @param $commission Comission obj.
			*/
			$args        = apply_filters(
				'yith_wcsc_check_exist_commission_args',
				array(
					'receiver_id'   => $commission['receiver_id'],
					'user_id'       => $commission['user_id'],
					'order_id'      => $commission['order_id'],
					'order_item_id' => $commission['order_item_id'],
					'product_id'    => $commission['product_id'],
				),
				$commission
			);
			$commissions = $stripe_connect_commissions->get_commissions( $args );

			if ( empty( $commissions ) ) {
				$exist = false;
			} else {
				$exist = true;
			}

			return $exist;
		}

		/** **** UTILS METHOD FOR COMMISSIONS **** */

		/**
		 * Load json commission
		 *
		 * Sends a Commission JSon formatted data.
		 *
		 * @param string $id_commission
		 *
		 * @since  1.0.0
		 *
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 */
		public function load_json_commission( $id_commission = '' ) {
			$id_commission = isset( $_POST['id_commission'] ) ? $_POST['id_commission'] : $id_commission;
			if ( ! empty( $id_commission ) ) {
				$commission = (array) $this->get_commission( $id_commission );

				// We get the commission with args prepared.
				$commission = yith_wcsc_prepare_commission_args( $commission );

				wp_send_json( $commission );
				die();
			}
		}

		/**
		 * Export to CSV
		 *
		 * Export the current commissions table results to CSV file.
		 *
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 * @since  1.0.0
		 */
		public function export_csv() {

			$header = $this->get_csv_headers();

			$name = uniqid( 'commissions_' );
			ignore_user_abort( true );
			nocache_headers();
			header( 'Content-Description: File Transfer' );
			header( 'Content-Disposition: attachment; filename=' . str_replace( ' ', '_', $name . '.csv' ) ); //@since 1.0.0
			header( 'Content-Type: text/csv; charset=utf-8' );

			$df = fopen( 'php://output', 'w' );

			fputcsv( $df, $header );

			$commissions_args = array(
				'product_id' => isset( $_GET['yith_wcs_product'] ) ? $_GET['yith_wcs_product'] : '',
				'user_id'    => isset( $_GET['yith_wcsc_admin_commission_section'] ) ? ( isset( $_GET['yith_wcs_user'] ) ? $_GET['yith_wcs_user'] : '' ) : get_current_user_id(),
				'day'        => isset( $_GET['yith_wcsc_day'] ) ? $_GET['yith_wcsc_day'] : '',
				'month_year' => isset( $_GET['yith_wcsc_month_year'] ) ? $_GET['yith_wcsc_month_year'] : '',
				'orderby'    => isset( $_GET['yith_wcsc_orderby'] ) ? $_GET['yith_wcsc_orderby'] : 'ID',
				'order'      => isset( $_GET['yith_wcsc_order'] ) ? $_GET['yith_wcsc_order'] : 'DESC'
			);

			$commissions = $this->get_commissions( $commissions_args );

			foreach ( $commissions as $commission ) {

				$prepared_commission = yith_wcsc_prepare_commission_args( $commission ); // Get the commission with a standar texts and format...

				// Prepare each cell items for our csv file...
				$cell_commission        = sprintf( '#%d %s', $commission['ID'], $prepared_commission['display_name'] );
				$cell_product           = sprintf( '%s x %d', $prepared_commission['product_title'], $prepared_commission['product_qty'] );
				$cell_total             = sprintf( '%s', html_entity_decode( $prepared_commission['commission_total'] ) );
				$cell_details           = sprintf( '%s', html_entity_decode( $prepared_commission['commission_text_detail'] ) );
				$cell_order             = sprintf( '#%s', html_entity_decode( $prepared_commission['order_id'] ) );
				$cell_purchased         = sprintf( '%s', html_entity_decode( $prepared_commission['purchased_date'] ) );
				$cell_status_commission = sprintf( '%s', html_entity_decode( $prepared_commission['commission_status_text'] ) );
				$cell_status_receiver   = sprintf( '%s', html_entity_decode( $prepared_commission['receiver_status'] ) );
				$cell_note              = sprintf( '%s', html_entity_decode( $prepared_commission['note'] ) );

				/** APPLY_FILTERS: yith_wcsc_prepare_rows_list_for_csv
				*
				* Filter all the data before creating the CSV file when exporting.
				*
				* @param array                Data in each row.
				* @param $prepared_commission Get the commission with a standard texts and format.
				*/
				$commission_row = apply_filters( 'yith_wcsc_prepare_rows_list_for_csv', array(
					$cell_commission,
					$cell_product,
					$cell_total,
					$cell_details,
					$cell_order,
					$cell_purchased,
					$cell_status_commission,
					$cell_status_receiver,
					$cell_note
				), $prepared_commission );

				/** APPLY_FILTERS: yith_wcaf_commissions_csv_row
				*
				* Filter all the row before creating the CSV file when exporting.
				*
				* @param $commission_row All the data of the entire row.
				* @param $header         Header of the document.
				*/
				fputcsv( $df, apply_filters( 'yith_wcaf_commissions_csv_row', $commission_row, $header ) );

			}

			fclose( $df );

			die();

		}

		/**
		 * Export to PDF
		 *
		 * Export the current commission table results to PDF file like a table.
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 * @since  1.0.0
		 */
		public function export_pdf() {
			$list = $this->get_csv_list();

			$rows = array();
			foreach ( $list['rows'] as $row ) {
				$rows[] = explode( ',', $row );
			}

			$args = array(
				'columns' => explode( ',', $list['columns'] ),
				'rows'    => $rows
			);

			ob_start();
			yith_wcsc_get_template( 'commission-table', $args, 'common' );

			$html = ob_get_clean();

			$mpdf = new \Mpdf\Mpdf();
			$mpdf->WriteHTML( $html, 2 );

			$mpdf->Output();

			exit;

		}

		/**
		 * Print Commission
		 *
		 * Print a Commission view to PDF
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 * @since  1.0.0
		 */
		public function print_commission() {
			$commission_id = isset( $_GET['id_commission'] ) ? $_GET['id_commission'] : '';

			if ( ! empty( $commission_id ) ) {
				$commission = $this->get_commission( $commission_id );
				$commission = yith_wcsc_prepare_commission_args( $commission );

				ob_start();
				yith_wcsc_get_template( 'commission-css-pdf', array(), 'common' );
				$css = ob_get_clean();

				ob_start();
				yith_wcsc_get_template( 'commission-pdf', $commission, 'common' );

				$html = ob_get_clean();

				$mpdf = new \Mpdf\Mpdf();
				$mpdf->WriteHTML( $css, 1 );
				$mpdf->WriteHTML( $html, 2 );
				$mpdf->Output();

				exit;
			}
			exit;
		}

		/**
		 * Get CSV List
		 *
		 * Get the commissions results ready to can be exported to CSV
		 *
		 * @return array
		 * @since  1.0.0
		 *
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 */
		public function get_csv_list() {
			$commissions_args = array(
				'product_id' => isset( $_GET['yith_wcs_product'] ) ? $_GET['yith_wcs_product'] : '',
				'user_id'    => isset( $_GET['yith_wcsc_admin_commission_section'] ) ? '' : get_current_user_id(),
				'day'        => isset( $_GET['yith_wcsc_day'] ) ? $_GET['yith_wcsc_day'] : '',
				'month_year' => isset( $_GET['yith_wcsc_month_year'] ) ? $_GET['yith_wcsc_month_year'] : '',
				'orderby'    => isset( $_GET['yith_wcsc_orderby'] ) ? $_GET['yith_wcsc_orderby'] : 'ID',
				'order'      => isset( $_GET['yith_wcsc_order'] ) ? $_GET['yith_wcsc_order'] : 'DESC'
			);

			$commissions = $this->get_commissions( $commissions_args );
			// Prepare the columns
			$column_commission        = __( 'Commission', 'yith-stripe-connect-for-woocommerce' );
			$column_product           = __( 'Product', 'yith-stripe-connect-for-woocommerce' );
			$column_total             = __( 'Total', 'yith-stripe-connect-for-woocommerce' );
			$column_details           = __( 'Details', 'yith-stripe-connect-for-woocommerce' );
			$column_order             = __( 'Order', 'yith-stripe-connect-for-woocommerce' );
			$column_purchased         = __( 'Purchased date', 'yith-stripe-connect-for-woocommerce' );
			$column_status_commission = __( 'Status', 'yith-stripe-connect-for-woocommerce' );
			$column_status_receiver   = __( 'Receiver status', 'yith-stripe-connect-for-woocommerce' );
			$column_note              = __( 'Notes', 'yith-stripe-connect-for-woocommerce' );

			/** APPLY_FILTERS: yith_wcsc_prepare_columns_list
			*
			* Filter the list before creating the CSV file when exporting the comission table.
			*
			* @param array All the data of the entire list ( columns & rows ).
			*/
			$list = apply_filters( 'yith_wcsc_prepare_columns_list', array(
				'columns' => sprintf( '%s,%s,%s,%s,%s,%s,%s,%s,%s', $column_commission, $column_product, $column_total, $column_details, $column_order, $column_purchased, $column_status_commission, $column_status_receiver, $column_note ),
				'rows'    => array()
			) );
			// For each commission we prepare one row...
			foreach ( $commissions as $commission ) {
				$prepared_commission = yith_wcsc_prepare_commission_args( $commission ); // Get the commission with a standar texts and format...
				$row                 = '';

				if ( ! is_array( $prepared_commission ) ) {
					continue;
				}

				// Prepare each cell items for our csv file...
				$cell_commission        = sprintf( '#%d %s', $commission['ID'], $prepared_commission['display_name'] );
				$cell_product           = sprintf( '%s x %d', $prepared_commission['product_title'], $prepared_commission['product_qty'] );
				$cell_total             = sprintf( '%s', html_entity_decode( $prepared_commission['commission_total'] ) );
				$cell_details           = sprintf( '%s', html_entity_decode( $prepared_commission['commission_text_detail'] ) );
				$cell_order             = sprintf( '#%s', html_entity_decode( $prepared_commission['order_id'] ) );
				$cell_purchased         = sprintf( '%s', html_entity_decode( $prepared_commission['purchased_date'] ) );
				$cell_status_commission = sprintf( '%s', html_entity_decode( $prepared_commission['commission_status_text'] ) );
				$cell_status_receiver   = sprintf( '%s', html_entity_decode( $prepared_commission['receiver_status'] ) );
				$cell_note              = sprintf( '%s', html_entity_decode( $prepared_commission['note'] ) );

				/** APPLY_FILTERS: yith_wcsc_prepare_rows_list
				*
				* Filter each row before creating the CSV file when exporting the comission table.
				*
				* @param sprintf              All the HTML of each row.
				* @param $prepared_commission Get the commission with a standard texts and format.
				*/
				$row .= apply_filters( 'yith_wcsc_prepare_rows_list', sprintf( '%s,%s,%s,%s,%s,%s,%s,%s,%s', $cell_commission, $cell_product, $cell_total, $cell_details, $cell_order, $cell_purchased, $cell_status_commission, $cell_status_receiver, $cell_note ), $prepared_commission );

				$list['rows'][] = $row;
			}

			return $list;
		}

		/**
		 * Get CSV Headers
		 *
		 * Get the commissions results ready to can be exported to CSV
		 *
		 * @return array
		 * @since  1.0.0
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 */
		public function get_csv_headers() {

			$column_commission        = __( 'Commission', 'yith-stripe-connect-for-woocommerce' );
			$column_product           = __( 'Product', 'yith-stripe-connect-for-woocommerce' );
			$column_total             = __( 'Total', 'yith-stripe-connect-for-woocommerce' );
			$column_details           = __( 'Details', 'yith-stripe-connect-for-woocommerce' );
			$column_order             = __( 'Order', 'yith-stripe-connect-for-woocommerce' );
			$column_purchased         = __( 'Purchased date', 'yith-stripe-connect-for-woocommerce' );
			$column_status_commission = __( 'Status', 'yith-stripe-connect-for-woocommerce' );
			$column_status_receiver   = __( 'Receiver status', 'yith-stripe-connect-for-woocommerce' );
			$column_note              = __( 'Notes', 'yith-stripe-connect-for-woocommerce' );

			/** APPLY_FILTERS: yith_wcsc_prepare_columns_list_header_csv
			*
			* Filter the header document before creating the CSV file when exporting the comission table.
			*
			* @param array Each column label.
			*/
			$header = apply_filters( 'yith_wcsc_prepare_columns_list_header_csv', array(
				$column_commission,
				$column_product,
				$column_total,
				$column_details,
				$column_order,
				$column_purchased,
				$column_status_commission,
				$column_status_receiver,
				$column_note
			) );

			return $header;
		}
	}
}

function YITH_Stripe_Connect_Commissions() {
	return YITH_Stripe_Connect_Commissions::instance();
}