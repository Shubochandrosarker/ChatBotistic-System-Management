<?php
namespace Chatbotistic_Widget\Admin;

use Chatbotistic_Widget\API;
use Chatbotistic_Widget\Targeting;
use Chatbotistic_Widget\License;

defined( 'ABSPATH' ) || exit;

/**
 * Analytics page — KPI cards, referral sources, recent leads.
 *
 * If the API isn't connected, the page becomes a marketing surface that
 * funnels free users to register / connect for live stats.
 */
final class Analytics_Page {

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) return;
		Admin::header( __( 'Analytics', 'chatbotistic-widget' ) );

		Admin::upgrade_banner_if_free();

		$widget_keys = Targeting::configured_keys();
		$default_key = Targeting::default_key();
		$selected    = isset( $_GET['widget'] ) ? sanitize_text_field( wp_unslash( $_GET['widget'] ) ) : $default_key; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! $selected && ! empty( $widget_keys ) ) $selected = $widget_keys[0];

		if ( ! API::is_connected() ) {
			self::render_connect_cta();
			Admin::footer();
			return;
		}

		if ( empty( $widget_keys ) ) {
			?>
			<div class="cbw-notice cbw-notice--info">
				<?php esc_html_e( 'Configure a widget key on the Widgets tab first.', 'chatbotistic-widget' ); ?>
			</div>
			<?php
			Admin::footer();
			return;
		}

		// Fetch data for the selected widget.
		$stats     = API::get_widget_stats( $selected );
		$referrals = API::get_widget_referrals( $selected, 365 );
		$leads     = API::get_leads( $selected, 25 );
		?>

		<form method="get" class="cbw-analytics-filter">
			<input type="hidden" name="page" value="<?php echo esc_attr( Admin::ANALYTICS_SLUG ); ?>" />
			<label for="cbw-widget-picker"><?php esc_html_e( 'Widget:', 'chatbotistic-widget' ); ?></label>
			<?php
			// Friendly names sourced from the cached widget catalog, same as
			// the Settings page's dropdown -- fall back to the raw key (UUID)
			// if a name isn't available for some reason.
			$remote_widgets = method_exists( License::class, 'get_widget_list' ) ? (array) License::get_widget_list() : array();
			$widget_names   = array();
			foreach ( $remote_widgets as $rw ) {
				if ( isset( $rw['key'] ) && '' !== $rw['key'] ) {
					$widget_names[ (string) $rw['key'] ] = isset( $rw['name'] ) && '' !== $rw['name'] ? (string) $rw['name'] : (string) $rw['key'];
				}
			}
			?>
			<select id="cbw-widget-picker" name="widget" onchange="this.form.submit()">
				<?php foreach ( $widget_keys as $k ) : ?>
					<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $selected, $k ); ?>><?php echo esc_html( $widget_names[ $k ] ?? $k ); ?></option>
				<?php endforeach; ?>
			</select>
			<a class="cbw-btn cbw-btn--ghost cbw-btn--compact" href="<?php echo esc_url( CBW_APP_BASE_URL . '/widget/' . rawurlencode( $selected ) ); ?>" target="_blank" rel="noopener">
				<?php esc_html_e( 'Customize in App', 'chatbotistic-widget' ); ?> ↗
			</a>
		</form>

		<?php
		require CBW_PLUGIN_PATH . 'includes/admin/views/analytics-tabs/dashboard.php';
		require CBW_PLUGIN_PATH . 'includes/admin/views/analytics-tabs/referrals.php';
		require CBW_PLUGIN_PATH . 'includes/admin/views/analytics-tabs/leads.php';

		Admin::footer();
	}

	private static function render_connect_cta(): void {
		?>
		<div class="cbw-card cbw-card--hero">
			<div class="cbw-hero">
				<div class="cbw-hero__icon">📊</div>
				<h2><?php esc_html_e( 'See your live widget analytics — right here in WordPress.', 'chatbotistic-widget' ); ?></h2>
				<p>
					<?php esc_html_e( 'Connect your free Chatbotistic account to track total leads, new WhatsApp conversations, click-throughs, referral sources and conversion rates without leaving wp-admin.', 'chatbotistic-widget' ); ?>
				</p>
				<div class="cbw-hero__perks">
					<div class="cbw-hero__perk"><strong>📈</strong> <?php esc_html_e( 'Lead & click metrics', 'chatbotistic-widget' ); ?></div>
					<div class="cbw-hero__perk"><strong>🔗</strong> <?php esc_html_e( 'Top referral sources', 'chatbotistic-widget' ); ?></div>
					<div class="cbw-hero__perk"><strong>🌍</strong> <?php esc_html_e( 'Country breakdown', 'chatbotistic-widget' ); ?></div>
					<div class="cbw-hero__perk"><strong>⚡</strong> <?php esc_html_e( 'AI training & FAQ', 'chatbotistic-widget' ); ?></div>
				</div>
				<div class="cbw-hero__actions">
					<a class="cbw-btn cbw-btn--primary cbw-btn--lg" href="<?php echo esc_url( CBW_REGISTER_URL ); ?>" target="_blank" rel="noopener">
						<?php esc_html_e( 'Create Free Account', 'chatbotistic-widget' ); ?>
					</a>
					<a class="cbw-btn cbw-btn--ghost cbw-btn--lg" href="<?php echo esc_url( admin_url( 'admin.php?page=' . Admin::SETTINGS_SLUG ) ); ?>">
						<?php esc_html_e( 'I already have an account — connect now', 'chatbotistic-widget' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php
	}
}
