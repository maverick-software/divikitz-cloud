<?php
/**
 * New affiliate email template plain
 *
 * @author YITH
 * @package YITH\Affiliates\Templates
 * @version 1.0.0
 */

/**
 * Template variables:
 *
 * @var $email_heading          string
 * @var $email                  WC_Email
 * @var $affiliate              YITH_WCAF_Affiliate
 * @var $affiliate_referral_url string
 */

if ( ! defined( 'YITH_WCAF' ) ) {
	exit;
} // Exit if accessed directly


$user = $affiliate->get_user();

if ( ! $user ) {
	return;
}

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo esc_html_x( 'A new affiliate account has been registered for user', '[EMAILS] New affiliate email', 'yith-woocommerce-affiliates' );
echo ' ';
echo esc_html( $affiliate->get_formatted_name() );
echo "\n";

echo "\n----------------------------------------\n\n";

echo esc_html_x( 'Username:', '[EMAILS] New affiliate email', 'yith-woocommerce-affiliates' );
echo ' ';
echo esc_html( $user->user_login );
echo "\n";

echo esc_html_x( 'User email:', '[EMAILS] New affiliate email', 'yith-woocommerce-affiliates' );
echo ' ';
echo esc_html( $user->user_email );
echo "\n";

echo esc_html_x( 'Affiliate token:', '[EMAILS] New affiliate email', 'yith-woocommerce-affiliates' );
echo ' ';
echo esc_html( $affiliate->get_token() );
echo '(' . esc_url( $affiliate->get_referral_url() ) . ')';
echo "\n";

$payment_email = $affiliate->get_payment_email();

echo esc_html_x( 'Payment email:', '[EMAILS] New affiliate email', 'yith-woocommerce-affiliates' );
echo ' ';
echo $payment_email ? esc_html( $payment_email ) : esc_html_x( 'N/A', '[EMAILS] New affiliate email', 'yith-woocommerce-affiliates' );
echo "\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );

