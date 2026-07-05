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
	<?php echo cb_logo( array( 'class' => 'cb-logo--auth' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside helper. ?>
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
