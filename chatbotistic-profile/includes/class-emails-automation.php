<?php
/**
 * Email Automation for the Chatbotistic stack.
 *
 * Layered on top of:
 *   - WordPress user_register (new account)
 *   - Memberistic transactional templates (membership lifecycle)
 *   - Licenseistic action hooks (license created / activated / deactivated)
 *   - WPistic Contact Form WPISTIC_CF_submission_captured (form replies)
 *
 * What this class adds beyond the bare ecosystem:
 *
 *   1. Email verification with a tokenised link + 6-digit OTP code.
 *      Generated on user_register, sent in a verification email, and
 *      consumed by the /verify-email/ page template. User can't access
 *      the member portal until verified.
 *
 *   2. A rich "Welcome to Chatbotistic" email after verification that
 *      bundles login URL, member portal URL, plugin download link,
 *      user guide URL, license key (when issued), and support contact
 *      into a single onboarding message.
 *
 *   3. License activation confirmation email — fires the moment a
 *      customer activates the license on their WordPress site, with
 *      the domain that was registered + plan caps + a deactivate-on-
 *      this-site quick link.
 *
 *   4. Per-form WPCF auto-responder copy. Where the WPCF preset writes
 *      ONE template for all forms, this class registers a filter that
 *      returns form-specific copy for the 5 named Chatbotistic forms
 *      (contact / demo / qualified demo / support / portal ticket).
 *
 * Every send respects:
 *   - WPCF_EMAIL_DISABLED constant (staging kill switch)
 *   - WPISTIC_CF_emails_disabled option (admin kill switch)
 *   - chatbotistic-profile's wp_mail_from / wp_mail_from_name brand
 *     overrides via Emails::with_branded_from()
 *
 * @package Chatbotistic\Profile
 */

namespace Chatbotistic\Profile;

defined( 'ABSPATH' ) || exit;

class Emails_Automation {

	/** Token + OTP storage on user meta. */
	const META_TOKEN     = 'cbp_verify_token';
	const META_OTP       = 'cbp_verify_otp';
	const META_EXPIRES   = 'cbp_verify_expires';
	const META_VERIFIED  = 'cbp_email_verified';
	const META_WELCOMED  = 'cbp_welcome_sent';

	/** Token + OTP lifetime. */
	const TOKEN_TTL  = 48 * HOUR_IN_SECONDS;

	public function register(): void {
		add_action( 'user_register',                       array( $this, 'on_user_register' ),           20, 1 );
		add_action( 'cbp_send_welcome',                    array( $this, 'send_welcome_email' ),         10, 1 );
		add_action( 'memberistic_membership_activated',    array( $this, 'on_membership_activated' ),    20, 1 );
		add_action( 'wpistic_lsi_license_activated',       array( $this, 'on_license_activated' ),       20, 2 );
		add_action( 'wpistic_lsi_license_deactivated',     array( $this, 'on_license_deactivated' ),     20, 2 );
		add_filter( 'WPISTIC_CF_ar_for_form',              array( $this, 'per_form_ar_template' ),       10, 3 );
	}

	// ── 1. Account creation: verification email ─────────────────────────

	public function on_user_register( int $user_id ): void {
		$user = get_user_by( 'id', $user_id );
		if ( ! $user || ! is_email( $user->user_email ) ) {
			return;
		}

		$token = wp_generate_password( 32, false, false );
		$otp   = str_pad( (string) wp_rand( 0, 999999 ), 6, '0', STR_PAD_LEFT );
		update_user_meta( $user_id, self::META_TOKEN,    $token );
		update_user_meta( $user_id, self::META_OTP,      $otp );
		update_user_meta( $user_id, self::META_EXPIRES,  time() + self::TOKEN_TTL );
		update_user_meta( $user_id, self::META_VERIFIED, '' );

		$brand   = self::brand_label();
		$verify  = add_query_arg(
			array( 'uid' => $user_id, 'token' => $token ),
			home_url( '/verify-email/' )
		);

		$subject = sprintf(
			/* translators: %s: brand label */
			__( 'Verify your %s email', 'chatbotistic-profile' ),
			$brand
		);
		$body  = sprintf( __( "Hi %s,\n\n", 'chatbotistic-profile' ), $user->display_name ?: $user->user_login );
		$body .= sprintf( __( "Welcome to %s. Verify your email to activate your account and access the member portal.\n\n", 'chatbotistic-profile' ), $brand );
		$body .= __( "Click the secure link below:\n\n", 'chatbotistic-profile' );
		$body .= $verify . "\n\n";
		$body .= __( "Or paste this 6-digit code on the /verify-email/ page:\n\n", 'chatbotistic-profile' );
		$body .= "   " . $otp . "\n\n";
		$body .= __( "The link and code expire in 48 hours.\n\n", 'chatbotistic-profile' );
		$body .= sprintf( __( "If you didn't sign up, ignore this email.\n\n— The %s team", 'chatbotistic-profile' ), $brand );

		self::send( $user->user_email, $subject, $body );
	}

	/**
	 * Mark a user verified and queue the welcome email. Call from the
	 * /verify-email/ page handler when a valid token or OTP matches.
	 *
	 * @return bool true on first-time verify, false if already verified or bad input.
	 */
	public static function verify( int $user_id, string $token_or_otp ): bool {
		if ( ! $user_id ) { return false; }
		if ( '1' === get_user_meta( $user_id, self::META_VERIFIED, true ) ) {
			return false;
		}
		$expires = (int) get_user_meta( $user_id, self::META_EXPIRES, true );
		if ( $expires && $expires < time() ) {
			return false;
		}
		$token = (string) get_user_meta( $user_id, self::META_TOKEN, true );
		$otp   = (string) get_user_meta( $user_id, self::META_OTP, true );
		if ( $token_or_otp !== $token && $token_or_otp !== $otp ) {
			return false;
		}

		update_user_meta( $user_id, self::META_VERIFIED, '1' );
		delete_user_meta( $user_id, self::META_TOKEN );
		delete_user_meta( $user_id, self::META_OTP );
		delete_user_meta( $user_id, self::META_EXPIRES );

		// Fire the welcome on the next request so the page can render
		// fast and we don't block on SMTP.
		wp_schedule_single_event( time() + 5, 'cbp_send_welcome', array( $user_id ) );
		return true;
	}

	// ── 2. Welcome email after verification ─────────────────────────────

	public function send_welcome_email( int $user_id ): void {
		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) { return; }
		if ( '1' === get_user_meta( $user_id, self::META_WELCOMED, true ) ) {
			return;
		}

		$brand        = self::brand_label();
		$login_url    = home_url( '/login/' );
		$account_url  = home_url( '/account/' );
		$pricing_url  = home_url( '/pricing/' );
		$docs_url     = home_url( '/docs/' );
		$support_url  = home_url( '/support/' );
		$plugin_url   = home_url( '/account/?view=install' );
		$license_key  = (string) get_user_meta( $user_id, 'mlb_license_key', true );

		$subject = sprintf( __( '🎉 Welcome to %s — your account is ready', 'chatbotistic-profile' ), $brand );
		$body    = sprintf( __( "Hi %s,\n\n", 'chatbotistic-profile' ), $user->display_name ?: $user->user_login );
		$body   .= sprintf( __( "Your %s account is verified and ready. Here's everything you need to get started.\n\n", 'chatbotistic-profile' ), $brand );
		$body   .= "ACCOUNT LOGIN\n";
		$body   .= "  Username:  " . $user->user_login . "\n";
		$body   .= "  Email:     " . $user->user_email . "\n";
		$body   .= "  Login URL: " . $login_url . "\n\n";
		$body   .= "MEMBER PORTAL\n";
		$body   .= "  " . $account_url . "\n\n";
		if ( $license_key ) {
			$body .= "YOUR LICENSE KEY\n";
			$body .= "  " . $license_key . "\n\n";
			$body .= "INSTALL THE WORDPRESS ADDON\n";
			$body .= "  " . $plugin_url . "\n\n";
		} else {
			$body .= "PICK A PLAN\n";
			$body .= "  " . $pricing_url . "\n\n";
		}
		$body   .= "USER GUIDE & DOCUMENTATION\n";
		$body   .= "  " . $docs_url . "\n\n";
		$body   .= "NEED HELP?\n";
		$body   .= "  " . $support_url . "\n\n";
		$body   .= __( "Reply to this email any time — a real person reads every reply.\n\n", 'chatbotistic-profile' );
		$body   .= sprintf( __( "— The %s team", 'chatbotistic-profile' ), $brand );

		self::send( $user->user_email, $subject, $body );
		update_user_meta( $user_id, self::META_WELCOMED, '1' );
	}

	// ── 3. Membership lifecycle (richer onboarding on activation) ───────

	public function on_membership_activated( int $membership_id ): void {
		// If user has been verified but never welcomed, push the welcome
		// (in case verification preceded the plan purchase).
		$repo = '\WordPressistic\Memberistic\Database\Memberships_Repository';
		if ( ! class_exists( $repo ) ) { return; }
		$m = $repo::get( $membership_id );
		$user_id = is_array( $m ) ? (int) ( $m['primary_user_id'] ?? 0 ) : 0;
		if ( ! $user_id ) { return; }

		if ( '1' === get_user_meta( $user_id, self::META_VERIFIED, true )
			&& '1' !== get_user_meta( $user_id, self::META_WELCOMED, true ) ) {
			$this->send_welcome_email( $user_id );
		}
	}

	// ── 4. License activation confirmation ──────────────────────────────

	public function on_license_activated( int $license_id, int $activation_id ): void {
		if ( ! class_exists( '\WPistic_LSI_License_Service' ) ) { return; }
		$license = \WPistic_LSI_License_Service::get_license( $license_id );
		if ( ! is_array( $license ) ) { return; }
		$user_id = (int) ( $license['customer_id'] ?? 0 );
		$user    = $user_id ? get_user_by( 'id', $user_id ) : null;
		$email   = $user ? $user->user_email : (string) ( $license['customer_email'] ?? '' );
		if ( ! is_email( $email ) ) { return; }

		// Pull the activation row for the domain detail.
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT site_url, instance_id, activated_at FROM {$wpdb->prefix}wpistic_lsi_activations WHERE activation_id = %d", $activation_id ) );
		$site_url = $row ? (string) $row->site_url : '';

		$caps = array();
		if ( ! empty( $license['notes'] ) ) {
			$decoded = json_decode( (string) $license['notes'], true );
			if ( is_array( $decoded ) && isset( $decoded['_mlb']['caps'] ) ) {
				$caps = (array) $decoded['_mlb']['caps'];
			}
		}

		$brand        = self::brand_label();
		$account_url  = home_url( '/account/' );
		$license_pg   = home_url( '/account/?view=license' );

		$subject = sprintf( __( '✓ License activated on %s', 'chatbotistic-profile' ), wp_parse_url( $site_url, PHP_URL_HOST ) ?: $site_url );
		$body    = sprintf( __( "Hi %s,\n\n", 'chatbotistic-profile' ), $user ? ( $user->display_name ?: $user->user_login ) : __( 'there', 'chatbotistic-profile' ) );
		$body   .= sprintf( __( "Your %s license was just activated on a new site.\n\n", 'chatbotistic-profile' ), $brand );
		$body   .= "SITE\n  " . ( $site_url ?: __( 'Unknown', 'chatbotistic-profile' ) ) . "\n\n";
		if ( $caps ) {
			$body .= "PLAN LIMITS NOW LIVE\n";
			$body .= "  " . sprintf( __( '%s widgets · %s agents · %s domains', 'chatbotistic-profile' ),
				self::cap_label( $caps['max_widgets'] ?? 1 ),
				self::cap_label( $caps['max_agents']  ?? 1 ),
				self::cap_label( $caps['max_domains'] ?? 1 )
			) . "\n\n";
		}
		$body   .= __( "If you didn't activate this site, deactivate it immediately:\n  ", 'chatbotistic-profile' ) . $license_pg . "\n\n";
		$body   .= __( "Manage your account:\n  ", 'chatbotistic-profile' ) . $account_url . "\n\n";
		$body   .= sprintf( __( "— The %s team", 'chatbotistic-profile' ), $brand );

		self::send( $email, $subject, $body );
	}

	public function on_license_deactivated( int $license_id, int $activation_id ): void {
		if ( ! class_exists( '\WPistic_LSI_License_Service' ) ) { return; }
		$license = \WPistic_LSI_License_Service::get_license( $license_id );
		if ( ! is_array( $license ) ) { return; }
		$email = (string) ( $license['customer_email'] ?? '' );
		if ( ! is_email( $email ) ) { return; }

		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT site_url FROM {$wpdb->prefix}wpistic_lsi_activations WHERE activation_id = %d", $activation_id ) );
		$site = $row ? (string) $row->site_url : '';

		$brand   = self::brand_label();
		$subject = sprintf( __( 'License deactivated on %s', 'chatbotistic-profile' ), wp_parse_url( $site, PHP_URL_HOST ) ?: $site );
		$body    = sprintf( __( "Your %s license was deactivated on:\n\n  %s\n\nThis frees up a domain slot. Activate the license on another site any time from /account/?view=license.\n\n— The %s team", 'chatbotistic-profile' ), $brand, $site ?: __( 'Unknown', 'chatbotistic-profile' ), $brand );

		self::send( $email, $subject, $body );
	}

	// ── 5. Per-form WPCF auto-responder copy ───────────────────────────

	/**
	 * Filter callback. WPCF auto-responder consults this BEFORE falling
	 * back to the global WPISTIC_CF_ar_subject / _body. Returns
	 * [ subject, body ] for a known form name, or null to defer to the
	 * default.
	 *
	 * Requires the small WPCF patch in class-wpcf-autoresponder.php
	 * that calls apply_filters( 'WPISTIC_CF_ar_for_form', null, ... ).
	 */
	public function per_form_ar_template( $default, $form_name, $fields ) {
		if ( is_array( $default ) ) { return $default; }

		$brand = self::brand_label();
		$site  = home_url( '/' );

		$templates = array(
			'Chatbotistic Contact Form' => array(
				'subject' => sprintf( __( 'Thanks for contacting %s', 'chatbotistic-profile' ), $brand ),
				'body'    => "Hi {name},\n\nThanks for reaching out to {site_name}. Your message landed safely and a real person will reply within one business day — sooner during business hours.\n\nYour message:\n----\n{message}\n----\n\nIf you need urgent help, just reply to this email and we'll prioritise it.\n\n— The " . $brand . " team\n" . $site,
			),
			'Chatbotistic Demo Request (qualified)' => array(
				'subject' => sprintf( __( "We're reviewing your %s demo request", 'chatbotistic-profile' ), $brand ),
				'body'    => "Hi {name},\n\nThanks for the detailed demo request. Here's what happens next:\n\n  1. We review your business details (≈ 1 business day)\n  2. If we're a fit, we build a 14-day demo environment using the paid-plan features most relevant to your use case\n  3. You receive login details + a guided walkthrough\n\nWe'll be in touch by email at {site_name} pace — no surprises.\n\nWant to start using the platform now instead? The Free plan is one-click at " . $site . "register/.\n\n— The " . $brand . " team\n" . $site,
			),
			'Chatbotistic Demo Request' => array(
				'subject' => sprintf( __( "We received your %s demo request", 'chatbotistic-profile' ), $brand ),
				'body'    => "Hi {name},\n\nThanks for asking for a demo. We'll reply within one business day with a tailored walkthrough.\n\n— The " . $brand . " team\n" . $site,
			),
			'Chatbotistic Support Request' => array(
				'subject' => sprintf( __( '[%s Support] We got your message', 'chatbotistic-profile' ), $brand ),
				'body'    => "Hi {name},\n\nYour support ticket is in the queue. We reply in under 2 hours during business days.\n\nYour message:\n----\n{message}\n----\n\nReply to this email to add more detail or attach screenshots.\n\n— The " . $brand . " support team\n" . $site,
			),
			'Member Portal Support Ticket' => array(
				'subject' => sprintf( __( '[%s Support] Ticket received', 'chatbotistic-profile' ), $brand ),
				'body'    => "Hi {name},\n\nYour support ticket is in the priority queue. A real person will reply within 2 hours during business days.\n\nYour message:\n----\n{message}\n----\n\n— The " . $brand . " support team\n" . $site,
			),
		);

		return $templates[ $form_name ] ?? null;
	}

	// ── helpers ─────────────────────────────────────────────────────────

	private static function send( string $to, string $subject, string $body ): void {
		if ( defined( 'WPCF_EMAIL_DISABLED' ) && WPCF_EMAIL_DISABLED ) { return; }
		if ( '1' === (string) get_option( 'WPISTIC_CF_emails_disabled', '0' ) ) { return; }

		// Brand the From header for this send only.
		if ( class_exists( __NAMESPACE__ . '\\Emails' ) && method_exists( __NAMESPACE__ . '\\Emails', 'arm_from' ) ) {
			Emails::arm_from();
		} else {
			$from_name  = defined( 'CBP_FROM_NAME' )  ? CBP_FROM_NAME  : self::brand_label();
			$from_email = defined( 'CBP_FROM_EMAIL' ) ? CBP_FROM_EMAIL : (string) get_option( 'admin_email' );
			add_filter( 'wp_mail_from',      static fn () => $from_email, 9999 );
			add_filter( 'wp_mail_from_name', static fn () => $from_name,  9999 );
		}
		wp_mail( $to, $subject, $body );
	}

	private static function brand_label(): string {
		$settings = (array) get_option( 'memberistic_settings', array() );
		return (string) ( $settings['brand_label'] ?? 'Chatbotistic' );
	}

	private static function cap_label( $value ): string {
		$value = (int) $value;
		return -1 === $value ? __( 'unlimited', 'chatbotistic-profile' ) : (string) $value;
	}
}
