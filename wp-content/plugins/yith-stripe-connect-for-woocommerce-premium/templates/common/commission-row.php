<?php

/**
 * Stripe Connect commission row
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
 * @var $index
 * @var $id_commission
 * @var $display_name
 * @var $order_item_id
 * @var $product_title
 * @var $product_qty
 * @var $product_info
 * @var $order_id
 * @var $order_date
 * @var $order_status
 * @var $order_total
 * @var $line_total
 * @var $cost
 * @var $commission_text_detail
 * @var $day_delay
 * @var $commission_status
 * @var $commission_rate
 * @var $commission_type
 * @var $commission_total
 * @var $commission_status_resumed
 * @var $commission_status_text
 * @var $purchased_date
 * @var $receiver_status
 * @var $note
 * @var $affiliate_text
 * @var $affiliate_payment_id
 * @var $affiliate_payment_link
 * @var $affiliate_commission_link
 * @var $affiliate_commission_id
 */
?>
<tr id="<?php echo esc_attr( $index ); ?>" class="yith_wcsc_commission_row">
	<?php
	$view_text              = __( 'View', 'yith-stripe-connect-for-woocommerce' );
	$integration_class_icon = '';

	if ( ! empty( $affiliate_text ) ) {
		$integration_class_icon = '<span class="integration_commission_icon dashicons dashicons-admin-users" title="' . esc_html__( 'Affiliates Commission', 'yith-stripe-connect-for-woocommerce' ) . '"></span>';
	}

	if ( ! empty( $multivendor_text ) ) {
		$integration_class_icon = '<span class="integration_commission_icon dashicons dashicons-groups" title="' . esc_html__( 'Multi Vendor Commission', 'yith-stripe-connect-for-woocommerce' ) . '"></span>';
	}

	?>
	<td>
		<a href="#" data-commission="<?php echo esc_attr( $id_commission ); ?>" class="_commission dashicons dashicons-visibility view-info" title="<?php echo esc_attr( $view_text ); ?>"><?php echo esc_html( $view_text ); ?></a>
	</td>
	<?php if ( is_admin() ) { ?>
		<td class="info-field _receivers_<?php echo esc_attr( $index ); ?>_receiver_info receiver-info">
			<span class="_commission commission_title"><strong> #<?php echo esc_html( $id_commission . ' ' . $display_name ); ?></strong></span>
			<?php echo $integration_class_icon; // phpcs:ignore ?>
		</td>
	<?php } ?>
	<td class="info-field _receivers_<?php echo esc_attr( $index ); ?>_product_info product-info">
		<?php echo ! is_admin() ? $integration_class_icon : ''; // phpcs:ignore ?>
		<span class="_commission"><?php echo esc_html( $product_info ); ?></span>
	</td>
	<td class="info-field _receivers_<?php echo esc_attr( $index ); ?>_commission_info commission-info">
		<?php
		if ( ! empty( $commission_text_detail ) ) {
			echo wc_help_tip( $commission_text_detail ); // phpcs:ignore
		}
		?>
		<span class="_commission" title="<?php echo esc_attr( $commission_text_detail ); ?> "><?php echo esc_html( $commission_total ); ?></span>
	</td>
	<td class="info-field _receivers_<?php echo esc_attr( $index ); ?>_order_info order-info">
		<?php if ( is_admin() ) : ?>
			<?php $order_link = get_edit_post_link( $order_id ); ?>
			<span class="_commission"><a href="<?php echo esc_url( $order_link ); ?>">#<?php echo esc_html( $order_id ); ?></a></span>
		<?php else : ?>
			<span class="_commission">#<?php echo esc_html( $order_id ); ?></span>
		<?php endif; ?>
	</td>
	<td class="info-field _receivers_<?php echo esc_attr( $index ); ?>_purchased_date_info purchased-date-info">
		<span class="_commission"><?php echo esc_html( $purchased_date ); ?></span>
	</td>
	<td class="info-field _receivers_<?php echo esc_attr( $index ); ?>_commission_status_info commission-status-info">
		<span class="_commission commission_status commission_status_<?php echo esc_attr( $commission_status ); ?>" title="<?php echo esc_attr( $commission_status_text ); ?>">
			<?php echo is_admin() ? esc_html( $commission_status_text ) : esc_html( $commission_status_resumed ); ?>
		</span>
		<?php
		if ( ! empty( $note ) ) {
			echo wc_help_tip( $note ); // phpcs:ignore
		}
		?>
	</td>
	<?php if ( is_admin() ) : ?>
		<td class="info-field _receivers_<?php echo esc_attr( $index ); ?>_status_receiver_info status-receiver-info <?php echo esc_attr( '_status_receiver_' . $receiver_status ); ?>">
			<?php
			$receiver_status_text = '';
			if ( 'connect' == $receiver_status ) {
				$receiver_status_text = __( 'Connected', 'yith-stripe-connect-for-woocommerce' );
			}
			if ( 'disconnect' == $receiver_status ) {
				$receiver_status_text = __( 'Disconnected', 'yith-stripe-connect-for-woocommerce' );
			}
			?>
			<img class="_commission" src="<?php echo esc_url( YITH_WCSC_ASSETS_URL . 'images/sc-icon-' . $receiver_status . '.svg' ); ?>" title="<?php echo esc_attr( $receiver_status_text ); ?>">
		</td>
	<?php endif; ?>
</tr>
