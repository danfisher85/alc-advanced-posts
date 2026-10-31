<?php
/**
 * Recent Videos - Featured (Hockey)
 *
 * One post per row: 16:9 thumbnail with play button and overlay, post format
 * icon + category label + author avatar row, title, excerpt, author name +
 * "time ago" row.
 *
 * @author    Dan Fisher
 * @package   Alchemists Advanced Posts
 * @since     2.7.0
 * @version   2.7.0
 */

?>

<div <?php post_class( $post_classes ); ?>>

	<figure class="<?php echo esc_attr( $thumb_classes ); ?>">
		<a href="<?php the_permalink(); ?>">
			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( $post_thumb_size, array( 'class' => '' ) );
			} else {
				echo '<img src="' . esc_url( get_theme_file_uri( '/assets/images/placeholder-380x270.jpg' ) ) . '" alt="" />';
			}
			?>
		</a>
	</figure>

	<div class="posts__inner">

		<ul class="post__meta post__meta--row post__meta--sm meta">
			<li class="meta__item meta__item--post-format">
				<span class="meta__item-post-format-label meta__item-post-format-label--video"><i class="fas fa-play"></i></span>
			</li>
			<?php
			if ( $categories_toggle ) {
				alchemists_post_category_labels( 'meta__item meta__item--cats posts__cat', $cat_taxonomy, 'li' );
			}
			?>
			<li class="meta__item meta__item--author">
				<?php echo get_avatar( get_the_author_meta( 'ID' ), 24, '', get_the_author(), array( 'class' => 'meta__item-avatar' ) ); ?>
			</li>
		</ul>

		<h6 class="posts__title" title="<?php the_title_attribute(); ?>"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h6>

		<?php if ( $excerpt_size > 0 ) : ?>
			<div class="posts__excerpt">
				<?php echo alchemists_string_limit_words( get_the_excerpt(), $excerpt_size ); ?>
			</div>
		<?php endif; ?>

		<ul class="post__meta post__meta--row post__meta--sm meta">
			<li class="meta__item meta__item--author"><?php the_author(); ?></li>
			<li class="meta__item meta__item--date">
				<time datetime="<?php echo esc_attr( get_the_time( 'c' ) ); ?>"><?php printf( esc_html__( '%s ago', 'alc-advanced-posts' ), human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ); ?></time>
			</li>
		</ul>

	</div>

</div>
