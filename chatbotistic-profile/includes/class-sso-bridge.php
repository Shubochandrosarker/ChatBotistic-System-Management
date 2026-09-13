<?php
/**
 * SSO hand-off to the standalone app.chatbotistic.com dashboard.
 *
 * Mints a short-lived, HMAC-signed token asserting a logged-in member's
 * identity, live plan, and license status, and appends it to every
 * "Open Dashboard" link via the theme's existing `cb_dashboard_url`
 * filter (chatbotistic/inc/helpers.php) — no theme changes required.
 *
 * Wire format (must match chatbotistic-dashboard/src/lib/sso/token.ts
 * byte-for-byte):
 *
 *   <base64url(JSON payload)>.<base64url(HMAC-SHA256(payload, secret))>
 *
 * The HMAC covers the *encoded payload string*, not the raw JSON bytes —
 * this is what token.ts's verifySsoToken() checks, so the exact JSON key
 * order here doesn't matter (JSON.parse doesn't care).
 *
 * @package Chatbotistic\Profile
 */

namespace Chatbotistic\Profile;

defined( 'ABSPATH' ) || exit;

class SSO_Bridge {

	const OPTION_SECRET = 'cbp_sso_secret_enc';

	public static function register(): void {
		add_filter( 'cb_dashboard_url', array( __CLASS__, 'filter_dashboard_url' ), 10, 2 );
		add_action( 'admin_notices', array( __CLASS__, 'maybe_show_secret_notice' ) );
		add_action( 'admin_post_cbp_dismiss_sso_notice', array( __CLASS__, 'handle_dismiss_notice' ) );
		add_action( 'wp_ajax_cbp_reveal_sso_secret', array( __CLASS__, 'handle_reveal_secret' ) );
	}

	/**
	 * Hooked onto the theme's `cb_dashboard_url` filter. For a logged-in
	 * member, appends a fresh SSO token so the dashboard app logs them in
	 * and provisions/updates their org from the live plan + license — no
	 * separate signup, no stale entitlements.
	 *
	 * The token must land on the dashboard app's `/api/sso/login` route
	 * (see chatbotistic-app/src/app/api/sso/login/route.ts) — that route
	 * verifies the token, provisions the Supabase user/org, and redirects
	 * to /dashboard itself. Appending `?token=` to whatever bare path the
	 * theme built (e.g. the site root) sends the browser to a page that
	 * never reads the token at all, so the member never gets logged in.
	 * We therefore always target {scheme}://{host}/api/sso/login, dropping
	 * whatever path was originally requested.
	 *
	 * @param string $url  The plain dashboard URL the theme built.
	 * @param string $path The path fragment that was requested.
	 * @return string
	 */
	public static function filter_dashboard_url( string $url, string $path = '' ): string {
		unset( $path );
		if ( ! is_user_logged_in() ) {
			return $url;
		}
		$token = self::mint_token_for_user( get_current_user_id() );
		if ( ! $token ) {
			return $url;
		}
		$parts = wp_parse_url( $url );
		if ( empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
			return $url;
		}
		$origin = $parts['scheme'] . '://' . $parts['host'] . ( isset( $parts['port'] ) ? ':' . $parts['port'] : '' );
		return add_query_arg( 'token', $token, $origin . '/api/sso/login' );
	}

	/**
	 * Mint a signed SSO token for one WordPress user.
	 *
	 * @param int $user_id WordPress user ID.
	 * @param int $ttl     Token lifetime in seconds (dashboard default skew is 300s).
	 * @return string|null Null if the user/email/secret aren't usable.
	 */
	public static function mint_token_for_user( int $user_id, int $ttl = 300 ): ?string {
		if ( ! $user_id ) {
			return null;
		}
		$secret = self::secret();
		if ( '' === $secret ) {
			return null;
		}

		$user = get_userdata( $user_id );
		if ( ! $user instanceof \WP_User || ! $user->user_email ) {
			return null;
		}

		$now    = time();
		$claims = array(
			'sub'   => 'wp-' . $user_id,
			'email' => $user->user_email,
			'name'  => $user->display_name ?: $user->user_login,
			'plan'  => self::dashboard_plan_for_user( $user_id ),
			'iat'   => $now,
			'exp'   => $now + max( 30, $ttl ),
		);

		$license_key = (string) get_user_meta( $user_id, 'mlb_license_key', true );
		if ( '' !== $license_key ) {
			$claims['license_key'] = $license_key;
		}
		$claims['license_status'] = self::license_status_for_user( $user_id );

		return self::sign( $claims, $secret );
	}

	/**
	 * Resolve the dashboard's plan id (free|starter|growth|agency) from the
	 * user's live Memberistic membership — resolved fresh on every call so
	 * a cancellation or upgrade takes effect on the very next click, with
	 * no cache to invalidate.
	 *
	 * The dashboard's entitlement model (chatbotistic-dashboard/src/lib/
	 * plans.ts) has no distinct Lifetime tier, so 'lifetime' maps to
	 * 'agency' — the plan's own copy already promises "everything in
	 * Agency", so this is a correct mapping, not a downgrade.
	 *
	 * @param int $user_id WordPress user ID.
	 * @return string
	 */
	private static function dashboard_plan_for_user( int $user_id ): string {
		$membership = self::active_membership( $user_id );
		if ( ! $membership ) {
			return 'free';
		}

		$plan_id = (int) ( $membership['plan_id'] ?? 0 );
		if ( ! $plan_id || ! class_exists( '\WordPressistic\MLB\Caps' ) ) {
			return 'free';
		}

		$tier = (string) ( \WordPressistic\MLB\Caps::for_plan_id( $plan_id )['tier'] ?? 'free' );
		if ( 'lifetime' === $tier ) {
			return 'agency';
		}
		return in_array( $tier, array( 'free', 'starter', 'growth', 'agency' ), true ) ? $tier : 'free';
	}

	/**
	 * @param int $user_id WordPress user ID.
	 * @return string one of active|inactive|expired|suspended.
	 */
	private static function license_status_for_user( int $user_id ): string {
		$membership = self::active_membership( $user_id );
		if ( $membership ) {
			return 'active';
		}

		$repo = '\WordPressistic\Memberistic\Database\Memberships_Repository';
		$row  = ( class_exists( $repo ) && method_exists( $repo, 'get_by_user_id' ) ) ? $repo::get_by_user_id( $user_id ) : null;
		$status = is_array( $row ) ? (string) ( $row['status'] ?? '' ) : '';

		if ( in_array( $status, array( 'suspended', 'paused', 'past_due' ), true ) ) {
			return 'suspended';
		}
		if ( in_array( $status, array( 'cancelled', 'expired' ), true ) ) {
			return 'expired';
		}
		return 'inactive';
	}

	/**
	 * The user's membership row if — and only if — it's in a status that
	 * counts as paid access (active/comped/trial), else null. Mirrors the
	 * `active` resolution in chatbotistic-connector/includes/class-membership.php.
	 *
	 * @param int $user_id WordPress user ID.
	 * @return array|null
	 */
	private static function active_membership( int $user_id ): ?array {
		$repo = '\WordPressistic\Memberistic\Database\Memberships_Repository';
		if ( ! class_exists( $repo ) || ! method_exists( $repo, 'get_by_user_id' ) ) {
			return null;
		}
		$row = $repo::get_by_user_id( $user_id );
		if ( ! is_array( $row ) ) {
			return null;
		}
		$status = (string) ( $row['status'] ?? '' );
		return in_array( $status, array( 'active', 'comped', 'trial' ), true ) ? $row : null;
	}

	// ── Wire format ──────────────────────────────────────────────────────

	/**
	 * @param array  $claims Fully-populated claim set (iat/exp already set).
	 * @param string $secret Shared HMAC secret.
	 * @return string
	 */
	private static function sign( array $claims, string $secret ): string {
		$payload = self::b64url_encode( (string) wp_json_encode( $claims ) );
		$sig     = self::b64url_encode( hash_hmac( 'sha256', $payload, $secret, true ) );
		return $payload . '.' . $sig;
	}

	private static function b64url_encode( string $binary ): string {
		return rtrim( strtr( base64_encode( $binary ), '+/', '-_' ), '=' );
	}

	// ── Secret management ────────────────────────────────────────────────

	/**
	 * The shared HMAC secret. Precedence:
	 *   1. `CB_SSO_SHARED_SECRET` constant in wp-config.php — preferred
	 *      for production, must be set to the *identical* value as the
	 *      dashboard app's `SSO_SHARED_SECRET` environment variable.
	 *   2. An auto-generated secret, encrypted at rest in options —
	 *      created transparently on first use so SSO works immediately
	 *      after activation. Surfaced once via an admin notice so the
	 *      site owner can copy the same value into the dashboard's env
	 *      (recommended before going live, since a value stored only in
	 *      wp_options won't survive a fresh dashboard deploy unless
	 *      copied over).
	 *
	 * @return string
	 */
	public static function secret(): string {
		if ( defined( 'CB_SSO_SHARED_SECRET' ) && '' !== (string) CB_SSO_SHARED_SECRET ) {
			return (string) CB_SSO_SHARED_SECRET;
		}

		$stored = (string) get_option( self::OPTION_SECRET, '' );
		if ( '' !== $stored ) {
			$decrypted = self::decrypt( $stored );
			if ( '' !== $decrypted ) {
				return $decrypted;
			}
		}

		// First use on this site — generate and persist one so SSO works
		// out of the box.
		$generated = bin2hex( random_bytes( 32 ) );
		update_option( self::OPTION_SECRET, self::encrypt( $generated ), false );
		return $generated;
	}

	/** Whether the secret comes from wp-config.php rather than an auto-generated option. */
	public static function secret_is_constant(): bool {
		return defined( 'CB_SSO_SHARED_SECRET' ) && '' !== (string) CB_SSO_SHARED_SECRET;
	}

	/**
	 * One-time admin notice surfacing the auto-generated secret so it can
	 * be copied into the dashboard app's environment. Dismissible; never
	 * shown once a wp-config constant is in place.
	 *
	 * The secret value itself is NEVER embedded in the page HTML (it
	 * would leak via screen-shares, browser cache, HTML source, and
	 * support tickets with a screenshot) — the notice renders a
	 * nonce-gated "Reveal" button backed by handle_reveal_secret().
	 */
	public static function maybe_show_secret_notice(): void {
		if ( ! current_user_can( 'manage_options' ) || self::secret_is_constant() ) {
			return;
		}
		if ( '1' === (string) get_option( 'cbp_sso_secret_dismissed', '' ) ) {
			return;
		}

		$dismiss_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=cbp_dismiss_sso_notice' ),
			'cbp_dismiss_sso_notice'
		);
		$reveal_url = wp_nonce_url(
			admin_url( 'admin-ajax.php?action=cbp_reveal_sso_secret' ),
			'cbp_reveal_sso_secret'
		);

		echo '<div class="notice notice-info is-dismissible"><p><strong>' .
			esc_html__( 'Chatbotistic — Dashboard SSO', 'chatbotistic-profile' ) .
			'</strong> ' .
			esc_html__( 'is active with an auto-generated secret. Set the identical value as SSO_SHARED_SECRET in the dashboard app\'s environment (and, optionally, define CB_SSO_SHARED_SECRET in this site\'s wp-config.php so it survives a database restore):', 'chatbotistic-profile' ) .
			'</p><p><button type="button" class="button" id="cbp-reveal-sso-secret" data-url="' . esc_url( $reveal_url ) . '">' .
			esc_html__( 'Reveal secret', 'chatbotistic-profile' ) .
			'</button> <code id="cbp-sso-secret-value" style="user-select:all;padding:6px 10px;background:#f0f0f1;display:none;"></code></p>' .
			'<p><a href="' . esc_url( $dismiss_url ) . '">' .
			esc_html__( "I've copied it, dismiss this", 'chatbotistic-profile' ) .
			'</a></p></div>';
		echo '<script>document.addEventListener("click",function(e){var b=e.target&&e.target.closest?e.target.closest("#cbp-reveal-sso-secret"):null;if(!b)return;e.preventDefault();b.disabled=true;b.textContent="' . esc_js( __( 'Loading…', 'chatbotistic-profile' ) ) . '";fetch(b.dataset.url).then(function(r){return r.json()}).then(function(d){var c=document.getElementById("cbp-sso-secret-value");if(d&&d.secret){c.textContent=d.secret;c.style.display="inline-block";b.parentNode.removeChild(b);}else{b.textContent="' . esc_js( __( 'Failed — reload and retry', 'chatbotistic-profile' ) ) . '";b.disabled=false;}}).catch(function(){b.textContent="' . esc_js( __( 'Failed — reload and retry', 'chatbotistic-profile' ) ) . '";b.disabled=false;});});</script>';
	}

	/**
	 * AJAX: reveal the auto-generated SSO secret to a site admin.
	 * Nonce + manage_options gated; the value is only ever in the
	 * AJAX response, never rendered into page HTML.
	 */
	public static function handle_reveal_secret(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
		}
		check_ajax_referer( 'cbp_reveal_sso_secret' );
		wp_send_json_success( array( 'secret' => self::secret() ) );
	}

	public static function handle_dismiss_notice(): void {
		if ( current_user_can( 'manage_options' ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) ), 'cbp_dismiss_sso_notice' ) ) {
			update_option( 'cbp_sso_secret_dismissed', '1' );
		}
		wp_safe_redirect( wp_get_referer() ?: admin_url() );
		exit;
	}

	// ── Encryption at rest (matches chatbotistic-connector/includes/class-store.php) ──

	private static function encrypt( string $value ): string {
		if ( '' === $value ) {
			return '';
		}
		$iv = openssl_random_pseudo_bytes( 16 );
		$ct = openssl_encrypt( $value, 'AES-256-CBC', self::enc_key(), OPENSSL_RAW_DATA, $iv );
		return base64_encode( $iv . $ct );
	}

	private static function decrypt( string $value ): string {
		if ( '' === $value ) {
			return '';
		}
		$raw = base64_decode( $value, true );
		if ( false === $raw || strlen( $raw ) <= 16 ) {
			return '';
		}
		$iv = substr( $raw, 0, 16 );
		$ct = substr( $raw, 16 );
		$pt = openssl_decrypt( $ct, 'AES-256-CBC', self::enc_key(), OPENSSL_RAW_DATA, $iv );
		return is_string( $pt ) ? $pt : '';
	}

	private static function enc_key(): string {
		$seed = ( defined( 'AUTH_KEY' ) ? AUTH_KEY : 'cbp' ) . ( defined( 'SECURE_AUTH_KEY' ) ? SECURE_AUTH_KEY : 'sso-bridge' );
		return substr( hash( 'sha256', $seed ), 0, 32 );
	}
}
