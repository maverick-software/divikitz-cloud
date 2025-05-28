<?php 
get_header();
?><div class="contentWrapper user_actions" id="table-page">
				<!-- //sidebar  --> 
				<?php require_once 'builder_siderbar.php'; ?>
				<div class="wrapContent">

				 	<!-- //Top Bar  --> 
                    <?php require_once 'builder_topnav.php'; ?>

				   	<div class="wrapContainer user_actions checkoutPage">
				      <?php 
				      	while ( have_posts() ) : the_post(); 
				      	the_content();
				       endwhile; ?>
					</div>
				</div>							
				<?php require_once 'builder_footer.php'; ?>
			</div>
			<?php get_footer();?>