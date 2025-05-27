<?php //Template Name: Global Section Sidebar API  ?>

<?php 



header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');


?>

<?php if (isset($_REQUEST['action'])) { ?>
    <?php 
    global $wpdb;

    //

    $conn_site = $wpdb->prefix . 'bloxx_apis';
    $current_user_email = $_REQUEST['current_user_email'];
    $api_key=@$_REQUEST['builder_key'];
    
    $website_url = $_REQUEST['site_url'];
    $api_token=$_REQUEST['api_token'];
    $get_usertype_byapi_curl = get_usertype_byapi_curl($current_user_email,$website_url,$api_key);

   // pre($_REQUEST);

   //die('type=>'.$get_usertype_byapi_curl);
    
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


    $get_sitebloxx_userid_from_siteurl = get_sitebloxx_userid_from_siteurl($_REQUEST['site_url']); // this function defined in divi_builder.php

    if ($_REQUEST['action'] == "section_ajax_load") {
        $cats_limits = get_field('category_limit_by_default', 'option');
        $cats_ajax_limits = get_field('category_limit_by_ajax', 'option');

        $section_limits = get_field('section_limit_by_default', 'option');
        $section_ajax_limits = get_field('section_limit_by_default', 'option');
        $total_offset = $section_limits + $section_ajax_limits;

        extract($_REQUEST);




        // echo 'auth_type=>'.$the_auth_type;
        /*
        if($the_auth_type==1){
             // do not show premium sections
              $simple_auth_type_meta_query = array(
                'key' => 'premium_section',
                'value' => 2,
                'compare' => '!=',
                'type' => 'NUMERIC',
            );

            $section_limits = 2; // show two free categories only
        }else{
             $simple_auth_type_meta_query = array(
                'key' => 'premium_section',
                'value' => 4, // 4 is not used. it means it will always return here
                'compare' => '!=',
                'type' => 'NUMERIC',
            );

             $section_limits = $section_limits; // show default
        }   
        */

        $cat_args = array(
            'post_type' => 'project',
            'meta_key' => 'premium_section',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'post_status' => 'publish',
            'posts_per_page' => $section_limits,
            'tax_query' => array(
                array(
                    'taxonomy' => 'project_category',
                    'terms' => $cats_id
                )
            ),
            // 'meta_query' => array(
            //      'relation' => 'AND',
            //      $simple_auth_type_meta_query,
            //    //  $longitude_meta_query,
            //   ),
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

        $count_collection = count(get_posts($cat_count));


        $latest_books = new WP_Query($cat_args);
        $enable_header_footer = 1;
       // echo 'get_sitebloxx_userid_from_siteurl=>'.$get_sitebloxx_userid_from_siteurl;
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

                    $usertype = $get_usertype_byapi_curl;
                    
                    // if app owner's sitebloxx user id matches with user library created user id then show user library post otherwise skip the loop
                    $builder_custom_cat_user = get_post_meta($post_id,'builder_custom_cat_user',true);
                   
                    // $is_free==0 means it is uploaded thru user library
                   // echo 'get_sitebloxx_userid_from_siteurl=>'.$get_sitebloxx_userid_from_siteurl.'<br>';
                  //  echo 'builder_custom_cat_user=>'.$builder_custom_cat_user.'<br>';

                    // section type $is_free = 0 // means user library
                    // section type $is_free = 1 // means free
                    // section type $is_free = 2 // means premium 
				    // if($get_sitebloxx_userid_from_siteurl!=$builder_custom_cat_user && $is_free==0){
                       
        //             	continue;
        //             }




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
                    <div datacheck_normal_get_sitebloxx_userid_from_siteurl="<?php echo $get_sitebloxx_userid_from_siteurl; ?>" datacheck_normal_builder_custom_cat_user="<?php echo $builder_custom_cat_user; ?>" data-sectiontype="<?php echo @$is_free; ?>" data-usertype="<?php echo $usertype; ?>" data-useremail="<?php echo $current_user_email; ?>" id="builder_inner_dragpost_<?php echo $post_id ?>" class="builder_inner_dragpost_sel <?php echo $drag_class ?>">
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
            }else{
                echo 'No Sections Found.';
            }
            die();
        }
        ?>



        <?php
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
                'orderby' => 'meta_value_num',
                'order' => 'ASC',
                //'offset' => $ajax_offset,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'project_category',
                        'terms' => $cats_id
                    )
                ),
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

            $count_collection = count(get_posts($cat_count));

            $latest_books = new WP_Query($cat_args);

            if ($latest_books->have_posts()) {

                while ($latest_books->have_posts()) {
                    $latest_books->the_post();
                    $post_id = get_the_id();
                    $feat_image = wp_get_attachment_url(get_post_thumbnail_id($post_id));
                    $json_data = strip_tags(htmlspecialchars(get_the_content()));
                    $is_free=get_field('premium_section', $post_id);

                    // if app owner's sitebloxx user id matches with user library created user id then show user library post otherwise skip the loop
                    $builder_custom_cat_user = get_post_meta($post_id,'builder_custom_cat_user',true);
                    //echo 'builder_custom_cat_user=>'.$builder_custom_cat_user;
				    // if($get_sitebloxx_userid_from_siteurl!=$builder_custom_cat_user && $is_free==0){
        //             	continue;
        //             }

                   //  echo 'get_sitebloxx_userid_from_siteurl=>'.$get_sitebloxx_userid_from_siteurl.'<br>';
                  //  echo 'builder_custom_cat_user=>'.$builder_custom_cat_user.'<br>';
                    // if($get_sitebloxx_userid_from_siteurl!=$builder_custom_cat_user && $is_free==0){
                    //    // $check1 = 'check1';
                    //     continue;
                    // }


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
                     <div datacheck_ajax="yes" data-sectiontype="<?php echo @$is_free; ?>" data-usertype="<?php echo $usertype; ?>" data-useremail="<?php echo $current_user_email; ?>" id="builder_inner_dragpost_<?php echo $post_id ?>" class="builder_inner_dragpost_sel <?php echo $drag_class ?>">
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

                <?php //if($the_auth_type==1){
                    // do not show load more button if auth type is simple
               // }
              //  else{
                    if ($section_limits < $count_collection) { ?>
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

            $builder_terms = get_terms(
                    array(
                        'taxonomy' => 'project_category',
                        'hide_empty' => false,
                        'exclude' => array("127", "128", "1062", "1063"),
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
                    'taxonomy' => 'project_category',
                    'hide_empty' => false,
                    'exclude' => array("127", "128", "1062", "1063"),
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