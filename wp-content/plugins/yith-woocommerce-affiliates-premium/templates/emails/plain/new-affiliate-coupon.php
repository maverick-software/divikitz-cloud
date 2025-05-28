<?php
/**
 * New affiliate coupon email template plain
 *
 * @author YITH
 * @package YITH\Affiliates\Templates
 * @version 1.0.0
 */

/**
 * Template variables:
 *
 * @var $email_heading string
 * @var $email         WC_Email
 * @var $coupon        WC_Coupon
 * @var $affiliate     YITH_WCAF_Affiliate
 * @var $user          WP_User
 * @var $display_name  string
 */

if ( ! defined( 'YITH_WCAF' ) ) {
	exit;
} // Exit if accessed directly

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

// translators: 1. Affiliate formatted name.
echo esc_html( sprintf( _x( 'Hi %s,', '[EMAILS] New Affiliate coupon email', 'yith-woocommerce-affiliates' ), $display_name ) );
echo "\n\n";

echo "{content_text}\n\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
