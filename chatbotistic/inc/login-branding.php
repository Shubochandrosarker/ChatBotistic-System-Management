<?php
/**
 * Branded login screen.
 *
 * Skins wp-login.php — including the set-password / reset-password screen
 * that new members land on from the account email — so the auth flow looks
 * like Chatbotistic, not stock WordPress. Outgoing mail is sent under the
 * site name for the same reason.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load fonts + the login skin on wp-login.php.
 */
function cb_login_assets() {
	wp_enqueue_style(
		'cb-fonts',
		'https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700&family=Manrope:wght@400;500;600&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'cb-login', CB_URI . '/assets/css/login.css', array(), cb_asset_ver( '/assets/css/login.css' ) );
}
add_action( 'login_enqueue_scripts', 'cb_login_assets' );

/**
 * Point the login logo at the site, not wordpress.org.
 *
 * @return string
 */
function cb_login_header_url() {
	return home_url( '/' );
}
add_filter( 'login_headerurl', 'cb_login_header_url' );

/**
 * Login logo title text.
 *
 * @return string
 */
function cb_login_title() {
	return get_bloginfo( 'name' );
}
add_filter( 'login_headertext', 'cb_login_title' );

/**
 * Branded wordmark above the login form (only when there is no system message).
 *
 * @param string $message Existing login message.
 * @return string
 */
function cb_login_message( $message ) {
	if ( '' !== trim( (string) $message ) ) {
		return $message;
	}
	ob_start();
	?>
	<div class="cb-login-brand">
		<span class="cb-login-brand__mark" aria-hidden="true">
			<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6.5C4 5 5 4 6.5 4h11C19 4 20 5 20 6.5v8c0 1.5-1 2.5-2.5 2.5H13l-4 3v-3H6.5C5 17 4 16 4 14.5z"/></svg>
		</span>
		<span class="cb-login-brand__name"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
	</div>
	<?php
	return ob_get_clean();
}
add_filter( 'login_message', 'cb_login_message' );

/**
 * Send theme/system mail under the site name instead of "WordPress".
 *
 * @return string
 */
function cb_mail_from_name() {
	return get_bloginfo( 'name' );
}
add_filter( 'wp_mail_from_name', 'cb_mail_from_name' );
