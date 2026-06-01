<?php
/**
 * Portal view: Subscription — native Chatbotistic membership detail.
 *
 * Renders membership data directly (no [memberistic_account] embed, which
 * pulls in the unrelated gun-range account template). License entitlements
 * appear on the Licenses tab.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/** @var array $cb_m Membership data from cb_membership(). */
/** @var bool  $cb_active Whether the membership is active. */

$cb_caps   = function_exists( 'cb_account_caps' ) ? cb_account_caps( get_current_user_id() ) : array();
$cb_cycle  = isset( $cb_m['billing_cycle'] ) ? (string) $cb_m['billing_cycle'] : '';
$cb_is_ltd = 'lifetime' === $cb_cycle || ( $cb_m['plan_name'] && false !== stripos( $cb_m['plan_name'], 'lifetime' ) );
?>

<div class="cb-panel cb-glass">
	<h2><?php esc_html_e( 'Your subscription', 'chatbotistic' ); ?></h2>
	<dl class="cb-defs">
		<div>
			<dt><?php esc_html_e( 'Plan', 'chatbotistic' ); ?></dt>
			<dd><strong><?php echo esc_html( $cb_active ? ( $cb_m['plan_name'] ?: __( 'Active', 'chatbotistic' ) ) : __( 'No active plan', 'chatbotistic' ) ); ?></strong></dd>
		</div>
		<div>
			<dt><?php esc_html_e( 'Status', 'chatbotistic' ); ?></dt>
			<dd>
				<?php if ( $cb_active ) : ?>
					<span class="cb-badge cb-badge--active"><?php echo esc_html( ucfirst( $cb_m['status'] ?: 'active' ) ); ?></span>
				<?php else : ?>
					<span class="cb-badge"><?php echo esc_html( $cb_m['status'] ? ucfirst( $cb_m['status'] ) : __( 'Inactive', 'chatbotistic' ) ); ?></span>
				<?php endif; ?>
			</dd>
		</div>
		<?php if ( $cb_is_ltd ) : ?>
			<div>
				<dt><?php esc_html_e( 'Billing', 'chatbotistic' ); ?></dt>
				<dd><?php esc_html_e( 'Lifetime — one-time, no renewal', 'chatbotistic' ); ?></dd>
			</div>
		<?php elseif ( ! empty( $cb_m['renewal'] ) ) : ?>
			<div>
				<dt><?php esc_html_e( 'Next renewal', 'chatbotistic' ); ?></dt>
				<dd><?php echo esc_html( date_i18n( get_option( 'date_format' ), $cb_m['renewal'] ) ); ?></dd>
			</div>
		<?php endif; ?>
	</dl>

	<?php if ( $cb_active && $cb_caps ) : ?>
		<h3 style="margin-top:22px;"><?php esc_html_e( 'Plan limits', 'chatbotistic' ); ?></h3>
		<div class="cb-stats" style="margin-top:12px;">
			<div class="cb-stat">
				<div class="cb-stat__label"><?php esc_html_e( 'AI ChatBot widgets', 'chatbotistic' ); ?></div>
				<div class="cb-stat__value"><?php echo esc_html( cb_cap_label( $cb_caps['max_widgets'] ?? 1 ) ); ?></div>
			</div>
			<div class="cb-stat">
				<div class="cb-stat__label"><?php esc_html_e( 'WhatsApp agents', 'chatbotistic' ); ?></div>
				<div class="cb-stat__value"><?php echo esc_html( cb_cap_label( $cb_caps['max_agents'] ?? 1 ) ); ?></div>
			</div>
			<div class="cb-stat">
				<div class="cb-stat__label"><?php esc_html_e( 'Website domains', 'chatbotistic' ); ?></div>
				<div class="cb-stat__value"><?php echo esc_html( cb_cap_label( $cb_caps['max_domains'] ?? 1 ) ); ?></div>
			</div>
			<div class="cb-stat">
				<div class="cb-stat__label"><?php esc_html_e( 'White label', 'chatbotistic' ); ?></div>
				<div class="cb-stat__value"><?php echo ! empty( $cb_caps['white_label'] ) ? esc_html__( 'Yes', 'chatbotistic' ) : esc_html__( 'No', 'chatbotistic' ); ?></div>
			</div>
		</div>
	<?php endif; ?>

	<div class="cb-portal__actions" style="margin-top:22px;">
		<?php
		if ( $cb_active && ! $cb_is_ltd ) {
			cb_button( __( 'Change plan', 'chatbotistic' ), cb_plans_url(), 'primary' );
			cb_button( __( 'Billing & renewal', 'chatbotistic' ), cb_member_url( 'renewal_page_id', 'memberistic-renewal', cb_plans_url() ), 'ghost' );
		} elseif ( $cb_active && $cb_is_ltd ) {
			cb_button( __( 'Manage license', 'chatbotistic' ), cb_view_url( 'licenses' ), 'primary' );
			cb_button( __( 'Contact support', 'chatbotistic' ), cb_view_url( 'support' ), 'ghost' );
		} else {
			cb_button( __( 'Choose a plan', 'chatbotistic' ), cb_plans_url(), 'primary', array( 'icon' => 'arrow-r' ) );
		}
		?>
	</div>
</div>
