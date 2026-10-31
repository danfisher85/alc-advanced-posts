<?php
/**
 * Widget: Social Buttons
 *
 * Stacked "follow us" buttons (Facebook, X / Twitter, Instagram, YouTube,
 * Twitch) with a title and a short text. URLs default to Theme Options >
 * Social when left empty.
 *
 * @author    Dan Fisher
 * @package   Alchemists Advanced Posts
 * @since     2.7.0
 * @version   2.7.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Alchemists_Widget_Social_Buttons extends WP_Widget {

	/**
	 * Constructor.
	 */
	function __construct() {

		$widget_ops = array(
			'classname'   => 'widget-social',
			'description' => esc_html__( 'Stacked "follow us" buttons for your social profiles.', 'alc-advanced-posts' ),
		);
		$control_ops = array(
			'id_base' => 'social-buttons-widget',
		);

		parent::__construct( 'social-buttons-widget', 'ALC - Social Buttons', $widget_ops, $control_ops );
	}

	/**
	 * Supported networks: icon, theme modifier class, Theme Options URL key and default texts.
	 */
	public static function networks() {
		return apply_filters( 'alc_social_buttons_networks', array(
			'facebook'  => array(
				'label'  => 'Facebook',
				'icon'   => 'fab fa-facebook',
				'mod'    => 'fb',
				'option' => 'alchemists__opt-social-fb-url',
				'title'  => esc_html__( 'Like Our Facebook Page', 'alc-advanced-posts' ),
				'text'   => esc_html__( 'Join our group', 'alc-advanced-posts' ),
			),
			'twitter'   => array(
				'label'  => 'X / Twitter',
				'icon'   => 'fab fa-x-twitter',
				'mod'    => 'twitter',
				'option' => 'alchemists__opt-social-tw-url',
				'title'  => esc_html__( 'Follow Us on X / Twitter', 'alc-advanced-posts' ),
				'text'   => esc_html__( 'Get the latest news', 'alc-advanced-posts' ),
			),
			'instagram' => array(
				'label'  => 'Instagram',
				'icon'   => 'fab fa-instagram',
				'mod'    => 'instagram',
				'option' => 'alchemists__opt-social-insta-url',
				'title'  => esc_html__( 'Follow Our Instagram', 'alc-advanced-posts' ),
				'text'   => esc_html__( 'Get notified', 'alc-advanced-posts' ),
			),
			'youtube'   => array(
				'label'  => 'YouTube',
				'icon'   => 'fab fa-youtube',
				'mod'    => 'youtube',
				'option' => 'alchemists__opt-social-youtube-url',
				'title'  => esc_html__( 'Subscribe on YouTube', 'alc-advanced-posts' ),
				'text'   => esc_html__( 'Watch our videos', 'alc-advanced-posts' ),
			),
			'twitch'    => array(
				'label'  => 'Twitch',
				'icon'   => 'fab fa-twitch',
				'mod'    => 'twitch',
				'option' => 'alchemists__opt-social-twitch-url',
				'title'  => esc_html__( 'Follow Us on Twitch', 'alc-advanced-posts' ),
				'text'   => esc_html__( 'Watch our streams', 'alc-advanced-posts' ),
			),
		) );
	}

	/**
	 * Outputs the widget content.
	 */
	function widget( $args, $instance ) {

		$title           = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '' );
		$alchemists_data = get_option( 'alchemists_data' );
		$buttons         = array();

		foreach ( self::networks() as $key => $network ) {
			if ( empty( $instance[ $key . '_on' ] ) ) {
				continue;
			}
			$url = ! empty( $instance[ $key . '_url' ] ) ? $instance[ $key . '_url' ] : ( isset( $alchemists_data[ $network['option'] ] ) ? $alchemists_data[ $network['option'] ] : '' );
			if ( ! $url ) {
				continue;
			}
			$buttons[] = array(
				'url'   => $url,
				'icon'  => $network['icon'],
				'mod'   => $network['mod'],
				'title' => ! empty( $instance[ $key . '_title' ] ) ? $instance[ $key . '_title' ] : $network['title'],
				'text'  => isset( $instance[ $key . '_text' ] ) ? $instance[ $key . '_text' ] : $network['text'],
			);
		}

		if ( empty( $buttons ) ) {
			return;
		}

		echo wp_kses_post( $args['before_widget'] );

		if ( $title ) {
			echo wp_kses_post( $args['before_title'] ) . esc_html( $title ) . wp_kses_post( $args['after_title'] );
		}

		foreach ( $buttons as $button ) {
			?>
			<a href="<?php echo esc_url( $button['url'] ); ?>" class="btn-social-counter btn-social-counter--<?php echo esc_attr( $button['mod'] ); ?>" target="_blank" rel="noopener">
				<div class="btn-social-counter__icon"><i class="<?php echo esc_attr( $button['icon'] ); ?>"></i></div>
				<h6 class="btn-social-counter__title"><?php echo esc_html( $button['title'] ); ?></h6>
				<?php if ( '' !== $button['text'] ) : ?>
					<span class="btn-social-counter__count"><?php echo esc_html( $button['text'] ); ?></span>
				<?php endif; ?>
				<span class="btn-social-counter__add-icon"></span>
			</a>
			<?php
		}

		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Updates a particular instance of a widget.
	 */
	function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );

		foreach ( self::networks() as $key => $network ) {
			$instance[ $key . '_on' ]    = ! empty( $new_instance[ $key . '_on' ] ) ? 1 : 0;
			$instance[ $key . '_url' ]   = isset( $new_instance[ $key . '_url' ] ) ? esc_url_raw( $new_instance[ $key . '_url' ] ) : '';
			$instance[ $key . '_title' ] = isset( $new_instance[ $key . '_title' ] ) ? strip_tags( $new_instance[ $key . '_title' ] ) : '';
			$instance[ $key . '_text' ]  = isset( $new_instance[ $key . '_text' ] ) ? strip_tags( $new_instance[ $key . '_text' ] ) : '';
		}

		return $instance;
	}

	/**
	 * Outputs the settings update form.
	 */
	function form( $instance ) {
		$defaults = array( 'title' => '' );
		foreach ( self::networks() as $key => $network ) {
			$defaults[ $key . '_on' ]    = in_array( $key, array( 'facebook', 'twitter', 'instagram' ), true ) ? 1 : 0;
			$defaults[ $key . '_url' ]   = '';
			$defaults[ $key . '_title' ] = $network['title'];
			$defaults[ $key . '_text' ]  = $network['text'];
		}
		$instance        = wp_parse_args( (array) $instance, $defaults );
		$alchemists_data = get_option( 'alchemists_data' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title (optional):', 'alc-advanced-posts' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<?php foreach ( self::networks() as $key => $network ) :
			$option_url = isset( $alchemists_data[ $network['option'] ] ) ? $alchemists_data[ $network['option'] ] : '';
			?>
			<p style="margin-bottom: 4px;">
				<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id( $key . '_on' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key . '_on' ) ); ?>" value="1" <?php checked( $instance[ $key . '_on' ], 1 ); ?> />
				<label for="<?php echo esc_attr( $this->get_field_id( $key . '_on' ) ); ?>"><strong><?php echo esc_html( $network['label'] ); ?></strong></label>
			</p>
			<p style="margin: 0 0 4px 18px;">
				<?php // A text field: a URL field blocks saving the widget on "#" or an address without https:// (WordPress checks the form silently) ?>
				<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( $key . '_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key . '_url' ) ); ?>" value="<?php echo esc_attr( $instance[ $key . '_url' ] ); ?>" placeholder="<?php echo esc_attr( $option_url ? $option_url : esc_html__( 'URL (empty = Theme Options > Social)', 'alc-advanced-posts' ) ); ?>" />
			</p>
			<p style="margin: 0 0 4px 18px;">
				<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( $key . '_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key . '_title' ) ); ?>" value="<?php echo esc_attr( $instance[ $key . '_title' ] ); ?>" placeholder="<?php esc_attr_e( 'Title', 'alc-advanced-posts' ); ?>" />
			</p>
			<p style="margin: 0 0 12px 18px;">
				<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( $key . '_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key . '_text' ) ); ?>" value="<?php echo esc_attr( $instance[ $key . '_text' ] ); ?>" placeholder="<?php esc_attr_e( 'Text', 'alc-advanced-posts' ); ?>" />
			</p>
		<?php endforeach; ?>
		<?php
	}
}
