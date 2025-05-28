<?php
/**
 * Renew needs action email template
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Stripe
 * @version 1.0.0
 */

/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'YITH_WCSC_VERSION' ) ) {
	exit;
} // Exit if accessed directly
?>

<?php
/** DO_ACTION: woocommerce_email_header
 *
 * Adds an action for the email header.
 *
 * @param $email_heading Email header.
 */
do_action( 'woocommerce_email_header', $email_heading );
?>

<p>
    <?php echo sprintf( __( 'Hi %s,', 'yith-woocommerce-stripe' ), $username ) ?>
</p>

{opening_text}

<p style="text-align: center;">
    <a class="button alt" href="<?php echo $pay_renew_url ?>" style="color: <?php echo $pay_renew_fg ?> !important; font-weight: normal; text-decoration: none !important; display: inline-block; background: <?php echo $pay_renew_bg ?>; border-radius: 5px; padding: 10px 20px; white-space: nowrap; margin-top: 20px; margin-bottom: 30px;"><?php _e( 'Confirm Payment', 'yith-woocommerce-stripe' ) ?></a>
</p>

<?php
/** DO_ACTION: woocommerce_email_order_details
 *
 * Adds an action for the email order details.
 *
 * @param $order         Order obj.
 * @param $sent_to_admin Bool to sent to the admin or not.
 * @param $plain_text    Plain text.
 * @param $email         Email obj.
 */
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
?>

{closing_text}

<?php
/** DO_ACTION: woocommerce_email_footer
 *
 * Adds an action for the email footer.
 */
do_action( 'woocommerce_email_footer' );
?>
