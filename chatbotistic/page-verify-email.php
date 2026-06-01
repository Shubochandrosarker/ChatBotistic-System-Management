<?php
/**
 * Template Name: Verify Email (V4)
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/auth-head' );

$top_right = '<a class="btn btn-ghost btn-sm" href="' . esc_url( home_url( '/login/' ) ) . '">' . esc_html__( 'Sign in', 'chatbotistic' ) . '</a>';
$actions   = '<a class="btn btn-primary" href="' . esc_url( home_url( '/account/' ) ) . '">' . esc_html__( 'I’ve verified — continue →', 'chatbotistic' ) . '</a>'
           . '<a class="btn btn-ghost" href="' . esc_url( add_query_arg( 'resend', '1', home_url( '/verify-email/' ) ) ) . '">' . esc_html__( 'Resend email', 'chatbotistic' ) . '</a>';
$foot      = esc_html__( 'Wrong address?', 'chatbotistic' ) . ' <a href="' . esc_url( home_url( '/register/' ) ) . '" style="color:var(--blue-bright);">' . esc_html__( 'Update it', 'chatbotistic' ) . '</a>';

get_template_part( 'template-parts/auth-result', null, array(
	'icon'      => 'wait',
	'title'     => __( 'Verify your email', 'chatbotistic' ),
	'lead'      => __( 'We sent a verification link to your inbox. Click it to activate your account and enter the member portal.', 'chatbotistic' ),
	'actions'   => $actions,
	'foot'      => $foot,
	'top_right' => $top_right,
) );

get_template_part( 'template-parts/auth-foot' );
