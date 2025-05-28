<?php
$colspan = 7;
if ( 'product_edit_page' != $context ) {
	$colspan = 7;
}
if ( ! $is_new_product ) {
	?>
	<form id="yith_wcsc_receiver_form"
	      method="post">
		<div id="yith_wcsc_receivers_top">
			<div class="receivers_search">
				<span><h2><?php _e( "Receiver's settings", 'yith-stripe-connect-for-woocommerce' ) ?></h2></span>
				<span class="yith_wcsc_receivers_decription">
                <?php
                echo __( 'Add new receiver conditions that will be applied when a customer make a purchase (Order status is Processing or Complete). You can add a fixed or percentage amount for each product to specific user.', 'yith-stripe-connect-for-woocommerce' );
                ?>
            </span>
			</div>
			<div class="receivers_pagination">
				<?php
				$total = ceil( $count_receivers / $items_per_page );
				if ( $total > 1 ) {
					?>
					<div>
						<?php
						echo
						paginate_links( array(
							'base'      => add_query_arg( 'current_page', '%#%' ),
							'format'    => '',
							'prev_text' => '&laquo;',
							'next_text' => '&raquo;',
							'total'     => $total,
							'current'   => $current_page
						) );
						?>
					</div>

					<?php
				}
				?>
			</div>

		</div>
		<div id="yith_wcsc_receivers_panel">
			<input type="hidden"
			       name="_data_to_save"
			       id="_data_to_save"
			       class="_data">
			<input type="hidden"
			       name="_data_to_remove"
			       id="_data_to_remove"
			       class="_data">
			<table class="receivers_options wp-list widefat">
				<thead>
				<tr>
					<th colspan="<?php echo $colspan; ?>">
						<button class="button add_receiver_button">
							<i class="dashicons dashicons-plus"></i>
							<?php _e( 'New receiver', 'yith-stripe-connect-for-woocommerce' ) ?>
						</button>
					</th>
					<th class="receiver_buttons">
						<button class="button save_receivers button-primary button-large">
							<?php _e( 'Save receivers', 'yith-stripe-connect-for-woocommerce' ) ?>
						</button>
					</th>
				</tr>
				<tr>
					<th><i class="dashicons dashicons-move"></i></th>
					<th class="option-disable"><?php _e( 'Disabled', 'yith-stripe-connect-for-woocommerce' ) ?></th>
					<th class="option-user"><?php _e( 'User', 'yith-stripe-connect-for-woocommerce' ) ?></th>
					<th class="option-commission"><?php _e( 'Commission', 'yith-stripe-connect-for-woocommerce' ) ?></th>
					<?php if ( 'product_edit_page' != $context ) { ?>
						<th class="option-product"><?php _e( 'Product', 'yith-stripe-connect-for-woocommerce' ) ?></th>
					<?php } ?>
					<th class="option-stripe-id"><?php _e( 'Stripe ID', 'yith-stripe-connect-for-woocommerce' ) ?></th>
					<th class="option-status">
						<img src="<?php echo YITH_WCSC_ASSETS_URL . 'images/sc-icon.svg' ?>"
						     title="<?php _e( 'Receiver Status', 'yith-stripe-connect-for-woocommerce' ) ?>"></img>
					</th>
					<th class="option-actions"><?php _e( 'Actions', 'yith-stripe-connect-for-woocommerce' ) ?></th>
				</tr>
				</thead>
				<tbody class="yith_wcsc_table_receivers">
				<?php
				foreach ( $receivers as $index => $receiver_row ) {
					$args = array(
						'context'      => $context,
						'index'        => $index,
						'receiver_row' => $receiver_row
					);
					yith_wcsc_get_template( 'receiver-row', $args, 'admin' );
				}
				?>
				</tbody>
				<tfoot>
				<tr>
					<th colspan="<?php echo $colspan; ?>">
						<button class="button add_receiver_button">
							<i class="dashicons dashicons-plus"></i>
							<?php _e( 'New receiver', 'yith-stripe-connect-for-woocommerce' ) ?>
						</button>
					</th>
					<th class="receiver_buttons">
						<button class="button save_receivers button-primary button-large">
							<?php _e( 'Save receivers', 'yith-stripe-connect-for-woocommerce' ) ?>
						</button>
					</th>
				</tr>
				</tfoot>
			</table>
		</div>
		<span><h3><?php echo __( 'About Receiver\'s disconnected accounts', 'yith-stripe-connect-for-woocommerce' );//@since 1.0.3
				?></h3></span>
		<span class="yith_wcsc_receivers_note">
                <?php
                echo sprintf( __( 'The receiver user <b>must connect their Stripe Account</b>. If the <b>Stripe ID</b> is <b>"User disconnected"</b>, the <b>commission can\'t be created</b>. Please read the <a href="%s" target="_blank"> Connect User Account</a> from plugin documentation.', 'yith-stripe-connect-for-woocommerce' ), 'https://docs.yithemes.com/yith-stripe-connect-for-woocommerce/premium-settings/connect-user-account/' ); //@since 1.0.3
                ?>
    </span>
	</form>

<?php
}else{
?>
<div class="yith_wcsc_receivers_new_product">
	<span><?php echo esc_html__('You must create the product before proceeding with the Receivers configuration')?></span>
</div>
<?php
}
?>