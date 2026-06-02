<?php
/**
 * Bootstrap.
 *
 * @package Chatbotistic\Profile
 */

namespace Chatbotistic\Profile;

defined( 'ABSPATH' ) || exit;

class Plugin {

	public function boot(): void {
		// Page slug override — applied to Memberistic's required-pages map.
		Pages::register_slug_overrides();

		// Override the seeded plans on first install via Memberistic's filter.
		Plans::register_default_plans_filter();

		// Override every transactional email body + subject to use chatbotistic.com URLs.
		Emails::register();

		// Auto-approve memberships when payment recorded; auto-activate Free plans on creation.
		Auto_Approve::register();

		// Hide waiver UI surfaces (CSS + email-template filter).
		Stripper::register();

		// Re-route Memberistic's PMPro importer onto the Chatbotistic plan
		// slugs. The plugin ships a Guns 2 Ammo-flavoured default map; we
		// replace it with Chatbotistic's free/pro/agency/lifetime so any
		// PMPro export imported on a Chatbotistic install lands on the
		// right plan.
		add_filter( 'memberistic_import_level_map', static function () {
			return array(
				'bronze'                                  => 'free',
				'bronze yearly'                           => 'free',
				'free'                                    => 'free',
				'silver'                                  => 'pro',
				'silver yearly'                           => 'pro',
				'pro'                                     => 'pro',
				'pro monthly'                             => 'pro',
				'pro yearly'                              => 'pro',
				'gold'                                    => 'agency',
				'gold yearly'                             => 'agency',
				'agency'                                  => 'agency',
				'agency monthly'                          => 'agency',
				'agency yearly'                           => 'agency',
				'lifetime'                                => 'lifetime',
				'ltd'                                     => 'lifetime',
			);
		} );

		add_filter( 'memberistic_import_level_keyword_rules', static function () {
			return array(
				array( 'keywords' => array( 'lifetime', 'ltd', 'one-time' ),       'slug' => 'lifetime' ),
				array( 'keywords' => array( 'agency', 'gold', 'enterprise' ),      'slug' => 'agency' ),
				array( 'keywords' => array( 'pro', 'silver', 'business' ),         'slug' => 'pro' ),
				array( 'keywords' => array( 'free', 'bronze', 'starter', 'trial' ), 'slug' => 'free' ),
			);
		} );

		// Sync bridge cap defaults to the new 4 plans whenever an admin loads
		// our settings page (cheap, idempotent).
		if ( is_admin() ) {
			( new Admin() )->register();
		}

		// System health (Site Health tests + dashboard widget + REST
		// endpoints + first-run notice).
		( new System_Health() )->register();

		// Dismiss-welcome handler.
		add_action( 'admin_post_cbp_dismiss_welcome', static function () {
			if ( current_user_can( 'manage_options' ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) ), 'cbp_dismiss_welcome' ) ) {
				update_option( System_Health::FIRST_RUN_FLAG, '1', false );
			}
			wp_safe_redirect( wp_get_referer() ?: admin_url() );
			exit;
		} );

		// WP-CLI integration (loaded only in CLI context).
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once CBP_DIR . 'includes/class-wp-cli.php';
		}
	}
}
