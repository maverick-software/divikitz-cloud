<?php

/**
 * Stripe Connect commissions table
 *
 * @class      YITH_Stripe_Connect_Frontend
 * @package    Yithemes
 * @since      Version 1.0.0
 * @author     Francisco Javier Mateo <francisco.mateo@yithemes.com>
 */

if ( ! defined( 'YITH_WCSC_PATH' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Template variables:
 *
 * @var $current_status
 * @var $oauth_link
 * @var $button_text
 * @var $button_class
 * @var $count_commissions
 * @var $current_page
 * @var $items_per_page
 * @var $commissions
 */
?>

<div id="yith_wcsc_commissions_header">
	<span><h2><?php esc_html_e( 'Commission Report', 'yith-stripe-connect-for-woocommerce' ); ?></h2></span>
	<div id="yith_wcsc_filter" class="_commissions_filters">
		<form class="yith_wcsc_filter_form" method="get">
			<input type="hidden" name="page" value="<?php echo isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore ?>">
			<input type="hidden" name="tab" value="<?php echo isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore ?>">
			<?php
			if ( is_admin() ) :
				?>
				<div id="yith_product_user_search">
					<?php
					$reset_args   = ! empty( $_REQUEST['page'] ) ? array(
						'page' => sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ),
					) : array();

					/** APPLY_FILTERS: yith_wcsc_commissions__reset_filter_url
					*
					* Filter the reset button url.
					*
					* @param $url Default reset URL.
					*/
					$reset_button = apply_filters( 'yith_wcsc_commissions__reset_filter_url', esc_url( add_query_arg( $reset_args, admin_url( 'admin.php' ) ) ) );

					$product_selected = '';
					$product_value    = '';
					$user_selected    = '';
					$user_value       = '';

					if ( isset( $_GET['yith_wcs_product'] ) ) {
						$product_value = intval( $_GET['yith_wcs_product'] );
						$product       = wc_get_product( $product_value );

						if ( version_compare( WC()->version, '2.7.0', '>=' ) ) {
							$product_selected = array( $_GET['yith_wcs_product'] => yit_get_prop( $product, 'name' ) );
						} else {
							$product_selected = yit_get_prop( $product, 'name' );
						}
					}

					if ( isset( $_GET['yith_wcs_user'] ) ) {
						$user_value = intval( $_GET['yith_wcs_user'] );
						$user       = get_userdata( $user_value );

						if ( version_compare( WC()->version, '2.7.0', '>=' ) ) {
							$user_selected = array( $_GET['yith_wcs_user'] => $user->display_name );
						} else {
							$user_selected = sprintf(
							// translators: 1. Display name. 2. User ID. 3. User email.
								esc_html__( '%1$s (#%2$s &ndash; %3$s)', 'yith-stripe-connect-for-woocommerce' ),
								$user->display_name,
								absint( $user->ID ),
								$user->user_email
							);
						}
					}

					$args_product_filter = array(
						'class'            => 'wc-product-search _commissions_product_filter _filter',
						'name'             => 'yith_wcs_product',
						'data-allow_clear' => true,
						'data-selected'    => $product_selected,
						'data-multiple'    => false,
						'data-placeholder' => __( 'Filter by product', 'yith-stripe-connect-for-woocommerce' ),
						'value'            => $product_value,
						'style'            => 'min-width:250px; max-width = 250px',

					);
					yit_add_select2_fields( $args_product_filter );

					$args_user_filter = array(
						'class'            => 'wc-customer-search _commissions_product_filter _filter',
						'name'             => 'yith_wcs_user',
						'data-allow_clear' => true,
						'data-selected'    => $user_selected,
						'data-multiple'    => false,
						'data-placeholder' => __( 'Filter by user', 'yith-stripe-connect-for-woocommerce' ),
						'value'            => $user_value,
						'style'            => 'min-width:250px; max-width = 250px',

					);
					yit_add_select2_fields( $args_user_filter );
					?>
				</div>
			<?php endif; ?>
			<div id="yith_wcsc_day_month_year">
				<?php
				$from_value = isset( $_GET['yith_wcsc_date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['yith_wcsc_date_from'] ) ) : '';
				$to_value   = isset( $_GET['yith_wcsc_date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['yith_wcsc_date_to'] ) ) : '';
				?>
				<span class="date_from_form">
					<input type="text" id="yith_wcsc_date_from" name="yith_wcsc_date_from" value="<?php echo esc_attr( $from_value ); ?>" placeholder="<?php esc_html_e( 'From...', 'yith-stripe-connect-for-woocommerce' ); ?>">
				</span>
				<span class="date_to_text">
					<strong> <?php esc_html_e( 'to', 'yith-stripe-connect-for-woocommerce' ); ?> </strong>
				</span>
				<span class="date_to_form">
					<input type="text" id="yith_wcsc_date_to" name="yith_wcsc_date_to" value="<?php echo esc_attr( $to_value ); ?>" placeholder="<?php esc_html_e( 'To...', 'yith-stripe-connect-for-woocommerce' ); ?>">
				</span>
			</div>
			<div id="yith_wcsc_filter_options" class="<?php echo is_admin() ? 'yith_wcsc_admin' : ''; ?>">
				<button class="button clear_filter"><?php esc_html_e( 'Clear', 'yith-stripe-connect-for-woocommerce' ); ?></button>
				<button type="submit" class="button-primary"><?php esc_html_e( 'Filter', 'yith-stripe-connect-for-woocommerce' ); ?></button>
			</div>
		</form>
		<div id="yith_wcsc_export_panel">
			<?php
			if ( isset( $_GET['page'] ) && 'yith_wcsc_panel' == $_GET['page'] ) {
				$_GET['yith_wcsc_admin_commission_section'] = 'admin_section';
			}
			$_GET['action'] = 'wcsc_export_csv_action'; // I know that $_GET variable have the filters args. So I reused to define the action for url export...
			?>
			<a id="_export_csv_button" class="_export_csv_button button button-secondary" href="<?php echo esc_url( add_query_arg( $_GET, admin_url( 'admin-ajax.php' ) ) ); ?>" target="_blank">
				<?php esc_html_e( 'Export to CSV', 'yith-stripe-connect-for-woocommerce' ); ?>
			</a>
			<?php
			$_GET['action'] = 'wcsc_export_pdf_action'; // I know that $_GET variable have the filters args. So I reused to define the action for url export...
			?>
			<a id="_print_result" class="_print_result_button button button-secondary" href="<?php echo esc_url( add_query_arg( $_GET, admin_url( 'admin-ajax.php' ) ) ); ?>" target="_blank">
				<?php esc_html_e( 'PDF', 'yith-stripe-connect-for-woocommerce' ); ?>
			</a>
		</div>
	</div>
	<div class="commissions_pagination">
		<?php
		$total = ( $count_commissions > $items_per_page ) ? ceil( $count_commissions / $items_per_page ) : 0;
		if ( $total > 1 ) {
			?>
			<div>
				<?php
				echo paginate_links( // phpcs:ignore
					array(
						'base'      => add_query_arg( 'current_page', '%#%' ),
						'format'    => '',
						'prev_text' => '&laquo;',
						'next_text' => '&raquo;',
						'total'     => $total,
						'current'   => $current_page,
					)
				);
				?>
			</div>
			<?php
		}
		?>
	</div>
</div>
<div id="yith_wcsc_commissions_panel">
	<table class="commissions_options wp-list widefat">
		<thead>
		<?php
		$yith_wcsc_order = 'ASC';
		if ( isset( $_GET['yith_wcsc_order'] ) ) {
			$yith_wcsc_order = ( 'ASC' == $_GET['yith_wcsc_order'] ) ? 'DESC' : 'ASC';
		}

		$order_by_commission_url = add_query_arg(
			array(
				'yith_wcsc_orderby' => 'ID',
				'yith_wcsc_order'   => $yith_wcsc_order,
			)
		);
		$order_by_total_url      = add_query_arg(
			array(
				'yith_wcsc_orderby' => 'commission',
				'yith_wcsc_order'   => $yith_wcsc_order,
			)
		);
		$order_by_order_url      = add_query_arg(
			array(
				'yith_wcsc_orderby' => 'order_id',
				'yith_wcsc_order'   => $yith_wcsc_order,
			)
		);
		$order_by_purchased_url  = add_query_arg(
			array(
				'yith_wcsc_orderby' => 'purchased_date',
				'yith_wcsc_order'   => $yith_wcsc_order,
			)
		);
		?>
		<tr>
			<th class="info-view"><span class="dashicons dashicons-visibility"></span></th>
			<?php if ( is_admin() ) { ?>
				<th class="info-receiver">
					<a href="<?php echo esc_url( $order_by_commission_url ); ?>"><?php esc_html_e( 'Commission', 'yith-stripe-connect-for-woocommerce' ); ?></a>
				</th>
			<?php } ?>
			<th class="info-product"><?php esc_html_e( 'Product', 'yith-stripe-connect-for-woocommerce' ); ?></th>
			<th class="info-commission-total">
				<a href="<?php echo esc_url( $order_by_total_url ); ?>"><?php esc_html_e( 'Total', 'yith-stripe-connect-for-woocommerce' ); ?></a>
			</th>
			<th class="info-order">
				<a class="" href="<?php echo esc_url( $order_by_order_url ); ?>"><?php esc_html_e( 'Order', 'yith-stripe-connect-for-woocommerce' ); ?></a>
			</th>
			<th class="info-purchased_date">
				<a href="<?php echo esc_url( $order_by_order_url ); ?>"><?php esc_html_e( 'Purchase date', 'yith-stripe-connect-for-woocommerce' ); ?></a>
			</th>
			<th class="info-commission_status"><?php esc_html_e( 'Status', 'yith-stripe-connect-for-woocommerce' ); ?></th>
			<?php if ( is_admin() ) { ?>
				<th class="info-status_receiver">
					<img src="<?php echo esc_url( YITH_WCSC_ASSETS_URL . 'images/sc-icon.svg' ); ?>" title="<?php esc_html_e( 'Receiver Status', 'yith-stripe-connect-for-woocommerce' ); ?>">
				</th>
			<?php } ?>
		</tr>
		</thead>
		<tbody class="yith_wcsc_table_commissions">
		<?php
		$_stripe_connect_receivers = new YITH_Stripe_Connect_Receivers();
		foreach ( $commissions as $index => $commission_row ) {
			$prepared_commission = yith_wcsc_prepare_commission_args( $commission_row, $index );
			if ( isset( $prepared_commission['id_commission'] ) ) {
				yith_wcsc_get_template( 'commission-row', $prepared_commission, 'common' );
			}
		}
		?>
		</tbody>
		<tfoot>
		</tfoot>
	</table>
</div>
<div id="yith_wcsc_commissions_footer">
	<?php
	yith_wcsc_get_template( 'commission-view', array(), 'common' );
	?>
</div>
