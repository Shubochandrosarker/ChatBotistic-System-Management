<?php
/**
 * Portal view: Analytics — Connector dashboard with the Analytics tab pre-selected.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/** @var bool $cb_active */
?>

<?php if ( ! $cb_active ) : ?>
	<div class="cb-panel cb-glass cb-paywall">
		<h2><?php esc_html_e( 'Analytics are part of a paid plan', 'chatbotistic' ); ?></h2>
		<p><?php esc_html_e( 'Activate a plan to see widget impressions, clicks, and conversion data.', 'chatbotistic' ); ?></p>
		<?php cb_button( __( 'See plans', 'chatbotistic' ), cb_plans_url(), 'primary', array( 'size' => 'lg', 'icon' => 'arrow-r' ) ); ?>
	</div>
<?php elseif ( shortcode_exists( 'chatbotistic_dashboard' ) ) : ?>
	<div class="cb-tools-frame cb-glass" data-cbc-default-tab="analytics">
		<?php echo do_shortcode( '[chatbotistic_dashboard default_tab="analytics"]' ); ?>
	</div>
<?php else : ?>
	<div class="cb-panel cb-glass">
		<h2><?php esc_html_e( 'Connector not active', 'chatbotistic' ); ?></h2>
		<p class="cb-soft"><?php esc_html_e( 'The Chatbotistic Connector plugin is required to view analytics.', 'chatbotistic' ); ?></p>
	</div>
<?php endif; ?>
