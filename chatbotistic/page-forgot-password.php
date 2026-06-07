<?php
/**
 * Template Name: Forgot Password (V4)
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/auth-head' );

$sent = isset( $_GET['sent'] ) && '1' === $_GET['sent']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

ob_start(); ?>
	<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( home_url( '/login/' ) ); ?>"><?php esc_html_e( 'Back to sign in', 'chatbotistic' ); ?></a>
<?php $top_right = ob_get_clean();
?>

<div class="auth-top" style="height:72px;display:flex;align-items:center;justify-content:space-between;padding:0 32px;border-bottom:1px solid var(--line-soft);position:sticky;top:0;z-index:30;background:rgba(4,6,12,0.7);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);">
	<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" style="display:inline-flex;align-items:center;gap:10px;text-decoration:none;color:var(--text);font-family:var(--font-display);font-weight:600;">
		<span class="logo-mark"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6.5C4 5 5 4 6.5 4h11C19 4 20 5 20 6.5v8c0 1.5-1 2.5-2.5 2.5H13l-4 3v-3H6.5C5 17 4 16 4 14.5z"/></svg></span>
		Chatbotistic
	</a>
	<?php echo $top_right; ?>
</div>

<main class="page-fade">
	<div class="result-wrap">
		<div class="glass glass-edge auth-card" style="padding:40px;border-radius:22px;max-width:480px;width:100%;">
			<?php if ( $sent ) : ?>
				<div style="text-align:center;">
					<div class="result-ico ok" style="width:60px;height:60px;font-size:26px;">✉</div>
					<h1 style="font-size:24px;"><?php esc_html_e( 'Check your inbox', 'chatbotistic' ); ?></h1>
					<p class="auth-sub" style="margin-bottom:18px;"><?php esc_html_e( 'We sent a reset link to your email. It expires in 30 minutes.', 'chatbotistic' ); ?></p>
					<a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/login/' ) ); ?>" style="justify-content:center;width:100%;"><?php esc_html_e( 'Back to sign in', 'chatbotistic' ); ?></a>
				</div>
			<?php else : ?>
				<h1 style="font-size:24px;"><?php esc_html_e( 'Reset your password', 'chatbotistic' ); ?></h1>
				<p class="auth-sub"><?php esc_html_e( 'Enter your account email and we’ll send a secure reset link.', 'chatbotistic' ); ?></p>
				<form class="cb-form" method="post" action="<?php echo esc_url( site_url( 'wp-login.php?action=lostpassword', 'login_post' ) ); ?>">
					<input type="hidden" name="redirect_to" value="<?php echo esc_url( add_query_arg( 'sent', '1', home_url( '/forgot-password/' ) ) ); ?>">
					<div class="field"><label><?php esc_html_e( 'Work email', 'chatbotistic' ); ?></label><input type="email" name="user_login" placeholder="you@company.com" required></div>
					<button class="btn btn-primary" type="submit" style="justify-content:center;"><?php esc_html_e( 'Send reset link →', 'chatbotistic' ); ?></button>
				</form>
			<?php endif; ?>
		</div>
	</div>
</main>

<?php get_template_part( 'template-parts/auth-foot' );
