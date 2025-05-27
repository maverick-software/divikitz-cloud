<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title></title>
<?php wp_head(); ?>
</head>
<body>
<div id="main-content">	
	<div class="container1">
		<div id="content-area" class="clearfix">
			<div id="left-area" style="margin: 0; padding: 0;width: 100%;">
				<?php while ( have_posts() ) : the_post(); ?>

					<article id="post-<?php the_ID(); ?>" <?php post_class( 'et_pb_post' ); ?> style="margin: 0;">
						<div class="entry-content1">
							<?php		
							if(isset($_GET['id'])){
								//$post_body   = get_post( $_GET['id'] );
								global $wpdb;
								$rows = $wpdb->get_row("select * from wp_section_preview where id=".$_GET['id']);
								echo do_shortcode($rows->post_content);
							}
							
							?>
						</div> <!-- .et_post_meta_wrapper -->
					</article> <!-- .et_pb_post -->

				<?php endwhile; ?>
			</div> <!-- #left-area -->

			<?php //get_sidebar(); ?>
		</div> <!-- #content-area -->
	</div> <!-- .container -->
	
</div> <!-- #main-content -->

</body>
<?php wp_footer();?>
</html>