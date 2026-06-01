<?php
/**
 * Branded XML sitemap + robots.txt.
 *
 * Replaces the core wp-sitemap with a proper sitemap *index* at
 * /sitemap.xml that links to type-specific sub-sitemaps —
 * /page-sitemap.xml, /post-sitemap.xml, /category-sitemap.xml — the same
 * structure search engines expect from established sites. An XSL stylesheet
 * renders all of them as branded pages in the browser.
 *
 * Also routes /llms.txt and /llms-full.txt (rendered by inc/llms.php).
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/** Use the branded sitemap, not the core one. */
add_filter( 'wp_sitemaps_enabled', '__return_false' );

/**
 * Register rewrite endpoints for the sitemap index, sub-sitemaps and llms files.
 */
function cb_register_endpoints() {
	add_rewrite_rule( '^sitemap\.xml$', 'index.php?cb_feed=sitemap', 'top' );
	add_rewrite_rule( '^page-sitemap\.xml$', 'index.php?cb_feed=sitemap-page', 'top' );
	add_rewrite_rule( '^post-sitemap\.xml$', 'index.php?cb_feed=sitemap-post', 'top' );
	add_rewrite_rule( '^category-sitemap\.xml$', 'index.php?cb_feed=sitemap-category', 'top' );
	add_rewrite_rule( '^llms\.txt$', 'index.php?cb_feed=llms', 'top' );
	add_rewrite_rule( '^llms-full\.txt$', 'index.php?cb_feed=llms-full', 'top' );
}
add_action( 'init', 'cb_register_endpoints' );

/**
 * Whitelist the custom query var.
 *
 * @param string[] $vars Query vars.
 * @return string[]
 */
function cb_query_vars( $vars ) {
	$vars[] = 'cb_feed';
	return $vars;
}
add_filter( 'query_vars', 'cb_query_vars' );

/**
 * Slugs kept out of sitemaps, indexes and llms files (portal, auth, transactional).
 *
 * @return string[]
 */
function cb_noindex_slugs() {
	return (array) apply_filters( 'cb_noindex_slugs', array(
		'account', 'login', 'register', 'memberistic-account', 'memberistic-checkout',
		'memberistic-login', 'memberistic-renewal', 'memberistic-thank-you',
		'memberistic-payment-failed', 'memberistic-staff-dashboard', 'memberistic-memberships',
	) );
}

/**
 * Sub-sitemap definitions: key => [ label, entries callback ].
 *
 * @return array<string,array{label:string,callback:callable}>
 */
function cb_sitemap_sections() {
	return array(
		'page'     => array( 'label' => __( 'Pages', 'chatbotistic' ),      'callback' => 'cb_sitemap_pages' ),
		'post'     => array( 'label' => __( 'Posts', 'chatbotistic' ),      'callback' => 'cb_sitemap_posts' ),
		'category' => array( 'label' => __( 'Categories', 'chatbotistic' ), 'callback' => 'cb_sitemap_terms' ),
	);
}

/**
 * Page entries (front page first, portal/auth pages excluded).
 *
 * @return array<int,array{url:string,modified:string}>
 */
function cb_sitemap_pages() {
	$entries = array( array( 'url' => home_url( '/' ), 'modified' => current_time( 'c' ) ) );
	$skip    = cb_noindex_slugs();
	$front   = (string) get_option( 'page_on_front' );

	foreach ( get_posts( array(
		'post_type'   => 'page',
		'post_status' => 'publish',
		'numberposts' => -1,
		'orderby'     => 'menu_order title',
		'order'       => 'ASC',
	) ) as $page ) {
		if ( in_array( $page->post_name, $skip, true ) || $front === (string) $page->ID ) {
			continue;
		}
		$entries[] = array(
			'url'      => get_permalink( $page ),
			'modified' => get_post_modified_time( 'c', true, $page ),
		);
	}
	return $entries;
}

/**
 * Post entries.
 *
 * @return array<int,array{url:string,modified:string}>
 */
function cb_sitemap_posts() {
	$entries = array();
	foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 1000 ) ) as $post ) {
		$entries[] = array(
			'url'      => get_permalink( $post ),
			'modified' => get_post_modified_time( 'c', true, $post ),
		);
	}
	return $entries;
}

/**
 * Category archive entries (non-empty only).
 *
 * @return array<int,array{url:string,modified:string}>
 */
function cb_sitemap_terms() {
	$entries = array();
	foreach ( get_categories( array( 'hide_empty' => true ) ) as $term ) {
		$link = get_term_link( $term );
		if ( ! is_wp_error( $link ) ) {
			$entries[] = array( 'url' => $link, 'modified' => current_time( 'c' ) );
		}
	}
	return $entries;
}

/**
 * Render whichever feed was requested.
 */
function cb_render_feed() {
	$feed = get_query_var( 'cb_feed' );
	if ( ! $feed ) {
		return;
	}

	if ( 'llms' === $feed || 'llms-full' === $feed ) {
		cb_render_llms( 'llms-full' === $feed );
		exit;
	}

	header( 'Content-Type: application/xml; charset=UTF-8' );
	header( 'X-Robots-Tag: noindex, follow', true );
	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	echo '<?xml-stylesheet type="text/xsl" href="' . esc_url( CB_URI . '/assets/sitemap.xsl' ) . '"?>' . "\n";

	if ( 'sitemap' === $feed ) {
		cb_render_sitemap_index();
	} elseif ( 0 === strpos( $feed, 'sitemap-' ) ) {
		$key      = substr( $feed, 8 );
		$sections = cb_sitemap_sections();
		$entries  = isset( $sections[ $key ] ) ? call_user_func( $sections[ $key ]['callback'] ) : array();
		cb_render_urlset( $entries );
	}
	exit;
}
add_action( 'template_redirect', 'cb_render_feed' );

/**
 * Output the <sitemapindex> linking each non-empty sub-sitemap.
 */
function cb_render_sitemap_index() {
	echo '<!-- Branded sitemap index for ' . esc_html( get_bloginfo( 'name' ) ) . ' — Chatbotistic theme -->' . "\n";
	echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
	foreach ( cb_sitemap_sections() as $key => $section ) {
		$entries = call_user_func( $section['callback'] );
		if ( ! $entries ) {
			continue;
		}
		$modified = '';
		foreach ( $entries as $entry ) {
			if ( $entry['modified'] > $modified ) {
				$modified = $entry['modified'];
			}
		}
		echo "\t<sitemap>\n";
		echo "\t\t<loc>" . esc_url( home_url( '/' . $key . '-sitemap.xml' ) ) . "</loc>\n";
		echo "\t\t<lastmod>" . esc_html( $modified ?: current_time( 'c' ) ) . "</lastmod>\n";
		echo "\t</sitemap>\n";
	}
	echo '</sitemapindex>';
}

/**
 * Output a <urlset> for one sub-sitemap.
 *
 * @param array<int,array{url:string,modified:string}> $entries Entries.
 */
function cb_render_urlset( $entries ) {
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
	foreach ( $entries as $entry ) {
		echo "\t<url>\n";
		echo "\t\t<loc>" . esc_url( $entry['url'] ) . "</loc>\n";
		echo "\t\t<lastmod>" . esc_html( $entry['modified'] ) . "</lastmod>\n";
		echo "\t\t<changefreq>weekly</changefreq>\n";
		echo "\t</url>\n";
	}
	echo '</urlset>';
}

/**
 * Comprehensive robots.txt — tuned for search engines and AI answer engines.
 *
 * @param string $output Default robots.txt body.
 * @param bool   $public Whether the site is public.
 * @return string
 */
function cb_robots_txt( $output, $public ) {
	if ( ! $public ) {
		return $output;
	}

	$home = untrailingslashit( home_url() );
	$L    = array();

	$L[] = '# robots.txt for ' . get_bloginfo( 'name' );
	$L[] = '# Tuned for search engines and AI answer engines (AEO).';
	$L[] = '';

	// Default policy for all crawlers.
	$L[] = 'User-agent: *';
	$L[] = 'Allow: /';
	$L[] = 'Disallow: /wp-admin/';
	$L[] = 'Allow: /wp-admin/admin-ajax.php';
	$L[] = 'Disallow: /wp-login.php';
	$L[] = 'Disallow: /wp-register.php';
	$L[] = 'Disallow: /xmlrpc.php';
	$L[] = 'Disallow: /wp-json/';
	$L[] = 'Disallow: /wp-includes/';
	$L[] = 'Disallow: /wp-content/plugins/';
	$L[] = 'Disallow: /wp-content/cache/';
	$L[] = 'Disallow: /cgi-bin/';
	$L[] = 'Disallow: /readme.html';
	$L[] = 'Disallow: /license.txt';
	$L[] = 'Disallow: /trackback/';
	$L[] = 'Disallow: /comments/feed/';
	// Transactional + private pages.
	foreach ( array_unique( array_merge( array( 'account' ), cb_noindex_slugs() ) ) as $slug ) {
		$L[] = 'Disallow: /' . $slug . '/';
	}
	// Low-value query strings (search, tracking, enumeration, cart).
	$L[] = 'Disallow: /*?s=';
	$L[] = 'Disallow: /*?author=';
	$L[] = 'Disallow: /*?replytocom';
	$L[] = 'Disallow: /*?memberistic_plan=';
	$L[] = 'Disallow: /*?memberistic_cycle=';
	$L[] = 'Disallow: /*?add-to-cart=';
	$L[] = 'Disallow: /*?*utm_';
	$L[] = '';

	// Explicitly welcome AI assistants — these power answer-engine visibility.
	$ai_bots = array(
		'GPTBot', 'OAI-SearchBot', 'ChatGPT-User', 'ClaudeBot', 'Claude-Web',
		'anthropic-ai', 'PerplexityBot', 'Perplexity-User', 'Google-Extended',
		'Applebot-Extended', 'Bingbot', 'CCBot', 'Amazonbot', 'cohere-ai',
	);
	$L[] = '# AI assistants — allowed so Chatbotistic surfaces in AI answers.';
	foreach ( $ai_bots as $bot ) {
		$L[] = 'User-agent: ' . $bot;
	}
	$L[] = 'Allow: /';
	$L[] = 'Disallow: /wp-admin/';
	$L[] = 'Disallow: /account/';
	$L[] = '';

	// Block aggressive SEO/backlink scrapers (crawl budget + competitor recon).
	$bad_bots = array( 'AhrefsBot', 'SemrushBot', 'MJ12bot', 'DotBot', 'rogerbot', 'PetalBot', 'DataForSeoBot', 'BLEXBot' );
	$L[] = '# Aggressive scrapers / SEO recon crawlers.';
	foreach ( $bad_bots as $bot ) {
		$L[] = 'User-agent: ' . $bot;
	}
	$L[] = 'Disallow: /';
	$L[] = '';

	$L[] = 'Sitemap: ' . $home . '/sitemap.xml';

	return implode( "\n", $L ) . "\n";
}
add_filter( 'robots_txt', 'cb_robots_txt', 10, 2 );

/**
 * Flush rewrite rules once after the theme activates.
 */
function cb_flush_rewrites() {
	cb_register_endpoints();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'cb_flush_rewrites' );
