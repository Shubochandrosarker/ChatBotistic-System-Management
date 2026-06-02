<?php
/**
 * Template Name: Reset Password (V4)
 *
 * Set new password — hands off to wp-login.php?action=resetpass.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/auth-head' );

$key   = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$login = isset( $_GET['login'] ) ? sanitize_text_field( wp_unslash( $_GET['login'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>

<div class="auth-top" style="height:72px;display:flex;align-items:center;justify-content:space-between;padding:0 32px;border-bottom:1px solid var(--line-soft);position:sticky;top:0;z-index:30;background:rgba(4,6,12,0.7);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);">
	<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" style="display:inline-flex;align-items:center;gap:10px;text-decoration:none;color:var(--text);font-family:var(--font-display);font-weight:600;">
		<span class="logo-mark"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6.5C4 5 5 4 6.5 4h11C19 4 20 5 20 6.5v8c0 1.5-1 2.5-2.5 2.5H13l-4 3v-3H6.5C5 17 4 16 4 14.5z"/></svg></span>
		Chatbotistic
	</a>
	<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( home_url( '/login/' ) ); ?>"><?php esc_html_e( 'Back to sign in', 'chatbotistic' ); ?></a>
</div>

<main class="page-fade">
	<div class="result-wrap">
		<div class="glass glass-edge auth-card" style="padding:40px;border-radius:22px;max-width:480px;width:100%;">
			<h1 style="font-size:24px;"><?php esc_html_e( 'Choose a new password', 'chatbotistic' ); ?></h1>
			<p class="auth-sub"><?php esc_html_e( 'Pick something secure — at least 8 characters with a number.', 'chatbotistic' ); ?></p>
			<form class="cb-form" method="post" action="<?php echo esc_url( site_url( 'wp-login.php?action=resetpass', 'login_post' ) ); ?>">
				<input type="hidden" name="rp_key" value="<?php echo esc_attr( $key ); ?>">
				<input type="hidden" name="rp_login" value="<?php echo esc_attr( $login ); ?>">
				<input type="hidden" name="redirect_to" value="<?php echo esc_url( home_url( '/login/?password=changed' ) ); ?>">
				<div class="field"><label><?php esc_html_e( 'New password', 'chatbotistic' ); ?></label><input type="password" name="pass1" placeholder="••••••••" autocomplete="new-password" required></div>
				<div class="field"><label><?php esc_html_e( 'Confirm password', 'chatbotistic' ); ?></label><input type="password" name="pass2" placeholder="••••••••" autocomplete="new-password" required></div>
				<button class="btn btn-primary" type="submit" style="justify-content:center;"><?php esc_html_e( 'Set new password →', 'chatbotistic' ); ?></button>
			</form>
		</div>
	</div>
</main>

<?php get_template_part( 'template-parts/auth-foot' );
