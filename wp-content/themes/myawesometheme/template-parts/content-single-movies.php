<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package myAwesomeTheme
 */
$metaData = array(
    "_movies_released" => 'Released',
    "_movies_actors" => 'Actors'
);

foreach ($metaData as $data) { 
    $key = $data;
    $$key = get_post_meta(get_the_ID(), $data,true);
}
?>




<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<?php
		if ( is_singular() ) :
			the_title( '<h1 class="entry-title">', '</h1>' );
		else :
			the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
		endif;
		?>
	</header><!-- .entry-header -->

	<?php myawesometheme_post_thumbnail(); ?>

	<div class="entry-content">
		<?php
		the_content( sprintf(
			wp_kses(
				/* translators: %s: Name of current post. Only visible to screen readers */
				__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'myawesometheme' ),
				array(
					'span' => array(
						'class' => array(),
					),
				)
			),
			get_the_title()
		) );
		?>
		<table>
			<table class="table">
			<?php
			foreach ($metaData as $arrayKey => $header) { 
				$value = get_post_meta(get_the_ID(), $arrayKey,true);
				echo "<tr><td>$header</td><td>$value</td></tr>";
			}
			?>
			</table>
	</div><!-- .entry-content -->

	<footer class="entry-footer">
		<?php myawesometheme_entry_footer(); ?>
	</footer><!-- .entry-footer -->
</article><!-- #post-<?php the_ID(); ?> -->
