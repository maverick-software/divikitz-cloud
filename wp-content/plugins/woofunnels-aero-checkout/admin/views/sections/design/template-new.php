<?php
$data = get_option('_bwf_fb_templates');
if( !is_array($data) || count($data) === 0 ){ ?>
<div class="empty_template_error">
	<div class="bwf-c-global-error" style="display: flex; align-items: center; justify-content: center; height: 60vh;">
		<div class="bwf-c-global-error-center" style="text-align: center; background-color: rgb(255, 255, 255); width: 500px; padding: 50px;">
			<span class="dashicon dashicons dashicons-warning" style="font-size: 70px; height: 70px; width: 70px;"></span>
			<p><?php esc_html_e( 'It seems there are some technical difficulties. Press F12 to open console. Take Screenshot of the error and send it to support.', 'funnel-builder' ) ?></p>
			<a herf="#" class="button button-primary is-primary"><span class="dashicon dashicons dashicons-image-rotate"></span>&nbsp;<?php esc_html_e( 'Refresh', 'funnel-builder' ) ?></a>
		</div>
	</div>
</div>
<?php } else { ?>
<div class="wfacp_tab_container" v-if="'no'==template_active" style="display: block">

    <div class="wfacp_template_header">
        <div class="wfacp_template_header_item" v-for="(templates,type) in designs" v-if="(current_template_type==type) && (_.size(templates)>0)">
            <div class="wfacp_filter_container" v-if="undefined!==wfacp_data.design.design_type_data[type]['filters']">
                <div v-for="(name,i) in wfacp_data.design.design_type_data[type]['filters']" :data-filter-type="i" v-bind:class="'wfacp_filter_container_inner'+(1==i?' wfacp_selected_filter':'')" v-on:click="currentStepsFilter = i">
                    <div class="wfacp_template_filters">{{name}}</div>
                </div>
            </div>
        </div>

        <div class="wfacp_template_header_item wfacp_template_editor_wrap wfacp_ml_auto">
            <div class="wfacp_template_editor">
                <span class="wfacp_editor_field_label"><?php _e( 'Page Builder:', 'funnel-builder' ) ?></span>
                <div class="wfacp_editor_field wfacp_field_select_dropdown">
                    <span class="wfacp_editor_label wfacp_field_select_label" v-on:click="show_template_dropdown">
                        {{design_types[current_template_type]}}
                        <i class="dashicons dashicons-arrow-down-alt2"></i>
                    </span>
                    <div class="wfacp_field_dropdown wfacp-hide">
                        <div class="wfacp_dropdown_header">
                            <label class="wfacp_dropdown_header_label"><?php _e( 'Select Page Builder', 'funnel-builder' ) ?></label>
                        </div>
                        <div class="wfacp_dropdown_body">
                            <label v-for="(design_name,type) in design_types" v-on:click="setTemplateType(type)" class="wfacp_dropdown_fields" v-if="wfacp.tools.ol(designs[type])>0">
                                <input type="radio" name="wfacp_tabs" v-bind:value="type" :checked="current_template_type==type" />
                                <span>{{design_name}}</span>
                            </label>
                        </div>
                        <div class="wfacp_dropdown_footer">

                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>


		<section id="wfacp_content1" class="wfacp_tab-content" style="display: block" v-for="(templates,type) in designs" v-if="(current_template_type==type) && (wfacp.tools.ol(templates)>0)">
			<div class="wfacp_pick_template">
				<div v-for="(template,slug) in templates" :data-slug="slug" :data-steps="template.no_steps" class="wfacp_temp_card wfacp_single_template">

					<div class="wfacp_template_sec wfacp_build_from_scratch" v-if="template.build_from_scratch">
						<div class="wfacp_template_sec_design">
							<div class="wfacp_temp_overlay">
								<div class="wfacp_temp_middle_align">
									<div class="wfacp_add_tmp_se">
										<a href="javascript:void(0)" class="wfacp_steps_btn_add" v-on:click="triggerImport(template,slug,type,$event)">
											<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 26"><g fill="none" fill-rule="evenodd"><g fill-rule="nonzero"><g><path d="M18 11.526c-.321-.045-.653-.067-.985-.067-3.897 0-7.058 3.195-7.058 7.134 0 1.23.31 2.389.852 3.401L10.8 22H3.435C2.09 22 1 20.898 1 19.538V2.462C1 1.102 2.09 0 3.436 0h8.934v4.073c0 1.052.842 1.903 1.882 1.903H18v5.55z" transform="translate(-511 -232) translate(511 232)"></path><path fill="#FFF" fill-opacity=".25" d="M12 1l5 6h-3.328C12.747 7 12 6.146 12 5.09V1z" transform="translate(-511 -232) translate(511 232)"></path><path fill="#FFF" d="M17.983 11.255v-4.92c0-.141-.066-.271-.159-.374L12.225.168C12.121.06 11.972 0 11.825 0H2.947C1.309 0 0 1.317 0 2.932V19.47c0 1.615 1.309 2.91 2.948 2.91h7.006c1.364 2.276 3.817 3.62 6.484 3.62C20.607 26 24 22.662 24 18.549c0-1.81-.654-3.55-1.864-4.91-1.095-1.224-2.558-2.059-4.153-2.384zm-5.609-9.353l3.756 3.896h-2.436c-.726 0-1.32-.59-1.32-1.306v-2.59zM1.1 19.47V2.932c0-1.02.82-1.848 1.848-1.848h8.326v3.408c0 1.317 1.084 2.39 2.42 2.39h3.19v4.232c-.165-.005-.297-.027-.44-.027-1.92 0-3.685.726-5.016 1.864H4.444c-.303 0-.55.244-.55.542 0 .298.247.542.55.542h5.961c-.39.542-.715 1.084-.968 1.68H4.444c-.303 0-.55.244-.55.542 0 .298.247.542.55.542H9.08c-.138.542-.21 1.143-.21 1.745 0 .948.182 1.885.534 2.752H2.948c-1.029 0-1.848-.813-1.848-1.826zm15.338 5.452c-2.37 0-4.554-1.28-5.686-3.333-.512-.927-.781-1.973-.781-3.035 0-3.511 2.898-6.367 6.462-6.367.302 0 .605.022.901.06 1.53.211 2.937.959 3.96 2.108 1.034 1.16 1.6 2.65 1.6 4.2.006 3.51-2.892 6.367-6.456 6.367z" transform="translate(-511 -232) translate(511 232)"></path><path fill="#FFF" d="M4.577 11h5.846c.317 0 .577-.225.577-.5s-.26-.5-.577-.5H4.577c-.317 0-.577.225-.577.5s.26.5.577.5zM18.607 18.617h-1.204v-1.224c0-.216-.177-.393-.393-.393-.217 0-.393.177-.393.393v1.224h-1.224c-.216 0-.393.176-.393.393 0 .216.177.393.393.393h1.224v1.204c0 .216.176.393.393.393.216 0 .393-.177.393-.393v-1.204h1.204c.216 0 .393-.177.393-.393 0-.217-.177-.393-.393-.393z" transform="translate(-511 -232) translate(511 232)"></path></g></g></g></svg>
										</a>
									</div>
									<div class="wfacp_p wfacp_import_template" v-on:click="triggerImport(template,slug,type,$event)">
										<span class="wfacp_import_text"><?php esc_html_e( 'Start from scratch', 'funnel-builder' ); ?></span>
										<span class="wfacp_importing_text"> <?php esc_html_e( 'Importing...', 'funnel-builder' ) ?></span>
										<div class="wfacp_clear_20"></div>
										<span class="wfacp_import_description"><?php esc_html_e( 'Create your Page from scratch', 'funnel-builder' ); ?></span>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="wfacp_template_sec" v-else>
						<div class="wfacp_template_sec_design">
							<img v-bind:src="template.thumbnail" class="wfacp_img_temp">
							<div class="wfacp_temp_overlay">
								<div class="wfacp_temp_middle_align">
									<div class="wfacp_pro_template" v-if="template.pro && `no` === template.license_exist">
										<a href="javascript:void(0)" v-on:click="triggerPreview(template,slug,type)" class="wfacp_steps_btn wfacp_steps_btn_success"><?php _e( 'Preview', 'woofunnels-aero-checkout' ) ?></a>
										<a href="javascript:void(0)" class="wfacp_steps_btn wfacp_steps_btn_danger"><?php _e( 'Get PRO', 'woofunnels-aero-checkout' ) ?></a>
									</div>
									<div class="wfacp_pro_template" v-else>
										<a href="javascript:void(0)" v-on:click="triggerPreview(template,slug,type)" class="wfacp_steps_btn wfacp_steps_btn_success"><?php _e( 'Preview', 'woofunnels-aero-checkout' ) ?></a>
										<a href="javascript:void(0)" class="wfacp_steps_btn wfacp_import_template wfacp_btn_blue" v-on:click="triggerImport(template,slug,type,$event)"><span class="dashicons dashicons-update"></span><span class="wfacp_import_text"><?php _e( 'Import', 'woofunnels-aero-checkout' ) ?></span><span class="wfacp_importing_text"><?php _e( 'Importing...', 'woofunnels-aero-checkout' ) ?></span></a>
									</div>
								</div>
							</div>
						</div>

						<div class="wfacp_template_sec_meta" v-if="`yes` != template.build_from_scratch">
							<div class="wfacp_template_meta_left">
								<span>{{template.name}}</span>
							</div>
							<div class="wfacp_template_meta_right"></div>
						</div>
						<div v-if="true===ShouldPreview(slug,type)" class="wfacp-preview-overlay">
							<div class="wfacp_template_preview_wrap">
								<div class="wfacp_template_preview_header">
									<div>
										<img src="<?php echo esc_url( WFACP_PLUGIN_URL . '/admin/assets/img/WooFunnels-Logo.svg' ); ?>" alt="Funnel Builder for WordPress" width="148">
									</div>
									<div class="wfacp_template_viewport">
										<div class="wfacp_template_viewport_inner">
												<span class="wfacp_viewport_icons active" v-on:click="setViewport('desktop', $event)" title="Desktop Viewport">
													<span class="dashicon dashicons dashicons-desktop"></span>
												</span>
											<span class="wfacp_viewport_icons" v-on:click="setViewport('tablet', $event)" title="Tablet Viewport">
													<span class="dashicon dashicons dashicons-tablet"></span>
												</span>
											<span class="wfacp_viewport_icons" v-on:click="setViewport('mobile', $event)" title="Mobile Viewport">
													<span class="dashicon dashicons dashicons-smartphone"></span>
												</span>
										</div>
									</div>
									<div class="bwf-t-center">
										<a href="javascript:void(0)" class="button button-primary wfacp-import-template-btn is-primary wfacp_btn_blue wfacp_import_template" v-on:click="triggerImport(template,slug,type,$event)"><span class="wfacp_import_text"><?php esc_html_e( 'Import', 'funnel-builder' ) ?></span><span class="wfacp_importing_text"><?php esc_html_e( 'Importing...', 'funnel-builder' ) ?></span></a>
									</div>
									<div class="wfacp_template_preview_close">
										<button type="button" v-on:click="previewClosed()" class="components-button">
											<span class="dashicon dashicons dashicons-no-alt"></span>
										</button>
									</div>
								</div>
								<div class="wfacp_template_preview_content">
									<div class="wfacp_template_preview_inner wfacp_funnel_preview">
										<div class="woocommerce-web-preview wfacp_template_preview_frame">
											<div class="woocommerce-web-preview__iframe-wrapper">
												<div class="wfacp_global_loader">
													<div class="spinner"></div>
												</div>
												<iframe v-bind:src="getPreviewUrl(template.slug, type)" width="100%" height="100%"></iframe>
											</div>
										</div>
									</div>
									<div class="wfacp_template_preview_sidebar">
										<div v-for="(template,slug) in templates" v-on:data-slug="slug" v-if="template.build_from_scratch !=='yes' && ((`undefined`=== typeof currentStepsFilter && template.no_steps === '1' ) ||(`undefined`!==typeof currentStepsFilter) && ( template.no_steps === currentStepsFilter))">

											<label class="wfacp_template_page_options" v-bind:pre_slug="template.slug" v-on:click="triggerPreview(template,slug,type)">
												<img v-bind:src="template.thumbnail">
												<span class="wfacp_template_name">{{template.name}}</span>
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<div class="wfacp_clear_20"></div>
		<div class="wfacp_clear_20"></div>
    </div>
<?php } ?>
</div>
