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
 *   4. Purchase / upgrade receipt — fires on `memberistic_membership_activated`
 *      alongside the existing welcome-email logic. Receipt-style summary
 *      (plan name always; amount/renewal date only when the membership
 *      row / latest payment actually has them) plus a "what's unlocked"
 *      list resolved from the Memberistic → Licenseistic Bridge caps.
 *
 *   5. Per-form WPCF auto-responder copy. Where the WPCF preset writes
 *      ONE template for all forms, this class registers a filter that
 *      returns form-specific copy for the 5 named Chatbotistic forms
 *      (contact / demo / qualified demo / support / portal ticket), and
 *      arms HTML rendering for every autoresponder send (named or the
 *      global default configured in WPCF_Preset).
 *
 *   6. Newsletter welcome — fires on `wpcf_newsletter_subscribed`, the
 *      action already emitted by WPistic Contact Form's newsletter
 *      module when a new subscriber is stored.
 *
 * Every send respects:
 *   - WPCF_EMAIL_DISABLED constant (staging kill switch)
 *   - WPISTIC_CF_emails_disabled option (admin kill switch)
 *   - Renders through Email_Template::render() + Email_Template::mail_headers()
 *     so every customer-facing email shares the same branded HTML shell.
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
		add_action( 'cbp_send_welcome',                     array( $this, 'send_welcome_email' ),         10, 1 );
		add_action( 'memberistic_membership_activated',     array( $this, 'on_membership_activated' ),    20, 1 );
		add_action( 'wpistic_lsi_license_activated',        array( $this, 'on_license_activated' ),       20, 2 );
		add_action( 'wpistic_lsi_license_deactivated',      array( $this, 'on_license_deactivated' ),     20, 2 );
		add_filter( 'WPISTIC_CF_ar_for_form',               array( $this, 'per_form_ar_template' ),       10, 3 );

		// WPistic Contact Form's newsletter module already fires this action
		// (WPISTIC_CF_Newsletter::process(), class-wpcf-newsletter.php) the
		// moment a new subscriber row is inserted — no changes needed there.
		add_action( 'wpcf_newsletter_subscribed',           array( $this, 'on_newsletter_subscribed' ),    10, 3 );
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

		$brand      = self::brand_label();
		$display    = $user->display_name ?: $user->user_login;
		$verify_url = add_query_arg(
			array( 'uid' => $user_id, 'token' => $token ),
			home_url( '/verify-email/' )
		);

		$subject = sprintf(
			/* translators: %s: brand label */
			__( 'Verify your %s email', 'chatbotistic-profile' ),
			$brand
		);

		$body  = '<p style="margin:0 0 16px 0;">' . sprintf( esc_html__( 'Hi %s,', 'chatbotistic-profile' ), esc_html( $display ) ) . '</p>';
		$body .= '<p style="margin:0 0 16px 0;">' . sprintf( esc_html__( 'Welcome to %s. Verify your email to activate your account and access the member portal.', 'chatbotistic-profile' ), esc_html( $brand ) ) . '</p>';
		$body .= '<p style="margin:0 0 4px 0;">' . esc_html__( 'Or enter this 6-digit code on the verification page:', 'chatbotistic-profile' ) . '</p>';
		$body .= '<p style="margin:0 0 20px 0;font-family:Consolas,Menlo,monospace;font-size:22px;font-weight:bold;letter-spacing:4px;color:#111827;">' . esc_html( $otp ) . '</p>';
		$body .= '<p style="margin:0;font-size:13px;color:#6b7280;">' . esc_html__( 'This link and code expire in 48 hours. If you did not sign up, you can ignore this email.', 'chatbotistic-profile' ) . '</p>';

		$html = Email_Template::render( array(
			'title'     => sprintf( __( 'Verify your %s email', 'chatbotistic-profile' ), $brand ),
			'preheader' => __( 'One click (or a 6-digit code) and your account is ready.', 'chatbotistic-profile' ),
			'body_html' => $body,
			'cta_label' => __( 'Verify My Email', 'chatbotistic-profile' ),
			'cta_url'   => $verify_url,
		) );

		self::send( $user->user_email, $subject, $html );
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

		$brand         = self::brand_label();
		$display       = $user->display_name ?: $user->user_login;
		$login_url     = home_url( '/login/' );
		$pricing_url   = home_url( '/pricing/' );
		$support_url   = home_url( '/support/' );
		$plugin_url    = home_url( '/account/?view=install' );
		$license_key   = (string) get_user_meta( $user_id, 'mlb_license_key', true );
		$dashboard_url = function_exists( 'cb_dashboard_url' ) ? cb_dashboard_url() : 'https://app.chatbotistic.com';
		$docs_url      = function_exists( 'cb_dashboard_url' ) ? cb_dashboard_url( 'docs' ) : 'https://app.chatbotistic.com/docs';

		$body  = '<p style="margin:0 0 16px 0;">' . sprintf( esc_html__( 'Hi %s, welcome aboard!', 'chatbotistic-profile' ), esc_html( $display ) ) . '</p>';
		$body .= '<p style="margin:0 0 20px 0;">' . sprintf( esc_html__( '%s turns your WordPress site into a WhatsApp-powered lead machine — build AI chat widgets, route conversations to your WhatsApp agents, and capture every lead automatically, all without leaving WordPress.', 'chatbotistic-profile' ), esc_html( $brand ) ) . '</p>';

		$body .= '<p style="margin:0 0 8px 0;font-weight:bold;">' . esc_html__( 'Getting started', 'chatbotistic-profile' ) . '</p>';
		$body .= '<ol style="margin:0 0 20px 0;padding-left:20px;">';
		$body .= '<li style="margin-bottom:8px;">' . esc_html__( 'Create your first widget', 'chatbotistic-profile' ) . '</li>';
		$body .= '<li style="margin-bottom:8px;">' . esc_html__( 'Add a WhatsApp agent', 'chatbotistic-profile' ) . '</li>';
		$body .= '<li style="margin-bottom:8px;">' . esc_html__( 'Embed the widget on your site', 'chatbotistic-profile' ) . '</li>';
		$body .= '<li style="margin-bottom:0;">' . esc_html__( 'Watch leads arrive', 'chatbotistic-profile' ) . '</li>';
		$body .= '</ol>';

		$body .= '<p style="margin:0 0 4px 0;"><strong>' . esc_html__( 'Username:', 'chatbotistic-profile' ) . '</strong> ' . esc_html( $user->user_login ) . '</p>';
		$body .= '<p style="margin:0 0 20px 0;"><strong>' . esc_html__( 'Login:', 'chatbotistic-profile' ) . '</strong> <a href="' . esc_url( $login_url ) . '" style="color:' . Email_Template::BRAND_GREEN . ';">' . esc_html( $login_url ) . '</a></p>';

		if ( $license_key ) {
			$body .= '<p style="margin:0 0 8px 0;"><strong>' . esc_html__( 'Your license key:', 'chatbotistic-profile' ) . '</strong> ' . esc_html( $license_key ) . '</p>';
			$body .= '<p style="margin:0 0 20px 0;">' . sprintf( wp_kses_post( __( 'Install the WordPress addon: <a href="%s" style="color:' . Email_Template::BRAND_GREEN . ';">%s</a>', 'chatbotistic-profile' ) ), esc_url( $plugin_url ), esc_html( $plugin_url ) ) . '</p>';
		} else {
			$body .= '<p style="margin:0 0 20px 0;">' . sprintf( wp_kses_post( __( "Haven't picked a plan yet? <a href=\"%s\" style=\"color:" . Email_Template::BRAND_GREEN . ';">Free Forever is one click away</a>.', 'chatbotistic-profile' ) ), esc_url( $pricing_url ) ) . '</p>';
		}

		$body .= '<p style="margin:0 0 8px 0;">' . sprintf( wp_kses_post( __( 'Full documentation: <a href="%s" style="color:' . Email_Template::BRAND_GREEN . ';">%s</a>', 'chatbotistic-profile' ) ), esc_url( $docs_url ), esc_html( $docs_url ) ) . '</p>';
		$body .= '<p style="margin:0;">' . sprintf( wp_kses_post( __( 'Need a person? <a href="%s" style="color:' . Email_Template::BRAND_GREEN . ';">Contact support</a>.', 'chatbotistic-profile' ) ), esc_url( $support_url ) ) . '</p>';

		$html = Email_Template::render( array(
			'title'       => sprintf( __( '🎉 Welcome to %s', 'chatbotistic-profile' ), $brand ),
			'preheader'   => __( 'Your account is verified and ready — here is how to get started.', 'chatbotistic-profile' ),
			'body_html'   => $body,
			'cta_label'   => __( 'Open Your Dashboard', 'chatbotistic-profile' ),
			'cta_url'     => $dashboard_url,
			'footer_note' => __( 'Reply to this email any time — a real person reads every reply.', 'chatbotistic-profile' ),
		) );

		self::send( $user->user_email, sprintf( __( '🎉 Welcome to %s — your account is ready', 'chatbotistic-profile' ), $brand ), $html );
		update_user_meta( $user_id, self::META_WELCOMED, '1' );
	}

	// ── 3. Membership lifecycle (welcome catch-up + purchase receipt) ───

	public function on_membership_activated( int $membership_id ): void {
		// If user has been verified but never welcomed, push the welcome
		// (in case verification preceded the plan purchase).
		$repo = '\WordPressistic\Memberistic\Database\Memberships_Repository';
		if ( class_exists( $repo ) ) {
			$m       = $repo::get( $membership_id );
			$user_id = is_array( $m ) ? (int) ( $m['primary_user_id'] ?? 0 ) : 0;
			if ( $user_id
				&& '1' === get_user_meta( $user_id, self::META_VERIFIED, true )
				&& '1' !== get_user_meta( $user_id, self::META_WELCOMED, true ) ) {
				$this->send_welcome_email( $user_id );
			}
		}

		$this->send_purchase_receipt( $membership_id );
	}

	/**
	 * Branded receipt-style email for a plan purchase/upgrade. Uses only
	 * whatever fields the membership row / latest payment actually
	 * provide — never invents amount or renewal-date data.
	 */
	private function send_purchase_receipt( int $membership_id ): void {
		$repo = '\WordPressistic\Memberistic\Database\Memberships_Repository';
		if ( ! class_exists( $repo ) ) { return; }

		$m = $repo::get_with_summary( $membership_id );
		if ( ! is_array( $m ) || empty( $m['email'] ) || ! is_email( $m['email'] ) ) {
			return;
		}

		$brand         = self::brand_label();
		$plan_name     = (string) ( $m['plan_name'] ?? '' );
		$member_name   = (string) ( $m['full_name'] ?? '' );
		$member_name   = '' !== $member_name ? $member_name : __( 'there', 'chatbotistic-profile' );
		$billing_url   = home_url( '/account/?view=billing' );
		$dashboard_url = function_exists( 'cb_dashboard_url' ) ? cb_dashboard_url() : 'https://app.chatbotistic.com';

		$latest_payment = null;
		$payments_repo  = '\WordPressistic\Memberistic\Database\Payments_Repository';
		if ( class_exists( $payments_repo ) && method_exists( $payments_repo, 'get_by_membership' ) ) {
			$payments       = (array) $payments_repo::get_by_membership( $membership_id );
			$latest_payment = $payments ? $payments[0] : null;
		}

		$body  = '<p style="margin:0 0 16px 0;">' . sprintf( esc_html__( 'Hi %s,', 'chatbotistic-profile' ), esc_html( $member_name ) ) . '</p>';
		$body .= '<p style="margin:0 0 20px 0;">' . sprintf(
			wp_kses_post( __( 'Your <strong>%1$s</strong> plan is now active on %2$s. Here is your receipt summary:', 'chatbotistic-profile' ) ),
			esc_html( $plan_name ),
			esc_html( $brand )
		) . '</p>';

		$body .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px 0;border:1px solid #e5e7eb;border-radius:6px;">';
		$body .= '<tr><td style="padding:12px 16px;border-bottom:1px solid #e5e7eb;font-size:14px;color:#6b7280;">' . esc_html__( 'Plan', 'chatbotistic-profile' ) . '</td><td style="padding:12px 16px;border-bottom:1px solid #e5e7eb;text-align:right;font-size:14px;font-weight:bold;color:#111827;">' . esc_html( $plan_name ) . '</td></tr>';

		if ( is_array( $latest_payment ) && isset( $latest_payment['amount'] ) && '' !== (string) $latest_payment['amount'] ) {
			$amount = number_format_i18n( (float) $latest_payment['amount'], 2 );
			$body  .= '<tr><td style="padding:12px 16px;border-bottom:1px solid #e5e7eb;font-size:14px;color:#6b7280;">' . esc_html__( 'Amount', 'chatbotistic-profile' ) . '</td><td style="padding:12px 16px;border-bottom:1px solid #e5e7eb;text-align:right;font-size:14px;font-weight:bold;color:#111827;">$' . esc_html( $amount ) . '</td></tr>';
		}
		if ( ! empty( $m['renewal_date'] ) ) {
			$renewal = date_i18n( get_option( 'date_format' ), strtotime( (string) $m['renewal_date'] ) );
			$body   .= '<tr><td style="padding:12px 16px;font-size:14px;color:#6b7280;">' . esc_html__( 'Next renewal', 'chatbotistic-profile' ) . '</td><td style="padding:12px 16px;text-align:right;font-size:14px;font-weight:bold;color:#111827;">' . esc_html( $renewal ) . '</td></tr>';
		}
		$body .= '</table>';

		$unlocked   = array();
		$caps_class = '\WordPressistic\MLB\Caps';
		if ( class_exists( $caps_class ) && method_exists( $caps_class, 'for_plan_id' ) && ! empty( $m['plan_id'] ) ) {
			$caps = $caps_class::for_plan_id( (int) $m['plan_id'] );
			if ( is_array( $caps ) ) {
				$unlocked[] = sprintf( __( '%s AI ChatBot widgets', 'chatbotistic-profile' ), self::cap_label( $caps['max_widgets'] ?? 1 ) );
				$unlocked[] = sprintf( __( '%s WhatsApp agents', 'chatbotistic-profile' ), self::cap_label( $caps['max_agents'] ?? 1 ) );
				$unlocked[] = sprintf( __( '%s website domains', 'chatbotistic-profile' ), self::cap_label( $caps['max_domains'] ?? 1 ) );
				if ( ! empty( $caps['white_label'] ) ) {
					$unlocked[] = __( 'Full white-label branding', 'chatbotistic-profile' );
				}
			}
		}

		$body .= '<p style="margin:0 0 8px 0;font-weight:bold;">' . esc_html__( "What's unlocked", 'chatbotistic-profile' ) . '</p>';
		if ( $unlocked ) {
			$body .= '<ul style="margin:0 0 20px 0;padding-left:20px;">';
			foreach ( $unlocked as $line ) {
				$body .= '<li style="margin-bottom:6px;">' . esc_html( $line ) . '</li>';
			}
			$body .= '</ul>';
		} else {
			$body .= '<p style="margin:0 0 20px 0;">' . esc_html__( 'Your new limits are now active.', 'chatbotistic-profile' ) . '</p>';
		}

		$body .= '<p style="margin:0;">' . sprintf(
			wp_kses_post( __( 'Manage billing any time: <a href="%1$s" style="color:' . Email_Template::BRAND_GREEN . '">%1$s</a>', 'chatbotistic-profile' ) ),
			esc_url( $billing_url )
		) . '</p>';

		$html = Email_Template::render( array(
			'title'     => sprintf( __( 'Your %s plan is active', 'chatbotistic-profile' ), $plan_name ?: $brand ),
			'preheader' => sprintf( __( "Receipt + what you've unlocked on the %s plan.", 'chatbotistic-profile' ), $plan_name ),
			'body_html' => $body,
			'cta_label' => __( 'Open Your Dashboard', 'chatbotistic-profile' ),
			'cta_url'   => $dashboard_url,
		) );

		self::send(
			$m['email'],
			sprintf( __( 'Receipt: your %s plan is active', 'chatbotistic-profile' ), $plan_name ?: $brand ),
			$html
		);
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

		$brand      = self::brand_label();
		$member     = $user ? ( $user->display_name ?: $user->user_login ) : __( 'there', 'chatbotistic-profile' );
		$account_url = home_url( '/account/' );
		$license_pg  = home_url( '/account/?view=license' );

		$body  = '<p style="margin:0 0 16px 0;">' . sprintf( esc_html__( 'Hi %s,', 'chatbotistic-profile' ), esc_html( $member ) ) . '</p>';
		$body .= '<p style="margin:0 0 16px 0;">' . sprintf( esc_html__( 'Your %s license was just activated on a new site.', 'chatbotistic-profile' ), esc_html( $brand ) ) . '</p>';
		$body .= '<p style="margin:0 0 4px 0;"><strong>' . esc_html__( 'Site', 'chatbotistic-profile' ) . '</strong></p>';
		$body .= '<p style="margin:0 0 20px 0;">' . esc_html( $site_url ?: __( 'Unknown', 'chatbotistic-profile' ) ) . '</p>';

		if ( $caps ) {
			$body .= '<p style="margin:0 0 8px 0;font-weight:bold;">' . esc_html__( 'Plan limits now live', 'chatbotistic-profile' ) . '</p>';
			$body .= '<p style="margin:0 0 20px 0;">' . esc_html( sprintf(
				__( '%1$s widgets · %2$s agents · %3$s domains', 'chatbotistic-profile' ),
				self::cap_label( $caps['max_widgets'] ?? 1 ),
				self::cap_label( $caps['max_agents']  ?? 1 ),
				self::cap_label( $caps['max_domains'] ?? 1 )
			) ) . '</p>';
		}

		$body .= '<p style="margin:0 0 16px 0;">' . sprintf(
			wp_kses_post( __( "Didn't activate this site? <a href=\"%s\" style=\"color:" . Email_Template::BRAND_GREEN . ';">Deactivate it now</a>.', 'chatbotistic-profile' ) ),
			esc_url( $license_pg )
		) . '</p>';

		$html = Email_Template::render( array(
			'title'     => __( 'License activated', 'chatbotistic-profile' ),
			'preheader' => sprintf( __( 'Your %s license is now live on a new site.', 'chatbotistic-profile' ), $brand ),
			'body_html' => $body,
			'cta_label' => __( 'Manage Your Account', 'chatbotistic-profile' ),
			'cta_url'   => $account_url,
		) );

		self::send( $email, sprintf( __( '✓ License activated on %s', 'chatbotistic-profile' ), wp_parse_url( $site_url, PHP_URL_HOST ) ?: $site_url ), $html );
	}

	public function on_license_deactivated( int $license_id, int $activation_id ): void {
		if ( ! class_exists( '\WPistic_LSI_License_Service' ) ) { return; }
		$license = \WPistic_LSI_License_Service::get_license( $license_id );
		if ( ! is_array( $license ) ) { return; }
		$email = (string) ( $license['customer_email'] ?? '' );
		if ( ! is_email( $email ) ) { return; }

		global $wpdb;
		$row  = $wpdb->get_row( $wpdb->prepare( "SELECT site_url FROM {$wpdb->prefix}wpistic_lsi_activations WHERE activation_id = %d", $activation_id ) );
		$site = $row ? (string) $row->site_url : '';

		$brand       = self::brand_label();
		$license_pg  = home_url( '/account/?view=license' );

		$body  = '<p style="margin:0 0 16px 0;">' . sprintf( esc_html__( 'Your %s license was deactivated on:', 'chatbotistic-profile' ), esc_html( $brand ) ) . '</p>';
		$body .= '<p style="margin:0 0 20px 0;">' . esc_html( $site ?: __( 'Unknown', 'chatbotistic-profile' ) ) . '</p>';
		$body .= '<p style="margin:0;">' . esc_html__( 'This frees up a domain slot. You can activate the license on another site any time from your account.', 'chatbotistic-profile' ) . '</p>';

		$html = Email_Template::render( array(
			'title'     => __( 'License deactivated', 'chatbotistic-profile' ),
			'preheader' => sprintf( __( 'A domain slot on your %s license just freed up.', 'chatbotistic-profile' ), $brand ),
			'body_html' => $body,
			'cta_label' => __( 'Manage Licenses', 'chatbotistic-profile' ),
			'cta_url'   => $license_pg,
		) );

		self::send( $email, sprintf( __( 'License deactivated on %s', 'chatbotistic-profile' ), wp_parse_url( $site, PHP_URL_HOST ) ?: $site ), $html );
	}

	// ── 5. Per-form WPCF auto-responder copy ───────────────────────────

	/**
	 * Filter callback. WPCF auto-responder consults this BEFORE falling
	 * back to the global WPISTIC_CF_ar_subject / _body. Returns
	 * [ subject, body ] for a known form name, or null to defer to the
	 * default.
	 *
	 * This fires (with $default = null) for every submission that reaches
	 * the auto-responder — named form or not — so it is also the hook
	 * point used to arm HTML rendering for the global default template
	 * configured in WPCF_Preset::apply().
	 */
	public function per_form_ar_template( $default, $form_name, $fields ) {
		// The WPCF auto-responder headers (From/Reply-To only, built in
		// WPISTIC_CF_Autoresponder::maybe_send()) never set a Content-Type,
		// so wp_mail() falls back to the `wp_mail_content_type` filter.
		// Arm it (self-removing on first use) so the HTML bodies below —
		// and the branded default body configured in WPCF_Preset — render
		// correctly, without leaking into unrelated wp_mail() calls later
		// in the same request (e.g. the admin new-submission notice).
		self::arm_html_content_type_once();

		if ( is_array( $default ) ) { return $default; }

		$brand         = self::brand_label();
		$site          = home_url( '/' );
		$dashboard_url = function_exists( 'cb_dashboard_url' ) ? cb_dashboard_url() : 'https://app.chatbotistic.com';
		$docs_url      = function_exists( 'cb_dashboard_url' ) ? cb_dashboard_url( 'docs' ) : 'https://app.chatbotistic.com/docs';

		$submitted_line = '<p style="margin:0 0 4px 0;font-size:13px;color:#6b7280;">' . esc_html__( 'Submitted:', 'chatbotistic-profile' ) . ' {date} &middot; ' . esc_html__( 'Form:', 'chatbotistic-profile' ) . ' {form}</p>';
		$message_block  = '<blockquote style="margin:12px 0 20px 0;padding:12px 16px;background:#f9fafb;border-left:3px solid ' . Email_Template::BRAND_GREEN . ';font-size:14px;color:#374151;">{message}</blockquote>';
		$links_line     = '<p style="margin:0;">' . sprintf(
			wp_kses_post( __( 'In the meantime: <a href="%1$s" style="color:' . Email_Template::BRAND_GREEN . '">docs</a> &middot; <a href="%2$s" style="color:' . Email_Template::BRAND_GREEN . '">your dashboard</a>.', 'chatbotistic-profile' ) ),
			esc_url( $docs_url ),
			esc_url( $dashboard_url )
		) . '</p>';

		$templates = array(
			'Chatbotistic Contact Form' => array(
				'subject' => sprintf( __( 'Thanks for contacting %s', 'chatbotistic-profile' ), $brand ),
				'body'    => Email_Template::render( array(
					'title'     => __( "We've received your message", 'chatbotistic-profile' ),
					'preheader' => __( 'A real person replies within one business day.', 'chatbotistic-profile' ),
					'body_html' => '<p style="margin:0 0 16px 0;">Hi {name},</p>'
						. '<p style="margin:0 0 16px 0;">' . sprintf( esc_html__( 'Thanks for reaching out to %s. Your message landed safely and a real person will reply within one business day — sooner during business hours.', 'chatbotistic-profile' ), esc_html( $brand ) ) . '</p>'
						. $submitted_line . $message_block . $links_line,
					'cta_label' => __( 'Open Your Dashboard', 'chatbotistic-profile' ),
					'cta_url'   => $dashboard_url,
				) ),
			),
			'Chatbotistic Demo Request (qualified)' => array(
				'subject' => sprintf( __( "We're reviewing your %s demo request", 'chatbotistic-profile' ), $brand ),
				'body'    => Email_Template::render( array(
					'title'     => __( "We're reviewing your demo request", 'chatbotistic-profile' ),
					'preheader' => __( 'Typical review turnaround is one business day.', 'chatbotistic-profile' ),
					'body_html' => '<p style="margin:0 0 16px 0;">Hi {name},</p>'
						. '<p style="margin:0 0 8px 0;">' . esc_html__( "Thanks for the detailed demo request. Here's what happens next:", 'chatbotistic-profile' ) . '</p>'
						. '<ol style="margin:0 0 16px 0;padding-left:20px;">'
						. '<li style="margin-bottom:6px;">' . esc_html__( 'We review your business details (≈ 1 business day)', 'chatbotistic-profile' ) . '</li>'
						. '<li style="margin-bottom:6px;">' . esc_html__( "If we're a fit, we build a 14-day demo environment using the paid-plan features most relevant to your use case", 'chatbotistic-profile' ) . '</li>'
						. '<li style="margin-bottom:0;">' . esc_html__( 'You receive login details + a guided walkthrough', 'chatbotistic-profile' ) . '</li>'
						. '</ol>'
						. '<p style="margin:0 0 16px 0;">' . sprintf( wp_kses_post( __( 'Want to start using the platform now instead? <a href="%s" style="color:' . Email_Template::BRAND_GREEN . '">The Free plan is one click</a>.', 'chatbotistic-profile' ) ), esc_url( trailingslashit( $site ) . 'register/' ) ) . '</p>'
						. $links_line,
				) ),
			),
			'Chatbotistic Demo Request' => array(
				'subject' => sprintf( __( 'We received your %s demo request', 'chatbotistic-profile' ), $brand ),
				'body'    => Email_Template::render( array(
					'title'     => __( 'We received your demo request', 'chatbotistic-profile' ),
					'preheader' => __( 'A tailored walkthrough is on its way.', 'chatbotistic-profile' ),
					'body_html' => '<p style="margin:0 0 16px 0;">Hi {name},</p>'
						. '<p style="margin:0 0 16px 0;">' . esc_html__( "Thanks for asking for a demo. We'll reply within one business day with a tailored walkthrough.", 'chatbotistic-profile' ) . '</p>'
						. $links_line,
				) ),
			),
			'Chatbotistic Support Request' => array(
				'subject' => sprintf( __( '[%s Support] We got your message', 'chatbotistic-profile' ), $brand ),
				'body'    => Email_Template::render( array(
					'title'     => __( 'Your support ticket is in the queue', 'chatbotistic-profile' ),
					'preheader' => __( 'We reply in under 2 hours during business days.', 'chatbotistic-profile' ),
					'body_html' => '<p style="margin:0 0 16px 0;">Hi {name},</p>'
						. '<p style="margin:0 0 16px 0;">' . esc_html__( 'Your support ticket is in the queue. We reply in under 2 hours during business days.', 'chatbotistic-profile' ) . '</p>'
						. $message_block . $links_line,
				) ),
			),
			'Member Portal Support Ticket' => array(
				'subject' => sprintf( __( '[%s Support] Ticket received', 'chatbotistic-profile' ), $brand ),
				'body'    => Email_Template::render( array(
					'title'     => __( 'Your support ticket is in the priority queue', 'chatbotistic-profile' ),
					'preheader' => __( 'A real person replies within 2 hours during business days.', 'chatbotistic-profile' ),
					'body_html' => '<p style="margin:0 0 16px 0;">Hi {name},</p>'
						. '<p style="margin:0 0 16px 0;">' . esc_html__( 'Your support ticket is in the priority queue. A real person will reply within 2 hours during business days.', 'chatbotistic-profile' ) . '</p>'
						. $message_block . $links_line,
				) ),
			),
		);

		return $templates[ $form_name ] ?? null;
	}

	/**
	 * Arm the `wp_mail_content_type` filter for exactly the next send, so
	 * the branded HTML autoresponder bodies (named-form or the global
	 * default) render as HTML. Self-removes on first use; also cleared on
	 * shutdown as a safety net in case an autoresponder send never fires
	 * (e.g. empty subject/body) so nothing leaks past this request.
	 */
	private static function arm_html_content_type_once(): void {
		if ( has_filter( 'wp_mail_content_type', array( __CLASS__, 'html_content_type_once' ) ) ) {
			return;
		}
		add_filter( 'wp_mail_content_type', array( __CLASS__, 'html_content_type_once' ), 9999 );
		add_action( 'shutdown', array( __CLASS__, 'disarm_html_content_type' ) );
	}

	public static function html_content_type_once( $type ) {
		remove_filter( 'wp_mail_content_type', array( __CLASS__, 'html_content_type_once' ), 9999 );
		return 'text/html';
	}

	public static function disarm_html_content_type(): void {
		remove_filter( 'wp_mail_content_type', array( __CLASS__, 'html_content_type_once' ), 9999 );
	}

	// ── 6. Newsletter welcome ────────────────────────────────────────────

	/**
	 * WPistic Contact Form's newsletter module (class-wpcf-newsletter.php,
	 * WPISTIC_CF_Newsletter::process()) already fires
	 * `do_action( 'wpcf_newsletter_subscribed', $email, $source, $id )`
	 * the moment a new subscriber row is inserted — no plugin change was
	 * needed there, we just hook the existing action.
	 *
	 * @param string $email  Subscriber email.
	 * @param string $source Capture source (footer/shortcode/contact-form:...).
	 * @param int    $id     Subscriber row ID.
	 */
	public function on_newsletter_subscribed( $email, $source, $id ): void {
		if ( ! is_email( $email ) ) { return; }

		$brand         = self::brand_label();
		$docs_url      = function_exists( 'cb_dashboard_url' ) ? cb_dashboard_url( 'docs' ) : 'https://app.chatbotistic.com/docs';
		$dashboard_url = function_exists( 'cb_dashboard_url' ) ? cb_dashboard_url() : 'https://app.chatbotistic.com';

		$body  = '<p style="margin:0 0 16px 0;">' . esc_html__( "Hi there,", 'chatbotistic-profile' ) . '</p>';
		$body .= '<p style="margin:0 0 20px 0;">' . sprintf( esc_html__( "You're on the %s list. Here's what to expect:", 'chatbotistic-profile' ), esc_html( $brand ) ) . '</p>';
		$body .= '<ul style="margin:0 0 20px 0;padding-left:20px;">';
		$body .= '<li style="margin-bottom:8px;">' . esc_html__( 'Product updates — new widget types, integrations, and WhatsApp features as they ship', 'chatbotistic-profile' ) . '</li>';
		$body .= '<li style="margin-bottom:8px;">' . esc_html__( 'Practical tips for turning WhatsApp conversations into paying customers', 'chatbotistic-profile' ) . '</li>';
		$body .= '<li style="margin-bottom:0;">' . esc_html__( 'Occasional offers — never more than a couple of emails a month', 'chatbotistic-profile' ) . '</li>';
		$body .= '</ul>';
		$body .= '<p style="margin:0;">' . sprintf( wp_kses_post( __( 'Curious what the platform can do right now? Read the <a href="%s" style="color:' . Email_Template::BRAND_GREEN . '">docs</a>.', 'chatbotistic-profile' ) ), esc_url( $docs_url ) ) . '</p>';

		$html = Email_Template::render( array(
			'title'       => sprintf( __( "You're on the %s list", 'chatbotistic-profile' ), $brand ),
			'preheader'   => sprintf( __( 'Welcome to the %s newsletter.', 'chatbotistic-profile' ), $brand ),
			'body_html'   => $body,
			'cta_label'   => __( 'Explore the Dashboard', 'chatbotistic-profile' ),
			'cta_url'     => $dashboard_url,
			'footer_note' => __( "You're receiving this because you subscribed on our site. You can unsubscribe from any future newsletter email.", 'chatbotistic-profile' ),
		) );

		self::send( $email, sprintf( __( "🎉 You're on the %s list", 'chatbotistic-profile' ), $brand ), $html );
	}

	// ── helpers ─────────────────────────────────────────────────────────

	/**
	 * Send a branded HTML email. Every automated email in this class
	 * routes through here so it shares the Email_Template shell + headers.
	 */
	private static function send( string $to, string $subject, string $body_html ): void {
		if ( defined( 'WPCF_EMAIL_DISABLED' ) && WPCF_EMAIL_DISABLED ) { return; }
		if ( '1' === (string) get_option( 'WPISTIC_CF_emails_disabled', '0' ) ) { return; }

		$headers = class_exists( __NAMESPACE__ . '\\Email_Template' )
			? Email_Template::mail_headers()
			: array( 'Content-Type: text/html; charset=UTF-8' );

		wp_mail( $to, $subject, $body_html, $headers );
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
