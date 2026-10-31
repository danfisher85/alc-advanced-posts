<?php
/**
 * User profile fields used by the Post Author widget:
 * social network URLs (contact methods) and a wide author image.
 *
 * @author    Dan Fisher
 * @package   Alchemists Advanced Posts
 * @since     2.7.0
 * @version   2.7.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Social networks an author can link from the profile.
 * Keys double as user meta keys (contact methods).
 */
if ( ! function_exists( 'alc_author_social_networks' ) ) {
	function alc_author_social_networks() {
		return apply_filters( 'alc_author_social_networks', array(
			'facebook'  => array( 'label' => 'Facebook',    'icon' => 'fab fa-facebook' ),
			'twitter'   => array( 'label' => 'X / Twitter', 'icon' => 'fab fa-x-twitter' ),
			'instagram' => array( 'label' => 'Instagram',   'icon' => 'fab fa-instagram' ),
			'linkedin'  => array( 'label' => 'LinkedIn',    'icon' => 'fab fa-linkedin' ),
			'youtube'   => array( 'label' => 'YouTube',     'icon' => 'fab fa-youtube' ),
		) );
	}
}

/**
 * Register the social URLs as user contact methods (Users > Profile).
 */
function alc_author_contact_methods( $methods ) {
	foreach ( alc_author_social_networks() as $key => $network ) {
		if ( ! isset( $methods[ $key ] ) ) {
			/* translators: %s: social network name */
			$methods[ $key ] = sprintf( esc_html__( '%s URL', 'alc-advanced-posts' ), $network['label'] );
		}
	}
	return $methods;
}
add_filter( 'user_contactmethods', 'alc_author_contact_methods' );

/**
 * Author Widget Image field (Users > Profile): media library picker.
 */
function alc_author_image_field( $user ) {
	$image = get_the_author_meta( 'alchemists_author_image', $user->ID );
	?>
	<h2><?php esc_html_e( 'Alchemists', 'alc-advanced-posts' ); ?></h2>
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="alchemists_author_image"><?php esc_html_e( 'Author Widget Image', 'alc-advanced-posts' ); ?></label></th>
			<td>
				<div class="alc-author-image-field">
					<div class="alc-author-image-field__preview" style="margin-bottom: 8px;<?php echo $image ? '' : ' display: none;'; ?>">
						<img src="<?php echo esc_url( $image ); ?>" alt="" style="display: block; max-width: 332px; height: auto; border: 1px solid #c3c4c7;" />
					</div>
					<input type="hidden" name="alchemists_author_image" id="alchemists_author_image" value="<?php echo esc_attr( $image ); ?>" />
					<button type="button" class="button alc-author-image-field__select"><?php esc_html_e( 'Select image', 'alc-advanced-posts' ); ?></button>
					<button type="button" class="button alc-author-image-field__remove"<?php echo $image ? '' : ' style="display: none;"'; ?>><?php esc_html_e( 'Remove', 'alc-advanced-posts' ); ?></button>
					<p class="description"><?php esc_html_e( 'Shown by the "ALC - Post Author" widget instead of the avatar. A wide image (about 332 x 120 px) works best. Leave empty to use the avatar.', 'alc-advanced-posts' ); ?></p>
				</div>
			</td>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile', 'alc_author_image_field' );
add_action( 'edit_user_profile', 'alc_author_image_field' );

function alc_author_image_field_save( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}
	if ( isset( $_POST['alchemists_author_image'] ) ) {
		update_user_meta( $user_id, 'alchemists_author_image', esc_url_raw( wp_unslash( $_POST['alchemists_author_image'] ) ) );
	}
}
add_action( 'personal_options_update', 'alc_author_image_field_save' );
add_action( 'edit_user_profile_update', 'alc_author_image_field_save' );

/**
 * Media picker script on the profile screens.
 */
function alc_author_image_field_assets( $hook ) {
	if ( ! in_array( $hook, array( 'profile.php', 'user-edit.php' ), true ) ) {
		return;
	}
	wp_enqueue_media();
	$js = "jQuery(function($){var frame;"
		. "$(document).on('click','.alc-author-image-field__select',function(e){e.preventDefault();var f=$(this).closest('.alc-author-image-field');"
		. "if(!frame){frame=wp.media({title:" . wp_json_encode( __( 'Author Widget Image', 'alc-advanced-posts' ) ) . ",button:{text:" . wp_json_encode( __( 'Use this image', 'alc-advanced-posts' ) ) . "},library:{type:'image'},multiple:false});"
		. "frame.on('select',function(){var a=frame.state().get('selection').first().toJSON();var url=(a.sizes&&a.sizes.full)?a.sizes.full.url:a.url;"
		. "f.find('input[type=hidden]').val(url);f.find('.alc-author-image-field__preview').show().find('img').attr('src',url);f.find('.alc-author-image-field__remove').show();});}"
		. "frame.open();});"
		. "$(document).on('click','.alc-author-image-field__remove',function(e){e.preventDefault();var f=$(this).closest('.alc-author-image-field');"
		. "f.find('input[type=hidden]').val('');f.find('.alc-author-image-field__preview').hide();$(this).hide();});});";
	wp_add_inline_script( 'media-editor', $js );
}
add_action( 'admin_enqueue_scripts', 'alc_author_image_field_assets' );
