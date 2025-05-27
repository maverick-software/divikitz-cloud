<?php //Template Name: Global Layout Sidebar API  ?>

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

   // pre($_REQUEST);

   // die('type=>'.$get_usertype_byapi_curl);
    
    //$verify_query = "SELECT * FROM $conn_site where api_key='$api_key' and api_token='$api_token' order by id desc limit 1"; // old query
    // $verify_query = "SELECT * FROM $conn_site where api_key='$api_key' and api_token='$api_token' order by id desc limit 1";
    
    // $verify_result = $wpdb->get_results($verify_query);
    // $count_verify = count($verify_result);

    /*
    $the_auth_type=0; // 
    if(isset($_REQUEST['auth_type']) && $_REQUEST['auth_type']=='simple'){
        global $wpdb;
        $connected_sites_tbl = $wpdb->prefix . 'connected_sites';
        $siteblox_key = $_REQUEST['builder_key'];
        $site_url = $_REQUEST['site_url'];
        
        $q="select * from $connected_sites_tbl where site_url = '$site_url' and 
        siteblox_key='$siteblox_key' and is_connect='1' order by id desc limit 1";       
        
        $getrows = $wpdb->get_results($q);
       // echo count($getrows);
        if(count($getrows) > 0){
            $count_verify = 1;
            $the_auth_type=1; // 
        }else{
            $count_verify = 0; // it means api key and url not matched
            $the_auth_type=0; // 
        }
    }
    */


    // commented this code because user will always have access to data. We will check in below loops if section is free or paid
    /*
    if($count_verify==0){
        echo "Data_not_found";
        die();
    }
    */

    ?>


    <?php


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
            );

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

        }else{
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
                    
                    // if(isset($usertype)){
                    //     if(@$is_free==2 && $usertype=='free'){
                    //         $assign_headfooter_class = '';
                    //         $drag_class = '';
                    //     }else{
                    //         $drag_class = 'builder_inner_dragpost connectedSortable';
                    //         $assign_headfooter_class = 'assign_headfooter';
                    //     }
                    // }

                    $drag_class = 'builder_inner_dragpost connectedSortable';
                    $assign_headfooter_class = 'assign_headfooter';
                    

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

            if($industry_id=='all'){
                $cat_args = array(
                    'post_type' => 'layouts',
                    'meta_key' => 'premium_section',
                    'orderby' => 'meta_value_num',
                    'order' => 'ASC',
                    'post_status' => 'publish',
                    'posts_per_page' => $ajax_offset,
                    //'offset' => $ajax_offset,
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
                    'meta_key' => 'premium_section',
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
                    
                   // if(isset($usertype)){
                   //      if(@$is_free==2 && $usertype=='free'){
                   //          $assign_headfooter_class = '';
                   //          $drag_class = '';
                   //      }else{
                   //          $drag_class = 'builder_inner_dragpost connectedSortable';
                   //          $assign_headfooter_class = 'assign_headfooter';
                   //      }
                   //  }

                    $drag_class = 'builder_inner_dragpost connectedSortable';
                    $assign_headfooter_class = 'assign_headfooter';

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
        ?>



        <?php
        

         if ($_REQUEST['action'] == "load_ajax_industries") {
            
            // start layout industries 
            $args = array( 'hide_empty=0' );
            $terms = get_terms( 'service_type',$args );
           // pre($terms);
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

            $builder_terms = get_bloxx_terms_by_industry_id($industry_id);
            $i = 1;
            if (count($builder_terms) > 0) {
                ?>
                <!-- foreach for admin categories display first -->
                <?php 

                //pre($builder_terms);
                foreach ($builder_terms as $termid): ?>
                    <?php //$builder_custom_cat_by_user = get_term_meta($builder_cats->term_id, 'builder_custom_cat_user', true); ?>
                    <?php //$cat_id = $builder_cats->term_id; 
                    $term = get_term_by('id', $termid, 'bloxx_categories');  
                    $term_id = $term->term_id;
                    $term_bame = $term->name;
                    ?>
                        <li data-id="layout_cat_id<?php echo $term_id; ?>" class="project_section<?php if ($term_id == 176 || $term_id == 502) { echo " project_details_menu sliding-buttons";} ?>" id="<?= $term_id; ?>">
                            <a href="javascript:void(0)" class="builder_cats<?php if ($i == 3) { echo " builder_cat_active";}?>" id="<?php echo $term_id; ?>"><?php echo str_replace("Global", "", ucfirst($term_bame)); ?></a>                            
                        </li>                       
                        <?php $i++; ?>
                    
                <?php endforeach; ?>
                <?php
            } else {
                echo '<p class="um-notice warning">Sorry, No Category found on the server</p>';
            }

            /*
            $builder_terms = get_terms(
                    array(
                        'taxonomy' => 'bloxx_categories',
                        'hide_empty' => false,
                       // 'exclude' => array("127", "128"),
                        //'number' => $cats_limits,
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


            //Total Term Counts
            $builder_terms_count = get_terms(
                array(
                    'taxonomy' => 'bloxx_categories',
                    'hide_empty' => false,
                   // 'exclude' => array("127", "128"),
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
                <?php 

                pre($builder_terms);
                foreach ($builder_terms as $builder_cats): ?>
                    <?php $builder_custom_cat_by_user = get_term_meta($builder_cats->term_id, 'builder_custom_cat_user', true); ?>
                    <?php $cat_id = $builder_cats->term_id; ?>

                    <?php
                    $cat_post_count = [
                        'post_type' => 'layouts',
                        'order' => 'desc',
                        'posts_per_page' => -1,
                        'tax_query' => [
                            [
                                'taxonomy' => 'bloxx_categories',
                                'terms' => $cat_id
                            ],
                        ],
                    ];

                    $count_sections = count(get_posts($cat_post_count));
                    ?>

                    <?php if ($builder_custom_cat_by_user == "") { ?>               
                        <li data-id="layout_cat_id<?php echo $cat_id; ?>" class="project_section<?php if ($cat_id == 176 || $cat_id == 502) { echo " project_details_menu sliding-buttons";} ?>" id="<?= $builder_cats->term_id; ?>">
                            <a href="javascript:void(0)" class="builder_cats<?php if ($i == 3) { echo " builder_cat_active";}?>" id="<?php echo $builder_cats->term_id; ?>"><?php echo str_replace("Global", "", ucfirst($builder_cats->name)); ?></a>                            
                        </li>                       
                        <?php $i++; ?>
                    <?php } ?>
                <?php endforeach; ?>
                <?php
            } else {
                echo '<p class="um-notice warning">Sorry, No Category found on the server</p>';
            }
            */
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