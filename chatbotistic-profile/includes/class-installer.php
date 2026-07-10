<?php
/**
 * One-button installer / re-installer.
 *
 * Runs on plugin activation AND when the admin hits the "Reset to
 * Chatbotistic defaults" button. Idempotent: re-running on a configured
 * site is safe.
 *
 * @package Chatbotistic\Profile
 */

namespace Chatbotistic\Profile;

defined( 'ABSPATH' ) || exit;

class Installer {

	/**
	 * Run the full install.
	 *
	 * @param bool $force_wipe_plans True on activation + the admin reset button.
	 * @return array Summary for the admin notice.
	 */
	public static function run( bool $force_wipe_plans = false ): array {
		$summary = [
			'pages_created' => 0,
			'plans_created' => 0,
			'plans_updated' => 0,
			'caps_synced'   => 0,
			'settings'      => false,
		];

		// 1. Pages.
		$summary['pages_created'] = Pages::ensure_all();

		// 2. Plans (wipe + replace on first install + on admin reset).
		if ( $force_wipe_plans && class_exists( '\WordPressistic\Memberistic\Database\Plans_Repository' ) ) {
			$plans = Plans::wipe_and_replace();
			$summary['plans_created'] = $plans['created'];
			$summary['plans_updated'] = $plans['updated'];
		}

		// 3. Sync bridge caps to the now-existing plan IDs.
		$summary['caps_synced'] = Plans::sync_bridge_caps();

		// 4. Memberistic settings hardening.
		$summary['settings'] = self::apply_settings();

		// 5. WPistic Contact Form preset — reply branding, auto-responder
		// copy, AI auto-reply rules, FAQ + KB seed for the local_rules
		// provider. Idempotent.
		if ( class_exists( 'WPISTIC_CF_Database' ) ) {
			$summary['wpcf_preset'] = WPCF_Preset::apply();
		}

		return $summary;
	}

	/**
	 * Non-destructive repair — the safe counterpart to run(true). Ensures
	 * pages, plans and mappings exist without deleting any plan or membership
	 * data. This is what the admin "Repair" button calls.
	 *
	 * @return array Summary for the admin notice.
	 */
	public static function repair(): array {
		$summary = [
			'pages_created' => 0,
			'plans_created' => 0,
			'plans_updated' => 0,
			'caps_synced'   => 0,
			'product_id'    => 0,
			'settings'      => false,
			'wpcf_preset'   => array(),
		];

		$summary['pages_created'] = Pages::ensure_all();

		if ( class_exists( '\WordPressistic\Memberistic\Database\Plans_Repository' ) ) {
			$plans = Plans::ensure_plans();
			$summary['plans_created'] = $plans['created'];
			$summary['plans_updated'] = $plans['updated'];
		}

		$summary['caps_synced'] = Plans::sync_bridge_caps();
		$summary['product_id']  = self::ensure_license_product();
		$summary['settings']    = self::apply_settings();

		// Push Chatbotistic-tuned defaults into WPistic Contact Form
		// (reply branding, auto-responder copy, AI auto-reply rules, FAQ
		// + KB seed). Idempotent — only writes keys still on their
		// shipped default.
		if ( class_exists( 'WPISTIC_CF_Database' ) ) {
			$summary['wpcf_preset'] = WPCF_Preset::apply();
		}

		// Pages may be new — make sure their pretty permalinks resolve.
		flush_rewrite_rules( false );

		return $summary;
	}

	/**
	 * Ensure the canonical Licenseistic product exists via the bridge, so a
	 * repair from this page can fix a missing product mapping too.
	 *
	 * @return int Product ID (0 if the bridge/Licenseistic is unavailable).
	 */
	private static function ensure_license_product(): int {
		if ( class_exists( '\WordPressistic\MLB\Plugin' ) && method_exists( '\WordPressistic\MLB\Plugin', 'ensure_product' ) ) {
			return ( new \WordPressistic\MLB\Plugin() )->ensure_product();
		}
		return (int) get_option( 'mlb_plan_product_id', 0 );
	}

	/**
	 * Push our preferred Memberistic settings (brand label, default cycle,
	 * waiver disabled, etc) into memberistic_settings.
	 */
	private static function apply_settings(): bool {
		$settings = get_option( 'memberistic_settings', [] );
		$settings = is_array( $settings ) ? $settings : [];

		$override = [
			'brand_label'           => 'Chatbotistic',
			'admin_menu_label'      => 'Memberistic',
			'business_name'         => 'Chatbotistic',
			'business_phone'        => '',
			'business_email'        => CBP_FROM_EMAIL,
			'support_email'         => CBP_FROM_EMAIL,
			'waiver_enabled'        => 'no',
			'checkins_enabled'      => 'no',
			'default_currency'      => 'USD',
			'currency_symbol'       => '$',
			'site_brand_url'        => CBP_BASE_URL,
			// Wire the new brand-neutral helpers in Memberistic to Chatbotistic copy.
			'member_id_prefix'      => 'CHT',
			'login_tagline'         => 'Welcome back. Sign in to your Chatbotistic account.',
			'login_cta_note'        => '',
			'qr_verification_label' => 'Chatbotistic — Member Verification',
			// Account template: hide the range/lane/check-in UI that ships in
			// Memberistic by default — Chatbotistic memberships are SaaS, not
			// range access.
			'account_show_lane_tools' => 'no',
		];

		// Merge — never overwrite a key the admin has explicitly set, except
		// the brand-y stuff which we want canonical.
		$canonical = [
			'brand_label',
			'admin_menu_label',
			'business_name',
			'business_email',
			'support_email',
			'waiver_enabled',
			'checkins_enabled',
			'member_id_prefix',
			'login_tagline',
			'qr_verification_label',
			'account_show_lane_tools',
		];
		foreach ( $override as $key => $val ) {
			if ( in_array( $key, $canonical, true ) || ! isset( $settings[ $key ] ) || '' === $settings[ $key ] ) {
				$settings[ $key ] = $val;
			}
		}

		update_option( 'memberistic_settings', $settings, false );
		return true;
	}
}
