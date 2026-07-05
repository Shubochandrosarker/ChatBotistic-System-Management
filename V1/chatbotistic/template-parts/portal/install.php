<?php
/**
 * Portal view: Install Plugin — instructions + download link for the
 * Chatbotistic Widget WordPress addon.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

$cb_uid       = get_current_user_id();
$cb_key       = (string) get_user_meta( $cb_uid, 'mlb_license_key', true );
$cb_download  = (string) apply_filters( 'cb_widget_download_url', home_url( '/account/?download=widget' ) );
$cb_docs_url  = home_url( '/docs/' );
?>

<div class="cb-panel cb-glass">
	<h2><?php esc_html_e( 'Install the Chatbotistic Widget plugin', 'chatbotistic' ); ?></h2>
	<p class="cb-soft"><?php esc_html_e( 'A small WordPress plugin you install on your own site. Activate your license, paste a widget key, and the chat button goes live for your visitors.', 'chatbotistic' ); ?></p>

	<div class="cb-portal__actions" style="margin-top:18px;">
		<?php cb_button( __( 'Download Widget plugin', 'chatbotistic' ), $cb_download, 'primary', array( 'icon' => 'arrow-r', 'size' => 'lg' ) ); ?>
		<?php cb_button( __( 'Read setup docs', 'chatbotistic' ), $cb_docs_url, 'ghost' ); ?>
	</div>
</div>

<div class="cb-panel cb-glass" style="margin-top:18px;">
	<h2><?php esc_html_e( '4-step install', 'chatbotistic' ); ?></h2>
	<ol class="cb-checklist">
		<li>
			<strong><?php esc_html_e( 'Download the plugin', 'chatbotistic' ); ?></strong><br>
			<?php esc_html_e( 'Click "Download Widget plugin" above to grab the latest .zip.', 'chatbotistic' ); ?>
		</li>
		<li>
			<strong><?php esc_html_e( 'Install on your WordPress site', 'chatbotistic' ); ?></strong><br>
			<?php esc_html_e( 'In wp-admin, go to Plugins → Add New → Upload Plugin, choose the .zip, then Install Now and Activate.', 'chatbotistic' ); ?>
		</li>
		<li>
			<strong><?php esc_html_e( 'Paste your license key', 'chatbotistic' ); ?></strong><br>
			<?php esc_html_e( 'Open Chatbotistic → License in wp-admin and paste the key shown below.', 'chatbotistic' ); ?>
			<?php if ( $cb_key ) : ?>
				<div class="cb-field" style="margin-top:8px;max-width:520px;">
					<input type="text" readonly value="<?php echo esc_attr( $cb_key ); ?>" onclick="this.select();" style="font-family:monospace;width:100%;">
				</div>
			<?php else : ?>
				<p class="cb-dim" style="margin-top:8px;"><?php esc_html_e( 'Your license key will appear here once your plan is active.', 'chatbotistic' ); ?></p>
			<?php endif; ?>
		</li>
		<li>
			<strong><?php esc_html_e( 'Connect a widget', 'chatbotistic' ); ?></strong><br>
			<?php esc_html_e( 'After activation the plugin auto-fetches your widgets — pick one from the dropdown and Save. The chat button goes live on your site immediately.', 'chatbotistic' ); ?>
		</li>
	</ol>
</div>

<div class="cb-panel cb-glass" style="margin-top:18px;">
	<h3><?php esc_html_e( 'System requirements', 'chatbotistic' ); ?></h3>
	<ul class="cb-linklist">
		<li><span><?php esc_html_e( 'WordPress 6.0 or newer', 'chatbotistic' ); ?></span></li>
		<li><span><?php esc_html_e( 'PHP 8.0 or newer', 'chatbotistic' ); ?></span></li>
		<li><span><?php esc_html_e( 'HTTPS site (required for the license heartbeat)', 'chatbotistic' ); ?></span></li>
	</ul>
</div>
