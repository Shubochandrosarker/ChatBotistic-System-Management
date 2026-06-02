<?php
/**
 * Template Name: Use Case Landing (V4)
 *
 * Single template reused by every industry use-case page. Content is
 * resolved from inc/v4-landing-data.php by the page slug, then rendered
 * by template-parts/landing-v4-usecase.php.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$cb_slug = get_post()->post_name;
	$cb_case = function_exists( 'cb_v4_use_case' ) ? cb_v4_use_case( $cb_slug ) : null;

	$cb_next = null;
	if ( $cb_case && function_exists( 'cb_v4_use_cases' ) ) {
		$cb_all       = cb_v4_use_cases();
		$cb_keys      = array_keys( $cb_all );
		$cb_idx       = array_search( $cb_slug, $cb_keys, true );
		$cb_next_slug = $cb_keys[ ( $cb_idx + 1 ) % count( $cb_keys ) ];
		$cb_next      = array(
			'slug'  => $cb_next_slug,
			'title' => $cb_all[ $cb_next_slug ]['title'],
		);
	}

	if ( $cb_case ) {
		get_template_part( 'template-parts/landing-v4-usecase', null, array(
			'case' => $cb_case,
			'slug' => $cb_slug,
			'next' => $cb_next,
		) );
	} else {
		$cb_legacy = function_exists( 'cb_landing_get' ) ? cb_landing_get( $cb_slug ) : null;
		if ( $cb_legacy ) {
			get_template_part( 'template-parts/landing', null, array( 'data' => $cb_legacy ) );
		} else {
			echo '<section class="section"><div class="container"><h1 class="h-1">' . esc_html( get_the_title() ) . '</h1><p>' . esc_html__( 'Content for this page is being prepared.', 'chatbotistic' ) . '</p></div></section>';
		}
	}
endwhile;

get_footer();
