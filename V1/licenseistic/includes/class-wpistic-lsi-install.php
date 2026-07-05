<?php
/**
 * Installer: builds and updates database schema.
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPistic_LSI_Install
 */
class WPistic_LSI_Install {

	/**
	 * Runs schema install or upgrade.
	 *
	 * @return void
	 */
	public static function install() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();
		$prefix          = $wpdb->prefix . 'wpistic_lsi_';

		$schemas = self::get_schemas( $prefix, $charset_collate );

		foreach ( $schemas as $sql ) {
			dbDelta( $sql );
		}

		self::ensure_secret_key();
		update_option( 'wpistic_lsi_db_version', WPISTIC_LSI_DB_VERSION );
	}

	/**
	 * Returns the array of CREATE TABLE statements.
	 *
	 * @param string $prefix          Table prefix.
	 * @param string $charset_collate Charset collate.
	 * @return array
	 */
	protected static function get_schemas( $prefix, $charset_collate ) {
		$schemas = array();

		$schemas[] = "CREATE TABLE {$prefix}licenses (
			license_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			license_key_hash VARCHAR(191) NOT NULL,
			license_key_encrypted LONGTEXT NULL,
			license_label VARCHAR(255) NULL,
			product_id BIGINT UNSIGNED NULL,
			customer_id BIGINT UNSIGNED NULL,
			customer_email VARCHAR(191) NULL,
			order_id BIGINT UNSIGNED NULL,
			source VARCHAR(50) DEFAULT 'manual',
			status VARCHAR(50) DEFAULT 'active',
			activation_limit INT UNSIGNED DEFAULT 1,
			activation_count INT UNSIGNED DEFAULT 0,
			usage_limit INT UNSIGNED NULL,
			usage_count INT UNSIGNED DEFAULT 0,
			expires_at DATETIME NULL,
			starts_at DATETIME NULL,
			created_by BIGINT UNSIGNED NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NULL,
			revoked_at DATETIME NULL,
			suspended_at DATETIME NULL,
			notes LONGTEXT NULL,
			PRIMARY KEY  (license_id),
			UNIQUE KEY license_key_hash (license_key_hash),
			KEY product_id (product_id),
			KEY customer_id (customer_id),
			KEY customer_email (customer_email),
			KEY order_id (order_id),
			KEY status (status),
			KEY expires_at (expires_at)
		) {$charset_collate};";

		$schemas[] = "CREATE TABLE {$prefix}products (
			product_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			product_name VARCHAR(255) NOT NULL,
			product_slug VARCHAR(191) NOT NULL,
			product_type VARCHAR(50) DEFAULT 'software',
			product_version VARCHAR(50) NULL,
			download_url TEXT NULL,
			changelog LONGTEXT NULL,
			status VARCHAR(50) DEFAULT 'active',
			default_activation_limit INT UNSIGNED DEFAULT 1,
			default_expiry_days INT UNSIGNED NULL,
			generator_id BIGINT UNSIGNED NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NULL,
			PRIMARY KEY  (product_id),
			UNIQUE KEY product_slug (product_slug),
			KEY status (status),
			KEY generator_id (generator_id)
		) {$charset_collate};";

		$schemas[] = "CREATE TABLE {$prefix}activations (
			activation_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			license_id BIGINT UNSIGNED NOT NULL,
			product_id BIGINT UNSIGNED NULL,
			site_url TEXT NULL,
			instance_id VARCHAR(191) NULL,
			environment VARCHAR(50) DEFAULT 'production',
			ip_address VARCHAR(100) NULL,
			user_agent TEXT NULL,
			status VARCHAR(50) DEFAULT 'active',
			activated_at DATETIME NOT NULL,
			deactivated_at DATETIME NULL,
			last_ping_at DATETIME NULL,
			meta LONGTEXT NULL,
			PRIMARY KEY  (activation_id),
			KEY license_id (license_id),
			KEY product_id (product_id),
			KEY instance_id (instance_id),
			KEY status (status),
			KEY last_ping_at (last_ping_at)
		) {$charset_collate};";

		$schemas[] = "CREATE TABLE {$prefix}generators (
			generator_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			prefix VARCHAR(50) NULL,
			suffix VARCHAR(50) NULL,
			segment_length INT UNSIGNED DEFAULT 5,
			segment_count INT UNSIGNED DEFAULT 4,
			separator VARCHAR(10) DEFAULT '-',
			charset VARCHAR(255) DEFAULT 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789',
			activation_limit INT UNSIGNED DEFAULT 1,
			expiry_days INT UNSIGNED NULL,
			status VARCHAR(50) DEFAULT 'active',
			created_at DATETIME NOT NULL,
			updated_at DATETIME NULL,
			PRIMARY KEY  (generator_id),
			KEY status (status)
		) {$charset_collate};";

		$schemas[] = "CREATE TABLE {$prefix}api_keys (
			api_key_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			public_key VARCHAR(191) NOT NULL,
			secret_hash VARCHAR(191) NOT NULL,
			permissions LONGTEXT NULL,
			status VARCHAR(50) DEFAULT 'active',
			last_used_at DATETIME NULL,
			created_by BIGINT UNSIGNED NULL,
			created_at DATETIME NOT NULL,
			revoked_at DATETIME NULL,
			PRIMARY KEY  (api_key_id),
			UNIQUE KEY public_key (public_key),
			KEY status (status)
		) {$charset_collate};";

		$schemas[] = "CREATE TABLE {$prefix}logs (
			log_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			object_type VARCHAR(50) NULL,
			object_id BIGINT UNSIGNED NULL,
			event_type VARCHAR(100) NOT NULL,
			message TEXT NULL,
			context LONGTEXT NULL,
			user_id BIGINT UNSIGNED NULL,
			ip_address VARCHAR(100) NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (log_id),
			KEY object_type (object_type),
			KEY object_id (object_id),
			KEY event_type (event_type),
			KEY user_id (user_id),
			KEY created_at (created_at)
		) {$charset_collate};";

		$schemas[] = "CREATE TABLE {$prefix}webhooks (
			webhook_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			target_url TEXT NOT NULL,
			events LONGTEXT NOT NULL,
			secret VARCHAR(191) NULL,
			status VARCHAR(50) DEFAULT 'active',
			created_at DATETIME NOT NULL,
			updated_at DATETIME NULL,
			PRIMARY KEY  (webhook_id),
			KEY status (status)
		) {$charset_collate};";

		$schemas[] = "CREATE TABLE {$prefix}meta (
			meta_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			object_type VARCHAR(50) NOT NULL,
			object_id BIGINT UNSIGNED NOT NULL,
			meta_key VARCHAR(191) NOT NULL,
			meta_value LONGTEXT NULL,
			PRIMARY KEY  (meta_id),
			KEY object_type_id (object_type, object_id),
			KEY meta_key (meta_key)
		) {$charset_collate};";

		return $schemas;
	}

	/**
	 * Ensures a plugin secret key exists, generating one if needed.
	 *
	 * @return string
	 */
	public static function ensure_secret_key() {
		$secret = get_option( 'wpistic_lsi_secret_key' );
		if ( empty( $secret ) ) {
			$secret = wp_generate_password( 64, true, true );
			update_option( 'wpistic_lsi_secret_key', $secret, false );
		}
		return $secret;
	}

	/**
	 * Runs upgrade routine when DB version changes.
	 *
	 * @return void
	 */
	public static function maybe_upgrade() {
		$current = get_option( 'wpistic_lsi_db_version' );
		if ( WPISTIC_LSI_DB_VERSION !== $current ) {
			self::install();
		}
	}
}
