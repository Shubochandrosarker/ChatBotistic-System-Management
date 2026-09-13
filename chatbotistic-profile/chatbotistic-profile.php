<?php
/**
 * Plugin Name:       Chatbotistic Profile for Memberistic
 * Plugin URI:        https://chatbotistic.com
 * Description:       One-install configuration profile for chatbotistic.com — replaces Memberistic's default plans with the 5 Chatbotistic plans (Free Forever / Starter / Growth / Agency / Lifetime), creates branded member-facing pages, auto-approves memberships on payment, auto-activates free signups, hides waiver UI, overrides every transactional email with chatbotistic.com URLs, seeds the Memberistic → Licenseistic Bridge caps, and mints signed SSO tokens for the standalone app.chatbotistic.com dashboard.
 * Version:           1.3.5
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            WordPressistic
 * Text Domain:       chatbotistic-profile
 *
 * @package Chatbotistic\Profile
 */

defined( 'ABSPATH' ) || exit;

define( 'CBP_VERSION',  '1.3.4' );
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
// Clean, branded URLs — never expose the underlying plugin name to end users.
define( 'CBP_SLUG_LOGIN',         'login' );
define( 'CBP_SLUG_PLANS',         'memberships' );
define( 'CBP_SLUG_CHECKOUT',      'checkout' );
define( 'CBP_SLUG_ACCOUNT',       'account' );
define( 'CBP_SLUG_RENEWAL',       'renew' );
define( 'CBP_SLUG_FAILED',        'payment-failed' );
define( 'CBP_SLUG_THANK_YOU',     'payment-success' );

// Autoload.
spl_autoload_register( function ( string $class ): void {
	if ( strncmp( $class, 'Chatbotistic\\Profile\\', 21 ) !== 0 ) {
		return;
	}
	$short = substr( $class, 21 );
	$map   = [
		'Plugin'        => 'includes/class-plugin.php',
		'Installer'     => 'includes/class-installer.php',
		'Plans'         => 'includes/class-plans.php',
		'Pages'         => 'includes/class-pages.php',
		'Emails'        => 'includes/class-emails.php',
		'Email_Template'     => 'includes/class-email-template.php',
		'Auto_Approve'  => 'includes/class-auto-approve.php',
		'Stripper'      => 'includes/class-stripper.php',
		'System_Health'      => 'includes/class-system-health.php',
		'WPCF_Preset'        => 'includes/class-wpcf-preset.php',
		'Emails_Automation'  => 'includes/class-emails-automation.php',
		'SSO_Bridge'         => 'includes/class-sso-bridge.php',
		'Admin'              => 'admin/class-admin.php',
	];
	if ( isset( $map[ $short ] ) && is_readable( CBP_DIR . $map[ $short ] ) ) {
		require_once CBP_DIR . $map[ $short ];
	}
} );

register_activation_hook( CBP_FILE, function () {
	require_once CBP_DIR . 'includes/class-installer.php';
	if ( defined( 'MEMBERISTIC_VERSION' ) ) {
		Chatbotistic\Profile\Installer::run( true );
		update_option( 'cbp_version', CBP_VERSION );
	} else {
		// Memberistic was activated after we already ran, OR the user
		// activated us first. Flag the install as pending and we'll
		// auto-run it the moment Memberistic shows up (see below).
		update_option( 'cbp_install_pending', '1' );
	}
} );

add_action( 'plugins_loaded', function () {
	if ( ! defined( 'MEMBERISTIC_VERSION' ) ) {
		add_action( 'admin_notices', function () {
			if ( current_user_can( 'manage_options' ) ) {
				echo '<div class="notice notice-warning"><p><strong>Chatbotistic Profile</strong> needs <strong>Memberistic</strong> active to work. Activate it and we\'ll auto-configure the rest.</p></div>';
			}
		} );
		return;
	}
	( new Chatbotistic\Profile\Plugin() )->boot();
}, 30 );

/**
 * Auto-configuration orchestrator.
 *
 * Runs Installer::repair() (non-destructive: creates anything missing,
 * deletes nothing) whenever the system state changes in a way that means
 * the profile needs to re-apply itself:
 *
 *   1. We were activated before Memberistic; Memberistic now exists.
 *   2. Memberistic / Licenseistic / Bridge / Connector was just activated.
 *   3. This plugin was just upgraded (CBP_VERSION moved).
 *
 * All three paths funnel through the same idempotent repair, so running
 * twice in one request is safe.
 */
add_action( 'admin_init', function () {
	if ( ! defined( 'MEMBERISTIC_VERSION' ) ) {
		return;
	}

	$stored_version  = (string) get_option( 'cbp_version', '' );
	$install_pending = '1' === (string) get_option( 'cbp_install_pending', '' );
	$version_drifted = '' === $stored_version || version_compare( $stored_version, CBP_VERSION, '<' );

	if ( ! $install_pending && ! $version_drifted ) {
		return;
	}

	require_once CBP_DIR . 'includes/class-installer.php';
	Chatbotistic\Profile\Installer::repair();
	update_option( 'cbp_version', CBP_VERSION );
	delete_option( 'cbp_install_pending' );
}, 5 );

/**
 * When any of the stack plugins is activated, queue a repair on next
 * admin page load so the Profile re-syncs caps + settings against the
 * freshly-activated companion.
 */
add_action( 'activated_plugin', function ( $plugin ) {
	$watched = array(
		'memberistic-membership-solutions/memberistic-membership-solutions.php',
		'licenseistic/licenseistic.php',
		'memberistic-licenseistic-bridge/memberistic-licenseistic-bridge.php',
		'chatbotistic-connector/chatbotistic-connector.php',
		'chatbotistic-widget/chatbotistic-widget.php',
	);
	if ( in_array( $plugin, $watched, true ) ) {
		update_option( 'cbp_install_pending', '1' );
	}
}, 10, 1 );
