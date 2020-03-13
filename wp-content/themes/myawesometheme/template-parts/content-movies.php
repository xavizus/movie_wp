<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package myAwesomeTheme
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<?php
        /*
		if ( is_singular() ) :
			the_title( '<h1 class="entry-title">', '</h1>' );
		else :
			the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
        endif;
        */
		?>
	</header><!-- .entry-header -->

	<?php #myawesometheme_post_thumbnail(); ?>

	<div class="entry-content">

    <div class="card" style="width: 18rem;">
        <?=the_post_thumbnail('post-thumbnail', ['class' => 'card-img-top'])?>
        <div class="card-body">
        <h5 class="card-title"><?= the_title()?></h5>
        <p class="card-text"><?=wp_strip_all_tags(get_the_content())?></p>
            <a href="<?= esc_url( get_permalink())?>" class="btn btn-primary">See more</a>
        </div>
        </div>
	</div><!-- .entry-content -->

	<footer class="entry-footer">
		<?php myawesometheme_entry_footer(); ?>
	</footer><!-- .entry-footer -->
</article><!-- #post-<?php the_ID(); ?> -->
