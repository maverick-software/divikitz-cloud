<?php 
if(isset($_GET['invite_email']) && $_GET['invite_email']!=''){
	$invite_email = $_GET['invite_email'];
	$invite_email_exists = email_exists( $invite_email );
	if ( $invite_email_exists ) {
		$invite_email_userid = $invite_email_exists;
	    // send to login page if user already exists
	    $register_url = get_site_url().'/login/';
	    wp_redirect($register_url);
	    exit;
	} else {
		$register_url = get_site_url().'/register?ref_invite_email='.$invite_email;
	    wp_redirect($register_url);
	    exit;
	}
}




?>
<!DOCTYPE html>
<!--[if IE 6]>
<html id="ie6" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 7]>
<html id="ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html id="ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 6) | !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<?php elegant_description(); ?>
	<?php elegant_keywords(); ?>
	<?php elegant_canonical(); ?>

	<?php do_action( 'et_head_meta' ); ?>

	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

	<?php $template_directory_uri = get_template_directory_uri(); ?>
	<!--[if lt IE 9]>
	<script src="<?php echo esc_url( $template_directory_uri . '/js/html5.js"' ); ?>" type="text/javascript"></script>
<![endif]-->
<script type="text/javascript">
		function backtocust(){
			//alert(5);
			window.location.replace(jQuery(".move_2divi1").data("href")); 
			return false;
		}
	</script>
<script type="text/javascript">
	document.documentElement.className = 'js';
</script>

<?php wp_head(); ?>




<?php 
if(isset($_GET['ref_invite_email']) && $_GET['ref_invite_email']!=''){
	?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
			//	alert('test');
				jQuery('#user_email-57').val("<?php echo $_GET['ref_invite_email']; ?>");
			});
		</script>
	<?php
}
?>
<style type="text/css">
	.diviblock-preloader {
		display: block;
		height: 100%;
		width: 100%;
		position: fixed;
		top: 0;
		right: 0;
		bottom: 0;
		left: 0;
		opacity: 1;
		-webkit-transition: opacity 0.8s 0.8s;
		transition: opacity 0.8s 0.8s;
		overflow: hidden;
		background-color: #333;
		z-index: 1999;
	}
	.diviblock-preloader .progress-bar-s {
		padding: 0 30px 30px;
		height: 10px;
		width: 300px;
		color: #444;
		position: absolute;
		bottom: 50%;
		margin-bottom: -120px;
		left: 50%;
		margin-left: -150px;
		-webkit-transition: all 2.5s ease-out;
		transition: all 2.5s ease-out;
	}
	.progress {
		display: -ms-flexbox;
		display: -webkit-box;
		display: flex;
		height: 1rem;
		overflow: hidden;
		font-size: .75rem;
		background-color: #e9ecef;
		border-radius: .25rem;
	}
	.diviblock-preloader .progress-bar-s .progress .load {
		height: 2px;
		width: 0%;
		background-color: #FFF;
		-webkit-transition: all 2.5s ease-out;
		-webkit-transition: all 1.4s ease-out;
		transition: all 1.4s ease-out;
	}
	.diviblock-preloader .progress-bar-s .progress {
		height: 2px;
		background-color: #444;
		-webkit-transition: all 2.5s ease-out;
		transition: all 2.5s ease-out;
	}
	.diviblock-preloader .rights {
		position: absolute;
		width: 150px;
		text-align: center;
		bottom: 0;
		left: 0;
		right: 0;
		margin: 0 auto 45px;
	}
	.diviblock-preloader img{
		width: 17%;
		top: 45%;
		position: absolute;
		left: 41%;
	}
	.diviblock-preloader .rights p,
	.diviblock-preloader .rights p a {
		color: #666;
	}
</style>
</head>
<body <?php body_class(); ?>>

	<?php if(is_single() && !isset($_GET['et_fb']) && !isset($_REQUEST['screenshot'])):?>
		<!-- <div class="diviblock-preloader">
			<img src="https://app.gobloxx.io/wp-content/uploads/2021/08/appbloxx-1.png" alt="diviblocklogo" style="bottom: 50px!important;">
			<div class="progress-bar-s">
				<div class="progress"><div class="load"></div></div>
			</div>
			<div class="rights">
				<p>© 2021 <a href="https://app.gobloxx.io" target="_blank">Bloxx</a></p>
			</div>
		</div> -->
	<?php endif; ?>

	<div id="page-container">
		<?php
		if ( is_page_template( 'page-template-blank.php' ) ) {
			return;
		}

		$et_secondary_nav_items = et_divi_get_top_nav_items();

		$et_phone_number = $et_secondary_nav_items->phone_number;

		$et_email = $et_secondary_nav_items->email;

		$et_contact_info_defined = $et_secondary_nav_items->contact_info_defined;

		$show_header_social_icons = $et_secondary_nav_items->show_header_social_icons;

		$et_secondary_nav = $et_secondary_nav_items->secondary_nav;

		$et_top_info_defined = $et_secondary_nav_items->top_info_defined;

		$et_slide_header = 'slide' === et_get_option( 'header_style', 'left' ) || 'fullscreen' === et_get_option( 'header_style', 'left' ) ? true : false;
		?>

		<?php if ( $et_top_info_defined && ! $et_slide_header || is_customize_preview() ) : ?>
			<div id="top-header"<?php echo $et_top_info_defined ? '' : 'style="display: none;"'; ?>>
				<div class="container clearfix">

					<?php if ( $et_contact_info_defined ) : ?>

						<div id="et-info">
							<?php if ( '' !== ( $et_phone_number = et_get_option( 'phone_number' ) ) ) : ?>
								<span id="et-info-phone"><?php echo et_sanitize_html_input_text( $et_phone_number ); ?></span>
							<?php endif; ?>

							<?php if ( '' !== ( $et_email = et_get_option( 'header_email' ) ) ) : ?>
								<a href="<?php echo esc_attr( 'mailto:' . $et_email ); ?>"><span id="et-info-email"><?php echo esc_html( $et_email ); ?></span></a>
							<?php endif; ?>

							<?php
							if ( true === $show_header_social_icons ) {
								get_template_part( 'includes/social_icons', 'header' );
							} ?>
						</div> <!-- #et-info -->

					<?php endif; // true === $et_contact_info_defined ?>

					<div id="et-secondary-menu">
						<?php
						if ( ! $et_contact_info_defined && true === $show_header_social_icons ) {
							get_template_part( 'includes/social_icons', 'header' );
						} else if ( $et_contact_info_defined && true === $show_header_social_icons ) {
							ob_start();

							get_template_part( 'includes/social_icons', 'header' );

							$duplicate_social_icons = ob_get_contents();

							ob_end_clean();

							printf(
								'<div class="et_duplicate_social_icons">
								%1$s
								</div>',
								$duplicate_social_icons
							);
						}

						if ( '' !== $et_secondary_nav ) {
							echo $et_secondary_nav;
						}

						et_show_cart_total();
						?>
					</div> <!-- #et-secondary-menu -->

				</div> <!-- .container -->
			</div> <!-- #top-header -->
		<?php endif; // true ==== $et_top_info_defined ?>

		<?php if ( $et_slide_header || is_customize_preview() ) : ?>
			<div class="et_slide_in_menu_container">
				<?php if ( 'fullscreen' === et_get_option( 'header_style', 'left' ) || is_customize_preview() ) { ?>
					<span class="mobile_menu_bar et_toggle_fullscreen_menu"></span>
				<?php } ?>

				<?php
				if ( $et_contact_info_defined || true === $show_header_social_icons || false !== et_get_option( 'show_search_icon', true ) || class_exists( 'woocommerce' ) || is_customize_preview() ) { ?>
					<div class="et_slide_menu_top">

						<?php if ( 'fullscreen' === et_get_option( 'header_style', 'left' ) ) { ?>
							<div class="et_pb_top_menu_inner">
							<?php } ?>
						<?php }

						if ( true === $show_header_social_icons ) {
							get_template_part( 'includes/social_icons', 'header' );
						}

						et_show_cart_total();
						?>
						<?php if ( false !== et_get_option( 'show_search_icon', true ) || is_customize_preview() ) : ?>
						<?php if ( 'fullscreen' !== et_get_option( 'header_style', 'left' ) ) { ?>
							<div class="clear"></div>
						<?php } ?>
						<form role="search" method="get" class="et-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
							<?php
							printf( '<input type="search" class="et-search-field" placeholder="%1$s" placeholder="%2$s" name="s" title="%3$s" />',
								esc_attr__( 'Search &hellip;', 'Divi' ),
								get_search_query(),
								esc_attr__( 'Search for:', 'Divi' )
							);
							?>
							<button type="submit" id="searchsubmit_header"></button>
						</form>
					<?php endif; // true === et_get_option( 'show_search_icon', false ) ?>

					<?php if ( $et_contact_info_defined ) : ?>

						<div id="et-info">
							<?php if ( '' !== ( $et_phone_number = et_get_option( 'phone_number' ) ) ) : ?>
								<span id="et-info-phone"><?php echo et_sanitize_html_input_text( $et_phone_number ); ?></span>
							<?php endif; ?>

							<?php if ( '' !== ( $et_email = et_get_option( 'header_email' ) ) ) : ?>
								<a href="<?php echo esc_attr( 'mailto:' . $et_email ); ?>"><span id="et-info-email"><?php echo esc_html( $et_email ); ?></span></a>
							<?php endif; ?>
						</div> <!-- #et-info -->

					<?php endif; // true === $et_contact_info_defined ?>
					<?php if ( $et_contact_info_defined || true === $show_header_social_icons || false !== et_get_option( 'show_search_icon', true ) || class_exists( 'woocommerce' ) || is_customize_preview() ) { ?>
						<?php if ( 'fullscreen' === et_get_option( 'header_style', 'left' ) ) { ?>
						</div> <!-- .et_pb_top_menu_inner -->
					<?php } ?>

				</div> <!-- .et_slide_menu_top -->
			<?php } ?>

			<div class="et_pb_fullscreen_nav_container">
				<?php
				$slide_nav = '';
				$slide_menu_class = 'et_mobile_menu';

				$slide_nav = wp_nav_menu( array( 'theme_location' => 'primary-menu', 'container' => '', 'fallback_cb' => '', 'echo' => false, 'items_wrap' => '%3$s' ) );
				$slide_nav .= wp_nav_menu( array( 'theme_location' => 'secondary-menu', 'container' => '', 'fallback_cb' => '', 'echo' => false, 'items_wrap' => '%3$s' ) );
				?>

				<ul id="mobile_menu_slide" class="<?php echo esc_attr( $slide_menu_class ); ?>">

					<?php
					if ( '' == $slide_nav ) :
						?>
						<?php if ( 'on' == et_get_option( 'divi_home_link' ) ) { ?>
							<li <?php if ( is_home() ) echo( 'class="current_page_item"' ); ?>><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'Divi' ); ?></a></li>
						<?php }; ?>

						<?php show_page_menu( $slide_menu_class, false, false ); ?>
						<?php show_categories_menu( $slide_menu_class, false ); ?>
						<?php
					else :
						echo( $slide_nav );
					endif;
					?>

				</ul>
			</div>
		</div>
	<?php endif; // true ==== $et_slide_header ?>

	<header id="main-header" data-height-onload="<?php echo esc_attr( et_get_option( 'menu_height', '66' ) ); ?>">
		<div class="container clearfix et_menu_container">
			<?php
			$logo = ( $user_logo = et_get_option( 'divi_logo' ) ) && '' != $user_logo
			? $user_logo
			: $template_directory_uri . '/images/logo.png';
			?>
			<div class="logo_container">
				<span class="logo_helper"></span>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<img src="<?php echo esc_attr( $logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" id="logo" data-height-percentage="<?php echo esc_attr( et_get_option( 'logo_height', '54' ) ); ?>" />
				</a>
			</div>
			<div id="et-top-navigation" data-height="<?php echo esc_attr( et_get_option( 'menu_height', '66' ) ); ?>" data-fixed-height="<?php echo esc_attr( et_get_option( 'minimized_menu_height', '40' ) ); ?>">
				<?php if ( ! $et_slide_header || is_customize_preview() ) : ?>
					<nav id="top-menu-nav">
						<?php
						$menuClass = 'nav';
						if ( 'on' == et_get_option( 'divi_disable_toptier' ) ) $menuClass .= ' et_disable_top_tier';
						$primaryNav = '';

						$primaryNav = wp_nav_menu( array( 'theme_location' => 'primary-menu', 'container' => '', 'fallback_cb' => '', 'menu_class' => $menuClass, 'menu_id' => 'top-menu', 'echo' => false ) );

						if ( '' == $primaryNav ) :
							?>
							<ul id="top-menu" class="<?php echo esc_attr( $menuClass ); ?>">
								<?php if ( 'on' == et_get_option( 'divi_home_link' ) ) { ?>
									<li <?php if ( is_home() ) echo( 'class="current_page_item"' ); ?>><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'Divi' ); ?></a></li>
								<?php }; ?>

								<?php show_page_menu( $menuClass, false, false ); ?>
								<?php show_categories_menu( $menuClass, false ); ?>
							</ul>
							<?php
						else :
							echo( $primaryNav );
						endif;
						?>
					</nav>
				<?php endif; ?>

				<?php
				if ( ! $et_top_info_defined && ( ! $et_slide_header || is_customize_preview() ) ) {
					et_show_cart_total( array(
						'no_text' => true,
					) );
				}
				?>

				<?php if ( $et_slide_header || is_customize_preview() ) : ?>
					<span class="mobile_menu_bar et_pb_header_toggle et_toggle_<?php echo esc_attr( et_get_option( 'header_style', 'left' ) ); ?>_menu"></span>
				<?php endif; ?>

				<?php if ( ( false !== et_get_option( 'show_search_icon', true ) && ! $et_slide_header ) || is_customize_preview() ) : ?>
				<div id="et_top_search">
					<span id="et_search_icon"></span>
				</div>
			<?php endif; // true === et_get_option( 'show_search_icon', false ) ?>

			<?php do_action( 'et_header_top' ); ?>
		</div> <!-- #et-top-navigation -->
	</div> <!-- .container -->
	<div class="et_search_outer">
		<div class="container et_search_form_container">
			<form role="search" method="get" class="et-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php
				printf( '<input type="search" class="et-search-field" placeholder="%1$s" value="%2$s" name="s" title="%3$s" />',
					esc_attr__( 'Search &hellip;', 'Divi' ),
					get_search_query(),
					esc_attr__( 'Search for:', 'Divi' )
				);
				?>
			</form>
			<span class="et_close_search_field"></span>
		</div>
	</div>
</header> <!-- #main-header -->

<script>
var get_content="test";
</script>

<div id="et-main-area">