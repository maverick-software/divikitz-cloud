<?php

get_header();

//$show_default_title = get_post_meta( get_the_ID(), '_et_pb_show_title', true );
	$term_4id= (isset($_GET['term_id'])) ? $_GET['term_id'] : 0;
?>
<div id="main-content">
	
	<div class="container">
		<div id="content-area" class="clearfix">
			<div style="margin: 0; padding: 0;">
				<?php
				$args= array(
					'post_type'=>'customer_templates',
					'order'=>'desc',
					'posts_per_page' => -1,									
					'tax_query' => array(
						array(
							'taxonomy' => 'project_categories',
							'field' => 'term_id',
							'terms' => $term_4id,
							'compare'   => '=' 
						)
					)
				); 

				$query = new WP_Query($args);
				?>	
				<?php if ($query->have_posts() ) : ?>
				
			<?php $ptr=0;
				$list='';
				while ($query->have_posts() ) { ?>
			<?php //the_post();
			  $post_id = get_the_ID();
					$list.= "<li><button class='tabMenu' id='template".$post_id."' >template".$post_id." </button></li>" ;
			?>
		<div class="builder_template_section " id="tab_template<?php echo get_the_ID(); ?>" <?php if($ptr>0){ echo "style='display:none;'"; } ?> >
		<article id="post-<?php echo get_the_ID(); ?>"  style="margin: 0;">
					
					<?php $body_id= get_the_ID(); ?>
					<div class="entry-content">
					<?php
					$post_cats  = get_the_terms( $body_id, 'project_categories');
					if(isset($post_cats) && !empty($post_cats)){
						$term_4id=$post_cats[0]->term_id;
						$sync_header = get_term_meta($term_4id, 'project_header', true);
						if($sync_header!=""){
							$post_header   = get_post( $sync_header );
							echo $header_content =  apply_filters( 'the_content', $post_header->post_content );
						}
					}
					?>

					<?php					
					$post_body   = get_post( $body_id );
					echo $body_content =  apply_filters( 'the_content', $post_body->post_content );
					?>


					<?php
					if(isset($post_cats) && !empty($post_cats)){
						$term_4id=$post_cats[0]->term_id;						
						$sync_footer = get_term_meta($term_4id, 'project_footer', true);
						if($sync_footer!=""){
							$post_footer   = get_post( $sync_footer );
							echo $footer_content =  apply_filters( 'the_content', $post_footer->post_content );
						}
					}?>
					</div> <!-- .et_post_meta_wrapper -->
				</article> <!-- .et_pb_post -->
				</div>
	<?php $ptr++;
			  } ?>
<ul class="builder_template_tabs" style="display:none;">
						
				<?php echo $list; ?>

				</ul>

				
				<div class="posts_tabs">
					<button type="button" class="btn-outline-primary btn-custom-tabs btn-prev" onclick="jQuery('.builder_template_tabs').find('li button.active').next().trigger('click');"> <i class="fa fa-arrow-left"></i> Previous</button>
					<button type="button" class="btn-outline-primary btn-custom-tabs btn-next" onclick="jQuery('.builder_template_tabs').find('li button.active').prev().trigger('click');">Next  <i class="fa fa-arrow-right"></i>  </button>
				</div>
				
			<?php else : ?>
				<?php echo "Nothing Found!!" ?>
			<?php endif; ?>
				
			
			</div> <!-- #left-area -->

			<?php //get_sidebar(); ?>
		</div> <!-- #content-area -->
	</div> <!-- .container -->
	
</div> <!-- #main-content -->

<?php

get_footer();
