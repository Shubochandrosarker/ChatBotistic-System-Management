<?php
/**
 * Uninstall script for Insightistic Pro.
 * Runs when the plugin is deleted from the WordPress admin.
 *
 * @package Insightistic_Pro
 */

// Only run when WordPress uninstaller calls this file directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// List of all options to remove.
$options = array(
	// GA4.
	'insightistic_pro_property_id',
	'insightistic_pro_api_email',
	'insightistic_pro_api_private_key',
	// Search Console.
	'insightistic_pro_gsc_property_url',
	// PageSpeed (key stored only encrypted; also delete any legacy plaintext entry).
	'insightistic_pro_pagespeed_api_key_enc',
	'insightistic_pro_pagespeed_api_key',
	'insightistic_pro_pagespeed_default_url',
	// Engagement.
	'insightistic_pro_engagement_enabled',
	'insightistic_pro_measurement_id',
	'insightistic_pro_measurement_secret',
	// AI.
	'insightistic_pro_ai_enabled',
	'insightistic_pro_ai_provider',
	'insightistic_pro_openai_key',
	'insightistic_pro_openai_model',
	'insightistic_pro_gemini_key',
	'insightistic_pro_gemini_model',
	'insightistic_pro_openrouter_key',
	'insightistic_pro_openrouter_model',
	'insightistic_pro_claude_key',
	'insightistic_pro_claude_model',
	// Misc.
	'insightistic_pro_video_guide_url',
	'insightistic_pro_docs_url',
);

foreach ( $options as $option ) {
	delete_option( $option );
}

// Remove all transients.
global $wpdb;
$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		'_transient_insightistic%',
		'_transient_timeout_insightistic%'
	)
);
