<?php
/**
 * Widget: Post Author
 *
 * Shows the author of the current post: image, name, bio and social links
 * taken from the user profile. Renders on single posts only.
 *
 * @author    Dan Fisher
 * @package   Alchemists Advanced Posts
 * @since     2.7.0
 * @version   2.7.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Alchemists_Widget_Post_Author extends WP_Widget {

	/**
	 * Constructor.
	 */
	function __construct() {

		$widget_ops = array(
			'classname'   => 'widget-post-author',
			'description' => esc_html__( 'Author of the current post: image, bio and social links from the user profile. Shown on single posts only.', 'alc-advanced-posts' ),
		);
		$control_ops = array(
			'id_base' => 'post-author-widget',
		);

		parent::__construct( 'post-author-widget', 'ALC - Post Author', $widget_ops, $control_ops );
	}

	/**
	 * Outputs the widget content.
	 */
	function widget( $args, $instance ) {

		$post_types = apply_filters( 'alc_post_author_widget_post_types', array( 'post', 'videos' ) );

		if ( ! is_singular( $post_types ) ) {
			return;
		}

		$author_id = (int) get_post_field( 'post_author', get_queried_object_id() );

		if ( ! $author_id ) {
			return;
		}

		$title       = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '' );
		$name        = get_the_author_meta( 'display_name', $author_id );
		$description = get_the_author_meta( 'description', $author_id );
		$image       = get_the_author_meta( 'alchemists_author_image', $author_id );
		$networks    = function_exists( 'alc_author_social_networks' ) ? alc_author_social_networks() : array();

		// Social links present in the profile
		$links = array();
		foreach ( $networks as $key => $network ) {
			$url = get_the_author_meta( $key, $author_id );
			if ( $url ) {
				$links[ $key ] = array_merge( $network, array( 'url' => $url ) );
			}
		}

		echo wp_kses_post( $args['before_widget'] );

		if ( $title ) {
			echo wp_kses_post( $args['before_title'] ) . esc_html( $title ) . wp_kses_post( $args['after_title'] );
		}
		?>
		<div class="author-info">
			<figure class="author-info__img">
				<?php if ( $image ) : ?>
					<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $name ); ?>">
				<?php else : ?>
					<?php echo get_avatar( $author_id, 400, '', $name ); ?>
				<?php endif; ?>
			</figure>
			<h5 class="author-info__name"><?php echo esc_html( $name ); ?></h5>
			<?php if ( $description ) : ?>
				<div class="author-info__desc"><?php echo wp_kses_post( $description ); ?></div>
			<?php endif; ?>
			<?php if ( ! empty( $links ) ) : ?>
				<div class="author-info__footer">
					<ul class="social-links social-links--btn social-links--btn-plus author-info__social-links">
						<?php foreach ( $links as $key => $link ) : ?>
							<li class="social-links__item">
								<a href="<?php echo esc_url( $link['url'] ); ?>" class="social-links__link social-links__link--<?php echo esc_attr( $key ); ?>" title="<?php echo esc_attr( $link['label'] ); ?>" target="_blank" rel="noopener">
									<i class="<?php echo esc_attr( $link['icon'] ); ?>"></i>
									<span class="social-links__icon"><i class="fas fa-plus"></i></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
		<?php
		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Updates a particular instance of a widget.
	 */
	function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	/**
	 * Outputs the settings update form.
	 */
	function form( $instance ) {
		$defaults = array(
			'title' => esc_html__( 'Article Author', 'alc-advanced-posts' ),
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'alc-advanced-posts' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p class="description"><?php esc_html_e( 'The image, bio and social links come from the author\'s profile (Users > Profile > "Author Widget Image" and the social URL fields).', 'alc-advanced-posts' ); ?></p>
		<?php
	}
}
