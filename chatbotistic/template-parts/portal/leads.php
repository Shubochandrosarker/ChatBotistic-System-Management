<?php
/**
 * Portal view: Leads — Connector dashboard with the Leads tab pre-selected.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/** @var bool $cb_active */
?>

<?php if ( ! $cb_active ) : ?>
	<div class="cb-panel cb-glass cb-paywall">
		<h2><?php esc_html_e( 'Lead capture is part of a paid plan', 'chatbotistic' ); ?></h2>
		<p><?php esc_html_e( 'Activate a plan to capture and review WhatsApp leads from your widgets.', 'chatbotistic' ); ?></p>
		<?php cb_button( __( 'See plans', 'chatbotistic' ), cb_plans_url(), 'primary', array( 'size' => 'lg', 'icon' => 'arrow-r' ) ); ?>
	</div>
<?php elseif ( shortcode_exists( 'chatbotistic_dashboard' ) ) : ?>
	<div class="cb-tools-frame cb-glass" data-cbc-default-tab="leads">
		<?php echo do_shortcode( '[chatbotistic_dashboard default_tab="leads"]' ); ?>
	</div>
<?php else : ?>
	<div class="cb-panel cb-glass">
		<h2><?php esc_html_e( 'Connector not active', 'chatbotistic' ); ?></h2>
		<p class="cb-soft"><?php esc_html_e( 'The Chatbotistic Connector plugin is required to view your leads.', 'chatbotistic' ); ?></p>
	</div>
<?php endif; ?>
