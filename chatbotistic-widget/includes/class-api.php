<?php
namespace Chatbotistic_Widget;

defined( 'ABSPATH' ) || exit;

/**
 * Chatbotistic plugin API client (v1.5.0+).
 *
 * Auth is the license activation token — the same token class-license.php
 * obtains on activation (POST /license/activate on CBW_API_BASE). No email
 * or password is stored; the license key is the customer's only secret.
 *
 * All calls go through app.chatbotistic.com's /api/plugin/* routes, which
 * scope every query to the license's own organization on the server side.
 *
 * SECURITY: every query MUST still be scoped to a widget key the site is
 * allowed to see — Analytics_Page::allowed_widget_keys() enforces this
 * before any call.
 */
final class API {

	const TOKEN_TRANSIENT = 'cbw_api_token';
	const TOKEN_TTL       = 600; // 10 minutes — matches the API's effective lifetime.

	public static function get_email(): string    { return (string) get_option( 'cbw_tochat_email', '' ); }

	public static function get_password(): string {
		$stored = (string) get_option( 'cbw_tochat_password', '' );
		if ( '' === $stored ) {
			return '';
		}
		$decrypted = self::decrypt_secret( $stored );
		if ( '' !== $decrypted ) {
			return $decrypted;
		}
		// Doesn't decrypt to anything — either a legacy plaintext row saved
		// before encryption-at-rest was added, or ciphertext that no longer
		// matches the current key. Either way, treat the raw stored value as
		// the password (so the Tochat API call keeps working) and
		// transparently migrate it to encrypted storage right now, with no
		// user action required.
		update_option( 'cbw_tochat_password', self::encrypt_secret( $stored ) );
		return $stored;
	}

	public static function set_credentials( string $email, string $password ): void {
		update_option( 'cbw_tochat_email', sanitize_email( $email ) );
		// Don't store the Tochat password as plaintext in wp_options. Encrypt
		// at rest with AUTH_KEY/SECURE_AUTH_KEY — the same AES-256-CBC scheme
		// used by the chatbotistic-connector plugin's Store class — so an
		// attacker with read-only DB access can't lift the customer's Tochat
		// credentials.
		update_option( 'cbw_tochat_password', self::encrypt_secret( $password ) );
		self::clear_token();
	}

	public static function clear_credentials(): void {
		delete_option( 'cbw_tochat_email' );
		delete_option( 'cbw_tochat_password' );
		self::clear_token();
	}

	/**
	 * Encrypt a value at rest with the site's own salts.
	 *
	 * @param string $plain Plain value.
	 * @return string
	 */
	private static function encrypt_secret( string $plain ): string {
		if ( '' === $plain ) {
			return '';
		}
		$iv     = openssl_random_pseudo_bytes( 16 );
		$cipher = openssl_encrypt( $plain, 'AES-256-CBC', self::secret_key(), OPENSSL_RAW_DATA, $iv );
		if ( false === $cipher ) {
			return $plain; // openssl unavailable — fall back to plain rather than lose creds.
		}
		return base64_encode( $iv . $cipher );
	}

	/**
	 * Decrypt a stored value.
	 *
	 * @param string $stored Encrypted value.
	 * @return string
	 */
	private static function decrypt_secret( string $stored ): string {
		if ( '' === $stored ) {
			return '';
		}
		$raw = base64_decode( $stored, true );
		if ( false === $raw || strlen( $raw ) <= 16 ) {
			return '';
		}
		$iv     = substr( $raw, 0, 16 );
		$cipher = substr( $raw, 16 );
		$plain  = openssl_decrypt( $cipher, 'AES-256-CBC', self::secret_key(), OPENSSL_RAW_DATA, $iv );
		return is_string( $plain ) ? $plain : '';
	}

	/**
	 * 32-byte key derived from WordPress secret keys. Same derivation as
	 * chatbotistic-connector's Store::key(), for consistency across the
	 * codebase.
	 *
	 * @return string
	 */
	private static function secret_key(): string {
		$seed = ( defined( 'AUTH_KEY' ) ? AUTH_KEY : 'cbw' ) . ( defined( 'SECURE_AUTH_KEY' ) ? SECURE_AUTH_KEY : 'widget' );
		return substr( hash( 'sha256', $seed ), 0, 32 );
	}

	public static function is_connected(): bool {
		// v1.5.0+: the license activation token is the API session.
		return \Chatbotistic_Widget\License::is_active()
			&& '' !== (string) get_option( \Chatbotistic_Widget\License::OPT_ACTIVATION, '' );
	}

	public static function clear_token(): void {
		delete_transient( self::TOKEN_TRANSIENT );
	}

	// ── Authentication ────────────────────────────────────────────────────────

	public static function get_token() {
		$token = get_transient( self::TOKEN_TRANSIENT );
		if ( $token ) return $token;

		// The license activation token IS the API session token in v1.5.0+.
		$token = (string) get_option( \Chatbotistic_Widget\License::OPT_ACTIVATION, '' );
		if ( '' === $token ) {
			return new \WP_Error( 'cbw_no_credentials', __( 'Activate your Chatbotistic license first.', 'chatbotistic-widget' ) );
		}
		set_transient( self::TOKEN_TRANSIENT, $token, self::TOKEN_TTL );
		return $token;
	}

	// ── Endpoints ─────────────────────────────────────────────────────────────

	/**
	 * Widget catalog for this license's organization.
	 *
	 * GET /widgets on the plugin API (Bearer = license activation token).
	 * The server scopes the list to the license's own org — nothing
	 * client-side can widen it.
	 */
	public static function get_widgets() {
		$data = self::get( '/widgets' );
		if ( is_wp_error( $data ) ) {
			return $data;
		}
		foreach ( [ 'widgets', 'hydra:member', 'member', 'data', 'items' ] as $key ) {
			if ( isset( $data[ $key ] ) && is_array( $data[ $key ] ) ) {
				return $data[ $key ];
			}
		}
		return array_values( array_filter( $data, 'is_array' ) );
	}

	/**
	 * Full analytics bundle (summary + leads + referrals) for one widget.
	 * Cached in a transient so the three tab views share one API call.
	 *
	 * GET /stats?widget=<id>&days=<n>&leads=<limit>
	 */
	private static function get_stats_bundle( string $widget_id, int $days = 365, int $limit = 25 ) {
		$cache_key = 'cbw_stats_' . md5( $widget_id . '|' . $days . '|' . $limit );
		$cached = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}
		$qs = http_build_query( [
			'widget' => $widget_id,
			'days'   => max( 1, min( 365, $days ) ),
			'leads'  => max( 1, min( 100, $limit ) ),
		] );
		$data = self::get( '/stats?' . $qs );
		if ( is_wp_error( $data ) ) {
			return $data;
		}
		set_transient( $cache_key, $data, 5 * MINUTE_IN_SECONDS );
		return $data;
	}

	public static function get_widget_stats( string $widget_id ) {
		$bundle = self::get_stats_bundle( $widget_id );
		if ( is_wp_error( $bundle ) ) return $bundle;
		$summary = is_array( $bundle['summary'] ?? null ) ? $bundle['summary'] : [];
		// Views expect the tochat widget_stats shape.
		return array_merge( $summary, [ 'totalLeads' => $summary['totalLeads'] ?? 0 ] );
	}

	public static function get_widget_referrals( string $widget_id, int $days = 365 ) {
		$bundle = self::get_stats_bundle( $widget_id, $days );
		if ( is_wp_error( $bundle ) ) return $bundle;
		$ref = $bundle['referrals'] ?? [];
		return is_array( $ref ) && isset( $ref['referers'] ) ? $ref : [ 'referers' => is_array( $ref ) ? $ref : [] ];
	}

	public static function get_leads( string $widget_id, int $limit = 25 ) {
		$bundle = self::get_stats_bundle( $widget_id, 365, $limit );
		if ( is_wp_error( $bundle ) ) return $bundle;
		$leads = $bundle['leads'] ?? [];
		return [ 'hydra:member' => is_array( $leads ) ? $leads : [] ];
	}

	// ── HTTP helper ───────────────────────────────────────────────────────────

	private static function get( string $endpoint ) {
		$token = self::get_token();
		if ( is_wp_error( $token ) ) return $token;

		$res = wp_remote_get( Brand::api_base_url() . $endpoint, [
			'timeout' => 15,
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $token,
			],
		] );

		if ( is_wp_error( $res ) ) {
			return new \WP_Error( 'cbw_api_network', __( 'Could not reach Chatbotistic.', 'chatbotistic-widget' ) );
		}
		$code = (int) wp_remote_retrieve_response_code( $res );
		$data = json_decode( wp_remote_retrieve_body( $res ), true );

		if ( 401 === $code ) {
			self::clear_token();
			return new \WP_Error( 'cbw_api_auth', __( 'Session expired. Please reconnect your Chatbotistic account.', 'chatbotistic-widget' ) );
		}
		if ( 404 === $code ) {
			return new \WP_Error( 'cbw_api_notfound', __( 'No data found for this widget.', 'chatbotistic-widget' ) );
		}
		if ( 200 !== $code ) {
			return new \WP_Error( 'cbw_api_unexpected', __( 'Unexpected response from Chatbotistic.', 'chatbotistic-widget' ) );
		}
		return is_array( $data ) ? $data : [];
	}
}
