<?php
/**
 * Recent Posts - List with skewed thumbnails (Hockey)
 *
 * Thumbnail 110x135, category label + author avatar row, title,
 * author name + "time ago" row.
 *
 * @author    Dan Fisher
 * @package   Alchemists Advanced Posts
 * @since     2.7.0
 * @version   2.7.0
 */

$has_thumb = has_post_thumbnail() && $show_thumb;

if ( ! $has_thumb ) {
	$post_classes[] = 'posts__item--no-thumb';
}
?>

<div <?php post_class( $post_classes ); ?>>

	<?php if ( $has_thumb ) : ?>
	<figure class="posts__thumb">
		<a href="<?php the_permalink(); ?>">
			<?php the_post_thumbnail( $post_thumb_size, array( 'class' => '' ) ); ?>
		</a>
	</figure>
	<?php endif; ?>

	<div class="posts__inner">

		<ul class="post__meta post__meta--row post__meta--sm meta">
			<?php
			if ( $categories_toggle ) {
				alchemists_post_category_labels( 'meta__item meta__item--cats posts__cat', 'category', 'li' );
			}
			if ( $post_author ) :
				?>
				<li class="meta__item meta__item--author">
					<?php echo get_avatar( get_the_author_meta( 'ID' ), 24, '', get_the_author(), array( 'class' => 'meta__item-avatar' ) ); ?>
				</li>
			<?php endif; ?>
		</ul>

		<h6 class="posts__title" title="<?php the_title_attribute(); ?>"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h6>

		<ul class="post__meta post__meta--row post__meta--sm meta">
			<?php if ( $post_author ) : ?>
				<li class="meta__item meta__item--author"><?php the_author(); ?></li>
			<?php endif; ?>
			<li class="meta__item meta__item--date">
				<time datetime="<?php echo esc_attr( get_the_time( 'c' ) ); ?>"><?php printf( esc_html__( '%s ago', 'alc-advanced-posts' ), human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ); ?></time>
			</li>
		</ul>

	</div>

</div>
