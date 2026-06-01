<?php
/**
 * llms.txt and llms-full.txt generation.
 *
 * The llms.txt standard (https://llmstxt.org) gives AI assistants a clean,
 * curated map of the site so they surface Chatbotistic accurately and fast.
 * /llms.txt       — concise index with links.
 * /llms-full.txt  — index plus page summaries.
 *
 * Routing is registered in inc/sitemap.php; this file renders the output.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render the llms.txt body.
 *
 * @param bool $full Whether to include per-page summaries (llms-full.txt).
 */
function cb_render_llms( $full = false ) {
	header( 'Content-Type: text/plain; charset=UTF-8' );

	$name = get_bloginfo( 'name' );
	$desc = get_bloginfo( 'description' );

	$lines   = array();
	$lines[] = '# ' . $name;
	$lines[] = '';
	if ( $desc ) {
		$lines[] = '> ' . $desc;
		$lines[] = '';
	}
	$lines[] = trim( (string) apply_filters( 'cb_llms_intro',
		'Chatbotistic is an AI WhatsApp Agent and chat-widget platform for service businesses, '
		. 'agencies, and WordPress sites. It captures leads, answers questions 24/7, books '
		. 'appointments, and routes conversations — replacing several disconnected tools with one.'
	) );
	$lines[] = '';

	// Curated, ordered page groups.
	$groups = cb_llms_groups();
	foreach ( $groups as $heading => $slugs ) {
		$section = array();
		foreach ( $slugs as $slug ) {
			$page = get_page_by_path( $slug );
			if ( ! $page || 'publish' !== $page->post_status ) {
				continue;
			}
			$line = '- [' . get_the_title( $page ) . '](' . get_permalink( $page ) . ')';
			$blurb = wp_strip_all_tags( $page->post_excerpt ?: wp_trim_words( $page->post_content, 24 ) );
			if ( $blurb ) {
				$line .= ': ' . $blurb;
			}
			$section[] = $line;
			if ( $full && $page->post_content ) {
				$body = wp_strip_all_tags( $page->post_content );
				$body = trim( preg_replace( '/\s+/', ' ', $body ) );
				if ( $body ) {
					$section[] = '  ' . wp_trim_words( $body, 90 );
				}
			}
		}
		if ( $section ) {
			$lines[] = '## ' . $heading;
			$lines[] = '';
			foreach ( $section as $entry ) {
				$lines[] = $entry;
			}
			$lines[] = '';
		}
	}

	// Latest posts.
	$posts = get_posts( array( 'numberposts' => $full ? 20 : 10, 'post_status' => 'publish' ) );
	if ( $posts ) {
		$lines[] = '## Blog';
		$lines[] = '';
		foreach ( $posts as $post ) {
			$line = '- [' . get_the_title( $post ) . '](' . get_permalink( $post ) . ')';
			$ex   = wp_strip_all_tags( $post->post_excerpt ?: wp_trim_words( $post->post_content, 22 ) );
			if ( $ex ) {
				$line .= ': ' . $ex;
			}
			$lines[] = $line;
		}
		$lines[] = '';
	}

	$lines[] = '## Contact';
	$lines[] = '';
	$lines[] = '- Website: ' . home_url( '/' );
	$lines[] = '- Sitemap: ' . home_url( '/sitemap.xml' );
	$lines[] = '';
	$lines[] = 'Generated ' . gmdate( 'Y-m-d' ) . ' · Part of the WordPressistic ecosystem.';

	echo implode( "\n", array_map( 'wp_strip_all_tags', $lines ) ) . "\n";
}

/**
 * Curated slug groups for the llms files. Slugs that don't exist are skipped.
 *
 * @return array<string,string[]>
 */
function cb_llms_groups() {
	return (array) apply_filters( 'cb_llms_groups', array(
		'Product'   => array( 'features', 'whatsapp-automation', 'ai-chatbot', 'booking-forms', 'wordpress-plugin', 'agency-white-label' ),
		'Pricing'   => array( 'pricing' ),
		'Use cases' => array( 'use-cases', 'agencies', 'local-business', 'clinics-spas', 'real-estate', 'ecommerce', 'coaches', 'wordpress-sites', 'saas-founders' ),
		'Resources' => array( 'docs', 'tutorials', 'faqs', 'demo', 'support' ),
		'Company'   => array( 'about', 'contact', 'affiliate' ),
		'Legal'     => array( 'privacy-policy', 'terms', 'refund-policy', 'security' ),
	) );
}
