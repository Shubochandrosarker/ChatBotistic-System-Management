<?php
/**
 * Plugin Name: Insightistic – GA4 Analytics & AI Insights
 * Plugin URI:  https://wordpressistic.com/insightistic
 * Description: Connect Google Analytics 4 and Google Search Console to your WordPress dashboard. View traffic, PageSpeed scores, engagement events and get AI-powered marketing insights.
 * Version:     2.0.0
 * Author:      WordPressistic
 * Author URI:  https://wordpressistic.com
 * License:     GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: insightistic
 * Domain Path: /languages
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * Tested up to: 6.7
 *
 * @package Insightistic_Pro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'INSIGHTISTIC_VERSION',  '2.0.0' );
define( 'INSIGHTISTIC_FILE',     __FILE__ );
define( 'INSIGHTISTIC_PATH',     plugin_dir_path( __FILE__ ) );
define( 'INSIGHTISTIC_URL',      plugin_dir_url( __FILE__ ) );
define( 'INSIGHTISTIC_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Load plugin classes after all plugins are loaded.
 */
function insightistic_pro_load() {
	$files = array(
		INSIGHTISTIC_PATH . 'includes/class-insightistic-encryption.php',
		INSIGHTISTIC_PATH . 'includes/class-insightistic-auth.php',
		INSIGHTISTIC_PATH . 'includes/class-insightistic-ga.php',
		INSIGHTISTIC_PATH . 'includes/class-insightistic-gsc.php',
		INSIGHTISTIC_PATH . 'includes/class-insightistic-pagespeed.php',
		INSIGHTISTIC_PATH . 'includes/class-insightistic-engagement.php',
		INSIGHTISTIC_PATH . 'includes/class-insightistic-ai.php',
		INSIGHTISTIC_PATH . 'includes/class-insightistic-admin.php',
	);

	foreach ( $files as $file ) {
		if ( ! file_exists( $file ) ) {
			add_action(
				'admin_notices',
				function () use ( $file ) {
					echo '<div class="notice notice-error"><p>';
					printf(
						/* translators: %s: file path */
						esc_html__( 'Insightistic: Missing file %s. Please reinstall the plugin.', 'insightistic' ),
						esc_html( str_replace( INSIGHTISTIC_PATH, '', $file ) )
					);
					echo '</p></div>';
				}
			);
			return;
		}
		require_once $file;
	}

	load_plugin_textdomain( 'insightistic', false, dirname( INSIGHTISTIC_BASENAME ) . '/languages' );

	( new Insightistic_Admin() )->init();
	( new Insightistic_GA() )->init();
	( new Insightistic_GSC() )->init();
	( new Insightistic_PageSpeed() )->init();
	( new Insightistic_Engagement() )->init();
	( new Insightistic_AI() )->init();
}
add_action( 'plugins_loaded', 'insightistic_pro_load' );

/**
 * Activation hook.
 */
function insightistic_pro_activate() {
	// GA4.
	add_option( 'insightistic_pro_property_id', '' );
	// AI.
	add_option( 'insightistic_pro_ai_provider', 'none' );
	add_option( 'insightistic_pro_ai_enabled', 0 );
	// GSC.
	add_option( 'insightistic_pro_gsc_property_url', '' );
	// PageSpeed (key stored only in encrypted form as _enc suffix).
	add_option( 'insightistic_pro_pagespeed_api_key_enc', '' );
	add_option( 'insightistic_pro_pagespeed_default_url', '' );
	// Engagement.
	add_option( 'insightistic_pro_engagement_enabled', 0 );
	add_option( 'insightistic_pro_measurement_id', '' );
	add_option( 'insightistic_pro_measurement_secret', '' );
	// Misc.
	add_option( 'insightistic_pro_video_guide_url', '' );
	add_option( 'insightistic_pro_docs_url', '' );
}
register_activation_hook( INSIGHTISTIC_FILE, 'insightistic_pro_activate' );

/**
 * Deactivation hook.
 */
function insightistic_pro_deactivate() {
	delete_transient( 'insightistic_pro_access_token_ga4' );
	delete_transient( 'insightistic_pro_access_token_gsc' );
	// Clear all analytics cache transients.
	global $wpdb;
	$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			'_transient_insightistic_data_%',
			'_transient_timeout_insightistic_data_%'
		)
	);
}
register_deactivation_hook( INSIGHTISTIC_FILE, 'insightistic_pro_deactivate' );
