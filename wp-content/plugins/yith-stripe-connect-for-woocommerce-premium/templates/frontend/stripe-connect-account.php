<?php

/**
 * Stripe Connect account's endpoint template
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

<span class="message"> </span>
<a id="yith-sc-connect-button" href="<?php echo esc_url( $oauth_link ); ?>" class="stripe-connect <?php echo esc_attr( $button_class ); ?>">
	<span><?php echo esc_html( $button_text ); ?></span>
</a>
<br/>

<?php
if ( 0 < $count_commissions ) {
	$args        = array(
		'current_status'    => $current_status,
		'current_page'      => $current_page,
		'items_per_page'    => $items_per_page,
		'count_commissions' => $count_commissions,
		'commissions'       => $commissions,
	);

	$commissions = new YITH_Stripe_Connect_Commissions();
	$commissions->enqueue_scripts();

	yith_wcsc_get_template( 'commissions-panel', $args, 'common' );
}
