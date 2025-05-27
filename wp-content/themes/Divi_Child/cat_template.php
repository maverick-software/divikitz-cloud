<?php //Template Name: Our Demo ?>


<?php if(isset($_REQUEST['action'])){ ?>

    <?php 
    if($_REQUEST['action']=="section_ajax_load"){
        $cats_limits=get_field('category_limit_by_default', 'option');
        $cats_ajax_limits=get_field('category_limit_by_ajax', 'option');

        $section_limits=get_field('section_limit_by_default', 'option');
        $section_ajax_limits=get_field('section_limit_by_default', 'option');
        $total_offset=$section_limits+$section_ajax_limits;

        extract($_REQUEST);
        
        

        $cat_args = array(
            'post_type' => 'project',
            'order' => 'desc',
            'post_status' => 'publish',
            'posts_per_page' => $section_limits,
            'tax_query' => array(
                array(
                    'taxonomy' => 'project_category',
                    'terms' => $cats_id
                )
            )
        );


        $cat_count = [
            'post_type' => 'project',
            'order' => 'desc',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'tax_query' => [
                [
                    'taxonomy' => 'project_category',
                    'terms' => $cats_id
                ],
            ],
        ];

        $count_collection= count( get_posts( $cat_count ) );


        $latest_books = new WP_Query($cat_args);

        if ($latest_books->have_posts()) {
            ?>
            <section class="builder_posts active_slide" id="cat_post_<?= $cats_id; ?>" style="display:none;">
            <?php
            while ($latest_books->have_posts()) {
                $latest_books->the_post();
                $post_id = get_the_id();
                $feat_image = wp_get_attachment_url(get_post_thumbnail_id($post_id));
                $json_data= strip_tags(htmlspecialchars(get_the_content()));
                ?>
                <div class="builder_inner_dragpost connectedSortable">
                    <div class="card">
                        <?php if($cats_id==502){ ?>
                            <a class="assign_headfooter" href="javascript:void(0)" id="<?= $post_id; ?>" data-id="<?= $page_term; ?>" data-title="assign_footer">
                                <img src="<?php echo $feat_image; ?>">
                            </a>
                        <?php } else if($cats_id==176){ ?>
                            <a class="assign_headfooter" href="javascript:void(0)" id="<?= $post_id; ?>" data-id="<?= $page_term; ?>" data-title="assign_header">
                                <img src="<?php echo $feat_image; ?>">
                            </a>                        
                        <?php } else { ?>
                        <div class="action_btns" style="display:none;">
                            <a href="javascript:void(0)" class="builder_uparrow" id="<?php echo $post_id; ?>">&#8593;</a>
                            <a href="javascript:void(0)" class="builder_downarrow" id="<?php echo $post_id; ?>">&#8595;</a>
                            <a href="javascript:void(0)" class="builder_remove_layout" id="<?php echo $post_id; ?>">
                                <i class="far fa-trash-alt" aria-hidden="true"></i>
                            </a>
                        </div>


                        <div class="builder-dragpost builder_<?php echo $post_id; ?>" id="<?php echo $post_id; ?>" data-id='<?php echo $cats_id; ?>'>
                            <img class ="show_clone_img" src="<?php echo $feat_image; ?>" style="display:block;width: 100%;">
                            <input type="hidden" class="builder_layout" value="<?= $json_data; ?>"/>
                            <div class="show_clone_html" style="display: none;"></div>

                        </div>
                        <?php } ?>
                    </div>                  
                </div>
            <?php } ?>        
            <?php wp_reset_query(); ?>
        
            <?php if($section_limits < $count_collection){ ?>
                <div class="load_more">
                    <a class="default-btn scroll-btn section_more_load" id="ajax_load_<?= $cats_id; ?>" data-id="<?= $cats_id; ?>" data-offset="<?= $total_offset ?>" ajax-limit="<?= $section_ajax_limits; ?>" total-counts="<?= $count_collection; ?>" data-nm="<?= $page_term; ?>">Load More</a>
                </div>
            <?php } ?>

        <?php
            echo "</section>";
        }
        die();
    }
    ?>



    <?php 
    if($_REQUEST['action']=="ajax_load_more"){
        $cats_limits=get_field('category_limit_by_default', 'option');
        $cats_ajax_limits=get_field('category_limit_by_ajax', 'option');

        $section_limits=get_field('section_limit_by_default', 'option');
        $section_ajax_limits=get_field('section_limit_by_default', 'option');
        
        extract($_REQUEST);
        $next_offset=$ajax_offset+$section_ajax_limits;        
        
        $cat_args = array(
            'post_type' => 'project',
            'order' => 'desc',
            'post_status' => 'publish',
            'posts_per_page' => $ajax_offset,
            //'offset' => $ajax_offset,
            'tax_query' => array(
                array(
                    'taxonomy' => 'project_category',
                    'terms' => $cats_id
                )
            )
        );


        $cat_count = [
            'post_type' => 'project',
            'order' => 'desc',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'tax_query' => [
                [
                    'taxonomy' => 'project_category',
                    'terms' => $cats_id
                ],
            ],
        ];
        
        $count_collection= count( get_posts( $cat_count ) );

        $latest_books = new WP_Query($cat_args);

        if ($latest_books->have_posts()) {

            while ($latest_books->have_posts()) {
                $latest_books->the_post();
                $post_id = get_the_id();
                $feat_image = wp_get_attachment_url(get_post_thumbnail_id($post_id));
                $json_data= strip_tags(htmlspecialchars(get_the_content()));
                ?>
                <div class="builder_inner_dragpost connectedSortable">
                    <div class="card">
                        <?php if($cats_id==502){ ?>
                            <a class="assign_headfooter" href="javascript:void(0)" id="<?= $post_id; ?>" data-id="<?= $page_term; ?>" data-title="assign_footer">
                                <img src="<?php echo $feat_image; ?>">
                            </a>
                        <?php } else if($cats_id==176){ ?>
                            <a class="assign_headfooter" href="javascript:void(0)" id="<?= $post_id; ?>" data-id="<?= $page_term; ?>" data-title="assign_header">
                                <img src="<?php echo $feat_image; ?>">
                            </a>                        
                        <?php } else { ?>
                            <div class="action_btns" style="display:none;">
                                <a href="javascript:void(0)" class="builder_uparrow" id="<?php echo $post_id; ?>">&#8593;</a>
                                <a href="javascript:void(0)" class="builder_downarrow" id="<?php echo $post_id; ?>">&#8595;</a>
                                <a href="javascript:void(0)" class="builder_remove_layout" id="<?php echo $post_id; ?>">
                                    <i class="far fa-trash-alt" aria-hidden="true"></i>
                                </a>
                            </div>

                            <div class="builder-dragpost builder_<?php echo $post_id; ?>" id="<?php echo $post_id; ?>" data-id='<?php echo $cats_id; ?>'>
                                <img class ="show_clone_img" src="<?php echo $feat_image; ?>" style="display:block;width: 100%;">
                                <input type="hidden" class="builder_layout" value="<?= $json_data; ?>"/>
                                <div class="show_clone_html" style="display: none;"></div>

                            </div>
                        <?php } ?>
                    </div>                  
                </div>
            <?php } ?>
            
            <?php if($ajax_offset < $count_collection){ ?>
                <div class="load_more">
                    <a class="default-btn scroll-btn section_more_load" id="ajax_load_<?= $cats_id; ?>" data-id="<?= $cats_id; ?>" data-offset="<?= $next_offset; ?>" ajax-limit="<?= $section_ajax_limits; ?>" total-counts="<?= $count_collection; ?>" data-nm="<?= $page_term; ?>">Load More</a>
                </div>
            <?php } ?>

            <?php
            wp_reset_postdata();
            die(); 
        } 
    }
    ?>

<?php } ?>    