<?php
/**
 * Affiliate Dashboard Payments - Withdraw Modal Opener
 *
 * @author YITH
 * @package YITH\Affiliates\Templates
 * @version 2.0.0
 */

/**
 * Template variables:
 *
 * @var $affiliate YITH_WCAF_Affiliate
 */

if ( ! defined( 'YITH_WCAF' ) ) {
	exit;
} // Exit if accessed directly
?>

<?php
/**
 * DO_ACTION: yith_wcaf_before_withdraw_modal_opener
 *
 * Allows to render some content before the button to open the withdraw modal.
 */
do_action( 'yith_wcaf_before_withdraw_modal_opener' );
?>

<a href="#" role="button" class="button" id="withdraw_modal_opener">
	<?php echo esc_html_x( 'Request withdrawal', '[FRONTEND] Payments tab', 'yith-woocommerce-affiliates' ); ?>
</a>

<?php
/**
 * DO_ACTION: yith_wcaf_after_withdraw_modal_opener
 *
 * Allows to render some content after the button to open the withdraw modal.
 */
do_action( 'yith_wcaf_after_withdraw_modal_opener' );
?>
