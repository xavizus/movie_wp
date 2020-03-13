<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package myAwesomeTheme
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="<?= get_stylesheet_directory_uri();?>/css/bootstrap.min.css">
	<link rel="stylesheet" href="<?= get_stylesheet_directory_uri();?>/css/jquery-ui.min.css">
	<script src="<?= get_stylesheet_directory_uri();?>/js/jquery.js"></script>
	<script src="<?= get_stylesheet_directory_uri();?>/js/bootstrap.min.js"></script>
	<script src="<?= get_stylesheet_directory_uri();?>/js/jquery-ui.min.js"></script>
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<script src="<?= get_stylesheet_directory_uri();?>/js/main.js"></script>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<div id="page" class="site container-fluid">
		<a class="skip-link screen-reader-text"
			href="#content"><?php esc_html_e( 'Skip to content', 'myAwesomeTheme' ); ?></a>

		<header id="masthead" class="site-header">
			<div class="site-branding">
				<?php
			the_custom_logo();
			if ( is_front_page() && is_home() ) :
				?>
				<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"
						rel="home"><?php bloginfo( 'name' ); ?></a></h1>
				<?php
			else :
				?>
				<p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"
						rel="home"><?php bloginfo( 'name' ); ?></a></p>
				<?php
			endif;
			$myAwesomeTheme_description = get_bloginfo( 'description', 'display' );
			if ( $myAwesomeTheme_description || is_customize_preview() ) :
				?>
				<p class="site-description"><?php echo $myAwesomeTheme_description; /* WPCS: xss ok. */ ?></p>
				<?php endif; ?>
			</div><!-- .site-branding -->

			<nav class="navbar navbar-expand-md navbar-dark bg-dark" role="navigation">
				<div class="container">
					<!-- Brand and toggle get grouped for better mobile display -->
					<button class="navbar-toggler" type="button" data-toggle="collapse"
						data-target="#bs-example-navbar-collapse-1" aria-controls="bs-example-navbar-collapse-1"
						aria-expanded="false" aria-label="Toggle navigation">
						<span class="navbar-toggler-icon"></span>
					</button>
					<a class="navbar-brand" href="<?php echo home_url(); ?>"><?php bloginfo( 'name' ); ?></a>
					<?php
        wp_nav_menu( array(
            'theme_location'    => 'myawesomemenu',
			'container'         => 'div',
			'depth'				=> 1,
            'container_class'   => 'collapse navbar-collapse',
            'container_id'      => 'bs-example-navbar-collapse-1',
            'menu_class'        => 'nav navbar-nav',
            'fallback_cb'       => 'WP_Bootstrap_Navwalker::fallback',
            'walker'            => new WP_Bootstrap_Navwalker(),
		) );
			if ( is_active_sidebar( 'bs-example-navbar-collapse-1' ) ) : ?>
			<div id="header-widget-area" class="chw-widget-area widget-area" role="complementary">
			<?php dynamic_sidebar( 'bs-example-navbar-collapse-1' ); ?>
			</div>
			 
			<?php endif; ?>
				</div>
			</nav>
		</header><!-- #masthead -->

		<div id="content" class="site-content">