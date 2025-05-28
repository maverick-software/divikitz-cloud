<?php
defined( 'ABSPATH' ) || exit;

/**
 * @var $this WFACP_admin
 */
$wfacp_id      = WFACP_Common::get_id();
$wfacp_section = WFACP_Common::get_current_step();
$wfacp_post    = get_post( $wfacp_id );

$localize_data = $this->get_localize_data();

$selected_design = $localize_data['design']['selected_type'];

if ( is_null( $wfacp_post ) ) {
	return;
}
$steps           = WFACP_Common::get_admin_menu();
$products        = WFACP_Common::get_page_product( WFACP_Common::get_id() );
$localize_data   = $this->get_localize_data();
$template_is_set = get_post_meta( $this->wfacp_id, '_wfacp_selected_design' );

$preview_url = get_the_permalink( $wfacp_id );


$box_size_class = ( isset( $_GET['wffn_funnel_ref'] ) ) ? 'wfacp_bread' : '';

$header_nav_data = array();
foreach ( $steps as $step ) {
	$href = BWF_Admin_Breadcrumbs::maybe_add_refs( add_query_arg( [
		'page'     => 'wfacp',
		'wfacp_id' => $wfacp_id,
		'section'  => $step['slug'],
	], admin_url( 'admin.php' ) ) );

	$header_nav_data[ $step['slug'] ] = array(
		'name' => $step['name'],
		'link' => $href,
	);
}

if ( class_exists( 'WFFN_Header' ) ) {
	$header_ins = new WFFN_Header();
	$header_ins->set_level_1_navigation_active( 'funnels' );

	ob_start();
	?>
    <div class="wfacp_box_size <?php if ( isset( $_GET['wffn_funnel_ref'] ) ) {
		echo "wfacp_bread";
	} ?>">
        <div class="wfacp_head_m wfacp_tl">
            <div class="wfacp_head_mr" data-status="live">
                <div class="funnel_state_toggle wfacp_toggle_btn">
                    <input name="offer_state" id="state_<?php echo $wfacp_id; ?>" data-id="<?php echo $wfacp_id; ?>" type="checkbox" class="wfacp-tgl wfacp-tgl-ios wfacp_checkout_page_status" <?php echo( $wfacp_post->post_status == 'publish' ? 'checked="checked"' : '' ); ?>>
                    <label for="state_<?php echo $wfacp_id; ?>" class="wfacp-tgl-btn wfacp-tgl-btn-small"></label>
                </div>
            </div>
        </div>
    </div>
	<?php
	$publisher = ob_get_contents();
	ob_end_clean();
	$header_ins->set_level_2_side_type( 'both' );
	$header_ins->set_level_2_right_html( $publisher );


	ob_start();
	?>
    <div class="bwf_breadcrumb">
        <div class="bwf_before_bre"></div>
		<?php BWF_Admin_Breadcrumbs::render_top_bar(); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <div class="bwf_after_bre" style="margin-top: 5px;">
            <a href="javascript:void(0)" data-izimodal-open="#modal-checkout-page" data-iziModal-title="New Checkout page" data-izimodal-transitionin="fadeInDown">
				<?php
				include plugin_dir_path( WFACP_PLUGIN_FILE ) . 'admin/assets/img/icons/edit.svg';
				?>
            </a>
            <a href="<?php echo $preview_url; ?>" target="_blank" class="wfacp-preview wfacp-preview-admin" style="    margin-left: 8px;">
				<?php

				include plugin_dir_path( WFACP_PLUGIN_FILE ) . 'admin/assets/img/icons/view.svg';

				?>
            </a>
        </div>
    </div>
	<?php
	$breadcrumb = ob_get_contents();
	ob_end_clean();
	$header_ins->set_level_2_post_title( $breadcrumb ); //used set_level_2_post_title as left inner html

	$header_ins->set_level_2_side_navigation( $header_nav_data ); //set header 2nd level navigation
	$header_ins->set_level_2_side_navigation_active( $wfacp_section ); // active navigation

	echo $header_ins->render();
} else {
	BWF_Admin_Breadcrumbs::render_sticky_bar();
}

?>
<div class="wfacp_body wfacp_funnels" id="wfacp_control" data-id="<?php echo $wfacp_id; ?>" data-template-set="<?php echo empty( $template_is_set ) ? 'yes' : '' ?>">
    <div id="poststuff">
        <div class="wfacp_inside">
			<?php if ( ! class_exists( 'WFFN_Header' ) ) : ?>
                <div class="wfacp_fixed_header">
                    <div class="wfacp_box_size <?php echo $box_size_class; ?>">
                        <div class="wfacp_head_m wfacp_tl">
                            <div class="wfacp_head_mr" data-status="live">
                                <div class="funnel_state_toggle wfacp_toggle_btn">
                                    <input name="offer_state" id="state_<?php echo $wfacp_id; ?>" data-id="<?php echo $wfacp_id; ?>" type="checkbox" class="wfacp-tgl wfacp-tgl-ios wfacp_checkout_page_status" <?php echo( $wfacp_post->post_status == 'publish' ? 'checked="checked"' : '' ); ?>>
                                    <label for="state_<?php echo $wfacp_id; ?>" class="wfacp-tgl-btn wfacp-tgl-btn-small"></label>
                                </div>
                            </div>
                            <div class="wfacp_head_ml">
								<?php BWF_Admin_Breadcrumbs::render(); ?>
                                <a href="javascript:void(0)" data-izimodal-open="#modal-checkout-page" data-iziModal-title="New Checkout page" data-izimodal-transitionin="fadeInDown">
                                    <span class="dashicons dashicons-edit"></span>
                                    <span><?php _e( 'Edit', 'wordpress' ) ?></span>
                                </a>
                                <a href="<?php echo $preview_url; ?>" target="_blank" class="wfacp-preview wfacp-preview-admin">
                                    <i class="dashicons dashicons-visibility wfacp-dash-eye"></i>
                                    <span class="preview_text"><?php _e( 'View', 'wordpress' ) ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bwf_menu_list_primary">
                    <ul>
						<?php
						foreach ( $steps as $step ) {
							$href = BWF_Admin_Breadcrumbs::maybe_add_refs( add_query_arg( [
								'page'     => 'wfacp',
								'wfacp_id' => $wfacp_id,
								'section'  => $step['slug'],
							], admin_url( 'admin.php' ) ) );
							?>
                            <li class="<?php echo( $step['slug'] == $wfacp_section ? 'active' : '' ); ?>">
                                <a data-slug="<?php echo $step['slug']; ?>" href="<?php echo $href; ?>">
									<?php echo $step['name']; ?>
                                </a>
                            </li>
							<?php
						}
						?>
                    </ul>
                </div>
			<?php endif; ?>

            <div class="wfacp_wrap wfacp_box_size <?php echo $wfacp_section; ?>">
                <div class="wfacp_loader"><span class="spinner"></span></div>
				<?php include_once $this->current_section; ?>
				<?php include_once __DIR__ . '/global/model.php'; ?>
            </div>
        </div>
    </div>
</div>
