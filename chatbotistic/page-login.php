<?php
/**
 * Template Name: Login (V4)
 *
 * Hands off to wp-login.php via JS for actual auth, since WordPress's own
 * login flow integrates with Memberistic's session, password reset, and
 * 2FA. The form posts to wp-login.php with redirect_to=/account/.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

if ( is_user_logged_in() ) {
	wp_safe_redirect( home_url( '/account/' ) );
	exit;
}

get_template_part( 'template-parts/auth-head' );

$redirect = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : home_url( '/account/' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$err      = isset( $_GET['login'] ) && 'failed' === $_GET['login']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

ob_start(); ?>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="text-decoration:none;">
		<span class="footer-status" style="display:inline-flex;align-items:center;gap:8px;font-family:var(--font-mono);font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-soft);"><span style="width:7px;height:7px;border-radius:50%;background:var(--green);box-shadow:0 0 9px var(--green);"></span><?php esc_html_e( 'System online', 'chatbotistic' ); ?></span>
	</a>
	<div>
		<h2 class="text-grad"><?php esc_html_e( 'Welcome back to your conversation engine.', 'chatbotistic' ); ?></h2>
		<p><?php esc_html_e( 'Manage widgets, licenses, leads, and your WordPress connection from one operational dashboard.', 'chatbotistic' ); ?></p>
		<div class="auth-points">
			<?php
			$points = array(
				array( __( 'Live widget status', 'chatbotistic' ), __( 'Heartbeat monitoring across every connected domain.', 'chatbotistic' ) ),
				array( __( 'Lead inbox', 'chatbotistic' ), __( 'Every captured conversation, exportable to CSV.', 'chatbotistic' ) ),
				array( __( 'WordPress-native', 'chatbotistic' ), __( 'One license key. Activate the addon and go live.', 'chatbotistic' ) ),
			);
			foreach ( $points as $p ) : ?>
				<div class="auth-point"><span class="ico">●</span><div><b><?php echo esc_html( $p[0] ); ?></b><?php echo esc_html( $p[1] ); ?></div></div>
			<?php endforeach; ?>
		</div>
	</div>
<?php $aside = ob_get_clean();

ob_start(); ?>
	<h1><?php esc_html_e( 'Sign in', 'chatbotistic' ); ?></h1>
	<p class="auth-sub"><?php esc_html_e( 'Access your Chatbotistic member portal.', 'chatbotistic' ); ?></p>
	<form class="cb-form" method="post" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>">
		<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect ); ?>">
		<?php if ( $err ) : ?>
			<div class="field-err" style="margin-top:-4px;">✕ <?php esc_html_e( 'Incorrect email or password.', 'chatbotistic' ); ?></div>
		<?php endif; ?>
		<div class="field"><label><?php esc_html_e( 'Work email', 'chatbotistic' ); ?></label><input type="text" name="log" placeholder="you@company.com" autocomplete="username" required></div>
		<div class="field"><label><?php esc_html_e( 'Password', 'chatbotistic' ); ?></label><input type="password" name="pwd" placeholder="••••••••" autocomplete="current-password" required></div>
		<div class="pw-row" style="margin-top:-4px;">
			<label class="check-line" style="font-size:13px;"><input type="checkbox" name="rememberme" value="forever" checked> <?php esc_html_e( 'Keep me signed in', 'chatbotistic' ); ?></label>
			<a href="<?php echo esc_url( home_url( '/forgot-password/' ) ); ?>"><?php esc_html_e( 'Forgot password?', 'chatbotistic' ); ?></a>
		</div>
		<button class="btn btn-primary" type="submit" style="justify-content:center;margin-top:6px;"><?php esc_html_e( 'Sign in →', 'chatbotistic' ); ?></button>
	</form>
	<p class="auth-foot"><?php esc_html_e( 'No account yet?', 'chatbotistic' ); ?> <a href="<?php echo esc_url( home_url( '/register/' ) ); ?>"><?php esc_html_e( 'Create one free', 'chatbotistic' ); ?></a></p>
<?php $form = ob_get_clean();

ob_start(); ?>
	<span style="font-size:13.5px;color:var(--text-soft);"><?php esc_html_e( 'New here?', 'chatbotistic' ); ?></span>
	<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( home_url( '/register/' ) ); ?>"><?php esc_html_e( 'Create account', 'chatbotistic' ); ?></a>
<?php $top_right = ob_get_clean();

get_template_part( 'template-parts/auth-layout', null, array(
	'aside'     => $aside,
	'form'      => $form,
	'top_right' => $top_right,
) );

get_template_part( 'template-parts/auth-foot' );
