<?php
/**
 * Portal view: Profile.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/** @var WP_User $cb_user Current user. */

$cb_flag = isset( $_GET['profile'] ) ? sanitize_key( wp_unslash( $_GET['profile'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>

<div class="cb-panel cb-glass">
	<h2><?php esc_html_e( 'Profile', 'chatbotistic' ); ?></h2>

	<?php if ( 'updated' === $cb_flag ) : ?>
		<div class="cb-note cb-note--ok" style="margin-top:12px;"><?php esc_html_e( 'Your profile was updated.', 'chatbotistic' ); ?></div>
	<?php elseif ( 'passfail' === $cb_flag ) : ?>
		<div class="cb-note cb-note--err" style="margin-top:12px;"><?php esc_html_e( 'Passwords did not match or were too short (minimum 8 characters). Other changes were saved.', 'chatbotistic' ); ?></div>
	<?php endif; ?>

	<form class="cb-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:18px;">
		<input type="hidden" name="action" value="cb_profile">
		<?php wp_nonce_field( 'cb_profile', 'cb_profile_nonce' ); ?>

		<div class="cb-field-row">
			<div class="cb-field">
				<label for="cb-first"><?php esc_html_e( 'First name', 'chatbotistic' ); ?></label>
				<input id="cb-first" type="text" name="first_name" value="<?php echo esc_attr( $cb_user->first_name ); ?>">
			</div>
			<div class="cb-field">
				<label for="cb-last"><?php esc_html_e( 'Last name', 'chatbotistic' ); ?></label>
				<input id="cb-last" type="text" name="last_name" value="<?php echo esc_attr( $cb_user->last_name ); ?>">
			</div>
		</div>
		<div class="cb-field">
			<label for="cb-email"><?php esc_html_e( 'Email address', 'chatbotistic' ); ?></label>
			<input id="cb-email" type="email" name="user_email" value="<?php echo esc_attr( $cb_user->user_email ); ?>">
		</div>
		<div class="cb-field-row">
			<div class="cb-field">
				<label for="cb-pass"><?php esc_html_e( 'New password', 'chatbotistic' ); ?></label>
				<input id="cb-pass" type="password" name="user_pass" autocomplete="new-password" placeholder="<?php esc_attr_e( 'Leave blank to keep current', 'chatbotistic' ); ?>">
			</div>
			<div class="cb-field">
				<label for="cb-pass2"><?php esc_html_e( 'Confirm password', 'chatbotistic' ); ?></label>
				<input id="cb-pass2" type="password" name="user_pass_confirm" autocomplete="new-password">
			</div>
		</div>
		<div>
			<button type="submit" class="cb-btn cb-btn--primary"><?php esc_html_e( 'Save changes', 'chatbotistic' ); ?></button>
		</div>
	</form>
</div>
