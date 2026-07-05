<?php
/**
 * Memberistic membership gate.
 *
 * Access and per-plan limits are resolved live on every request — no
 * reliance on activation hooks, so cancellations and expiries take effect
 * immediately. Memberistic is the single source of truth for paid status.
 *
 * @package Chatbotistic\Connector
 */

namespace Chatbotistic\Connector;

defined( 'ABSPATH' ) || exit;

class Membership {

	/**
	 * Resolve a user's current membership and connector limits.
	 *
	 * Cap precedence (high → low):
	 *   1. Bridge caps via MLB\Caps::for_plan_id() — the single source of
	 *      truth across the whole stack. Already resolves admin overrides,
	 *      plan settings, slug defaults.
	 *   2. Connector's own cbc_settings.plan_limits override (legacy).
	 *   3. Memberistic plan's own settings.limits block.
	 *   4. Conservative 1/1/1 fallback.
	 *
	 * @param int $user_id WordPress user ID.
	 * @return array{active:bool,status:string,plan_id:int,plan_name:string,widget_limit:int,agent_limit:int,domain_limit:int,white_label:bool}
	 */
	public static function for_user( int $user_id ): array {
		$out = array(
			'active'       => false,
			'status'       => '',
			'plan_id'      => 0,
			'plan_name'    => '',
			'widget_limit' => 0,
			'agent_limit'  => 0,
			'domain_limit' => 0,
			'white_label'  => false,
		);
		if ( ! $user_id ) {
			return $out;
		}

		$repo = '\WordPressistic\Memberistic\Database\Memberships_Repository';
		$row  = ( class_exists( $repo ) && method_exists( $repo, 'get_by_user_id' ) ) ? $repo::get_by_user_id( $user_id ) : null;

		if ( is_array( $row ) ) {
			$out['status']  = (string) ( $row['status'] ?? '' );
			$out['plan_id'] = (int) ( $row['plan_id'] ?? 0 );
			$out['active']  = in_array( $out['status'], array( 'active', 'comped', 'trial' ), true );
		}

		$out['plan_name'] = (string) get_user_meta( $user_id, 'memberistic_active_plan_name', true );

		if ( $out['active'] ) {
			// 1. Bridge caps — single source of truth.
			if ( class_exists( '\WordPressistic\MLB\Caps' ) && $out['plan_id'] ) {
				$caps                = \WordPressistic\MLB\Caps::for_plan_id( $out['plan_id'] );
				$out['plan_name']    = $caps['plan_name']    ?? $out['plan_name'];
				$out['widget_limit'] = (int) ( $caps['max_widgets'] ?? 1 );
				$out['agent_limit']  = (int) ( $caps['max_agents']  ?? 1 );
				$out['domain_limit'] = (int) ( $caps['max_domains'] ?? 1 );
				$out['white_label']  = (bool) ( $caps['white_label'] ?? false );
			} else {
				// 2-3. Legacy fallback chain.
				$limits = self::plan_limits();
				$plan   = $limits[ $out['plan_id'] ] ?? null;
				if ( $plan ) {
					$out['widget_limit'] = (int) $plan['widgets'];
					$out['agent_limit']  = (int) $plan['agents'];
				} else {
					$from_plan = self::limits_from_plan_settings( $out['plan_id'] );
					$out['widget_limit'] = $from_plan['widgets'] ?? 1;
					$out['agent_limit']  = $from_plan['agents']  ?? 1;
					$out['domain_limit'] = $from_plan['domains'] ?? 1;
				}
			}
		}

		return apply_filters( 'cbc_membership', $out, $user_id );
	}

	/**
	 * Read widget/agent limits from a Memberistic plan's settings.limits block.
	 * -1 means unlimited. Returns an empty array when the plan has none.
	 *
	 * @param int $plan_id Memberistic plan ID.
	 * @return array{widgets?:int,agents?:int}
	 */
	private static function limits_from_plan_settings( int $plan_id ): array {
		if ( ! $plan_id ) {
			return array();
		}
		$repo = '\WordPressistic\Memberistic\Database\Plans_Repository';
		if ( ! class_exists( $repo ) || ! method_exists( $repo, 'get' ) ) {
			return array();
		}
		$plan = $repo::get( $plan_id );
		if ( ! is_array( $plan ) ) {
			return array();
		}
		$settings = json_decode( (string) ( $plan['settings'] ?? '' ), true );
		if ( ! is_array( $settings ) || empty( $settings['limits'] ) || ! is_array( $settings['limits'] ) ) {
			return array();
		}
		$out = array();
		if ( array_key_exists( 'widgets', $settings['limits'] ) ) {
			$out['widgets'] = (int) $settings['limits']['widgets'];
		}
		if ( array_key_exists( 'agents', $settings['limits'] ) ) {
			$out['agents'] = (int) $settings['limits']['agents'];
		}
		if ( array_key_exists( 'domains', $settings['limits'] ) ) {
			$out['domains'] = (int) $settings['limits']['domains'];
		}
		return $out;
	}

	/**
	 * Per-plan limits map: [ memberistic_plan_id => ['widgets'=>int,'agents'=>int] ].
	 * A value of -1 means unlimited.
	 *
	 * @return array<int,array{widgets:int,agents:int}>
	 */
	public static function plan_limits(): array {
		$raw = Store::setting( 'plan_limits', array() );
		return is_array( $raw ) ? $raw : array();
	}

	/**
	 * Memberistic plans, for the admin limits screen.
	 *
	 * @return array<int,array>
	 */
	public static function memberistic_plans(): array {
		$repo = '\WordPressistic\Memberistic\Database\Plans_Repository';
		if ( ! class_exists( $repo ) || ! method_exists( $repo, 'get_all' ) ) {
			return array();
		}
		$rows = $repo::get_all( array( 'status' => 'active' ) );
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Whether Memberistic is active.
	 *
	 * @return bool
	 */
	public static function memberistic_active(): bool {
		return defined( 'MEMBERISTIC_VERSION' ) || class_exists( '\WordPressistic\Memberistic\Database\Memberships_Repository' );
	}
}
