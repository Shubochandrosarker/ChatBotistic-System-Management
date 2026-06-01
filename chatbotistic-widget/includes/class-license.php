<?php
namespace Chatbotistic_Widget;

defined( 'ABSPATH' ) || exit;

/**
 * Licenseistic client.
 *
 * Talks to chatbotistic.com/wp-json/licenseistic/v1/* :
 *   POST  /activate    — validates key, registers this domain, returns plan caps.
 *   POST  /heartbeat   — daily ping so the master can track active installs.
 *   POST  /deactivate  — releases this domain from the user's license.
 *
 * Expected JSON response shape (success):
 *   {
 *     ok: true,
 *     status: "active" | "inactive",
 *     tier: "free" | "starter" | "growth" | "agency",
 *     plan_name: "Growth",
 *     max_widgets: 5,         // -1 = unlimited
 *     max_agents:  10,
 *     max_domains: 3,
 *     expires_at:  "2026-12-31T00:00:00Z",
 *     customer_email: "user@example.com"
 *   }
 */
final class License {

	const OPT_KEY      = 'cbw_license_key';
	const OPT_STATUS   = 'cbw_license_status';
	const OPT_TIER     = 'cbw_license_tier';
	const OPT_PAYLOAD  = 'cbw_license_payload';
	const OPT_LASTSEEN = 'cbw_license_last_check';
	const OPT_INSTANCE = 'cbw_license_instance_id';
	const OPT_GRACE    = 'cbw_license_grace_since';

	/**
	 * Grace window after the license server becomes unreachable. Premium
	 * features keep working during this window so a transient outage never
	 * breaks a customer's site; only a sustained failure downgrades to Free.
	 */
	const GRACE_PERIOD = 3 * DAY_IN_SECONDS;

	/** Free-tier caps used when no valid license is present. */
	const FREE_CAPS = [
		'tier'        => 'free',
		'plan_name'   => 'Free',
		'max_widgets' => 1,
		'max_agents'  => 1,
		'max_domains' => 1,
		'white_label' => false,
		'branding'    => true,
	];

	// ── Accessors ────────────────────────────────────────────────────────────

	public static function get_key(): string {
		return (string) get_option( self::OPT_KEY, '' );
	}

	public static function is_active(): bool {
		return 'active' === get_option( self::OPT_STATUS, '' );
	}

	public static function get_tier(): string {
		return (string) get_option( self::OPT_TIER, self::FREE_CAPS['tier'] );
	}

	/**
	 * Returns the cached plan payload, or the free-tier caps if no license.
	 */
	public static function get_caps(): array {
		$payload = get_option( self::OPT_PAYLOAD, [] );
		if ( ! is_array( $payload ) || empty( $payload ) || ! self::is_active() ) {
			return self::FREE_CAPS;
		}
		return wp_parse_args( $payload, self::FREE_CAPS );
	}

	public static function get_cap( string $key, $default = null ) {
		$caps = self::get_caps();
		return $caps[ $key ] ?? $default;
	}

	// ── Remote calls ──────────────────────────────────────────────────────────

	public static function activate( string $key ) {
		$key = trim( $key );
		if ( '' === $key ) {
			return new \WP_Error( 'cbw_license_empty', __( 'Please enter your license key.', 'chatbotistic-widget' ) );
		}

		// Step 1: register this domain against the license.
		$res = self::call_license_endpoint( 'activate', array_merge( [
			'license_key' => $key,
			'product'     => CBW_PRODUCT_SLUG,
			'site_url'    => home_url(),
			'domain'      => wp_parse_url( home_url(), PHP_URL_HOST ),
			'instance_id' => self::instance_id(),
			'site_title'  => get_bloginfo( 'name' ),
		], self::diagnostics() ) );

		if ( is_wp_error( $res ) ) return $res;

		if ( ! self::envelope_ok( $res ) ) {
			$msg = self::envelope_message( $res ) ?: __( 'License could not be activated.', 'chatbotistic-widget' );
			return new \WP_Error( 'cbw_license_rejected', $msg );
		}

		update_option( self::OPT_KEY, $key );

		// Step 2: pull plan caps. /activate only confirms the domain; the plan
		// entitlements live behind /entitlements.
		$flat = self::fetch_entitlements( $key );
		if ( is_array( $flat ) ) {
			update_option( self::OPT_STATUS,  sanitize_key( $flat['status'] ?: 'active' ) );
			update_option( self::OPT_TIER,    sanitize_key( $flat['tier']   ?: 'free' ) );
			update_option( self::OPT_PAYLOAD, self::clean_payload( $flat ) );
		} else {
			// Domain is registered but entitlements weren't reachable yet; treat
			// as active and let the next heartbeat fill in the caps.
			update_option( self::OPT_STATUS, 'active' );
			update_option( self::OPT_TIER,   'free' );
		}

		update_option( self::OPT_LASTSEEN, time() );
		delete_option( self::OPT_GRACE );

		return $res;
	}

	public static function deactivate() {
		$key = self::get_key();
		if ( '' === $key ) return true;

		$res = self::call_license_endpoint( 'deactivate', [
			'license_key' => $key,
			'product'     => CBW_PRODUCT_SLUG,
			'site_url'    => home_url(),
			'domain'      => wp_parse_url( home_url(), PHP_URL_HOST ),
			'instance_id' => self::instance_id(),
		] );

		delete_option( self::OPT_KEY );
		delete_option( self::OPT_STATUS );
		delete_option( self::OPT_TIER );
		delete_option( self::OPT_PAYLOAD );
		delete_option( self::OPT_LASTSEEN );

		return is_wp_error( $res ) ? $res : true;
	}

	/** Best-effort fire-and-forget called from register_deactivation_hook. */
	public static function deactivate_silently(): void {
		$key = self::get_key();
		if ( '' === $key ) return;
		self::call_license_endpoint( 'deactivate', [
			'license_key' => $key,
			'product'     => CBW_PRODUCT_SLUG,
			'site_url'    => home_url(),
			'domain'      => wp_parse_url( home_url(), PHP_URL_HOST ),
			'instance_id' => self::instance_id(),
		], 5 );
	}

	/**
	 * Cron callback: validate the license and resync plan caps.
	 *
	 * Failure handling has three distinct cases:
	 *   1. Server unreachable / 5xx (transient) → keep current caps and start a
	 *      grace window. Premium features keep working until the window lapses,
	 *      then we downgrade to Free. This is what stops a brief outage from
	 *      breaking customer sites.
	 *   2. Server reachable but says the key is invalid/expired (authoritative)
	 *      → flag inactive immediately; no grace.
	 *   3. Success → refresh status + caps and clear any grace window.
	 */
	public static function run_heartbeat(): void {
		$key = self::get_key();
		if ( '' === $key ) return;

		// Throttle: don't resync more than once every ~11 hours (the cron runs
		// every 12h; the margin absorbs scheduler jitter).
		$last = (int) get_option( self::OPT_LASTSEEN, 0 );
		if ( $last && ( time() - $last ) < ( ( 12 * HOUR_IN_SECONDS ) - HOUR_IN_SECONDS ) ) return;

		// Best-effort ping so the master records this install's last-seen. Its
		// result doesn't drive local state — /entitlements is authoritative.
		self::call_license_endpoint( 'heartbeat', array_merge( [
			'license_key' => $key,
			'product'     => CBW_PRODUCT_SLUG,
			'site_url'    => home_url(),
			'domain'      => wp_parse_url( home_url(), PHP_URL_HOST ),
			'instance_id' => self::instance_id(),
		], self::diagnostics() ) );

		$ent = self::request_entitlements( $key );

		// Case 1: transient failure — apply the grace window.
		if ( is_wp_error( $ent ) ) {
			self::apply_grace_window();
			return;
		}

		// Case 2: authoritative rejection.
		if ( ! self::envelope_ok( $ent ) ) {
			update_option( self::OPT_STATUS, 'inactive' );
			delete_option( self::OPT_GRACE );
			update_option( self::OPT_LASTSEEN, time() );
			return;
		}

		// Case 3: success.
		$flat = self::flatten_entitlements( $ent );
		update_option( self::OPT_STATUS,  sanitize_key( $flat['status'] ?: 'active' ) );
		update_option( self::OPT_TIER,    sanitize_key( $flat['tier']   ?: 'free' ) );
		update_option( self::OPT_PAYLOAD, self::clean_payload( $flat ) );
		update_option( self::OPT_LASTSEEN, time() );
		delete_option( self::OPT_GRACE );
	}

	/**
	 * Open or advance the grace window after a transient sync failure. Once the
	 * window lapses, premium features are disabled (status → inactive). We do
	 * not bump OPT_LASTSEEN so the next cron run retries promptly.
	 */
	private static function apply_grace_window(): void {
		$since = (int) get_option( self::OPT_GRACE, 0 );
		if ( ! $since ) {
			update_option( self::OPT_GRACE, time() );
			return;
		}
		if ( ( time() - $since ) > self::GRACE_PERIOD ) {
			update_option( self::OPT_STATUS, 'inactive' );
		}
	}

	/**
	 * Grace state for the admin License screen.
	 *
	 * @return array{active:bool,since:int,expires:int}
	 */
	public static function grace_state(): array {
		$since = (int) get_option( self::OPT_GRACE, 0 );
		return [
			'active'  => $since > 0,
			'since'   => $since,
			'expires' => $since ? $since + self::GRACE_PERIOD : 0,
		];
	}

	// ── HTTP ──────────────────────────────────────────────────────────────────

	private static function request( string $path, array $body, int $timeout = 15 ) {
		$url = rtrim( Brand::license_base_url(), '/' ) . $path;
		$res = wp_remote_post( $url, [
			'timeout'   => $timeout,
			'sslverify' => true,
			'headers'   => [
				'Content-Type' => 'application/json',
				'Accept'       => 'application/json',
				'X-CBW-Plugin' => CBW_VERSION,
			],
			'body'      => wp_json_encode( $body ),
		] );

		if ( is_wp_error( $res ) ) {
			return new \WP_Error( 'cbw_license_network', __( 'Could not reach the license server. Please try again.', 'chatbotistic-widget' ), $res->get_error_message() );
		}

		$code = (int) wp_remote_retrieve_response_code( $res );
		$raw  = wp_remote_retrieve_body( $res );
		$data = json_decode( $raw, true );

		if ( $code >= 500 ) {
			return new \WP_Error( 'cbw_license_server', __( 'License server error. Please try again in a moment.', 'chatbotistic-widget' ) );
		}
		if ( ! is_array( $data ) ) {
			return new \WP_Error( 'cbw_license_bad_response', __( 'Unexpected response from the license server.', 'chatbotistic-widget' ) );
		}
		return $data;
	}

	/** GET request (for read endpoints like /entitlements). */
	private static function request_get( string $path, array $query, int $timeout = 15 ) {
		$url = add_query_arg( $query, rtrim( Brand::license_base_url(), '/' ) . $path );
		$res = wp_remote_get( $url, [
			'timeout'   => $timeout,
			'sslverify' => true,
			'headers'   => [
				'Accept'       => 'application/json',
				'X-CBW-Plugin' => CBW_VERSION,
			],
		] );

		if ( is_wp_error( $res ) ) {
			return new \WP_Error( 'cbw_license_network', __( 'Could not reach the license server. Please try again.', 'chatbotistic-widget' ), $res->get_error_message() );
		}

		$code = (int) wp_remote_retrieve_response_code( $res );
		$data = json_decode( wp_remote_retrieve_body( $res ), true );

		if ( $code >= 500 ) {
			return new \WP_Error( 'cbw_license_server', __( 'License server error. Please try again in a moment.', 'chatbotistic-widget' ) );
		}
		if ( ! is_array( $data ) ) {
			return new \WP_Error( 'cbw_license_bad_response', __( 'Unexpected response from the license server.', 'chatbotistic-widget' ) );
		}
		return $data;
	}

	/** Raw /entitlements request (array envelope or WP_Error). */
	private static function request_entitlements( string $key ) {
		return self::request_get( '/entitlements', [
			'license_key' => $key,
			'site_url'    => home_url(),
			'instance_id' => self::instance_id(),
		] );
	}

	/**
	 * Fetch + flatten entitlements. Returns a flat caps array on success, or
	 * null when the server is unreachable or rejects the key.
	 *
	 * @param string $key License key.
	 * @return array|null
	 */
	private static function fetch_entitlements( string $key ): ?array {
		$res = self::request_entitlements( $key );
		if ( is_wp_error( $res ) || ! self::envelope_ok( $res ) ) {
			return null;
		}
		return self::flatten_entitlements( $res );
	}

	/** Whether a Licenseistic response envelope indicates success. */
	private static function envelope_ok( $res ): bool {
		return is_array( $res ) && ( ! empty( $res['success'] ) || ! empty( $res['ok'] ) );
	}

	/** Human message from a Licenseistic response envelope. */
	private static function envelope_message( $res ): string {
		return is_array( $res ) && isset( $res['message'] ) ? (string) $res['message'] : '';
	}

	/**
	 * Flatten Licenseistic's { success, data:{ plan, entitlements:{...} } }
	 * entitlements envelope into the flat caps shape the rest of the plugin uses.
	 *
	 * @param array $res Response envelope.
	 * @return array
	 */
	private static function flatten_entitlements( array $res ): array {
		$data = isset( $res['data'] ) && is_array( $res['data'] ) ? $res['data'] : $res;
		$ent  = isset( $data['entitlements'] ) && is_array( $data['entitlements'] ) ? $data['entitlements'] : [];

		$flat = [
			'status'     => (string) ( $data['status'] ?? '' ),
			'tier'       => (string) ( $data['plan'] ?? ( $data['tier'] ?? 'free' ) ),
			'plan_name'  => (string) ( $data['plan_name'] ?? '' ),
			'expires_at' => (string) ( $data['expires_at'] ?? '' ),
		];

		foreach ( [ 'max_widgets', 'max_agents', 'max_domains' ] as $k ) {
			if ( isset( $ent[ $k ] ) ) {
				$flat[ $k ] = (int) $ent[ $k ];
			} elseif ( isset( $data[ $k ] ) ) {
				$flat[ $k ] = (int) $data[ $k ];
			}
		}
		foreach ( [ 'white_label', 'branding' ] as $k ) {
			if ( isset( $ent[ $k ] ) ) {
				$flat[ $k ] = (bool) $ent[ $k ];
			}
		}

		// Optional white-label brand fields, if the server includes them.
		foreach ( [ 'brand_label', 'brand_tagline', 'brand_homepage', 'support_email', 'logo_url', 'custom_domain', 'api_base_url', 'customer_email' ] as $k ) {
			if ( isset( $data[ $k ] ) ) {
				$flat[ $k ] = $data[ $k ];
			}
		}

		return $flat;
	}

	/**
	 * Call short alias endpoints first, then fall back to canonical Licenseistic routes.
	 *
	 * @param string $action  activate|deactivate|heartbeat
	 * @param array  $body    Request payload.
	 * @param int    $timeout Timeout seconds.
	 * @return array|\WP_Error
	 */
	private static function call_license_endpoint( string $action, array $body, int $timeout = 15 ) {
		$short = [
			'activate'   => '/activate',
			'deactivate' => '/deactivate',
			'heartbeat'  => '/heartbeat',
		];
		$canonical = [
			'activate'   => '/license/activate',
			'deactivate' => '/license/deactivate',
			'heartbeat'  => '/license/ping',
		];

		$first = self::request( $short[ $action ] ?? '/activate', $body, $timeout );
		if ( ! is_wp_error( $first ) ) {
			return $first;
		}

		$msg = (string) $first->get_error_message();
		$recoverable = in_array( $first->get_error_code(), [ 'cbw_license_bad_response', 'cbw_license_server' ], true )
			|| false !== stripos( $msg, 'Unexpected response' );
		if ( ! $recoverable ) {
			return $first;
		}

		return self::request( $canonical[ $action ] ?? '/license/activate', $body, $timeout );
	}

	/**
	 * Site diagnostics included with license calls so chatbotistic.com can
	 * track this install (the widget heartbeat doubles as the site connector).
	 *
	 * We deliberately do NOT send a license_key_hash: the plain key is already
	 * required by the API, and a widget-side hash can't match the server's
	 * salted hash anyway — so it would be redundant data for no benefit.
	 *
	 * @return array<string,mixed>
	 */
	private static function diagnostics(): array {
		return [
			'home_url'        => home_url(),
			'admin_email'     => sanitize_email( (string) get_option( 'admin_email', '' ) ),
			'plugin_version'  => CBW_VERSION,
			'wp_version'      => get_bloginfo( 'version' ),
			'php_version'     => PHP_VERSION,
			'plan_slug'       => self::get_tier(),
			'license_status'  => self::is_active() ? 'active' : (string) get_option( self::OPT_STATUS, 'inactive' ),
			'usage_count'     => ( self::get_key() && self::is_active() ) ? 1 : 0,
			'last_seen'       => (int) get_option( self::OPT_LASTSEEN, 0 ),
		];
	}

	/**
	 * Stable install instance ID sent with all license calls.
	 */
	private static function instance_id(): string {
		$id = (string) get_option( self::OPT_INSTANCE, '' );
		if ( '' !== $id ) {
			return $id;
		}
		$id = wp_generate_password( 24, false, false );
		update_option( self::OPT_INSTANCE, $id );
		return $id;
	}

	/** Whitelist the payload fields we trust + persist. */
	private static function clean_payload( array $res ): array {
		$keys = [
			'status', 'tier', 'plan_name', 'max_widgets', 'max_agents', 'max_domains',
			'white_label', 'branding', 'expires_at', 'customer_email',
			// White-label brand fields delivered by the M→L Bridge.
			'brand_label', 'brand_tagline', 'brand_homepage', 'support_email',
			'logo_url', 'custom_domain', 'api_base_url',
		];
		$out  = [];
		foreach ( $keys as $k ) {
			if ( ! isset( $res[ $k ] ) ) continue;
			if ( in_array( $k, [ 'max_widgets', 'max_agents', 'max_domains' ], true ) ) {
				$out[ $k ] = (int) $res[ $k ];
			} elseif ( in_array( $k, [ 'white_label', 'branding' ], true ) ) {
				$out[ $k ] = (bool) $res[ $k ];
			} elseif ( in_array( $k, [ 'brand_homepage', 'logo_url', 'custom_domain', 'api_base_url' ], true ) ) {
				$out[ $k ] = esc_url_raw( (string) $res[ $k ] );
			} elseif ( 'support_email' === $k ) {
				$out[ $k ] = sanitize_email( (string) $res[ $k ] );
			} else {
				$out[ $k ] = sanitize_text_field( (string) $res[ $k ] );
			}
		}
		return $out;
	}
}
