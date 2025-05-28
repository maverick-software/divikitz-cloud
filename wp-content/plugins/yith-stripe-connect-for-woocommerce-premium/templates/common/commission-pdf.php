<div data-commission_id="<?php echo esc_html( $id_commission ); ?>" class="wc-backbone-modal yith-wcsc-commission-view">
    <div class="wc-backbone-modal-content">
        <section class="wc-backbone-modal-main" role="main">
            <header class="wc-backbone-modal-header">
                <h1><?php echo esc_html( '#' . $id_commission . ' ' . $display_name ); ?></h1>
            </header>
            <article class="yith_backbone_modal_article">
                <div class="status_details">
                    <h2><?php echo __( 'Status details', 'yith-stripe-connect-for-woocommerce' ); ?></h2>
                    <div class="commission_item commission_item_status">
                            <span>
                                <strong><?php echo __( 'Commission status', 'yith-stripe-connect-for-woocommerce' ); ?>: </strong>
                            </span>
                        <span>
	                            <?php echo esc_html( $commission_status_text ); ?>
                            </span>
                    </div>
                    <div class="commission_item">
                            <span>
                                <strong><?php echo __( 'Commission date', 'yith-stripe-connect-for-woocommerce' ); ?>: </strong>
                            </span>
                        <span>
	                            <?php echo esc_html( $purchased_date ); ?>
                            </span>
                    </div>
					<?php if ( $note ) { ?>
                        <div class="commission_item">
                            <span>
                                <strong><?php echo __( 'Commission note', 'yith-stripe-connect-for-woocommerce' ); ?>: </strong>
                            </span>
                            <span>
	                            <?php echo esc_html( $note ); ?>
                            </span>
                        </div>
					<?php } ?>
                    <div class="commission_item">
                        <div>
                                    <span class="commission_receiver_status_label">
                                        <strong><?php echo __( 'Stripe connect status', 'yith-stripe-connect-for-woocommerce' ); ?> </strong>
                                    </span>
                            <image class="_commission" src="<?php echo YITH_WCSC_ASSETS_URL . 'images/sc-icon-' . $receiver_status . '.svg' ?>" title="<?php echo esc_html( $receiver_status ); ?>"></image>
                        </div>
                        <div>
                                    <span class="commission_receiver_status">
                                        <?php if ( $receiver_status == 'connect' ) {
	                                        echo esc_html( sprintf( __( '%s is currently connected', 'yith-stripe-connect-for-woocommerce' ), $display_name ) );
                                        } else {
	                                        echo esc_html( sprintf( __( '%s is currently disconnected', 'yith-stripe-connect-for-woocommerce' ), $display_name ) );
                                        } ?>
                                    </span>
                        </div>

                    </div>
                </div>
                <div class="order_details">
                    <h2><?php echo __( 'Order details', 'yith-stripe-connect-for-woocommerce' ); ?></h2>
                    <div class="commission_item">
                            <span>
                                <strong><?php echo __( 'Order number', 'yith-stripe-connect-for-woocommerce' ); ?>: </strong>
                            </span>
                        <span>
	                            #<?php echo esc_html( $order_id ); ?>
                            </span>
                    </div>
                    <div class="commission_item">
                            <span>
                                <strong><?php echo __( 'Order status', 'yith-stripe-connect-for-woocommerce' ); ?>: </strong>
                            </span>
                        <span>
	                            <?php echo esc_html( $order_status ); ?>
                            </span>
                    </div>
                    <div class="commission_item">
                            <span>
                                <strong><?php echo __( 'Order date', 'yith-stripe-connect-for-woocommerce' ); ?>: </strong>
                            </span>
                        <span>
	                            <?php echo esc_html( $order_date ); ?>
                            </span>
                    </div>
                    <div class="commission_item">
                            <span>
                                <strong><?php echo __( 'Order total', 'yith-stripe-connect-for-woocommerce' ); ?>: </strong>
                            </span>
                        <span>
	                            <?php echo esc_html( $order_total ) ?>
                            </span>
                    </div>
                </div>
                <?php if ( $affiliate_text ) { ?>
                    <div class="affiliates_commission_details">
                        <h2><?php echo __( 'Affiliates\' commission details', 'yith-stripe-connect-for-woocommerce' ); ?></h2>
                        <div class="commission_item">
                            <span>
                                <strong><?php echo __( 'Affiliate payment ID', 'yith-stripe-connect-for-woocommerce' ); ?> :</strong>
                            </span>
                            <span>
                             <?php
	                             echo esc_html( '#' . $affiliate_payment_id );
                             ?>
                         </span>
                        </div>
                        <div class="commission_item">
                             <span>
                                 <strong><?php echo __( 'Affiliate commission ID', 'yith-stripe-connect-for-woocommerce' ); ?> :</strong>
                             </span>
                            <span>
                             <?php
	                             echo esc_html( '#' . $affiliate_commission_id );
                             ?>
                            </span>
                        </div>
                    </div>
                    <?php } ?>
                <div class="commission_details">
                    <table class="commission_view_table" cellspacing="0">
                        <thead>
                        <tr>
                            <th class="view_table_product_column"><?php echo __( 'Product', 'yith-stripe-connect-for-woocommerce' ); ?></th>
                            <th class="view_table_quantity_column"><?php echo __( 'Quantity', 'yith-stripe-connect-for-woocommerce' ); ?></th>
                            <th class="view_table_cost_column"><?php echo __( 'Cost', 'yith-stripe-connect-for-woocommerce' ); ?></th>
                            <th class="view_table_rate_column"><?php echo _x( 'Rate', 'The type of commission applied', 'yith-stripe-connect-for-woocommerce' ); ?></th>
                            <th class="view_table_type_column"><?php echo _x( 'Type', 'The type of commission applied', 'yith-stripe-connect-for-woocommerce' ); ?></th>
                            <th class="view_table_commission_column"><?php echo __( 'Commission', 'yith-stripe-connect-for-woocommerce' ); ?></th>
                        </tr>
                        <tbody>
                        <tr>
                            <td class="view_table_product_column"><?php echo esc_html( $product_title ); ?></td>
                            <td class="view_table_quantity_column"><?php echo esc_html( $product_qty ); ?></td>
                            <td class="view_table_cost_column"><?php echo esc_html( $cost ); ?></td>
                            <td class="view_table_rate_column"><?php echo esc_html( $commission_rate ); ?></td>
                            <td class="view_table_type_column"><?php echo esc_html( $commission_type ); ?></td>
                            <td class="view_table_commission_column"><?php echo esc_html( $commission_total ); ?></td>
                        </tr>
                        </tbody>
                        </thead>
                    </table>
                </div>
            </article>
        </section>
    </div>
</div>