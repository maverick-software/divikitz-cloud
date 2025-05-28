<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Blocksy {

	public function __construct() {
		add_action( 'wfacp_internal_css', [ $this, 'internal_css' ] );
	}

	public function enable() {
		return class_exists( 'Blocksy_Manager' );
	}


	public function internal_css() {
		if ( ! $this->enable() ) {
			return;
		}

		echo '<style>';
		echo '.checkout_coupon p:first-child {display: block;}';
		echo 'form#checkout {display: block;}';
		echo '.payment_methods>li>label{height: auto;}';
		echo 'body #wfacp-e-form form.checkout_coupon.woocommerce-form-coupon input#coupon_code{padding: 12px 10px;}';
		echo '.button:hover{transform: none;}';
		echo 'body #wfacp-e-form form.checkout_coupon.woocommerce-form-coupon .button{min-height: 52px;}';
		echo 'body .wfacp_main_form.woocommerce input[type=radio]{border: 1px solid #b4b9be !important;background: #fff !important;}';
		echo 'body .wfacp_main_form.woocommerce .woocommerce-form__input[type="checkbox"]:checked{border: 1px solid #b4b9be !important;background: #fff !important;}';
		echo '</style>';

	}

}
if(!class_exists('Blocksy_Manager')){
	return;
}


WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Blocksy(), 'wfacp-blocksy' );
