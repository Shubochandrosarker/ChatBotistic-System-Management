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

		$widget_keys = self::allowed_widget_keys();
		$default_key = Targeting::default_key();
		$requested   = isset( $_GET['widget'] ) ? sanitize_text_field( wp_unslash( $_GET['widget'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// SECURITY: the requested widget must be one this site is allowed
		// to see — either configured for display here or present in this
		// license's widget catalog. A raw ?widget=<uuid> pointing at
		// someone else's widget is ignored, never queried: with the
		// platform's shared API account connected, an unvalidated id
		// would leak other customers' stats.
		$selected = '';
		if ( '' !== $requested && in_array( $requested, $widget_keys, true ) ) {
			$selected = $requested;
		} elseif ( '' !== $default_key && in_array( $default_key, $widget_keys, true ) ) {
			$selected = $default_key;
		} elseif ( ! empty( $widget_keys ) ) {
			$selected = $widget_keys[0];
		}

		if ( ! API::is_connected() ) {
			self::render_connect_cta();
			Admin::footer();
			return;
		}

		if ( '' === $selected ) {
			?>
			<div class="cbw-notice cbw-notice--info">
				<?php esc_html_e( 'No accessible widgets yet — configure a widget key on the Widgets tab or activate your license to pull your catalog.', 'chatbotistic-widget' ); ?>
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

	/**
	 * Return only configured widget keys that are also present in the
	 * license-scoped catalog pulled from this customer's own account. The
	 * intersection is intentional: a manually entered UUID is never enough
	 * to authorize an analytics query.
	 *
	 * @return string[]
	 */
	private static function allowed_widget_keys(): array {
		$keys   = array_values( Targeting::configured_keys() );
		$remote = method_exists( License::class, 'get_widget_list' ) ? (array) License::get_widget_list() : array();
		$owned = array();
		foreach ( $remote as $rw ) {
			if ( is_array( $rw ) && isset( $rw['key'] ) && '' !== (string) $rw['key'] ) {
				$owned[] = (string) $rw['key'];
			}
		}
		return array_values( array_intersect( array_unique( array_filter( $keys ) ), array_unique( $owned ) ) );
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
