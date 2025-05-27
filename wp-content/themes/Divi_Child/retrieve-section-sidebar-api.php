<?php //Template Name: Retrieve Section Sidebar API  ?>

<?php 
error_reporting(0); 


header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');


?>

<?php if (isset($_REQUEST['action'])) { ?>
    <?php 
    global $wpdb;
    $conn_site = $wpdb->prefix . 'bloxx_apis';
    $current_user_email = $_REQUEST['current_user_email'];
    $api_key=@$_REQUEST['builder_key'];
    
    $website_url = $_REQUEST['site_url'];
    $api_token=$_REQUEST['api_token'];
    $get_usertype_byapi_curl = get_usertype_byapi_curl($current_user_email,$website_url,$api_key);

    $verify_query = "SELECT * FROM $conn_site where api_key='$api_key' and prime_key='1' order by id desc limit 1"; // old query
    $verify_result = $wpdb->get_results($verify_query);
    $count_verify = count($verify_result);

    // if($count_verify==0){
    //     echo "Data_not_found";
    //     die();
    // } else {
        $result_row = $wpdb->get_row($verify_query);
                if($result_row != null){
            $key_user=$result_row->user_id;
        }
        else{
            $key_user="";
        }
    // }

    $get_sitebloxx_userid_from_siteurl = get_sitebloxx_userid_from_siteurl($_REQUEST['site_url']); // this function defined in divi_builder.php

    if ($_REQUEST['action'] == "section_ajax_load") {
        $cats_limits = get_field('category_limit_by_default', 'option');
        $cats_ajax_limits = get_field('category_limit_by_ajax', 'option');

        $section_limits = get_field('section_limit_by_default', 'option');
        $section_ajax_limits = get_field('section_limit_by_default', 'option');
        $total_offset = $section_limits + $section_ajax_limits;

        extract($_REQUEST);

        $cat_args = array(
            'post_type' => 'project',
            'meta_key' => 'premium_section',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'post_status' => 'publish',
            'posts_per_page' => $section_limits,
            // 'meta_query'    => array(
            //     //'relation' => 'AND',
            //     array(
            //         'key'       => 'builder_custom_cat_user',
            //         'value'     => $key_user,
            //         'compare'   => 'LIKE',
            //     )
            // ),
            'tax_query' => array(
                array(
                    'taxonomy' => 'project_category',
                    'field' => 'term_id',
                    'terms' => $cats_id
                )
            )
        );
           if(empty($api_token) ||  $api_token == "disconnect"){
                $cat_args['meta_query'] = array(
                    array(
                        'key'       => 'premium_section',
                        'value'     => 1, //free
                        'compare'   => '=',
                    )
                );
            }
            else{
                 $cat_args['meta_query'] = array(
                    'relation'=>"OR",
                    array(
                        'key'       => 'premium_section',
                        'value'     => 1, //free
                        'compare'   => '=',
                    ),
                       array(
                        'key'       => 'premium_section',
                        'value'     => 2, //free
                        'compare'   => '=',
                    ),
                       array(
                            'key'       => 'builder_custom_cat_user',
                            'value'     => $key_user,
                            'compare'   => 'LIKE',
                        )
                );
            }


        $cat_count = [
            'post_type' => 'project',
            'order' => 'desc',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'tax_query' => array(
                array(
                    'taxonomy' => 'project_category',
                     'field' => 'term_id',
                    'terms' => $cats_id
                )
            )

        ];

        if(empty($api_token) ||  $api_token == "disconnect"){
                $cat_count['meta_query'] = array(
                    array(
                        'key'       => 'premium_section',
                        'value'     => 1, //free
                        'compare'   => '=',
                    )
                );
            }
           

           // pre($cat_count);
        $count_collection = count(get_posts($cat_count));
        $latest_books = new WP_Query($cat_args);
        $enable_header_footer = 1;
        if ($latest_books->have_posts()) { ?>
  
            <section data-count_collection-section_ajax_load="<?php echo $count_collection; ?>" data-section_limit-section_ajax_load="<?php echo $section_limits; ?>" class="builder_posts active_slide"  id="cat_post_<?= $cats_id; ?>" style="display:none;">
                <?php
                $f=1;
                while ($latest_books->have_posts()) {
                    $latest_books->the_post();
                    $post_id = get_the_id();
                    $feat_image = wp_get_attachment_url(get_post_thumbnail_id($post_id));
                    $json_data = strip_tags(htmlspecialchars(get_the_content()));
                    $is_free=get_field('premium_section', $post_id);

                    // echo $post_id;
                    // die();

                    $usertype = $get_usertype_byapi_curl;
                    $builder_custom_cat_user = get_post_meta($post_id,'builder_custom_cat_user',true);
				    
                    if(isset($usertype)){
                        if(@$is_free==2 && $usertype=='free' || @$is_free==""){
                            $assign_headfooter_class = '';
                            $drag_class = '';
                        } else{
                            $drag_class = 'builder_inner_dragpost connectedSortable';
                            $assign_headfooter_class = 'assign_headfooter';
                        }
                    } else {
                        $drag_class = 'builder_inner_dragpost connectedSortable';
                        $assign_headfooter_class = 'assign_headfooter';
                    }
                ?>
                    <div data-sectiontype="<?php echo @$is_free; ?>" data-usertype="<?php echo $usertype; ?>" data-useremail="<?php echo $current_user_email; ?>" id="builder_inner_dragpost_<?php echo $post_id ?>" class="builder_inner_dragpost_sel <?php echo $drag_class ?>">
                        <?php if(@$is_free==1){ ?>
                        <div class="section_type is_free">
                            <!-- <h3>Free</h3> -->
                        </div>
                        <?php } ?>
                        <?php if(isset($is_free) && $is_free==2) { ?>
                        <div class="section_type is_premium">
                            <h3>Premium</h3>
                        </div>
                        <?php } ?> 

                        <div class="card" data-sectiontype="<?php echo @$is_free; ?>" data-usertype="<?php echo $usertype; ?>">
                            <?php if ($cats_id == 502) { ?>
                                <a class="<?php echo $assign_headfooter_class; ?> assign_headfooter_sel" href="javascript:void(0)" id="<?= $post_id; ?>" data-title="assign_footer">
                                    <img src="<?php echo $feat_image; ?>">
                                    <input type="hidden" id="header_json" value="<?= $json_data; ?>"/>
                                </a>
                            <?php } else if ($cats_id == 176) { ?>
                                <a class="<?php echo $assign_headfooter_class; ?> assign_headfooter_sel" href="javascript:void(0)" id="<?= $post_id; ?>" data-title="assign_header">
                                    <img src="<?php echo $feat_image; ?>">
                                    <input type="hidden" id="header_json" value="<?= $json_data; ?>"/>
                                </a>                        
                            <?php } else { ?>
                                <div class="action_btns" style="display:none;">
                                    <a href="javascript:void(0)" class="builder_uparrow" id="<?php echo $post_id; ?>">&#8593;</a>
                                    <a href="javascript:void(0)" class="builder_downarrow" id="<?php echo $post_id; ?>">&#8595;</a>
                                    <a href="javascript:void(0)" class="builder_remove_layout" id="<?php echo $post_id; ?>">
                                        <i class="far fa-trash-alt" aria-hidden="true"></i>
                                    </a>
                                </div>


                                <div class="builder-dragpost builder-dragpost-sel builder-dragpost-sidebar  builder_<?php echo $post_id; ?>" id="<?php echo $post_id; ?>" data-id='<?php echo $cats_id; ?>'  data-sectiontype="<?php echo @$is_free; ?>" data-usertype="<?php echo $usertype; ?>">
                                    <img class ="show_clone_img" src="<?php echo $feat_image; ?>" style="display:block;margin: auto">
                                    <input type="hidden" class="builder_layout" value="<?= $json_data; ?>"/>
                                    <div class="show_clone_html" style="display: none;"></div>

                                </div>
                            <?php } ?>
                        </div>                  
                    </div>
                    <?php $f++; ?>
                <?php } ?>                        
                <?php wp_reset_query(); ?>


                <?php 

               // if($the_auth_type==1){
                    // do not show load more button if auth type is simple
               // }
               // else{
                    if ($section_limits < $count_collection) { ?>
                    <div class="load_more">
                        <a class="default-btn scroll-btn section_more_load" id="ajax_load_<?= $cats_id; ?>" data-id="<?= $cats_id; ?>" data-offset="<?= $total_offset ?>" ajax-limit="<?= $section_ajax_limits; ?>" total-counts="<?= $count_collection; ?>">Load More</a>
                    </div>
                    <?php } 
               // }
                

                
                echo "</section>";
            }
            die();
        }


        if ($_REQUEST['action'] == "ajax_load_more") {
            $cats_limits = get_field('category_limit_by_default', 'option');
            $cats_ajax_limits = get_field('category_limit_by_ajax', 'option');

            $section_limits = get_field('section_limit_by_default', 'option');
            $section_ajax_limits = get_field('section_limit_by_default', 'option');

            extract($_REQUEST);
            $next_offset = $ajax_offset + $section_ajax_limits;

            $cat_args = array(
                'post_type' => 'project',
                'post_status' => 'publish',
                'posts_per_page' => $ajax_offset,
                'meta_key' => 'premium_section',
                'orderby' => 'meta_value',
                'order' => 'ASC',
                //'offset' => $ajax_offset,

                'tax_query' => array(
                    array(
                        'taxonomy' => 'project_category',
                        'terms' => $cats_id
                    )
                ),
            );

                if(empty($api_token) ||  $api_token == "disconnect"){
                $cat_args['meta_query'] = array(
                    array(
                        'key'       => 'premium_section',
                        'value'     => 1, //free
                        'compare'   => '=',
                    )
                );
            }
             else{
                 $cat_args['meta_query'] = array(
                    'relation'=>"OR",
                    array(
                        'key'       => 'premium_section',
                        'value'     => 1, //free
                        'compare'   => '=',
                    ),
                       array(
                        'key'       => 'premium_section',
                        'value'     => 2, //free
                        'compare'   => '=',
                    ),
                       array(
                            'key'       => 'builder_custom_cat_user',
                            'value'     => $key_user,
                            'compare'   => 'LIKE',
                        )
                );
            }
          

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

            

            $count_collection = count(get_posts($cat_count));

            $latest_books = new WP_Query($cat_args);

            if ($latest_books->have_posts()) {

                while ($latest_books->have_posts()) {
                    $latest_books->the_post();
                    $post_id = get_the_id();
                    $feat_image = wp_get_attachment_url(get_post_thumbnail_id($post_id));
                    $json_data = strip_tags(htmlspecialchars(get_the_content()));
                    $is_free=get_field('premium_section', $post_id);

                    
                    $builder_custom_cat_user = get_post_meta($post_id,'builder_custom_cat_user',true);
                    $usertype = $get_usertype_byapi_curl;
                    
                    if(isset($usertype)){
                        if(@$is_free==2 && $usertype=='free'){
                            $assign_headfooter_class = '';
                            $drag_class = '';
                        }else{
                            $drag_class = 'builder_inner_dragpost connectedSortable';
                            $assign_headfooter_class = 'assign_headfooter';
                        }
                    } else {
                        $drag_class = 'builder_inner_dragpost connectedSortable';
                        $assign_headfooter_class = 'assign_headfooter';
                    }
                    ?>
                    
                    <div data-count_collection-ajax_load_more="<?php echo $count_collection; ?>" data-section_limit-ajax_load_more="<?php echo $section_limits; ?>" data-sectiontype="<?php echo @$is_free; ?>" data-usertype="<?php echo $usertype; ?>" data-useremail="<?php echo $current_user_email; ?>" id="builder_inner_dragpost_<?php echo $post_id ?>" class="builder_inner_dragpost_sel <?php echo $drag_class ?>">
                        <?php if(@$is_free==1){ ?>
                        <div class="section_type is_free">
                            <!-- <h3>Free</h3> -->
                        </div>
                        <?php } ?>
                        <?php if(isset($is_free) && $is_free==2) { ?>
                        <div class="section_type is_premium">
                            <h3>Premium</h3>
                        </div>
                        <?php } ?> 
                        
                        <div class="card" data-sectiontype="<?php echo @$is_free; ?>" data-usertype="<?php echo $usertype; ?>">
                            <?php if($cats_id==502){ ?>
                                <a class="<?php echo $assign_headfooter_class; ?> assign_headfooter_sel" href="javascript:void(0)" id="<?= $post_id; ?>" data-title="assign_footer">
                                    <img src="<?php echo $feat_image; ?>">
                                    <input type="hidden" id="header_json" value="<?= $json_data; ?>"/>
                                </a>
                            <?php } else if($cats_id==176){ ?>
                                <a class="<?php echo $assign_headfooter_class; ?> assign_headfooter_sel" href="javascript:void(0)" id="<?= $post_id; ?>" data-title="assign_header">
                                    <img src="<?php echo $feat_image; ?>">
                                    <input type="hidden" id="header_json" value="<?= $json_data; ?>"/>
                                </a>                        
                            <?php } else { ?>
                            <div class="action_btns" style="display:none;">
                                <a href="javascript:void(0)" class="builder_uparrow" id="<?php echo $post_id; ?>">&#8593;</a>
                                <a href="javascript:void(0)" class="builder_downarrow" id="<?php echo $post_id; ?>">&#8595;</a>
                                <a href="javascript:void(0)" class="builder_remove_layout" id="<?php echo $post_id; ?>">
                                    <i class="far fa-trash-alt" aria-hidden="true"></i>
                                </a>
                            </div>

                            <div class="builder-dragpost builder-dragpost-sel builder_<?php echo $post_id; ?>" id="<?php echo $post_id; ?>" data-id='<?php echo $cats_id; ?>'  data-sectiontype="<?php echo @$is_free; ?>" data-usertype="<?php echo $usertype; ?>">
                                <img class ="show_clone_img" src="<?php echo $feat_image; ?>" style="display:block;margin:auto;">
                                <input type="hidden" class="builder_layout" value="<?= $json_data; ?>"/>
                                <div class="show_clone_html" style="display: none;"></div>

                            </div>
                            <?php } ?>
                        </div>                  
                    </div>
                <?php } ?>

                <?php if ($section_limits < $count_collection) { ?>
                    <div class="load_more">
                        <a class="default-btn scroll-btn section_more_load" id="ajax_load_<?= $cats_id; ?>" data-id="<?= $cats_id; ?>" data-offset="<?= $next_offset; ?>" ajax-limit="<?= $section_ajax_limits; ?>" total-counts="<?= $count_collection; ?>">Load More</a>
                    </div>
                <?php //} 
                    }
                ?>

                <?php
                wp_reset_postdata();
                die();
            }
        }
        ?>



        <?php
        if ($_REQUEST['action'] == "load_ajax_cats") {
            
            $cats_limits = get_field('category_limit_by_default', 'option');
            $cats_ajax_limits = get_field('category_limit_by_ajax', 'option');

            $section_limits = get_field('section_limit_by_default', 'option');
            $section_ajax_limits = get_field('section_limit_by_default', 'option');
            $total_offset = $section_limits + $section_ajax_limits;


            //Get Users Category
            $users_cat_listing = array(
                'post_type' => 'project',
                'post_status' => 'publish',
                'posts_per_page' => -1,
            );

                if(empty($api_token) ||  $api_token == "disconnect"){
                $users_cat_listing['meta_query'] = array(
                    array(
                        'key'       => 'premium_section',
                        'value'     => 1, //free
                        'compare'   => '=',
                    )
                );
            }
             else{
                 $users_cat_listing['meta_query'] = array(
                    'relation'=>"OR",
                    array(
                        'key'       => 'premium_section',
                        'value'     => 1, //free
                        'compare'   => '=',
                    ),
                       array(
                        'key'       => 'premium_section',
                        'value'     => 2, //free
                        'compare'   => '=',
                    ),
                       array(
                            'key'       => 'builder_custom_cat_user',
                            'value'     => $key_user,
                            'compare'   => 'LIKE',
                        )
                );
            }
            
            $usrcats_data = new WP_Query($users_cat_listing);

            $usrs_cats=array();
            if ($usrcats_data->have_posts()) {
                while ($usrcats_data->have_posts()) {
                    $usrcats_data->the_post();
                    $postid=get_the_id();
                    $categories = get_the_terms($postid, 'project_category');
                    $usrs_cats[]=$categories[0]->term_id;
                }
            }

            $builder_terms = get_terms(
                array(
                    'taxonomy' => 'project_category',
                    'hide_empty' => false,
                    'exclude' => array("127", "128", "1062", "1063"),
                    'include' => $usrs_cats,
                    'meta_query'    => array(
                        'relation' => 'AND',
                        array(
                            'key'       => 'builder_custom_cat_user',
                            'value'     => "",
                            'compare'   => 'NOT EXISTS',
                        )
                    )
                    //'number' => $cats_limits,
                    // 'meta_query' => array(
                    //     'relation' => 'OR',
                    //     array(
                    //         'key' => 'builder_custom_cat_user',
                    //         'compare' => 'NOT EXISTS', // works!
                    //         'value' => '' // This is ignored, but is necessary...
                    //     )
                    // )
                )
            );


            //Total Term Counts
            $builder_terms_count = get_terms(
                array(
                    'taxonomy' => 'project_category',
                    'hide_empty' => false,
                    'exclude' => array("127", "128", "1062", "1063"),
                    'include' => $usrs_cats,
                    'meta_query' => array(
                        'relation' => 'OR',
                        array(
                            'key' => 'builder_custom_cat_user',
                            'compare' => 'NOT EXISTS', // works!
                            'value' => '' // This is ignored, but is necessary...
                        )
                    )
                    
                )
            );



            $count_cats = count($builder_terms_count);

            $i = 1;
            if (isset($builder_terms)) {
                ?>
                <!-- foreach for admin categories display first -->
                <?php foreach ($builder_terms as $builder_cats): ?>
                    <?php $builder_custom_cat_by_user = get_term_meta($builder_cats->term_id, 'builder_custom_cat_user', true); ?>
                    <?php $cat_id = $builder_cats->term_id; ?>

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

                    $count_sections = count(get_posts($cat_post_count));
                    ?>

                    <?php if ($builder_custom_cat_by_user == "") { ?>               
                        <li class="project_section<?php if ($cat_id == 176 || $cat_id == 502) { echo " project_details_menu sliding-buttons";} ?>" id="<?= $builder_cats->term_id; ?>">
                            <a href="javascript:void(0)" class="builder_cats<?php if ($i == 3) { echo " builder_cat_active";}?>" id="<?php echo $builder_cats->term_id; ?>"><?php echo str_replace("Global", "", ucfirst($builder_cats->name)); ?></a>                            
                        </li>                       
                        <?php $i++; ?>
                    <?php } ?>
                <?php endforeach; ?>
                <?php
            } else {
                echo '<p class="um-notice warning">Sorry, No Category found on the server</p>';
            }
        }
        ?>



        <?php if ($_REQUEST['action'] == "pixaby_load") { ?>
            <?php 
            require_once pixr_path.'include/API_call.php';
            $query = $_REQUEST['searchKey'];
            $page = $_REQUEST['pageing'];
            $API = new API_call();
            $API->set_query($query);
            $API->set_page($page);
            $images = $API->call();

            ob_start();

            foreach ($images->hits as $key => $image) { 
                require pixr_path.'template/front/image_element_search.php';
            } 

            $output = ob_get_contents();
            ob_end_clean();
            
            //echo json_encode(array('list'=>$output));
            echo $output;
            die();
            ?>
        <?php } ?>




        <?php if ($_REQUEST['action'] == "pixaby_load_onload") { ?>
            <?php 
            require_once pixr_path.'include/API_call.php';
            $API = new API_call();
            $images = $API->call();
            ob_start();             
            require pixr_path.'template/front/image_list_api.php';           

            $output = ob_get_contents();
            ob_end_clean();
            
            //echo json_encode(array('list'=>$output));
            echo $output;
            die();
            ?>
        <?php } ?>

<?php } ?>    