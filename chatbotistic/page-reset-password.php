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
	<?php echo cb_logo( array( 'class' => 'cb-logo--auth' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside helper. ?>
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
