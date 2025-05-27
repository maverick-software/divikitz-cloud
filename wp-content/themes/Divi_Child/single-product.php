<?php 
get_header();
?><div class="contentWrapper user_actions" id="table-page">
				<!-- //sidebar  --> 
				<?php require_once builder_path.'templates/builder_siderbar.php'; ?>
				<div class="wrapContent">
				   <div class="topWrapmenu">
				      <div>
				         <a href="javascript:void(0);" class="togglebar"><img src="<?php echo plugins_url(); ?>/divi-builder/images/right-angle.png"/></a>
				      </div>
				      <div class="rowWrap">
				         <div class="flex-3">
				            <?php 
					            global $wp_roles;
					            global $ultimatemember;
					            $user = wp_get_current_user();
					            $current_user_id= $user->ID;

					            $current_plan=get_user_meta($current_user_id, 'current_plan_selected', true);
					            if($current_plan!=""){
					                $plan_title=get_the_title($current_plan);                
					            } else {
					                $plan_title="Free";                
					            }

					            $display_nm= get_user_meta($current_user_id, "display_name", true);
					            $timestemp= strtotime(date("Y-m-d H:i:s"));
					            $nonce = wp_create_nonce( 'um_upload_nonce-' . $timestemp);

					            um_fetch_user( $current_user_id );
					            
					            $user_profile=get_user_meta($current_user_id, "profile_photo", true);

					            $avatar_uri = um_get_avatar_uri( um_profile('profile_photo'), 32 );
					            if($user_profile==""){
					            	$avatar_uri= builder_url."images/profile-icon.png";
					            }
				            ?>

                                <h5><?php echo get_the_title(); ?></h5>
				         </div>
				         <div class="flex-9 text-right">
				            <ul class="topMenuUser">
				            	<!-- <a href="<?php // echo builder_url.'assets/addons/bloxx.zip'; ?>" download class="default-btn">Download Plugin</a> -->
				              
				               	<li class="storeIcon"><a href="https://sitebloxx.com/"><i class="fas fa-shopping-basket"></i> Store</a></li>
				               	<!-- <li class="plusSign"><a href="javascript:void(0)" title="Add New Section"><i class="fas fa-plus"></i></a></li> -->
				               	<li><a href="#"><i class="far fa-bell"></i></a></li>
				               	
				               	<li><?php echo do_shortcode('[profile_details]'); ?></li>
				            </ul>
				         </div>
				      </div>
				   </div>
				   <div class="wrapContainer user_actions checkoutPage">
				     <?php while ( have_posts() ) : ?>
			<?php the_post(); ?>

			<?php wc_get_template_part( 'content', 'single-product' ); ?>

		<?php endwhile; // end of the loop. ?>
					</div>
				</div>							
				<?php require_once builder_path.'templates/builder_mobile_nav.php'; ?>
			</div>
			<?php get_footer();?>