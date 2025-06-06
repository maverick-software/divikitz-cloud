
<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

?>

<ul id="tabs-navv" class="tabs-navv">
    <li><a href="#cart"><span>1</span>Shopping Cart</a></li>
    <li><a href="#billing_details"><span>2</span>Billing Details</a></li>
  </ul> 
  <div id="cart" class="tab-content">
  	<div class="col-leftside">
  		<?php echo customcart();?>
  	</div>
  	<div class="col-rightside">
	  	<div class="custom-cart-sidebar">
	  		<p class="cart-iconn"><i class="fas fa-shopping-cart"></i>
	  		<h3>Your Total Cart</h3>
	  		<?php wc_cart_totals_order_total_html(); ?>
	  		<button type="button" class="button secure-check">Secure Checkout</button>
	  		<p>Price displayed excludes any applicable taxes.</p>
	  	</div>
	  </div>
  </div>
<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">
	
	<div id="billing_details" class="tab-content">
		<div id="billing_details_filled" style="display:none;"></div>
		<?php if ( $checkout->get_checkout_fields() ) : ?>

			<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

			<div class="col2-set" id="customer_details">
				<div class="col-1 billing-area">
					<?php do_action( 'woocommerce_checkout_billing' ); ?>
				</div>
				<div class="cart-sidebar-custom side-col">

					<?php //echo woocommerce_order_review_custom();?>
					
					<div id="checkout-fields"></div>
	<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
	
	<!-- <h3 id="order_review_heading"><?php //esc_html_e( 'Your order', 'woocommerce' ); ?></h3> -->
	
	<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>



				</div>
				<div class="col-2 checkout-section">
					<?php do_action( 'woocommerce_checkout_shipping' ); ?>
				</div>
			</div>

			<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

		<?php endif; ?>
		
	</div>
	
	

	<div id="order_review" class="woocommerce-checkout-review-order">
		<?php //do_action( 'woocommerce_checkout_order_review' ); ?>
		
	</div>

	<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

</form>

<?php  do_action( 'woocommerce_after_checkout_form', $checkout ); ?>