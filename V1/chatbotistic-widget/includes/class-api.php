<?php
namespace Chatbotistic_Widget;

defined( 'ABSPATH' ) || exit;

/**
 * Chatbotistic / Tochat backend API client.
 *
 * The Chatbotistic master account on services.tochat.be is what powers widget
 * analytics. The user signs in once with Chatbotistic credentials; the plugin
 * caches a JWT in a transient and reuses it for stats + referral queries.
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
		// Legacy plaintext rows (no v1: prefix) — read once, then re-write
		// encrypted on the next set_credentials. We return the raw value so the
		// Tochat API call still works during the migration window.
		if ( 0 !== strpos( $stored, 'v1:' ) ) {
			return $stored;
		}
		return self::decrypt_secret( $stored );
	}

	public static function set_credentials( string $email, string $password ): void {
		update_option( 'cbw_tochat_email', sanitize_email( $email ) );
		// Don't store the Tochat password as plaintext in wp_options. Encrypt
		// at rest with AUTH_KEY (or sodium if available) so an attacker with
		// read-only DB access can't lift the customer's Tochat credentials.
		update_option( 'cbw_tochat_password', self::encrypt_secret( $password ) );
		self::clear_token();
	}

	public static function clear_credentials(): void {
		delete_option( 'cbw_tochat_email' );
		delete_option( 'cbw_tochat_password' );
		self::clear_token();
	}

	/**
	 * Derive a stable 32-byte key from WordPress's AUTH_KEY+AUTH_SALT for use
	 * with openssl_encrypt. Survives plugin upgrades; rotating AUTH_KEY would
	 * invalidate stored credentials, which is the correct security posture.
	 */
	private static function secret_key(): string {
		$material = ( defined( 'AUTH_KEY' )  ? AUTH_KEY  : '' )
			. '|cbw_tochat|'
			. ( defined( 'AUTH_SALT' ) ? AUTH_SALT : '' );
		return hash( 'sha256', $material, true );
	}

	private static function encrypt_secret( string $plain ): string {
		if ( '' === $plain ) {
			return '';
		}
		$iv     = random_bytes( 16 );
		$cipher = openssl_encrypt( $plain, 'aes-256-cbc', self::secret_key(), OPENSSL_RAW_DATA, $iv );
		if ( false === $cipher ) {
			return $plain; // openssl unavailable — fall back to plain rather than lose creds.
		}
		return 'v1:' . base64_encode( $iv . $cipher );
	}

	private static function decrypt_secret( string $stored ): string {
		if ( 0 !== strpos( $stored, 'v1:' ) ) {
			return $stored;
		}
		$blob = base64_decode( substr( $stored, 3 ), true );
		if ( false === $blob || strlen( $blob ) < 17 ) {
			return '';
		}
		$iv     = substr( $blob, 0, 16 );
		$cipher = substr( $blob, 16 );
		$plain  = openssl_decrypt( $cipher, 'aes-256-cbc', self::secret_key(), OPENSSL_RAW_DATA, $iv );
		return false === $plain ? '' : $plain;
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
