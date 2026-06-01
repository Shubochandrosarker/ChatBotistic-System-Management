<?php
/**
 * Portal view: Tools — embeds the SaaS Connector dashboard.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/** @var bool $cb_active Whether the membership is active. */
?>

<?php if ( ! $cb_active ) : ?>
	<div class="cb-panel cb-glass cb-paywall">
		<h2><?php esc_html_e( 'Tools are part of a paid plan', 'chatbotistic' ); ?></h2>
		<p><?php esc_html_e( 'Activate a plan to access the WhatsApp dashboard, AI Agent builder, and broadcast tools.', 'chatbotistic' ); ?></p>
		<?php cb_button( __( 'See plans', 'chatbotistic' ), cb_plans_url(), 'primary', array( 'size' => 'lg', 'icon' => 'arrow-r' ) ); ?>
	</div>
<?php elseif ( shortcode_exists( 'chatbotistic_dashboard' ) ) : ?>
	<div class="cb-tools-frame cb-glass">
		<?php echo do_shortcode( '[chatbotistic_dashboard]' ); ?>
	</div>
<?php else : ?>
	<div class="cb-panel cb-glass">
		<h2><?php esc_html_e( 'Dashboard connector not active', 'chatbotistic' ); ?></h2>
		<p class="cb-soft"><?php esc_html_e( 'The Chatbotistic SaaS Connector plugin is required to load your Tools dashboard. Please activate it, or contact support.', 'chatbotistic' ); ?></p>
	</div>
<?php endif; ?>
