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
	add_rewrite_rule( '^sitemap\.xml$',                'index.php?cb_feed=sitemap',             'top' );
	add_rewrite_rule( '^sitemap-pages\.xml$',          'index.php?cb_feed=sitemap-pages',       'top' );
	add_rewrite_rule( '^sitemap-posts\.xml$',          'index.php?cb_feed=sitemap-posts',       'top' );
	add_rewrite_rule( '^sitemap-usecases\.xml$',       'index.php?cb_feed=sitemap-usecases',    'top' );
	add_rewrite_rule( '^sitemap-products\.xml$',       'index.php?cb_feed=sitemap-products',    'top' );
	add_rewrite_rule( '^sitemap-categories\.xml$',     'index.php?cb_feed=sitemap-categories',  'top' );
	add_rewrite_rule( '^sitemap-tags\.xml$',           'index.php?cb_feed=sitemap-tags',        'top' );
	// Legacy aliases — early branded paths used <type>-sitemap.xml. Keep
	// them resolving so any existing crawler bookmarks survive.
	add_rewrite_rule( '^page-sitemap\.xml$',           'index.php?cb_feed=sitemap-pages',       'top' );
	add_rewrite_rule( '^post-sitemap\.xml$',           'index.php?cb_feed=sitemap-posts',       'top' );
	add_rewrite_rule( '^category-sitemap\.xml$',       'index.php?cb_feed=sitemap-categories',  'top' );
	add_rewrite_rule( '^llms\.txt$',                   'index.php?cb_feed=llms',                'top' );
	add_rewrite_rule( '^llms-full\.txt$',              'index.php?cb_feed=llms-full',           'top' );
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
	return (array) apply_filters( 'cb_sitemap_sections', array(
		'pages'      => array( 'label' => __( 'Pages',       'chatbotistic' ), 'callback' => 'cb_sitemap_pages' ),
		'posts'      => array( 'label' => __( 'Blog posts',  'chatbotistic' ), 'callback' => 'cb_sitemap_posts' ),
		'usecases'   => array( 'label' => __( 'Use cases',   'chatbotistic' ), 'callback' => 'cb_sitemap_usecases' ),
		'products'   => array( 'label' => __( 'Products',    'chatbotistic' ), 'callback' => 'cb_sitemap_products' ),
		'categories' => array( 'label' => __( 'Categories',  'chatbotistic' ), 'callback' => 'cb_sitemap_categories' ),
		'tags'       => array( 'label' => __( 'Tags',        'chatbotistic' ), 'callback' => 'cb_sitemap_tags' ),
	) );
}

/**
 * Pages that belong in the dedicated usecases / products sub-sitemaps —
 * pulled out of the generic pages sub-sitemap so SEO tools can analyse
 * landing-page health in isolation.
 *
 * @return array{usecases:string[],products:string[]}
 */
function cb_special_landing_slugs() {
	return array(
		'usecases' => array(
			'use-cases', 'agencies', 'local-business', 'clinics-spas', 'clinics',
			'real-estate', 'ecommerce', 'wordpress-sites', 'saas-founders',
			'coaches', 'coaches-consultants', 'travel-agencies',
		),
		'products' => array(
			'features', 'whatsapp-automation', 'ai-chatbot', 'booking-forms',
			'wordpress-plugin', 'agency-white-label',
		),
	);
}

/**
 * Page entries (front page first, portal/auth pages excluded).
 *
 * @return array<int,array{url:string,modified:string}>
 */
function cb_sitemap_pages() {
	$front_img = function_exists( 'cb_share_image_for' ) ? cb_share_image_for( null ) : '';
	$entries   = array( array( 'url' => home_url( '/' ), 'modified' => current_time( 'c' ), 'image' => $front_img ) );
	$skip      = cb_noindex_slugs();
	$special   = cb_special_landing_slugs();
	$reserved  = array_merge( $special['usecases'], $special['products'] );
	$front     = (string) get_option( 'page_on_front' );

	foreach ( get_posts( array(
		'post_type'   => 'page',
		'post_status' => 'publish',
		'numberposts' => -1,
		'orderby'     => 'menu_order title',
		'order'       => 'ASC',
	) ) as $page ) {
		if ( in_array( $page->post_name, $skip, true ) || in_array( $page->post_name, $reserved, true ) || $front === (string) $page->ID ) {
			continue;
		}
		$entries[] = array(
			'url'      => get_permalink( $page ),
			'modified' => get_post_modified_time( 'c', true, $page ),
			'image'    => function_exists( 'cb_share_image_for' ) ? cb_share_image_for( $page ) : '',
		);
	}
	return $entries;
}

/**
 * Use-case landing pages (split out of the generic pages sub-sitemap).
 *
 * @return array<int,array{url:string,modified:string}>
 */
function cb_sitemap_usecases() {
	$entries = array();
	$skip    = cb_noindex_slugs();
	foreach ( cb_special_landing_slugs()['usecases'] as $slug ) {
		if ( in_array( $slug, $skip, true ) ) {
			continue;
		}
		$page = get_page_by_path( $slug );
		if ( $page && 'publish' === $page->post_status ) {
			$entries[] = array(
				'url'      => get_permalink( $page ),
				'modified' => get_post_modified_time( 'c', true, $page ),
				'image'    => function_exists( 'cb_share_image_for' ) ? cb_share_image_for( $page ) : '',
			);
		}
	}
	return $entries;
}

/**
 * Product landing pages.
 *
 * @return array<int,array{url:string,modified:string}>
 */
function cb_sitemap_products() {
	$entries = array();
	$skip    = cb_noindex_slugs();
	foreach ( cb_special_landing_slugs()['products'] as $slug ) {
		if ( in_array( $slug, $skip, true ) ) {
			continue;
		}
		$page = get_page_by_path( $slug );
		if ( $page && 'publish' === $page->post_status ) {
			$entries[] = array(
				'url'      => get_permalink( $page ),
				'modified' => get_post_modified_time( 'c', true, $page ),
				'image'    => function_exists( 'cb_share_image_for' ) ? cb_share_image_for( $page ) : '',
			);
		}
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
			'image'    => function_exists( 'cb_share_image_for' ) ? cb_share_image_for( $post ) : '',
		);
	}
	return $entries;
}

/**
 * Category archive entries (non-empty only).
 *
 * @return array<int,array{url:string,modified:string}>
 */
function cb_sitemap_categories() {
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
 * Tag archive entries (non-empty only).
 *
 * @return array<int,array{url:string,modified:string}>
 */
function cb_sitemap_tags() {
	$entries = array();
	foreach ( get_tags( array( 'hide_empty' => true ) ) as $term ) {
		$link = get_term_link( $term );
		if ( ! is_wp_error( $link ) ) {
			$entries[] = array( 'url' => $link, 'modified' => current_time( 'c' ) );
		}
	}
	return $entries;
}

/**
 * Back-compat alias — older callers referenced cb_sitemap_terms.
 *
 * @deprecated Use cb_sitemap_categories() directly.
 */
function cb_sitemap_terms() {
	return cb_sitemap_categories();
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
		// Back-compat: the early branded path used 'page'/'post'/'category'
		// keys instead of 'pages'/'posts'/'categories'. Map them through.
		$compat = array( 'page' => 'pages', 'post' => 'posts', 'category' => 'categories' );
		if ( isset( $compat[ $key ] ) ) {
			$key = $compat[ $key ];
		}
		$entries = isset( $sections[ $key ] ) ? call_user_func( $sections[ $key ]['callback'] ) : array();
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
		echo "\t\t<loc>" . esc_url( home_url( '/sitemap-' . $key . '.xml' ) ) . "</loc>\n";
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
	// xmlns:image lets each <url> carry one or more <image:image><image:loc>
	// blocks — the same OG / share image we serve to social cards, now
	// visible to Google Images and AI image-search engines.
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
	foreach ( $entries as $entry ) {
		echo "\t<url>\n";
		echo "\t\t<loc>" . esc_url( $entry['url'] ) . "</loc>\n";
		echo "\t\t<lastmod>" . esc_html( $entry['modified'] ) . "</lastmod>\n";
		if ( ! empty( $entry['image'] ) ) {
			echo "\t\t<image:image>\n";
			echo "\t\t\t<image:loc>" . esc_url( $entry['image'] ) . "</image:loc>\n";
			echo "\t\t</image:image>\n";
		}
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
