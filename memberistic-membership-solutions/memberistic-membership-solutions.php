<?php
/**
 * Plugin Name: Memberistic Membership Solutions
 * Plugin URI: https://www.wordpressistic.com
 * Description: A modern membership operations engine for service and SaaS businesses — plans, signups, payments, renewals, linked members, check-ins, staff dashboards, REST API, and Stripe + WooCommerce integration. Brand-neutral by default; host products configure the brand label, login copy, member-ID prefix, QR header, and account-template features through memberistic_settings or filters.
 * Version: 1.12.4
 * Author: WordPressistic
 * Author URI: https://www.wordpressistic.com
 * Text Domain: memberistic
 * Domain Path: /languages
 * Requires PHP: 8.0
 *
 * @package Memberistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MEMBERISTIC_VERSION', '1.12.4' );
define( 'MEMBERISTIC_DB_VERSION', '1.2.0' );
define( 'MEMBERISTIC_FILE', __FILE__ );
define( 'MEMBERISTIC_PATH', plugin_dir_path( __FILE__ ) );
define( 'MEMBERISTIC_URL', plugin_dir_url( __FILE__ ) );
define( 'MEMBERISTIC_BASENAME', plugin_basename( __FILE__ ) );

require_once MEMBERISTIC_PATH . 'includes/class-plugin.php';

register_activation_hook(
	MEMBERISTIC_FILE,
	static function () {
		require_once MEMBERISTIC_PATH . 'includes/class-activator.php';
		WordPressistic\Memberistic\Activator::activate();
	}
);

register_deactivation_hook(
	MEMBERISTIC_FILE,
	static function () {
		require_once MEMBERISTIC_PATH . 'includes/class-deactivator.php';
		WordPressistic\Memberistic\Deactivator::deactivate();
	}
);

add_action(
	'plugins_loaded',
	static function () {
		$plugin = new WordPressistic\Memberistic\Plugin();
		$plugin->run();
	}
);
