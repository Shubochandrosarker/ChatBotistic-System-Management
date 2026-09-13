<?php
/**
 * Tochat.be (ChatWith) REST API client.
 *
 * Authenticates with the master account, caches the JWT, and retries once
 * on a 401 so an expired token never surfaces as a user-facing error.
 * Every public method returns a decoded array (or bool for DELETE) or a
 * WP_Error.
 *
 * @package Chatbotistic\Connector
 */

namespace Chatbotistic\Connector;

use WP_Error;

defined( 'ABSPATH' ) || exit;

class API {

	const TOKEN_KEY = 'cbc_api_token';
	const TOKEN_TTL = 3000; // ~50 minutes.

	// ── Authentication ────────────────────────────────────────────────────

	/**
	 * Get a bearer token, from cache or by logging in.
	 *
	 * @param bool $force Skip the cache.
	 * @return string|WP_Error
	 */
	public static function token( bool $force = false ) {
		if ( ! $force ) {
			$cached = get_transient( self::TOKEN_KEY );
			if ( is_string( $cached ) && $cached ) {
				return $cached;
			}
		}

		$email    = (string) Store::setting( 'api_email' );
		$password = Store::decrypt( (string) Store::setting( 'api_password' ) );
		if ( ! $email || ! $password ) {
			return new WP_Error( 'cbc_no_credentials', __( 'Tochat API credentials are not configured. Add them in Chatbotistic Connector settings.', 'chatbotistic-connector' ) );
		}

		$res = self::http( '/api/authentication_token', 'POST', array( 'email' => $email, 'password' => $password ), null );
		if ( is_wp_error( $res ) ) {
			return $res;
		}
		$token = $res['body']['token'] ?? '';
		if ( ! $token ) {
			return new WP_Error( 'cbc_token_empty', __( 'Tochat did not return an authentication token.', 'chatbotistic-connector' ) );
		}

		set_transient( self::TOKEN_KEY, $token, self::TOKEN_TTL );
		return $token;
	}

	/**
	 * Forget the cached token.
	 */
	public static function clear_token(): void {
		delete_transient( self::TOKEN_KEY );
	}

	// ── Widgets ───────────────────────────────────────────────────────────

	/**
	 * List widgets for one userClient tag.
	 *
	 * @param string $user_client userClient value.
	 * @return array|WP_Error
	 */
	public static function widgets_list( string $user_client ) {
		return self::collection( self::request( '/api/v2/widgets?' . http_build_query( array( 'userClient[]' => $user_client, 'itemsPerPage' => 100 ) ), 'GET' ) );
	}

	public static function widget_get( string $id ) {
		return self::request( '/api/v2/widgets/' . rawurlencode( $id ), 'GET' );
	}

	public static function widget_create( array $payload ) {
		return self::request( '/api/v2/widgets', 'POST', $payload );
	}

	public static function widget_update( string $id, array $payload ) {
		return self::request( '/api/v2/widgets/' . rawurlencode( $id ), 'PUT', $payload );
	}

	public static function widget_patch( string $id, array $payload ) {
		return self::request( '/api/v2/widgets/' . rawurlencode( $id ), 'PATCH', $payload );
	}

	public static function widget_delete( string $id ) {
		return self::request( '/api/v2/widgets/' . rawurlencode( $id ), 'DELETE' );
	}

	// ── WhatsApp operators (agents) ───────────────────────────────────────

	/**
	 * List every agent belonging to a userClient.
	 *
	 * @param string $user_client userClient value.
	 * @return array|WP_Error
	 */
	public static function operators_list( string $user_client ) {
		return self::collection( self::request( '/api/v2/whatsapp_operators?' . http_build_query( array( 'business.userClient[]' => $user_client, 'itemsPerPage' => 200 ) ), 'GET' ) );
	}

	/**
	 * List agents for a single widget.
	 *
	 * @param string $widget_id Widget UUID.
	 * @return array|WP_Error
	 */
	public static function operators_for_widget( string $widget_id ) {
		return self::collection( self::request( '/api/v2/whatsapp_operators?' . http_build_query( array( 'business.id' => $widget_id, 'itemsPerPage' => 200 ) ), 'GET' ) );
	}

	public static function operator_get( string $id ) {
		return self::request( '/api/v2/whatsapp_operators/' . rawurlencode( $id ), 'GET' );
	}

	public static function operator_create( array $payload ) {
		return self::request( '/api/v2/whatsapp_operators', 'POST', $payload );
	}

	public static function operator_update( string $id, array $payload ) {
		return self::request( '/api/v2/whatsapp_operators/' . rawurlencode( $id ), 'PUT', $payload );
	}

	public static function operator_delete( string $id ) {
		return self::request( '/api/v2/whatsapp_operators/' . rawurlencode( $id ), 'DELETE' );
	}

	// ── FAQ groups ────────────────────────────────────────────────────────

	public static function faq_list( string $operator_id ) {
		return self::collection( self::request( '/api/v2/whatsapp_operators/' . rawurlencode( $operator_id ) . '/faq_grps', 'GET' ) );
	}

	public static function faq_create( array $payload ) {
		return self::request( '/api/v2/faq_grps', 'POST', $payload );
	}

	public static function faq_get( string $id ) {
		return self::request( '/api/v2/faq_grps/' . rawurlencode( $id ), 'GET' );
	}

	public static function faq_update( string $id, array $payload ) {
		return self::request( '/api/v2/faq_grps/' . rawurlencode( $id ), 'PUT', $payload );
	}

	public static function faq_delete( string $id ) {
		return self::request( '/api/v2/faq_grps/' . rawurlencode( $id ), 'DELETE' );
	}

	// ── Leads (Stats) + analytics ─────────────────────────────────────────

	/**
	 * List leads. Always pass a userClient/business filter to stay scoped.
	 *
	 * @param array $filters Query filters.
	 * @return array|WP_Error
	 */
	public static function stats_list( array $filters ) {
		$filters['itemsPerPage'] = $filters['itemsPerPage'] ?? 100;
		return self::collection( self::request( '/api/v2/stats?' . http_build_query( $filters ), 'GET' ) );
	}

	/**
	 * Visits/clicks/leads graph for one widget.
	 *
	 * @param string $widget_id Widget UUID.
	 * @param string $from      Y-m-d.
	 * @param string $to        Y-m-d.
	 * @param string $type      day|week.
	 * @return array|WP_Error
	 */
	public static function stats_graph( string $widget_id, string $from, string $to, string $type = 'day' ) {
		return self::request(
			'/api/v2/' . rawurlencode( $widget_id ) . '/stats-graph?' . http_build_query( array( 'from' => $from, 'to' => $to, 'type' => $type ) ),
			'GET'
		);
	}

	public static function landing_links( string $widget_id ) {
		return self::request( '/api/landing-links/' . rawurlencode( $widget_id ), 'GET' );
	}

	// ── Campaigns (broadcast / drip) ──────────────────────────────────────

	public static function campaigns_list( string $user_client ) {
		return self::collection( self::request( '/api/v2/campaigns?' . http_build_query( array( 'business.userClient[]' => $user_client, 'itemsPerPage' => 100 ) ), 'GET' ) );
	}

	public static function campaign_create( array $payload ) {
		return self::request( '/api/v2/campaigns', 'POST', $payload );
	}

	public static function campaign_update( string $id, array $payload ) {
		return self::request( '/api/v2/campaigns/' . rawurlencode( $id ), 'PUT', $payload );
	}

	public static function campaign_delete( string $id ) {
		return self::request( '/api/v2/campaigns/' . rawurlencode( $id ), 'DELETE' );
	}

	// ── Audiences / many_contacts ─────────────────────────────────────────

	public static function audiences_list( string $user_client ) {
		return self::collection( self::request( '/api/v2/many_contacts?' . http_build_query( array( 'business.userClient[]' => $user_client, 'itemsPerPage' => 100 ) ), 'GET' ) );
	}

	public static function audience_create( array $payload ) {
		return self::request( '/api/v2/many_contacts', 'POST', $payload );
	}

	public static function audience_delete( string $id ) {
		return self::request( '/api/v2/many_contacts/' . rawurlencode( $id ), 'DELETE' );
	}

	// ── Booking configs ───────────────────────────────────────────────────

	public static function bookings_list( string $operator_id ) {
		return self::collection( self::request( '/api/v2/booking_configs?' . http_build_query( array( 'whatsapp' => $operator_id ) ), 'GET' ) );
	}

	public static function booking_create( array $payload ) {
		return self::request( '/api/v2/booking_configs', 'POST', $payload );
	}

	public static function booking_update( string $id, array $payload ) {
		return self::request( '/api/v2/booking_configs/' . rawurlencode( $id ), 'PUT', $payload );
	}

	public static function booking_delete( string $id ) {
		return self::request( '/api/v2/booking_configs/' . rawurlencode( $id ), 'DELETE' );
	}

	// ── Banners ───────────────────────────────────────────────────────────

	public static function banners_list( string $widget_id ) {
		return self::collection( self::request( '/api/v2/banners?' . http_build_query( array( 'business.id' => $widget_id ) ), 'GET' ) );
	}

	public static function banner_create( array $payload ) {
		return self::request( '/api/v2/banners', 'POST', $payload );
	}

	public static function banner_update( string $id, array $payload ) {
		return self::request( '/api/v2/banners/' . rawurlencode( $id ), 'PUT', $payload );
	}

	public static function banner_delete( string $id ) {
		return self::request( '/api/v2/banners/' . rawurlencode( $id ), 'DELETE' );
	}

	// ── Widget targeting / display rules ──────────────────────────────────

	public static function widget_rules_list( string $widget_id ) {
		return self::collection( self::request( '/api/v2/widget_rules?' . http_build_query( array( 'widget.uuid' => $widget_id ) ), 'GET' ) );
	}

	public static function widget_rule_create( array $payload ) {
		return self::request( '/api/v2/widget_rules', 'POST', $payload );
	}

	public static function widget_rule_delete( string $id ) {
		return self::request( '/api/v2/widget_rules/' . rawurlencode( $id ), 'DELETE' );
	}

	// ── Payment links + transactions ──────────────────────────────────────

	public static function payment_links_list( string $user_client ) {
		return self::collection( self::request( '/api/v2/payment_links?' . http_build_query( array( 'business.userClient[]' => $user_client, 'itemsPerPage' => 100 ) ), 'GET' ) );
	}

	public static function payment_link_create( array $payload ) {
		return self::request( '/api/v2/payment_links', 'POST', $payload );
	}

	public static function payment_link_delete( string $id ) {
		return self::request( '/api/v2/payment_links/' . rawurlencode( $id ), 'DELETE' );
	}

	public static function transactions_list( array $filters ) {
		$filters['itemsPerPage'] = $filters['itemsPerPage'] ?? 100;
		return self::collection( self::request( '/api/v2/transactions?' . http_build_query( $filters ), 'GET' ) );
	}

	// ── Lead export via long-lived API key ────────────────────────────────
	//
	// Uses a *separate* API key (not the JWT) configured in Settings →
	// "Lead Export API Key". Endpoint:
	//   GET /api/get-json-lead?fromDate=YYYY-MM-DD&page=1
	public static function get_json_leads( string $from_date = '', int $page = 1, ?string $to_date = null ) {
		$api_key = (string) Store::setting( 'lead_api_key' );
		if ( ! $api_key ) {
			return new WP_Error( 'cbc_no_lead_api_key', __( 'Lead Export API Key is not configured. Add it in Chatbotistic → Settings.', 'chatbotistic-connector' ) );
		}
		$query = array( 'page' => max( 1, $page ) );
		if ( $from_date ) $query['fromDate'] = $from_date;
		if ( $to_date )   $query['toDate']   = $to_date;
		$endpoint = '/api/get-json-lead?' . http_build_query( $query );

		$res = self::http( $endpoint, 'GET', null, $api_key );
		if ( is_wp_error( $res ) ) {
			return $res;
		}
		return self::interpret( $endpoint, 'GET', $res );
	}

	// ── Request plumbing ──────────────────────────────────────────────────

	/**
	 * Authenticated request with a one-shot retry on token expiry.
	 *
	 * @param string     $endpoint Endpoint path.
	 * @param string     $method   HTTP method.
	 * @param array|null $body     Request body.
	 * @return array|bool|WP_Error
	 */
	private static function request( string $endpoint, string $method = 'GET', ?array $body = null ) {
		$token = self::token();
		if ( is_wp_error( $token ) ) {
			return $token;
		}

		$res = self::http( $endpoint, $method, $body, $token );
		if ( is_wp_error( $res ) ) {
			return $res;
		}

		// Token expired mid-flight — refresh once and retry.
		if ( 401 === $res['status'] ) {
			self::clear_token();
			$token = self::token( true );
			if ( is_wp_error( $token ) ) {
				return $token;
			}
			$res = self::http( $endpoint, $method, $body, $token );
			if ( is_wp_error( $res ) ) {
				return $res;
			}
		}

		return self::interpret( $endpoint, $method, $res );
	}

	/**
	 * Turn an HTTP result into data or a WP_Error, and log it.
	 *
	 * @param string $endpoint Endpoint.
	 * @param string $method   Method.
	 * @param array  $res      Result from http().
	 * @return array|bool|WP_Error
	 */
	private static function interpret( string $endpoint, string $method, array $res ) {
		$status = $res['status'];
		$body   = $res['body'];
		$ok     = $status >= 200 && $status < 300;
		$uid    = get_current_user_id() ?: null;

		if ( ! $ok ) {
			$message = is_array( $body )
				? ( $body['detail'] ?? $body['hydra:description'] ?? $body['message'] ?? ( $body['violations'][0]['message'] ?? '' ) )
				: '';
			$message = $message ?: sprintf( __( 'Tochat API error (HTTP %d).', 'chatbotistic-connector' ), $status );
			Store::log( $uid, $endpoint, $method, $status, false, $message );
			return new WP_Error( 'cbc_api_' . $status, $message );
		}

		Store::log( $uid, $endpoint, $method, $status, true, '' );

		if ( 'DELETE' === strtoupper( $method ) ) {
			return true;
		}
		return is_array( $body ) ? $body : array();
	}

	/**
	 * Raw HTTP call. Never throws — returns status + decoded body, or WP_Error.
	 *
	 * @param string      $endpoint Endpoint path.
	 * @param string      $method   HTTP method.
	 * @param array|null  $body     Body.
	 * @param string|null $token    Bearer token, or null for unauthenticated.
	 * @return array{status:int,body:mixed}|WP_Error
	 */
	private static function http( string $endpoint, string $method, ?array $body, ?string $token ) {
		$headers = array(
			'Accept'       => 'application/json',
			'Content-Type' => 'application/json',
		);
		if ( $token ) {
			$headers['Authorization'] = 'Bearer ' . $token;
		}

		$args = array(
			'method'  => strtoupper( $method ),
			'headers' => $headers,
			'timeout' => 30,
		);
		if ( null !== $body && in_array( strtoupper( $method ), array( 'POST', 'PUT', 'PATCH' ), true ) ) {
			$args['body'] = wp_json_encode( $body );
			if ( 'PATCH' === strtoupper( $method ) ) {
				$args['headers']['Content-Type'] = 'application/merge-patch+json';
			}
		}

		$response = wp_remote_request( rtrim( CBC_API_BASE, '/' ) . $endpoint, $args );
		if ( is_wp_error( $response ) ) {
			Store::log( get_current_user_id() ?: null, $endpoint, $method, null, false, $response->get_error_message() );
			return $response;
		}

		return array(
			'status' => (int) wp_remote_retrieve_response_code( $response ),
			'body'   => json_decode( wp_remote_retrieve_body( $response ), true ),
		);
	}

	/**
	 * Flatten a Hydra collection to its member array.
	 *
	 * @param array|bool|WP_Error $result Result from request().
	 * @return array|WP_Error
	 */
	private static function collection( $result ) {
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		if ( is_array( $result ) && isset( $result['hydra:member'] ) ) {
			return $result['hydra:member'];
		}
		return is_array( $result ) ? $result : array();
	}

	/**
	 * Extract a resource ID from a create/get response (id or @id IRI).
	 *
	 * @param array $resource API resource.
	 * @return string
	 */
	public static function resource_id( array $resource ): string {
		if ( ! empty( $resource['id'] ) ) {
			return (string) $resource['id'];
		}
		if ( ! empty( $resource['@id'] ) ) {
			return basename( rtrim( (string) $resource['@id'], '/' ) );
		}
		return '';
	}
}
