<?php
/**
 * Page slug + URL overrides.
 *
 * Memberistic's Settings_Page::create_required_pages() honours the
 * memberistic_required_pages filter for the slug/title/content of each page,
 * and writes the resulting post IDs into the memberistic_settings option.
 * We override the slugs to match chatbotistic.com's URL scheme, then call
 * the same routine from the Installer.
 *
 * @package Chatbotistic\Profile
 */

namespace Chatbotistic\Profile;

defined( 'ABSPATH' ) || exit;

class Pages {

	/**
	 * Map of memberistic_settings key → slug + title + content.
	 *
	 * @return array<string,array{slug:string,title:string,content:string}>
	 */
	public static function chatbotistic_pages(): array {
		return [
			'login_page_id'          => [ 'slug' => CBP_SLUG_LOGIN,     'title' => 'Login',              'content' => '[memberistic_login]' ],
			'plans_page_id'          => [ 'slug' => CBP_SLUG_PLANS,     'title' => 'Memberships',        'content' => '[memberistic_plans]' ],
			'checkout_page_id'       => [ 'slug' => CBP_SLUG_CHECKOUT,  'title' => 'Checkout',           'content' => '[memberistic_checkout]' ],
			'account_page_id'        => [ 'slug' => CBP_SLUG_ACCOUNT,   'title' => 'My Account',         'content' => '[memberistic_account]' ],
			'renewal_page_id'        => [ 'slug' => CBP_SLUG_RENEWAL,   'title' => 'Renew Membership',   'content' => '[memberistic_renewal]' ],
			'failed_payment_page_id' => [ 'slug' => CBP_SLUG_FAILED,    'title' => 'Payment Failed',     'content' => '[memberistic_payment_failed]' ],
			'thank_you_page_id'      => [ 'slug' => CBP_SLUG_THANK_YOU, 'title' => 'Thank You',          'content' => '[memberistic_thank_you]' ],
			// staff_dashboard_page_id is intentionally NOT created here —
			// chatbotistic.com manages the dashboard via [chatbotistic_dashboard]
			// in the connector. Memberistic's setting can stay empty.
		];
	}

	public static function register_slug_overrides(): void {
		add_filter( 'memberistic_required_pages', function ( $pages ) {
			$chatbotistic = self::chatbotistic_pages();
			// Merge — keep any extra keys Memberistic adds (eg. staff_dashboard_page_id)
			// but override every key we care about.
			foreach ( $chatbotistic as $key => $row ) {
				$pages[ $key ] = $row;
			}
			return $pages;
		}, 5 );
	}

	/**
	 * URL for a given Memberistic page setting key.
	 */
	public static function url( string $setting_key, string $fallback_path = '/' ): string {
		$settings = get_option( 'memberistic_settings', [] );
		$post_id  = isset( $settings[ $setting_key ] ) ? (int) $settings[ $setting_key ] : 0;
		if ( $post_id ) {
			$url = get_permalink( $post_id );
			if ( $url ) {
				return $url;
			}
		}
		return CBP_BASE_URL . $fallback_path;
	}

	/**
	 * Force-recreate every Chatbotistic page right now — used by the
	 * Installer and the admin "Reset" button. Mirrors Memberistic's own
	 * create_required_pages() logic but only for our 7 keys.
	 */
	public static function ensure_all(): int {
		// Rename legacy "memberistic-*" pages to their clean slugs first, so
		// the existence checks below find them and don't create duplicates.
		self::migrate_legacy_slugs();

		$settings = get_option( 'memberistic_settings', [] );
		$settings = is_array( $settings ) ? $settings : [];
		$created  = 0;

		foreach ( self::chatbotistic_pages() as $key => $row ) {
			$existing_id = isset( $settings[ $key ] ) ? (int) $settings[ $key ] : 0;
			if ( $existing_id && 'trash' !== get_post_status( $existing_id ) ) {
				continue;
			}
			$by_slug = get_page_by_path( $row['slug'] );
			if ( $by_slug && 'trash' !== get_post_status( $by_slug ) ) {
				$settings[ $key ] = (int) $by_slug->ID;
				continue;
			}
			$id = wp_insert_post( [
				'post_title'   => $row['title'],
				'post_name'    => $row['slug'],
				'post_content' => $row['content'],
				'post_status'  => 'publish',
				'post_type'    => 'page',
			] );
			if ( ! is_wp_error( $id ) ) {
				$settings[ $key ] = (int) $id;
				$created++;
			}
		}
		update_option( 'memberistic_settings', $settings, false );
		return $created;
	}

	/**
	 * Rename pages still using the legacy "memberistic-*" slug to their
	 * clean Chatbotistic slug ("checkout", "renew", "payment-failed",
	 * "payment-success"). Also strips the leading "Memberistic " from the
	 * title so the URL bar and the page title never expose the underlying
	 * plugin name to end users.
	 *
	 * Idempotent — safe to run on every install / repair / activation.
	 */
	private static function migrate_legacy_slugs(): void {
		$rename_map = [
			'memberistic-checkout'        => [ 'slug' => CBP_SLUG_CHECKOUT,  'title' => 'Checkout' ],
			'memberistic-renewal'         => [ 'slug' => CBP_SLUG_RENEWAL,   'title' => 'Renew Membership' ],
			'memberistic-payment-failed'  => [ 'slug' => CBP_SLUG_FAILED,    'title' => 'Payment Failed' ],
			'memberistic-thank-you'       => [ 'slug' => CBP_SLUG_THANK_YOU, 'title' => 'Thank You' ],
			'memberistic-memberships'     => [ 'slug' => CBP_SLUG_PLANS,     'title' => 'Memberships' ],
			'memberistic-account'         => [ 'slug' => CBP_SLUG_ACCOUNT,   'title' => 'My Account' ],
			'memberistic-login'           => [ 'slug' => CBP_SLUG_LOGIN,     'title' => 'Login' ],
		];

		foreach ( $rename_map as $legacy_slug => $target ) {
			$page = get_page_by_path( $legacy_slug );
			if ( ! $page || 'trash' === get_post_status( $page ) ) {
				continue;
			}
			// Don't clobber a real page that already lives at the clean slug.
			if ( get_page_by_path( $target['slug'] ) ) {
				continue;
			}
			$update = [
				'ID'        => (int) $page->ID,
				'post_name' => $target['slug'],
			];
			if ( 0 === strpos( (string) $page->post_title, 'Memberistic ' ) ) {
				$update['post_title'] = $target['title'];
			}
			wp_update_post( $update );
		}
	}
}
