<?php
/**
 * WPistic Contact Form preset for Chatbotistic.
 *
 * Pushes canonical WPCF settings into the database on Profile install/
 * repair so a fresh Chatbotistic deployment auto-configures:
 *
 *   - Reply branding (From name / email / signature)
 *   - Auto-responder enabled with a Chatbotistic-tone subject + body
 *   - AI smart-reply + auto-reply rules tuned for our form names
 *     (Contact / Demo / Demo Qualified / Support / Portal Ticket)
 *   - Capture toggles aligned to the Chatbotistic theme (no G2A forms)
 *
 * Never overwrites a setting the admin has already changed — every
 * write is gated by an "is it still default / empty?" check.
 *
 * @package Chatbotistic\Profile
 */

namespace Chatbotistic\Profile;

defined( 'ABSPATH' ) || exit;

class WPCF_Preset {

	/**
	 * Apply the preset. Idempotent — safe to run on every Profile repair.
	 *
	 * @return array<string,int>  Summary: keys=areas, values=written count.
	 */
	public static function apply(): array {
		$summary = array( 'reply_branding' => 0, 'autoresponder' => 0, 'ai_rules' => 0, 'capture_toggles' => 0 );

		if ( ! class_exists( 'WPISTIC_CF_Database' ) ) {
			return $summary;
		}

		$brand     = (string) ( get_option( 'memberistic_settings', array() )['brand_label'] ?? 'Chatbotistic' );
		$from_mail = defined( 'CBP_FROM_EMAIL' ) ? CBP_FROM_EMAIL : (string) get_option( 'admin_email' );
		$from_name = defined( 'CBP_FROM_NAME' )  ? CBP_FROM_NAME  : $brand;
		$site_url  = home_url( '/' );

		// 1. Reply branding — only write if still default/empty so we don't
		// overwrite an admin's customisation.
		$reply_defaults = array(
			'WPISTIC_CF_reply_from_name'  => $from_name,
			'WPISTIC_CF_reply_from_email' => $from_mail,
			'WPISTIC_CF_reply_signature'  => sprintf(
				"— The %s team\n%s\nReply to this email to continue the conversation.",
				$brand,
				$site_url
			),
		);
		foreach ( $reply_defaults as $key => $value ) {
			if ( self::is_writable_default( $key ) ) {
				update_option( $key, $value, false );
				$summary['reply_branding']++;
			}
		}

		// 2. Auto-responder — enable by default with a Chatbotistic-tone
		// confirmation. Honours WPCF_EMAIL_DISABLED so staging deploys
		// don't blast emails.
		if ( '' === (string) get_option( 'WPISTIC_CF_ar_enabled', '' ) || '0' === (string) get_option( 'WPISTIC_CF_ar_enabled' ) && self::is_first_run() ) {
			update_option( 'WPISTIC_CF_ar_enabled', '1', false );
			$summary['autoresponder']++;
		}
		$ar_subject = 'Thanks for contacting ' . $brand . ' — we got your message';
		$ar_body    = self::default_ar_body( $brand, $site_url );

		foreach ( array( 'WPISTIC_CF_ar_subject' => $ar_subject, 'WPISTIC_CF_ar_body' => $ar_body ) as $key => $value ) {
			if ( self::is_writable_default( $key ) ) {
				update_option( $key, $value, false );
				$summary['autoresponder']++;
			}
		}

		// 3. AI smart-reply + auto-reply rules tuned for Chatbotistic forms.
		// Provider stays local_rules until an admin configures an API key.
		if ( self::is_writable_default( 'wpistic_cf_ai_smart_reply_enabled' ) ) {
			update_option( 'wpistic_cf_ai_smart_reply_enabled', '1', false );
			$summary['ai_rules']++;
		}
		// Per-form auto-reply seed rules. Each line is "keyword => template".
		// Template substitutions: {name}, {site_name}, {site_url}.
		$ai_rules_default = implode( "\n", array(
			'demo => Hi {name}, thanks for requesting a demo. We review every demo request manually to make sure we set up a 14-day environment that actually fits your business — expect a reply within one business day. — The {site_name} team',
			'pricing => Hi {name}, plans start at $0 Free Forever, $9/mo Pro, $99/mo Agency, plus a Lifetime tier. Full details at {site_url}pricing/.',
			'agency => Hi {name}, our Agency plan covers up to 30 widgets, 100 WhatsApp agents, 50 domains, and full white-label — perfect for reselling. Reply if you want a partner-rate quote.',
			'wordpress => Hi {name}, the Chatbotistic WordPress addon is included with every plan. Paste your license key and you go live in under 5 minutes — guide at {site_url}docs/.',
			'whatsapp => Hi {name}, no WhatsApp Business API account is needed — Chatbotistic works with a standard WhatsApp number. Want a quick walkthrough? Reply with a good time to call.',
			'lifetime => Hi {name}, the Lifetime / LTD tier is limited to the first 50 founders. Reply to this email with your business details and we will send the offer terms.',
			'billing => Hi {name}, our billing team has been notified and will reply within one business day with the invoice / refund / cancellation detail you need.',
			'integration => Hi {name}, Chatbotistic ships native integrations for HubSpot, Zoho, Pipedrive, Stripe, PayPal, Google Sheets, Email and Webhooks. Tell us which stack you use and we will send a specific guide.',
		) );
		if ( self::is_writable_default( 'wpistic_cf_ai_auto_reply_rules' ) ) {
			update_option( 'wpistic_cf_ai_auto_reply_rules', $ai_rules_default, false );
			$summary['ai_rules']++;
		}
		if ( self::is_writable_default( 'wpistic_cf_ai_auto_reply_subject' ) ) {
			update_option( 'wpistic_cf_ai_auto_reply_subject', 'Re: your message to ' . $brand, false );
			$summary['ai_rules']++;
		}
		// FAQ + KB seed (admins can extend in WPCF → Settings → AI).
		if ( self::is_writable_default( 'wpistic_cf_ai_faq_text' ) ) {
			update_option( 'wpistic_cf_ai_faq_text', self::default_faq_text( $brand, $site_url ), false );
			$summary['ai_rules']++;
		}
		if ( self::is_writable_default( 'wpistic_cf_ai_kb_text' ) ) {
			update_option( 'wpistic_cf_ai_kb_text', self::default_kb_text( $brand ), false );
			$summary['ai_rules']++;
		}

		// 4. Capture toggles — disable the G2A bridge (this is a
		// Chatbotistic deployment) and keep the standard 4 plugins on.
		$capture_defaults = array(
			'WPISTIC_CF_capture_cf7'     => '1',
			'WPISTIC_CF_capture_wpforms' => '1',
			'WPISTIC_CF_capture_gform'   => '1',
			'WPISTIC_CF_capture_fluent'  => '1',
			'WPISTIC_CF_capture_g2a'     => '0',
			'WPISTIC_CF_capture_wpmail'  => '0',
		);
		foreach ( $capture_defaults as $key => $value ) {
			if ( self::is_writable_default( $key, true ) ) {
				update_option( $key, $value, false );
				$summary['capture_toggles']++;
			}
		}

		return $summary;
	}

	/**
	 * Whether we should write this option — true when the option is empty
	 * or matches the WPCF-shipped default.
	 *
	 * @param string $key WP option key.
	 * @param bool   $treat_zero_as_default Treat '0' as still-default
	 *                                      (capture toggles default to '1';
	 *                                      we want to overwrite '0' too).
	 */
	private static function is_writable_default( string $key, bool $treat_zero_as_default = false ): bool {
		$current = get_option( $key, '' );
		if ( '' === $current || array() === $current || null === $current ) {
			return true;
		}
		// Some keys default to '1' or '0' — let the caller decide whether
		// to overwrite. We never overwrite a non-empty string that already
		// looks customised (length > 5, not just '0' or '1').
		if ( is_string( $current ) && in_array( $current, array( '0', '1' ), true ) ) {
			return $treat_zero_as_default;
		}
		return false;
	}

	/**
	 * Whether this looks like a fresh Chatbotistic install (no submissions
	 * captured yet). Used to decide if the auto-responder default-on flip
	 * is safe.
	 */
	private static function is_first_run(): bool {
		global $wpdb;
		if ( ! method_exists( 'WPISTIC_CF_Database', 'submissions_table' ) ) {
			return false;
		}
		$table = \WPISTIC_CF_Database::submissions_table();
		$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ); // phpcs:ignore WordPress.DB
		return 0 === $count;
	}

	/**
	 * Branded HTML body for the global (non-form-specific) auto-responder —
	 * the "general enquiry" reply sent whenever a submitted form has no
	 * dedicated template in Emails_Automation::per_form_ar_template().
	 *
	 * WPISTIC_CF_ar_body is a plain stored option string, not PHP, so we
	 * pre-render the full Email_Template HTML once here and store *that*
	 * string — the {name}/{form}/{message}/{date} placeholder tokens stay
	 * literal text inside the markup and are still resolved later by
	 * WPISTIC_CF_Autoresponder::maybe_send()'s strtr() call.
	 */
	private static function default_ar_body( string $brand, string $site_url ): string {
		if ( ! class_exists( __NAMESPACE__ . '\\Email_Template' ) ) {
			// Fallback plain text if the template class is unavailable for some reason.
			return "Hey {name},\n\n"
				. 'Thanks for reaching out to ' . $brand . ". We've received your "
				. "message and reply within 24 hours.\n\n"
				. "Submitted: {date}\n"
				. "Form: {form}\n\n"
				. "Your message:\n----------\n{message}\n----------\n\n"
				. '— The ' . $brand . " team\n" . $site_url;
		}

		$dashboard_url = function_exists( 'cb_dashboard_url' ) ? cb_dashboard_url() : 'https://dashboard.chatbotistic.com';
		$docs_url      = function_exists( 'cb_dashboard_url' ) ? cb_dashboard_url( 'docs' ) : 'https://dashboard.chatbotistic.com/docs';

		$body  = '<p style="margin:0 0 16px 0;">Hey {name},</p>';
		$body .= '<p style="margin:0 0 16px 0;">' . sprintf(
			esc_html__( "Thanks for reaching out to %s. We've received your message and reply within 24 hours.", 'chatbotistic-profile' ),
			esc_html( $brand )
		) . '</p>';
		$body .= '<p style="margin:0 0 4px 0;font-size:13px;color:#6b7280;">' . esc_html__( 'Submitted:', 'chatbotistic-profile' ) . ' {date} &middot; ' . esc_html__( 'Form:', 'chatbotistic-profile' ) . ' {form}</p>';
		$body .= '<blockquote style="margin:12px 0 20px 0;padding:12px 16px;background:#f9fafb;border-left:3px solid ' . Email_Template::BRAND_GREEN . ';font-size:14px;color:#374151;">{message}</blockquote>';
		$body .= '<p style="margin:0;">In the meantime: <a href="' . esc_url( $docs_url ) . '" style="color:' . Email_Template::BRAND_GREEN . ';">docs</a> &middot; <a href="' . esc_url( $dashboard_url ) . '" style="color:' . Email_Template::BRAND_GREEN . ';">your dashboard</a>.</p>';

		return Email_Template::render( array(
			'title'     => __( "We've received your message", 'chatbotistic-profile' ),
			/* translators: %s: brand label */
			'preheader' => sprintf( __( 'Thanks for contacting %s — a real person replies within 24 hours.', 'chatbotistic-profile' ), $brand ),
			'body_html' => $body,
			'cta_label' => __( 'Open Your Dashboard', 'chatbotistic-profile' ),
			'cta_url'   => $dashboard_url,
		) );
	}

	private static function default_faq_text( string $brand, string $site_url ): string {
		return implode( "\n", array(
			'Q: What is ' . $brand . '?',
			'A: A WordPress-first WhatsApp AI chatbot platform. Create widgets in your member portal, activate them with a license key on your WordPress site, and start capturing leads.',
			'',
			'Q: How do I install the WordPress plugin?',
			'A: Buy or pick a free plan, download the addon from your member portal, upload it via Plugins → Add New → Upload, activate it, paste your license key on the License screen, and pick a widget on the Widgets screen.',
			'',
			'Q: How many domains can I use one license on?',
			'A: Free covers 1 domain, Pro covers 10, Agency covers 50, Lifetime is unlimited.',
			'',
			'Q: Do you support WhatsApp Business API?',
			'A: A standard WhatsApp number is enough — the platform handles routing. Business API integration is available on Agency.',
			'',
			'Q: How does pricing work?',
			'A: Free forever for 1 widget. Pro $9/mo or $90/yr. Agency $99/mo or $990/yr. Lifetime by application — see ' . $site_url . 'pricing/.',
			'',
			'Q: What happens when my plan expires?',
			'A: A 3-day grace window keeps widgets live during transient issues; after that, premium features pause and the widget falls back to free-tier caps. Your data is retained 90 days.',
			'',
			'Q: How do I cancel?',
			'A: Sign in, open Billing in the member portal, click Cancel — the widget stays live until the end of the current billing period.',
		) );
	}

	private static function default_kb_text( string $brand ): string {
		return implode( "\n", array(
			$brand . ' is a managed WhatsApp chatbot SaaS. Customers buy a plan on the brand site, get a license key, install the WordPress addon, and connect their chosen widget to their site. The addon validates the license against the brand\'s Licenseistic server every 12 hours with a 3-day grace window.',
			'',
			'Modules: AI Chatbot Widgets, WhatsApp Chat Widgets, Lead Capture Forms, Booking Forms, Multi-Agent Inbox, Email Notifications, CRM Integrations (HubSpot/Zoho/Pipedrive/Sheets), Stripe & PayPal payments, Landing Page tools, White-Label dashboard (Agency+), API & Webhooks, Analytics & Reports.',
			'',
			'Plan caps: Free (1 widget / 1 agent / 1 domain, includes brand mention), Pro (5/15/10, no brand mention), Agency (30/100/50, full white-label), Lifetime (unlimited).',
			'',
			'Demo policy: Demos are 14 days, paid-plan features enabled, manually approved after reviewing the business details — typical review turnaround is 1 business day.',
			'',
			'Refund policy: Pro / Agency plans are refundable within 14 days of purchase. Lifetime is non-refundable but transferable.',
			'',
			'Sales contact: hello@chatbotistic.com',
			'Support contact: support form at /support/',
		) );
	}
}
