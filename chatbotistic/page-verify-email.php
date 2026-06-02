<?php
/**
 * Template Name: Verify Email (V4)
 *
 * Consumes verification tokens from the email link (?uid=&token=) and
 * 6-digit OTPs from the form. On success, marks the user verified and
 * queues the welcome email. Falls back to the "we sent a link" state
 * when no token / OTP is present.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/auth-head' );

// phpcs:disable WordPress.Security.NonceVerification.Recommended,WordPress.Security.NonceVerification.Missing
$uid_q   = isset( $_GET['uid'] ) ? (int) $_GET['uid'] : 0;
$token_q = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
$otp_p   = isset( $_POST['cbp_otp'] ) ? preg_replace( '/\D/', '', wp_unslash( $_POST['cbp_otp'] ) ) : '';
$uid_p   = isset( $_POST['cbp_uid'] ) ? (int) $_POST['cbp_uid'] : 0;
// phpcs:enable

$status = '';

// Auto-verify from email link (link contains uid + token).
if ( $uid_q && $token_q && class_exists( '\\Chatbotistic\\Profile\\Emails_Automation' ) ) {
	$status = \Chatbotistic\Profile\Emails_Automation::verify( $uid_q, $token_q )
		? 'verified' : ( '1' === (string) get_user_meta( $uid_q, \Chatbotistic\Profile\Emails_Automation::META_VERIFIED, true ) ? 'already' : 'invalid' );
}

// Manual OTP submit (form on the page).
if ( ! $status && $uid_p && $otp_p && isset( $_POST['cbp_verify_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cbp_verify_nonce'] ) ), 'cbp_verify' ) && class_exists( '\\Chatbotistic\\Profile\\Emails_Automation' ) ) {
	$status = \Chatbotistic\Profile\Emails_Automation::verify( $uid_p, $otp_p )
		? 'verified' : 'invalid';
}

$top_right = '<a class="btn btn-ghost btn-sm" href="' . esc_url( home_url( '/login/' ) ) . '">' . esc_html__( 'Sign in', 'chatbotistic' ) . '</a>';

if ( 'verified' === $status ) {
	get_template_part( 'template-parts/auth-result', null, array(
		'icon'      => 'ok',
		'title'     => __( 'Email verified — welcome aboard', 'chatbotistic' ),
		'lead'      => __( 'Your account is active. A welcome email with your login details and next steps is on its way.', 'chatbotistic' ),
		'actions'   => '<a class="btn btn-primary" href="' . esc_url( home_url( '/account/' ) ) . '">' . esc_html__( 'Open the member portal →', 'chatbotistic' ) . '</a>',
		'top_right' => $top_right,
	) );
} elseif ( 'already' === $status ) {
	get_template_part( 'template-parts/auth-result', null, array(
		'icon'      => 'ok',
		'title'     => __( 'Already verified', 'chatbotistic' ),
		'lead'      => __( "This account is already verified. You can sign in any time.", 'chatbotistic' ),
		'actions'   => '<a class="btn btn-primary" href="' . esc_url( home_url( '/login/' ) ) . '">' . esc_html__( 'Sign in →', 'chatbotistic' ) . '</a>',
		'top_right' => $top_right,
	) );
} elseif ( 'invalid' === $status ) {
	get_template_part( 'template-parts/auth-result', null, array(
		'icon'      => 'err',
		'title'     => __( 'Verification failed', 'chatbotistic' ),
		'lead'      => __( "That link or code is invalid or expired. Request a new one or sign in to retry.", 'chatbotistic' ),
		'actions'   => '<a class="btn btn-primary" href="' . esc_url( home_url( '/login/' ) ) . '">' . esc_html__( 'Sign in', 'chatbotistic' ) . '</a>'
		             . '<a class="btn btn-ghost" href="' . esc_url( home_url( '/register/' ) ) . '">' . esc_html__( 'Create a new account', 'chatbotistic' ) . '</a>',
		'top_right' => $top_right,
	) );
} else {
	// No status: render the "we sent you a link" wait state with an OTP
	// fallback form so users who didn't get the email can type the code.
	$prefill_uid = $uid_q ?: $uid_p;
	ob_start(); ?>
		<form method="post" style="display:flex;flex-direction:column;gap:10px;align-items:stretch;max-width:280px;margin:18px auto 0;">
			<?php wp_nonce_field( 'cbp_verify', 'cbp_verify_nonce' ); ?>
			<input type="hidden" name="cbp_uid" value="<?php echo esc_attr( (string) $prefill_uid ); ?>">
			<input type="text" name="cbp_otp" placeholder="<?php esc_attr_e( '6-digit code', 'chatbotistic' ); ?>" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required style="text-align:center;letter-spacing:0.3em;font-family:var(--font-mono);">
			<button class="btn btn-primary" type="submit"><?php esc_html_e( 'Verify code', 'chatbotistic' ); ?></button>
		</form>
		<p style="text-align:center;color:var(--text-dim);font-size:12.5px;margin-top:14px;">
			<?php esc_html_e( "Didn't get the email? Check spam, or", 'chatbotistic' ); ?>
			<a href="<?php echo esc_url( add_query_arg( 'resend', '1', home_url( '/verify-email/' ) ) ); ?>"><?php esc_html_e( 'resend it', 'chatbotistic' ); ?></a>.
		</p>
	<?php
	$actions = '<a class="btn btn-ghost" href="' . esc_url( home_url( '/login/' ) ) . '">' . esc_html__( 'Sign in instead', 'chatbotistic' ) . '</a>';
	$lead    = __( 'We sent a verification link and 6-digit code to your inbox. Click the link or paste the code below.', 'chatbotistic' );
	get_template_part( 'template-parts/auth-result', null, array(
		'icon'      => 'wait',
		'title'     => __( 'Verify your email', 'chatbotistic' ),
		'lead'      => $lead,
		'actions'   => $actions . ob_get_clean(),
		'top_right' => $top_right,
	) );
}

get_template_part( 'template-parts/auth-foot' );
