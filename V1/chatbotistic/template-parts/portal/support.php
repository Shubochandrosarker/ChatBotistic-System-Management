<?php
/**
 * Portal view: Support.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

$cb_sent = isset( $_GET['support'] ) && 'sent' === sanitize_key( wp_unslash( $_GET['support'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>

<div class="cb-panel cb-glass">
	<h2><?php esc_html_e( 'Need a hand?', 'chatbotistic' ); ?></h2>
	<p class="cb-soft"><?php esc_html_e( 'Send us a message — members get a priority response within one business day.', 'chatbotistic' ); ?></p>

	<?php if ( $cb_sent ) : ?>
		<div class="cb-note cb-note--ok" style="margin-top:14px;"><?php esc_html_e( 'Thanks — your message is on its way. We will reply by email.', 'chatbotistic' ); ?></div>
	<?php endif; ?>

	<form class="cb-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:18px;">
		<input type="hidden" name="action" value="cb_support">
		<?php wp_nonce_field( 'cb_support', 'cb_support_nonce' ); ?>
		<div class="cb-field">
			<label for="cb-subject"><?php esc_html_e( 'Subject', 'chatbotistic' ); ?></label>
			<input id="cb-subject" type="text" name="subject" required>
		</div>
		<div class="cb-field">
			<label for="cb-message"><?php esc_html_e( 'Message', 'chatbotistic' ); ?></label>
			<textarea id="cb-message" name="message" rows="6" required></textarea>
		</div>
		<div>
			<button type="submit" class="cb-btn cb-btn--primary"><?php esc_html_e( 'Send message', 'chatbotistic' ); ?></button>
		</div>
	</form>
</div>
