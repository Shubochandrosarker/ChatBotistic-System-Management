<?php
/**
 * The 5 Chatbotistic plans.
 *
 * Slugs (free/starter/growth/agency/lifetime), prices, and caps here are
 * the single source of truth on the WordPress side and MUST stay in sync
 * with two other places that independently describe the same plans:
 *   - chatbotistic/page-pricing.php        (marketing pricing page copy)
 *   - chatbotistic-dashboard/src/lib/plans.ts (dashboard entitlement model —
 *     PLAN_ORDER there is exactly free|starter|growth|agency; Lifetime has
 *     no distinct dashboard tier and maps to 'agency' entitlements, see
 *     class-sso-bridge.php's plan mapping).
 *
 * @package Chatbotistic\Profile
 */

namespace Chatbotistic\Profile;

defined( 'ABSPATH' ) || exit;

class Plans {

	/**
	 * The canonical plan list. Caps in `_caps` are read by the
	 * Memberistic → Licenseistic Bridge to enforce per-plan limits.
	 *
	 * @return array<int,array>
	 */
	public static function definitions(): array {
		return [
			[
				'name'            => 'Free Forever',
				'slug'            => 'free',
				'description'     => 'Full access to the Chatbotistic Dashboard — no credit card.',
				'monthly_price'   => 0,
				'annual_price'    => 0,
				'included_people' => 1,
				'benefits'        => wp_json_encode( [
					'1 AI ChatBot Widget',
					'1 WhatsApp Agent',
					'1 Website Domain',
					'1 Team Seat',
					'100 campaign messages / mo',
					'Leads captured to your personal WhatsApp',
					'Includes Chatbotistic branding',
				] ),
				'is_featured'     => 0,
				'sort_order'      => 10,
				'status'          => 'active',
				'settings'        => wp_json_encode( [
					'billing_cycle'    => 'forever',
					'requires_payment' => false,
					'contact_only'     => false,
					'manual_approval'  => false,
					'license_plan'     => 'free',
					'limits'           => [ 'widgets' => 1, 'agents' => 1, 'domains' => 1, 'seats' => 1, 'messages_per_month' => 100, 'white_label' => false, 'branding' => true ],
				] ),
				'_caps'           => [ 'tier' => 'free',     'plan_name' => 'Free Forever', 'max_widgets' => 1,  'max_agents' => 1,  'max_domains' => 1,  'white_label' => false, 'branding' => true  ],
			],
			[
				'name'            => 'Starter',
				'slug'            => 'starter',
				'description'     => 'Full access to the Chatbotistic Dashboard for small teams.',
				'monthly_price'   => 19,
				'annual_price'    => 190,
				'included_people' => 2,
				'benefits'        => wp_json_encode( [
					'3 AI ChatBot Widgets',
					'5 WhatsApp Agents',
					'3 Website Domains',
					'2 Team Seats',
					'1,000 campaign messages / mo',
					'Booking forms',
					'Shared team inbox',
				] ),
				'is_featured'     => 0,
				'sort_order'      => 20,
				'status'          => 'active',
				'settings'        => wp_json_encode( [
					'billing_cycle'    => 'monthly',
					'requires_payment' => true,
					'contact_only'     => false,
					'manual_approval'  => false,
					'license_plan'     => 'starter',
					'limits'           => [ 'widgets' => 3, 'agents' => 5, 'domains' => 3, 'seats' => 2, 'messages_per_month' => 1000, 'white_label' => false, 'branding' => true ],
				] ),
				'_caps'           => [ 'tier' => 'starter',  'plan_name' => 'Starter',     'max_widgets' => 3,  'max_agents' => 5,  'max_domains' => 3,  'white_label' => false, 'branding' => true  ],
			],
			[
				'name'            => 'Growth',
				'slug'            => 'growth',
				'description'     => 'Full access to the Chatbotistic Dashboard, built to scale.',
				'monthly_price'   => 49,
				'annual_price'    => 490,
				'included_people' => 5,
				'benefits'        => wp_json_encode( [
					'10 AI ChatBot Widgets',
					'20 WhatsApp Agents',
					'10 Website Domains',
					'5 Team Seats',
					'5,000 campaign messages / mo',
					'Landing page editor',
					'AI replies',
					'Chatbotistic branding removed',
					'Ad attribution & ROAS dashboard',
				] ),
				'is_featured'     => 1,
				'sort_order'      => 30,
				'status'          => 'active',
				'settings'        => wp_json_encode( [
					'billing_cycle'    => 'monthly',
					'requires_payment' => true,
					'contact_only'     => false,
					'manual_approval'  => false,
					'license_plan'     => 'growth',
					'limits'           => [ 'widgets' => 10, 'agents' => 20, 'domains' => 10, 'seats' => 5, 'messages_per_month' => 5000, 'white_label' => false, 'branding' => false ],
				] ),
				'_caps'           => [ 'tier' => 'growth',   'plan_name' => 'Growth',      'max_widgets' => 10, 'max_agents' => 20, 'max_domains' => 10, 'white_label' => false, 'branding' => false ],
			],
			[
				'name'            => 'Agency',
				'slug'            => 'agency',
				'description'     => 'Full access to the Chatbotistic Dashboard, white-labeled for your agency.',
				'monthly_price'   => 149,
				'annual_price'    => 1490,
				'included_people' => 15,
				'benefits'        => wp_json_encode( [
					'30 AI ChatBot Widgets',
					'Unlimited WhatsApp Agents',
					'50 Website Domains',
					'15 Team Seats',
					'25,000 campaign messages / mo',
					'Full white-label + custom domain',
					'10 client sub-accounts',
					'Branded client reports',
				] ),
				'is_featured'     => 0,
				'sort_order'      => 40,
				'status'          => 'active',
				'settings'        => wp_json_encode( [
					'billing_cycle'    => 'monthly',
					'requires_payment' => true,
					'contact_only'     => false,
					'manual_approval'  => false,
					'license_plan'     => 'agency',
					'limits'           => [ 'widgets' => 30, 'agents' => -1, 'domains' => 50, 'seats' => 15, 'messages_per_month' => 25000, 'white_label' => true, 'branding' => false ],
				] ),
				'_caps'           => [ 'tier' => 'agency',   'plan_name' => 'Agency',      'max_widgets' => 30, 'max_agents' => -1, 'max_domains' => 50, 'white_label' => true,  'branding' => false ],
			],
			[
				'name'            => 'Lifetime — Contact Us',
				'slug'            => 'lifetime',
				'description'     => 'Legacy plan, limited availability. Full access to the Chatbotistic Dashboard, custom-priced.',
				'monthly_price'   => 0,  // Contact for pricing — checkout disabled.
				'annual_price'    => 0,
				'included_people' => 15,
				'benefits'        => wp_json_encode( [
					'Legacy plan — limited availability',
					'Everything in Agency',
					'Unlimited Widgets / Agents / Domains',
					'White-label with custom domain',
					'Founder-direct support channel',
					'Custom contract & invoicing',
				] ),
				'is_featured'     => 0,
				'sort_order'      => 50,
				'status'          => 'active',
				'settings'        => wp_json_encode( [
					'billing_cycle'    => 'lifetime',
					'requires_payment' => false,
					'contact_only'     => true,
					'manual_approval'  => true,
					'price_display'    => '***',
					'license_plan'     => 'lifetime',
					// Maps to dashboard plan 'agency' — see class-sso-bridge.php —
					// since the dashboard model has no distinct Lifetime tier.
					'limits'           => [ 'widgets' => -1, 'agents' => -1, 'domains' => -1, 'seats' => -1, 'messages_per_month' => -1, 'white_label' => true, 'branding' => false ],
				] ),
				'_caps'           => [ 'tier' => 'lifetime', 'plan_name' => 'Lifetime',    'max_widgets' => -1, 'max_agents' => -1, 'max_domains' => -1, 'white_label' => true,  'branding' => false ],
			],
		];
	}

	/**
	 * On Memberistic's first install the plugin calls seed_default_plans()
	 * which honours this filter, so we replace the seed with the Chatbotistic
	 * plans.
	 */
	public static function register_default_plans_filter(): void {
		add_filter( 'memberistic_default_plans', function () {
			return array_map( fn ( $p ) => self::strip_internal_keys( $p ), self::definitions() );
		}, 10 );
	}

	/**
	 * Wipe every existing plan and write the 4 Chatbotistic plans. Called by
	 * the Installer (on activate) and the admin Reset button. Idempotent.
	 */
	public static function wipe_and_replace(): array {
		$repo = '\WordPressistic\Memberistic\Database\Plans_Repository';
		if ( ! class_exists( $repo ) ) {
			return [ 'created' => 0, 'updated' => 0, 'skipped' => 'plans repo missing' ];
		}

		global $wpdb;
		// Direct truncate is safe — we own the schema and aren't deleting
		// memberships, only plan templates. Memberships keep their plan_id
		// reference; the IDs we write below may not match, so we update
		// memberships after.
		$existing  = $repo::get_all();
		$old_by_slug = [];
		foreach ( (array) $existing as $row ) {
			$old_by_slug[ (string) ( $row['slug'] ?? '' ) ] = (int) ( $row['id'] ?? 0 );
		}

		$created = 0;
		$updated = 0;
		foreach ( self::definitions() as $plan ) {
			$slug = $plan['slug'];
			$existing_id = $repo::exists_by_slug( $slug ) ? (int) ( $repo::get_by_slug( $slug )['id'] ?? 0 ) : 0;
			$db_data = self::strip_internal_keys( $plan );
			if ( $existing_id ) {
				$repo::update( $existing_id, $db_data );
				$updated++;
			} else {
				$repo::create( $db_data );
				$created++;
			}
		}

		// Delete any non-Chatbotistic plans (defender, patriot, guardian, etc).
		$chatbotistic_slugs = wp_list_pluck( self::definitions(), 'slug' );
		foreach ( $existing as $row ) {
			$slug = (string) ( $row['slug'] ?? '' );
			if ( $slug && ! in_array( $slug, $chatbotistic_slugs, true ) ) {
				// Only delete if no active memberships still use it.
				if ( ! $repo::has_active_memberships( (int) $row['id'] ) ) {
					$repo::delete( (int) $row['id'] );
				}
			}
		}

		return [ 'created' => $created, 'updated' => $updated ];
	}

	/**
	 * Non-destructive repair: create any missing Chatbotistic plans and update
	 * the settings/benefits of existing ones, WITHOUT deleting anything. Safe to
	 * run on a live site — no plans are removed and no memberships are touched.
	 *
	 * @return array{created:int,updated:int}
	 */
	public static function ensure_plans(): array {
		$repo = '\WordPressistic\Memberistic\Database\Plans_Repository';
		if ( ! class_exists( $repo ) ) {
			return [ 'created' => 0, 'updated' => 0 ];
		}

		$created = 0;
		$updated = 0;
		foreach ( self::definitions() as $plan ) {
			$db_data = self::strip_internal_keys( $plan );
			$existing = $repo::exists_by_slug( $plan['slug'] ) ? $repo::get_by_slug( $plan['slug'] ) : null;
			if ( is_array( $existing ) && ! empty( $existing['id'] ) ) {
				$repo::update( (int) $existing['id'], $db_data );
				$updated++;
			} else {
				$repo::create( $db_data );
				$created++;
			}
		}
		return [ 'created' => $created, 'updated' => $updated ];
	}

	/**
	 * Push the cap definitions into the Memberistic → Licenseistic Bridge
	 * option (mlb_plan_caps) so the bridge issues licenses with the right
	 * widgets / agents / domains. Keyed by the plan IDs as they exist now.
	 */
	public static function sync_bridge_caps(): int {
		$repo = '\WordPressistic\Memberistic\Database\Plans_Repository';
		if ( ! class_exists( $repo ) ) {
			return 0;
		}
		$caps = [];
		foreach ( self::definitions() as $plan ) {
			$row = $repo::get_by_slug( $plan['slug'] );
			if ( ! is_array( $row ) ) {
				continue;
			}
			$plan_id = (int) ( $row['id'] ?? 0 );
			if ( ! $plan_id ) {
				continue;
			}
			$caps[ $plan_id ] = $plan['_caps'];
		}
		update_option( 'mlb_plan_caps', $caps );
		return count( $caps );
	}

	private static function strip_internal_keys( array $plan ): array {
		unset( $plan['_caps'] );
		return $plan;
	}
}
