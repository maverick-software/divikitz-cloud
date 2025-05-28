<?php

/**
 * https://yithemes.com/themes/plugins/yith-woocommerce-ajax-product-filter/
 * Class WFACP_Compatibility_Yith_Wc_ajax_Product_Filter_Premium
 */
class WFACP_Compatibility_Yith_Wc_ajax_Product_Filter_Premium {
	public function __construct() {
		add_filter( 'wfacp_css_js_removal_paths', [ $this, 'remove_js' ] );
	}


	public function remove_js( $path ) {
		$path[] = 'yith-woocommerce-ajax-product-filter-premium';

		return $path;
	}
}

if ( class_exists( 'YITH_WCAN' ) ) {
	WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_Yith_Wc_ajax_Product_Filter_Premium(), 'yith-ajax-product-filter' );
}
