<?php get_header(); ?>

<?php
$body_id = get_the_id();

$post_cats = get_the_terms($body_id, 'project_categories');
$term_4id = $post_cats[0]->term_id;
$term_4nm = $post_cats[0]->name;
$current_user = wp_get_current_user();
$current_user_id = $current_user->ID;
$user_email = $current_user->user_email;

$coll_item = get_term_meta($term_4id, "allow_collaborate", true);

if ($coll_item == $user_email) {
    $current_user_id = get_term_meta($term_4id, 'request_collaborate', true);
}

$post_user = get_post_meta($body_id, 'template_user', true);

?>

<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 30px;
        height: 17px;
    }

    .switch input { 
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: -3px;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 13px;
        width: 13px;
        left: 0px;
        bottom: 1px;
        background-color: #930abc;
        -webkit-transition: .4s;
        transition: .4s;
        border: 1px solid #fff;
    }

    input:checked + .slider {
        background-color: #fff;
    }

    input:focus + .slider {
        box-shadow: 0 0 1px #2196F3;
    }

    input:checked + .slider:before {
        -webkit-transform: translateX(11px);
        -ms-transform: translateX(11px);
        transform: translateX(11px);
    }

    /* Rounded sliders */
    .slider.round {
        border-radius: 17px;
    }

    .slider.round:before {
        border-radius: 50%;
    }
</style>

<!-- <?php // if (isset($_REQUEST['et_fb'])) { ?>
    <a class="bloxSwitch" href="<?php // echo get_the_permalink(); ?>/?update=1">SWITCH TO BLOXX</a>
<?php // } ?> -->


<div id="main-content">	
    <div class="container">

        <div id="content-area" class="clearfix">
            <div id="left-area" style="margin: 0; padding: 0;width: 100%;">
                <!-- //sidebar  --> 
                <?php // if(isset($_REQUEST['et_fb']) && $_REQUEST['et_fb']==1): require_once builder_path.'/templates/builder_siderbar.php'; endif; ?>
                <?php while (have_posts()) : the_post(); ?>

                    <article id="post-<?php the_ID(); ?>" <?php post_class('et_pb_post'); ?> style="margin: 0;">						
                        <div class="entry-content">
                            <?php if (isset($_REQUEST['et_fb']) && $_REQUEST['et_fb'] == 1): ?>
                                <div class="contentWrapper inside" id="category-page">
                                    <div class="builder_desktop_sidebar">                                       
                                        <div class="left-aside buttonDisabled">
                                            <ul class="left-list">
                                                <li>
                                                    <a href="<?= get_site_url(); ?>"  title="Site bloxx" class="left-list-img">
                                                        <img src="<?php echo builder_url; ?>images/logoicon.png" alt="Bloxx" width="50" />
                                                    </a>		
                                                </li>

                                                <li class="switch-sidebar">
                                                    <a href="javascript:void(0)" title="Add New">
                                                        <img src="<?php echo builder_url; ?>images/page-new.png" alt="Upload" />
                                                    </a>
                                                </li>

                                                <li class="open-sidebar">
                                                    <a href="javascript:void(0);" title="Add Section">
                                                        <img src="<?php echo builder_url; ?>images/add-new.png" alt="Bloxx" />
                                                    </a>
                                                </li>

                                                <!-- <li class="builder_layout_save">
                                                        <a href="javascript:void(0)" data-id="update" title="Save"><img src="<?php echo builder_url; ?>images/save.png" alt="Save"/></a>
                                                </li>

                                                <li class="builder_live_preview">
                                                        <a href="javascript:void(0)" class="preview_data" id="<?php echo $term_id; ?>" data-id="<?php echo $body_id; ?>" title="Preview"><img src="<?php echo builder_url; ?>images/eye.png" alt="Bloxx" /></a>
                                                </li> -->

                                                <li class="builder_export_json">
                                                    <a href="javascript:void(0)" class="export_json" title="Export Json"><img src="<?php echo builder_url; ?>images/download.png" alt="Download" /></a>
                                                    <a class="click_download" href="javascript:void(0)" download style="visibility: hidden; position: absolute;"><img src="<?php echo builder_url; ?>images/download.png" alt="Bloxx" width="50" /></a>
                                                </li>

                                                <li class="builder_layout_exit">
                                                    <?php $back_url = site_url() . "/builder-projects/?term_id=" . $term_4id."&panel=page"; ?>
                                                    <a href="javascript:void(0)" data-id="<?php echo $back_url; ?>" class="exit_builder" title="Exit builder">
                                                        <img src="<?php echo builder_url; ?>images/cross.png" alt="Close" />
                                                    </a>
                                                </li>
                                                
                                                
                                                <li class="modeOption">
                                                    <button class="clickDrop">Mode</button>
                                                    <ul class="dropdownShow">									                              		
                                                        <li>
                                                            <?php if (isset($_REQUEST['et_fb'])) { ?>
                                                                <a href="<?php echo get_the_permalink(); ?>?update=1">Build</a>
                                                            <?php } ?>
                                                        </li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="wrapContent divi1et_fb_main">

                                        <div class="topWrapmenu">
                                            <ul class="builder_bredcumbs">				
                                                <li><a href="<?php echo site_url(); ?>/builder-projects/?term_id=<?php echo $term_id; ?>"><?php echo $name; ?></a></li>
                                                <!-- <li><span><?php // echo $term_4nm;       ?></span></li>	 -->
                                            </ul>
                                            <ul class="project_details_menu disabled" id="slideNav">	
                                                <li><a href="javascript:void(0)" class=" rounded-left btnDesktopView variation_views" id="desktop" data-id="<?php echo $post_permalink ?>"><img src="<?php echo builder_url; ?>images/desk-icon.png" alt="Desktop" /></a></li>
                                                <li><a href="javascript:void(0)" class="btnTabletView variation_views" id="tablet" data-id="<?php echo $post_permalink ?>"><img src="<?php echo builder_url; ?>images/tab-icon.png" alt="Tablet" /></a></li>
                                                <li><a href="javascript:void(0)" class="btnMobileView variation_views" id="mobile" data-id="<?php echo $post_permalink ?>"><img src="<?php echo builder_url; ?>images/mobile-icon.png" alt="Mobile" /></a></li>
                                                <li><a href="javascript:void(0)" title="Close" id="closeSlideNav"><i class="fa fa-times"></i></a></li>
                                            </ul>
                                            <ul class="headerButton disabled">
                                                <li class="builder_layout_save">
                                                    <a href="javascript:void(0)" data-id="update" title="Save"><img src="<?php echo builder_url; ?>images/floppy-icon.png" alt="Save" /> Save</a>
                                                </li>

                                                <li class="builder_live_preview disabled">
                                                    <a href="javascript:void(0)" class="preview_data" id="<?php echo $term_id; ?>" data-id="<?php echo $post_id; ?>" title="Preview"><img src="<?php echo builder_url; ?>images/view-icon.png" alt="Bloxx" /> Preview</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="builder_create_template">
                                            <?php echo $content = apply_filters('the_content', get_the_content()); ?>											
                                        </div>
                                    </div>
                                </div>
                                <?php
                            else:

                                if (isset($_REQUEST['update']) && $current_user_id == $post_user):
                                    get_template_part('builder_parts/manage_builder');
                                else:
                                    get_template_part('builder_parts/builder_single');
                                endif;

                            endif;
                            ?>
                        </div> <!-- .et_post_meta_wrapper -->
                    </article> <!-- .et_pb_post -->

                <?php endwhile; ?>
            </div> <!-- #left-area -->

            <?php //get_sidebar();  ?>
        </div> <!-- #content-area -->
    </div> <!-- .container -->

</div> <!-- #main-content -->

<script type="text/javascript">
    function backtocust() {
        //window.location.replace(jQuery(".move_2divi1").data("href")); 
        return false;
    }
</script>
<?php get_footer(); ?>
