<?php

class Builder_library{
	
	public function __construct(){
		add_shortcode('builder_library', array($this, 'builder_library'));
		
		add_action("wp_ajax_builder_blox_saved", array($this, "builder_blox_saved"));
		add_action("wp_ajax_nopriv_builder_blox_saved", array($this, "builder_blox_saved"));

		add_action("wp_ajax_builder_create_section", array($this, "builder_create_section"));
		add_action("wp_ajax_nopriv_builder_create_section", array($this, "builder_create_section"));

		add_action("wp_ajax_builder_create_section_preview", array($this, "builder_create_section_preview"));
		add_action("wp_ajax_nopriv_builder_create_section_preview", array($this, "builder_create_section_preview"));

		add_action("wp_ajax_fetch_categories", array($this, "fetch_categories_function"));
		add_action("wp_ajax_nopriv_fetch_categories", array($this, "fetch_categories_function"));

		// Delete Section
		add_action("wp_ajax_builder_custom_section", array($this, "builder_custom_section"));
		add_action("wp_ajax_nopriv_builder_custom_section", array($this, "builder_custom_section"));


		// Duplicate Section
		add_action("wp_ajax_duplicate_section_content", array($this, "duplicate_section_content"));
		add_action("wp_ajax_nopriv_duplicate_section_content", array($this, "duplicate_section_content"));

		// Regenerate Image Thumbnail
		add_action("wp_ajax_regenerate_section_image", array($this, "regenerate_section_image"));
		add_action("wp_ajax_nopriv_regenerate_section_image", array($this, "regenerate_section_image"));

		//Rename Section
		add_action("wp_ajax_section_rename_ajax", array($this, "section_rename_ajax"));
		add_action("wp_ajax_nopriv_section_rename_ajax", array($this, "section_rename_ajax"));
		
	}


	public function section_rename_ajax(){
		extract($_REQUEST);
		$my_post = array(
			'ID'           => $rn_id,
			'post_title' => $rn_nm,
		);
		wp_update_post( $my_post );
		$result=array(
			'code' => 200,
			'message' => "$rn_nm name updated successfully"
		);
		

		echo json_encode($result);
		die();
	}


	// Duplicate Section
	public function duplicate_section_content(){
		extract($_REQUEST);
		if($dpl_id!="" && $dpl_catid!=""){
			$post_id = $dpl_id;	
			$cat_id = $dpl_catid;

			$project = get_post( $post_id );
			$thumbnail_id= get_post_thumbnail_id($post_id);
			$new_post = array(
				'post_title' => $project->post_title." copy",
				'post_content' => $project->post_content,
				'post_status' => 'publish',
				'post_name' => 'pending',
				'post_type' => 'customer_templates'
			);
			$pid = wp_insert_post($new_post);
			wp_set_object_terms( $pid, intval($cat_id), 'project_categories' );

			$current_user = wp_get_current_user();
			$current_user_id= $current_user->ID;
			update_post_meta( $pid, 'template_user', $current_user_id );
			update_post_meta( $pid, 'template_library_save', $current_user_id );
			update_post_meta( $pid, '_thumbnail_id', $thumbnail_id );			
			$result=array(
				'code' => 200,					
				'message' => 'Page copied Successfully'
			);
		} else {
			$result=array(
				'code' => 202,					
				'message' => 'Need valid parameters required'
			);
		}
		echo json_encode($result);
		die();

	}



	// Regenerate Image Thumbnail
	
	public function regenerate_section_image(){
		extract($_REQUEST);
		if($rgn_id != ""){
			$permalink=get_the_permalink($rgn_id);
			$shot_nm="project_$rgn_id.png";
			$version=$permalink."?id=".time();
			$pepe= $this->take_screenshot($version, $shot_nm);

			if($pepe=="screenshot_captured"){
				//Set Feature image
				$feature_nm=$shot_nm;
				$feature_image= siteblox_path."/project_shots/".$feature_nm;

				$siteblox_core = new Siteblox_core();
				$attachment_id = $siteblox_core->upload_feature_image($feature_image, $feature_nm);
				set_post_thumbnail( $rgn_id, $attachment_id );
			}
			
			$result=array(
				'code' => 200,					
				'message' => 'Thumbnail re-generated successfully'
			);
		} else {
			$result=array(
				'code' => 202,					
				'message' => 'Need valid parameters required'
			);
		}
		echo json_encode($result);
		die();

	}


	// delete custom section

	public function builder_custom_section(){
		extract($_REQUEST);
		
		$project_catid = $section_id;
		//$cid = wp_delete_post($project_catid);
		$cid = wp_trash_post($project_catid);
		if ( is_wp_error($cid) ) {
			$error_message= $cid->get_error_message();
			$result=array(
				'code' => 202,
				'message' => $error_message
			);
		} else {
			$result=array(
				'code' => 200,
				'message' => "$del_name deleted successfully"
			);
		}
		echo json_encode($result);
		die();
	}


	public function builder_create_section_preview(){
		global $wpdb;
		for ($i=0; $i < count($_FILES['files']['name']); $i++) { 
			$builder_own_section_file = file_get_contents($_FILES['files']['tmp_name'][$i]);
			$section_json_decode=json_decode($builder_own_section_file, true);

			if(isset($section_json_decode['data'])){
				$section_content = ''; //echo "<pre>";print_r($section_json_decode);die;
				foreach ($section_json_decode['data'] as $key => $data_every_array) {
					//$data_every_array['post_content'];
					if(isset($data_every_array['post_content'])){
						$section_content .= $data_every_array['post_content'];
					} else {
						$section_content .= $data_every_array;
					}
				}
			}
			
			$section_preview_tb = $wpdb->prefix . 'section_preview';
			$wpdb->insert($section_preview_tb,array("post_content" => $section_content));
			$rowss = $wpdb->insert_id;
		}
		echo $rowss;
		exit;
	}

	public function fetch_categories_function(){

		$builder_terms = get_terms( array(
			'taxonomy' => 'project_category',
			'hide_empty' => false,
		));

		$html = '<select name="section_category">';

		foreach ($builder_terms as $builder_cats) {

			$html.= '<option value="'.$builder_cats->term_id.'">'.$builder_cats->name.'</option>';
		}

		$html.= '</select>';

		echo $html;

	}

	public function builder_create_section(){
		extract($_REQUEST);
		$user = wp_get_current_user();
		$user_roles = $user->roles;
		$current_user= $user->ID;
		$current_plan = get_user_meta($current_user,'current_plan', true); // plan id 
		$bloxx_library = get_user_meta($current_user,'assets_limit', true);

		// 
		global $wpdb, $wp_query;
		$curauth = $wp_query->get_queried_object();
		$current_user_post_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_author = '" . $current_user . "' AND post_type = 'project' AND post_status = 'publish'");

		



		if($current_user_post_count <= $bloxx_library || in_array( 'administrator', $user_roles, true )) {
			//echo 'can publish library';
		
			for ($i=0; $i < count($_POST['title']); $i++) {
				$builder_import_title= strip_tags(htmlspecialchars($_POST['title'][$i]));	
				$builder_own_section_file = file_get_contents($_FILES['files']['tmp_name'][$i]);
				$project_category = strip_tags(htmlspecialchars($_POST['project_category'][$i]));

				if($import_data=="page"){
					$prj_ind_category = strip_tags(htmlspecialchars($ind_category[$i]));
				}

				$section_json_decode=json_decode($builder_own_section_file, true);

				if(isset($section_json_decode['data'])){
					$section_content = '';
					

					foreach ($section_json_decode['data'] as $key => $data_every_array) {
							if(isset($data_every_array['post_content'])){
								$section_content .= $data_every_array['post_content'];
							} else {
								$section_content .= $data_every_array;
							}
					}

					
					if($import_data=="page"){
						$post_type="layouts";
						$post_cats="bloxx_categories";
					} else {
						$post_type="project";
						$post_cats="project_category";
					}

					
		       		//Create New Post for Wordpress admin section custom post
					$new_post = array(
						'post_title' => $builder_import_title,
						'post_content' => $section_content,
						'post_status' => 'publish',
						'post_type' => $post_type,
						//'post_category' => $category
					);
					$pid = wp_insert_post($new_post);
					// set category
					wp_set_post_terms( $pid, $project_category, $post_cats);


					if($import_data=="page"){
						wp_set_post_terms( $pid, $prj_ind_category, "service_type");
					}


					//Update Post Meta
					update_post_meta($pid, 'builder_custom_cat_user', $current_user);
					update_post_meta($pid,'_et_pb_post_hide_nav','default');
					update_post_meta($pid,'_et_pb_project_nav','off');
					update_post_meta($pid,'_et_pb_page_layout','et_no_sidebar');
					update_post_meta($pid,'_et_pb_side_nav','off');
					update_post_meta($pid,'_et_pb_use_builder','on');

					update_post_meta($pid,'premium_section',0);



					//Capture Image
					$link_url= get_the_permalink($pid);

					$shot_nm="project_$pid.png";

					$version=$link_url."?id=".time();

					$pepe= $this->take_screenshot($version, $shot_nm);

					// echo "<pre>";
					// print_r($pepe);

					if($pepe=="screenshot_captured"){
						//Set Feature image
						$feature_nm=$shot_nm;
						$feature_image= siteblox_path."/project_shots/".$feature_nm;

						$siteblox_core = new Siteblox_core();
						$attachment_id = $siteblox_core->upload_feature_image($feature_image, $feature_nm);
						set_post_thumbnail( $pid, $attachment_id );
					}
					//die();
					

				} 
			}

			$result=array(
				'code' => 200,
				'message' => 'Section added successfully'
			);

		} else {
			// 'publish library limit exceeded';
			$result=array(
				'code' => 201,
				'message' => 'Library Limit Exceeded! Please upgrade your plan.'
			);
		}		
		
				

		echo json_encode($result);
		die();
	}



	public function take_screenshot($version, $shot_nm){
		$scriptpath= "node " .siteblox_path."/script.js {$version} {$shot_nm}";
		exec($scriptpath, $output);
		$myJSON = $output;
		$pepe = implode($myJSON);
		return $pepe;
	}


	public function builder_blox_saved() {
		if(isset($_REQUEST['project_id'])){
			extract($_REQUEST);
			$current_user = wp_get_current_user();
			$user_id= $current_user->ID;
			update_post_meta($project_id, 'template_library_save', $user_id);
			$result=array(
				'code' => 200,
				'message' => 'Project added into library'
			);
		} else {
			$result=array(
				'code' => 202,
				'message' => 'Please save before this action'
			);
		}
		echo json_encode($result);
		die();
	}

	
	public function builder_library(){
		if(is_user_logged_in()){						
			ob_start();			
			?>
			<?php if(isset($_REQUEST['tabs'])) { ?>
				<?php if($_REQUEST['tabs']!="section"){ ?>
					<script>
						jQuery(function($){
							$(".tab_section").hide();
							setTimeout(function(){
								//console.log(".viewList .mytab_<?= $_REQUEST['tabs'] ?>");
								$(".viewList .mytab_<?= $_REQUEST['tabs'] ?>").trigger("click");
							}, 200);
						});
					</script>
				<?php } ?>
			<?php } ?>


			<div class="contentWrapper user_actions" id="table-page">
				<!-- //sidebar  --> 
				<?php require_once 'builder_siderbar.php'; ?>

				<div class="wrapContent">
						<!-- //Top Bar  --> 
            		<?php require_once 'builder_topnav.php'; ?> 
				   	<div class="wrapContainer user_actions">
						<style>
							.dropbtn {
							  background-color: #3498DB;
							  color: white;
							  padding: 16px;
							  font-size: 16px;
							  border: none;
							  cursor: pointer;
							}

							.dropbtn:hover, .dropbtn:focus {
							  background-color: #2980B9;
							}

							.dropdown {
							  position: relative;
							  display: inline-block;
							}

							.dropdown-content {
							  display: none;
							  position: absolute;
							  background-color: #f1f1f1;
							  min-width: 160px;
							  overflow: auto;
							  box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
							  z-index: 1;
							  right: -126px;
							}

							.dropdown-content a {
							  color: black;
							  padding: 12px 16px;
							  text-decoration: none;
							  display: block;
							  font-size: 14px;
							}

							.dropdown a:hover {background-color: #ddd;}

							.show {display: block;}
							</style>


							<div class="sectionTitle filter-options">
			         			<h3 class="libTitle dropdown">
			         				<img src="<?php echo plugins_url(); ?>/divi-builder/images/section-icon.png" alt="..." /> Divikitz 
			         				<span class="plusSign">
			         					<a href="javascript:void(0)" class="dropbtn"><i class="fas fa-plus"></i></a>
			         				</span>




				         			<div id="myDropdown" class="dropdown-content" style="display:none;">
				         				<a href="javascript:void(0)" class="lib_plusSign" id="section">Add Section</a>
								    	<a href="javascript:void(0)" class="lib_plusSign" id="page">Add Page</a>
								    	<a href="javascript:void(0)" class="lib_plusSign" id="header">Add Header</a>
								    	<a href="javascript:void(0)" class="lib_plusSign" id="footer">Add Footer</a>
							      	</div>
			         			</h3>

			         			<div class="viewList list_type">
						      		<ul>
						      			<li><a href="javascript:void(0)" class="select_tab mytab_section <?php if(!isset($_REQUEST['tabs'])) { echo 'active'; } ?>" id="section">Sections</a></li>
						      			<li><a href="javascript:void(0)" class="select_tab mytab_page" id="page">Pages</a></li>
						      			<li><a href="javascript:void(0)" class="select_tab mytab_header" id="header">Header</a></li>
						      			<li><a href="javascript:void(0)" class="select_tab mytab_footer" id="footer">Footer</a></li>
						      		</ul>
						      	</div>
					      	</div>


					      	<!-- Start Section Table Html -->
					      	<div class="tab_section" id="mytab_section" style="display:block;">
						      	<?php
								$user = wp_get_current_user();
								$paged = get_query_var('paged') ? get_query_var('paged') : 1;
								$current_user= $user->ID; 
								$args= array(
									'post_type'=>'project',
									'order'=>'DESC',
									'posts_per_page' => 15,
									'paged' => $paged,
									'meta_query'    => array(
										'relation' => 'AND',
										array(
											'key'       => 'builder_custom_cat_user',
											'value'     => $current_user,
											'compare'   => 'LIKE',
										)
									),
									'tax_query' => array(
								        array (
								            'taxonomy' => 'project_category',
								            'field' => 'term_id',
								            'terms' => array(176, 502, 1062, 1063),
								            'operator' => 'NOT IN'
								        )
								    )
								);
								$query = new WP_Query($args);
								?>
								
								<?php if ( $query->have_posts() ) { ?>
									<?php $j=1; ?>

							      	<div class="sectionTitle filter-options">
					         			<!-- <h3 class="libTitle dropdown">Sections</h3> -->
					         			<h3 class="libTitle dropdown"></h3>
					         			<div class="viewList list_type">
								      		<ul class="viewList">
									            <!-- <li><a href="javascript:void(0)"><i class="fas fa-sort-amount-down" aria-hidden="true"></i></a></li> -->
									            <li>
									               <div class="filter-buttons">
									                  <div class="grid-view-button"><i class="fas fa-th-large"></i></div>
									                  <div class="list-view-button active"><i class="fas fa-bars"></i></div>
									               </div>
									            </li>
								         	</ul>
								      	</div>
							      	</div>

							      	<div class="list list-view-filter" id="tab_section">
							         	<ul class="listLi">
							         		<li class="backColor">
								               <span>&nbsp;</span>
								               <span class="width_1">Preview</span>
								               <span class="width_2">Title <img src="<?php echo plugins_url(); ?>/divi-builder/images/sort-arrow.png" alt="..." /></span>
								               <span class="width_3">Category <img src="<?php echo plugins_url(); ?>/divi-builder/images/sort-arrow.png" alt="..." /></span>
								               <span class="width_4">Created <img src="<?php echo plugins_url(); ?>/divi-builder/images/sort-arrow.png" alt="..." /></span>
								               <span class="lastChild">&nbsp;</span>
								            </li>
											<?php while ( $query->have_posts() ) { ?>									
												<?php 
												$query->the_post();
												$postid=get_the_id();
												$post_nm= get_the_title();
												$post_create_date = get_the_date('M d, Y', $postid);
												$builder_json_link=get_post_meta($postid, 'builder_json', true);
												$feat_image = wp_get_attachment_url( get_post_thumbnail_id($postid) );
												$sec_cat= get_the_terms($postid, 'project_category');

												if(isset($sec_cat) && !empty($sec_cat)){
													$cat_nm=$sec_cat[0]->name;
													$cat_id=$sec_cat[0]->term_id;
												} else {
													$cat_nm="-N/A-";
												}
											?>
								            	<li>
								            		<span></span>
													<!-- <span><input type="checkbox" name="select"></span> -->
													<span class="width_1"><img src="<?= $feat_image; ?>" alt="<?= $post_nm; ?>" /></span>
													<span class="width_2"><strong><?= $post_nm; ?></strong></span>
													<span class="width_3"><?= $cat_nm; ?></span>
													<span class="width_4"><?= $post_create_date ?></span>
													<span class="lastChild">
														<button class="buttonView"><i class="fas fa-ellipsis-v"></i></button>
														<ul class="dropdownList">
															<li><a href="<?php echo get_the_permalink(); ?>"><i class="far fa-eye"></i> Preview</a></li>

															<div class="dividerLine"></div>
															<!--<li><a href="javascript:void(0)"><i class="fas fa-share-alt"></i> Share</a></li>-->

															<li class="duplicate_section" id="<?= $cat_id; ?>">
																<a href="javascript:void(0)" title="Duplicate" data-id="<?= $postid; ?>" data-name="<?= $post_nm; ?>" data-title="You want to duplicate <?= $post_nm; ?> section"><i class="far fa-copy"></i> Duplicate</a>
															</li>

															<li class="regenrate_section" id="<?= $cat_id; ?>">
																<a href="javascript:void(0)" title="Regenrate Thumbnail" data-id="<?= $postid; ?>" data-name="<?= $post_nm; ?>" data-title="You want to regenerate <?= $post_nm; ?> image"><i class="fas fa-repeat"></i> Regenerate Thumbnail</a>
															</li>

															<div class="dividerLine"></div>

															<li class="section_rename_btn"><a href="javascript:void(0)" title="Rename" id="rename_section" data-id="<?= $postid; ?>" data-name="<?= $post_nm; ?>" data-title="You want to rename <?= $post_nm; ?> section name"><i class="fas fa-text-width"></i> Rename</a></li>

															<li><a href="javascript:void(0)" class="del_saction" id="<?php echo $postid; ?>" data-id="<?= site_url('my-library/?tabs=section'); ?>" data_title="Do you want to remove the <?= $post_nm; ?> section?" data_nm="<?= $post_nm; ?> section"><i class="far fa-trash-alt"></i> Move to Trash</a></li>
														</ul>
													</span>
												</li>
						            		<?php $j++; ?>
										<?php } ?>
										</ul>
										<div class="page_navi header_pagination">
											<?php wp_pagenavi( array( 'query' => $query ) ); ?>
										</div>
									</div>

								<?php } else { ?>
			            			<div class="no-data-found">
			            				<div class="accordion-content">
								            <div class="rowWrap starred_prj">
								              	<div class="flex-12 dashboard_no">
								                   	<div class="uploadData bg-white p-2 plusSign">
														<img src="<?php echo plugins_url(); ?>/divi-builder/images/cross-icon.png" alt="..." />
														<p>You do not currently have any sections saved in your library.<br/> Upload a new section template to get started</p>
														<a href="javascript:void(0)" class="project-btn">Upload <i class="fas fa-upload"></i></a>
													</div>													   
								                </div>
							                  										                  
								            </div>
								        </div>
			            			</div>
				           		<?php } ?>
								<?php wp_reset_postdata(); ?>	
							</div>
							<!-- End Section Table Html -->



							<!-- Page Table Html -->
							<div class="tab_section" id="mytab_page" style="display:none;">
						      	<?php
								$user = wp_get_current_user();
								$current_user= $user->ID; 
								$paged = get_query_var('paged') ? get_query_var('paged') : 1;
								$args= array(
									'post_type'=>'layouts',
									'order'=>'DESC',
									'paged' => $paged,
									'posts_per_page' => 15,
									'meta_query'    => array(
										'relation' => 'AND',
										array(
											'key'       => 'builder_custom_cat_user',
											'value'     => $current_user,
											'compare'   => 'LIKE',
										)
									)
								);
								$query = new WP_Query($args);
								?>
								
								<?php if ( $query->have_posts() ) { ?>
									<?php $j=1; ?>

							      	<div class="sectionTitle filter-options">
					         			<!-- <h3 class="libTitle dropdown">Page</h3> -->
					         			<h3 class="libTitle dropdown"></h3>
					         			<div class="viewList list_type">
								      		<ul class="viewList">
									            <!-- <li><a href="javascript:;"><i class="fas fa-sort-amount-down" aria-hidden="true"></i></a></li>
									            <li> -->
									               <div class="filter-buttons">
									                  <div class="grid-view-button"><i class="fas fa-th-large"></i></div>
									                  <div class="list-view-button active"><i class="fas fa-bars"></i></div>
									               </div>
									            </li>
								         	</ul>
								      	</div>
							      	</div>

							      	<div class="list list-view-filter" id="tab_section">
							         	<ul class="listLi">
							         		<li class="backColor">
								               <span>&nbsp;</span>
								               <span class="width_1">Preview</span>
								               <span class="width_2">Title <img src="<?php echo plugins_url(); ?>/divi-builder/images/sort-arrow.png" alt="..." /></span>
								               <span class="width_3">Industry <img src="<?php echo plugins_url(); ?>/divi-builder/images/sort-arrow.png" alt="..." /></span>
								               <span class="width_3">Category <img src="<?php echo plugins_url(); ?>/divi-builder/images/sort-arrow.png" alt="..." /></span>
								               <span class="width_4">Created <img src="<?php echo plugins_url(); ?>/divi-builder/images/sort-arrow.png" alt="..." /></span>
								               <span class="lastChild">&nbsp;</span>
								            </li>
											<?php while ( $query->have_posts() ) { ?>									
												<?php 
												$query->the_post();
												$postid=get_the_id();
												$post_nm= get_the_title();
												$post_create_date = get_the_date('M d, Y', $postid);
												$builder_json_link=get_post_meta($postid, 'builder_json', true);
												$feat_image = wp_get_attachment_url( get_post_thumbnail_id($postid) );
												$sec_cat= get_the_terms($postid, 'bloxx_categories');

												$ind_cat= get_the_terms($postid, 'service_type');

												if(isset($sec_cat) && !empty($sec_cat)){
													$cat_nm=$sec_cat[0]->name;
													$cat_id=$sec_cat[0]->term_id;
												} else {
													$cat_nm="-N/A-";
												}

												if(isset($ind_cat) && !empty($ind_cat)){
													$catind_nm=$ind_cat[0]->name;
													$catind_id=$ind_cat[0]->term_id;
												} else {
													$cat_nm="-N/A-";
												}
												?>
								            	<li>
								            		<span></span>
													<!-- <span><input type="checkbox" name="select"></span> -->
													<span class="width_1"><img src="<?= $feat_image; ?>" alt="<?= $post_nm; ?>" /></span>
													<span class="width_2"><strong><?= $post_nm; ?></strong></span>
													<span class="width_3"><?= $catind_nm; ?></span>
													<span class="width_3"><?= $cat_nm; ?></span>
													<span class="width_4"><?= $post_create_date ?></span>
													<span class="lastChild">
														<button class="buttonView"><i class="fas fa-ellipsis-v"></i></button>
														<ul class="dropdownList">
															<li><a href="<?php echo get_the_permalink(); ?>"><i class="far fa-eye"></i> Preview</a></li>

															<div class="dividerLine"></div>
															<!--<li><a href="javascript:void(0)"><i class="fas fa-share-alt"></i> Share</a></li>-->

															<li class="duplicate_section" id="<?= $cat_id; ?>">
																<a href="javascript:void(0)" title="Duplicate" data-id="<?= $postid; ?>" data-name="<?= $post_nm; ?>" data-title="You want to duplicate <?= $post_nm; ?> page"><i class="far fa-copy"></i> Duplicate</a>
															</li>

															<li class="regenrate_section" id="<?= $cat_id; ?>">
																<a href="javascript:void(0)" title="Regenrate Thumbnail" data-id="<?= $postid; ?>" data-name="<?= $post_nm; ?>" data-title="You want to regenerate <?= $post_nm; ?> image"><i class="fas fa-repeat"></i> Regenerate Thumbnail</a>
															</li>

															<div class="dividerLine"></div>

															<li class="section_rename_btn"><a href="javascript:void(0)" title="Rename" id="rename_section" data-id="<?= $postid; ?>" data-name="<?= $post_nm; ?>" data-title="You want to rename <?= $post_nm; ?> page name"><i class="fas fa-text-width"></i> Rename</a></li>

															<li><a href="javascript:void(0)" class="del_saction" id="<?php echo $postid; ?>" data-id="<?= site_url('my-library/?tabs=page'); ?>" data_title="Do you want to remove the <?= $post_nm; ?> page?" data_nm="<?= $post_nm; ?> page"><i class="far fa-trash-alt"></i> Move to Trash</a></li>
														</ul>
													</span>
												</li>
						            		<?php $j++; ?>
										<?php } ?>
										</ul>
										
										<div class="page_navi header_pagination">
											<?php wp_pagenavi( array( 'query' => $query ) ); ?>
										</div>
									</div>

								<?php } else { ?>
			            			<div class="no-data-found">
			            				<div class="accordion-content">
								            <div class="rowWrap starred_prj">
								              	<div class="flex-12 dashboard_no">
								                   	<div class="uploadData bg-white p-2 plusSign">
														<img src="<?php echo plugins_url(); ?>/divi-builder/images/cross-icon.png" alt="..." />
														<p>You do not currently have any page saved in your library.<br/> Upload a new page template to get started</p>
														<a href="javascript:void(0)" class="project-btn">Upload <i class="fas fa-upload"></i></a>
													</div>													   
								                </div>
							                  										                  
								            </div>
								        </div>
			            			</div>
				           		<?php } ?>
								<?php wp_reset_postdata(); ?>	
							</div>

							<!-- End Page Table Html -->




							<!-- Start Header Table Html -->
					      	<div class="tab_section" id="mytab_header" style="display:none;">
						      	<?php
								$user = wp_get_current_user();
								$current_user= $user->ID;
								$paged = get_query_var('paged') ? get_query_var('paged') : 1;
								$args= array(
									'post_type'=>'project',
									'order'=>'DESC',
									'paged' => $paged,
									'posts_per_page' => 15,
									'meta_query'    => array(
										'relation' => 'AND',
										array(
											'key'       => 'builder_custom_cat_user',
											'value'     => $current_user,
											'compare'   => 'LIKE',
										)
									),
									'tax_query' => array(
								        array (
								            'taxonomy' => 'project_category',
								            'field' => 'term_id',
								            'terms' => '176',
								        )
								    )
								);
								$query = new WP_Query($args);
								?>
								
								<?php if ( $query->have_posts() ) { ?>
									<?php $j=1; ?>

							      	<div class="sectionTitle filter-options">
					         			<!-- <h3 class="libTitle dropdown">Header</h3> -->
					         			<h3 class="libTitle dropdown"></h3>
					         			<div class="viewList list_type">
								      		<ul class="viewList">
									            <!-- <li><a href="javascript:;"><i class="fas fa-sort-amount-down" aria-hidden="true"></i></a></li> -->
									            <li>
									               <div class="filter-buttons">
									                  <div class="grid-view-button"><i class="fas fa-th-large"></i></div>
									                  <div class="list-view-button active"><i class="fas fa-bars"></i></div>
									               </div>
									            </li>
								         	</ul>
								      	</div>
							      	</div>

							      	<div class="list list-view-filter" id="tab_section">
							         	<ul class="listLi">
							         		<li class="backColor">
								               <span>&nbsp;</span>
								               <span class="width_1">Preview</span>
								               <span class="width_2">Title <img src="<?php echo plugins_url(); ?>/divi-builder/images/sort-arrow.png" alt="..." /></span>
								               <span class="width_4">Created <img src="<?php echo plugins_url(); ?>/divi-builder/images/sort-arrow.png" alt="..." /></span>
								               <span class="lastChild">&nbsp;</span>
								            </li>
											<?php while ( $query->have_posts() ) { ?>									
												<?php 
												$query->the_post();
												$postid=get_the_id();
												$post_nm= get_the_title();
												$post_create_date = get_the_date('M d, Y', $postid);
												$builder_json_link=get_post_meta($postid, 'builder_json', true);
												$feat_image = wp_get_attachment_url( get_post_thumbnail_id($postid) );
												
											?>
								            	<li>
								            		<span></span>
													<!-- <span><input type="checkbox" name="select"></span> -->
													<span class="width_1"><img src="<?= $feat_image; ?>" alt="<?= $post_nm; ?>" /></span>
													<span class="width_2"><strong><?= $post_nm; ?></strong></span>
													<span class="width_4"><?= $post_create_date ?></span>
													<span class="lastChild">
														<button class="buttonView"><i class="fas fa-ellipsis-v"></i></button>
														<ul class="dropdownList">
															<li><a href="<?php echo get_the_permalink(); ?>"><i class="far fa-eye"></i> Preview</a></li>

															<div class="dividerLine"></div>
															<!--<li><a href="javascript:void(0)"><i class="fas fa-share-alt"></i> Share</a></li>-->

															<li class="duplicate_section" id="<?= $cat_id; ?>">
																<a href="javascript:void(0)" title="Duplicate" data-id="<?= $postid; ?>" data-name="<?= $post_nm; ?>" data-title="You want to duplicate <?= $post_nm; ?> header"><i class="far fa-copy"></i> Duplicate</a>
															</li>

															<li class="regenrate_section" id="<?= $cat_id; ?>">
																<a href="javascript:void(0)" title="Regenrate Thumbnail" data-id="<?= $postid; ?>" data-name="<?= $post_nm; ?>" data-title="You want to regenerate <?= $post_nm; ?> image"><i class="fas fa-repeat"></i> Regenerate Thumbnail</a>
															</li>

															<div class="dividerLine"></div>

															<li class="section_rename_btn"><a href="javascript:void(0)" title="Rename" id="rename_section" data-id="<?= $postid; ?>" data-name="<?= $post_nm; ?>" data-title="You want to rename <?= $post_nm; ?> header"><i class="fas fa-text-width"></i> Rename</a></li>

															<li><a href="javascript:void(0)" class="del_saction" id="<?php echo $postid; ?>" data-id="<?= site_url('my-library/?tabs=header'); ?>" data_title="Do you want to remove the <?= $post_nm; ?> header?" data_nm="<?= $post_nm; ?> header"><i class="far fa-trash-alt"></i> Move to Trash</a></li>
														</ul>
													</span>
												</li>
						            		<?php $j++; ?>
										<?php } ?>
										</ul>
										<div class="page_navi header_pagination">
											<?php wp_pagenavi( array( 'query' => $query ) ); ?>
										</div>
									</div>

								<?php } else { ?>
			            			<div class="no-data-found">
			            				<div class="accordion-content">
								            <div class="rowWrap starred_prj">
								              	<div class="flex-12 dashboard_no">
								                   	<div class="uploadData bg-white p-2 plusSign">
														<img src="<?php echo plugins_url(); ?>/divi-builder/images/cross-icon.png" alt="..." />
														<p>You do not currently have any header saved in your library.<br/> Upload a new header template to get started</p>
														<a href="javascript:void(0)" class="project-btn">Upload <i class="fas fa-upload"></i></a>
													</div>													   
								                </div>
							                  										                  
								            </div>
								        </div>
			            			</div>
				           		<?php } ?>
								<?php wp_reset_postdata(); ?>	
							</div>
							<!-- End Header Table Html -->


							<!-- Start Footer Table Html -->
					      	<div class="tab_section" id="mytab_footer" style="display:none;">
						      	<?php
								$user = wp_get_current_user();
								$current_user= $user->ID;
								$paged = get_query_var('paged') ? get_query_var('paged') : 1;
								$args= array(
									'post_type'=>'project',
									'order'=>'DESC',
									'paged' => $paged,
									'posts_per_page' => 15,
									'meta_query'    => array(
										'relation' => 'AND',
										array(
											'key'       => 'builder_custom_cat_user',
											'value'     => $current_user,
											'compare'   => 'LIKE',
										)
									),
									'tax_query' => array(
								        array (
								            'taxonomy' => 'project_category',
								            'field' => 'term_id',
								            'terms' => '502',
								        )
								    )
								);
								$query = new WP_Query($args);
								?>
								
								<?php if ( $query->have_posts() ) { ?>
									<?php $j=1; ?>

							      	<div class="sectionTitle filter-options">
					         			<!-- <h3 class="libTitle dropdown">Footer</h3> -->
					         			<h3 class="libTitle dropdown"></h3>
					         			<div class="viewList list_type">
								      		<ul class="viewList">
									            <!-- <li><a href="javascript:;"><i class="fas fa-sort-amount-down" aria-hidden="true"></i></a></li> -->
									            <li>
									               <div class="filter-buttons">
									                  <div class="grid-view-button"><i class="fas fa-th-large"></i></div>
									                  <div class="list-view-button active"><i class="fas fa-bars"></i></div>
									               </div>
									            </li>
								         	</ul>
								      	</div>
							      	</div>

							      	<div class="list list-view-filter" id="tab_section">
							         	<ul class="listLi">
							         		<li class="backColor">
								               <span>&nbsp;</span>
								               <span class="width_1">Preview</span>
								               <span class="width_2">Title <img src="<?php echo plugins_url(); ?>/divi-builder/images/sort-arrow.png" alt="..." /></span>
								               <span class="width_4">Created <img src="<?php echo plugins_url(); ?>/divi-builder/images/sort-arrow.png" alt="..." /></span>
								               <span class="lastChild">&nbsp;</span>
								            </li>
											<?php while ( $query->have_posts() ) { ?>									
												<?php 
												$query->the_post();
												$postid=get_the_id();
												$post_nm= get_the_title();
												$post_create_date = get_the_date('M d, Y', $postid);
												$builder_json_link=get_post_meta($postid, 'builder_json', true);
												$feat_image = wp_get_attachment_url( get_post_thumbnail_id($postid) );
												
											?>
								            	<li>
								            		<span></span>
													<!-- <span><input type="checkbox" name="select"></span> -->
													<span class="width_1"><img src="<?= $feat_image; ?>" alt="<?= $post_nm; ?>" /></span>
													<span class="width_2"><strong><?= $post_nm; ?></strong></span>
													<span class="width_4"><?= $post_create_date ?></span>
													<span class="lastChild">
														<button class="buttonView"><i class="fas fa-ellipsis-v"></i></button>
														<ul class="dropdownList">
															<li><a href="<?php echo get_the_permalink(); ?>"><i class="far fa-eye"></i> Preview</a></li>

															<div class="dividerLine"></div>
															<!--<li><a href="javascript:void(0)"><i class="fas fa-share-alt"></i> Share</a></li>-->

															<li class="duplicate_section" id="<?= $cat_id; ?>">
																<a href="javascript:void(0)" title="Duplicate" data-id="<?= $postid; ?>" data-name="<?= $post_nm; ?>" data-title="You want to duplicate <?= $post_nm; ?> footer"><i class="far fa-copy"></i> Duplicate</a>
															</li>

															<li class="regenrate_section" id="<?= $cat_id; ?>">
																<a href="javascript:void(0)" title="Regenrate Thumbnail" data-id="<?= $postid; ?>" data-name="<?= $post_nm; ?>" data-title="You want to regenerate <?= $post_nm; ?> image"><i class="fas fa-repeat"></i> Regenerate Thumbnail</a>
															</li>

															<div class="dividerLine"></div>

															<li class="section_rename_btn"><a href="javascript:void(0)" title="Rename" id="rename_section" data-id="<?= $postid; ?>" data-name="<?= $post_nm; ?>" data-title="You want to rename <?= $post_nm; ?> footer name"><i class="fas fa-text-width"></i> Rename</a></li>

															<li><a href="javascript:void(0)" class="del_saction" id="<?php echo $postid; ?>" data-id="<?= site_url('my-library/?tabs=footer'); ?>" data_title="Do you want to remove the <?= $post_nm; ?> footer?" data_nm="<?= $post_nm; ?> footer"><i class="far fa-trash-alt"></i> Move to Trash</a></li>
														</ul>
													</span>
												</li>
						            		<?php $j++; ?>
										<?php } ?>
										</ul>
										<div class="page_navi header_pagination">
											<?php wp_pagenavi( array( 'query' => $query ) ); ?>
										</div>
									</div>

								<?php } else { ?>
			            			<div class="no-data-found">
			            				<div class="accordion-content">
								            <div class="rowWrap starred_prj">
								              	<div class="flex-12 dashboard_no">
								                   	<div class="uploadData bg-white p-2 plusSign">
														<img src="<?php echo plugins_url(); ?>/divi-builder/images/cross-icon.png" alt="..." />
														<p>You do not currently have any footer saved in your library.<br/> Upload a new footer template to get started</p>
														<a href="javascript:void(0)" class="project-btn">Upload <i class="fas fa-upload"></i></a>
													</div>													   
								                </div>
							                  										                  
								            </div>
								        </div>
			            			</div>
				           		<?php } ?>
								<?php wp_reset_postdata(); ?>	
							</div>
							<!-- End Footer Table Html -->
				      </div>
				   </div>
				</div>

				<!-- Section Modal -->
				<div id="library_section" class="library_section modal" style="display: none;">
					<div class="modal-content modal-sm">							
						<div class="modal-header">
							<span class="closebtn" onclick="jQuery('#library_section').fadeOut('slow');"><img src="<?php echo plugins_url(); ?>/divi-builder/images/close-png.png" /></span>
							<h3 id="page_nm" class="text-center text-bold">Import a new Section</h3>
						</div>
						<p class="text-center">You can use this tool to import and store your own personal section for faster development.</p>						
						<div class="importBox">
							<form method="post" class="builder_import" data-id="<?= site_url('my-library/?tabs=section'); ?>" autocomplete="off" enctype="multipart/form-data" style="width: 100%;">
								<img id="previewImage" src="" style="display:none;">
								<div>
									<div id="step1">
										<div class="wrapper">
											<div class="drop">
											<div class="cont" style="color: rgb(142, 153, 165);">
												<img src="<?php echo plugins_url(); ?>/divi-builder/images/drag-icon.png" alt="..." />
												<h3>Drag & Drop Files <span>or <label for="files">browse JSON files</label> on your computer</span></h3>
											</div>
											<output id="list"></output>
											<input id="section_files" multiple="false" name="files[]" type="file" accept="json/*" data-id="section">
										</div>
										</div>
										<div>
											<button type="button" class="default-btn chascrren" onclick="jQuery('#list_section').show();jQuery('#step1').hide();" style="pointer-events:none;">Next</button>
										</div>
										
									</div>

									<div class="template-table-responsive row" id="list_section" style="display: none;">
										<table class="table">
											<thead>
												<tr>
													<th scope="col">Filename</th>
													<th scope="col">Title</th>
													<th scope="col">Category</th>
												</tr>
											</thead>
											<tbody>
												<tr style="display: none;">
													<td>
													
													</td>
													<td>
														<input type="text" id="builder_import_title" name="builder_import_title" placeholder="Title" autocomplete="off">

														<span class="span_error" id="import_title_span" style="display:none;">This Field is required</span>
													</td>
													<td>
														<select>
															<option value="">Select Category</option>
														</select>
														<input type="text" name="cate_name" id="cate_name" placeholder="New Category" style="display:none;">
													</td>
												</tr>
											</tbody>
										</table>

										<input type="hidden" name="import_data" value="section">
										<button type="submit" class="default-btn"><i class="fas fa-plus"></i>&nbsp;&nbsp;Import and Save</button>
									</div>


									<!-- <div class="builder_template_section col-sm" id="tab_template" style="visibility:hidden; position: absolute;"> -->
									<div class="builder_template_section col-sm" id="tab_template">
										<div id="content-1">
											<div class="topWrapmenu m-0">
												<div class="builder_import_section" style="display: none;">
													<div class="tabWrapcontent padding-profile" style="display:none;">
													</div>
												</div>
											</div>
										</div>
										
										<div class="inner_wrap_iframe" style="width: 1920px; overflow-y: auto; display: none;">
											<iframe id="dd" src="" style=" width: 100%; overflow-y: scroll;height: 700px;"></iframe>
										</div>
									</div>
								</div>
								<input type="hidden" name="action" value="builder_create_section">
							</form>
						</div>	
					</div>
				</div>				
				<!-- End Section Modal -->

				<!-- Page Modal -->
				<div id="library_page" class="library_section modal" style="display: none;">
					<div class="modal-content modal-sm">							
						<div class="modal-header">
							<span class="closebtn" onclick="jQuery('#library_page').fadeOut('slow');"><img src="<?php echo plugins_url(); ?>/divi-builder/images/close-png.png" /></span>
							<h3 id="page_nm" class="text-center text-bold">Import a new Layouts</h3>
						</div>
						<p class="text-center">You can use this tool to import and store your own personal layouts for faster development.</p>						
						<div class="importBox">
							<form method="post" class="builder_import" data-id="<?= site_url('my-library/?tabs=page'); ?>" autocomplete="off" enctype="multipart/form-data" style="width: 100%;">
								<img id="previewImage" src="" style="display:none;">
								<div>
									<div id="step1_page">
										<div class="wrapper">
											<div class="drop">
											<div class="cont" style="color: rgb(142, 153, 165);">
												<img src="<?php echo plugins_url(); ?>/divi-builder/images/drag-icon.png" alt="..." />
												<h3>Drag & Drop Files <span>or <label for="files">browse JSON files</label> on your computer</span></h3>
											</div>
											<output id="list"></output>
											<input id="page_files" multiple="false" name="files[]" type="file" accept="json/*" data-id="page">

										</div>
										</div>
										<div>
											<button style="pointer-events: none;" type="button" class="default-btn chascrren" onclick="jQuery('#list_section_page').show();jQuery('#step1_page').hide();">Next</button>
										</div>
										
									</div>

									<div class="template-table-responsive row" id="list_section_page" style="display: none;">
										<table class="table">
											<thead>
												<tr>
													<th scope="col" colspan="4" style="text-align:center">Filename</th>
													<!-- <th scope="col">Title</th>
													<th scope="col">Industry</th>
													<th scope="col">Page Type</th> -->
												</tr>
											</thead>
											<tbody>
												<tr style="display: none;">
													<td>
													
													</td>
													<td>
														<input type="text" id="builder_import_title" name="builder_import_title" placeholder="Title" autocomplete="off">

														<span class="span_error" id="import_title_span" style="display:none;">This Field is required</span>
													</td>
													<td>
														<select>
															<option value="">Select Category</option>
															<option value="800">Global Headers</option>	
															<optgroup> 
																<option value="add-new">  New Category</option>
															</optgroup>			
														</select>
														<input type="text" name="cate_name" id="cate_name" placeholder="New Category" style="display:none;">
													</td>
												</tr>
											</tbody>
										</table>
										<input type="hidden" name="import_data" value="page">
										<button type="submit" class="default-btn"><i class="fas fa-plus"></i>&nbsp;&nbsp;Import and Save</button>
										<br>
										<em style=""><strong>Note:</strong> Only upload page templates that have been exported through the front end Divi builder. If you try and upload a page from the Divi Library, it may not uploaded properly.</em>
									</div>


									<!-- <div class="builder_template_section col-sm" id="tab_template" style="visibility:hidden; position: absolute;"> -->
									<div class="builder_template_section col-sm" id="tab_template">
										<div id="content-1">
											<div class="topWrapmenu m-0">
												<div class="builder_import_section" style="display: none;">
													<div class="tabWrapcontent padding-profile" style="display:none;">
													</div>
												</div>
											</div>
										</div>
										
										<div class="inner_wrap_iframe" style="width: 1920px; overflow-y: auto; display: none;">
											<iframe id="dd2" src="" style=" width: 100%; overflow-y: scroll;height: 700px;"></iframe>
										</div>
									</div>
								</div>
								<input type="hidden" name="action" value="builder_create_section">
							</form>
						</div>	
					</div>
				</div>	
				<!-- End Page Modal -->

				<!-- Header Modal -->
				<div id="library_header" class="library_section modal" style="display: none;">
					<div class="modal-content modal-sm">							
						<div class="modal-header">
							<span class="closebtn" onclick="jQuery('#library_header').fadeOut('slow');"><img src="<?php echo plugins_url(); ?>/divi-builder/images/close-png.png" /></span>
							<h3 id="page_nm" class="text-center text-bold">Import a new header</h3>
						</div>
						<p class="text-center">You can use this tool to import and store your own personal header for faster development.</p>						
						<div class="importBox">
							<form method="post" class="builder_import" data-id="<?= site_url('my-library/?tabs=header'); ?>" autocomplete="off" enctype="multipart/form-data" style="width: 100%;">
								<img id="previewImage" src="" style="display:none;">
								<div>
									<div id="step1_page">
										<div class="wrapper">
											<div class="drop">
											<div class="cont" style="color: rgb(142, 153, 165);">
												<img src="<?php echo plugins_url(); ?>/divi-builder/images/drag-icon.png" alt="..." />
												<h3>Drag & Drop Files <span>or <label for="files">browse JSON files</label> on your computer</span></h3>
											</div>
											<output id="list"></output>
											<input id="header_files" multiple="false" name="files[]" type="file" accept="json/*" data-id="header">

										</div>
										</div>
										<div>
											<button style="pointer-events: none;" type="button" class="default-btn chascrren" onclick="jQuery('#list_section_header').show();jQuery('#step1_page').hide();">Next</button>
										</div>
										
									</div>

									<div class="template-table-responsive row" id="list_section_header" style="display: none;">
										<table class="table">
											<thead>
												<tr>
													<th scope="col">Filename</th>
													<th scope="col">Title</th>
													<th scope="col">Category</th>
												</tr>
											</thead>
											<tbody>
												<tr style="display: none;">
													<td>
													
													</td>
													<td>
														<input type="text" id="builder_import_title" name="builder_import_title" placeholder="Title" autocomplete="off">

														<span class="span_error" id="import_title_span" style="display:none;">This Field is required</span>
													</td>
													<td>
														<select>
															<option value="">Select Category</option>
															<option value="800">Global Headers</option>	
															<optgroup> 
																<option value="add-new">  New Category</option>
															</optgroup>			
														</select>
														<input type="text" name="cate_name" id="cate_name" placeholder="New Category" style="display:none;">
													</td>
												</tr>
											</tbody>
										</table>
										<input type="hidden" name="import_data" value="section">
										<button type="submit" class="default-btn"><i class="fas fa-plus"></i>&nbsp;&nbsp;Import and Save</button>
									</div>


									<!-- <div class="builder_template_section col-sm" id="tab_template" style="visibility:hidden; position: absolute;"> -->
									<div class="builder_template_section col-sm" id="tab_template">
										<div id="content-1">
											<div class="topWrapmenu m-0">
												<div class="builder_import_section" style="display: none;">
													<div class="tabWrapcontent padding-profile" style="display:none;">
													</div>
												</div>
											</div>
										</div>
										
										<div class="inner_wrap_iframe" style="width: 1920px; overflow-y: auto; display: none;">
											<iframe id="dd2" src="" style=" width: 100%; overflow-y: scroll;height: 700px;"></iframe>
										</div>
									</div>
								</div>
								<input type="hidden" name="action" value="builder_create_section">
							</form>
						</div>	
					</div>
				</div>
				<!-- End Header Modal -->



				<!-- Footer Modal -->
				<div id="library_footer" class="library_section modal" style="display: none;">
					<div class="modal-content modal-sm">							
						<div class="modal-header">
							<span class="closebtn" onclick="jQuery('#library_footer').fadeOut('slow');"><img src="<?php echo plugins_url(); ?>/divi-builder/images/close-png.png" /></span>
							<h3 id="page_nm" class="text-center text-bold">Import a new footer</h3>
						</div>
						<p class="text-center">You can use this tool to import and store your own personal footer for faster development.</p>						
						<div class="importBox">
							<form method="post" class="builder_import" data-id="<?= site_url('my-library/?tabs=footer'); ?>" autocomplete="off" enctype="multipart/form-data" style="width: 100%;">
								<img id="previewImage" src="" style="display:none;">
								<div>
									<div id="step1_page">
										<div class="wrapper">
											<div class="drop">
											<div class="cont" style="color: rgb(142, 153, 165);">
												<img src="<?php echo plugins_url(); ?>/divi-builder/images/drag-icon.png" alt="..." />
												<h3>Drag & Drop Files <span>or <label for="files">browse JSON files</label> on your computer</span></h3>
											</div>
											<output id="list"></output>
											<input id="footer_files" multiple="false" name="files[]" type="file" accept="json/*" data-id="footer">

										</div>
										</div>
										<div>
											<button style="pointer-events: none;" type="button" class="default-btn chascrren" onclick="jQuery('#list_section_footer').show();jQuery('#step1_page').hide();">Next</button>
										</div>
										
									</div>

									<div class="template-table-responsive row" id="list_section_footer" style="display: none;">
										<table class="table">
											<thead>
												<tr>
													<th scope="col">Filename</th>
													<th scope="col">Title</th>
													<th scope="col">Category</th>
												</tr>
											</thead>
											<tbody>
												<tr style="display: none;">
													<td>
													
													</td>
													<td>
														<input type="text" id="builder_import_title" name="builder_import_title" placeholder="Title" autocomplete="off">

														<span class="span_error" id="import_title_span" style="display:none;">This Field is required</span>
													</td>
													<td>
														<select>
															<option value="">Select Category</option>
															<option value="800">Global Headers</option>	
															<optgroup> 
																<option value="add-new">  New Category</option>
															</optgroup>			
														</select>
														<input type="text" name="cate_name" id="cate_name" placeholder="New Category" style="display:none;">
													</td>
												</tr>
											</tbody>
										</table>
										<input type="hidden" name="import_data" value="section">
										<button type="submit" class="default-btn"><i class="fas fa-plus"></i>&nbsp;&nbsp;Import and Save</button>
									</div>


									<!-- <div class="builder_template_section col-sm" id="tab_template" style="visibility:hidden; position: absolute;"> -->
									<div class="builder_template_section col-sm" id="tab_template">
										<div id="content-1">
											<div class="topWrapmenu m-0">
												<div class="builder_import_section" style="display: none;">
													<div class="tabWrapcontent padding-profile" style="display:none;">
													</div>
												</div>
											</div>
										</div>
										
										<div class="inner_wrap_iframe" style="width: 1920px; overflow-y: auto; display: none;">
											<iframe id="dd2" src="" style=" width: 100%; overflow-y: scroll;height: 700px;"></iframe>
										</div>
									</div>
								</div>
								<input type="hidden" name="action" value="builder_create_section">
							</form>
						</div>	
					</div>
				</div>
				<!-- End Footer Modal -->
				
				<?php require_once 'builder_footer.php'; ?>
			</div>
			<?php
			$output .= ob_get_contents();
			ob_end_clean();
			return $output;
		} else {
			restricate_page_content();
		}
	}
}

$builder_library = new Builder_library();