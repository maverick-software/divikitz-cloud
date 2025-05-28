<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WFACP_Compatibility_With_WOOCS {

	public function __construct() {

		add_filter( 'wfacp_discount_regular_price_data', [ $this, 'wfacp_discount_regular_price_data' ] );
		add_filter( 'wfacp_discount_price_data', [ $this, 'wfacp_discount_price_data' ] );
		add_filter( 'wfacp_product_switcher_price_data', [ $this, 'wfacp_product_switcher_price_data' ], 10, 2 );
		add_filter( 'wfacp_show_item_price', '__return_false' );
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'draw_html' ] );
		add_action( 'wfacp_internal_css', [ $this, 'css' ] );
	}

	public function draw_html() {
		$instance = WFACP_Common::remove_actions( 'wp_footer', 'WOOCS_AUTO_SWITCHER', 'draw_html' );
		if ( $instance instanceof WOOCS_AUTO_SWITCHER ) {
			//		WOOCS_AUTO_SWITCHER
			add_action( 'wfacp_footer_before_print_scripts', array( $instance, 'draw_html' ) );
		}
	}

	public function css() {

		echo "<style> .wfacp_main_wrapper .woocs_auto_switcher li a {
                display: inline-block;
            }</style>";

	}

	public function wfacp_discount_regular_price_data( $price ) {

		if ( version_compare( WOOCS_VERSION, '1.2.9.1', '>' ) && version_compare( WOOCS_VERSION, '1.3.3', '<' ) ) {
			return $price;
		}

		return $this->alter_fixed_amount( $price );

	}

	/**
	 * @hooked into `wcct_deal_amount_fixed_amount_{$type}` | `wcct_regular_price_event_value_fixed`
	 * Modifies the amount for the fixed discount given by the admin in the currency selected.
	 *
	 * @param integer|float $price
	 *
	 * @return float
	 */
	public function alter_fixed_amount( $price, $currency = null ) {

		return $GLOBALS['WOOCS']->woocs_exchange_value( $price );
	}

	public function wfacp_discount_price_data( $price ) {

		if ( version_compare( WOOCS_VERSION, '1.2.9.1', '>' ) ) {
			return $price;
		}


		return $this->alter_fixed_amount( $price );
	}

	/**
	 * @param $price_data
	 * @param $pro WC_Product;
	 *
	 * @return mixed
	 */
	public function wfacp_product_switcher_price_data( $price_data, $pro ) {
		if ( version_compare( WOOCS_VERSION, '1.2.9.1', '>' ) && version_compare( WOOCS_VERSION, '1.3.3', '<' ) ) {
			return $price_data;
		}
		$price_data['regular_org'] = $pro->get_regular_price();
		$price_data['price']       = $pro->get_price();

		return $price_data;
	}
}

if ( class_exists( 'WOOCS' ) ) {
	WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_WOOCS(), 'woocs' );
}



