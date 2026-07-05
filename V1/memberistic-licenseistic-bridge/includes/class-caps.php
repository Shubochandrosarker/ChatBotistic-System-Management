<?php
/**
 * Per-plan caps configuration.
 *
 * Stored in option `mlb_plan_caps` as:
 *   [
 *     <memberistic_plan_id> => [
 *       'tier'        => 'free' | 'starter' | 'growth' | 'agency',
 *       'plan_name'   => 'Growth',
 *       'max_widgets' => 20,      // -1 = unlimited
 *       'max_agents'  => 50,
 *       'max_domains' => 5,
 *     ],
 *   ]
 *
 * @package WordPressistic\MLB
 */

namespace WordPressistic\MLB;

defined( 'ABSPATH' ) || exit;

class Caps {

	/** Free-tier defaults when no row is configured. */
	public const FREE = [
		'tier'          => 'free',
		'plan_name'     => 'Free',
		'max_widgets'   => 1,
		'max_agents'    => 1,
		'max_domains'   => 1,
		'white_label'   => false,
		'branding'      => true,
		// White-label brand fields surfaced to the customer-side widget plugin.
		// Empty strings = the widget renders its neutral fallback ("WhatsApp Widget").
		'brand_label'   => '',
		'brand_tagline' => '',
		'brand_homepage'=> '',
		'support_email' => '',
		'logo_url'      => '',
		'custom_domain' => '',
		'api_base_url'  => '',
	];

	/**
	 * Defaults seeded on first save — match the user's 4 Chatbotistic plans.
	 * Keys are plan slugs because plan IDs are install-specific; the admin
	 * page maps slug → ID on save.
	 *
	 * @return array<string,array>
	 */
	public static function default_by_slug(): array {
		return [
			'free' => [
				'tier'        => 'free',
				'plan_name'   => 'Free Forever',
				'max_widgets' => 1,
				'max_agents'  => 1,
				'max_domains' => 1,
				'white_label' => false,
				'branding'    => true,
			],
			'pro' => [
				'tier'        => 'pro',
				'plan_name'   => 'Pro',
				'max_widgets' => 5,
				'max_agents'  => 15,
				'max_domains' => 10,
				'white_label' => false,
				'branding'    => false,
			],
			'agency' => [
				'tier'        => 'agency',
				'plan_name'   => 'Agency',
				'max_widgets' => 30,
				'max_agents'  => 100,
				'max_domains' => 50,
				'white_label' => true,
				'branding'    => false,
			],
			'lifetime' => [
				'tier'        => 'lifetime',
				'plan_name'   => 'Lifetime',
				'max_widgets' => -1,
				'max_agents'  => -1,
				'max_domains' => -1,
				'white_label' => true,
				'branding'    => false,
			],
		];
	}

	/**
	 * Get the cap row for one Memberistic plan ID.
	 *
	 * @param int $plan_id Memberistic plan ID.
	 * @return array
	 */
	public static function for_plan_id( int $plan_id ): array {
		// Precedence:
		//   1. mlb_plan_caps keyed by plan ID         (admin UI override)
		//   2. mlb_plan_caps keyed by plan slug       (env-driven override —
		//      survives a Plans::wipe_and_replace cycle that changes plan IDs)
		//   3. plan settings.limits from Memberistic  (single source of truth)
		//   4. slug defaults from default_by_slug()
		//   5. FREE hard fallback
		$all = (array) get_option( 'mlb_plan_caps', [] );
		if ( isset( $all[ $plan_id ] ) && is_array( $all[ $plan_id ] ) ) {
			return array_merge( self::FREE, $all[ $plan_id ] );
		}

		$plan = self::memberistic_plan( $plan_id );

		// Slug-keyed override (precedence 2) — keep the plan rename / replace
		// resilient. Admins who write `mlb_plan_caps[pro] = [...]` keep their
		// override applying even after a Plans::wipe_and_replace assigns a
		// new numeric plan_id.
		if ( $plan ) {
			$slug = sanitize_key( (string) ( $plan['slug'] ?? '' ) );
			if ( $slug && isset( $all[ $slug ] ) && is_array( $all[ $slug ] ) ) {
				return array_merge( self::FREE, $all[ $slug ] );
			}
		}

		if ( $plan ) {
			$by_slug = self::default_by_slug();
			$slug    = sanitize_key( (string) ( $plan['slug'] ?? '' ) );
			$base    = ( $slug && isset( $by_slug[ $slug ] ) )
				? array_merge( self::FREE, $by_slug[ $slug ] )
				: self::FREE;

			// Let the plan's own settings.limits override the slug default so
			// the Memberistic plan stays the single source of truth.
			$from_settings = self::caps_from_plan_settings( $plan );
			if ( $from_settings ) {
				$base = array_merge( $base, $from_settings );
				if ( ! empty( $plan['name'] ) ) {
					$base['plan_name'] = sanitize_text_field( (string) $plan['name'] );
				}
			}
			return $base;
		}
		return self::FREE;
	}

	/**
	 * Translate a Memberistic plan's settings.limits block into cap keys.
	 *
	 * Limits use -1 for unlimited, consistent with the rest of the bridge and
	 * the license activation_limit handling.
	 *
	 * @param array $plan Memberistic plan row.
	 * @return array Partial cap row (may be empty).
	 */
	private static function caps_from_plan_settings( array $plan ): array {
		$settings = json_decode( (string) ( $plan['settings'] ?? '' ), true );
		if ( ! is_array( $settings ) || empty( $settings['limits'] ) || ! is_array( $settings['limits'] ) ) {
			return [];
		}
		$limits = $settings['limits'];
		$out    = [];
		if ( array_key_exists( 'widgets', $limits ) ) {
			$out['max_widgets'] = self::int_or_unlimited( $limits['widgets'] );
		}
		if ( array_key_exists( 'agents', $limits ) ) {
			$out['max_agents'] = self::int_or_unlimited( $limits['agents'] );
		}
		if ( array_key_exists( 'domains', $limits ) ) {
			$out['max_domains'] = self::int_or_unlimited( $limits['domains'] );
		}
		if ( array_key_exists( 'white_label', $limits ) ) {
			$out['white_label'] = (bool) $limits['white_label'];
		}
		if ( array_key_exists( 'branding', $limits ) ) {
			$out['branding'] = (bool) $limits['branding'];
		}
		return $out;
	}

	/**
	 * All cap rows keyed by plan_id.
	 *
	 * @return array
	 */
	public static function all(): array {
		return (array) get_option( 'mlb_plan_caps', [] );
	}

	public static function save( array $caps_by_plan_id ): void {
		update_option( 'mlb_plan_caps', $caps_by_plan_id );
	}

	/**
	 * Look up a Memberistic plan row by ID.
	 *
	 * @param int $plan_id Plan ID.
	 * @return array|null
	 */
	public static function memberistic_plan( int $plan_id ): ?array {
		$repo = '\WordPressistic\Memberistic\Database\Plans_Repository';
		if ( ! class_exists( $repo ) || ! method_exists( $repo, 'get' ) ) {
			return null;
		}
		$row = $repo::get( $plan_id );
		return is_array( $row ) ? $row : null;
	}

	/**
	 * All active Memberistic plans.
	 *
	 * @return array<int,array>
	 */
	public static function memberistic_plans(): array {
		$repo = '\WordPressistic\Memberistic\Database\Plans_Repository';
		if ( ! class_exists( $repo ) || ! method_exists( $repo, 'get_all' ) ) {
			return [];
		}
		$rows = $repo::get_all( [ 'status' => 'active' ] );
		return is_array( $rows ) ? $rows : [];
	}

	/**
	 * Sanitise one cap row.
	 *
	 * @param array $row Raw input.
	 * @return array
	 */
	public static function sanitize_row( array $row ): array {
		$caps = [
			'tier'           => sanitize_key( (string) ( $row['tier'] ?? 'free' ) ),
			'plan_name'      => sanitize_text_field( (string) ( $row['plan_name'] ?? 'Plan' ) ),
			'max_widgets'    => self::int_or_unlimited( $row['max_widgets'] ?? 1 ),
			'max_agents'     => self::int_or_unlimited( $row['max_agents']  ?? 1 ),
			'max_domains'    => self::int_or_unlimited( $row['max_domains'] ?? 1 ),
			'white_label'    => ! empty( $row['white_label'] ),
			'branding'       => isset( $row['branding'] ) ? (bool) $row['branding'] : true,
			'brand_label'    => sanitize_text_field( (string) ( $row['brand_label']    ?? '' ) ),
			'brand_tagline'  => sanitize_text_field( (string) ( $row['brand_tagline']  ?? '' ) ),
			'brand_homepage' => esc_url_raw(       (string) ( $row['brand_homepage'] ?? '' ) ),
			'support_email'  => sanitize_email(    (string) ( $row['support_email']  ?? '' ) ),
			'logo_url'       => esc_url_raw(       (string) ( $row['logo_url']       ?? '' ) ),
			'custom_domain'  => esc_url_raw(       (string) ( $row['custom_domain']  ?? '' ) ),
			'api_base_url'   => esc_url_raw(       (string) ( $row['api_base_url']   ?? '' ) ),
		];
		return $caps;
	}

	private static function int_or_unlimited( $value ): int {
		$s = strtolower( trim( (string) $value ) );
		if ( 'unlimited' === $s || '-1' === $s ) {
			return -1;
		}
		return max( 0, (int) $value );
	}
}
