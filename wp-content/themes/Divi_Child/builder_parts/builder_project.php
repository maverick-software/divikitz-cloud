<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


class Builder_project{
	
	public function __construct(){
		add_shortcode('builder_projects', array($this, 'builder_projects'));
		add_action("wp_ajax_duplicate_content", array($this, "duplicate_content"));
		add_action("wp_ajax_nopriv_duplicate_content", array($this, "duplicate_content"));

		add_action("wp_ajax_template_data", array($this, "template_data"));
		add_action("wp_ajax_nopriv_template_data", array($this, "template_data"));

		add_action("wp_ajax_builder_createpage", array($this, "builder_createpage"));
		add_action("wp_ajax_nopriv_builder_createpage", array($this, "builder_createpage"));

		add_action("wp_ajax_share_update_exclude_url", array($this, "share_update_exclude_url"));
		add_action("wp_ajax_nopriv_share_update_exclude_url", array($this, "share_update_exclude_url"));

		add_action("wp_ajax_builder_order_page", array($this, "builder_order_page"));
		add_action("wp_ajax_nopriv_builder_order_page", array($this, "builder_order_page"));	
		
	}


	public function builder_order_page(){
		//echo '<pre>';
		//print_r($_POST['jsarr']);
		$jsarr = $_POST['jsarr'];
		if(!empty($jsarr)){
			foreach ($jsarr as $key => $value) {
				$post_id = str_replace("builder_column_", "", $value['id']);
				$position =  $value['position'];
				update_post_meta($post_id,'divipage_order',$position);
			}
		}
		exit;
	}

	public function share_update_exclude_url(){
		$termID = $_POST['data'];
		$excludeURL = get_option('um_options');
		//print_r($excludeURL);
		$args= array(
			'post_type'=>'customer_templates',
			'posts_per_page' => -1,
			'post_status' => 'public',
			'tax_query' => array(
				array(
					'taxonomy' => 'project_categories',
					'field' => 'term_id',
					'terms' => $termID,
				)
			)
		); 
		//print_r($args);
		$query = new WP_Query($args); 
		if($query->have_posts()){
			while($query->have_posts()){
				$query->the_post();
				$pageLink = get_the_permalink();
				//echo $pageLink.'<br>';
				if (!in_array($pageLink, $excludeURL['access_exclude_uris'])){
					array_push($excludeURL['access_exclude_uris'],$pageLink);
				}
			}
		}
		update_option( 'um_options', $excludeURL );
		$result=array(
			'code' 		=> 200,				
			'message' 	=> 'Updated exclude url successfully....'
		);
		echo json_encode($result);
		
		
		die();
	}

	public function builder_createpage(){
		extract($_REQUEST);
		$current_user = wp_get_current_user();
		$current_user_id= $current_user->ID;


		$post_slug= str_replace(" ", "-", strtolower($pnm));
		$create_pg = array(
			'post_title' => $pnm,
			'post_content'  => '',
			'post_status' 	=> 'publish',
			'post_name' 	=> $post_slug,
			'post_type' 	=> 'customer_templates',
		);		 
		// Insert the post into the database
		$pid= wp_insert_post( $create_pg );
		wp_set_object_terms( $pid, intval($pcat_id), 'project_categories' );
		update_post_meta( $pid, '_et_pb_use_builder', "on" );
		update_post_meta( $pid, 'divi_content_arr', '');
		update_post_meta($pid,'_et_pb_post_hide_nav','default');
		update_post_meta($pid,'_et_pb_project_nav','off');
		update_post_meta($pid,'_et_pb_page_layout','et_no_sidebar');
		update_post_meta($pid,'_et_pb_side_nav','off');
		update_post_meta($pid,'_et_pb_use_builder','on');
		update_post_meta($pid,'template_user', $current_user_id);
		$get_link= get_the_permalink($pid);
		$result=array(
			'code' 		=> 200,
			'pid'		=> $pid,
			'builder_link'=> $get_link,			
			'message' 	=> 'Page created Successfully'
		);
		echo json_encode($result);
		die();
	}


	public function duplicate_content(){
		$post_id = $_REQUEST['id'];
		$cat_id = $_REQUEST['cat_id'];

		$project = get_post( $post_id );
		$post_content= $project->post_content;
		$new_post = array(
			'post_title' => $project->post_title." copy",
			'post_content' => $post_content,
			'post_status' => 'publish',
			'post_name' => 'pending',
			'post_type' => 'customer_templates'
		);
		$pid = wp_insert_post($new_post);
		wp_set_object_terms( $pid, intval($cat_id), 'project_categories' );

		$current_user = wp_get_current_user();
		$current_user_id= $current_user->ID;
		update_post_meta( $pid, 'template_user', $current_user_id );

		update_post_meta( $pid, 'template_user_temp_builder', get_post_meta( $post_id,'template_user_temp_builder', true ));


		$json_meta= get_post_meta( $post_id,'builder_json', true );


		if($json_meta!=""){
			$file_content = file_get_contents();
			$file_nm=round(microtime(true))."_".$current_user->ID.".json";
			$fp = fopen(builder_path.'assets/builder_json/'.$file_nm, 'w');
			fwrite($fp, $file_content);
			fclose($fp);				
			$json_file_path= builder_url."assets/builder_json/".$file_nm;
			update_post_meta( $pid, 'builder_json', $json_file_path );
		}

		$result=array(
			'code' => 200,					
			'message' => 'Page copied Successfully'
		);
		echo json_encode($result);
		die();

	}


	public function template_data(){
		$post_id = $_REQUEST['id'];
		$template = get_post_meta($post_id, 'template_user_temp_builder', true);
		$template = html_entity_decode(unserialize($template));

		$result =	array(
			'code' => 200,					
			'template' => trim('<style>.builder_remove_layout{ display:none;}</style>'.$template), 
			'message' => 'Project viewed on popup successfully'
		);

		echo json_encode($result);
		die();
	}


	public function builder_projects(){
		if(is_user_logged_in()){			
			ob_start();
			?>
<?php 
			if(!isset($_REQUEST['term_id'])){
				$this->restricate_page_without_cats();
			} 

			$term_4id= (isset($_GET['term_id'])) ? $_GET['term_id'] : 0;

			$current_user = wp_get_current_user();
			$current_user_id= $current_user->ID;
			$term_user_id= get_term_meta($term_4id, 'builder_cat_user', true);

			if($current_user_id!=$term_user_id){
				$this->restricate_page_without_cats();
			}

			$category_data = get_term_by('id', $_REQUEST['term_id'], 'project_categories');
			?>

<div class="contentWrapper user_action" id="table-page">
    <!-- //sidebar  -->
    <?php require_once 'builder_siderbar.php'; ?>
    <div class="wrapContent">
        <!-- Top Nav Bar -->
        <div class="topWrapmenu">
            <div>
                <a href="javascript:void(0);" class="togglebar"><img
                        src="<?php  echo plugins_url(); ?>/divi-builder/images/right-angle.png" /></a>
            </div>
            <div class="rowWrap">
                <div class="flex-6">
                    <h2 class="project-title">
                        <?php 
									if(!empty($category_data)){ 
										echo ucwords($category_data->name)."<span> - App</span>"; 
									} 
									?>
                    </h2>
                </div>
                <div class="flex-6 text-right">
                    <?php 
					 global $wp_roles;
					 global $ultimatemember;
					 $user = wp_get_current_user();
					 $current_user_id= $user->ID; 
					 $display_nm= get_user_meta($current_user_id, "display_name", true);
					 $timestemp= strtotime(date("Y-m-d H:i:s"));
					 $nonce = wp_create_nonce( 'um_upload_nonce-' . $timestemp);

					 um_fetch_user( $current_user_id );
					 
					 $user_profile=get_user_meta($current_user_id, "profile_photo", true);

					 $avatar_uri = um_get_avatar_uri( um_profile('profile_photo'), 32 );
					 if($user_profile==""){
					 	$avatar_uri= builder_url."images/profile-icon.png";
					 }
					 $add_project_url= site_url()."/page-builder?create=1&pcat=".$term_4id;
					 $projectButton='';
					 if(get_page_limit(get_current_user_id()) <= user_page_count(get_current_user_id())) {
									$projectButton .='<a href="javascript:void(0)" class="project-btn" id="add_page_restriction" data-name="'.$term_4id.'" data-id=""><i class="fa fa-plus"></i></a>';
								}else{
									$projectButton .='<a href="javascript:void(0)" class="project-btn" id="add_page_restriction" data-name="'.$term_4id.'" data-id="'.$add_project_url.'"><i class="fa fa-plus"></i></a>';
								}
					 ?>

                    <ul class="topMenuUser">
                        <!-- <li class="searchForm">
					  		<input type="search" class="txtInput" id="searchTheKey" placeholder="Search Sections">
					  		<i class="fa fa-search"></i>
					 	</li>
					 	<li class="storeIcon"><a href="https://sitebloxx.com/"><i class="fa fa-shopping-basket"></i> Store</a></li> -->
                        <li class="plusSign">
                            <?php // echo $projectButton; ?>
							<a href="javascript:void(0)" class="project-btn buttonView"><i class="fa fa-plus"></i></a>
							<ul class="dropdownList">
								<li><a href="javascript:;">Apps</a></li>
								<div class="dividerLine"></div>
								<li><a href="javascript:;">Sections</a></li>
								<div class="dividerLine"></div>
								<li><a href="javascript:;">Pages</a></li>
							</ul>
                        </li>
                        <li>
							<a href="javascript:;"><i class="fa fa-bell-o"></i></a>
						</li>
                        <li>
                            <a class="buttonView" href="javascript:void(0)">
                                <img src="<?= $avatar_uri; ?>" />
                            </a>

                            <ul class="dropdownList">
                                <div class="um-header">
                                    <div class="um-profile-photo um-trigger-menu-on-click"
                                        data-user_id="<?= $current_user_id ?>">
                                        <a href="<?php echo site_url(); ?>/user/<?= $display_nm; ?>/"
                                            class="um-profile-photo-img" title="<?= $display_nm; ?>">
                                            <span class="um-profile-photo-overlay">
                                                <span class="um-profile-photo-overlay-s">
                                                    <ins><i class="um-faicon-camera"></i></ins>
                                                </span>
                                            </span>
                                            <img width="190" height="190" alt="<?= $display_nm; ?>"
                                                data-default="<?= $avatar_uri; ?>" data-src="<?= $avatar_uri; ?>"
                                                class="gravatar avatar avatar-190 um-avatar um-avatar-default lazyloaded"
                                                src="<?= $avatar_uri; ?>">
                                        </a>
                                        <?= $display_nm; ?>

                                        <div style="display: none !important;">
                                            <div id="um_field__profile_photo"
                                                class="um-field um-field-image  um-field-profile_photo um-field-image um-field-type_image"
                                                data-key="profile_photo" data-mode="" data-upload-label="Upload">
                                                <input type="hidden" name="profile_photo" id="profile_photo"
                                                    value="profile_photo.png">

                                                <div class="um-field-label">
                                                    <label for="profile_photo">Change your profile photo</label>
                                                    <div class="um-clear"></div>
                                                </div>

                                                <div class="um-field-area" style="text-align: center;">

                                                    <div class="um-single-image-preview crop" data-crop="square"
                                                        data-key="profile_photo" style="display: block;">
                                                        <a href="javascript:void(0);" class="cancel"><i
                                                                class="um-icon-close"></i></a>
                                                        <img src="<?php echo site_url(); ?>/wp-content/uploads/ultimatemember/<?= $current_user_id ?>/profile_photo.png?1629268323315?1629268326415"
                                                            alt="">
                                                        <div class="um-clear"></div>
                                                    </div>

                                                    <a href="javascript:void(0);" data-modal="um_upload_single"
                                                        data-modal-size="normal" data-modal-copy="1"
                                                        class="um-button um-btn-auto-width">Change photo</a>
                                                </div>

                                                <div class="um-modal-hidden-content">
                                                    <div class="um-modal-header"> Change your profile photo</div>
                                                    <div class="um-modal-body">
                                                        <div class="um-single-image-preview crop" data-crop="square"
                                                            data-ratio="1" data-min_width="190" data-min_height="190"
                                                            data-coord="">
                                                            <a href="javascript:void(0);" class="cancel"><i
                                                                    class="um-icon-close"></i></a>
                                                            <img src="" alt="">
                                                            <div class="um-clear"></div>
                                                        </div>

                                                        <div class="um-clear"></div>

                                                        <div class="um-single-image-upload"
                                                            data-user_id="<?= $current_user_id ?>"
                                                            data-nonce="<?= $nonce; ?>"
                                                            data-timestamp="<?= $timestemp; ?>"
                                                            data-icon="um-faicon-camera" data-set_id="0"
                                                            data-set_mode="" data-type="image" data-key="profile_photo"
                                                            data-max_size="999999999"
                                                            data-max_size_error="This image is too large!"
                                                            data-min_size_error="This image is too small!"
                                                            data-extension_error="Sorry this is not a valid image."
                                                            data-allowed_types="gif,jpg,jpeg,png"
                                                            data-upload_text="Upload your profile image"
                                                            data-max_files_error="You can only upload one image"
                                                            data-upload_help_text="">Upload</div>

                                                        <div class="um-modal-footer">
                                                            <div class="um-modal-right">
                                                                <a href="javascript:void(0);"
                                                                    class="um-modal-btn um-finish-upload image disabled"
                                                                    data-key="profile_photo" data-change="Change photo"
                                                                    data-processing="Processing..."> Apply</a>
                                                                <a href="javascript:void(0);" class="um-modal-btn alt"
                                                                    data-action="um_remove_modal"> Cancel</a>
                                                            </div>
                                                            <div class="um-clear"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="um-dropdown" data-element="div.um-profile-photo" data-position="bc"
                                            data-trigger="click"
                                            style="top: 43.5px; width: 200px; left: -25px; right: auto; text-align: center; display: none;">
                                            <div class="um-dropdown-b">
                                                <div class="um-dropdown-arr"
                                                    style="top: -17px; left: 87px; right: auto;"><i
                                                        class="um-icon-arrow-up-b"></i>
                                                </div>

                                                <ul>
                                                    <li><a href="javascript:void(0);" class="um-manual-trigger" data-parent=".um-profile-photo" data-child=".um-btn-auto-width">Upload photo</a></li>
                                                    <li><a href="javascript:void(0);" class="um-dropdown-hide">Cancel</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <!-- <h3><img src="<?php echo builder_url;?>images/profile-icon.png" /> <?php echo ucwords($display_nm); ?></h3> -->
                                <li><a href="<?= site_url().'/bloxx-account/'; ?>">Account Settings</a></li>
                                <div class="dividerLine"></div>
                                <li><a href="javascript:;" class="modalBtn">Subscription <span class="badge">Freelancer</span></a></li>
						                  	<!-- <li><a href="javascript:;">Billing History</a></li> -->
						                  	<div class="dividerLine"></div>
						                  	<li><a href="javascript:;">Create a New Site</a></li>
						                  	<li><a href="javascript:;">Language</a></li>
						                  	<li><a href="javascript:;">Get Support</a></li>
						                  	<!-- <li><a href="javascript:;" class="modalBtn">Upgrade</a></li> -->
                                <li><a href="<?php echo site_url(); ?>/logout">Logout</a></li>
                            </ul>
						</li>
						<li class="builder_layout_exit"><a href="javascript:void(0)" data-id="<?php echo $back_url; ?>" class="exit_builder" title="Exit builder"><img src="<?php echo builder_url; ?>images/doorway.png" alt="Close" /> Exit</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- End Top Nav Bar -->

        <div class="wrapContainer user_actions">
            <a href="<?=site_url();?>/my-projects" class="link-btn"><img
                    src="<?php echo plugins_url(); ?>/divi-builder/images/arrow-alt-circle-left.png" alt="..." /> Go
                Back to Apps</span></a>
            <div class="rowWrap">
                <div class="flex-12">
                    <?php
                    //Hosted Info
                    $app_id= get_term_meta($term_4id, 'bloxx_app_id', true);
                    $meta_key= "website_".$app_id;
					$user_meta=get_user_meta($current_user_id, $meta_key, true); 

					$sync_connected= connected_status($term_4id);
					$args= array(
						'post_type'=>'customer_templates',
						'order'=>'asc',
						'posts_per_page' => -1,
						'meta_query' => array(
							array(
								'key'  => 'template_user',
								'value'  => $current_user_id,
								'compare' => '=',
							)
						),
						'tax_query' => array(
							array(
								'taxonomy' => 'project_categories',
								'field' => 'term_id',
								'terms' => $term_4id,
								'compare' => '=' 
							)
						)
					); 

					$query = new WP_Query($args); ?>


                    <?php if ( $query->have_posts() ) { ?>
                    <?php while ( $query->have_posts() ) { ?>
                    <?php
					$query->the_post();
					$postid=get_the_id();
					$post_nm= get_the_title();
					$post_create_date = get_the_date('M d, Y', $postid);
					$project_link= get_the_permalink();
					$builder_json_link=get_post_meta($postid, 'builder_json', true); 
					$slug = get_post_field( 'post_name', $postid );
					?>

                    <div class="boxWhite">
                        <div class="projectHeader">
                            <div class="imageSec">
                                <span class="proImage"><img
                                        src="<?php echo plugins_url(); ?>/divi-builder/images/project-file-1.jpg"
                                        alt="..." /></span>

                                <div class="builder-box" id="builderBox_<?= $postid; ?>">
                                    <a href="javascript:void(0);" class="builder-edit" title="Edit"
                                        data-id="<?= $postid; ?>" style=""><i class="fa fa-pencil"></i></a>

                                    <a href="javascript:void(0);" class="builder-save builder_live_update"
                                        id="update_page" title="Save" data-id="<?= $postid; ?>"
                                        data-title="<?= $post_nm; ?>" style="display: none;"><i
                                            class="fa fa-save"></i></a>
                                    <a href="javascript:void(0);" class="builder-cancel" id="cancel_project"
                                        title="Cancel" data-id="230" style="display: none;"><i
                                            class="fa fa-times"></i></a>
                                    <div class="builder-text"><?= $post_nm; ?></div>
									<span class="projTitle">
										<?php if($sync_connected['count_data']==1){ ?>
										<p><?php echo $sync_connected['website_url']; ?></p>
										<?php } ?>
									</span>
                                </div>

                            </div>

                            <div class="projButtons">
                                <ul>
                                    <li class="page_option">
                                        <a class="buttonView" href="javascript:;">Site Actions <i
                                                class="fa fa-angle-down"></i></a>
                                        <ul class="dropdownList">
                                            <li><a title="View Page" target="_blank"
                                                    href="<?php echo $project_link; ?>"><i class="fa fa-eye"></i>
                                                    View</a></li>

                                            <?php if($sync_connected['count_data']==1){ ?>
                                            <li>
                                                <a title="Sync Page" class="builder_direct_transfer"
                                                    href="javascript:void(0)"
                                                    id="<?php echo $sync_connected['server_userid']; ?>"
                                                    data-id="<?php echo $postid; ?>"
                                                    data-title="<?php echo $term_4id; ?>"><i
                                                        class="fa fa-refresh"></i> Sync Page</a>
                                            </li>
                                            <?php } ?>



                                            <?php
											$current_user = wp_get_current_user();
											$user = new WP_User( $current_user->ID );

											$freelancer = get_option('freelancer');
											$agency = get_option('agency');
											$team = get_option('team');
											$subscriber = get_option('subscriber');

											if(in_array('um_freelancer', $user->roles)) {
												$freelancer_projects_limit = $freelancer['projects'];
												$current_count = count_user_posts($current_user->ID , "customer_templates" );
												if($freelancer_projects_limit == $current_count) { ?>
                                            		<li class="builder_fa_duplicate"><a href="#" title="Duplicate Page"><i
                                                        class="fa fa-clone"></i> Duplicate</a></li>

                                            	<?php } else { ?>

                                            		<li class="builder_fa_duplicate" data-id="<?php echo $postid; ?>"><a
                                                    href="#" title="Duplicate Page"><i class="fa fa-clone"></i>
                                                    Duplicate Page</a></li>
                                            <?php
												}
											}

				  			// agency
											else if(in_array('um_agency', $user->roles)) {
												$agency_projects_limit = $agency['projects'];
												$current_count = count_user_posts($current_user->ID , "customer_templates" );
												if($agency_projects_limit == $current_count) {
													?>
		                                            <li class="builder_fa_duplicate"><a href="#" title="Duplicate Page"><i
		                                                        class="fa fa-clone"></i> Duplicate Page</a></li>
		                                            <?php } else { ?>
		                                            <li class="builder_fa_duplicate" data-id="<?php echo $postid; ?>"><a
		                                                    href="#" title="Duplicate Page"><i class="fa fa-clone"></i>
		                                                    Duplicate Page</a></li>
                                            <?php
													}
												}
												// team
												else if(in_array('um_team', $user->roles)) {
													$team_projects_limit = $team['projects'];
													$current_count = count_user_posts($current_user->ID , "customer_templates" );
													if($team_projects_limit == $current_count) {
														?>
			                                            <li class="builder_fa_duplicate"><a href="#" title="Duplicate Page"><i
			                                                        class="fa fa-clone"></i> Duplicate Page</a></li>
			                                            <?php
																					} else {
																						?>
			                                            <li class="builder_fa_duplicate" data-id="<?php echo $postid; ?>"><a
			                                                    href="#" title="Duplicate Page"><i class="fa fa-clone"></i>
			                                                    Duplicate Page</a></li>
                                            <?php
												}
											}
									
											// subscriber
											else if(in_array('subscriber', $user->roles)) {
												$subscriber_projects_limit = $subscriber['projects'];
												$current_count = count_user_posts($current_user->ID , "customer_templates" );
												if($subscriber_projects_limit == $current_count) {
													?>
                                            		<li class="builder_fa_duplicate"><a href="javascript:void(0)"
                                                    title="Duplicate Page"><i class="fa fa-clone"></i> Duplicate
                                                    Page</a></li>

                                            <?php } else { ?>
					                        <li class="builder_fa_duplicate"><a id="clone_<?= $postid ?>"
					                                href="javascript:void(0)" title="Duplicate Page"><i
					                                    class="fa fa-clone"></i> Duplicate Page</a></li>
					                        <?php
												}
											} else {
												if(!in_array('um_free', $user->roles)) {
													$cat_id= ($_REQUEST['term_id'])? $_REQUEST['term_id']: '';
													?>
                                            			<li class="builder_fa_duplicate" data-id="<?php echo $postid; ?>"
                                                data-value="<?= $cat_id ;?>"><a id="clone_<?= $postid ?>"
                                                    href="javascript:void(0)" title="Duplicate Page"><i
                                                        class="fa fa-clone"></i> Duplicate Page</a></li>
                                            <?php } 	}?>
                                            <li class="builder_fa_download"><a
                                                    href="<?php echo $project_link; ?>/?et_fb=1&PageSpeed=off"
                                                    title="Edit"><i class="fa fa-edit"></i> Edit With Builder</a></li>

                                            <li class="builder_fa_download"><a href="<?php echo $builder_json_link; ?>"
                                                    title="Import JSON" download><i class="fa fa-download"></i>Download
                                                    JSON</a></li>

                                            <li class="builder_delete_project">
                                                <a href="javascript:void(0)" title="Delete Page" class="rounded-right"
                                                    id="del_page" data-id="<?= $postid ?>"
                                                    data-name="<?= $post_nm; ?> Page"
                                                    data-link="<?php echo $project_link; ?>"><i class="fa fa-trash"></i>
                                                    Move To Trash</a>
                                            </li>
                                        </ul>
                                    </li>

                                    <li class="page_option">
                                        <a class="buttonBackground" href="<?php echo get_the_permalink(); ?>?update=1"
                                            title="Edit <?= $post_nm; ?> Page">Edit Page</a>

                                    </li>
                                </ul>
                            </div>
                        </div>



                        <!--<ul class="appOptions">
                            <li>
                                <span>App Role</span>
                                <p>Owner</p>
                            </li>
                            <li>
                                <span>Hosting</span>
                                <p>Free <a href="javascript:;">Compare Plans</a></p>
                            </li>
                            <li>
                                <span>Mailbox</span>
                                <p>Not Connected <a href="javascript:;">Add New</a></p>
                            </li>
                            <li>
                                <span>Collaborators</span>
                                <p>
                                    <span class="userImgs">
                                        <img src="<?php echo builder_url;?>images/profile-icon.png" />
                                        <img src="<?php echo builder_url;?>images/man-icon.png" />
                                        <img src="<?php echo builder_url;?>images/profile-icon.png" />
                                        <span class="lastNmbr">5+</span>
                                    </span>
                                    <a href="javascript:;">Invite People</a>
                                </p>
                            </li>
                        </ul>-->
                    </div>
                    <?php } ?>
                    <?php } else { ?>
                    <div class="dashboard_no">
                        <div class="box bg-white p-2">
                            <p>Get started building your first web page.</p>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>

            <div class="rowWrap">
                <div class="tabbedPanels flex-12">
                    <ul class="tabs">
                        <li><a class="active" href="#panelGeneral">General</a></li>
						<li><a href="#panelHost">Hosting</a></li>
						<li><a href="#panelThemes">Themes</a></li>
                        <li><a href="#panelPlugins">Plugins</a></li>
						<li><a href="#panelManager">File Manager</a></li>
						<li><a href="#panelPages">Pages</a></li>
						<li><a href="#panelInte">Integrations</a></li>
						<li><a href="#panelChat">Team Chat</a></li>
						<li><a href="#panelTasks">Tasks</a></li>
						<li><a href="#panelActivity">Activity</a></li>
                    </ul>

                    <div class="panelContainer">
                        <div id="panelGeneral" class="panel">
                        	<?php if($app_id!=""){ ?>

                            	<h4>General Settings <button type="button" class="default-btn bg_teal">Save Changes</button></h4>                           

	                            <!-- <form>
	                                <div class="rowWrap align-items-center">
	                                    <div class="flex-7">
	                                        <div class="form-group">
	                                            <label>Name</label>
	                                            <div class="input-group">
	                                                <input type="text" class="form-control" name="name" value="<?= $user_meta->label; ?>" readonly>
	                                            </div>
	                                        </div>
	                                    </div>
	                                    <div class="flex-5">
	                                        <p>This is the project's title within blox. You can update what visitors see in
	                                            search results in each page's settings in the Designer.</p>
	                                    </div>
	                                </div>
	                                <div class="rowWrap align-items-center">
	                                    <div class="flex-7">
	                                        <div class="form-group">
	                                            <label>Subdomain</label>
	                                            <div class="input-group selected">
	                                                <input type="text" class="form-control" name="subdomain">
	                                                <div class="input-group-addon">.bloxxsite.com <i
	                                                        class="fa fa-check-circle"></i></div>
	                                            </div>
	                                        </div>
	                                    </div>
	                                    <div class="flex-5">
	                                        <p>Must be alphanumeric (A-Z, 0-9) with dashes between words. Last published 2
	                                            days ago.</p>
	                                    </div>
	                                </div>
	                                <div class="rowWrap align-items-center">
													<div class="flex-7">
														<div class="form-group">
															<label>Folder</label>
															<div class="input-group">
																<input type="text" class="form-control" name="folder">
															</div>
														</div>
													</div>
													<div class="flex-5">
														<p>Projects can be moved into and out of folders here.</p>
													</div>
												</div>
	                            </form> 
	                            <h4>Application URL</h4>-->
	                            <form>
	                                <div class="rowWrap">
	                                    <div class="flex-7">
	                                        <div class="form-group">
	                                            <div class="input-group">
	                                            	<label>Domain</label>
	                                                <input type="text" class="form-control" name="name" value="https://<?= $user_meta->app_fqdn; ?>" readonly>
	                                            </div>
	                                            <!-- <label><small>For SSH/SFTP access to your Application. Read
	                                                    this</small></label> -->
	                                        </div>
	                                    </div>
	                                </div>
	                            </form>
	                            <h4>Admin Panel</h4>
	                            <form>
	                                <div class="rowWrap">
	                                    <div class="flex-7">
	                                        <div class="form-group mBottom">
	                                            <label>URL</label>
	                                            <div class="input-group">
	                                                <input type="text" class="form-control" name="url" value="https://<?= $user_meta->app_fqdn.$user_meta->backend_url; ?>" readonly>
	                                            </div>
	                                        </div>
	                                        <div class="rowWrap">
	                                            <div class="flex-6">
	                                                <div class="form-group">
	                                                    <label>Username</label>
	                                                    <div class="input-group">
	                                                        <input type="text" class="form-control" name="username" value="<?= $user_meta->app_user; ?>" readonly>
	                                                    </div>
	                                                </div>
	                                            </div>
	                                            <div class="flex-6">
	                                                <div class="form-group">
	                                                    <label>Password</label>
	                                                    <div class="input-group">
															<input type="password" class="form-control" name="password" value="<?= $user_meta->app_password; ?>" readonly>
															<a href="javascript:void(0)" class="toggle_password show"><i class="fa fa-eye"></i></a>
	                                                    </div>
	                                                </div>
	                                            </div>
	                                        </div>
	                                    </div>
	                                </div>
	                            </form>
	                            <div class="uploadIcon d-flex">
	                                <h4>Favicon</h4>
	                                <p><span class="favIcon"><img
	                                            src="<?php echo plugins_url(); ?>/divi-builder/images/logoicon.png"
	                                            alt="..." /></span> Upload a 32 x 32 pixel ICO, PNG, GIF, or JPG to display
	                                    in browser tabs.</p>
	                                <button type="button" class="default-btn"><i class="fa fa-cloud-upload"></i>
	                                    Upload</button>
	                            </div>
	                            <div class="uploadIcon d-flex">
	                                <h4>Webclip</h4>
	                                <p><span class="favIcon"><img
	                                            src="<?php echo plugins_url(); ?>/divi-builder/images/logoicon.png"
	                                            alt="..." /> <i class="fa fa-close"></i></span> Upload a 256 x 256 pixel
	                                    webclip image. This icon shows up when your website link is saved to an iPhone home
	                                    screen.</p>
	                                <button type="button" class="default-btn"><i class="fa fa-cloud-upload"></i>
	                                    Upload</button>
	                            </div>
	                            <div class="uploadIcon">
	                                <h4>Localization</h4>
	                                <label>Choose the time xone form the list.</label>
	                                <select name="timezone_offset" id="timezone-offset" class="span5">
	                                    <option value="-12:00">(GMT -12:00) Eniwetok, Kwajalein</option>
	                                    <option value="-11:00">(GMT -11:00) Midway Island, Samoa</option>
	                                    <option value="-10:00">(GMT -10:00) Hawaii</option>
	                                    <option value="-09:50">(GMT -9:30) Taiohae</option>
	                                    <option value="-09:00">(GMT -9:00) Alaska</option>
	                                    <option value="-08:00">(GMT -8:00) Pacific Time (US & Canada)</option>
	                                    <option value="-07:00">(GMT -7:00) Mountain Time (US & Canada)</option>
	                                    <option value="-06:00">(GMT -6:00) Central Time (US & Canada), Mexico City
	                                    </option>
	                                    <option value="-05:00">(GMT -5:00) Eastern Time (US & Canada), Bogota, Lima
	                                    </option>
	                                    <option value="-04:50">(GMT -4:30) Caracas</option>
	                                    <option value="-04:00">(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz</option>
	                                    <option value="-03:50">(GMT -3:30) Newfoundland</option>
	                                    <option value="-03:00">(GMT -3:00) Brazil, Buenos Aires, Georgetown</option>
	                                    <option value="-02:00">(GMT -2:00) Mid-Atlantic</option>
	                                    <option value="-01:00">(GMT -1:00) Azores, Cape Verde Islands</option>
	                                    <option value="+00:00" selected="selected">(GMT) Western Europe Time, London,
	                                        Lisbon, Casablanca</option>
	                                    <option value="+01:00">(GMT +1:00) Brussels, Copenhagen, Madrid, Paris</option>
	                                    <option value="+02:00">(GMT +2:00) Kaliningrad, South Africa</option>
	                                    <option value="+03:00">(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg</option>
	                                    <option value="+03:50">(GMT +3:30) Tehran</option>
	                                    <option value="+04:00">(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi</option>
	                                    <option value="+04:50">(GMT +4:30) Kabul</option>
	                                    <option value="+05:00">(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent
	                                    </option>
	                                    <option value="+05:50">(GMT +5:30) Bombay, Calcutta, Madras, New Delhi</option>
	                                    <option value="+05:75">(GMT +5:45) Kathmandu, Pokhara</option>
	                                    <option value="+06:00">(GMT +6:00) Almaty, Dhaka, Colombo</option>
	                                    <option value="+06:50">(GMT +6:30) Yangon, Mandalay</option>
	                                    <option value="+07:00">(GMT +7:00) Bangkok, Hanoi, Jakarta</option>
	                                    <option value="+08:00">(GMT +8:00) Beijing, Perth, Singapore, Hong Kong</option>
	                                    <option value="+08:75">(GMT +8:45) Eucla</option>
	                                    <option value="+09:00">(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk</option>
	                                    <option value="+09:50">(GMT +9:30) Adelaide, Darwin</option>
	                                    <option value="+10:00">(GMT +10:00) Eastern Australia, Guam, Vladivostok</option>
	                                    <option value="+10:50">(GMT +10:30) Lord Howe Island</option>
	                                    <option value="+11:00">(GMT +11:00) Magadan, Solomon Islands, New Caledonia</option>
	                                    <option value="+11:50">(GMT +11:30) Norfolk Island</option>
	                                    <option value="+12:00">(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka</option>
	                                    <option value="+12:75">(GMT +12:45) Chatham Islands</option>
	                                    <option value="+13:00">(GMT +13:00) Apia, Nukualofa</option>
	                                    <option value="+14:00">(GMT +14:00) Line Islands, Tokelau</option>
	                                </select>
	                            </div>
                            <?php } else {  ?>                            	
                            	<div class="flex-12 dashboard_no">
                            		<h4>General Settings</h4>                            		
									<div class="box bg-white p-2">
										<p>You didn't hosted with us</p>
									</div>
								</div>
                            <?php } ?>

                        </div>
                        <div id="panelHost" class="panel">
                           <?php if($app_id!=""){ ?>
	                            <div class="panelFlex">
									<ul id="tab-links">
										<li><a class="active" href="#accessTab">Access Details</a></li>
										<li><a href="#domainTab">Domain Management</a></li>
										<li><a href="#sslTab">SSL Management</a></li>
										<li><a href="#backTab" id="bckandrest_tabs">Backup and Restore</a></li>
										<li><a href="#plansTab">Site Plans</a></li>
									</ul>
									<div class="panelContainer">
										<div id="accessTab" class="active subpanel">
											<div class="rowWrap">
												<div class="flex-6">
													<h4>Application URL</h4>
													<p><?= $user_meta->app_fqdn; ?> <a target="_blank" href="<?php echo "https://".$user_meta->app_fqdn; ?>"><i class="fa fa-external-link" aria-hidden="true"></i></a></p>
													<h4>Admin Panel</h4>
													<p><label>URL:</label> <span><?= $user_meta->app_fqdn; ?><?= $user_meta->backend_url; ?> <a target="_blank" href="https://<?= $user_meta->app_fqdn; ?><?= $user_meta->backend_url; ?>"><i class="fa fa-external-link" aria-hidden="true"></i></a></span></p>
													<p><label>Username:</label> <span><?= $user_meta->app_user; ?></span></p>
													<p>
														<label>Password:</label>
														<input type="password" class="form-control" name="password" value="<?= $user_meta->app_password; ?>" readonly="">
														<!-- <a href="#"><i class="fa fa-pencil"></i></a> -->
														<a href="javascript:void(0)" class="toggle_password show"><i class="fa fa-eye"></i></a>
													</p>
													<h4>MYSQL Access</h4>
													<p><label>DB name:</label> <span><?= $user_meta->mysql_db_name; ?></span></p>
													<p><label>Username:</label> <span><?= $user_meta->mysql_user; ?></span></p>
													<p>
														<label>Password:</label> 
														<input type="password" class="form-control" name="password" value="<?= $user_meta->mysql_password; ?>" readonly="">
														<a href="javascript:void(0)" class="toggle_password show"><i class="fa fa-eye"></i></a>
													</p>
												</div>
												<div class="flex-6">
													<h4>Application Credentials</h4>
													<p>You can create and use multiple Application credentials for SFTP or SSH access to this application.</p>
													<p><label>Public IP:</label> <?= $user_meta->public_ip; ?></p>

													<?php $sftp_meta= get_term_meta($term_4id, "sftp_credentials", true); ?>

													<div class="sftp_details">
														<?php if(isset($sftp_meta) && !empty($sftp_meta)){ ?>
															<h3>SFTP Details</h3>
															<?php foreach($sftp_meta as $inner_array){ ?>
																<div class="sftp_inner_steps" id="<?= $inner_array['username'] ?>">		
																	<p><label>User Name</label> <span><?= $inner_array['username'] ?></span></p>
																	<p><label>Password</label> <span><?= $inner_array['user_pwd'] ?></span></p>
																	<a href="javascript:void(0)" class="sftp_editable create_cred" id="<?= $term_4id; ?>" server-id="<?= $user_meta->server_id; ?>" app-id="<?= $user_meta->id; ?>" data-id="<?= $inner_array['cred_id'] ?>"><i class="fa fa-pencil"></i></a>	
																</div>	
																<?php } ?>
														<?php } ?>
													</div>

													<form method="post" id="sftp_credentials" autocomplete="off">
														<p><label>Username</label> <input type="text" name="username" required /></p>
														<p><label>Password</label> <input type="password" name="password" required /></p>

														<input type="hidden" name="server_id" value="<?= $user_meta->server_id; ?>"/>
														<input type="hidden" name="app_id" value="<?= $user_meta->id; ?>"/>
														<input type="hidden" name="cred_id" value=""/>
														<input type="hidden" name="action" value="save_app_credentials"/>
														<input type="hidden" name="type" value="add"/>
														<input type="hidden" name="term_id" value="<?= $term_4id; ?>"/>
														<input id="add_cred-btn" type="submit" value="Add"/>
													</form>
												</div>
											</div>
											<!--<button type="button" class="buttonCustom">Launch Database Manager</button>-->

											<!-- Edit SFTP -->
				                            <div id="update_sftp_credentials" class="modal" style="display: none;">
												<div class="modal-content modal-sm">
													<div class="modal-header">
														<span class="closebtn" onclick="jQuery('#update_sftp_credentials').hide();"><i class="fa fa-close"></i></span>
															<h3 id="page_nm" class="text-white text-center text-bold">APP CREDENTIALS</h3>
													</div>
													<div class="importBox">
														<form method="post" class="bloxx_app" id="bloxx_app_crendentials" autocomplete="off" style="width: 100%;">
															<div>
																<div id="step1">
																	<div class="form-group">
																		<input type="text" placeholder="Enter Username" name="username" required class="form-input">
																	</div>
																	<div class="form-group">
																		<input type="password" placeholder="Enter Password" name="password" required class="form-input" minlength="8" maxlength="60">
																	</div>
																	<input type="hidden" name="server_id" value="">
																	<input type="hidden" name="app_id" value="">
																	<input type="hidden" name="action" value="save_app_credentials">
																	<input type="hidden" name="cred_id" value="">
																	<input type="hidden" name="type" value="add">
																	<input type="hidden" name="term_id" value="<?= $term_4id; ?>"/>
																</div>
																
															</div>
															<button type="submit" class="default-btn" id="add_cred-btn"><i class="fa fa-plus"></i>  Submit</button>
														</form>
														<p id="res-msg"></p>
													</div>
												</div>
											</div>
				                            <!-- End Edit SFTP Modal -->
										</div>
										<div id="domainTab" class="subpanel">
											<div class="rowWrap">
												<div class="flex-6">
													<h4>Primary Domain</h4>
													<form method="post" id="domain_managment">
														<input type="text" name="domain" placeholder="Enter Primary Domain" required/>
														<input type="hidden" name="server_id" value="<?= $user_meta->server_id; ?>"/>
														<input type="hidden" name="app_id" value="<?= $user_meta->id; ?>"/>
														<input type="hidden" name="app_name" value="<?= $user_meta->label; ?>"/>
														<input type="hidden" name="action" value="primary_domain"/>
														<button type="submit" class="buttonCustom">Save Changes</button>
													</form>
													<!--<button type="button" class="buttondefault">Delete</button>-->
												</div>
												
												<div class="flex-6">
													<h4>Additional Domains</h4>
													<?php
														$cloud = new Cloudways();
														$allapps = $cloud->getallapps();
														$allapps = json_decode($allapps);
														$domains = '<ul>';
														if(isset($allapps->servers)){
															foreach($allapps->servers as $key => $app){
																if(isset($app->apps) && count($app->apps) > 0){
																    for($a=0; $a< count($app->apps); $a++){
																		if($app->apps[$a]->id != $user_meta->id){
																			continue;
																		}else{
																			$pkey = getparentkeyindex($user_meta->id,$app->apps);
													    					if(is_null($pkey)){
													    						continue;
													    					}else{
													    						if(!empty($app->apps[$pkey]->aliases)){
													    							for ($i=0; $i < count($app->apps[$pkey]->aliases); $i++) { 
													    								$domains .= '<li><a href="javascript:void(0)" class="db-btn makeprimary_btn">'.$app->apps[$pkey]->aliases[$i].' <span> make primary</span></a></li>';
													    							}
													    						}
													    					}
																		}
																	}
																}
															}
														}
														$domains .= '</ul>';
														echo $domains;
													?>
													<form method="post" id="subdomain_managment">
														<input type="text" name="domain" placeholder="Enter SubDomain" required/>
														<input type="hidden" name="server_id" value="<?= $user_meta->server_id; ?>"/>
														<input type="hidden" name="app_id" value="<?= $user_meta->id; ?>"/>
														<input type="hidden" name="app_name" value="<?= $user_meta->label; ?>"/>
														<input type="hidden" name="action" value="app_domain_save"/>
														<button type="submit" class="buttonCustom">Save Changes</button>
													</form>
												</div>
											</div>
										</div>

										<form method="post" id="ssl_management">
											<div id="sslTab" class="subpanel">
												<p>Cloudways supports Let's Encrypt and custom SSL certificates. You can either create a free SSL certificate (choose the Let's Encryption SSL option) or deploy a Paid SSL certificate for your applications (choose an option from Custom SSL).</p>
												<p>
													<select name="ssl_method">
														<option>Let's Encrypt</option>
														<option>I do not have a certificate</option>
														<option>I already have a certificate</option>
													</select>
												</p>
												<div class="encryptDiv newStyle">
													<p>Install a Let's Encrypt SSL certificate by providing the information below. This will override any existing SSL certificate you may have installed.</p>
													<p><label>Email Address</label>
													<input type="email" name="email" placeholder="email@domain.com" required /></p>
													<p><label>Domain Name</label>
													<input type="text" name="dname" placeholder="www.domain.com" required/></p>

													<p>
														<input type="hidden" name="server_id" value="<?= $user_meta->server_id; ?>"/>
														<input type="hidden" name="app_id" value="<?= $user_meta->id; ?>"/>
														<input type="hidden" name="action" value="installCertificate"/>
														<button type="submit" class="buttonCustom">Install Certificate</button>
													</p>
													<!--<button type="button" class="buttondefault">Add Domain</button>-->
												</div>
											

												<div class="noCert newStyle" style="display: none;">
													<p>Create a Certificate Signing Request (CSR) on the server by providing information about your application. This will override any existing CSR and private key generated earlier.</p>
													<button type="button" class="buttonCustom">Create CSR</button>
												</div>
												<div class="certDiv newStyle" style="display: none;">
													<p>Install a custom SSL certificate by providing your application certificate and the private key.</p>
													<button type="submit" class="buttonCustom">Install SSL</button>
												</div>											
											</div>
										</form>


										<div id="backTab" class="subpanel">
											<p>The section allows you to back up and restore application data (files and database). You can create multiple on-demand backups.</p>

											<div class="rowWrap" style="position: relative;">
												<div class="loader_load" style="display: none; background: #fff; height: 100%; padding: 0 0 0 96%; position: absolute; z-index: 999; opacity: 0.8;">
													<div class="app-loader"></div> 
												</div>


												<div class="flex-6">
													<h4><a href="javascript:void(0)" id="bckandrest" data-id="<?= $user_meta->id; ?>" data-name="<?= $user_meta->server_id; ?>">Restore</a></h4>
													<p>Restore your app(files and database) using one of the available backups.</p>
													<form method="post" id="bckup_restore">
														<select id="backup_restore" name="time">
															<option value="">Select</option>	
														</select>
														<input type="hidden" name="server_id" value="<?= $user_meta->server_id; ?>"/>
														<input type="hidden" name="app_id" value="<?= $user_meta->id; ?>"/>
														<input type="hidden" name="action" value="restore_app"/>
														<button type="submit" class="buttonCustom">Restore Application Now</button>
													</form>
												</div>

												<div class="flex-6">
													<h4>Backup</h4>
													<p>Restore your app(files and database) using one of the available backups.</p>
													<p>Last Backup Date: 10 August, 2021, 21:00:23 UTC</p>
													<form method="post" id="backup_managment">
														<input type="hidden" name="server_id" id="appserver_id" value="<?= $user_meta->server_id; ?>"/>
														<input type="hidden" name="app_id" id="apk_id" value="<?= $user_meta->id; ?>"/>
														<input type="hidden" name="action" value="take_backup"/>
														<button type="submit" class="buttondefault">Take Backup Now</button>
													</form>
												</div>
											</div>
										</div>
										<div id="plansTab" class="subpanel">
											<div class="planSwitch">
												<div class="off">Monthly</div>
												<label class="switch">
													<input id="check" onclick="onOff()" type="checkbox">
													<span class="toggle"></span>
												</label>
												<div class="on">Annually</div>
											</div>
											<div class="rowWrap">
												<div class="flex-4">
													<div class="sitePlans lite">
														<h5>Lite</h5>
														<p>For simple static websites that you can share with a client.</p>
														<p class="priceTag">$8 <small>per month</small>
														</p>
														<button type="button" class="btn-plan liteBG">Add Lite +</button>
														<ul>
															<li>Connect your domain <i class="fa fa-question"></i></li>
															<li>SSL included <i class="fa fa-question"></i></li>
															<li>CDN <i class="fa fa-question"></i></li>
															<li>8GB $30 / month <i class="fa fa-question"></i></li>
															<li>100 static pages <i class="fa fa-question"></i></li>
															<li>25,000 monthly visits <i class="fa fa-question"></i></li>
															<li>100 form submissions <i class="fa fa-question"></i></li>
															<li>No CMS items <i class="fa fa-question"></i></li>
															<li>No CMS API access <i class="fa fa-question"></i></li>
															<li>No site search <i class="fa fa-question"></i></li>
															<li>No content editors <i class="fa fa-question"></i></li>
															<li>No form file upload <i class="fa fa-question"></i></li>
															<li>Email support <i class="fa fa-question"></i></li>
															<li>Free University videos <i class="fa fa-question"></i></li>
															<li>Standard TOS <i class="fa fa-question"></i></li>
															<li>Basic DoS protection <i class="fa fa-question"></i></li>
															<li>Credit card billing <i class="fa fa-question"></i></li>
														</ul>
													</div>
												</div>
												<div class="flex-4">
													<div class="sitePlans standard">
														<span class="rTag">Recommended</span>
														<h5>Standard</h5>
														<p>For simple static websites that you can share with a client.</p>
														<p class="priceTag">$16 <small>per month</small>
														</p>
														<button type="button" class="btn-plan stanBG">Add Standard
															+</button>
														<ul>
															<li>Connect your domain <i class="fa fa-question"></i></li>
															<li>SSL included <i class="fa fa-question"></i></li>
															<li>CDN <i class="fa fa-question"></i></li>
															<li>4GB $50 / month <i class="fa fa-question"></i></li>
															<li>100 static pages <i class="fa fa-question"></i></li>
															<li>25,000 monthly visits <i class="fa fa-question"></i></li>
															<li>100 form submissions <i class="fa fa-question"></i></li>
															<li>No CMS items <i class="fa fa-question"></i></li>
															<li>No CMS API access <i class="fa fa-question"></i></li>
															<li>No site search <i class="fa fa-question"></i></li>
															<li>No content editors <i class="fa fa-question"></i></li>
															<li>No form file upload <i class="fa fa-question"></i></li>
															<li>Email support <i class="fa fa-question"></i></li>
															<li>Free University videos <i class="fa fa-question"></i></li>
															<li>Standard TOS <i class="fa fa-question"></i></li>
															<li>Basic DoS protection <i class="fa fa-question"></i></li>
															<li>Credit card billing <i class="fa fa-question"></i></li>
														</ul>
													</div>
												</div>
												<div class="flex-4">
													<div class="sitePlans elite">
														<h5>Elite</h5>
														<p>For simple static websites that you can share with a client.</p>
														<p class="priceTag">$24 <small>per month</small>
														</p>
														<button type="button" class="btn-plan eliteBG">Add Elite +</button>
														<ul>
															<li>Connect your domain <i class="fa fa-question"></i></li>
															<li>SSL included <i class="fa fa-question"></i></li>
															<li>CDN <i class="fa fa-question"></i></li>
															<li>8GB $90 / month <i class="fa fa-question"></i></li>
															<li>100 static pages <i class="fa fa-question"></i></li>
															<li>25,000 monthly visits <i class="fa fa-question"></i></li>
															<li>100 form submissions <i class="fa fa-question"></i></li>
															<li>No CMS items <i class="fa fa-question"></i></li>
															<li>No CMS API access <i class="fa fa-question"></i></li>
															<li>No site search <i class="fa fa-question"></i></li>
															<li>No content editors <i class="fa fa-question"></i></li>
															<li>No form file upload <i class="fa fa-question"></i></li>
															<li>Email support <i class="fa fa-question"></i></li>
															<li>Free University videos <i class="fa fa-question"></i></li>
															<li>Standard TOS <i class="fa fa-question"></i></li>
															<li>Basic DoS protection <i class="fa fa-question"></i></li>
															<li>Credit card billing <i class="fa fa-question"></i></li>
														</ul>
													</div>
												</div>
											</div>	
										</div>
									</div>
								</div>
								

                            <?php } else {  ?>
                            	
                            	<div class="flex-12 dashboard_no">
                            		<h4>Hosting</h4>
									<div class="box bg-white p-2">
										<p>You didn't hosted with us</p>
									</div>
								</div>
                            <?php } ?>
                        </div>
                        <div id="panelThemes" class="panel">
							<h4>Coming Soon!!</h4>
						</div>
						<div id="panelPlugins" class="panel">
							<h4>Coming Soon!!</h4>
                        </div>
                        <div id="panelManager" class="panel">
                        	<?php if($app_id!=""){ ?>
                        		<h4>File Manager</h4>
                        		<p>
                        			<label style="display: inline-block;vertical-align:middle;">Enable/Disable File Manager:</label> 
	                        		<a href="javascript:void(0)" title="Switch to Divi editor" style="display:inline-block;vertical-align:middle;margin-left:1rem;">
										<label class="switch">
											<input type="checkbox" class="move_filemanager">
											<span class="slider round"></span>
										</label>
									</a>
								</p>

                            <!-- Section CSS -->

                            <style>
								.switch {
									position: relative;
									display: inline-block;
									width: 40px;
									height: 20px;
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
									left: 0px;
									right: 0;
									bottom: 0;
									background-color: #ffffff;
									border: 1px solid #f4ac45;
									-webkit-transition: .4s;
									transition: .4s;
								}

								.slider:before {
									position: absolute;
									content: "";
									height: 14px;
									width: 14px;
									left: 1px;
									bottom: 1px;
									background-color: #f4ac45;
									-webkit-transition: .4s;
									transition: .4s;
									border: 1px solid #fff;
								}

								input:checked + .slider {
									background-color: #f4ac45;
								}

								input:focus + .slider {
									box-shadow: 0 0 1px #2196F3;
								}

								input:checked + .slider:before {
									background: #fff;
									-webkit-transform: translateX(18px);
									-ms-transform: translateX(18px);
									transform: translateX(18px);
								}

								/* Rounded sliders */
								.slider.round {
									border-radius: 17px;
								}

								.slider.round:before {
									border-radius: 100px;
								}
							</style>

                            <!-- jQuery UI (REQUIRED) -->
                            <link rel="stylesheet"
                                href="<?php echo builder_url ?>assets/elFinder/jquery/jquery-ui-1.12.0.css"
                                type="text/css">

                            <!-- elfinder css -->
                            <link rel="stylesheet" href="<?php echo builder_url ?>assets/elFinder/css/commands.css"
                                type="text/css">
                            <link rel="stylesheet" href="<?php echo builder_url ?>assets/elFinder/css/common.css"
                                type="text/css">
                            <link rel="stylesheet" href="<?php echo builder_url ?>assets/elFinder/css/contextmenu.css"
                                type="text/css">
                            <link rel="stylesheet" href="<?php echo builder_url ?>assets/elFinder/css/cwd.css"
                                type="text/css">
                            <link rel="stylesheet" href="<?php echo builder_url ?>assets/elFinder/css/dialog.css"
                                type="text/css">
                            <link rel="stylesheet" href="<?php echo builder_url ?>assets/elFinder/css/fonts.css"
                                type="text/css">
                            <link rel="stylesheet" href="<?php echo builder_url ?>assets/elFinder/css/navbar.css"
                                type="text/css">
                            <link rel="stylesheet" href="<?php echo builder_url ?>assets/elFinder/css/places.css"
                                type="text/css">
                            <link rel="stylesheet" href="<?php echo builder_url ?>assets/elFinder/css/quicklook.css"
                                type="text/css">
                            <link rel="stylesheet" href="<?php echo builder_url ?>assets/elFinder/css/statusbar.css"
                                type="text/css">
                            <link rel="stylesheet" href="<?php echo builder_url ?>assets/elFinder/css/theme.css"
                                type="text/css">
                            <link rel="stylesheet" href="<?php echo builder_url ?>assets/elFinder/css/toast.css"
                                type="text/css">
                            <link rel="stylesheet" href="<?php echo builder_url ?>assets/elFinder/css/toolbar.css"
                                type="text/css">

                            <!-- Section JavaScript -->
                            <!-- jQuery and jQuery UI (REQUIRED) -->
                            <script src="<?php echo builder_url ?>assets/elFinder/jquery/jquery-1.12.4.js"
                                type="text/javascript" charset="utf-8"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/jquery/jquery-ui-1.12.0.js"
                                type="text/javascript" charset="utf-8"></script>

                            <!-- elfinder core -->
                            <script src="<?php echo builder_url ?>assets/elFinder/js/elFinder.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/elFinder.version.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/jquery.elfinder.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/elFinder.mimetypes.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/elFinder.options.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/elFinder.options.netmount.js">
                            </script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/elFinder.history.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/elFinder.command.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/elFinder.resources.js"></script>

                            <!-- elfinder dialog -->
                            <script src="<?php echo builder_url ?>assets/elFinder/js/jquery.dialogelfinder.js"></script>

                            <!-- elfinder default lang -->
                            <script src="<?php echo builder_url ?>assets/elFinder/js/i18n/elfinder.en.js"></script>

                            <!-- elfinder ui -->
                            <script src="<?php echo builder_url ?>assets/elFinder/js/ui/button.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/ui/contextmenu.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/ui/cwd.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/ui/dialog.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/ui/fullscreenbutton.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/ui/navbar.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/ui/navdock.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/ui/overlay.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/ui/panel.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/ui/path.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/ui/places.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/ui/searchbutton.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/ui/sortbutton.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/ui/stat.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/ui/toast.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/ui/toolbar.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/ui/tree.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/ui/uploadButton.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/ui/viewbutton.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/ui/workzone.js"></script>

                            <!-- elfinder commands -->
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/archive.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/back.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/chmod.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/colwidth.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/copy.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/cut.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/download.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/duplicate.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/edit.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/empty.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/extract.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/forward.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/fullscreen.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/getfile.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/help.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/hidden.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/hide.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/home.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/info.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/mkdir.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/mkfile.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/netmount.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/open.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/opendir.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/opennew.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/paste.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/places.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/preference.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/quicklook.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/quicklook.plugins.js">
                            </script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/reload.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/rename.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/resize.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/restore.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/rm.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/search.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/selectall.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/selectinvert.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/selectnone.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/sort.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/undo.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/up.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/upload.js"></script>
                            <script src="<?php echo builder_url ?>assets/elFinder/js/commands/view.js"></script>

                            <!-- elfinder 1.x connector API support (OPTIONAL) -->
                            <script src="<?php echo builder_url ?>assets/elFinder/js/proxy/elFinderSupportVer1.js">
                            </script>

                            <!-- Extra contents editors (OPTIONAL) -->
                            <script src="<?php echo builder_url ?>assets/elFinder/js/extras/editors.default.js">
                            </script>

                            <!-- GoogleDocs Quicklook plugin for GoogleDrive Volume (OPTIONAL) -->
                            <script src="<?php echo builder_url ?>assets/elFinder/js/extras/quicklook.googledocs.js">
                            </script>

                            <!-- elfinder initialization  -->
                            <script>
                            $(function() {
                                $('#elfinder').elfinder({
                                        // Disable CSS auto loading
                                        cssAutoLoad: false,

                                        // Base URL to css/*, js*
                                        baseUrl: './',

                                        // Connector URL
                                        url: "<?php echo admin_url('admin-ajax.php'); ?>/?action=elconnector&term_id=<?php echo $term_4id; ?>",

                                        // Callback when a file is double-clicked
                                        getFileCallback: function(file) {
                                            // ...
                                        },
                                    },

                                    // 2nd Arg - before boot up function
                                    function(fm, extraObj) {
                                        // `init` event callback function
                                        fm.bind('init', function() {
                                            // Optional for Japanese decoder "extras/encoding-japanese.min"
                                            delete fm.options.rawStringDecoder;
                                            if (fm.lang === 'ja') {
                                                fm.loadScript(
                                                    [fm.baseUrl +
                                                        '<?php echo builder_url ?>assets/elFinder/js/extras/encoding-japanese.min.js'
                                                    ],
                                                    function() {
                                                        if (window.Encoding && Encoding
                                                            .convert) {
                                                            fm.options.rawStringDecoder =
                                                                function(s) {
                                                                    return Encoding.convert(s, {
                                                                        to: 'UNICODE',
                                                                        type: 'string'
                                                                    });
                                                                };
                                                        }
                                                    }, {
                                                        loadType: 'tag'
                                                    }
                                                );
                                            }
                                        });

                                        // Optional for set document.title dynamically.
                                        var title = document.title;
                                        fm.bind('open', function() {
                                            var path = '',
                                                cwd = fm.cwd();
                                            if (cwd) {
                                                path = fm.path(cwd.hash) || null;
                                            }
                                            document.title = path ? path + ':' + title : title;
                                        }).bind('destroy', function() {
                                            document.title = title;
                                        });
                                    }
                                );
                            });
                            </script>

                            <div id="elfinder" style="display:none;"></div>
                        	<?php } else { ?>
                        		<div class="flex-12 dashboard_no">
                            		<h4>File Manager</h4>                            		
									<div class="box bg-white p-2">
										<p>You didn't hosted with us</p>
									</div>
								</div>
                        	<?php } ?>
						</div>
						<div id="panelPages" class="panel">
							<h4>Coming Soon!!</h4>
                        </div>
						<div id="panelInte" class="panel">
							<h4>Coming Soon!!</h4>
						</div>

						<div id="panelChat" class="panel">
							<div class="empty_colabrate">
								<label class="error">Sorry, No collaborate found for this project</label>
								<a href="javascript:void(0)" type="button" class="default-btn bg_teal collaborate_add">Add Collaborate</a>
								<form class="teamForm" id="collab_invitation" method="post">
									<input type="text" name="collab_email" placeholder="Email Address..." required />
									<button type="submit" class="default-btn" id="update_info">Submit</button>
									<input type="hidden" name="userid" value="<?= $current_user_id; ?>">
									<input type="hidden" name="action" value="collab_invitation">
								</form>
							</div>
						</div>

						<div id="panelTasks" class="panel">
							<div class="empty_colabrate">
								<label class="error">Sorry, No collaborate found for this project</label>
								<a href="javascript:void(0)" type="button" class="default-btn bg_teal collaborate_add">Add Tasks</a>
								<form class="teamForm" id="collab_invitation" method="post">
									<input type="text" name="collab_email" placeholder="Email Address..." required />
									<button type="submit" class="default-btn" id="update_info">Submit</button>
									<input type="hidden" name="userid" value="<?= $current_user_id; ?>">
									<input type="hidden" name="action" value="collab_invitation">
								</form>
							</div>
						</div>

						<div id="panelActivity" class="panel">
							<div class="empty_colabrate">
								<label class="error">Sorry, No collaborate found for this project</label>
								<a href="javascript:void(0)" type="button" class="default-btn bg_teal collaborate_add">Add Activity</a>
								<form class="teamForm" id="collab_invitation" method="post">
									<input type="text" name="collab_email" placeholder="Email Address..." required />
									<button type="submit" class="default-btn" id="update_info">Submit</button>
									<input type="hidden" name="userid" value="<?= $current_user_id; ?>">
									<input type="hidden" name="action" value="collab_invitation">
								</form>
							</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once 'builder_mobile_nav.php'; ?>
</div>
<?php
	$output .= ob_get_contents();
	ob_end_clean();
	return $output;
	?>
<?php } else {
	$this->restricate_page_content();
} ?>

<?php 
}

public function connected_status($term_id){
	global $wpdb;
	$current_user = wp_get_current_user();
	$user_id= $current_user->ID;
	$conn_site = $wpdb->prefix.'connected_sites';
	$project_query="SELECT * FROM $conn_site where siteblox_user_id='$user_id' and `is_connect`='1' and siteblox_termid='$term_id' order by id desc limit 1";
	$connected_sites = $wpdb->get_results($project_query);
	$count_connected= count($connected_sites);
	if($count_connected==1){
		$con_details = $wpdb->get_row($project_query);
		$get_data=array(
			'website_url'=> $con_details->site_url,
			'server_userid' => $con_details->user_id,
			'count_data' => $count_connected
		);
	} else {
		$get_data=array(
			'website_url'=> '',
			'count_data' => $count_connected
		);
	}		
	return $get_data;
}

public function restricate_page_content(){

	?>
<div class="contentWrapper">
    <div class="wrapContent">
        <div class="topWrapmenu">
            <ul class="builder_back_dashboard">
                <li>
                    <a href="<?php echo site_url(); ?>/login"> Login</a>
                </li>
            </ul>
        </div>

        <div class="tabWrapcontent builder_create_template">
            <p class="um-notice warning">You must <a href="<?php echo site_url(); ?>/login">login</a> to access this
                page</p>
        </div>
    </div>
</div>
<?php
}


public function restricate_page_without_cats(){

	?>
<div class="contentWrapper">
    <div class="wrapContent">
        <div class="topWrapmenu">
            <ul class="builder_back_dashboard">
                <li>
                    <a href="<?php echo site_url(); ?>/my-projects">Back to Projects</a>
                </li>
            </ul>
        </div>

        <div class=" builder_create_template">
            <p class="um-notice warning">You must click at least one category on project page</p>
        </div>
    </div>
</div>
<?php
	die();
}
}

$builder_project = new Builder_project();
