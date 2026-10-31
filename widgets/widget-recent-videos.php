<?php
/**
 * Recent Videos
 *
 * @author    Dan Fisher
 * @package   Alchemists Advanced Posts
 * @since     2.0.0
 * @version   2.7.1
 */


// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}


/**
 * Widget class.
 */

class Alchemists_Widget_Recent_Videos extends WP_Widget {


	/**
	 * Constructor.
	 *
	 * @access public
	 */
	function __construct() {

		$widget_ops = array(
			'classname' => 'recent-videos',
			'description' => esc_html__( 'Display your videos.', 'alc-advanced-posts' ),
		);
		$control_ops = array(
			'id_base' => 'recent-videos-widget'
		);

		parent::__construct( 'recent-videos-widget', 'ALC - Recent Videos', $widget_ops, $control_ops );

	}


	/**
	 * Outputs the widget content.
	 */

	function widget( $args, $instance ) {

		extract( $args );

		$title        = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '' );
		$number       = isset( $instance['number'] ) ? $instance['number'] : 4;
		$type         = isset( $instance['type'] ) ? $instance['type'] : 'post-format';
		$orderby      = isset( $instance['orderby'] ) ? $instance['orderby'] : 'date';
		$popularity   = isset( $instance['popularity'] ) ? $instance['popularity'] : 'likes';
		$layout_style = isset( $instance['layout_style'] ) ? $instance['layout_style'] : 'grid';
		$button_text  = isset( $instance['button_text'] ) ? $instance['button_text'] : '';
		$button_url   = isset( $instance['button_url'] ) ? $instance['button_url'] : '';
		$excerpt_size = isset( $instance['excerpt_size'] ) ? (int) $instance['excerpt_size'] : 20;

		// Optional button in the widget header (after the title)
		if ( $button_text && $button_url ) {
			$after_title = str_replace( '</h4>', '</h4><a href="' . esc_url( $button_url ) . '" class="btn btn-xs btn-default btn-icon card-header__button">' . esc_html( $button_text ) . '</a>', $after_title );
		}

		// The Featured layout is a regular card (the recent-videos class strips the card look)
		if ( $layout_style == 'featured' ) {
			$before_widget = str_replace( 'recent-videos', 'widget-featured-video', $before_widget );
		} elseif ( $layout_style == 'list-skewed' ) {
			$before_widget = str_replace( 'recent-videos', 'widget-popular-videos', $before_widget );
		}

		echo wp_kses_post( $before_widget );

		if ( $title ) {
			echo wp_kses_post( $before_title ) . esc_html( $title ) . wp_kses_post( $after_title );
		}

		$alchemists_data    = get_option('alchemists_data');
		$post_likes         = isset( $alchemists_data['alchemists__blog-post-likes'] ) ? $alchemists_data['alchemists__blog-post-likes'] : true;
		$post_views         = isset( $alchemists_data['alchemists__blog-post-views'] ) ? $alchemists_data['alchemists__blog-post-views'] : true;
		$post_comments      = isset( $alchemists_data['alchemists__blog-post-comments'] ) ? $alchemists_data['alchemists__blog-post-comments'] : true;

		$args = array(
			'posts_per_page'      => $number,
			'no_found_rows'       => true,
			'orderby'             => $orderby,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
		);

		if ( $type == 'custom-post-type-video' ) {
			$args['post_type'] = 'videos';
		} else {
			$args['post_type'] = 'post';
			$args['tax_query'][] = array(
				'taxonomy' => 'post_format',
				'field' => 'slug',
				'terms' => 'post-format-video'
			);
		}

		$alchemists_data = get_option('alchemists_data');
		$categories_toggle = isset( $alchemists_data['alchemists__posts-categories'] ) ? $alchemists_data['alchemists__posts-categories'] : 1;

		// Post list class
		$posts_list_classes = array(
			'posts',
			'posts--card-compact',
			'row'
		);
		$post_thumb_size = 'alchemists_thumbnail-alt';

		// Post classes
		$post_classes = array(
			'posts__item',
			'card'
		);

		// Featured Image classes
		$thumb_classes = array(
			'posts__thumb',
			'posts__thumb--video'
		);

		// Category taxonomy
		$cat_taxonomy = ( $type == 'custom-post-type-video' ) ? 'catvideos' : 'category';

		// Featured layout: one post per row, no card, 16:9 thumb with overlay
		if ( $layout_style == 'featured' ) {
			$posts_list_classes = array( 'posts', 'posts--video-featured' );
			$post_thumb_size    = 'alchemists_thumbnail-alt';
			$post_classes       = array( 'posts__item' );
			array_push( $thumb_classes, 'posts__thumb--video-md', 'posts__thumb--video-has-overlay', 'posts__thumb--has-ratio', 'posts__thumb--ratio-16-9' );
		}

		// List with skewed thumbnails (Hockey)
		if ( $layout_style == 'list-skewed' ) {
			$posts_list_classes = array( 'posts', 'posts--list-skewed' );
			$post_thumb_size    = 'alchemists_thumbnail-xs-tall';
			$post_classes       = array( 'posts__item' );
		}

		$thumb_classes = implode( ' ', $thumb_classes );

		// Start the Loop
		$wp_query = new WP_Query( $args );
		if ( $wp_query->have_posts() ) : ?>

		<div class="<?php echo esc_attr( implode( ' ', $posts_list_classes ) ); ?>">
			<?php
			while ($wp_query->have_posts()) : $wp_query->the_post();
				// get post category class
				$post_class = alchemists_post_category_class();
				$post_classes[] = $post_class;

				if ( $layout_style == 'featured' ) {
					include ALCADVPOSTS_PLUGIN_DIR . '/widgets/widget-recent-videos/post-layout-video-featured.php';
				} elseif ( $layout_style == 'list-skewed' ) {
					include ALCADVPOSTS_PLUGIN_DIR . '/widgets/widget-recent-videos/post-layout-video-list-skewed.php';
				} else {
					include ALCADVPOSTS_PLUGIN_DIR . '/widgets/widget-recent-videos/post-layout-video.php';
				}

			endwhile;
			wp_reset_postdata();
			?>
		</div>

		<?php endif; ?>

		<?php echo wp_kses_post( $after_widget );
	}

	/**
	 * Updates a particular instance of a widget.
	 */

	function update($new_instance, $old_instance) {

		$instance = $old_instance;

		$instance['title']        = strip_tags( $new_instance['title'] );
		$instance['number']       = $new_instance['number'];
		$instance['orderby']      = $new_instance['orderby'];
		$instance['type']         = $new_instance['type'];
		$instance['popularity']   = $new_instance['popularity'];
		$instance['layout_style'] = isset( $new_instance['layout_style'] ) ? $new_instance['layout_style'] : 'grid';
		$instance['button_text']  = isset( $new_instance['button_text'] ) ? strip_tags( $new_instance['button_text'] ) : '';
		$instance['button_url']   = isset( $new_instance['button_url'] ) ? esc_url_raw( $new_instance['button_url'] ) : '';
		$instance['excerpt_size'] = isset( $new_instance['excerpt_size'] ) ? (int) $new_instance['excerpt_size'] : 20;

		return $instance;
	}


	/**
	 * Outputs the settings update form.
	 */

	function form( $instance ) {

		$defaults = array(
			'title'        => esc_html__( 'Recent Videos', 'alc-advanced-posts' ),
			'number'       => 4,
			'orderby'      => 'date',
			'type'         => 'post-format',
			'popularity'   => 'likes',
			'layout_style' => 'grid',
			'button_text'  => '',
			'button_url'   => '',
			'excerpt_size' => 20,
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'alc-advanced-posts' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_html_e( 'Number of items to show:', 'alc-advanced-posts' ); ?></label>
			<input class="tiny-text" type="number" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" step="1" min="1" size="3" value="<?php echo esc_attr( $instance['number'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>"><?php esc_html_e( 'Source:', 'alc-advanced-posts' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'type' ) ); ?>" class="widefat" style="width:100%;">
				<option value="post-format" <?php echo ( 'post-format' == $instance['type'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Video Post Format', 'alc-advanced-posts' ); ?></option>
				<option value="custom-post-type-video" <?php echo ( 'custom-post-type-video' == $instance['type'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Video Custom Post Type', 'alc-advanced-posts' ); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>"><?php esc_html_e( 'Order by:', 'alc-advanced-posts' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>" class="widefat" style="width:100%;">
				<option value="date" <?php echo ( 'date' == $instance['orderby'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Date', 'alc-advanced-posts' ); ?></option>
				<option value="meta_value_num" <?php echo ( 'meta_value_num' == $instance['orderby'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Popularity', 'alc-advanced-posts' ); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'popularity' ) ); ?>"><?php esc_html_e( 'Popularity:', 'alc-advanced-posts' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'popularity' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'popularity' ) ); ?>" class="widefat" style="width:100%;">
				<option value="likes" <?php echo ( 'likes' == $instance['popularity'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Likes', 'alc-advanced-posts' ); ?></option>
				<option value="views" <?php echo ( 'views' == $instance['popularity'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Views', 'alc-advanced-posts' ); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'layout_style' ) ); ?>"><?php esc_html_e( 'Layout:', 'alc-advanced-posts' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'layout_style' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'layout_style' ) ); ?>" class="widefat" style="width:100%;">
				<option value="grid" <?php echo ( 'grid' == $instance['layout_style'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Cards', 'alc-advanced-posts' ); ?></option>
				<option value="featured" <?php echo ( 'featured' == $instance['layout_style'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Featured - with excerpt (Hockey)', 'alc-advanced-posts' ); ?></option>
				<option value="list-skewed" <?php echo ( 'list-skewed' == $instance['layout_style'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'List - Skewed thumbnails 110x135 (Hockey)', 'alc-advanced-posts' ); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'excerpt_size' ) ); ?>"><?php esc_html_e( 'Excerpt length (words, Featured layout; 0 hides it):', 'alc-advanced-posts' ); ?></label>
			<input class="tiny-text" type="number" id="<?php echo esc_attr( $this->get_field_id( 'excerpt_size' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'excerpt_size' ) ); ?>" step="1" min="0" size="3" value="<?php echo esc_attr( $instance['excerpt_size'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'button_text' ) ); ?>"><?php esc_html_e( 'Header button text:', 'alc-advanced-posts' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'button_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'button_text' ) ); ?>" value="<?php echo esc_attr( $instance['button_text'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'button_url' ) ); ?>"><?php esc_html_e( 'Header button URL:', 'alc-advanced-posts' ); ?></label>
			<?php // A text field: a URL field blocks saving the widget on a relative address (WordPress checks the form silently) ?>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'button_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'button_url' ) ); ?>" value="<?php echo esc_attr( $instance['button_url'] ); ?>" />
		</p>

		<?php

	}
}
