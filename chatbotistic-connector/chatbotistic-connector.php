<?php
/**
 * Plugin Name:       Chatbotistic Connector
 * Plugin URI:        https://chatbotistic.com
 * Description:       Self-hosted dashboard for Chatbotistic users — provisions and manages WhatsApp widgets, agents, FAQs, leads and analytics through the Tochat.be API. Membership-gated via Memberistic. Users never leave your site.
 * Version: 3.4.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            WordPressistic
 * Author URI:        https://www.wordpressistic.com
 * License:           GPL v2 or later
 * Text Domain:       chatbotistic-connector
 *
 * @package Chatbotistic\Connector
 */

defined( 'ABSPATH' ) || exit;

define( 'CBC_VERSION',  '3.4.0' );
define( 'CBC_FILE',     __FILE__ );
define( 'CBC_DIR',      plugin_dir_path( __FILE__ ) );
define( 'CBC_URL',      plugin_dir_url( __FILE__ ) );
define( 'CBC_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Chatbotistic white-label API URL (our white-label of tochat.be) —
 * where the actual API + widget script live.
 *
 * Can be overridden per-deployment by defining CBC_API_BASE in wp-config.php
 * BEFORE this plugin loads (e.g. for a staging deployment pointed at a
 * different provider host).
 */
if ( ! defined( 'CBC_API_BASE' ) ) {
	define( 'CBC_API_BASE', 'https://services.tochat.be' );
}

/**
 * Customer-facing dashboard (CRM) URL. This is what end-users see in their
 * dashboards, emails, and "manage widgets in app.chatbotistic.com"
 * hints. Sub-brands and reseller deployments can override via wp-config.
 * The provider API remains services.tochat.be and is never used as the
 * customer-facing dashboard origin.
 */
if ( ! defined( 'CBC_APP_BASE' ) ) {
	define( 'CBC_APP_BASE', 'https://app.chatbotistic.com' );
}

/**
 * Lightweight class-map autoloader for the Chatbotistic\Connector namespace.
 */
spl_autoload_register( function ( string $class ): void {
	if ( strncmp( 'Chatbotistic\\Connector\\', $class, 23 ) !== 0 ) {
		return;
	}
	$short = substr( $class, 23 );
	$map   = array(
		'Activator'      => 'includes/class-activator.php',
		'API'            => 'includes/class-api.php',
		'Membership'     => 'includes/class-membership.php',
		'Store'          => 'includes/class-store.php',
		'Ajax'           => 'includes/class-ajax.php',
		'Dashboard'      => 'includes/class-dashboard.php',
		'Admin'          => 'admin/class-admin.php',
		'Admin_Ajax'     => 'admin/class-admin-ajax.php',
		'Page_Members'   => 'admin/pages/class-page-members.php',
		'Page_Payments'  => 'admin/pages/class-page-payments.php',
		'Page_Licenses'  => 'admin/pages/class-page-licenses.php',
		'Page_Plans'     => 'admin/pages/class-page-plans.php',
	);
	if ( isset( $map[ $short ] ) && is_readable( CBC_DIR . $map[ $short ] ) ) {
		require_once CBC_DIR . $map[ $short ];
	}
} );

register_activation_hook( CBC_FILE, array( 'Chatbotistic\\Connector\\Activator', 'activate' ) );
register_deactivation_hook( CBC_FILE, array( 'Chatbotistic\\Connector\\Activator', 'deactivate' ) );

/**
 * Boot the plugin once all plugins are loaded.
 */
add_action( 'plugins_loaded', function (): void {
	// Keep the schema current (dbDelta is additive and safe).
	if ( get_option( 'cbc_db_version' ) !== Chatbotistic\Connector\Activator::DB_VERSION ) {
		Chatbotistic\Connector\Activator::install();
	}

	// One-time migration: repoint previously saved dashboard URLs to the
	// canonical app origin. The old CRM hostname is a temporary migration
	// alias and must not remain in customer-facing links.
	if ( '1' !== get_option( 'cbc_dashboard_url_migrated_340', '' ) ) {
		$settings = get_option( 'cbc_settings', array() );
		if ( is_array( $settings ) && isset( $settings['dashboard_url'] )
			&& ( false !== strpos( (string) $settings['dashboard_url'], 'chatbot.wpistic.cloud' )
				|| false !== strpos( (string) $settings['dashboard_url'], 'crm.chatbotistic.com' ) ) ) {
			$settings['dashboard_url'] = 'https://app.chatbotistic.com';
			update_option( 'cbc_settings', $settings );
		}
		update_option( 'cbc_dashboard_url_migrated_340', '1' );
	}

	load_plugin_textdomain( 'chatbotistic-connector', false, dirname( CBC_BASENAME ) . '/languages' );

	new Chatbotistic\Connector\Ajax();
	new Chatbotistic\Connector\Dashboard();

	if ( is_admin() ) {
		new Chatbotistic\Connector\Admin();
	}
} );
