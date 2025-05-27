<?php if (isset($_REQUEST['update'])) { ?>
    <?php
    $post_id = get_the_id();
    $post_cats = get_the_terms($post_id, 'project_categories');
    $term_id = $post_cats[0]->term_id;
    $main_cats_id=$term_id;
    $term_nm = $post_cats[0]->name;
    $post_permalink = get_the_permalink() . "?button_hide=1";
    $back_url = site_url() . "/builder-projects/?term_id=" . $term_id;
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


    <div class="left-aside">
        <ul class="left-list">
            <li>
                <a href="<?= $back_url; ?>"  title="Site bloxx" class="left-list-img">
                    <img src="<?php echo builder_url; ?>images/logo_Icon@2x.png" alt="Bloxx" width="50" />
                </a>        
            </li>
            <li class="switch-sidebar">
                <a href="javascript:void(0)" title="Add New">
                    <img src="<?php echo builder_url; ?>images/add-plus.png" alt="Upload" />
                </a>
            </li>
            <li class="open-sidebar">
                <a href="javascript:void(0);" title="Add Section">
                    <img src="<?php echo builder_url; ?>images/new-section-plus.png" alt="Bloxx" />
                </a>
            </li>


            <li class="builder_export_json">
                <a href="javascript:void(0)" class="export_json" title="Export Json"><img src="<?php echo builder_url; ?>images/export-new.png" alt="Download" width="50" /></a>
                <a class="click_download" href="javascript:void(0)" download style="visibility: hidden; position: absolute;"><img src="<?php echo builder_url; ?>images/export-new.png" alt="Bloxx" width="50" /></a>
            </li>
            <!-- <li class="ai_programs ai_design">
                <a href="javascript:void(0)" title="AI">
                    <img src="<?php // echo builder_url; ?>images/ai-new.png" alt="AI" />
                </a>
            </li> -->
            
            
            <li class="modeOption">
                
                <button class="clickDrop">Mode</button>
                <ul class="dropdownShow">									                              		
                    <li>
                        
                    </li>
                    <li>
                        <a href="javascript:void(0)" title="Switch to Divi editor" class="move_2divi" data-id="<?php echo $post_id; ?>" data-href="<?php echo get_the_permalink($post_id); ?>?update=1&et_fb=1&PageSpeed=off&pcat=<?php echo $term_id; ?>">
                            Edit
                        </a>
                    </li>
                </ul>
            </li>
            <li>
                <!-- <a href="javascript:void(0)" title="Switch to Divi editor" class="move_2divi" data-id="<?php // echo $post_id; ?>" data-href="<?php // echo get_the_permalink($post_id); ?>?update=1&et_fb=1&PageSpeed=off&pcat=<?php echo $term_id; ?>"> -->
                        <!-- <img src="<?php // echo builder_url;    ?>images/divi-icon.png"> -->
                    <!-- <span class="diviTag">Divi</span> -->
                    <!-- <label class="switch">
                            <input type="checkbox" class="move_2divi" data-id="<?php // echo $post_id;    ?>" data-href="<?php // echo get_the_permalink($post_id);   ?>?update=1&et_fb=1&PageSpeed=off&pcat=<?php // echo $term_id;    ?>">
                            <span class="slider round"></span>
                    </label> -->
                <!-- </a> -->
            </li>
        </ul>
    </div>



    <!-- User Pages When click on project pancil icon -->
    <?php if (isset($term_id)) { ?>
        <div class="left-category-aside" id="left_project">
            <div class="wrapCategoryMenu">
                <ul class="builder_categories">
                    <?php $category_data = get_term_by('id', $term_id, 'project_categories'); ?>
                    <h2 class="heading2 text-white"><!-- Building Bloxx --> <?php echo ucfirst(get_the_title()); ?></h2>
                    <?php
                    $user = wp_get_current_user();
                    $current_user_id = $user->ID;
                    $args = array(
                        'post_type' => 'customer_templates',
                        'order' => 'asc',
                        'posts_per_page' => 5,
                        'meta_query' => array(
                            array(
                                'key' => 'template_user',
                                'value' => $current_user_id,
                                'compare' => '=',
                            )
                        ),
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'project_categories',
                                'field' => 'term_id',
                                'terms' => $term_id,
                                'compare' => '='
                            )
                        )
                    );

                    $query = new WP_Query($args);
                    ?>
                    <?php if ($query->have_posts()) { ?>
                        <?php while ($query->have_posts()) { ?>
                            <?php
                            $query->the_post();
                            $switchid = get_the_id();
                            $switch_title = get_the_title();
                            $switch_url = get_the_permalink()."?update=1"
                            ?>
                            <?php if($switchid==$post_id){ ?>
                                <li>
                                    <a href="javascript:void(0)" class="current_active"><?php echo get_the_title(); ?></a>
                                </li>
                            <?php } else { ?>
                                <li>
                                    <a href="<?php echo $switch_url; ?>" class="current_active"><?php echo get_the_title(); ?></a>
                                </li>
                            <?php } ?>
                        <?php } ?>
                    <?php } else { ?>

                        <li>
                            <a href="javascript:void(0)" class="builder_cats builder_cat_active">No Page Found</a>                                                              
                        </li>
                    <?php } ?>
                    <li class="builder_page user_action">
                        <a href="javascript:void(0)" class="addNew add_page_restriction" data-name="<?php echo $term_id; ?>" data-title="builder">Add Blank Page <i class="fa fa-plus"></i></a>
                    </li>
                    <?php wp_reset_postdata(); ?>
                </ul>
            </div>
        </div>
    <?php } ?>
    <!-- End User Pages When click on project pancil icon -->


    

    <!-- Get Cats By Args Query -->
    <?php
    $current_user = wp_get_current_user();
    $current_user_id = $current_user->ID;

    $cats_limits=get_field('category_limit_by_default', 'option');
    $cats_ajax_limits=get_field('category_limit_by_ajax', 'option');

    $section_limits=get_field('section_limit_by_default', 'option');
    $section_ajax_limits=get_field('section_limit_by_default', 'option');
    $total_offset=$section_limits+$section_ajax_limits;

    $builder_terms = get_terms(
        array(
            'taxonomy' => 'project_category',
            'hide_empty' => false,
            'exclude' => array("127", "128"),
            //'number' => $cats_limits,
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'builder_custom_cat_user',
                    'compare' => 'NOT EXISTS', // works!
                    'value' => '' // This is ignored, but is necessary...
                ),
                array(
                    'key' => 'builder_custom_cat_user',
                    'value' => $current_user_id,
                    'compare' => 'LIKE'
                )
            )
        )
    );


    //Total Term Counts
    $builder_terms_count = get_terms(
        array(
            'taxonomy' => 'project_category',
            'hide_empty' => false,
            'exclude' => array("127", "128"),
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'builder_custom_cat_user',
                    'compare' => 'NOT EXISTS', // works!
                    'value' => '' // This is ignored, but is necessary...
                ),
                array(
                    'key' => 'builder_custom_cat_user',
                    'value' => $current_user_id,
                    'compare' => 'LIKE'
                )
            )
        )
    );

    $count_cats= count($builder_terms_count);

    ?>
    <!-- End Get Cats by Args Query -->




    <!-- All Section Displayed while click on plus icon -->
    <div class="left-category-aside" id="leftCategorySidebar">
        <div class="wrapCategoryMenu">
            <ul class="builder_categories">
                <h2 class="heading2 text-white"><!-- Building Bloxx --> <?php echo ucfirst(get_the_title()); ?></h2>
                <!--<ul class="project_details_menu sliding-buttons">
                    <li>
                        <a href="javascript:void(0);" title="Select a header for this project" class="top-btns" onclick="jQuery('#syncBox_assign_header').show();  return false;">Header</a>
                    </li>
                    <li>
                        <a href="javascript:void(0);" title="Select a footer for this project" class="top-btns" onclick="jQuery('#syncBox_assign_footer').show(); return false;">Footer</a>
                    </li>
                </ul>-->
                <?php
                $i = 1;
                if (isset($builder_terms)) {
                    ?>
                    <!-- foreach for admin categories display first -->
                    <?php foreach ($builder_terms as $builder_cats): ?>
                        <?php $builder_custom_cat_by_user = get_term_meta($builder_cats->term_id, 'builder_custom_cat_user', true); ?>
                        <?php $cat_id= $builder_cats->term_id; ?>

                        <?php 
                        $cat_post_count = [
                            'post_type' => 'project',
                            'order' => 'desc',
                            'posts_per_page' => -1,
                            'tax_query' => [
                                [
                                    'taxonomy' => 'project_category',
                                    'terms' => $cat_id
                                ],
                            ],
                        ];
                        
                        $count_sections= count( get_posts( $cat_post_count ) );
                        ?>
                        
                        <?php if ($builder_custom_cat_by_user == "") { ?>               
                            <li class="project_section<?php if($cat_id==176 || $cat_id==502){echo " project_details_menu sliding-buttons";} ?>" id="<?= $builder_cats->term_id; ?>">
                                <a href="javascript:void(0)" class="builder_cats<?php
                                if ($i == 3) {
                                    echo " builder_cat_active";
                                }
                                ?>" id="<?php echo $builder_cats->term_id; ?>" data-id="<?= $term_id; ?>"><?php echo str_replace("Global", "", ucfirst($builder_cats->name)); ?>
                                </a>                            
                            </li>                       
                            <?php $i++; ?>
                        <?php } ?>
                    <?php endforeach; ?>

                <?php
                } else {
                    echo '<p class="um-notice warning">No Section found on the server</p>';
                }
                ?>
            </ul>
        </div>
    </div>

    <div class="sections_lists"><!-- Section lists --></div>


    <!-- End All Section Displayed while click on plus icon -->

    <?php 
} else {

    //Default Sidebar Content
    if (( is_single() || is_page() ) && in_array(get_post_meta(get_queried_object_id(), '_et_pb_page_layout', true), array('et_full_width_page', 'et_no_sidebar'))) {
        return;
    }

    if (is_active_sidebar('sidebar-1')) :
        ?>
        <div id="sidebar">
        <?php dynamic_sidebar('sidebar-1'); ?>
        </div> <!-- end #sidebar -->
        <?php
    endif;
    // End Default Sidebar content
}