<?php //Template Name: Tutorials Video ?>

<?php get_header(); ?>


<?php if(isset($_REQUEST['et_fb'])){ ?>

<?php
$is_page_builder_used = et_pb_is_pagebuilder_used( get_the_ID() );
?>

	<div id="main-content">

	<?php if ( ! $is_page_builder_used ) : ?>

		<div class="container">
			<div id="content-area" class="clearfix">
				<div id="left-area">

	<?php endif; ?>

				<?php while ( have_posts() ) : the_post(); ?>

					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

					<?php if ( ! $is_page_builder_used ) : ?>

						<h1 class="entry-title main_title"><?php the_title(); ?></h1>
					<?php
						$thumb = '';

						$width = (int) apply_filters( 'et_pb_index_blog_image_width', 1080 );

						$height = (int) apply_filters( 'et_pb_index_blog_image_height', 675 );
						$classtext = 'et_featured_image';
						$titletext = get_the_title();
						$alttext = get_post_meta( get_post_thumbnail_id(), '_wp_attachment_image_alt', true );
						$thumbnail = get_thumbnail( $width, $height, $classtext, $alttext, $titletext, false, 'Blogimage' );
						$thumb = $thumbnail["thumb"];

						if ( 'on' === et_get_option( 'divi_page_thumbnails', 'false' ) && '' !== $thumb )
							print_thumbnail( $thumb, $thumbnail["use_timthumb"], $alttext, $width, $height );
					?>

					<?php endif; ?>

						<div class="entry-content">
						<?php
							the_content();

							if ( ! $is_page_builder_used )
								wp_link_pages( array( 'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'Divi' ), 'after' => '</div>' ) );
						?>
						</div>

					<?php
						if ( ! $is_page_builder_used && comments_open() && 'on' === et_get_option( 'divi_show_pagescomments', 'false' ) ) comments_template( '', true );
					?>

					</article>

				<?php endwhile; ?>

				<?php if ( ! $is_page_builder_used ) : ?>

				</div>

				<?php get_sidebar(); ?>
			</div>
		</div>

	<?php endif; ?>

	</div>



<?php } else { ?>

<div class="contentWrapper user_actions" id="table-page">
				<!-- //sidebar  --> 
	<?php require_once WP_PLUGIN_DIR.'/divi-builder/templates/builder_siderbar.php'; ?>

	<div class="wrapContent">
		<!-- //Top Bar  --> 
		<?php require_once WP_PLUGIN_DIR.'/divi-builder/templates/builder_topnav.php'; ?>

   		<div class="wrapContainer user_actions">
			<div class="container">
				<div id="content-area" class="clearfix">
					<div id="left-area">
						<?php while ( have_posts() ) : the_post(); ?>
							<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
								<div class="entry-content">
								<?php the_content(); ?>
								</div>
							</article>
						<?php endwhile; ?>
					</div>
				</div>
			</div>		
      	</div>
   	</div>
</div>

<?php
}
get_footer();