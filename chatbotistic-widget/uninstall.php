<?php
/**
 * Chatbotistic Widget — uninstall.
 *
 * @package Chatbotistic\Widget
 */
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Strip plugin options.
$opts = [
	'cbw_license_key',
	'cbw_license_status',
	'cbw_license_tier',
	'cbw_license_payload',
	'cbw_license_last_check',
	'cbw_license_instance_id',
	'cbw_tochat_email',
	'cbw_tochat_password',
	'cbw_default_widget_key',
	'cbw_widget_keys_by_post',
	'cbw_widget_keys_by_url',
	'cbw_register_domain',
];
foreach ( $opts as $opt ) {
	delete_option( $opt );
}

delete_transient( 'cbw_api_token' );
delete_transient( 'cbw_license_heartbeat_lock' );
wp_clear_scheduled_hook( 'cbw_license_heartbeat' );
