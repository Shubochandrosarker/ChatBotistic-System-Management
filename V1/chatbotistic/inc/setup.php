<?php
/**
 * Theme setup — supports, menus, widget areas.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme supports and navigation menus.
 */
function cb_setup() {
	load_theme_textdomain( 'chatbotistic', CB_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'custom-logo', array(
		'height'      => 48,
		'width'       => 200,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets',
	) );

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'chatbotistic' ),
		'footer'  => __( 'Footer Menu', 'chatbotistic' ),
	) );

	set_post_thumbnail_size( 1200, 675, true );
}
add_action( 'after_setup_theme', 'cb_setup' );

/**
 * Content width for embeds.
 */
function cb_content_width() {
	$GLOBALS['content_width'] = 820;
}
add_action( 'after_setup_theme', 'cb_content_width', 0 );

/**
 * Dynamic body classes.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function cb_body_classes( $classes ) {
	$classes[] = 'cb-theme';
	if ( is_front_page() ) {
		$classes[] = 'cb-home';
	}
	if ( is_page_template( 'page-account.php' ) ) {
		$classes[] = 'cb-portal-page';
	}
	return $classes;
}
add_filter( 'body_class', 'cb_body_classes' );

/**
 * Trim excerpts and use a clean ellipsis.
 */
add_filter( 'excerpt_length', fn() => 26 );
add_filter( 'excerpt_more', fn() => '&hellip;' );

/**
 * Security + performance housekeeping.
 */
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'xmlrpc_enabled', '__return_false' );

function cb_clean_head() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'cb_clean_head' );

/**
 * Hide REST user enumeration for anonymous requests.
 *
 * @param array $endpoints REST endpoints.
 * @return array
 */
function cb_rest_user_enum( $endpoints ) {
	if ( is_user_logged_in() ) {
		return $endpoints;
	}
	unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	return $endpoints;
}
add_filter( 'rest_endpoints', 'cb_rest_user_enum' );
