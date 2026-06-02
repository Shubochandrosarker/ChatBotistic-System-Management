<?php
/**
 * Template Name: WordPress Plugin
 *
 * V4 product landing — content sourced from cb_v4_product('wordpress-plugin').
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cb_product = function_exists( 'cb_v4_product' ) ? cb_v4_product( 'wordpress-plugin' ) : null;
if ( $cb_product ) {
	get_template_part( 'template-parts/landing-v4-product', null, array( 'product' => $cb_product ) );
} else {
	echo '<section class="section"><div class="container"><h1 class="h-1">' . esc_html( get_the_title() ) . '</h1></div></section>';
}

get_footer();
