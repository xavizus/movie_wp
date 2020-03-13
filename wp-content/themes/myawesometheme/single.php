<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package myAwesomeTheme
 */

get_header();
?>

	<div id="primary" class="content-area mt-5">
		<main id="main" class="site-main container">
		
		<div class="row">
		<?php
		while ( have_posts() ) :
			the_post();

			get_template_part( 'template-parts/content-single', get_post_type() );

			// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;

		endwhile; // End of the loop.
		?>
		</div>
		<div class="row">
		<?=get_footer() ?>
		</div>
		</main><!-- #main -->
	</div><!-- #primary -->

<?php
#get_sidebar();
