<?php
namespace Chatbotistic_Widget;

defined( 'ABSPATH' ) || exit;

/**
 * Chatbotistic white-label (tochat.be) API client.
 *
 * The site owner signs in once with their own Chatbotistic account
 * (app.chatbotistic.com); the plugin caches a JWT in a transient and
 * reuses it for stats + referral queries.
 *
 * SECURITY: every query MUST be scoped to a widget key the site is
 * allowed to see — Analytics_Page::allowed_widget_keys() enforces this
 * before any call. Never fetch stats for an arbitrary UUID with these
 * credentials: on shared accounts that would leak other customers'
 * data.
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
		return self::get_email() && self::get_password();
	}

	public static function clear_token(): void {
		delete_transient( self::TOKEN_TRANSIENT );
	}

	// ── Authentication ────────────────────────────────────────────────────────

	public static function get_token() {
		$token = get_transient( self::TOKEN_TRANSIENT );
		if ( $token ) return $token;

		$email = self::get_email();
		$pass  = self::get_password();
		if ( ! $email || ! $pass ) {
			return new \WP_Error( 'cbw_no_credentials', __( 'Connect your Chatbotistic account first.', 'chatbotistic-widget' ) );
		}

		$res = wp_remote_post( Brand::api_base_url() . '/api/authentication_token', [
			'timeout' => 20,
			'headers' => [ 'Content-Type' => 'application/json' ],
			'body'    => wp_json_encode( [ 'email' => $email, 'password' => $pass ] ),
		] );

		if ( is_wp_error( $res ) ) {
			return new \WP_Error( 'cbw_api_network', __( 'Could not reach Chatbotistic. Please try again.', 'chatbotistic-widget' ) );
		}
		$code = (int) wp_remote_retrieve_response_code( $res );
		$data = json_decode( wp_remote_retrieve_body( $res ), true );

		if ( 401 === $code ) {
			return new \WP_Error( 'cbw_api_auth', __( 'Invalid Chatbotistic credentials.', 'chatbotistic-widget' ) );
		}
		if ( 200 !== $code || empty( $data['token'] ) ) {
			return new \WP_Error( 'cbw_api_unexpected', __( 'Unexpected response from Chatbotistic.', 'chatbotistic-widget' ) );
		}

		set_transient( self::TOKEN_TRANSIENT, sanitize_text_field( $data['token'] ), self::TOKEN_TTL );
		return $data['token'];
	}

	// ── Endpoints ─────────────────────────────────────────────────────────────

	/**
	 * List widgets from the connected customer's own Tochat account.
	 *
	 * The account JWT is the primary tenant boundary. This method deliberately
	 * does not accept a userClient or arbitrary filter from the browser.
	 */
	public static function get_widgets() {
		$data = self::get( '/api/v2/widgets?itemsPerPage=100' );
		if ( is_wp_error( $data ) ) {
			return $data;
		}
		foreach ( [ 'hydra:member', 'member', 'data', 'items' ] as $key ) {
			if ( isset( $data[ $key ] ) && is_array( $data[ $key ] ) ) {
				return $data[ $key ];
			}
		}
		return array_values( array_filter( $data, 'is_array' ) );
	}

	public static function get_widget_stats( string $widget_id ) {
		return self::get( '/api/v2/widget_stats/' . rawurlencode( $widget_id ) );
	}

	public static function get_widget_referrals( string $widget_id, int $days = 365 ) {
		$qs = http_build_query( [
			'order' => 'desc',
			'from'  => wp_date( 'Y-m-d', strtotime( "-{$days} days" ) ),
			'to'    => wp_date( 'Y-m-d', strtotime( '-1 day' ) ),
		] );
		return self::get( '/api/v2/' . rawurlencode( $widget_id ) . '/referer-graph?' . $qs );
	}

	public static function get_leads( string $widget_id, int $limit = 25 ) {
		$qs = http_build_query( [
			'business.uuid' => $widget_id,
			'order[id]'     => 'desc',
			'itemsPerPage'  => $limit,
		] );
		return self::get( '/api/v2/stats?' . $qs );
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
