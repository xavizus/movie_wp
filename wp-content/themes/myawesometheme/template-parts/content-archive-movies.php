<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package myAwesomeTheme
 */

?>
<?php
$metaDataFields = array(
    "_movies_imdb",
    "_movies_released",
    "_movies_actors",
    "_movies_poster"
);

foreach($metaDataFields as $field) {
    $key = $field;
    $$key = get_post_meta(get_the_ID(), $field, true);
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="entry-content">

    <div class="card" style="width: 18rem;">
        <div class="item">
            <?=the_post_thumbnail('post-thumbnail', ['class' => 'card-img-top'])?>
            <?php if($_movies_imdb): ?>
                <span class="notify-badge">Info added from IMDB-ID</span>
            <?php endif; ?>
        </div>
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
