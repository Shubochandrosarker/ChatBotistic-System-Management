<?php
/**
 * Settings, secrets, the API log and per-user identifiers.
 *
 * @package Chatbotistic\Connector
 */

namespace Chatbotistic\Connector;

defined( 'ABSPATH' ) || exit;

class Store {

	const SETTINGS = 'cbc_settings';

	/**
	 * Read one plugin setting.
	 *
	 * For api_email / api_password / lead_api_key the lookup precedence is:
	 *   1. PHP constant defined in wp-config.php  (preferred for production
	 *      deployments — secrets stay out of the database)
	 *   2. WordPress option (admin-entered via the settings UI)
	 *   3. The $default argument
	 *
	 * Constants take precedence so a deploy can override per environment
	 * without touching the option table.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Fallback.
	 * @return mixed
	 */
	public static function setting( string $key, $default = '' ) {
		// Environment-driven overrides. Recognised constants:
		//   CBC_API_EMAIL       — Tochat master account email
		//   CBC_API_PASSWORD    — Tochat master account password (plain text,
		//                         NOT base64; the encrypt/decrypt pair is
		//                         skipped on the constant path).
		//   CBC_LEAD_API_KEY    — lead webhook signing key
		$env_map = array(
			'api_email'    => 'CBC_API_EMAIL',
			'api_password' => 'CBC_API_PASSWORD',
			'lead_api_key' => 'CBC_LEAD_API_KEY',
		);
		if ( isset( $env_map[ $key ] ) && defined( $env_map[ $key ] ) ) {
			$val = (string) constant( $env_map[ $key ] );
			// api_password gets stored encrypted, but the constant ships
			// plaintext — return it pre-encrypted so callers using
			// Store::decrypt() get back the right thing without changes.
			if ( 'api_password' === $key && '' !== $val ) {
				return self::encrypt( $val );
			}
			return $val;
		}
		$all = get_option( self::SETTINGS, array() );
		return is_array( $all ) && array_key_exists( $key, $all ) ? $all[ $key ] : $default;
	}

	/**
	 * Whether a particular setting is being supplied by a wp-config.php
	 * constant rather than the database. The admin UI uses this to render
	 * a "managed in wp-config.php" label instead of an editable field.
	 *
	 * @param string $key Setting key.
	 * @return bool
	 */
	public static function setting_is_locked( string $key ): bool {
		$env_map = array(
			'api_email'    => 'CBC_API_EMAIL',
			'api_password' => 'CBC_API_PASSWORD',
			'lead_api_key' => 'CBC_LEAD_API_KEY',
		);
		return isset( $env_map[ $key ] ) && defined( $env_map[ $key ] );
	}

	/**
	 * Merge and persist settings.
	 *
	 * @param array $values Key/value pairs.
	 */
	public static function update_settings( array $values ): void {
		$all = get_option( self::SETTINGS, array() );
		$all = is_array( $all ) ? $all : array();
		update_option( self::SETTINGS, array_merge( $all, $values ) );
	}

	/**
	 * URL of the standalone Chatbotistic dashboard app
	 * (https://chatbot.wpistic.cloud), where members will eventually
	 * manage widgets more richly than this in-WordPress connector does.
	 *
	 * Falls back to the shipped default until an admin overrides it via the
	 * Settings screen.
	 *
	 * @return string
	 */
	public static function dashboard_url(): string {
		return (string) self::setting( 'dashboard_url', 'https://chatbot.wpistic.cloud' );
	}

	/**
	 * Stable Tochat userClient tag for a WordPress user.
	 *
	 * Every widget a member creates is tagged with this string, so all
	 * Tochat queries can be scoped to the member with no cross-tenant leak.
	 *
	 * @param int $wp_user_id WordPress user ID.
	 * @return string
	 */
	public static function user_client( int $wp_user_id ): string {
		return 'cbc-' . $wp_user_id;
	}

	// ── Secret storage (master API password) ──────────────────────────────

	/**
	 * Encrypt a value at rest with the site's own salts.
	 *
	 * @param string $value Plain value.
	 * @return string
	 */
	public static function encrypt( string $value ): string {
		if ( '' === $value ) {
			return '';
		}
		$iv = openssl_random_pseudo_bytes( 16 );
		$ct = openssl_encrypt( $value, 'AES-256-CBC', self::key(), OPENSSL_RAW_DATA, $iv );
		return base64_encode( $iv . $ct );
	}

	/**
	 * Decrypt a stored value.
	 *
	 * @param string $value Encrypted value.
	 * @return string
	 */
	public static function decrypt( string $value ): string {
		if ( '' === $value ) {
			return '';
		}
		$raw = base64_decode( $value, true );
		if ( false === $raw || strlen( $raw ) <= 16 ) {
			return '';
		}
		$iv = substr( $raw, 0, 16 );
		$ct = substr( $raw, 16 );
		$pt = openssl_decrypt( $ct, 'AES-256-CBC', self::key(), OPENSSL_RAW_DATA, $iv );
		return is_string( $pt ) ? $pt : '';
	}

	/**
	 * 32-byte key derived from WordPress secret keys.
	 *
	 * @return string
	 */
	private static function key(): string {
		$seed = ( defined( 'AUTH_KEY' ) ? AUTH_KEY : 'cbc' ) . ( defined( 'SECURE_AUTH_KEY' ) ? SECURE_AUTH_KEY : 'connector' );
		return substr( hash( 'sha256', $seed ), 0, 32 );
	}

	// ── API log ───────────────────────────────────────────────────────────

	/**
	 * Record an API call.
	 *
	 * @param int|null $user_id  WordPress user.
	 * @param string   $endpoint Endpoint path.
	 * @param string   $method   HTTP method.
	 * @param int|null $status   HTTP status.
	 * @param bool     $ok       Whether the call succeeded.
	 * @param string   $message  Short message (truncated).
	 */
	public static function log( ?int $user_id, string $endpoint, string $method, ?int $status, bool $ok, string $message = '' ): void {
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix . 'cbc_api_log',
			array(
				'wp_user_id'  => $user_id ?: null,
				'endpoint'    => substr( $endpoint, 0, 255 ),
				'method'      => strtoupper( $method ),
				'status_code' => $status,
				'ok'          => $ok ? 1 : 0,
				'message'     => substr( $message, 0, 2000 ),
			),
			array( '%d', '%s', '%s', '%d', '%d', '%s' )
		);
	}

	/**
	 * Recent API log rows.
	 *
	 * @param int $limit Row cap.
	 * @return array<int,object>
	 */
	public static function get_log( int $limit = 100 ): array {
		global $wpdb;
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT l.*, u.user_email FROM {$wpdb->prefix}cbc_api_log l
				 LEFT JOIN {$wpdb->users} u ON u.ID = l.wp_user_id
				 ORDER BY l.id DESC LIMIT %d",
				$limit
			)
		) ?: array();
	}
}
