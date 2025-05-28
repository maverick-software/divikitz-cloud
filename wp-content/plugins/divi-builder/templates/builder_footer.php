<div class="mobWrapPanel" id="mobile-wrapper">


	<!-- User Own Create Categories -->
	<?php
	$current_user = wp_get_current_user();
	$current_user_id= $current_user->ID;
	$builder_project = get_terms( 
		array(
	    	'taxonomy' => 'project_categories',
	    	'hide_empty' => false,
	    	'meta_query' => array(
			    array(
			       'key'       =>  'builder_cat_user',
			       'value'     =>  $current_user_id,
			       'compare'   =>  'LIKE'
			    )
			)
		) 
	);
	?>

	<?php 
	if(isset($builder_project) && !empty($builder_project)){ 
		$submenu="";
		foreach ($builder_project as $builder_cats):
			$term_id= $builder_cats->term_id;
			$term_nm= ucfirst($builder_cats->name);
			$term_link= site_url().'/builder-projects?term_id='.$term_id;;
			$submenu .="<li class='mega-menu-item  mega-menu-item-$term_id' id='mega-menu-item-$term_id'>";
			$submenu .="<a class='mega-menu-link' href='$term_link'>$term_nm</a>";
			$submenu .="</li>";
		endforeach;
		
	?>

	<?php } ?>

	<!-- End User Own Create Categories -->


	<div class="mobWrapMenu">
		<div class="mobile_top_menus">
			<?php
			wp_nav_menu( 
				array( 
				    'theme_location' => 'mobile-menu'
				) 
			);
			?>
		</div>
		
	</div>
</div>
<div class="footer-support">
	<a href="javascript:void(0);" class="support-bar" onclick="jQuery('#support_modal').show();"><img src="<?= builder_url ;?>images/support.png" width="30px"/></a>
</div>
<div id="support_modal" class="modal" style="display: none;">
	<div class="modal-content modal-sm">
		<div class="modal-header">
		
		<span class="closebtn" onclick="jQuery('#support_modal').hide();"><i class="fas fa-times"></i></span>
			<h3 id="page_nm">Contact Us</h3>
		</div>
		<div class="support-box">
			
			 <?php echo do_shortcode('[gravityform id="1" title="false" description="false" ajax="true" tabindex=""]'); ?>
		</div>
	</div>
<!-- 	<img src="<?php echo builder_url; ?>images/bloxxlogo.svg" width="180px" class="modal-brand-img" /> -->
</div>


<div class="videoModal">
    <div class="custom-model-inner">        
        <div class="modalClose">×</div>
        <div class="custom-model-wrap">
            <div class="pop-up-content-wrap">
                
            </div>
        </div>  
    </div>  
    <div class="bg-overlay"></div>
</div>
