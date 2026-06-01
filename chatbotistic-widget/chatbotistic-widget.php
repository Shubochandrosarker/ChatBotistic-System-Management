<?php
/**
 * Plugin Name:       Chatbotistic Widget
 * Plugin URI:        https://chatbotistic.com/wordpress-plugin
 * Description:       WhatsApp + AI chat widget for WordPress, by Chatbotistic. A WordPressistic product.
 * Version:           1.1.0
 * Requires at least: 5.6
 * Requires PHP:      7.4
 * Author:            Chatbotistic (a WordPressistic product)
 * Author URI:        https://chatbotistic.com/
 * Text Domain:       chatbotistic-widget
 * Domain Path:       /languages/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package Chatbotistic\Widget
 */

defined( 'ABSPATH' ) || exit;

// ── Constants ─────────────────────────────────────────────────────────────────
define( 'CBW_PLUGIN_FILE',     __FILE__ );
define( 'CBW_PLUGIN_PATH',     plugin_dir_path( __FILE__ ) );
define( 'CBW_PLUGIN_URL',      plugin_dir_url( __FILE__ ) );
define( 'CBW_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'CBW_VERSION',         '1.1.0' );

// ── Branding (neutral fallbacks — Brand class resolves per-license at runtime
//    so all four plans are white-label per spec) ────────────────────────────────
if ( ! defined( 'CBW_PRODUCT_NAME'  ) ) { define( 'CBW_PRODUCT_NAME',   'WhatsApp Widget' ); }
if ( ! defined( 'CBW_BRAND_TAGLINE' ) ) { define( 'CBW_BRAND_TAGLINE',  '' ); }
if ( ! defined( 'CBW_BRAND_HOMEPAGE') ) { define( 'CBW_BRAND_HOMEPAGE', '' ); }
if ( ! defined( 'CBW_REGISTER_URL'  ) ) { define( 'CBW_REGISTER_URL',   '' ); }
if ( ! defined( 'CBW_PRICING_URL'   ) ) { define( 'CBW_PRICING_URL',    '' ); }
if ( ! defined( 'CBW_DOCS_URL'      ) ) { define( 'CBW_DOCS_URL',       '' ); }
if ( ! defined( 'CBW_APP_BASE_URL'  ) ) { define( 'CBW_APP_BASE_URL',   '' ); }

// ── License server (default to chatbotistic.com; Lifetime tier with a
//    custom_domain in its license overrides at runtime via Brand) ──────────────
if ( ! defined( 'CBW_LICENSE_BASE_URL' ) ) { define( 'CBW_LICENSE_BASE_URL', 'https://chatbotistic.com/wp-json/licenseistic/v1' ); }
if ( ! defined( 'CBW_PRODUCT_SLUG'     ) ) { define( 'CBW_PRODUCT_SLUG',     'chatbotistic-widget' ); }

// ── Tochat backend API ──────────────────────────────────────────────────────
if ( ! defined( 'CBW_API_BASE' ) ) { define( 'CBW_API_BASE', 'https://services.tochat.be' ); }

// ── Bootstrap ─────────────────────────────────────────────────────────────────
require_once CBW_PLUGIN_PATH . 'includes/class-plugin.php';

register_activation_hook( CBW_PLUGIN_FILE, [ 'Chatbotistic_Widget\\Plugin', 'on_activate' ] );
register_deactivation_hook( CBW_PLUGIN_FILE, [ 'Chatbotistic_Widget\\Plugin', 'on_deactivate' ] );

add_action( 'plugins_loaded', static function () {
	\Chatbotistic_Widget\Plugin::instance();
} );
