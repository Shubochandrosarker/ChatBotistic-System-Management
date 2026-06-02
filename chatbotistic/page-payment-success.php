<?php
/**
 * Template Name: Payment Success (V4)
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/auth-head' );

$top_right = '<a class="btn btn-ghost btn-sm" href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Back to site', 'chatbotistic' ) . '</a>';
$actions   = '<a class="btn btn-primary" href="' . esc_url( home_url( '/account/' ) ) . '">' . esc_html__( 'Go to dashboard →', 'chatbotistic' ) . '</a>'
           . '<a class="btn btn-ghost" href="' . esc_url( home_url( '/account/?view=billing' ) ) . '">' . esc_html__( 'Download invoice', 'chatbotistic' ) . '</a>';

$plan    = isset( $_GET['plan'] )    ? sanitize_text_field( wp_unslash( $_GET['plan'] ) )    : 'Pro · Annual';    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$amount  = isset( $_GET['amount'] )  ? sanitize_text_field( wp_unslash( $_GET['amount'] ) )  : '$90.00';          // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$invoice = isset( $_GET['invoice'] ) ? sanitize_text_field( wp_unslash( $_GET['invoice'] ) ) : '#CB-2026-0481';   // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$license = (string) get_user_meta( get_current_user_id(), 'mlb_license_key', true );
if ( '' === $license ) {
	$license = 'CBT-XXXX-XXXX-XXXX';
}

get_template_part( 'template-parts/auth-result', null, array(
	'icon'      => 'ok',
	'title'     => __( 'Payment successful', 'chatbotistic' ),
	'lead'      => __( 'Welcome to Chatbotistic. Your plan is active and your license has been generated.', 'chatbotistic' ),
	'receipt'   => array(
		array( __( 'Plan', 'chatbotistic' ),    $plan ),
		array( __( 'Amount', 'chatbotistic' ),  $amount ),
		array( __( 'Invoice', 'chatbotistic' ), $invoice ),
		array( __( 'License', 'chatbotistic' ), $license ),
	),
	'actions'   => $actions,
	'top_right' => $top_right,
) );

get_template_part( 'template-parts/auth-foot' );
