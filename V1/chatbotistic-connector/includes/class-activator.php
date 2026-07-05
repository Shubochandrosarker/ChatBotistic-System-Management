<?php
/**
 * Activation, deactivation and schema.
 *
 * The Tochat API is the source of truth for widgets/agents/leads, so the
 * plugin keeps no mirror tables — only an API call log for debugging.
 *
 * @package Chatbotistic\Connector
 */

namespace Chatbotistic\Connector;

defined( 'ABSPATH' ) || exit;

class Activator {

	const DB_VERSION = '3.0.0';

	/**
	 * Activation hook.
	 */
	public static function activate(): void {
		self::install();
		add_option( 'cbc_version', CBC_VERSION );
		flush_rewrite_rules();
	}

	/**
	 * Deactivation hook.
	 */
	public static function deactivate(): void {
		delete_transient( 'cbc_api_token' );
		flush_rewrite_rules();
	}

	/**
	 * Create/upgrade the API log table.
	 */
	public static function install(): void {
		global $wpdb;
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$wpdb->prefix}cbc_api_log (
			id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			wp_user_id  BIGINT(20) UNSIGNED DEFAULT NULL,
			endpoint    VARCHAR(255)        NOT NULL,
			method      VARCHAR(10)         NOT NULL DEFAULT 'GET',
			status_code SMALLINT(6)         DEFAULT NULL,
			ok          TINYINT(1)          NOT NULL DEFAULT 1,
			message     TEXT                DEFAULT NULL,
			created_at  DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY wp_user_id (wp_user_id),
			KEY created_at (created_at)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'cbc_db_version', self::DB_VERSION );
	}
}
