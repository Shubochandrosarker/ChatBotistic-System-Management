<?php
/**
 * Template Name: Agency & White Label
 *
 * Product landing page. Content resolves from inc/landing-data.php by slug
 * and renders through template-parts/landing.php.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$cb_data = cb_landing_get( get_post()->post_name );
	if ( $cb_data ) {
		get_template_part( 'template-parts/landing', null, array( 'data' => $cb_data ) );
	} else {
		echo '<section class="cb-page-hero"><div class="cb-container"><h1 class="cb-h1"><span class="cb-grad">' . esc_html( get_the_title() ) . '</span></h1></div></section>';
		echo '<section class="cb-section"><div class="cb-container"><div class="cb-prose">' . wp_kses_post( apply_filters( 'the_content', get_the_content() ) ) . '</div></div></section>';
	}
endwhile;

get_footer();
