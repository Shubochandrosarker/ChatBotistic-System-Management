<?php
/**
 * Uninstall handler for Licenseistic.
 *
 * Removes plugin data only when the user has explicitly opted in via the
 * settings option `wpistic_lsi_delete_on_uninstall`.
 *
 * @package Licenseistic
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$delete_data = get_option( 'wpistic_lsi_delete_on_uninstall', 'no' );

if ( 'yes' !== $delete_data ) {
	return;
}

$tables = array(
	$wpdb->prefix . 'wpistic_lsi_licenses',
	$wpdb->prefix . 'wpistic_lsi_products',
	$wpdb->prefix . 'wpistic_lsi_activations',
	$wpdb->prefix . 'wpistic_lsi_generators',
	$wpdb->prefix . 'wpistic_lsi_api_keys',
	$wpdb->prefix . 'wpistic_lsi_logs',
	$wpdb->prefix . 'wpistic_lsi_webhooks',
	$wpdb->prefix . 'wpistic_lsi_meta',
);

foreach ( $tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB
}

$options = array(
	'wpistic_lsi_db_version',
	'wpistic_lsi_secret_key',
	'wpistic_lsi_settings',
	'wpistic_lsi_delete_on_uninstall',
);

foreach ( $options as $option ) {
	delete_option( $option );
}
