<?php
$post_id = get_the_id();
$post_nm = get_the_title();
$post_cats = get_the_terms($post_id, 'project_categories');
$term_id = $post_cats[0]->term_id;
$term_nm = $post_cats[0]->name;
$post_permalink = get_the_permalink() . "?button_hide=1";
$back_url = site_url() . "/builder-projects/?term_id=" . $term_id."&panel=page";


$header_assign = get_term_meta($term_id, 'project_header', true);
$footer_assign = get_term_meta($term_id, 'project_footer', true);
?>

<!-- <a href="javascript:void(0)" class="project_fixed_menu" id="openSlideNav">
        <i class="fa fa-bars"></i>
</a>


<ul class="project_details_menu" id="slideNav">	
        <li><a href="javascript:void(0)" class=" rounded-left btnDesktopView variation_views" id="desktop" data-id="<?php // echo $post_permalink   ?>"><i class="fa fa-television"></i></a></li>
        <li><a href="javascript:void(0)" class="btnTabletView variation_views" id="tablet" data-id="<?php // echo $post_permalink   ?>"><i class="fa fa-tablet"></i></a></li>
        <li><a href="javascript:void(0)" class="btnMobileView variation_views" id="mobile" data-id="<?php // echo $post_permalink   ?>"><i class="fa fa-mobile"></i></a></li>
        <li >
                <a href="javascript:void(0)" class="rounded-right" title="Close" id="closeSlideNav"><i class="fa fa-times"></i></a>
        </li>
</ul> -->



<div class="contentWrapper inside" id="category-page">
    <div class="builder_desktop_sidebar">
        <?php get_sidebar(); ?>
    </div>


    <div class="wrapContent">
        <div class="topWrapmenu">
            <ul class="builder_bredcumbs">				
                <li><a href="<?php echo site_url(); ?>/builder-projects/?term_id=<?php echo $term_id; ?>"><?php echo $name; ?></a></li>
                <!-- <li><span><?php echo $term_nm; ?></span></li>	 -->
            </ul>			

            <ul class="project_details_menu" id="slideNav">	
                <li><a href="javascript:void(0)" class=" rounded-left btnDesktopView variation_views" id="desktop" data-id="<?php echo $post_permalink ?>"><img src="<?php echo builder_url; ?>images/desk-icon.png" alt="Desktop" /></a></li>
                <li><a href="javascript:void(0)" class="btnTabletView variation_views" id="tablet" data-id="<?php echo $post_permalink ?>"><img src="<?php echo builder_url; ?>images/tab-icon.png" alt="Tablet" /></a></li>
                <li><a href="javascript:void(0)" class="btnMobileView variation_views" id="mobile" data-id="<?php echo $post_permalink ?>"><img src="<?php echo builder_url; ?>images/mobile-icon.png" alt="Mobile" /></a></li>
                <li><a href="javascript:void(0)" title="Close" id="closeSlideNav"><i class="fa fa-times"></i></a></li>
            </ul>
            <ul class="headerButton">
                <li class="builder_layout_save">
                    <a href="javascript:void(0)" data-id="update" title="Save"><img src="<?php echo builder_url; ?>images/floppy-icon.png" alt="Save" /> Publish</a>
                </li>

                <li class="builder_live_preview">
                    <a href="javascript:void(0)" class="preview_data" id="<?php echo $term_id; ?>" data-id="<?php echo $post_id; ?>" title="Preview"><img src="<?php echo builder_url; ?>images/view-icon.png" alt="Bloxx" /> Preview</a>
                </li>
            </ul>
            <ul class="topMenuUser">
                <li class="builder_layout_exit"><a href="javascript:void(0)" data-id="<?php echo $back_url; ?>" class="exit_builder" title="Exit builder"><img src="<?php echo builder_url; ?>images/doorway.png" alt="Close" /> Exit</a></li>
            </ul>
        </div>


        <div class="builder_create_template variation_desktop">
            <script>
                jQuery(function ($) {
                    var changed_array = [];
                    $(".builder_inner_dropable .card > .builder-dragpost").each(function () {
                        var get_content = $(this).attr('id')
                        changed_array.push(get_content);
                    });
                    $("#section_count_default").val(changed_array);
                });
            </script>


            <!-- Hidden fields  -->
            <input type="hidden" id="project_nm" name="project_nm" value="<?php echo $post_nm; ?>">
            <input type="hidden" id="project_id" value="<?php echo $post_id; ?>">
            <input type="hidden" id="project_cat_id" value="<?php echo $term_id; ?>">
            <input type="hidden" value="" id="section_count_default" value="0">
            <!-- End Hidden Fields -->

            <!-- Header Data -->
            <?php
            $sync_header = get_term_meta($term_id, 'project_header', true);
            $sync_footer = get_term_meta($term_id, 'project_footer', true);
            ?>
            <div class="header_resp">
                <?php if ($sync_header != ""): ?>
                    <div class="show_header_data" style="margin: 0 auto;width: 70%;display: block;">
                        <?php
                        $post_header = get_post($sync_header);
                        if (!empty($post_header)) {
                            echo $header_content = apply_filters('the_content', $post_header->post_content);
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </div>
            <!-- End Header Data -->


            <!-- Body Dragable Data -->
            <div class="builder_inner_dropable connectedSortable">	
                <!-- <ul class="fixed-btn-save" style="display:none;">
                    <li class="builder_layout_save">
                    <a href="javascript:void(0)" data-id="update" title="Save"><img src="<?php echo builder_url; ?>images/floppy-icon.png" alt="Save" /> Save</a>
                    </li>
                </ul> -->
                <?php $post_content = get_post_meta($post_id, 'template_user_temp_builder', true); ?>
                <?php if ($post_content == "") { ?>
                    <div class="dropable_area test">
                        <h1>Start by dragging a section here</h1>					
                    </div>
                <?php } else { ?>

                    <?php
                    $page_content = get_the_content();
                    $explode_content = explode("[et_pb_section", $page_content);

                    
                    $pg = (rand(-10, -100));
                    foreach ($explode_content as $pg_content) {
                        if ($pg_content != "") {
                            $page_shortcode = "[et_pb_section" . $pg_content;
                            ?>

                            <div class="card">
                               

                                <div class="action_btns">
                                    <a href="javascript:void(0)" class="builder_uparrow" id="<?php echo $pg; ?>">&#8593;</a>
                                    <a href="javascript:void(0)" class="builder_downarrow" id="<?php echo $pg; ?>">&#8595;</a>
                                     <a href="javascript:void(0)" class="builder_remove_layout" id="<?php echo $pg; ?>"><i class="far fa-trash-alt" aria-hidden="true"></i></a>
                                </div>

                                <div class="builder-dragpost builder_<?php echo $pg; ?>" id="<?php echo $pg; ?>" data-id='<?php echo $term_id; ?>'>
                                    <div class="builder_inner_area">								
                                        <input type="hidden" class="builder_layout" value="<?php echo strip_tags(htmlspecialchars($page_shortcode)); ?>"/>
                                        <div class="show_clone_html"><?php echo do_shortcode("$page_shortcode"); ?></div>
                                    </div>
                                </div>
                            </div>


                            <?php $pg++; ?>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
            </div>

            <!-- End Body Dragable Data -->



            <!-- Footer Data -->
            <?php $sync_footer = get_term_meta($term_id, 'project_footer', true); ?>
            
            <div class="footer_resp">            
                <?php if ($sync_footer != ""): ?>
                    <div class="show_header_data" style="margin: 0 auto;width: 70%;display: block;">
                        <?php
                        $post_footer = get_post($sync_footer);
                        if (!empty($post_footer)) {
                            echo $footer_content = apply_filters('the_content', $post_footer->post_content);
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </div>
            <!-- End Footer Data -->
        </div>
    </div>


    <!-- Footer Tab For Mobile -->
    <div id="footer-nav">
        <ul class="mob-collapsible" style="height:50px;">
            <li>
                <a href="javascript:void(0);" data-id="menu-mobile-menu" class="mob-togglebar"><i class="fa fa-bars"></i></a>
            </li>
        </ul>

        <ul class="mob-builder-menu" id="menu-mobile-menu" style="display:none;">
            <li class="builder_layout_new builder_page mob-switch-sidebar">
                <a href="javascript:void(0)" data-id='<?php echo site_url(); ?>/page-builder/?create=1' title="Add New">
                    <img src="<?php echo builder_url; ?>images/page-new.png" alt="Upload" /></a>
            </li>

            <li class="mob-open-sidebar">
                <a href="javascript:void(0);" title="Add Section">
                    <img src="<?php echo builder_url; ?>images/add-new.png" alt="Bloxx" />
                </a>
            </li>

            <li class="builder_export_json">
                <a href="javascript:void(0)" class="export_json" title="Export Json"><img src="<?php echo builder_url; ?>images/download-icon.png" alt="Download" /></a>
                <a class="click_download" href="javascript:void(0)" download style="visibility: hidden; position: absolute;"><img src="<?php echo builder_url; ?>images/download.png" alt="Bloxx" width="50" /></a>
            </li>
            
            

            <li>
                <a href="javascript:void(0)" title="Switch to Divi editor" class="move_2divi" data-id="<?php echo $post_id; ?>" data-href="<?php echo get_the_permalink($post_id); ?>?update=1&et_fb=1&PageSpeed=off&pcat=<?php echo $term_id; ?>">
                    <img src="<?php echo builder_url; ?>images/divi-icon.png">
                    <!-- <label class="switch">
                            <input type="checkbox" class="move_2divi" data-id="<?php // echo $post_id;   ?>" data-href="<?php // echo get_the_permalink($post_id);  ?>?update=1&et_fb=1&PageSpeed=off&pcat=<?php // echo $term_id;   ?>">
                            <span class="slider round"></span>
                    </label> -->
                </a>
            </li>
        </ul>
    </div>
    <!-- Footer Tab For Mobile -->
</div>





<!-- AI Section Modal -->
<div id="ai_design" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <span class="closebtn" onclick="jQuery('#ai_design').hide();">
                <i class="fa fa-times"></i>
            </span>
            <!-- <h3 id="page_nm" class="text-bold">Select Header</h3>
            <p class="text-left">You can select header for your pages</p>-->

        </div>
        <div class="ai_images">
            <?php echo do_shortcode('[Writesonic_fliter]'); ?>
        </div>
    </div>    
</div>
<!-- End Header Selection Section -->