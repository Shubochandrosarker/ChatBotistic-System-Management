<?php
/**
 * Template Name: Payment Failed (V4)
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/auth-head' );

$top_right = '<a class="btn btn-ghost btn-sm" href="' . esc_url( home_url( '/contact/' ) ) . '">' . esc_html__( 'Contact support', 'chatbotistic' ) . '</a>';
$actions   = '<a class="btn btn-primary" href="' . esc_url( home_url( '/checkout/' ) ) . '">' . esc_html__( 'Try again →', 'chatbotistic' ) . '</a>'
           . '<a class="btn btn-ghost" href="' . esc_url( home_url( '/contact/' ) ) . '">' . esc_html__( 'Contact billing', 'chatbotistic' ) . '</a>';

$reason = isset( $_GET['reason'] ) ? sanitize_key( wp_unslash( $_GET['reason'] ) ) : 'card_declined'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$plan   = isset( $_GET['plan'] )   ? sanitize_text_field( wp_unslash( $_GET['plan'] ) ) : 'Pro · Annual'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

get_template_part( 'template-parts/auth-result', null, array(
	'icon'      => 'err',
	'title'     => __( 'Payment failed', 'chatbotistic' ),
	'lead'      => __( 'Your card was declined and you have not been charged. Check your card details or try a different payment method.', 'chatbotistic' ),
	'receipt'   => array(
		array( __( 'Reason', 'chatbotistic' ), $reason, '#ff8aa0' ),
		array( __( 'Plan', 'chatbotistic' ),   $plan ),
	),
	'actions'   => $actions,
	'top_right' => $top_right,
) );

get_template_part( 'template-parts/auth-foot' );
