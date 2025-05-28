<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Name: Braintree For WooCommerce
 * Class WFACP_Compatibility_With_Woo_Payment_Gateway
 * https://wordpress.org/plugins/woo-payment-gateway/
 */
class WFACP_Compatibility_With_Woo_Payment_Gateway {

	private $gateways = [];

	public function __construct() {
		add_filter( 'wfacp_body_class', [ $this, 'add_body_class' ], 999 );
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'remove_action' ], 999 );
		add_filter( 'woocommerce_update_order_review_fragments', [ $this, 'add_fragment' ], 150, 2 );
		add_action( 'wfacp_intialize_template_by_ajax', function () {
			//for when our fragments calls running
			add_filter( 'woocommerce_update_order_review_fragments', [ $this, 'add_fragment' ], 99, 2 );
		}, 10 );

		add_filter( 'wfacp_smart_buttons', [ $this, 'add_buttons' ], 16 );
		add_action( 'wfacp_smart_button_container_woo_braintree', [ $this, 'print_smart_buttons' ] );
		add_action( 'wfacp_internal_css', [ $this, 'some_css' ] );
	}

	public function add_buttons( $buttons ) {

		if ( ! class_exists( 'WC_Braintree_Manager' ) ) {
			return $buttons;
		}
		if ( ! is_checkout() ) {
			return $buttons;
		}

		foreach ( WC()->payment_gateways()->get_available_payment_gateways() as $id => $gateway ) {
			if ( $gateway->supports( 'wc_braintree_banner_checkout' ) && $gateway->banner_checkout_enabled() ) {
				$this->gateways[ $id ] = $gateway;
			}
		}


		if ( ! empty( $this->gateways ) ) {
			remove_action( 'woocommerce_checkout_before_customer_details', 'wc_braintree_banner_checkout_template' );
			$buttons['woo_braintree'] = [
				'iframe' => true,
				'name'   => __( 'Braintree' ),
			];
		}

		return $buttons;
	}

	public function print_smart_buttons() {
		if ( ! empty( $this->gateways ) && function_exists( 'wc_braintree_banner_checkout_template' ) ) {

			foreach ( $this->gateways as $gateway ) :?>
                <div class="wc-braintree-banner-gateway wc_braintree_banner_gateway_<?php echo esc_attr( $gateway->id ); ?>">
					<?php $gateway->banner_fields(); ?>
                </div>
			<?php
			endforeach;
		}
	}


	public function add_body_class( $class ) {
		if ( function_exists( 'requireBraintreeProDependencies' ) ) {
			$class[] = 'bfwc-body';
		}

		return $class;
	}

	public function remove_action() {
		if ( class_exists( 'WC_Braintree_Field_Manager' ) ) {
			remove_action( 'woocommerce_review_order_after_order_total', [ 'WC_Braintree_Field_Manager', 'output_checkout_fields' ] );
			add_action( 'woocommerce_checkout_before_customer_details', [ $this, 'print_order_total_fields' ] );
		}

	}

	public function print_order_total_fields() {
		if ( class_exists( 'WC_Braintree_Field_Manager' ) ) {
			echo '<div id="woo-payment-gatewway-wfacp-payment-fields">';
			WC_Braintree_Field_Manager::output_checkout_fields();
			echo '</div>';
		}
	}

	public function add_fragment( $fragments ) {
		if ( class_exists( 'WC_Braintree_Field_Manager' ) && isset( WFACP_Common::$post_data['_wfacp_post_id'] ) ) {
			ob_start();
			$this->print_order_total_fields();
			$fragments['#woo-payment-gatewway-wfacp-payment-fields'] = ob_get_clean();
		}

		return $fragments;
	}

	public function some_css() {
		?>
        <style>
            .wfacp_smart_button_container .wc-braintree-banner-gateway {
                display: inline-block;
                vertical-align: top;
            }
        </style>
		<?php
	}
}


if ( ! class_exists( 'WC_Braintree_Manager' ) ) {
	return;
}
WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Woo_Payment_Gateway(), 'woo-payment-gateway' );



