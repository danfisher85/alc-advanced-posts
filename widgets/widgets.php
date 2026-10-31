<?php
/**
 * Alchemists widgets
 *
 * @author    Dan Fisher
 * @package   Alchemists Advanced Posts
 * @since     1.1.0
 * @version   2.7.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Load the widget on widgets_init
function alc_init_widgets() {
	register_widget( 'Alchemists_Widget_Top_Posts' );
	register_widget( 'Alchemists_Widget_Recent_Comments' );
	register_widget( 'Alchemists_Widget_Contact_Info' );
	register_widget( 'Alchemists_Widget_Recent_Posts' );
	register_widget( 'Alchemists_Widget_Recent_Videos' );
	register_widget( 'Alchemists_Widget_Post_Author' );
	register_widget( 'Alchemists_Widget_Social_Buttons' );
}
add_action( 'widgets_init', 'alc_init_widgets' );

include_once ALCADVPOSTS_PLUGIN_DIR . '/widgets/widget-top-posts.php';
include_once ALCADVPOSTS_PLUGIN_DIR . '/widgets/widget-recent-comments.php';
include_once ALCADVPOSTS_PLUGIN_DIR . '/widgets/widget-contact-info.php';
include_once ALCADVPOSTS_PLUGIN_DIR . '/widgets/widget-recent-posts.php';
include_once ALCADVPOSTS_PLUGIN_DIR . '/widgets/widget-recent-videos.php';
include_once ALCADVPOSTS_PLUGIN_DIR . '/widgets/widget-post-author.php';
include_once ALCADVPOSTS_PLUGIN_DIR . '/widgets/widget-social-buttons.php';
include_once ALCADVPOSTS_PLUGIN_DIR . '/widgets/user-profile.php';
