<?php
/**
 * Plugin Name: Licenseistic
 * Plugin URI: https://wpistic.com/licenseistic
 * Description: Secure WordPress license management system for plugins, themes, SaaS, and digital products. Generate, validate, and manage software licenses with REST API, activation tracking, and customer dashboards.
 * Version: 1.1.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: WPistic
 * Author URI: https://wpistic.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: licenseistic
 * Domain Path: /languages
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'WPISTIC_LSI_VERSION' ) ) {
	return;
}

define( 'WPISTIC_LSI_VERSION', '1.1.0' );
define( 'WPISTIC_LSI_DB_VERSION', '1.0.0' );
define( 'WPISTIC_LSI_FILE', __FILE__ );
define( 'WPISTIC_LSI_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPISTIC_LSI_URL', plugin_dir_url( __FILE__ ) );
define( 'WPISTIC_LSI_BASENAME', plugin_basename( __FILE__ ) );
define( 'WPISTIC_LSI_REST_NAMESPACE', 'licenseistic/v1' );

require_once WPISTIC_LSI_PATH . 'includes/helpers.php';
require_once WPISTIC_LSI_PATH . 'includes/class-wpistic-lsi-loader.php';
require_once WPISTIC_LSI_PATH . 'includes/class-wpistic-lsi-db.php';
require_once WPISTIC_LSI_PATH . 'includes/class-wpistic-lsi-install.php';
require_once WPISTIC_LSI_PATH . 'includes/class-wpistic-lsi-activator.php';
require_once WPISTIC_LSI_PATH . 'includes/class-wpistic-lsi-deactivator.php';
require_once WPISTIC_LSI_PATH . 'includes/class-wpistic-lsi-crypto.php';
require_once WPISTIC_LSI_PATH . 'includes/class-wpistic-lsi-logger.php';
require_once WPISTIC_LSI_PATH . 'includes/class-wpistic-lsi-key-generator.php';
require_once WPISTIC_LSI_PATH . 'includes/class-wpistic-lsi-product-service.php';
require_once WPISTIC_LSI_PATH . 'includes/class-wpistic-lsi-customer-service.php';
require_once WPISTIC_LSI_PATH . 'includes/class-wpistic-lsi-license-service.php';
require_once WPISTIC_LSI_PATH . 'includes/class-wpistic-lsi-api-auth.php';
require_once WPISTIC_LSI_PATH . 'includes/class-wpistic-lsi-rest-api.php';
require_once WPISTIC_LSI_PATH . 'includes/class-wpistic-lsi-plugin.php';

require_once WPISTIC_LSI_PATH . 'admin/class-wpistic-lsi-admin.php';
require_once WPISTIC_LSI_PATH . 'admin/class-wpistic-lsi-admin-menu.php';

require_once WPISTIC_LSI_PATH . 'public/class-wpistic-lsi-public.php';
require_once WPISTIC_LSI_PATH . 'public/class-wpistic-lsi-shortcodes.php';

register_activation_hook( __FILE__, array( 'WPistic_LSI_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WPistic_LSI_Deactivator', 'deactivate' ) );

/**
 * Returns the main plugin instance.
 *
 * @return WPistic_LSI_Plugin
 */
function wpistic_lsi() {
	return WPistic_LSI_Plugin::instance();
}

add_action( 'plugins_loaded', 'wpistic_lsi', 5 );
