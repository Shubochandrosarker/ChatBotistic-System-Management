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
		<?php if ( has_custom_logo() ) : ?>
			<?php echo wp_kses_post( preg_replace( '#</?a[^>]*>#i', '', get_custom_logo() ) ); ?>
		<?php else : ?>
			<img class="cb-login-brand__img" src="<?php echo esc_url( CB_URI . '/assets/images/chatbotistic-logo.png' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="242" height="60" />
		<?php endif; ?>
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
