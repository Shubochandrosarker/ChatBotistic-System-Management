<?php
/**
 * Email template overrides.
 *
 * Every Memberistic transactional email body + subject is rewritten to use
 * chatbotistic.com URLs and a Chatbotistic voice. From address is
 * hello@chatbotistic.com / "Chatbotistic" — applied only during Memberistic
 * email dispatch so we don't hijack other site emails.
 *
 * @package Chatbotistic\Profile
 */

namespace Chatbotistic\Profile;

defined( 'ABSPATH' ) || exit;

class Emails {

	public static function register(): void {
		add_filter( 'memberistic_email_template_subject', [ __CLASS__, 'subject' ], 10, 3 );
		add_filter( 'memberistic_email_template_body',    [ __CLASS__, 'body' ],    10, 3 );
		add_filter( 'memberistic_email_merge_tags',       [ __CLASS__, 'merge_tags' ], 10, 1 );

		// Override the FROM address but only while a Memberistic email is in flight.
		add_filter( 'memberistic_should_send_email', [ __CLASS__, 'arm_from_filter' ], 10, 1 );

		// Point the Memberistic → Licenseistic Bridge license email at the
		// canonical chatbotistic.com URLs.
		add_filter( 'mlb_account_url',         fn () => CBP_BASE_URL . '/account/' );
		add_filter( 'mlb_login_url',           fn () => CBP_BASE_URL . '/login/' );
		add_filter( 'mlb_reset_url',           fn () => CBP_BASE_URL . '/login?action=resetpassword' );
		add_filter( 'mlb_widget_download_url', fn () => CBP_BASE_URL . '/account/?download=widget' );
	}

	/** @param string $body */
	public static function body( $body, string $template, array $context ): string {
		$over = self::overrides();
		return $over[ $template ]['body'] ?? $body;
	}

	/** @param string $subject */
	public static function subject( $subject, string $template, array $context ): string {
		$over = self::overrides();
		return $over[ $template ]['subject'] ?? $subject;
	}

	/**
	 * Inject our own URL merge tags so the {login_url} / {plans_url} etc.
	 * tags work without us having to re-define their values inside each body.
	 */
	public static function merge_tags( array $context ): array {
		$context['{login_url}']         = Pages::url( 'login_page_id',          '/memberistic-login/' );
		$context['{plans_url}']         = Pages::url( 'plans_page_id',          '/memberistic-memberships/' );
		$context['{checkout_url}']      = Pages::url( 'checkout_page_id',       '/memberistic-checkout/' );
		$context['{account_url}']       = Pages::url( 'account_page_id',        '/account/' );
		$context['{renewal_url}']       = Pages::url( 'renewal_page_id',        '/memberistic-renewal/' );
		$context['{payment_failed_url}']= Pages::url( 'failed_payment_page_id', '/memberistic-payment-failed/' );
		$context['{thank_you_url}']     = Pages::url( 'thank_you_page_id',      '/memberistic-thank-you/' );
		$context['{password_reset_url}']= CBP_BASE_URL . '/login?action=resetpassword';
		$context['{brand_label}']       = 'Chatbotistic';
		$context['{site_url}']          = CBP_BASE_URL;
		return $context;
	}

	/**
	 * Arm wp_mail_from + wp_mail_from_name filters for this single send.
	 * Memberistic calls `memberistic_should_send_email` immediately before
	 * `wp_mail()`, so the filters are scoped to one dispatch.
	 *
	 * @param bool $should_send
	 * @return bool
	 */
	public static function arm_from_filter( $should_send ): bool {
		if ( ! $should_send ) {
			return $should_send;
		}
		add_filter( 'wp_mail_from',      [ __CLASS__, 'from_email' ], 9999 );
		add_filter( 'wp_mail_from_name', [ __CLASS__, 'from_name' ],  9999 );
		// One-shot: un-arm on shutdown so we never affect non-Memberistic mail.
		add_action( 'shutdown', [ __CLASS__, 'disarm_from_filter' ] );
		return $should_send;
	}

	public static function disarm_from_filter(): void {
		remove_filter( 'wp_mail_from',      [ __CLASS__, 'from_email' ], 9999 );
		remove_filter( 'wp_mail_from_name', [ __CLASS__, 'from_name' ],  9999 );
	}

	public static function from_email(): string { return CBP_FROM_EMAIL; }
	public static function from_name():  string { return CBP_FROM_NAME; }

	/**
	 * Per-template subject + body overrides.
	 *
	 * All bodies are plain text; Memberistic wraps with nl2br for HTML mail.
	 * Merge tags: {member_name}, {plan_name}, {membership_id}, {billing_cycle},
	 * {renewal_date}, {brand_label}, {site_url}, plus everything in merge_tags() above.
	 *
	 * @return array<string,array{subject:string,body:string}>
	 */
	private static function overrides(): array {
		$signoff = "\n\nThe Chatbotistic team\n{site_url}";

		return [
			'membership_created' => [
				'subject' => 'Welcome to Chatbotistic — your {plan_name} account is ready',
				'body'    => "Hi {member_name},\n\n"
					. "Welcome aboard! Your {plan_name} account ({membership_id}) is created. "
					. "Once your payment is confirmed you'll get an activation email and your dashboard unlocks immediately.\n\n"
					. "Log in:   {login_url}\n"
					. "Account:  {account_url}\n"
					. $signoff,
			],
			'membership_activated' => [
				'subject' => 'Your Chatbotistic {plan_name} plan is now active',
				'body'    => "Hi {member_name},\n\n"
					. "Your {plan_name} plan is now active. Your license key has been emailed separately — paste it on any WordPress site running the Chatbotistic Widget plugin to start delivering WhatsApp chats.\n\n"
					. "Manage your account: {account_url}\n"
					. "Build your widget:   {site_url}/dashboard/\n"
					. $signoff,
			],
			'membership_renewed' => [
				'subject' => 'Your Chatbotistic {plan_name} plan renewed — thanks!',
				'body'    => "Hi {member_name},\n\n"
					. "Your {plan_name} plan ({membership_id}) renewed for another {billing_cycle}. "
					. "Next renewal: {renewal_date}.\n\n"
					. "Receipt + invoice: {account_url}\n"
					. $signoff,
			],
			'renewal_reminder' => [
				'subject' => 'Your Chatbotistic plan renews soon',
				'body'    => "Hi {member_name},\n\n"
					. "Friendly reminder — your {plan_name} plan renews on {renewal_date}. "
					. "Nothing to do; we'll handle it on your saved card.\n\n"
					. "Manage payment method: {account_url}\n"
					. $signoff,
			],
			'expiring_30_days' => [
				'subject' => 'Your Chatbotistic plan renews in 30 days',
				'body'    => "Hi {member_name},\n\n"
					. "Heads up: your {plan_name} plan renews in 30 days ({renewal_date}). "
					. "Want to change plans, update your card, or cancel? Do it here:\n\n"
					. "{account_url}\n"
					. $signoff,
			],
			'expiring_7_days' => [
				'subject' => 'Your Chatbotistic plan renews in 7 days',
				'body'    => "Hi {member_name},\n\n"
					. "Quick reminder — your {plan_name} plan renews in 7 days ({renewal_date}).\n\n"
					. "Manage your subscription: {account_url}\n"
					. $signoff,
			],
			'expiring_tomorrow' => [
				'subject' => 'Your Chatbotistic plan renews tomorrow',
				'body'    => "Hi {member_name},\n\n"
					. "Your {plan_name} plan renews tomorrow ({renewal_date}). "
					. "If you'd like to make any changes, here's your account: {account_url}\n"
					. $signoff,
			],
			'membership_expired' => [
				'subject' => 'Your Chatbotistic plan has expired',
				'body'    => "Hi {member_name},\n\n"
					. "Your {plan_name} plan ({membership_id}) has expired. Your widgets will keep working in read-only mode for 7 days — after that, lead capture pauses.\n\n"
					. "Renew now: {renewal_url}\n"
					. "Pick a different plan: {plans_url}\n"
					. $signoff,
			],
			'payment_failed' => [
				'subject' => 'Action needed — your Chatbotistic payment failed',
				'body'    => "Hi {member_name},\n\n"
					. "We couldn't process the last payment for your {plan_name} plan. Your widgets are still live for a few days while you fix this.\n\n"
					. "Update your card: {payment_failed_url}\n"
					. "Account home:     {account_url}\n"
					. $signoff,
			],
			'membership_cancelled' => [
				'subject' => 'Your Chatbotistic plan was cancelled',
				'body'    => "Hi {member_name},\n\n"
					. "Your {plan_name} plan ({membership_id}) is cancelled. You'll keep access until {renewal_date}, then your widgets will go to read-only mode.\n\n"
					. "Re-activate any time: {plans_url}\n"
					. $signoff,
			],
			'linked_member_added' => [
				'subject' => "You've been added to a Chatbotistic team",
				'body'    => "Hi {member_name},\n\n"
					. "You've been added to a Chatbotistic team account. Log in to start managing widgets:\n\n"
					. "{login_url}\n"
					. "Forgot your password? {password_reset_url}\n"
					. $signoff,
			],
			'staff_manual' => [
				'subject' => 'A message from Chatbotistic',
				'body'    => "Hi {member_name},\n\n{message}\n" . $signoff,
			],
			// waiver_missing — Chatbotistic doesn't use waivers. Returning an
			// empty subject suppresses the send via memberistic_should_send_email
			// below.
			'waiver_missing' => [
				'subject' => '',
				'body'    => '',
			],
		];
	}
}
