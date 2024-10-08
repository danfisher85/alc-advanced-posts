<?php
/*
Plugin Name: Alchemists Advanced Posts
Plugin URI: https://themeforest.net/user/dan_fisher/portfolio
Description: This plugin adds social sharing, post views, likes, custom post types to Alchemists WP Theme.
Version: 2.2.0
Author: Dan Fisher
Author URI: https://themeforest.net/user/dan_fisher
Text Domain: alc-advanced-posts
License: GPLv2
*/

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/*
 * 1. PLUGIN GLOBAL VARIABLES
 */

// Plugin Paths
if (!defined('ALCADVPOSTS_THEME_DIR'))
		define('ALCADVPOSTS_THEME_DIR', get_stylesheet_directory());

if (!defined('ALCADVPOSTS_PLUGIN_NAME'))
		define('ALCADVPOSTS_PLUGIN_NAME', trim(dirname(plugin_basename(__FILE__)), '/'));

if (!defined('ALCADVPOSTS_PLUGIN_DIR'))
		define('ALCADVPOSTS_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . ALCADVPOSTS_PLUGIN_NAME);

if (!defined('ALCADVPOSTS_PLUGIN_URL'))
		define('ALCADVPOSTS_PLUGIN_URL', WP_PLUGIN_URL . '/' . ALCADVPOSTS_PLUGIN_NAME);

// Plugin Version
if (!defined('ALCADVPOSTS_VERSION_KEY'))
		define('ALCADVPOSTS_VERSION_KEY', 'alcsocial_version');

if (!defined('ALCADVPOSTS_VERSION_NUM'))
		define('ALCADVPOSTS_VERSION_NUM', '2.2.0');


/*
 * 2. INCLUDES
 */

$alchemists_data = get_option( 'alchemists_data' );
$post_views      = isset( $alchemists_data['alchemists__blog-post-views'] ) ? $alchemists_data['alchemists__blog-post-views'] : true;

if ( $post_views ) {
	// Post Views
	include ALCADVPOSTS_PLUGIN_DIR . '/post-views/post-views.php';
}

// Post Like System
include ALCADVPOSTS_PLUGIN_DIR . '/post-like-system/post-like.php';

// Custom Post Types
include ALCADVPOSTS_PLUGIN_DIR . '/custom-post-types/admin-permalinks-settings.php';
include ALCADVPOSTS_PLUGIN_DIR . '/custom-post-types/custom-post-types.php';

// Widgets
include ALCADVPOSTS_PLUGIN_DIR . '/widgets/widgets.php';



/*
 * 3. TRANSLATION
 */

add_action( 'plugins_loaded', 'alc_adv_posts_language_init' );
function alc_adv_posts_language_init() {
	 load_plugin_textdomain( 'alc-advanced-posts', false, ALCADVPOSTS_PLUGIN_URL . '/languages/' );
}



/*
 * 4. FUNCTIONS
 */

/**
 * Get number of Twitter followers
 */
if(!function_exists('alchemists_tweet_count')) {
	function alchemists_tweet_count($twitter_id, $consumer_key, $consumer_secret, $access_token, $access_token_secret ){
		$twitter_id          = $twitter_id;
		$consumer_key        = $consumer_key;
		$consumer_secret     = $consumer_secret;
		$access_token        = $access_token;
		$access_token_secret = $access_token_secret;

		if($twitter_id && $consumer_key && $consumer_secret && $access_token && $access_token_secret) {

			// some variables
			$consumerKey      = $consumer_key;
			$consumerSecret   = $consumer_secret;
			$token            = get_option('cfTwitterToken');

				// cache version does not exist or expired

				// getting new auth bearer only if we don't have one
				if(!$token) {
					// preparing credentials
					$credentials = $consumerKey . ':' . $consumerSecret;
					$toSend      = base64_encode($credentials);

					// http post arguments
					$args = array(
						'method'      => 'POST',
						'httpversion' => '1.1',
						'blocking' 		=> true,
						'headers' 		=> array(
							'Authorization' => 'Basic ' . $toSend,
							'Content-Type'  => 'application/x-www-form-urlencoded;charset=UTF-8'
						),
						'body' => array( 'grant_type' => 'client_credentials' )
					);

					add_filter('https_ssl_verify', '__return_false');

					$response = wp_remote_post('https://api.twitter.com/oauth2/token', $args);
					$keys     = json_decode(wp_remote_retrieve_body($response));

					if( $keys ) {
						// saving token to wp_options table
						update_option('cfTwitterToken', $keys->access_token);
						$token 	= 	$keys->access_token;
					}
				}
				// we have bearer token wether we obtained it from API or from options
				$args = array(
					'httpversion' 	=> '1.1',
					'blocking' 		=> true,
					'headers' 		=> array(
						'Authorization' => "Bearer $token"
					)
				);

				add_filter('https_ssl_verify', '__return_false');
				$api_url  = "https://api.twitter.com/1.1/users/show.json?screen_name=$twitter_id";
				$response = wp_remote_get($api_url, $args);

				if (!is_wp_error($response)) {
					$followers         = json_decode(wp_remote_retrieve_body($response));
					$numberOfFollowers = $followers->followers_count;

				} else {
					// get old value and break
					$numberOfFollowers = get_option('cfNumberOfFollowers');

					// uncomment below to debug
					//die($response->get_error_message());
				}

				// cache for an hour

			return $numberOfFollowers;
		}
	}
}


// Social Share buttons with icons
function alc_post_social_share_buttons_small() {

	global $post;

	$url = urlencode( get_permalink( $post->ID ));
	$title = urlencode( get_the_title( $post->ID ));
	$thumbnail = wp_get_attachment_image_url( get_post_thumbnail_id( $post->ID ), 'alchemists_thumbnail-lg-alt' );

	$alchemists_data  = get_option('alchemists_data');
	$social_share     = array();

	$post_social      = isset( $alchemists_data['alchemists__opt-single-post-social'] ) ? esc_html( $alchemists_data['alchemists__opt-single-post-social'] ) : '';
	if ( isset( $alchemists_data['alchemists__opt-single-post-social-sorter']['enabled'] )) {
		$social_share = $alchemists_data['alchemists__opt-single-post-social-sorter']['enabled'];
	}

	if ( $post_social == 1 ) : ?>
	<ul class="social-links social-links--btn">

		<?php // Social Sharing

		if ( $social_share ): foreach ($social_share as $key=>$value) {
			switch($key) {

				case 'social_facebook': ?>

				<li class="social-links__item">
					<a target="_blank" onClick="popup = window.open('https://www.facebook.com/share.php?u=<?php echo $url; ?>&title=<?php echo esc_html( $title ); ?>', 'PopupPage', 'height=450,width=500,scrollbars=yes,resizable=yes'); return false" href="#" class="social-links__link social-links__link--fb" rel="nofollow"><i class="fab fa-facebook"></i></a>
				</li>

				<?php break;

				case 'social_twitter': ?>

				<li class="social-links__item">
					<a target="_blank" onClick="popup = window.open('https://twitter.com/intent/tweet?text=<?php echo $title; ?>&amp;url=<?php echo $url; ?>', 'PopupPage', 'height=450,width=500,scrollbars=yes,resizable=yes'); return false" href="#" class="social-links__link social-links__link--twitter" rel="nofollow"><i class="fab fa-x-twitter"></i></a>
				</li>

				<?php break;

				case 'social_linkedin': ?>

				<li class="social-links__item">
					<a target="_blank" onClick="popup = window.open('https://linkedin.com/shareArticle?mini=true&amp;url=<?php echo $url; ?>&amp;title=<?php echo $title; ?>', 'PopupPage', 'height=450,width=500,scrollbars=yes,resizable=yes'); return false" href="#" class="social-links__link social-links__link--linkedin" rel="nofollow"><i class="fab fa-linkedin"></i></a>
				</li>

				<?php break;

				case 'social_vk': ?>

				<li class="social-links__item">
					<a target="_blank" onClick="popup = window.open('https://vk.com/share.php?url=<?php echo $url; ?>&amp;<?php echo $title; ?><?php echo $thumbnail; ?>', 'PopupPage', 'height=450,width=500,scrollbars=yes,resizable=yes'); return false" href="#" class="social-links__link social-links__link--vk" rel="nofollow"><i class="fab fa-vk"></i></a>
				</li>

				<?php break;

				case 'social_ok': ?>

				<li class="social-links__item">
					<a target="_blank" onClick="popup = window.open('https://connect.ok.ru/offer?url=<?php echo $url; ?>', 'PopupPage', 'height=450,width=500,scrollbars=yes,resizable=yes'); return false" href="#" class="social-links__link social-links__link--ok" rel="nofollow"><i class="fab fa-odnoklassniki"></i></a>
				</li>

				<?php break;

				case 'social_whatsapp': ?>
				
				<li class="social-links__item">
					<a target="_blank" href="https://api.whatsapp.com/send?text=<?php echo $url; ?>" class="social-links__link social-links__link--whatsapp" rel="nofollow"><i class="fab fa-whatsapp"></i></a>
				</li>

				<?php break;

				case 'social_viber': ?>
				
				<li class="social-links__item">
					<a target="_blank" href="viber://forward?text=<?php echo $url; ?>" class="social-links__link social-links__link--viber" rel="nofollow"><i class="fab fa-viber"></i></a>
				</li>

				<?php break;

				case 'social_telegram': ?>

				<li class="social-links__item">
					<a target="_blank" href="https://telegram.me/share/url?url=<?php echo $url; ?>&text=<?php echo $title; ?>" class="social-links__link social-links__link--telegram" rel="nofollow"><i class="fab fa-telegram-plane"></i></a>
				</li>

				<?php break;

			}
		}
		endif; ?>

	</ul>
	<?php endif;
}



// Social Share button with labels
function alc_post_social_share_buttons() {

	global $post;

	$url = urlencode( get_permalink( $post->ID ));
	$title = urlencode( get_the_title( $post->ID ));
	$thumbnail = wp_get_attachment_image_url( get_post_thumbnail_id( $post->ID ), 'alchemists_thumbnail-lg-alt' );

	$alchemists_data  = get_option('alchemists_data');
	$social_share     = array();

	$post_social      = isset( $alchemists_data['alchemists__opt-single-post-social'] ) ? esc_html( $alchemists_data['alchemists__opt-single-post-social'] ) : '';
	if ( isset( $alchemists_data['alchemists__opt-single-post-social-sorter']['enabled'] )) {
		$social_share = $alchemists_data['alchemists__opt-single-post-social-sorter']['enabled'];
	}

	if ( $post_social == 1 ) : ?>
	<div class="post-sharing">

		<?php // Social Sharing

		if ( $social_share ): foreach ($social_share as $key=>$value) {
			switch($key) {

				case 'social_facebook': ?>

				<a target="_blank" onClick="popup = window.open('https://www.facebook.com/sharer.php?u=<?php echo $url; ?>', 'PopupPage', 'height=450,width=500,scrollbars=yes,resizable=yes'); return false" href="#" class="btn btn-default btn-facebook btn-icon btn-block" rel="nofollow"><i class="fab fa-facebook"></i> <span class="post-sharing__label hidden-xs"><?php esc_html_e( 'Share on Facebook', 'alc-advanced-posts' ); ?></span></a>

				<?php break;

				case 'social_twitter': ?>

				<a target="_blank" onClick="popup = window.open('https://twitter.com/intent/tweet?text=<?php echo $title; ?>&amp;url=<?php echo $url; ?>', 'PopupPage', 'height=450,width=500,scrollbars=yes,resizable=yes'); return false" href="#" class="btn btn-default btn-twitter btn-icon btn-block" rel="nofollow"><i class="fab fa-x-twitter"></i> <span class="post-sharing__label hidden-xs"><?php esc_html_e( 'Share on Twitter', 'alc-advanced-posts' ); ?></span></a>

				<?php break;

				case 'social_linkedin': ?>

				<a target="_blank" onClick="popup = window.open('https://linkedin.com/shareArticle?mini=true&amp;url=<?php echo $url; ?>&amp;title=<?php echo $title; ?>', 'PopupPage', 'height=450,width=500,scrollbars=yes,resizable=yes'); return false" href="#" class="btn btn-default btn-linkedin btn-icon btn-block" rel="nofollow"><i class="fab fa-linkedin"></i> <span class="post-sharing__label hidden-xs"><?php esc_html_e( 'Share on Linkedin', 'alc-advanced-posts' ); ?></span></a>

				<?php break;

				case 'social_vk': ?>

				<a target="_blank" onClick="popup = window.open('https://vk.com/share.php?url=<?php echo $url; ?>&amp;<?php echo $title; ?><?php echo $thumbnail; ?>', 'PopupPage', 'height=450,width=500,scrollbars=yes,resizable=yes'); return false" href="#" class="btn btn-default btn-vk btn-icon btn-block" rel="nofollow"><i class="fab fa-vk"></i> <span class="post-sharing__label hidden-xs"><?php esc_html_e( 'Share on VK', 'alc-advanced-posts' ); ?></span></a>

				<?php break;

				case 'social_ok': ?>

				<a target="_blank" onClick="popup = window.open('https://connect.ok.ru/offer?url=<?php echo $url; ?>', 'PopupPage', 'height=450,width=500,scrollbars=yes,resizable=yes'); return false" href="#" class="btn btn-default btn-odnoklassniki btn-icon btn-block" rel="nofollow"><i class="fab fa-odnoklassniki"></i> <span class="post-sharing__label hidden-xs"><?php esc_html_e( 'Share on OK', 'alc-advanced-posts' ); ?></span></a>

				<?php break;

				case 'social_whatsapp': ?>
				
				<a target="_blank" href="https://api.whatsapp.com/send?text=<?php echo $url; ?>" class="btn btn-default btn-whatsapp btn-icon btn-block" rel="nofollow"><i class="fab fa-whatsapp"></i> <span class="post-sharing__label hidden-xs"><?php esc_html_e( 'Share on WhatsApp', 'alc-advanced-posts' ); ?></span></a>

				<?php break;

				case 'social_viber': ?>
				
				<a target="_blank" href="viber://forward?text=<?php echo $url; ?>" class="btn btn-default btn-viber btn-icon btn-block" rel="nofollow"><i class="fab fa-viber"></i> <span class="post-sharing__label hidden-xs"><?php esc_html_e( 'Share on Viber', 'alc-advanced-posts' ); ?></span></a>

				<?php break;

				case 'social_telegram': ?>

				<a target="_blank" href="https://telegram.me/share/url?url=<?php echo $url; ?>&text=<?php echo $title; ?>" class="btn btn-default btn-telegram btn-icon btn-block" rel="nofollow"><i class="fab fa-telegram-plane"></i> <span class="post-sharing__label hidden-xs"><?php esc_html_e( 'Share on Telegram', 'alc-advanced-posts' ); ?></span></a>

				<?php break;

			}
		}
		endif; ?>

	</div>
	<?php endif;
}



// Social Share buttons with labels
function alc_post_social_share_buttons_labels() {

	global $post;

	$url = urlencode( get_permalink( $post->ID ));
	$title = urlencode( get_the_title( $post->ID ));
	$thumbnail = wp_get_attachment_image_url( get_post_thumbnail_id( $post->ID ), 'alchemists_thumbnail-lg-alt' );

	$alchemists_data  = get_option('alchemists_data');
	$social_share     = array();

	$post_social      = isset( $alchemists_data['alchemists__opt-single-post-social'] ) ? esc_html( $alchemists_data['alchemists__opt-single-post-social'] ) : '';
	if ( isset( $alchemists_data['alchemists__opt-single-post-social-sorter']['enabled'] )) {
		$social_share = $alchemists_data['alchemists__opt-single-post-social-sorter']['enabled'];
	}

	if ( $post_social == 1 ) : ?>
	<ul class="social-links social-links--btn social-links--btn-block">

		<?php // Social Sharing

		if ( $social_share ): foreach ($social_share as $key=>$value) {
			switch($key) {

				case 'social_facebook': ?>

				<li class="social-links__item">
					<a target="_blank" onClick="popup = window.open('https://www.facebook.com/share.php?u=<?php echo $url; ?>', 'PopupPage', 'height=450,width=500,scrollbars=yes,resizable=yes'); return false" href="#" class="social-links__link social-links__link--fb" rel="nofollow"><?php esc_html_e( 'Share on Facebook', 'alc-advanced-posts' ); ?></a>
				</li>

				<?php break;

				case 'social_twitter': ?>

				<li class="social-links__item">
					<a target="_blank" onClick="popup = window.open('https://twitter.com/intent/tweet?text=<?php echo $title; ?>&amp;url=<?php echo $url; ?>', 'PopupPage', 'height=450,width=500,scrollbars=yes,resizable=yes'); return false" href="#" class="social-links__link social-links__link--twitter" rel="nofollow"><?php esc_html_e( 'Share on Twitter', 'alc-advanced-posts' ); ?></a>
				</li>

				<?php break;

				case 'social_linkedin': ?>

				<li class="social-links__item">
					<a target="_blank" onClick="popup = window.open('https://linkedin.com/shareArticle?mini=true&amp;url=<?php echo $url; ?>&amp;title=<?php echo $title; ?>', 'PopupPage', 'height=450,width=500,scrollbars=yes,resizable=yes'); return false" href="#" class="social-links__link social-links__link--linkedin" rel="nofollow"><?php esc_html_e( 'Share on LinkedIn', 'alc-advanced-posts' ); ?></a>
				</li>

				<?php break;

				case 'social_vk': ?>

				<li class="social-links__item">
					<a target="_blank" onClick="popup = window.open('https://vk.com/share.php?url=<?php echo $url; ?>&amp;<?php echo $title; ?><?php echo $thumbnail; ?>', 'PopupPage', 'height=450,width=500,scrollbars=yes,resizable=yes'); return false" href="#" class="social-links__link social-links__link--vk" rel="nofollow"><?php esc_html_e( 'Share on VK', 'alc-advanced-posts' ); ?></a>
				</li>

				<?php break;

				case 'social_ok': ?>

				<li class="social-links__item">
					<a target="_blank" onClick="popup = window.open('https://connect.ok.ru/offer?url=<?php echo $url; ?>', 'PopupPage', 'height=450,width=500,scrollbars=yes,resizable=yes'); return false" href="#" class="social-links__link social-links__link--ok" rel="nofollow"><?php esc_html_e( 'Share on OK', 'alc-advanced-posts' ); ?></a>
				</li>

				<?php break;

				case 'social_whatsapp': ?>
				
				<li class="social-links__item">
					<a target="_blank" href="https://api.whatsapp.com/send?text=<?php echo $url; ?>" class="social-links__link social-links__link--whatsapp" rel="nofollow"><?php esc_html_e( 'Share on WhatsApp', 'alc-advanced-posts' ); ?></a>
				</li>

				<?php break;

				case 'social_viber': ?>
				
				<li class="social-links__item">
					<a target="_blank" href="viber://forward?text=<?php echo $url; ?>" class="social-links__link social-links__link--viber" rel="nofollow"><?php esc_html_e( 'Share on Viber', 'alc-advanced-posts' ); ?></a>
				</li>

				<?php break;

				case 'social_telegram': ?>

				<li class="social-links__item">
					<a target="_blank" href="https://telegram.me/share/url?url=<?php echo $url; ?>&text=<?php echo $title; ?>" class="social-links__link social-links__link--whatsapp" rel="nofollow"><?php esc_html_e( 'Share on Telegram', 'alc-advanced-posts' ); ?></a>
				</li>

				<?php break;

			}
		}
		endif; ?>

	</ul>
	<?php endif;
}



// Social Share buttons with icons
function alc_post_social_share_buttons_icons( $css_class = '' ) {

	global $post;

	$url = urlencode( get_permalink( $post->ID ));
	$title = urlencode( get_the_title( $post->ID ));
	$thumbnail = wp_get_attachment_image_url( get_post_thumbnail_id( $post->ID ), 'alchemists_thumbnail-lg-alt' );

	$alchemists_data  = get_option('alchemists_data');
	$social_share     = array();

	$post_social      = isset( $alchemists_data['alchemists__opt-single-post-social'] ) ? esc_html( $alchemists_data['alchemists__opt-single-post-social'] ) : '';
	if ( isset( $alchemists_data['alchemists__opt-single-post-social-sorter']['enabled'] )) {
		$social_share = $alchemists_data['alchemists__opt-single-post-social-sorter']['enabled'];
	}

	if ( $post_social == 1 ) : ?>
	<div class="post-sharing-compact <?php echo esc_attr( $css_class ); ?>">

		<?php // Social Sharing

		if ( $social_share ): foreach ($social_share as $key=>$value) {
			switch($key) {

				case 'social_facebook': ?>

				<a target="_blank" onClick="popup = window.open('https://www.facebook.com/share.php?u=<?php echo $url; ?>&title=<?php echo esc_html( $title ); ?>', 'PopupPage', 'height=450,width=500,scrollbars=yes,resizable=yes'); return false" href="#" class="btn btn-default btn-sm btn-facebook btn-icon" rel="nofollow"><i class="fab fa-facebook"></i></a>

				<?php break;

				case 'social_twitter': ?>

				<a target="_blank" onClick="popup = window.open('https://twitter.com/intent/tweet?text=<?php echo $title; ?>&amp;url=<?php echo $url; ?>', 'PopupPage', 'height=450,width=500,scrollbars=yes,resizable=yes'); return false" href="#" class="btn btn-default btn-sm btn-twitter btn-icon" rel="nofollow"><i class="fab fa-x-twitter"></i></a>

				<?php break;

				case 'social_linkedin': ?>

				<a target="_blank" onClick="popup = window.open('https://linkedin.com/shareArticle?mini=true&amp;url=<?php echo $url; ?>&amp;title=<?php echo $title; ?>', 'PopupPage', 'height=450,width=500,scrollbars=yes,resizable=yes'); return false" href="#" class="btn btn-default btn-sm btn-linkedin btn-icon" rel="nofollow"><i class="fab fa-linkedin"></i></a>

				<?php break;

				case 'social_vk': ?>

				<a target="_blank" onClick="popup = window.open('https://vk.com/share.php?url=<?php echo $url; ?>&amp;<?php echo $title; ?><?php echo $thumbnail; ?>', 'PopupPage', 'height=450,width=500,scrollbars=yes,resizable=yes'); return false" href="#" class="btn btn-default btn-sm btn-vk btn-icon" rel="nofollow"><i class="fab fa-vk"></i></a>

				<?php break;

				case 'social_ok': ?>

				<a target="_blank" onClick="popup = window.open('https://connect.ok.ru/offer?url=<?php echo $url; ?>', 'PopupPage', 'height=450,width=500,scrollbars=yes,resizable=yes'); return false" href="#" class="btn btn-default btn-sm btn-odnoklassniki btn-icon" rel="nofollow"><i class="fab fa-odnoklassniki"></i></a>

				<?php break;

				case 'social_whatsapp': ?>
				
				<a target="_blank" href="https://api.whatsapp.com/send?text=<?php echo $url; ?>" class="btn btn-default btn-sm btn-whatsapp btn-icon" rel="nofollow"><i class="fab fa-whatsapp"></i></a>

				<?php break;

				case 'social_viber': ?>
				
				<a target="_blank" href="viber://forward?text=<?php echo $url; ?>" class="btn btn-default btn-sm btn-viber btn-icon" rel="nofollow"><i class="fab fa-viber"></i></a>

				<?php break;

				case 'social_telegram': ?>

				<a target="_blank" href="https://telegram.me/share/url?url=<?php echo $url; ?>&text=<?php echo $title; ?>" class="btn btn-default btn-sm btn-telegram btn-icon" rel="nofollow"><i class="fab fa-telegram-plane"></i></a>

				<?php break;

			}
		}
		endif; ?>

	</div>
	<?php endif;
}


/**
 * Add Open Graph Meta Tags
 */

// Adding the Open Graph in the Language Attributes
function alchemists_add_opengraph_doctype( $output ) {
	return $output . ' xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml"';
}
add_filter( 'language_attributes', 'alchemists_add_opengraph_doctype' );


function alchemists_add_opengraph_meta() {
	
	// Add OG meta tags only on blog posts
	if ( is_singular( 'post' ) ) {

		// Enable Open Graph depends on Theme Options
		$alchemists_data = get_option( 'alchemists_data' );

		if ( isset( $alchemists_data['alchemists__blog-post-og'] ) && $alchemists_data['alchemists__blog-post-og'] == 1 ) {
			global $post;

			if ( $excerpt = $post->post_excerpt ) {
				$excerpt = strip_tags( $post->post_excerpt );
				$excerpt = str_replace( "", "'", $excerpt );
			} else {
				$excerpt = get_bloginfo( 'description' );
			}

			// Twitter Card
			echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
			echo '<meta name="twitter:title" content="' . esc_attr( get_the_title() ) . '" />' . "\n";
			if ( has_post_thumbnail( $post->ID ) ) {
				$img_src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'alchemists_thumbnail' );
				echo '<meta name="twitter:image" content="' . esc_attr( $img_src[0] ) . '"/>' . "\n";
			} else {
				$img_src = get_theme_file_uri( '/assets/images/placeholder-380x270.jpg' );
				echo '<meta name="twitter:image" content="' . esc_attr( $img_src ) . '"/>' . "\n";
			}

			// Open Graphs Meta tags
			echo '<meta property="og:title" content="' . esc_attr( get_the_title() ) . '"/>' . "\n";
			echo '<meta property="og:description" content="' . esc_attr( $excerpt ) . '"/>' . "\n";
			echo '<meta property="og:type" content="article"/>' . "\n";
			echo '<meta property="og:url" content="' . esc_attr( get_the_permalink() ) . '"/>' . "\n";
			echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo() ) . '"/>' . "\n";
			if ( has_post_thumbnail( $post->ID ) ) {
				$img_src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'alchemists_thumbnail' );
				echo '<meta property="og:image" content="' . esc_attr( $img_src[0] ) . '"/>' . "\n";
			} else {
				$img_src = get_theme_file_uri( '/assets/images/placeholder-380x270.jpg' );
				echo '<meta property="og:image" content="' . esc_attr( $img_src ) . '"/>' . "\n";
			}
		}
	}
}
add_action( 'wp_head', 'alchemists_add_opengraph_meta', 5 );
