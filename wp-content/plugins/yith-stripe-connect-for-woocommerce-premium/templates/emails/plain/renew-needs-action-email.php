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

echo "= " . $email_heading . " =\n\n";
echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
?>

<?php
echo sprintf( __( 'Hi %s,', 'yith-woocommerce-stripe' ), $username )
?>

{opening_text}

<?php echo sprintf( __( 'Confirm payment (%s)', 'yith-woocommerce-stripe' ), $pay_renew_url ) ?>

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
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/** APPLY_FILTERS: woocommerce_email_footer_text
*
* Filter the footer of the renew email.
*
* @param $option Default 'woocommerce_email_footer_text' option.
*/
echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
