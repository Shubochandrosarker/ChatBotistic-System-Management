<?php
/**
 * Template Name: Account Required (V4)
 *
 * Shown when a logged-out visitor hits a protected page.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

if ( is_user_logged_in() ) {
	wp_safe_redirect( home_url( '/account/' ) );
	exit;
}

get_template_part( 'template-parts/auth-head' );

$cb_back = isset( $_GET['from'] ) ? esc_url_raw( wp_unslash( $_GET['from'] ) ) : home_url( '/account/' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$top_right = '<a class="btn btn-ghost btn-sm" href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Back to site', 'chatbotistic' ) . '</a>';
$actions = '<a class="btn btn-primary" href="' . esc_url( add_query_arg( 'redirect_to', $cb_back, home_url( '/login/' ) ) ) . '">' . esc_html__( 'Sign in →', 'chatbotistic' ) . '</a>'
         . '<a class="btn btn-ghost" href="' . esc_url( home_url( '/register/' ) ) . '">' . esc_html__( 'Create account', 'chatbotistic' ) . '</a>';

get_template_part( 'template-parts/auth-result', null, array(
	'icon'      => 'wait',
	'title'     => __( 'Sign in to continue', 'chatbotistic' ),
	'lead'      => __( 'This page is part of the Chatbotistic member portal. Sign in or create a free account to access widgets, leads, and your license.', 'chatbotistic' ),
	'actions'   => $actions,
	'top_right' => $top_right,
) );

get_template_part( 'template-parts/auth-foot' );
