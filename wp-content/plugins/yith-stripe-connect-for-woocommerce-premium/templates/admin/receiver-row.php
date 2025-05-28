<?php
$all_products = isset( $receiver_row['all_products'] ) ? $receiver_row['all_products'] : '';
?>
<tr id="<?php echo $index ?>"
    class="yith_wcsc_receiver_row">
    <input type="hidden"
           name="_receivers[<?php echo $index ?>][ID]"
           id="_receivers_<?php echo $index ?>_ID"
           class="_receiver"
           value="<?php echo isset( $receiver_row['ID'] ) ? $receiver_row['ID'] : 'new' ?>"
           placeholder="">
    <input type="hidden"
           name="_receivers[<?php echo $index ?>][order]"
           id="_receivers_<?php echo $index ?>_receiver_order"
           class="_receiver _receiver_order"
           value="<?php echo isset( $receiver_row['order'] ) ? $receiver_row['order'] : $index ?>"
           placeholder="">
    <td class="drag-icon">
        <i class="dashicons dashicons-move"></i>
    </td>
    <td class="form-field _receivers_<?php echo $index ?>_disabled_field option-disabled">
        <input type="checkbox" class="checkbox _receiver" style=""
               name="_receivers[<?php echo $index ?>][disabled]"
               id="_receivers_<?php echo $index ?>_disabled"
			<?php
			checked( isset( $receiver_row['disabled'] ) && '1' == $receiver_row['disabled'] ) ?> >
    </td>
    <td class="form-field _receivers_<?php echo $index ?>_user_field option-user">
		<?php
		$user_id = isset( $receiver_row['user_id'] ) ? $receiver_row['user_id'] : '';

		$user = get_user_by( 'id', $user_id );

		$data_selected = $user ? array( $receiver_row['user_id'] => $user->data->display_name . ' (#' . $user_id . ' - ' . $user->data->user_email . ')' ) : array();

		$args = array(
			'class'            => 'wc-customer-search _receiver',
			'id'               => '_receivers_' . $index . '_user_id',
			'name'             => '_receivers[' . $index . '][user_id]',
			'data-allow_clear' => true,
			'data-selected'    => $data_selected,
			'data-multiple'    => false,
			'data-placeholder' => __( 'Search user', 'yith-stripe-connect-for-woocommerce' ),
			'value'            => $user_id,
			'style'            => 'min-width:250px; max-width = 250px',

		);
		yit_add_select2_fields( $args );
		?>
    </td>
    <td class="form-field _receivers_<?php echo $index ?>_commission_value option-commission-value">
        <input type="number" style=""
               name="_receivers[<?php echo $index ?>][commission_value]"
               id="_receivers_<?php echo $index ?>_commission_value"
               class="_receiver"
               value="<?php echo isset( $receiver_row['commission_value'] ) ? $receiver_row['commission_value'] : '' ?>"
               placeholder="" step="0.01">
        <select style=""
                name="_receivers[<?php echo $index ?>][commission_type]"
                id="_receivers_<?php echo $index ?>_commission_type"
                class="_receiver">
            <option value="fixed"
				<?php if ( isset( $receiver_row['commission_type'] ) ) {
					if ( 'fixed' == $receiver_row['commission_type'] ) {
						echo 'selected';
					}
				} ?>
            ><?php echo __( 'Fixed', 'yith-stripe-connect-for-woocommerce' ) ?></option>
            <option value="percentage"
				<?php if ( isset( $receiver_row['commission_type'] ) ) {
					if ( 'percentage' == $receiver_row['commission_type'] ) {
						echo 'selected';
					}
				} ?>
            ><?php echo __( '%', 'yith-stripe-connect-for-woocommerce' ) ?></option>
        </select>
    </td>

	<?php
	$product_id = isset( $receiver_row['product_id'] ) ? $receiver_row['product_id'] : '';
	if ( 'product_edit_page' != $context ) {
		?>
        <td class="form-field _receivers_<?php echo $index ?>_product_field option-product">

            <a href="" class="_receiver_all_products_button button <?php if ( $all_products ) {
				echo 'yith_wcsc_enabled';
			} ?>">All</a>

            <input type="text" class="_receiver _receiver_all_products" style=""
                   name="_receivers[<?php echo $index ?>][all_products]"
                   id="_receivers_<?php echo $index ?>_all_products"
                   value="<?php echo $all_products; ?>">
			<?php


			$product = wc_get_product( $product_id );

			$data_selected = $product ? array( $product_id => wp_strip_all_tags( $product->get_formatted_name() ) ) : array();

			$args = array(
				'class'            => 'wc-product-search _receiver_product_search _receiver',
				'id'               => '_receivers_' . $index . '_product_id',
				'name'             => '_receivers[' . $index . '][product_id]',
				'data-allow_clear' => true,
				'data-selected'    => $data_selected,
				'data-multiple'    => false,
				'data-placeholder' => __( 'Search product', 'yith-stripe-connect-for-woocommerce' ),
				'value'            => $product_id,
				'style'            => 'min-width:250px; max-width = 250px',

			);
			yit_add_select2_fields( $args );
			?>
        </td>

	<?php } else {
		?>
        <input type="hidden" class="_receiver _receiver_all_products" style=""
               name="_receivers[<?php echo $index ?>][all_products]"
               id="_receivers_<?php echo $index ?>_all_products"
               value="<?php echo $all_products; ?>">

        <input type="hidden" class="_receiver_product_search _receiver" style=""
               name="<?php echo '_receivers[' . $index . '][product_id]' ?>"
               id="<?php echo '_receivers_' . $index . '_product_id' ?>"
               value="<?php echo $product_id ?>">

		<?php

	} ?>
    <td class="form-field _receivers_<?php echo $index ?>_stripe_id option-stripe-id">
		<?php
		?>
        <input type="text" style=""
               name="_receivers[<?php echo $index ?>][stripe_id]"
               id="_receivers_<?php echo $index ?>_stripe_id"
               class="_receiver field_stripe_id <?php if ( isset( $receiver_row['status_receiver'] ) ) {
			       echo $receiver_row['status_receiver'];
		       } else {
			       echo 'dissconnect';
		       } ?>"
               value="<?php echo ! empty( $receiver_row['stripe_id'] ) ? $receiver_row['stripe_id'] : __( 'User disconnected', 'yith-stripe-connect-for-woocommerce' ); ?>"
               placeholder="" disabled>
    </td>

    <td class="option-stripe-status">

		<?php
		$receiver_status_text = __( 'Disconnected', 'yith-stripe-connect-for-woocommerce' );
		$status_receiver      = 'disconnect';

		if ( isset( $receiver_row['status_receiver'] ) ) {
			if ( 'connect' == $receiver_row['status_receiver'] ) {
				$receiver_status_text = __( 'Connected', 'yith-stripe-connect-for-woocommerce' );
				$status_receiver      = 'connect';
			}
		}
		?>
        <image class="_commission" src="<?php echo YITH_WCSC_ASSETS_URL . 'images/sc-icon-' . $status_receiver . '.svg' ?>" title="<?php echo $receiver_status_text; ?>"></image>

    </td>
    <td class="option-actions">
        <button class="button remove_receiver"><?php echo __( 'Remove', 'yith-stripe-connect-for-woocommerce' ) ?></button>
		<?php
		if ( 'product_edit_page' == $context ) {
			if ( $all_products ) {
				?>
                <span class="dashicons dashicons-warning" title="<?php echo __( 'This receiver has all products assigned',
                    'yith-stripe-connect-for-woocommerce' ); ?>"></span>
				<?php
			}

		}
		?>
    </td>
</tr>