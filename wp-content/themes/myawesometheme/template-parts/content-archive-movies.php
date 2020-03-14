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
