<?php //Template Name: Retrieve Layout Sidebar API  ?>

<?php 



header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');

 

?>

<?php if (isset($_REQUEST['action'])) { ?>
    <?php 
   global $wpdb;
   //pre($_REQUEST);
    $conn_site = $wpdb->prefix . 'bloxx_apis';
    $current_user_email = $_REQUEST['current_user_email'];
    $api_key=$_REQUEST['builder_key'];
    
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





    if ($_REQUEST['action'] == "section_ajax_load") {
        $cats_limits = get_field('category_limit_by_default', 'option');
        $cats_ajax_limits = get_field('category_limit_by_ajax', 'option');

        $section_limits = get_field('section_limit_by_default', 'option');
        $section_ajax_limits = get_field('section_limit_by_default', 'option');
        $total_offset = $section_limits + $section_ajax_limits;

        extract($_REQUEST);

        if($industry_id=='all'){
            $cat_args = array(
                'post_type' => 'layouts',
                'post_status' => 'publish',
                'meta_key' => 'premium_section',
                'orderby' => 'meta_value_num',
                'order' => 'ASC',
                'posts_per_page' => $section_limits,
                'tax_query' => array(
                    'relation' => 'AND',
                    array(
                        'taxonomy' => 'bloxx_categories',
                        'field'    => 'id',
                        'terms' => $cats_id
                    ),
                ),
                // 'meta_query'    => array(
                //     array(
                //         'key'       => 'builder_custom_cat_user',
                //         'value'     => $key_user,
                //         'compare'   => 'LIKE',
                //     )
                // )
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
                'post_type' => 'layouts',
                'order' => 'desc',
                'posts_per_page' => -1,
                'post_status' => 'publish',

                'tax_query' => [
                    [
                        'taxonomy' => 'bloxx_categories',
                        'field'    => 'id',
                        'terms' => $cats_id
                    ],
                    
                ],
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
            else{
                 $cat_count['meta_query'] = array(
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
            

        }else{
            $cat_args = array(
                'post_type' => 'layouts',
                'post_status' => 'publish',
                'meta_key' => 'premium_section',
                'orderby' => 'meta_value_num',
                'order' => 'ASC',
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
                    'relation' => 'AND',
                    array(
                        'taxonomy' => 'bloxx_categories',
                        'field'    => 'id',
                        'terms' => $cats_id
                    ),
                    array(
                        'taxonomy' => 'service_type',
                        'field'    => 'id',
                        'terms' => $industry_id
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
            }else{
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
                'post_type' => 'layouts',
                'order' => 'desc',
                'posts_per_page' => -1,
                'post_status' => 'publish',
   
                'tax_query' => [
                    [
                        'taxonomy' => 'bloxx_categories',
                        'field'    => 'id',
                        'terms' => $cats_id
                    ],
                    [
                        'taxonomy' => 'service_type',
                        'field'    => 'id',
                        'terms' => $industry_id
                    ],
                ],
            ];
        }

         if(empty($api_token) ||  $api_token == "disconnect"){
                $cat_count['meta_query'] = array(
                    array(
                        'key'       => 'premium_section',
                        'value'     => 1, //free
                        'compare'   => '=',
                    )
                );
            }
           else{
                 $cat_count['meta_query'] = array(
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

        

       // pre($cat_args);

        

        $count_collection = count(get_posts($cat_count));


        $latest_books = new WP_Query($cat_args);
        $enable_header_footer = 1;
        if ($latest_books->have_posts()) {
            ?>
            <section class="builder_posts active_slide" id="cat_post_<?= $cats_id; ?>" style="display:none;">
                <?php
                $f=1;
                while ($latest_books->have_posts()) {
                    $latest_books->the_post();
                    $post_id = get_the_id();
                    $feat_image = wp_get_attachment_url(get_post_thumbnail_id($post_id));
                    $json_data = strip_tags(htmlspecialchars(get_the_content()));
                    $is_free=get_field('premium_section', $post_id);
                    $industries_list = wp_get_post_terms( $post_id, 'service_type', array( 'fields' => 'ids' ) );
                    //print_r( $industries_list );
                    if(count($industries_list) > 0){
                        $industries_list_ids = implode(',', $industries_list);
                    }else{
                        $industries_list_ids = '';
                    }
                    
                    
                    $usertype = $get_usertype_byapi_curl;
                    // if($usertype=='"'.$usertype.'"'){
                    //     $usertype = str_replace('"', '', $usertype);
                    // }else{
                    //     $usertype = $usertype;
                    // }
                    
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

                    
                    

                  // echo '<pre>';
                  //   print_r($response);
                  //   echo '</pre>';


                    ?>
                    <div data-industry_ids="<?php echo $industries_list_ids; ?>" data-sectiontype="<?php echo @$is_free; ?>" data-usertype="<?php echo $usertype; ?>" data-useremail="<?php echo $current_user_email; ?>" id="builder_inner_dragpost_<?php echo $post_id ?>" class="builder_inner_dragpost_sel <?php echo $drag_class ?>">
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
                            
                                <div class="action_btns" style="display:none;">
                                    <a href="javascript:void(0)" class="builder_uparrow" id="<?php echo $post_id; ?>">&#8593;</a>
                                    <a href="javascript:void(0)" class="builder_downarrow" id="<?php echo $post_id; ?>">&#8595;</a>
                                    <a href="javascript:void(0)" class="builder_remove_layout" id="<?php echo $post_id; ?>">
                                        <i class="far fa-trash-alt" aria-hidden="true"></i>
                                    </a>
                                </div>

                                <div class="neo_hover_section" style="display: none;">
                                    <a href="javascript:void(0)" class="neo_hover" id="hover_<?= $post_id ?>" data-id="<?= $post_id ?>" data_image="<?= $feat_image; ?>">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </div>

                                <div class="builder-dragpost builder-dragpost-sel builder-dragpost-sidebar  builder_<?php echo $post_id; ?>" id="<?php echo $post_id; ?>" data-id='<?php echo $cats_id; ?>'  data-sectiontype="<?php echo @$is_free; ?>" data-usertype="<?php echo $usertype; ?>">
                                    <img class ="show_clone_img" src="<?php echo $feat_image; ?>" style="display:block;margin: auto">
                                    <input type="hidden" class="builder_layout" value="<?= $json_data; ?>"/>
                                    <div class="show_clone_html" style="display: none;"></div>

                                </div>
                            <?php // } ?>
                        </div>

                    </div>
                    <?php $f++; ?>
                <?php } ?>                        
                <?php wp_reset_query(); ?>



                <?php //if($the_auth_type==1){
                    // do not show load more button if auth type is simple
                //}
                //else{
                    if ($section_limits < $count_collection) { ?>
                    <div class="load_more">
                        <a class="default-btn scroll-btn layout_more_load" id="ajax_load_<?= $cats_id; ?>" data-id="<?= $cats_id; ?>" data-offset="<?= $total_offset; ?>" ajax-limit="<?= $section_ajax_limits; ?>" total-counts="<?= $count_collection; ?>">Load More</a>
                    </div>
                <?php } 
                    //}
                ?>

                <?php
                echo "</section>";
            }else{
                echo 'No_Layout_Found';
            }
            die();
        }
        ?>



        <?php
        if ($_REQUEST['action'] == "layouts_ajax_load_more") {
            $cats_limits = get_field('category_limit_by_default', 'option');
            $cats_ajax_limits = get_field('category_limit_by_ajax', 'option');

            $section_limits = get_field('section_limit_by_default', 'option');
            $section_ajax_limits = get_field('section_limit_by_default', 'option');

            extract($_REQUEST);
            $next_offset = $ajax_offset + $section_ajax_limits;


            //Get Users Category
            $users_cat_listing = array(
                'post_type' => 'layouts',
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
                    $categories = get_the_terms($postid, 'bloxx_categories');
                     if( $categories != null){

                    $usrs_cats[]=$categories[0]->term_id;
                }
                }
            }


            if($industry_id=='all'){
                $cat_args = array(
                    'post_type' => 'layouts',
                    //'meta_key' => 'premium_section',
                    'orderby' => 'meta_value_num',
                    'order' => 'ASC',
                    'post_status' => 'publish',
                    'posts_per_page' => $ajax_offset,
                    //'offset' => $ajax_offset,
                    'include' => $usrs_cats,
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'bloxx_categories',
                            'field'    => 'id',
                            'terms' => $cats_id,
                        )
                    )
                );

                $cat_count = [
                    'post_type' => 'layouts',
                    'order' => 'desc',
                    'posts_per_page' => -1,
                    'post_status' => 'publish',
                    'include' => $usrs_cats,
                    'tax_query' => [
                        [
                            'taxonomy' => 'bloxx_categories',
                            'field'    => 'id',
                            'terms' => $cats_id,
                            
                        ],
                    ],
                ];

            }else{
                $cat_args = array(
                    'post_type' => 'layouts',
                    //'meta_key' => 'premium_section',
                    'include' => $usrs_cats,
                    'orderby' => 'meta_value_num',
                    'order' => 'ASC',
                    'post_status' => 'publish',
                    'posts_per_page' => $ajax_offset,
                    //'offset' => $ajax_offset,
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'bloxx_categories',
                            'field'    => 'id',
                            'terms' => $cats_id
                        ),
                        array(
                            'taxonomy' => 'service_type',
                            'field'    => 'id',
                            'terms' => $industry_id
                        )
                    ),
                );

                $cat_count = [
                    'post_type' => 'layouts',
                    'order' => 'desc',
                    'include' => $usrs_cats,
                    'posts_per_page' => -1,
                    'post_status' => 'publish',
                    'tax_query' => [
                        [
                            'taxonomy' => 'bloxx_categories',
                            'field'    => 'id',
                            'terms' => $cats_id
                        ],
                        [
                            'taxonomy' => 'service_type',
                            'field'    => 'id',
                            'terms' => $industry_id
                        ],
                    ],
                ];
            }


            

            $count_collection = count(get_posts($cat_count));

            $latest_books = new WP_Query($cat_args);

            if ($latest_books->have_posts()) {

                while ($latest_books->have_posts()) {
                    $latest_books->the_post();
                    $post_id = get_the_id();
                    $feat_image = wp_get_attachment_url(get_post_thumbnail_id($post_id));
                    $json_data = strip_tags(htmlspecialchars(get_the_content()));
                    $is_free=get_field('premium_section', $post_id);

                    $industries_list = wp_get_post_terms( $post_id, 'service_type', array( 'fields' => 'ids' ) );
                    //print_r( $industries_list );
                    if(count($industries_list) > 0){
                        $industries_list_ids = implode(',', $industries_list);
                    }else{
                        $industries_list_ids = '';
                    }


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
                     <div data-industry_ids="<?php echo $industries_list_ids; ?>" data-sectiontype="<?php echo @$is_free; ?>" data-usertype="<?php echo $usertype; ?>" data-useremail="<?php echo $current_user_email; ?>" id="builder_inner_dragpost_<?php echo $post_id ?>" class="builder_inner_dragpost_sel <?php echo $drag_class ?>">
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
                           
                            <div class="action_btns" style="display:none;">
                                <a href="javascript:void(0)" class="builder_uparrow" id="<?php echo $post_id; ?>">&#8593;</a>
                                <a href="javascript:void(0)" class="builder_downarrow" id="<?php echo $post_id; ?>">&#8595;</a>
                                <a href="javascript:void(0)" class="builder_remove_layout" id="<?php echo $post_id; ?>">
                                    <i class="far fa-trash-alt" aria-hidden="true"></i>
                                </a>
                            </div>

                            <div class="neo_hover_section" style="display: none;">
                                <a href="javascript:void(0)" class="neo_hover" id="hover_<?= $post_id ?>" data-id="<?= $post_id ?>" data_image="<?= $feat_image; ?>">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </div>

                            <div class="builder-dragpost builder-dragpost-sel builder_<?php echo $post_id; ?>" id="<?php echo $post_id; ?>" data-id='<?php echo $cats_id; ?>'  data-sectiontype="<?php echo @$is_free; ?>" data-usertype="<?php echo $usertype; ?>">
                                <img class ="show_clone_img" src="<?php echo $feat_image; ?>" style="display:block;margin:auto;">
                                <input type="hidden" class="builder_layout" value="<?= $json_data; ?>"/>
                                <div class="show_clone_html" style="display: none;"></div>

                            </div>
                           
                        </div>                  
                    </div>
                <?php } ?>



                <?php //if($the_auth_type==1){
                    // do not show load more button if auth type is simple
               // }
               // else{
                    if ($ajax_offset < $count_collection) { ?>
                    <div class="load_more">
                        <a class="default-btn scroll-btn layout_more_load" id="ajax_load_<?= $cats_id; ?>" data-id="<?= $cats_id; ?>" data-offset="<?= $next_offset; ?>" ajax-limit="<?= $section_ajax_limits; ?>" total-counts="<?= $count_collection; ?>">Load More</a>
                    </div>
                <?php } 
                   // }
                ?>

                <?php
                wp_reset_postdata();
                die();
            }
        }
        


        //Load Page Industry categories

        if ($_REQUEST['action'] == "load_ajax_industries") {
            
            //Get Users Category
            $users_cat_listing = array(
                'post_type' => 'layouts',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                // 'meta_query'    => array(
                //     //'relation' => 'AND',
                //     array(
                //         'key'       => 'builder_custom_cat_user',
                //         'value'     => $key_user,
                //         'compare'   => 'LIKE',
                //     )
                // )
            );
            $usrcats_data = new WP_Query($users_cat_listing);

            $usrs_cats=array();
            if ($usrcats_data->have_posts()) {
                while ($usrcats_data->have_posts()) {
                    $usrcats_data->the_post();
                    $postid=get_the_id();
                    $categories = get_the_terms($postid, 'service_type');
                    if($categories != null){
                         $usrs_cats[]=$categories[0]->term_id;
                    }
                   
                }
            }


           
            $args = array( 'hide_empty=0', 'include' => $usrs_cats );
            $terms = get_terms( 'service_type', $args);
           
            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
               // pre($term);
                echo '<input type="hidden" id="service_type_id" value="">';
                echo '<select name="service_type" id="service_type">';
                echo '<option value="all">All Industries</option>';
                foreach ( $terms as $term ) {
                    echo '<option value="'.$term->term_id.'">' . $term->name . '</option>';
                }
                echo '</select>';
            }else{
                echo '<h3>No Industries Found.</h3>';
            }
            echo '</select>';
            // end layout industries 

        }



        if ($_REQUEST['action'] == "load_ajax_cats") {
           extract($_REQUEST);
            //pre($_REQUEST);
            
            $cats_limits = get_field('category_limit_by_default', 'option');
            $cats_ajax_limits = get_field('category_limit_by_ajax', 'option');

            $section_limits = get_field('section_limit_by_default', 'option');
            $section_ajax_limits = get_field('section_limit_by_default', 'option');
            $total_offset = $section_limits + $section_ajax_limits;



            //Get Users Category
            $users_cat_listing = array(
                'post_type' => 'layouts',
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
            }else{
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
                    if($industry_id=='all'){
                        $categories = get_the_terms($postid, 'bloxx_categories');
                    } else {
                        $categories = get_the_terms($postid, 'service_type');
                    }
                    if( $categories != null){
                         $usrs_cats[]=$categories[0]->term_id;
                    }
                   
                }
            }
            
            $builder_terms = get_bloxx_terms_filter_industry_id($industry_id, $usrs_cats, $key_user);
            $i = 1;
            if (count($builder_terms) > 0) {
                foreach ($builder_terms as $termid):
                    $term = get_term_by('id', $termid, 'bloxx_categories');  
                    $term_id = $term->term_id;
                    $term_bame = $term->name;
                    ?>
                        <li data-id="layout_cat_id<?php echo $term_id; ?>" class="project_section<?php if ($term_id == 176 || $term_id == 502) { echo " project_details_menu sliding-buttons";} ?>" id="<?= $term_id; ?>">
                            <a href="javascript:void(0)" class="builder_cats<?php if ($i == 3) { echo " builder_cat_active";}?>" id="<?php echo $term_id; ?>"><?php echo str_replace("Global", "", ucfirst($term_bame)); ?></a>                            
                        </li>                       
                        <?php $i++; ?>
                    
                <?php 
                endforeach; 
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