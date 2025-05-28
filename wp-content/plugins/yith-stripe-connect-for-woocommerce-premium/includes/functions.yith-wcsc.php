<?php
if ( ! function_exists( 'yith_wcsc_locate_template' ) ) {
	/**
	 * Locate template for Stripe plugin
	 *
	 * @param $filename string Template name (with or without extension)
	 * @param $section  string Subdirectory where to search
	 *
	 * @return string Found template
	 */
	function yith_wcsc_locate_template( $filename, $section = '' ) {
		$ext = strpos( $filename, '.php' ) === false ? '.php' : '';

		$template_name = $section . '/' . $filename . $ext;
		$template_path = WC()->template_path() . 'yith-wcsc/';
		$default_path  = YITH_WCSC_PATH . 'templates/';

		return wc_locate_template( $template_name, $template_path, $default_path );
	}
}

if ( ! function_exists( 'yith_wcsc_get_template' ) ) {
	/**
	 * Get template for Stripe Connect plugin
	 *
	 * @param $filename string Template name (with or without extension)
	 * @param $args     mixed Array of params to use in the template
	 * @param $section  string Subdirectory where to search
	 */
	function yith_wcsc_get_template( $filename, $args = array(), $section = '' ) {
		$ext = preg_match( '/^.*\.[^\.]+$/', $filename ) ? '' : '.php';

		$template_name = $section . '/' . $filename . $ext;
		$template_path = WC()->template_path() . 'yith-wcsc/';
		$default_path  = YITH_WCSC_PATH . 'templates/';

		wc_get_template( $template_name, $args, $template_path, $default_path );
	}
}

if ( ! function_exists( 'yith_wcsc_check_free_order_item' ) ) {
	function yith_wcsc_check_free_order_item( $item ) {
		$item_free  = true;
		$line_total = 0;

		if ( is_object( $item ) && method_exists( $item, 'get_total' ) ) {
			$line_total = $item->get_total();
		} elseif ( isset( $item['line_total'] ) ) {
			$line_total = $item['line_total'];
		}

		if ( 0 < $line_total ) {
			$item_free = false;
		}

		return $item_free;
	}
}

if ( ! function_exists( 'yith_wcsc_prepare_commission_args' ) ) {
	//Prepare the commissions args for templates or another kind of situations...
	function yith_wcsc_prepare_commission_args( $commission = array(), $index = '' ) {
		if ( $commission instanceof stdClass ) {
			$commission = (array) $commission;
		}

		$user          = get_user_by( 'id', $commission['user_id'] );
		$product       = ! empty( $commission['product_id'] ) ? wc_get_product( $commission['product_id'] ) : false;
		$order_item_id = $commission['order_item_id'];

		$product_qty = wc_get_order_item_meta( $order_item_id, '_qty' );
		$line_total  = wc_get_order_item_meta( $order_item_id, '_line_total' );
		$day_delay   = yith_wcsc_calculate_day_delay( $commission['payment_retarded'], $commission['purchased_date'] );

		$receiver_status = yith_wcsc_get_stripe_user_status( $commission['user_id'] );
		$order           = wc_get_order( $commission['order_id'] );
		if ( ! $order ) {
			return;
		}
		$status_order = yit_get_prop( $order, 'status' );
		$order_date   = yit_get_prop( $order, 'date_created' );
		$order_total  = yit_get_prop( $order, 'order_total' ) . get_woocommerce_currency_symbol();

		if ( version_compare( WC()->version, '3.0.0', '>=' ) ) {
			$order_date_array  = (array) $order_date;
			$order_date_string = date_i18n( get_option( 'date_format' ), strtotime( $order_date_array['date'] ) );
		} else {
			$order_date_string = date_i18n( get_option( 'date_format' ), strtotime( $order_date ) );
		}

		$purchased_date_commission    = date_i18n( get_option( 'date_format' ), strtotime( $commission['purchased_date'] ) );
		$commission['purchased_date'] = $purchased_date_commission;


		$commission_status_resumed = yith_wcsc_get_commission_status_text( $commission['commission_status'], $day_delay, $receiver_status, $status_order, 'resumed' );
		$commission_status_text    = yith_wcsc_get_commission_status_text( $commission['commission_status'], $day_delay, $receiver_status, $status_order );

		$note_text = '';
		if ( ! empty( $commission['note'] ) ) {
			$notes = maybe_unserialize( $commission['note'] );
			if ( is_array( $notes ) ) {
				foreach ( $notes as $key => $note ) {
					switch ( $key ) {
						case 'transfer_id':
							$note_text .= sprintf( __( 'Transfer ID: %s', 'yith-stripe-connect-for-woocommerce' ), $note ) . ' ';
							break;
						case 'destination_payment':
							$note_text .= sprintf( __( 'Destination payment: %s', 'yith-stripe-connect-for-woocommerce' ), $note ) . ' ';
							break;
						case 'error_transfer':
							$note_text .= sprintf( __( 'Transfer error: %s', 'yith-stripe-connect-for-woocommerce' ), $note ) . ' ';
							break;
					}
				}
			}
		}

		$affiliate_data = array(
			'text'                      => '',
			'payment_link'              => '',
			'commission_affiliate_link' => '',
			'payment_id'                => '',
			'affiliate_commission_id'   => ''
		);

		$multivendor_data = array(
			'text'                        => '',
			'commission_multivendor_link' => '',
			'multivendor_commission_id'
										  => ''
		);
		if ( ! empty( $commission['integration_item'] ) ) {
			$commission_integrated = unserialize( $commission['integration_item'] );
			$plugin_integration    = isset( $commission_integrated['plugin_integration'] ) ? $commission_integrated['plugin_integration'] : '';

			if ( 'affiliates' == $plugin_integration && class_exists( 'YITH_WCAF' ) ) {
				$affiliate_data['text']         = __( 'Commissions paid with YITH WooCommerce Affiliates Plugin',
					'yith-stripe-connect-for-woocommerce' );
				$affiliate_data['payment_link'] = ( is_admin() ) ? sprintf( '<a href="%s" target="_blank"><strong>#%d</strong></a>', esc_url( add_query_arg( array(
					'page'       => 'yith_wcaf_panel',
					'tab'        => 'payments',
					'payment_id' => $commission_integrated['payment_id']
				), admin_url( 'admin.php' ) ) ), $commission_integrated['payment_id'] ) : '';

				$affiliate_data['commission_affiliate_link'] = ( is_admin() ) ? sprintf( '<a href="%s" target="_blank"><strong>#%d</strong></a>', esc_url( add_query_arg( array(
					'page'          => 'yith_wcaf_panel',
					'tab'           => 'commissions',
					'commission_id' => $commission_integrated['affiliate_commission_id']
				), admin_url( 'admin.php' ) ) ), $commission_integrated['affiliate_commission_id'] ) : '';

				$affiliate_data['payment_id']              = $commission_integrated['payment_id'];
				$affiliate_data['affiliate_commission_id'] = $commission_integrated['affiliate_commission_id'];
			}
		}

		$commission_text_detail = '';
		if ( 'percentage' == $commission['commission_type'] ) {
			$commission_text_detail = $commission['commission_rate'] . '% ' . _x( 'from', 'xx% from price-item', 'yith-stripe-connect-for-woocommerce' ) . ' ' . $line_total . get_woocommerce_currency_symbol();
		} else if ( 'fixed' == $commission['commission_type'] ) {
			$commission_text_detail = __( 'Fixed commission', 'yith-stripe-connect-for-woocommerce' );
			if ( $product_qty > 1 ) {
				$commission_text_detail .= '(' . $commission['commission_rate'] . get_woocommerce_currency_symbol() . ' ' . __( 'each one', 'yith-stripe-connect-for-woocommerce' ) . ')';
			}
		}
		$product_title       = ! empty( $product ) ? yit_get_prop( $product, 'title' ) : '';

		$currency_code = $order->get_currency();
		$currency_symbol = get_woocommerce_currency_symbol( $currency_code );

		$prepared_commission = array(
			'index'                     => $index,
			'id_commission'             => $commission['ID'],
			'display_name'              => ( $user ) ? $user->display_name : '',
			'order_item_id'             => $order_item_id,
			'product_title'             => $product_title,
			'product_qty'               => $product_qty,
			'product_info'              => ! empty( $product_title ) && ! empty( $product_qty ) ? sprintf( "%s x %s", $product_title, $product_qty ) : '',
			'order_id'                  => yit_get_prop( $order, 'id' ),
			'order_date'                => $order_date_string,
			'order_status'              => $status_order,
			'order_total'               => $order_total,
			'line_total'                => $line_total,
			'cost'                      => $line_total . $currency_symbol,
			'commission_text_detail'    => $commission_text_detail,
			'day_delay'                 => $day_delay,
			'commission_status'         => $commission['commission_status'],
			'commission_rate'           => $commission['commission_rate'],
			'commission_type'           => $commission['commission_type'],
			'commission_total'          => $commission['commission'] . $currency_symbol,
			'commission_status_resumed' => $commission_status_resumed,
			'commission_status_text'    => $commission_status_text,
			'purchased_date'            => $commission['purchased_date'],
			'receiver_status'           => $receiver_status,
			'note'                      => $note_text,
			'affiliate_text'            => $affiliate_data['text'],
			'affiliate_payment_id'      => $affiliate_data['payment_id'],
			'affiliate_payment_link'    => $affiliate_data['payment_link'],
			'affiliate_commission_link' => $affiliate_data['commission_affiliate_link'],
			'affiliate_commission_id'   => $affiliate_data['affiliate_commission_id'],
		);

		/** APPLY_FILTERS: yith_wcsc_prepared_commission_args
		*
		* Filter the prepared comission args.
		*
		* @param array $prepared_commission prepared_commission.
		* @param array $commission          Commission data.
		*/
		return apply_filters( 'yith_wcsc_prepared_commission_args', $prepared_commission, $commission );
	}
}

if ( ! function_exists( 'yith_wcsc_calculate_day_delay' ) ) {

	function yith_wcsc_calculate_day_delay( $days, $purchased_date ) {
		$purchased_date_timestamp = strtotime( $purchased_date );
		$current_timestamp        = time();

		$seconds_left = $purchased_date_timestamp - $current_timestamp;
		$passed_days  = ceil( $seconds_left / DAY_IN_SECONDS );

		$result = $days + $passed_days;

		$result = ( $result > 0 ) ? $result : 0;

		return $result;
	}
}

if ( ! function_exists( 'yith_wcsc_get_stripe_user_status' ) ) {
	function yith_wcsc_get_stripe_user_status( $id ) {
		$result         = 'disconnect';
		$stripe_user_id = get_user_meta( $id, 'stripe_user_id', true );
		if ( ! empty( $stripe_user_id ) ) {
			$result = 'connect';
		}

		return $result;
	}
}

if ( ! function_exists( 'yith_wcsc_get_amount' ) ) {
	function yith_wcsc_get_amount( $total, $currency = '' ) {
		$zero_decimals = array(
			'BIF',
			'CLP',
			'DJF',
			'GNF',
			'JPY',
			'KMF',
			'KRW',
			'MGA',
			'PYG',
			'RWF',
			'VND',
			'VUV',
			'XAF',
			'XOF',
			'XPF'
		);

		if ( empty( $currency ) ) {
			$currency = get_woocommerce_currency();
		}

		$currency = strtoupper( $currency );

		if ( ! in_array( $currency, $zero_decimals ) ) {
			$total *= 100;
		}

		return round( $total );
	}
}

if ( ! function_exists( 'yith_wcsc_get_price' ) ) {
	function yith_wcsc_get_price( $amount, $currency = '' ) {
		$zero_decimals = array(
			'BIF',
			'CLP',
			'DJF',
			'GNF',
			'JPY',
			'KMF',
			'KRW',
			'MGA',
			'PYG',
			'RWF',
			'VND',
			'VUV',
			'XAF',
			'XOF',
			'XPF'
		);

		if ( empty( $currency ) ) {
			$currency = get_woocommerce_currency();
		}

		$currency = strtoupper( $currency );

		if ( ! in_array( $currency, $zero_decimals ) ) {
			$amount = number_format( $amount / 100, 2, '.', '' );;
		}

		return $amount;
	}
}

if ( ! function_exists( 'yith_wcsc_get_commission_status_text' ) ) {
	function yith_wcsc_get_commission_status_text( $status, $day_delay = 0, $receiver = '', $status_order = '', $context = '' ) {
		$status_text = '';
		// Pepare WC Order Status.
		$order_group = array(
			'cancelled' => true,
			'refunded'  => true,
			'failed'    => true,
			'on-hold'   => true,
			'pending'   => true
		);
		/**
		 * Swicth differents status to change the current status text...
		 */
		switch ( $status ) {
			case 'sc_pending':
				// Define a Pending text by default...
				$status_text = ( 'resumed' == $context ) ? __( 'Pending...', 'yith-stripe-connect-for-woocommerce' ) : __( 'Pending payment', 'yith-stripe-connect-for-woocommerce' );

				/**
				 * Now we handles diferents situations for pending commissions...
				 */

				// First we check that our order status be cancelled, refunded, failed, on-hold, pending...
				// On these cases we directly display this status. Because on this status the commission can´ be proceed.
				if ( isset( $order_group[ $status_order ] ) ) {
					$status_text = __( 'Order ' . $status_order, 'yith-stripe-connect-for-woocommerce' );

					return $status_text;
				}
				// Now checks that stripe account is connected. If is disconnect we print this info. Because on this status the commission can´ be proceed.
				if ( 'disconnect' == $receiver ) {
					$status_text = ( 'resumed' == $context ) ? __( 'Disconnected', 'yith-stripe-connect-for-woocommerce' ) : __( 'The user\'s Stripe account has been disconnected', 'yith-stripe-connect-for-woocommerce' );

					return $status_text;
				}
				// Now we get the time delay to display on commission status...
				if ( 0 != $day_delay ) {
					$status_text = ( 'resumed' == $context ) ? $day_delay . ' ' . __( 'days left...', 'yith-stripe-connect-for-woocommerce' ) : $day_delay . ' ' . __( 'days left to proceed with payment...', 'yith-stripe-connect-for-woocommerce' );

					return $status_text;
				}
				break;
			case  'sc_transfer_processing':
				$status_text = ( 'resumed' == $context ) ? __( 'Processing...', 'yith-stripe-connect-for-woocommerce' ) : __( 'Processing transfer', 'yith-stripe-connect-for-woocommerce' );

				return $status_text;
				break;
			case 'sc_transfer_error':
				$status_text = ( 'resumed' == $context ) ? __( 'Transfer error...', 'yith-stripe-connect-for-woocommerce' ) : __( 'Error processing transfer', 'yith-stripe-connect-for-woocommerce' );;

				return $status_text;
				break;
			case 'sc_transfer_success':
				$status_text = ( 'resumed' == $context ) ? __( 'Paid', 'yith-stripe-connect-for-woocommerce' ) : __( 'Commission paid', 'yith-stripe-connect-for-woocommerce' );

				return $status_text;
				break;
		}

		return $status_text;
	}
}

if ( ! function_exists( 'yith_wcsc_get_csv_list' ) ) {
	function yith_wcsc_get_csv_list() {
		$stripe_connect_commissions = YITH_Stripe_Connect_Commissions::instance();

		$commissions_args = array(
			'product_id' => isset( $_GET['yith_wcs_product'] ) ? $_GET['yith_wcs_product'] : '',
			'user_id'    => isset( $_GET['yith_wcs_user'] ) ? $_GET['yith_wcs_user'] : '',
			'day'        => isset( $_GET['yith_wcsc_day'] ) ? $_GET['yith_wcsc_day'] : '',
			'month_year' => isset( $_GET['yith_wcsc_month_year'] ) ? $_GET['yith_wcsc_month_year'] : ''
		);
		$commissions      = $stripe_connect_commissions->get_commissions( $commissions_args );
		// Prepare the columns
		$column_commission        = __( 'Commission', 'yith-stripe-connect-for-woocommerce' );
		$column_product           = __( 'Product', 'yith-stripe-connect-for-woocommerce' );
		$column_total             = __( 'Total', 'yith-stripe-connect-for-woocommerce' );
		$column_details           = __( 'Details', 'yith-stripe-connect-for-woocommerce' );
		$column_order             = __( 'Order', 'yith-stripe-connect-for-woocommerce' );
		$column_purchased         = __( 'Purchased date', 'yith-stripe-connect-for-woocommerce' );
		$column_status_commission = __( 'Status', 'yith-stripe-connect-for-woocommerce' );
		$column_status_receiver   = __( 'Receiver Status', 'yith-stripe-connect-for-woocommerce' );
		$column_note              = __( 'Notes', 'yith-stripe-connect-for-woocommerce' );

		$list = array(
			'columns' => sprintf( '%s,%s,%s,%s,%s,%s,%s,%s,%s', $column_commission, $column_product, $column_total, $column_details, $column_order, $column_purchased, $column_status_commission, $column_status_receiver, $column_note ),
			'rows'    => array()
		);
		// For each commission we prepare one row...
		foreach ( $commissions as $commission ) {
			$prepared_commission = yith_wcsc_prepare_commission_args( $commission ); // Get the commission with a standar texts and format...
			$row                 = '';

			// Prepare each cell items for our csv file...
			$cell_commission        = sprintf( '#%d %s', $commission['ID'], $prepared_commission['user']->display_name );
			$cell_product           = sprintf( '%s x %d', $prepared_commission['product']->get_title(), $prepared_commission['product_qty'] );
			$cell_total             = sprintf( '%d%s', $commission['commission_rate'], get_woocommerce_currency_symbol() );
			$cell_details           = sprintf( '%s', $prepared_commission['commission_text_detail'] );
			$cell_order             = sprintf( '#%s', $commission['order_id'] );
			$cell_purchased         = sprintf( '%s', $commission['purchased_date'] );
			$cell_status_commission = sprintf( '%s', $prepared_commission['commission_status_text'] );
			$cell_status_receiver   = sprintf( '%s', $prepared_commission['receiver_status'] );
			$cell_note              = sprintf( '%s', $prepared_commission['note'] );

			$row .= sprintf( '%s,%s,%s,%s,%s,%s,%s,%s,%s', $cell_commission, $cell_product, $cell_total, $cell_details, $cell_order, $cell_purchased, $cell_status_commission, $cell_status_receiver, $cell_note );

			$list['rows'][] = $row;
		}

		return $list;
	}
}

if ( ! function_exists( 'yith_wcsc_get_cart_hash' ) ) {
	/**
	 * Retrieves cart hash, using WC method when available, or providing an approximated version for older WC versions
	 *
	 * @return string Cart hash
	 */
	function yith_wcsc_get_cart_hash() {
		$cart = WC()->cart;

		if ( ! $cart ) {
			return '';
		}

		if ( method_exists( $cart, 'get_cart_hash' ) ) {
			return $cart->get_cart_hash();
		} else {
			$cart_contents = $cart->get_cart_contents();

			return $cart_contents ? md5( wp_json_encode( $cart_contents ) . $cart->get_total( 'edit' ) ) : '';
		}
	}
}
