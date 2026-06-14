<?php
/**
 * Uninstall script for Insightistic.
 * Runs when the plugin is deleted from the WordPress admin.
 *
 * @package Insightistic
 */

// Only run when WordPress uninstaller calls this file directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// List of all options to remove.
$options = array(
	// GA4.
	'insightistic_property_id',
	'insightistic_api_email',
	'insightistic_api_private_key',
	// Search Console.
	'insightistic_gsc_property_url',
	// PageSpeed (key stored only encrypted; also delete any legacy plaintext entry).
	'insightistic_pagespeed_api_key_enc',
	'insightistic_pagespeed_api_key',
	'insightistic_pagespeed_default_url',
	// Engagement.
	'insightistic_engagement_enabled',
	'insightistic_measurement_id',
	'insightistic_measurement_secret',
	// AI.
	'insightistic_ai_enabled',
	'insightistic_ai_provider',
	'insightistic_ai_skill_profile',
	'insightistic_openai_key',
	'insightistic_openai_model',
	'insightistic_gemini_key',
	'insightistic_gemini_model',
	'insightistic_openrouter_key',
	'insightistic_openrouter_model',
	'insightistic_claude_key',
	'insightistic_claude_model',
	'insightistic_groq_key',
	'insightistic_groq_model',
	// AI history + key rotation timestamps.
	'insightistic_ai_history',
	'insightistic_openai_key_updated_at',
	'insightistic_gemini_key_updated_at',
	'insightistic_openrouter_key_updated_at',
	'insightistic_claude_key_updated_at',
	'insightistic_groq_key_updated_at',
	// Encryption.
	'insightistic_crypto_secret',
	'insightistic_enc_migrated_330',
	// Misc.
	'insightistic_video_guide_url',
	'insightistic_docs_url',
	'insightistic_addons',
	'insightistic_email_automations',
	'insightistic_email_next_run',
	'insightistic_email_last_sent',
	'insightistic_migrated_from_pro',
);

foreach ( $options as $option ) {
	delete_option( $option );
}

// Sweep any remaining plugin options (e.g. dynamically-named rotation
// timestamps) plus all plugin transients.
global $wpdb;
$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s",
		'insightistic\_%\_key_updated_at',
		'_transient_insightistic%',
		'_transient_timeout_insightistic%'
	)
);
