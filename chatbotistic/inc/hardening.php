<?php
/**
 * Backend hardening — reduce the fingerprints a CMS/tech scanner reads,
 * stop user enumeration, and keep private/transactional pages out of
 * search indexes.
 *
 * Note: a theme cannot fully hide that a site runs WordPress — asset paths
 * such as /wp-content/ are in the page source and require server-level
 * rewrites to mask. This file removes every fingerprint the theme can
 * reach from PHP.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/**
 * Strip discovery + fingerprint links from <head>.
 */
function cb_strip_head_meta() {
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'rest_output_link_wp_head' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	remove_action( 'template_redirect', 'rest_output_link_header', 11 );
}
add_action( 'init', 'cb_strip_head_meta' );

/**
 * Drop the X-Pingback and WordPress version-ish response headers.
 *
 * @param array $headers HTTP headers.
 * @return array
 */
function cb_clean_headers( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
}
add_filter( 'wp_headers', 'cb_clean_headers' );

/**
 * Remove the ?ver= query string from theme/plugin assets so the exact
 * WordPress and plugin versions are not advertised in markup.
 *
 * @param string $src Asset URL.
 * @return string
 */
function cb_strip_asset_version( $src ) {
	if ( $src && false !== strpos( $src, 'ver=' ) ) {
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
}
add_filter( 'style_loader_src', 'cb_strip_asset_version', 9999 );
add_filter( 'script_loader_src', 'cb_strip_asset_version', 9999 );

/**
 * Block author enumeration via ?author=N and author archive URLs, which
 * otherwise leak usernames to scanners.
 */
function cb_block_author_enum() {
	if ( is_admin() ) {
		return;
	}
	$is_author_probe = isset( $_GET['author'] ) && '' !== $_GET['author']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $is_author_probe || is_author() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'cb_block_author_enum' );

/**
 * Never expose author archive permalinks.
 *
 * @return string
 */
function cb_kill_author_link() {
	return home_url( '/' );
}
add_filter( 'author_link', 'cb_kill_author_link' );

/**
 * Lock down default WordPress login/admin URLs for non-admin users.
 *
 * - Frontend users are routed to /login/.
 * - /wp-admin is blocked for non-admins (except admin-ajax).
 * - Optional admin backdoor URL: /login-w-hub?cb_admin_login=1
 */
function cb_protect_wp_login_surfaces() {
	$pagenow = isset( $GLOBALS['pagenow'] ) ? $GLOBALS['pagenow'] : '';

	// Block the wp-admin dashboard for non-admins, but never interfere with the
	// AJAX (admin-ajax.php) or form (admin-post.php) endpoints — both define
	// WP_ADMIN, so is_admin() is true there even for logged-out visitors, and
	// the theme's public forms (contact, demo, newsletter, support) post to
	// admin-post.php. Redirecting those would break every front-end form.
	if (
		is_admin()
		&& ! wp_doing_ajax()
		&& 'admin-post.php' !== $pagenow
		&& ! current_user_can( 'manage_options' )
	) {
		wp_safe_redirect( home_url( '/404/' ) );
		exit;
	}

	if ( 'wp-login.php' !== $pagenow ) {
		return;
	}

	// Authentication itself must always reach wp-login.php. The branded /login/
	// form POSTs credentials here, and the password-reset / logout / GDPR flows
	// arrive via email links. Only the bare login *form display* (a plain GET)
	// is hidden behind the branded /login/ page.
	$request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( (string) $_SERVER['REQUEST_METHOD'] ) : 'GET';
	if ( 'POST' === $request_method ) {
		return;
	}

	$action          = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : 'login'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$allowed_actions = array( 'logout', 'lostpassword', 'retrievepassword', 'resetpass', 'rp', 'postpass', 'confirmaction', 'register' );
	if ( in_array( $action, $allowed_actions, true ) ) {
		return;
	}

	$is_admin_gate = isset( $_GET['cb_admin_login'] ) && '1' === (string) $_GET['cb_admin_login']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$request_uri   = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '';
	$is_hub_path   = false !== strpos( $request_uri, '/login-w-hub' );

	if ( current_user_can( 'manage_options' ) || $is_admin_gate || $is_hub_path ) {
		return;
	}

	wp_safe_redirect( home_url( '/login/' ) );
	exit;
}
add_action( 'init', 'cb_protect_wp_login_surfaces', 1 );

/**
 * Keep private + transactional pages out of search engines.
 *
 * robots.txt only asks crawlers not to crawl; this emits a real
 * noindex directive so these URLs are never indexed.
 *
 * @param array $robots Robots directives.
 * @return array
 */
function cb_noindex_private( $robots ) {
	$noindex = is_page_template( 'page-account.php' ) || is_search() || is_404();

	if ( ! $noindex && is_page() ) {
		$slug = get_post() ? get_post()->post_name : '';
		if ( in_array( $slug, cb_noindex_slugs(), true ) ) {
			$noindex = true;
		}
		if ( function_exists( 'cb_is_memberistic_page' ) && cb_is_memberistic_page() ) {
			$noindex = true;
		}
	}

	if ( $noindex ) {
		$robots['noindex']  = true;
		$robots['nofollow'] = true;
		unset( $robots['max-image-preview'] );
	}
	return $robots;
}
add_filter( 'wp_robots', 'cb_noindex_private' );
