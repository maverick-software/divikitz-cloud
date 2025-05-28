<!-- Nando told me that we need type of view that allow us display ticket like a invoice to be printed on PDF, CSV.
I think that we could use this view for another options like display better the commission info or add features...
 Similar that WC do with the new Orders on 3.3.0 -->

<script type="text/template" id="tmpl-yith-wcsc-modal-view-commission">
    <div data-commission_id="<?php echo esc_html( '{{ data.id_commission }}' ); ?>" class="wc-backbone-modal yith-wcsc-commission-view">
        <div class="wc-backbone-modal-content">
            <section class="wc-backbone-modal-main" role="main">
                <header class="wc-backbone-modal-header <?php if ( ! is_admin() ) {
					echo 'frontend';
				} ?>">
                    <mark class="commission_status_mark _commission commission_status commission_status_<?php echo esc_html( '{{data.commission_status}}' ); ?>">
                        <span class="" title="<?php echo esc_html( '{{data.commission_status_text}}' ); ?>"><?php echo esc_html( '{{data.commission_status_text}}' ); ?></span>
                    </mark>
                    <h1><?php echo esc_html( '#{{ data.id_commission }} {{data.display_name}}' ); ?></h1>
                    <button class="modal-close modal-close-link dashicons dashicons-no-alt">
                        <span class="screen-reader-text"><?php esc_html_e( 'Close modal panel', 'yith-stripe-connect-for-woocommerce' ); ?></span>
                    </button>
                </header>
                <article class="yith_backbone_modal_article">
                    <div class="status_details">
                        <h2><?php echo __( 'Status details', 'yith-stripe-connect-for-woocommerce' ); ?></h2>
                        <div class="commission_item commission_item_status">
                            <span>
                                <strong><?php echo __( 'Commission status', 'yith-stripe-connect-for-woocommerce' ); ?>: </strong>
                            </span>
                            <span>
	                            <?php echo esc_html( '{{data.commission_status_text}}' ); ?>
                            </span>
                        </div>
                        <div class="commission_item">
                            <span>
                                <strong><?php echo __( 'Commission date', 'yith-stripe-connect-for-woocommerce' ); ?>: </strong>
                            </span>
                            <span>
	                            <?php echo esc_html( '{{data.purchased_date}}' ); ?>
                            </span>
                        </div>
                        <# if ( data.note ) { #>
                            <div class="commission_item">
                            <span>
                                <strong><?php echo __( 'Commission note', 'yith-stripe-connect-for-woocommerce' ); ?>: </strong>
                            </span>
                                <span>
	                            <?php echo esc_html( '{{{ data.note }}}' ); ?>
                            </span>
                            </div>
                            <# } #>
                                <div class="commission_item">
                                    <div>
                                    <span class="commission_receiver_status_label">
                                        <strong><?php echo __( 'Stripe connect status', 'yith-stripe-connect-for-woocommerce' ); ?> </strong>
                                    </span>
                                        <image class="_commission" src="<?php echo YITH_WCSC_ASSETS_URL . 'images/sc-icon-{{data.receiver_status}}.svg' ?>" title="<?php echo esc_html( '{{data.receiver_status}}' ); ?>"></image>
                                    </div>
                                    <div>
                                    <span class="commission_receiver_status">
                                        <# if ( data.receiver_status == 'connect' ) { #>
									    <?php echo esc_html( sprintf( __( '%s is currently connected', 'yith-stripe-connect-for-woocommerce' ), '{{ data.display_name }}' ) ); ?>
                                            <# } else {#>
									    <?php echo esc_html( sprintf( __( '%s is currently disconnected', 'yith-stripe-connect-for-woocommerce' ), '{{ data.display_name }}' ) ); ?>
                                                <# } #>
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
	                            #<?php echo esc_html( '{{data.order_id}}' ); ?>
                            </span>
                        </div>
                        <div class="commission_item">
                            <span>
                                <strong><?php echo __( 'Order status', 'yith-stripe-connect-for-woocommerce' ); ?>: </strong>
                            </span>
                            <span>
	                            <?php echo esc_html( '{{data.order_status}}' ); ?>
                            </span>
                        </div>
                        <div class="commission_item">
                            <span>
                                <strong><?php echo __( 'Order date', 'yith-stripe-connect-for-woocommerce' ); ?>: </strong>
                            </span>
                            <span>
	                            <?php echo esc_html( '{{data.order_date}}' ); ?>
                            </span>
                        </div>
                        <div class="commission_item">
                            <span>
                                <strong><?php echo __( 'Order total', 'yith-stripe-connect-for-woocommerce' ); ?>: </strong>
                            </span>
                            <span>
	                            <?php echo esc_html( '{{{data.order_total}}}' ) ?>
                            </span>
                        </div>
                    </div>
                    <# if ( data.affiliate_text ) { #>
                        <div class="affiliates_commission_details">
                            <h2><?php echo __( 'Affiliates Commission details', 'yith-stripe-connect-for-woocommerce' ); ?></h2>

							<?php echo esc_html( '{{data.affiliate_text}}' ); ?>

                            <div class="commission_item">
                                <span>
                                    <strong><?php echo __( 'Affiliate payment ID', 'yith-stripe-connect-for-woocommerce' ); ?> :</strong>
                                </span>
                                <span>
                                <?php if ( is_admin() ) {
	                                echo esc_html( '{{{ data.affiliate_payment_link }}}' );
                                } else {
	                                echo esc_html( '#{{ data.affiliate_payment_id }}' );
                                } ?>
                                </span>
                            </div>
                            <div class="commission_item">
                                <span>
                                    <strong><?php echo __( 'Affiliate commission ID', 'yith-stripe-connect-for-woocommerce' ); ?> :</strong>
                                </span>
                                <span>
                                <?php if ( is_admin() ) {
	                                echo esc_html( '{{{ data.affiliate_commission_link }}}' );
                                } else {
	                                echo esc_html( '#{{ data.affiliate_commission_id }}' );
                                } ?>
                                </span>
                            </div>
                        </div>
                        <# } #>
                            <# if ( data.multivendor_text ) { #>
                                <div class="multivendor_commission_details">
                                    <h2><?php echo __( 'Multi Vendor Commission details', 'yith-stripe-connect-for-woocommerce' ); ?></h2>

									<?php echo esc_html( '{{data.multivendor_text}}' ); ?>

                                    <div class="commission_item">
                                    <span>
                                        <strong><?php echo __( 'Multivendor commission ID', 'yith-stripe-connect-for-woocommerce' ); ?> :</strong>
                                    </span>
                                        <span>
                                        <?php if ( is_admin() ) {
	                                        echo esc_html( '{{{ data.multivendor_commision_link }}}' );
                                        } else {
	                                        echo esc_html( '#{{ data.multivendor_commission_id }}' );
                                        } ?>
                                        </span>
                                    </div>
                                </div>
                                <# } #>
                                    <div class="commission_details">
                                        <table class="commission_view_table <?php if ( ! is_admin() ) {
											echo 'frontend';
										} ?>" cellspacing="0">
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
                                                <td class="view_table_product_column"><?php echo esc_html( '{{data.product_title}}' ); ?></td>
                                                <td class="view_table_quantity_column"><?php echo esc_html( '{{data.product_qty}}' ); ?></td>
                                                <td class="view_table_cost_column"><?php echo esc_html( '{{{data.cost}}}' ); ?></td>
                                                <td class="view_table_rate_column"><?php echo esc_html( '{{data.commission_rate}}' ); ?></td>
                                                <td class="view_table_type_column"><?php echo esc_html( '{{data.commission_type}}' ); ?></td>
                                                <td class="view_table_commission_column"><?php echo esc_html( '{{{data.commission_total}}}' ); ?></td>
                                            </tr>
                                            </tbody>
                                            </thead>
                                        </table>
                                    </div>
                </article>
                <footer>
                    <div class="inner">
                        <div class="commission_view_actions">
                            <span>
                                <?php $_GET['action'] = 'print_commission';
                                ?>
                                <a class="button print_commission_button" href="<?php echo esc_url( add_query_arg( $_GET, admin_url( 'admin-ajax.php' ) ) ) . '&id_commission=' . esc_html( '{{data.id_commission}}' ); ?>" target="_blank"><?php echo __( 'Print', 'yith-stripe-connect-for-woocommerce' ); ?></a>
                            </span>
                        </div>
						<?php if ( is_admin() ){ ?>
                        <# if ( data.commission_status == 'sc_pending' | data.commission_status == 'sc_transfer_processing' | data.commission_status == 'sc_transfer_error'  ) { #>
                            <a data-receciver_status="<?php echo esc_html( '{{data.receiver_status}}' ); ?>" data-commission_status="<?php echo esc_html( '{{data.commission_status}}' ) ?>" data-order_status="<?php echo esc_html( '{{data.order_status}}' ) ?>" data-day_delay="<?php echo esc_html( '{{data.day_delay}}' ) ?>" class="button button-primary button-large pay_commission_button" href="#">Pay now</a>
                            <# } #>
								<?php } ?>

                    </div>
                </footer>
            </section>
        </div>
    </div>
    <div class="wc-backbone-modal-backdrop modal-close"></div>
</script>