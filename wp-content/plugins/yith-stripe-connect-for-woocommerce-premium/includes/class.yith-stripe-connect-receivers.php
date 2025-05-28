<?php
/**
 * Receivers class
 *
 * This file belongs to the YITH Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @class      YITH_Stripe_Connect_Receivers
 * @package    YITH Stripe Connect for WooCommerce
 * @since      1.0.0
 * @author     YITH
 */

if ( ! defined( 'YITH_WCSC_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

if ( ! class_exists( 'YITH_Stripe_Connect_Receivers' ) ) {
	/**
	 * Class YITH_Stripe_Connect_Receivers
	 *
	 * @author Francisco Mateo
	 */
	class YITH_Stripe_Connect_Receivers {


		protected static $_instance = null;

		/**
		 * Stripe Connect Handler Instance
		 *
		 * @var YITH_Stripe_Connect_Commissions
		 * @since  1.0.0
		 * @access protected
		 */
		protected $_stripe_connect_commissions = null;

		public $items_per_page = '';


		/**
		 * Construct
		 *
		 * @author Francisco Mateo
		 * @since  1.0
		 */
		public function __construct() {
			$this->_stripe_connect_commissions = YITH_Stripe_Connect_Commissions::instance();

			/** APPLY_FILTERS: yith_wcsc_items_per_page_receivers
			*
			* Filter the items per page in the receivers table.
			*
			* @param float Number of items per page.
			*/
			$this->items_per_page = apply_filters( 'yith_wcsc_items_per_page_receivers', 20 );

		}

		/**
		 * Enqueue Script
		 *
		 * @author Francisco Javier Mateo <francisco.mateo@yithemes.com>
		 * @since  1.0.0
		 */
		public function enqueue_scripts() {
			global $pagenow;
			$debug_enabled   = defined( 'WP_DEBUG' ) ? WP_DEBUG : false;
			$prefix          = ! $debug_enabled ? '.min' : '';
			$js_dependencies = array(
				'jquery',
				'jquery-ui-sortable'
			);
			$data_to_js      = array();
			if ( $pagenow == 'post.php' && isset( $_GET['post'] ) && ( get_post_type( $_GET['post'] ) == 'product' || $pagenow == 'post-new.php' && $_GET['post_type'] == 'product' ) ) {
				$data_to_js['context']    = 'product_edit_page';
				$data_to_js['product_id'] = $_GET['post'];
			}
			if ( isset( $_GET['page'] ) && $_GET['page'] == 'yith_wcsc_panel' ){
				$data_to_js['assets_url'] = YITH_WCSC_ASSETS_URL;
			}

			wp_register_style( 'yith-wcsc-receivers-style', YITH_WCSC_ASSETS_URL . 'css/style-yith-sc-receivers.css', null, YITH_WCSC_VERSION );
			wp_register_script( 'yith-wcsc-receivers-script', YITH_WCSC_ASSETS_URL . 'js/script-yith-sc-receivers' . $prefix . '.js', $js_dependencies, YITH_WCSC_VERSION, true );

			wp_localize_script( 'yith-wcsc-receivers-script', 'yith_wcsc_receivers', $data_to_js );

			wp_enqueue_style( 'yith-wcsc-receivers-style' );
			wp_enqueue_script( 'yith-wcsc-receivers-script' );

		}

		public function insert( $receivers ) {
			global $wpdb;
			//*ID* | user_id | user_email | disabled | product_id | stripe_id | commission_value | commission_type | order_receiver
			$inserted = $wpdb->insert(
				$wpdb->yith_wcsc_receivers,
				array(
					'disabled'         => ( isset( $receivers['disabled'] ) ) ? true : false,
					'user_id'          => ( isset( $receivers['user_id'] ) ) ? $receivers['user_id'] : '',
					'user_email'       => ( isset( $receivers['user_email'] ) ) ? $receivers['user_email'] : '',
					'all_products'     => ( isset( $receivers['all_products'] ) ) ? $receivers['all_products'] : false,
					'product_id'       => ( isset( $receivers['product_id'] ) ) ? $receivers['product_id'] : '',
					'stripe_id'        => ( isset( $receivers['stripe_id'] ) ) ? $receivers['stripe_id'] : '',
					'commission_value' => ( isset( $receivers['commission_value'] ) ) ? $receivers['commission_value'] : '',
					'commission_type'  => ( isset( $receivers['commission_type'] ) ) ? $receivers['commission_type'] : '',
					'status_receiver'  => ( isset( $receivers['status_receiver'] ) ) ? $receivers['status_receiver'] : '',
					'order_receiver'   => ( isset( $receivers['order'] ) ) ? $receivers['order'] : - 1

				)
			);

			return $inserted;
		}

		public function update_by_user_id( $user_id, $receivers ) {
			global $wpdb;

			$data = array();
			foreach ( $receivers as $key => $receiver_column ) {
				$data[ $key ] = $receiver_column;
			}

			$updated = $wpdb->update(
				$wpdb->yith_wcsc_receivers,
				$data,
				array( 'user_id' => $user_id )
			);

			return $updated;
		}

		public function update( $id_receiver, $receivers ) {
			global $wpdb;

			$updated = $wpdb->update(
				$wpdb->yith_wcsc_receivers,
				array(
					'disabled'         => ( isset( $receivers['disabled'] ) ) ? true : false,
					'user_id'          => ( isset( $receivers['user_id'] ) ) ? $receivers['user_id'] : '',
					'user_email'       => ( isset( $receivers['user_email'] ) ) ? $receivers['user_email'] : '',
					'all_products'     => ( isset( $receivers['all_products'] ) ) ? $receivers['all_products'] : false,
					'product_id'       => ( isset( $receivers['product_id'] ) ) ? $receivers['product_id'] : '',
					'stripe_id'        => ( isset( $receivers['stripe_id'] ) ) ? $receivers['stripe_id'] : '',
					'commission_value' => ( isset( $receivers['commission_value'] ) ) ? $receivers['commission_value'] : '',
					'commission_type'  => ( isset( $receivers['commission_type'] ) ) ? $receivers['commission_type'] : '',
					'order_receiver'   => ( isset( $receivers['order'] ) ) ? $receivers['order'] : - 1

				),
				array( 'ID' => $id_receiver )
			);

			return $updated;

		}

		public function delete( $id_receiver ) {
			global $wpdb;

			$deleted = $wpdb->delete(
				$wpdb->yith_wcsc_receivers,
				array( 'ID' => $id_receiver )
			);

			return $deleted;
		}

		public function get_receivers_count( $args = array() ) {
			global $wpdb;
			$default_args = array(
				'user_id'      => '',
				'product_id'   => '',
				'all_products' => '',
				'disabled'     => ''
			);
			$receivers    = wp_parse_args( $args, $default_args );


			$query     = "select count(ID) from  $wpdb->yith_wcsc_receivers";
			$query_arg = array();

			$where_query = $this->build_where_query( $query, $query_arg, $receivers );
			$query       = $where_query['where_query'];
			$query_arg   = $where_query ['where_query_args'];

			$prepared_query = ! empty( $query_arg ) ? $wpdb->prepare( $query, $query_arg ) : $query;
			$result         = $wpdb->get_var( $prepared_query );

			return $result;
		}

		public function get_receiver( $id_receiver ) {
			global $wpdb;
			$query  = $wpdb->prepare( "SELECT * FROM $wpdb->yith_wcsc_receivers WHERE ID = %d", $id_receiver );
			$is_get = $wpdb->get_row(
				$query,
				OBJECT
			);

			return $is_get;
		}

		public function get_receivers( $args = array(), $paged = false ) {
			global $wpdb;

			$items_per_page = '';
			$offset         = '';

			if ( $paged ) {
				$items_per_page = $this->items_per_page;
				$page           = isset( $_GET['current_page'] ) ? abs( (int) $_GET['current_page'] ) : 1;
				$offset         = ( $page * $items_per_page ) - $items_per_page;
			}

			$default_args = array(
				'user_id'          => '',
				'user_email'       => '',
				'disabled'         => '',
				'all_products'     => '',
				'product_id'       => '',
				'stripe_id'        => '',
				'commission_value' => '',
				'commission_type'  => '',
				'status_receiver'  => '',
				'orderby'          => 'order_receiver',
				'order'            => 'ASC',
				'limit'            => $items_per_page,
				'offset'           => $offset
			);

			$receivers = wp_parse_args( $args, $default_args );

			$query_arg = array();
			$query     = "SELECT * FROM $wpdb->yith_wcsc_receivers";

			$where_query = $this->build_where_query( $query, $query_arg, $receivers );
			$query       = $where_query['where_query'];
			$query_arg   = $where_query ['where_query_args'];

			if ( ! empty( $receivers['orderby'] ) ) {
				$query .= sprintf( ' ORDER BY %s %s', $receivers['orderby'], $receivers['order'] );
			}
			if ( ! empty ( $receivers['limit'] ) ) {
				$query .= sprintf( ' LIMIT %d, %d', ! empty( $receivers['offset'] ) ? $receivers['offset'] : 0, $receivers['limit'] );
			}

			$prepared_query = ! empty( $query_arg ) ? $wpdb->prepare( $query, $query_arg ) : $query;
			$res            = $wpdb->get_results( $prepared_query, ARRAY_A );

			return $res;
		}

		public function connect_by_user_id_and_access_code( $user_id, $code ) {

			$stripe_connect_api_handler = YITH_Stripe_Connect_API_Handler::instance();
			$current_status             = yith_wcsc_get_stripe_user_status( $user_id );
			$stripe_token               = 'disconnect' == $current_status ? $stripe_connect_api_handler->get_OAuth_token( $code ) : false;

			if ( $stripe_token ) {
				$stripe_serialized = $stripe_token->jsonSerialize();
				update_user_meta( $user_id, 'stripe_user_id', $stripe_serialized['stripe_user_id'] );
				update_user_meta( $user_id, 'stripe_access_token', $stripe_serialized['access_token'] );
				$receiver = array(
					'user_id'         => $user_id,
					'status_receiver' => 'connect',
					'stripe_id'       => $stripe_serialized['stripe_user_id']
				);

				$this->update_by_user_id( $user_id, $receiver );
				// We check the current commissions that belong to user that could be tried proceed with the transfer and couldn't it because user disconnected their account.
				$this->_stripe_connect_commissions->check_commissions_status_by_user_id( $user_id );

				/** DO_ACTION: yith_wcsc_after_connect_with_stripe
				*
				* Adds an action after connect with Stripe.
				*
				* @param $user_id           ID of the user.
				* @param $code              ¿User? code.
				* @param $stripe_serialized Serialized Stripe token.
				*/
				do_action( 'yith_wcsc_after_connect_with_stripe', $user_id, $code, $stripe_serialized );
			}
		}

		public function disconnect_by_user_id( $user_id ) {
			$stripe_connect_api_handler = YITH_Stripe_Connect_API_Handler::instance();

			$stripe_user_id        = get_user_meta( $user_id, 'stripe_user_id', true );
			$acc_disc_from_stripe  = false;
			$acc_deleted_from_site = false;

			$result = array(
				'disconnected' => false,
				'message'      => ''
			);

			if ( ! empty( $stripe_user_id ) ) {
				$stripe_object = $stripe_connect_api_handler->deauthorize_account( $stripe_user_id );
				if ( $stripe_object instanceof Stripe\StripeObject or $stripe_object instanceof \Stripe\Exception\OAuth\InvalidClientException ) {
					$acc_disc_from_stripe = true;
				} else {
					$result['message'] = __( 'A problem occurred while trying to disconnect from Stripe Connect server',
						'yith-stripe-connect-for-woocommerce' );
				}
			}

			if ( $acc_disc_from_stripe ) {
				$acc_deleted_from_site     = delete_user_meta( $user_id, 'stripe_user_id' );
				$acc_detleted_access_token = delete_user_meta( $user_id, 'stripe_access_token' );
				$receiver                  = array(
					'user_id'         => $user_id,
					'status_receiver' => 'disconnect',
					'stripe_id'       => ''
				);
				$this->update_by_user_id( $user_id, $receiver );

				if ( ! $acc_deleted_from_site & ! $acc_detleted_access_token ) {
					$result['message'] = __( 'A problem occurred while trying to disconnect from the web page',
						'yith-stripe-connect-for-woocommerce' );
				}
			}

			if ( $acc_deleted_from_site & $acc_disc_from_stripe ) {
				$result['disconnected'] = true;
				$result['message'] == __( 'Your account has been disconnected', 'yith-stripe-connect-for-woocommerce' );
			}

			/** DO_ACTION: yith_wcsc_after_disconnect_with_stripe
			*
			* Adds an action after disconnect with Stripe.
			*
			* @param $user_id        ID of the user.
			* @param $stripe_user_id 'stripe_user_id' user meta.
			* @param $result         Result.
			*/
			do_action( 'yith_wcsc_after_disconnect_with_stripe', $user_id, $stripe_user_id, $result );

			return $result;
		}

		/** Some methods used the same where query structure, for this reason I grouped on one method.
		 *  WHERE clauses for...:
		 *  AND: user_id, order_id, product_id, all_products
		 */
		private function build_where_query( $query, $query_arg, $receivers ) {
			$query .= ' WHERE 1=1 ';

			if ( ! empty( $receivers['user_id'] ) ) {
				$query      .= ' AND user_id = %d';
				$query_arg[] = $receivers['user_id'];
			}

			if ( ! empty( ( $receivers['disabled'] ) ) ) {
				$query      .= 'AND disabled != %d';
				$query_arg[] = $receivers['disabled'];
			}

			// explicitly cast product_id parameter to array, in case it is not.
			if ( ! empty( $receivers['product_id'] ) ) {
				$receivers['product_id'] = (array) $receivers['product_id'];
			}

			if ( ! empty( $receivers['product_id'] ) && ! empty( $receivers['all_products'] ) ) {
				$query    .= ' AND ( product_id IN (' . trim( str_repeat( '%d, ', count( $receivers['product_id'] ) ), ', ' ) . ') OR all_products = 1 )';
				$query_arg = array_merge( $query_arg, $receivers['product_id'] );
			} elseif ( ! empty( $receivers['product_id'] ) ) {
				$query    .= ' AND product_id IN (' . trim( str_repeat( '%d, ', count( $receivers['product_id'] ) ), ', ' ) . ')';
				$query_arg = array_merge( $query_arg, $receivers['product_id'] );
			} elseif ( ! empty( $receivers['all_products'] ) ) {
				$query .= ' AND all_products = 1';
			}

			return array(
				'where_query'      => $query,
				'where_query_args' => $query_arg,
			);
		}

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}
	}
}

function YITH_Stripe_Connect_Receivers() {
	return YITH_Stripe_Connect_Receivers::instance();
}
