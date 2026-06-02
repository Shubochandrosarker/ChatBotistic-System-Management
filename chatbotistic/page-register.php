<?php
/**
 * Template Name: Register (V4)
 *
 * Form posts to wp-login.php?action=register so WordPress + Memberistic
 * handle account creation. Plan + redirect carry through.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

if ( is_user_logged_in() ) {
	wp_safe_redirect( home_url( '/account/' ) );
	exit;
}

get_template_part( 'template-parts/auth-head' );

$cb_plan = isset( $_GET['plan'] ) ? sanitize_key( wp_unslash( $_GET['plan'] ) ) : 'free'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

ob_start(); ?>
	<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Free forever plan', 'chatbotistic' ); ?></span>
	<div>
		<h2 class="text-grad"><?php esc_html_e( 'Create your account. Launch your first widget in minutes.', 'chatbotistic' ); ?></h2>
		<p><?php esc_html_e( 'Start on the free plan — one widget, one WhatsApp agent, one domain. Upgrade only when you grow.', 'chatbotistic' ); ?></p>
		<div class="auth-points">
			<?php
			$points = array(
				array( __( 'No credit card required', 'chatbotistic' ), __( 'The free plan stays free. Includes Chatbotistic branding.', 'chatbotistic' ) ),
				array( __( 'WordPress addon included', 'chatbotistic' ), __( 'Download, activate, paste your license, go live.', 'chatbotistic' ) ),
				array( __( 'Your data, exportable', 'chatbotistic' ), __( 'Own every lead and conversation. Export anytime.', 'chatbotistic' ) ),
			);
			foreach ( $points as $p ) : ?>
				<div class="auth-point"><span class="ico">✓</span><div><b><?php echo esc_html( $p[0] ); ?></b><?php echo esc_html( $p[1] ); ?></div></div>
			<?php endforeach; ?>
		</div>
	</div>
<?php $aside = ob_get_clean();

ob_start(); ?>
	<h1><?php esc_html_e( 'Create account', 'chatbotistic' ); ?></h1>
	<p class="auth-sub"><?php esc_html_e( 'Free forever. No card needed.', 'chatbotistic' ); ?></p>
	<form class="cb-form" method="post" action="<?php echo esc_url( wp_registration_url() ); ?>">
		<input type="hidden" name="plan" value="<?php echo esc_attr( $cb_plan ); ?>">
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( home_url( '/account/?welcome=1' ) ); ?>">
		<div class="grid-2">
			<div class="field"><label><?php esc_html_e( 'Full name', 'chatbotistic' ); ?></label><input name="display_name" placeholder="Sarah Kim" required></div>
			<div class="field"><label><?php esc_html_e( 'Company', 'chatbotistic' ); ?></label><input name="company" placeholder="Acme Inc."></div>
		</div>
		<div class="field"><label><?php esc_html_e( 'Work email', 'chatbotistic' ); ?></label><input type="email" name="user_email" placeholder="you@company.com" required autocomplete="email"></div>
		<div class="field"><label><?php esc_html_e( 'Password', 'chatbotistic' ); ?></label><input type="password" name="user_pass" placeholder="••••••••" autocomplete="new-password" required><div class="field-hint"><?php esc_html_e( 'At least 8 characters, one number.', 'chatbotistic' ); ?></div></div>
		<label class="check-line"><input type="checkbox" required>
			<?php
			printf(
				/* translators: 1: Terms link, 2: Privacy link */
				wp_kses( __( 'I agree to the %1$s and %2$s.', 'chatbotistic' ), array( 'a' => array( 'href' => array(), 'style' => array() ) ) ),
				'<a href="' . esc_url( home_url( '/terms/' ) ) . '" style="color:var(--blue-bright);">' . esc_html__( 'Terms', 'chatbotistic' ) . '</a>',
				'<a href="' . esc_url( home_url( '/privacy-policy/' ) ) . '" style="color:var(--blue-bright);">' . esc_html__( 'Privacy Policy', 'chatbotistic' ) . '</a>'
			);
			?>
		</label>
		<button class="btn btn-primary" type="submit" style="justify-content:center;margin-top:4px;"><?php esc_html_e( 'Create free account →', 'chatbotistic' ); ?></button>
	</form>
	<p class="auth-foot"><?php esc_html_e( 'Already a member?', 'chatbotistic' ); ?> <a href="<?php echo esc_url( home_url( '/login/' ) ); ?>"><?php esc_html_e( 'Sign in', 'chatbotistic' ); ?></a></p>
<?php $form = ob_get_clean();

ob_start(); ?>
	<span style="font-size:13.5px;color:var(--text-soft);"><?php esc_html_e( 'Have an account?', 'chatbotistic' ); ?></span>
	<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( home_url( '/login/' ) ); ?>"><?php esc_html_e( 'Sign in', 'chatbotistic' ); ?></a>
<?php $top_right = ob_get_clean();

get_template_part( 'template-parts/auth-layout', null, array(
	'aside'     => $aside,
	'form'      => $form,
	'top_right' => $top_right,
) );

get_template_part( 'template-parts/auth-foot' );
