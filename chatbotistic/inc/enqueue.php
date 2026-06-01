<?php
/**
 * Asset loading — one stylesheet system, one script.
 *
 * Every asset is versioned with filemtime(): the cache-bust string changes
 * the instant a file is saved, so browsers and CDN/page caches can never
 * serve a stale build. This is the fix for the v3 theme's frozen version bug.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/**
 * Per-file cache-busting version. Falls back to theme version if missing.
 *
 * @param string $rel Path relative to theme root, e.g. /assets/css/theme.css.
 * @return string
 */
function cb_asset_ver( $rel ) {
	$path = CB_DIR . $rel;
	return file_exists( $path ) ? (string) filemtime( $path ) : CB_VERSION;
}

/**
 * Enqueue frontend styles and scripts.
 */
function cb_enqueue_assets() {
	// Fonts — Sora (display), Manrope (body), JetBrains Mono (mono).
	wp_enqueue_style(
		'cb-fonts',
		'https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=Manrope:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap',
		array(),
		null
	);

	// Core design system.
	wp_enqueue_style( 'cb-theme', CB_URI . '/assets/css/theme.css', array( 'cb-fonts' ), cb_asset_ver( '/assets/css/theme.css' ) );

	// Portal layer — only on the account template.
	if ( is_page_template( 'page-account.php' ) ) {
		wp_enqueue_style( 'cb-portal', CB_URI . '/assets/css/portal.css', array( 'cb-theme' ), cb_asset_ver( '/assets/css/portal.css' ) );
	}

	// Single interactions script. Defer so it never blocks render.
	wp_enqueue_script( 'cb-theme', CB_URI . '/assets/js/theme.js', array(), cb_asset_ver( '/assets/js/theme.js' ), true );

	// Casual source-protection deterrent — not loaded for editors/admins.
	if ( apply_filters( 'cb_enable_no_inspect', true ) && ! current_user_can( 'edit_posts' ) ) {
		wp_enqueue_script( 'cb-no-inspect', CB_URI . '/assets/js/no-inspect.js', array(), cb_asset_ver( '/assets/js/no-inspect.js' ), true );
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'cb_enqueue_assets' );

/**
 * Defer the theme script for a faster first paint.
 *
 * @param string $tag    Script tag.
 * @param string $handle Script handle.
 * @return string
 */
function cb_defer_script( $tag, $handle ) {
	if ( 'cb-theme' === $handle && false === strpos( $tag, 'defer' ) ) {
		$tag = str_replace( ' src', ' defer src', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'cb_defer_script', 10, 2 );

/**
 * Preconnect to the Google Fonts origins.
 *
 * @param array  $hints Resource hints.
 * @param string $rel   Relation type.
 * @return array
 */
function cb_resource_hints( $hints, $rel ) {
	if ( 'preconnect' === $rel ) {
		$hints[] = array( 'href' => 'https://fonts.googleapis.com' );
		$hints[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $hints;
}
add_filter( 'wp_resource_hints', 'cb_resource_hints', 10, 2 );
