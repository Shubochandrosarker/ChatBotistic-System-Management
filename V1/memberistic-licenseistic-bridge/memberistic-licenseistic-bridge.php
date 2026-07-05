<?php
/**
 * Plugin Name:       Memberistic → Licenseistic Bridge
 * Plugin URI:        https://chatbotistic.com
 * Description:       Auto-issues a Licenseistic license key when a Memberistic membership activates, revokes on cancel/expiry, and enriches the license activation response with plan caps (widgets, agents, domains) for downstream client plugins like the Chatbotistic Widget.
 * Version:           1.2.1
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            WordPressistic
 * Author URI:        https://www.wordpressistic.com
 * License:           GPL v2 or later
 * Text Domain:       memberistic-licenseistic-bridge
 *
 * @package WordPressistic\MLB
 */

defined( 'ABSPATH' ) || exit;

define( 'MLB_VERSION',  '1.2.1' );
define( 'MLB_FILE',     __FILE__ );
define( 'MLB_DIR',      plugin_dir_path( __FILE__ ) );
define( 'MLB_URL',      plugin_dir_url( __FILE__ ) );
define( 'MLB_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Autoload bridge classes.
 */
spl_autoload_register( function ( string $class ): void {
	if ( strncmp( $class, 'WordPressistic\\MLB\\', 19 ) !== 0 ) {
		return;
	}
	$short = substr( $class, 19 );
	$map   = [
		'Plugin'      => 'includes/class-plugin.php',
		'Bridge'      => 'includes/class-bridge.php',
		'Caps'        => 'includes/class-caps.php',
		'Rest'        => 'includes/class-rest.php',
		'Admin\\Page' => 'admin/class-page.php',
	];
	if ( isset( $map[ $short ] ) && is_readable( MLB_DIR . $map[ $short ] ) ) {
		require_once MLB_DIR . $map[ $short ];
	}
} );

register_activation_hook( MLB_FILE, function () {
	add_option( 'mlb_plan_caps', [] );
	add_option( 'mlb_plan_product_id', 0 );
	add_option( 'mlb_version', MLB_VERSION );
} );

add_action( 'plugins_loaded', function () {
	if ( ! defined( 'MEMBERISTIC_VERSION' ) || ! defined( 'WPISTIC_LSI_VERSION' ) ) {
		add_action( 'admin_notices', function () {
			if ( current_user_can( 'manage_options' ) ) {
				echo '<div class="notice notice-warning"><p><strong>Memberistic → Licenseistic Bridge</strong> needs both <strong>Memberistic</strong> and <strong>Licenseistic</strong> active to work. Activate both, then revisit this page.</p></div>';
			}
		} );
		return;
	}
	( new WordPressistic\MLB\Plugin() )->boot();
}, 20 );
