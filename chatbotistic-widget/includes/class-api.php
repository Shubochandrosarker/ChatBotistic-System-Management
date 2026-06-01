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
	public static function get_password(): string { return (string) get_option( 'cbw_tochat_password', '' ); }

	public static function set_credentials( string $email, string $password ): void {
		update_option( 'cbw_tochat_email', sanitize_email( $email ) );
		update_option( 'cbw_tochat_password', $password );
		self::clear_token();
	}

	public static function clear_credentials(): void {
		delete_option( 'cbw_tochat_email' );
		delete_option( 'cbw_tochat_password' );
		self::clear_token();
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
