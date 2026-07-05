<?php
/**
 * Template Name: Cancel Plan (V4)
 *
 * Two states: confirm prompt and confirmed result.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( home_url( '/login/?redirect_to=' . rawurlencode( home_url( '/cancel-plan/' ) ) ) );
	exit;
}

get_template_part( 'template-parts/auth-head' );

$cb_confirmed = isset( $_GET['confirmed'] ) && '1' === $_GET['confirmed']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$top_right = '<a class="btn btn-ghost btn-sm" href="' . esc_url( home_url( '/account/?view=billing' ) ) . '">' . esc_html__( 'Back to billing', 'chatbotistic' ) . '</a>';

if ( $cb_confirmed ) :
	$actions = '<a class="btn btn-primary" href="' . esc_url( home_url( '/account/' ) ) . '">' . esc_html__( 'Back to dashboard', 'chatbotistic' ) . '</a>'
	         . '<a class="btn btn-ghost" href="' . esc_url( home_url( '/pricing/' ) ) . '">' . esc_html__( 'Resubscribe', 'chatbotistic' ) . '</a>';
	get_template_part( 'template-parts/auth-result', null, array(
		'icon'      => 'ok',
		'title'     => __( 'Cancellation confirmed', 'chatbotistic' ),
		'lead'      => __( 'Your plan will stay active until the end of your current billing period. You can re-subscribe anytime — your data is preserved for 90 days.', 'chatbotistic' ),
		'actions'   => $actions,
		'top_right' => $top_right,
	) );
else :
	$actions = '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center;">'
	         . '<input type="hidden" name="action" value="cb_cancel_plan">'
	         . wp_nonce_field( 'cb_cancel_plan', '_wpnonce', true, false )
	         . '<button class="btn btn-primary" type="submit" style="background:linear-gradient(135deg,#ff7a8a,#ff5572);">' . esc_html__( 'Confirm cancellation', 'chatbotistic' ) . '</button>'
	         . '<a class="btn btn-ghost" href="' . esc_url( home_url( '/account/?view=billing' ) ) . '">' . esc_html__( 'Keep my plan', 'chatbotistic' ) . '</a>'
	         . '</form>';
	get_template_part( 'template-parts/auth-result', null, array(
		'icon'      => 'wait',
		'title'     => __( 'Cancel your plan?', 'chatbotistic' ),
		'lead'      => __( 'Your widgets stay live until the end of the current billing period. After that, premium features pause and your data is preserved for 90 days.', 'chatbotistic' ),
		'actions'   => $actions,
		'top_right' => $top_right,
	) );
endif;

get_template_part( 'template-parts/auth-foot' );
