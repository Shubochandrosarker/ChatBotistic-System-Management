<?php
/**
 * API key authentication and rate limiting.
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPistic_LSI_API_Auth
 */
class WPistic_LSI_API_Auth {

	/**
	 * Creates a new API key pair.
	 *
	 * @param string $name        Friendly name.
	 * @param array  $permissions Permissions array.
	 * @return array {
	 *     @type int    $api_key_id
	 *     @type string $public_key
	 *     @type string $secret_key Plain secret (shown once).
	 * }
	 */
	public static function create_api_key( $name, $permissions = array( 'read', 'write' ) ) {
		$public = 'lsi_pk_' . wp_generate_password( 24, false, false );
		$secret = 'lsi_sk_' . wp_generate_password( 40, false, false );

		$id = WPistic_LSI_DB::insert( 'api_keys', array(
			'name'        => sanitize_text_field( $name ),
			'public_key'  => $public,
			'secret_hash' => WPistic_LSI_Crypto::hash_secret( $secret ),
			'permissions' => wp_json_encode( $permissions ),
			'status'      => 'active',
			'created_by'  => get_current_user_id(),
			'created_at'  => wpistic_lsi_now(),
		) );

		if ( ! $id ) {
			return new WP_Error( 'db_error', __( 'Could not create API key.', 'licenseistic' ) );
		}

		WPistic_LSI_Logger::log( 'api_key_created', sprintf( 'API key #%d created', $id ), array(
			'object_type' => 'api_key',
			'object_id'   => $id,
		) );

		return array(
			'api_key_id' => $id,
			'public_key' => $public,
			'secret_key' => $secret,
		);
	}

	/**
	 * Revokes an API key.
	 *
	 * @param int $api_key_id API key id.
	 * @return bool
	 */
	public static function revoke_api_key( $api_key_id ) {
		$result = WPistic_LSI_DB::update( 'api_keys', array(
			'status'     => 'revoked',
			'revoked_at' => wpistic_lsi_now(),
		), array( 'api_key_id' => (int) $api_key_id ) );
		if ( false !== $result ) {
			WPistic_LSI_Logger::log( 'api_key_revoked', sprintf( 'API key #%d revoked', $api_key_id ), array(
				'object_type' => 'api_key',
				'object_id'   => (int) $api_key_id,
			) );
		}
		return false !== $result;
	}

	/**
	 * Returns the API key row matching the credentials from the request.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return array|null
	 */
	public static function authenticate_request( $request ) {
		$public = $request->get_header( 'x-lsi-public-key' );
		$secret = $request->get_header( 'x-lsi-secret-key' );

		if ( ! $public || ! $secret ) {
			$auth = $request->get_header( 'authorization' );
			if ( $auth && stripos( $auth, 'basic ' ) === 0 ) {
				$decoded = base64_decode( substr( $auth, 6 ), true ); // phpcs:ignore
				if ( $decoded && strpos( $decoded, ':' ) !== false ) {
					list( $public, $secret ) = explode( ':', $decoded, 2 );
				}
			}
		}

		if ( ! $public || ! $secret ) {
			return null;
		}

		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( // phpcs:ignore WordPress.DB
			'SELECT * FROM ' . wpistic_lsi_table( 'api_keys' ) . ' WHERE public_key = %s AND status = %s',
			sanitize_text_field( $public ),
			'active'
		), ARRAY_A );

		if ( ! $row ) {
			return null;
		}

		if ( ! WPistic_LSI_Crypto::verify_secret( $secret, $row['secret_hash'] ) ) {
			return null;
		}

		WPistic_LSI_DB::update( 'api_keys', array( 'last_used_at' => wpistic_lsi_now() ), array( 'api_key_id' => (int) $row['api_key_id'] ) );

		return $row;
	}

	/**
	 * Checks whether an API key row grants a permission.
	 *
	 * @param array  $api_key API key row.
	 * @param string $perm    Permission slug.
	 * @return bool
	 */
	public static function has_permission( $api_key, $perm ) {
		if ( empty( $api_key ) ) {
			return false;
		}
		$perms = ! empty( $api_key['permissions'] ) ? json_decode( $api_key['permissions'], true ) : array();
		if ( ! is_array( $perms ) ) {
			return false;
		}
		return in_array( $perm, $perms, true ) || in_array( '*', $perms, true );
	}

	/**
	 * Lists API keys.
	 *
	 * @return array
	 */
	public static function get_api_keys() {
		global $wpdb;
		return $wpdb->get_results( 'SELECT api_key_id, name, public_key, permissions, status, last_used_at, created_at FROM ' . wpistic_lsi_table( 'api_keys' ) . ' ORDER BY api_key_id DESC', ARRAY_A ); // phpcs:ignore WordPress.DB
	}

	/**
	 * Tracks request count per identifier (IP or public key) for rate limiting.
	 *
	 * @param string $identifier Identifier.
	 * @return bool True if allowed, false if blocked.
	 */
	public static function check_rate_limit( $identifier ) {
		$limit = (int) wpistic_lsi_get_setting( 'rate_limit_per_minute', 60 );
		if ( $limit <= 0 ) {
			return true;
		}
		$key   = 'wpistic_lsi_rl_' . md5( $identifier );
		$count = (int) get_transient( $key );
		if ( $count >= $limit ) {
			return false;
		}
		set_transient( $key, $count + 1, MINUTE_IN_SECONDS );
		return true;
	}
}
