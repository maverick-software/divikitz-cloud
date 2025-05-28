/**
 * ywsbs-order-editor.js
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH WooCommerce Subscription
 * @version 2.0.0
 */
/* global ywsbs_order_admin */
jQuery( function ( $ ) {

	/**
	 * ORDER EDITOR TITLE
	 */
	if ( $( document ).find( '.woocommerce-order-data__meta' ).length > 0 ) {
		$( '<div class="ywsbs-order-label">' + ywsbs_order_admin.order_label + '</div>' ).insertBefore( '.woocommerce-order-data__meta' );
	}

	$( '#order_status' ).on( 'change', function () {
		var status = $( '#original_post_status' ).val();
		if ( $( this ).val() !== status && 'wc-on-hold' === status ) {

			window.alert( ywsbs_order_admin.warning_message )

		}
	} );

} );