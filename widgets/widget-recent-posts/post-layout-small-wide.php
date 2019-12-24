<?php
/**
 * Recent Posts - Small
 *
 * @author    Dan Fisher
 * @package   Alchemists Advanced Posts
 * @since     1.2.0
 * @version   2.0.0
 */

?>

<div <?php post_class( $post_classes ); ?>>

	<?php if ( has_post_thumbnail() && $show_thumb ) : ?>
	<figure class="<?php echo esc_attr( $thumb_classes ); ?>">
		<a href="<?php the_permalink(); ?>">
			<?php the_post_thumbnail( $post_thumb_size, array( 'class' => '' )); ?>
		</a>
	</figure>
	<?php endif; ?>

	<div class="posts__inner">

		<?php
		if ( $categories_toggle ) {
			alchemists_post_category_labels();
		}
		?>

		<h6 class="posts__title posts__title--color-hover" title="<?php the_title_attribute(); ?>"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h6>
		<time datetime="<?php esc_attr( the_time('c') ); ?>" class="posts__date">
			<?php the_time( get_option('date_format') ); ?>
		</time>

	</div>

</div>
