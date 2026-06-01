<?php
namespace Chatbotistic_Widget;

defined( 'ABSPATH' ) || exit;

require_once CBW_PLUGIN_PATH . 'includes/class-license.php';
require_once CBW_PLUGIN_PATH . 'includes/class-brand.php';
require_once CBW_PLUGIN_PATH . 'includes/class-api.php';
require_once CBW_PLUGIN_PATH . 'includes/class-targeting.php';
require_once CBW_PLUGIN_PATH . 'includes/class-widget-renderer.php';

if ( is_admin() ) {
	require_once CBW_PLUGIN_PATH . 'includes/admin/class-admin.php';
	require_once CBW_PLUGIN_PATH . 'includes/admin/class-license-page.php';
	require_once CBW_PLUGIN_PATH . 'includes/admin/class-settings-page.php';
	require_once CBW_PLUGIN_PATH . 'includes/admin/class-analytics-page.php';
}

/**
 * Plugin bootstrap.
 *
 * Free-tier caps are 1 widget, 1 agent, 1 domain. Anything beyond that
 * requires a valid paid license verified by Licenseistic on chatbotistic.com.
 */
final class Plugin {

	private static ?Plugin $instance = null;

	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		load_plugin_textdomain( 'chatbotistic-widget', false, dirname( CBW_PLUGIN_BASENAME ) . '/languages' );

		// License heartbeat (paid tiers only — free tier doesn't ping).
		add_action( 'cbw_license_heartbeat', [ License::class, 'run_heartbeat' ] );

		// Front-end widget injection.
		new Widget_Renderer();

		if ( is_admin() ) {
			new Admin\Admin();
		}
	}

	public static function on_activate(): void {
		if ( ! wp_next_scheduled( 'cbw_license_heartbeat' ) ) {
			wp_schedule_event( time() + ( 12 * HOUR_IN_SECONDS ), 'twicedaily', 'cbw_license_heartbeat' );
		}
		if ( ! get_option( 'cbw_license_instance_id' ) ) {
			update_option( 'cbw_license_instance_id', wp_generate_password( 24, false, false ) );
		}
		// Remember the domain so we can include it in license verify calls.
		update_option( 'cbw_register_domain', wp_parse_url( home_url(), PHP_URL_HOST ) );
	}

	public static function on_deactivate(): void {
		wp_clear_scheduled_hook( 'cbw_license_heartbeat' );
		// Best-effort: tell Licenseistic this site is releasing the slot.
		if ( class_exists( __NAMESPACE__ . '\\License' ) ) {
			License::deactivate_silently();
		}
	}
}
