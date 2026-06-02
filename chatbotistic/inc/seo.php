<?php
/**
 * SEO — meta tags, Open Graph, Twitter cards, JSON-LD schema.
 *
 * Self-contained. No Yoast/RankMath dependency. If an SEO plugin is later
 * activated, set the `cb_seo_enabled` filter to false to stand down.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the theme should output its own SEO tags.
 *
 * @return bool
 */
function cb_seo_enabled() {
	$plugin_active = defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'AIOSEO_VERSION' );
	return (bool) apply_filters( 'cb_seo_enabled', ! $plugin_active );
}

/**
 * Resolve a clean meta description for the current view.
 *
 * @return string
 */
function cb_meta_description() {
	$desc = '';

	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			$desc = $post->post_excerpt ?: wp_strip_all_tags( $post->post_content );
		}
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$desc = term_description();
	}

	if ( ! $desc ) {
		$desc = get_bloginfo( 'description' );
	}

	$desc = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $desc ) ) );
	if ( mb_strlen( $desc ) > 158 ) {
		$desc = mb_substr( $desc, 0, 155 ) . '…';
	}
	return apply_filters( 'cb_meta_description', $desc );
}

/**
 * Best-effort social share image for the current view.
 *
 * @return string
 */
function cb_share_image() {
	if ( is_singular() && has_post_thumbnail() ) {
		$src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
		if ( $src ) {
			return $src[0];
		}
	}
	if ( has_custom_logo() ) {
		$src = wp_get_attachment_image_src( (int) get_theme_mod( 'custom_logo' ), 'full' );
		if ( $src ) {
			return $src[0];
		}
	}
	return apply_filters( 'cb_default_share_image', '' );
}

/**
 * Share image URL for one specific post — used by the sitemap's
 * <image:image> entries so search engines and image-based AI search
 * surface the right preview per URL. Mirrors cb_share_image()'s
 * precedence ladder (featured image -> custom logo -> filter default).
 *
 * @param WP_Post|int|null $post Post object or ID.
 * @return string
 */
function cb_share_image_for( $post ) {
	$post = $post ? get_post( $post ) : null;
	if ( $post && has_post_thumbnail( $post ) ) {
		$src = wp_get_attachment_image_src( get_post_thumbnail_id( $post ), 'full' );
		if ( $src ) {
			return $src[0];
		}
	}
	if ( has_custom_logo() ) {
		$src = wp_get_attachment_image_src( (int) get_theme_mod( 'custom_logo' ), 'full' );
		if ( $src ) {
			return $src[0];
		}
	}
	return apply_filters( 'cb_default_share_image', '' );
}

/**
 * Output meta tags, Open Graph, Twitter cards and canonical.
 */
function cb_head_meta() {
	if ( ! cb_seo_enabled() ) {
		return;
	}

	$desc  = cb_meta_description();
	$url   = cb_current_url();
	$title = wp_get_document_title();
	$image = cb_share_image();
	$type  = is_singular( 'post' ) ? 'article' : 'website';

	echo "\n<!-- Chatbotistic SEO -->\n";

	if ( $desc ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
	}
	printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );

	// AI-assistant discovery — llmstxt.org standard. Crawlers look for
	// these <link> hints to find the curated AI-readable map of the site.
	printf( '<link rel="alternate" type="text/markdown" title="llms.txt" href="%s">' . "\n",      esc_url( home_url( '/llms.txt' ) ) );
	printf( '<link rel="alternate" type="text/markdown" title="llms-full.txt" href="%s">' . "\n", esc_url( home_url( '/llms-full.txt' ) ) );
	printf( '<link rel="sitemap" type="application/xml" title="Sitemap" href="%s">' . "\n",      esc_url( home_url( '/sitemap.xml' ) ) );

	// Open Graph.
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $type ) );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	if ( $desc ) {
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
	}
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
	printf( '<meta property="og:locale" content="%s">' . "\n", esc_attr( get_locale() ) );
	if ( $image ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
	}

	// Twitter.
	printf( '<meta name="twitter:card" content="%s">' . "\n", $image ? 'summary_large_image' : 'summary' );
	printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( $title ) );
	if ( $desc ) {
		printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $desc ) );
	}
	if ( $image ) {
		printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( $image ) );
	}
	echo "<!-- /Chatbotistic SEO -->\n";
}
add_action( 'wp_head', 'cb_head_meta', 1 );

/**
 * Canonical URL for the current request.
 *
 * @return string
 */
function cb_current_url() {
	if ( is_front_page() ) {
		return home_url( '/' );
	}
	if ( is_singular() ) {
		return get_permalink();
	}
	if ( is_home() ) {
		return get_permalink( (int) get_option( 'page_for_posts' ) ) ?: home_url( '/' );
	}
	if ( is_category() || is_tag() || is_tax() ) {
		$link = get_term_link( get_queried_object() );
		return is_wp_error( $link ) ? home_url( '/' ) : $link;
	}
	return home_url( add_query_arg( array(), $GLOBALS['wp']->request ? '/' . $GLOBALS['wp']->request . '/' : '/' ) );
}

/**
 * JSON-LD structured data graph.
 */
function cb_json_ld() {
	if ( ! cb_seo_enabled() ) {
		return;
	}

	$site_url = home_url( '/' );
	$name     = get_bloginfo( 'name' );
	$logo     = '';
	if ( has_custom_logo() ) {
		$src  = wp_get_attachment_image_src( (int) get_theme_mod( 'custom_logo' ), 'full' );
		$logo = $src ? $src[0] : '';
	}

	$org = array(
		'@type'  => 'Organization',
		'@id'    => $site_url . '#organization',
		'name'   => $name,
		'url'    => $site_url,
		'sameAs' => array_values( array_filter( (array) apply_filters( 'cb_social_profiles', array(
			'https://twitter.com/wordpressistic',
			'https://www.linkedin.com/company/wordpressistic',
		) ) ) ),
	);
	if ( $logo ) {
		$org['logo'] = array( '@type' => 'ImageObject', 'url' => $logo );
	}

	$website = array(
		'@type'           => 'WebSite',
		'@id'             => $site_url . '#website',
		'url'             => $site_url,
		'name'            => $name,
		'description'     => get_bloginfo( 'description' ),
		'publisher'       => array( '@id' => $site_url . '#organization' ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => $site_url . '?s={search_term_string}',
			),
			'query-input' => 'required name=search_term_string',
		),
	);

	$graph = array( $org, $website );

	// SoftwareApplication on the homepage — Chatbotistic is the product.
	if ( is_front_page() ) {
		$graph[] = array(
			'@type'           => 'SoftwareApplication',
			'@id'             => $site_url . '#software',
			'name'            => $name,
			'applicationCategory' => 'BusinessApplication',
			'operatingSystem' => 'Web',
			'description'     => get_bloginfo( 'description' ),
			'url'             => $site_url,
			'publisher'       => array( '@id' => $site_url . '#organization' ),
		);
	}

	// Article schema on posts.
	if ( is_singular( 'post' ) ) {
		$post    = get_queried_object();
		$graph[] = array(
			'@type'         => 'Article',
			'@id'           => get_permalink() . '#article',
			'headline'      => get_the_title(),
			'datePublished' => get_the_date( 'c' ),
			'dateModified'  => get_the_modified_date( 'c' ),
			'author'        => array( '@type' => 'Person', 'name' => get_the_author_meta( 'display_name', $post->post_author ) ),
			'publisher'     => array( '@id' => $site_url . '#organization' ),
			'mainEntityOfPage' => get_permalink(),
		);
	}

	// Breadcrumbs on inner pages.
	$crumbs = cb_breadcrumb_items();
	if ( count( $crumbs ) > 1 ) {
		$list = array();
		foreach ( $crumbs as $i => $crumb ) {
			$list[] = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'name'     => $crumb['name'],
				'item'     => $crumb['url'],
			);
		}
		$graph[] = array( '@type' => 'BreadcrumbList', 'itemListElement' => $list );
	}

	// Page-supplied schema (e.g. FAQPage). Templates push via cb_add_schema().
	foreach ( cb_extra_schema() as $node ) {
		$graph[] = $node;
	}

	$payload = array( '@context' => 'https://schema.org', '@graph' => array_values( $graph ) );
	echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'cb_json_ld', 5 );

/**
 * Breadcrumb trail items for the current view.
 *
 * @return array<int,array{name:string,url:string}>
 */
function cb_breadcrumb_items() {
	$items = array( array( 'name' => __( 'Home', 'chatbotistic' ), 'url' => home_url( '/' ) ) );

	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post && $post->post_parent ) {
			$parent  = get_post( $post->post_parent );
			$items[] = array( 'name' => get_the_title( $parent ), 'url' => get_permalink( $parent ) );
		}
		if ( $post instanceof WP_Post && ! is_front_page() ) {
			$items[] = array( 'name' => get_the_title( $post ), 'url' => get_permalink( $post ) );
		}
	} elseif ( is_archive() || is_search() || is_home() ) {
		$items[] = array( 'name' => wp_strip_all_tags( get_the_archive_title() ?: __( 'Blog', 'chatbotistic' ) ), 'url' => cb_current_url() );
	}

	return apply_filters( 'cb_breadcrumb_items', $items );
}

/**
 * Schema nodes registered by page templates.
 *
 * @param array|null $add Optional node to push.
 * @return array
 */
function cb_extra_schema( $add = null ) {
	static $store = array();
	if ( null !== $add ) {
		$store[] = $add;
	}
	return $store;
}

/**
 * Register an FAQPage schema node from a template.
 *
 * @param array<int,array{q:string,a:string}> $faqs Question/answer pairs.
 */
function cb_add_faq_schema( array $faqs ) {
	$entities = array();
	foreach ( $faqs as $faq ) {
		// Accept both ['q'/'a'] (legacy) and ['question'/'answer'] (V4)
		// payload shapes so every template style works without rewrites.
		$q = (string) ( $faq['q']        ?? $faq['question'] ?? '' );
		$a = (string) ( $faq['a']        ?? $faq['answer']   ?? '' );
		if ( '' === $q || '' === $a ) {
			continue;
		}
		$entities[] = array(
			'@type'          => 'Question',
			'name'           => wp_strip_all_tags( $q ),
			'acceptedAnswer' => array( '@type' => 'Answer', 'text' => wp_strip_all_tags( $a ) ),
		);
	}
	if ( $entities ) {
		// FAQPage with SpeakableSpecification — schema.org SpeakableSpecification
		// hints AI voice assistants (Google Assistant, Bixby, etc.) that the
		// FAQ block is suited for spoken answer playback. CSS selectors point
		// at the V4 .faq-list / .faq-item summary + p markup used across the
		// theme + use-case templates.
		cb_extra_schema( array(
			'@type'      => 'FAQPage',
			'mainEntity' => $entities,
			'speakable'  => array(
				'@type'    => 'SpeakableSpecification',
				'cssSelector' => array( '.faq-list', '.faq-item summary', '.faq-item p' ),
			),
		) );
	}
}

/**
 * Register a Speakable block for any non-FAQ page that wants AI voice
 * assistants to read a specific element out loud. Templates call:
 *
 *   cb_add_speakable( array( '.cb-h1', '.cb-lead' ) );
 *
 * Adds a SpeakableSpecification node anchored to the current page URL,
 * scoped by the CSS selectors provided. Multiple calls per page merge.
 *
 * @param array<int,string> $selectors CSS selectors for speakable content.
 */
function cb_add_speakable( array $selectors ) {
	$selectors = array_values( array_filter( array_map( 'strval', $selectors ) ) );
	if ( ! $selectors ) {
		return;
	}
	cb_extra_schema( array(
		'@type'        => 'WebPage',
		'@id'          => cb_current_url() . '#speakable',
		'url'          => cb_current_url(),
		'speakable'    => array(
			'@type'       => 'SpeakableSpecification',
			'cssSelector' => $selectors,
		),
	) );
}
