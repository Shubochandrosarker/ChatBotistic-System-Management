<?php
/**
 * Plugin Name:       Chatbotistic Profile for Memberistic
 * Plugin URI:        https://chatbotistic.com
 * Description:       One-install configuration profile for chatbotistic.com — replaces Memberistic's default plans with the 4 Chatbotistic plans (Free / Pro / Agency / Lifetime), creates branded member-facing pages, auto-approves memberships on payment, auto-activates free signups, hides waiver UI, overrides every transactional email with chatbotistic.com URLs, and seeds the Memberistic → Licenseistic Bridge caps.
 * Version:           1.1.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            WordPressistic
 * Text Domain:       chatbotistic-profile
 *
 * @package Chatbotistic\Profile
 */

defined( 'ABSPATH' ) || exit;

define( 'CBP_VERSION',  '1.1.0' );
define( 'CBP_FILE',     __FILE__ );
define( 'CBP_DIR',      plugin_dir_path( __FILE__ ) );
define( 'CBP_URL',      plugin_dir_url( __FILE__ ) );
define( 'CBP_BASENAME', plugin_basename( __FILE__ ) );

// All site-facing URLs the profile expects. Keep absolute so emails work
// regardless of which host wp_mail is fired from (multisite, cron, CLI).
define( 'CBP_BASE_URL',           'https://www.chatbotistic.com' );
define( 'CBP_FROM_EMAIL',         'hello@chatbotistic.com' );
define( 'CBP_FROM_NAME',          'Chatbotistic' );

// Profile-managed page slugs (must match what's written into memberistic_settings).
define( 'CBP_SLUG_LOGIN',         'login' );
define( 'CBP_SLUG_PLANS',         'memberships' );
define( 'CBP_SLUG_CHECKOUT',      'memberistic-checkout' );
define( 'CBP_SLUG_ACCOUNT',       'account' );
define( 'CBP_SLUG_RENEWAL',       'memberistic-renewal' );
define( 'CBP_SLUG_FAILED',        'memberistic-payment-failed' );
define( 'CBP_SLUG_THANK_YOU',     'memberistic-thank-you' );

// Autoload.
spl_autoload_register( function ( string $class ): void {
	if ( strncmp( $class, 'Chatbotistic\\Profile\\', 21 ) !== 0 ) {
		return;
	}
	$short = substr( $class, 21 );
	$map   = [
		'Plugin'       => 'includes/class-plugin.php',
		'Installer'    => 'includes/class-installer.php',
		'Plans'        => 'includes/class-plans.php',
		'Pages'        => 'includes/class-pages.php',
		'Emails'       => 'includes/class-emails.php',
		'Auto_Approve' => 'includes/class-auto-approve.php',
		'Stripper'     => 'includes/class-stripper.php',
		'Admin'        => 'admin/class-admin.php',
	];
	if ( isset( $map[ $short ] ) && is_readable( CBP_DIR . $map[ $short ] ) ) {
		require_once CBP_DIR . $map[ $short ];
	}
} );

register_activation_hook( CBP_FILE, function () {
	require_once CBP_DIR . 'includes/class-installer.php';
	Chatbotistic\Profile\Installer::run( true );
	add_option( 'cbp_version', CBP_VERSION );
} );

add_action( 'plugins_loaded', function () {
	if ( ! defined( 'MEMBERISTIC_VERSION' ) ) {
		add_action( 'admin_notices', function () {
			if ( current_user_can( 'manage_options' ) ) {
				echo '<div class="notice notice-warning"><p><strong>Chatbotistic Profile</strong> needs <strong>Memberistic</strong> active to work. Activate it, then re-activate this plugin.</p></div>';
			}
		} );
		return;
	}
	( new Chatbotistic\Profile\Plugin() )->boot();
}, 30 );
