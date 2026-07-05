<?php
/**
 * Uninstall — drop the API log table + connector options.
 *
 * Set CBC_KEEP_DATA_ON_UNINSTALL in wp-config.php to keep everything.
 *
 * @package Chatbotistic\Connector
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

if ( defined( 'CBC_KEEP_DATA_ON_UNINSTALL' ) && CBC_KEEP_DATA_ON_UNINSTALL ) {
	return;
}

global $wpdb;

delete_option( 'cbc_settings' );
delete_option( 'cbc_version' );
delete_option( 'cbc_db_version' );
delete_transient( 'cbc_api_token' );

$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}cbc_api_log" );
