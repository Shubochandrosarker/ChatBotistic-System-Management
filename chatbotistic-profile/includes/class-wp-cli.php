<?php
/**
 * WP-CLI integration for the Chatbotistic stack.
 *
 *   wp chatbotistic status
 *   wp chatbotistic repair
 *   wp chatbotistic sync-caps
 *   wp chatbotistic reset --force
 *   wp chatbotistic entitlements <user_id|user_email>
 *
 * Use from deploy scripts so admins don't have to click around the WP admin
 * to verify or repair the system after a deploy.
 *
 * @package Chatbotistic\Profile
 */

namespace Chatbotistic\Profile;

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

class CLI {

	/**
	 * Print a one-line status for every component in the Chatbotistic stack.
	 *
	 * ## EXAMPLES
	 *
	 *     wp chatbotistic status
	 */
	public function status( $args, $assoc ) {
		$rows = array();
		foreach ( array(
			'Memberistic'         => defined( 'MEMBERISTIC_VERSION' ) ? constant( 'MEMBERISTIC_VERSION' ) : false,
			'Licenseistic'        => defined( 'WPISTIC_LSI_VERSION' ) ? constant( 'WPISTIC_LSI_VERSION' ) : false,
			'Memberistic→Licenseistic Bridge' => defined( 'MLB_VERSION' ) ? constant( 'MLB_VERSION' ) : false,
			'Chatbotistic Connector' => defined( 'CBC_VERSION' ) ? constant( 'CBC_VERSION' ) : false,
			'Chatbotistic Profile' => defined( 'CBP_VERSION' ) ? constant( 'CBP_VERSION' ) : false,
		) as $name => $ver ) {
			$rows[] = array(
				'component' => $name,
				'state'    => $ver ? 'active' : 'missing',
				'version'  => $ver ?: '—',
			);
		}

		// Tochat credentials
		if ( class_exists( '\Chatbotistic\Connector\Store' ) ) {
			$email  = (string) \Chatbotistic\Connector\Store::setting( 'api_email' );
			$has_pw = '' !== (string) \Chatbotistic\Connector\Store::setting( 'api_password' );
			$rows[] = array(
				'component' => 'Tochat credentials',
				'state'    => ( $email && $has_pw ) ? 'configured' : 'missing',
				'version'  => $email ? $email : '—',
			);
		}

		// License routes
		if ( function_exists( 'rest_get_server' ) ) {
			$routes = rest_get_server()->get_routes();
			$ok     = isset( $routes['/licenseistic/v1/entitlements'] );
			$rows[] = array(
				'component' => 'License REST',
				'state'    => $ok ? 'live' : 'missing',
				'version'  => $ok ? '/licenseistic/v1/*' : '—',
			);
		}

		// Cron
		$next = wp_next_scheduled( 'memberistic_daily_expire_memberships' );
		$rows[] = array(
			'component' => 'Daily reconcile cron',
			'state'    => $next ? 'scheduled' : 'missing',
			'version'  => $next ? date_i18n( 'Y-m-d H:i', $next ) : '—',
		);

		\WP_CLI\Utils\format_items( $assoc['format'] ?? 'table', $rows, array( 'component', 'state', 'version' ) );

		// Exit code
		$failing = 0;
		foreach ( $rows as $r ) {
			if ( in_array( $r['state'], array( 'missing' ), true ) ) {
				$failing++;
			}
		}
		if ( $failing > 0 ) {
			\WP_CLI::warning( sprintf( '%d component(s) need attention. Run `wp chatbotistic repair` to auto-fix the configurable ones.', $failing ) );
		} else {
			\WP_CLI::success( 'Chatbotistic stack is fully wired.' );
		}
	}

	/**
	 * Run the idempotent Installer::repair() — creates missing pages,
	 * plans, mappings, license product, and re-applies the canonical
	 * settings without deleting anything.
	 *
	 * ## EXAMPLES
	 *
	 *     wp chatbotistic repair
	 */
	public function repair( $args, $assoc ) {
		if ( ! defined( 'MEMBERISTIC_VERSION' ) ) {
			\WP_CLI::error( 'Memberistic is not active. Activate it first.' );
		}
		$res = Installer::repair();
		\WP_CLI::log( sprintf(
			'Repair complete: %d pages created, %d plans created, %d plans updated, %d cap rows synced, product #%d.',
			$res['pages_created'], $res['plans_created'], $res['plans_updated'], $res['caps_synced'], $res['product_id']
		) );
		\WP_CLI::success( 'Done.' );
	}

	/**
	 * Sync the bridge plan-caps option against the current Memberistic plan
	 * IDs. Cheap and idempotent. Run after a plan rename/edit if the
	 * Connector or Widget plugin is showing stale limits.
	 *
	 * ## EXAMPLES
	 *
	 *     wp chatbotistic sync-caps
	 */
	public function sync_caps( $args, $assoc ) {
		$n = Plans::sync_bridge_caps();
		\WP_CLI::success( sprintf( 'Synced caps for %d plan(s).', $n ) );
	}

	/**
	 * DESTRUCTIVE: replace any non-Chatbotistic plans (whose slug is not
	 * one of free / pro / agency / lifetime) when they have no active
	 * memberships. Use after a fresh install if seeded G2A plans need to
	 * go away.
	 *
	 * ## OPTIONS
	 *
	 * [--force]
	 * : Skip the confirmation prompt.
	 *
	 * ## EXAMPLES
	 *
	 *     wp chatbotistic reset --force
	 */
	public function reset( $args, $assoc ) {
		if ( empty( $assoc['force'] ) ) {
			\WP_CLI::confirm( 'This will delete non-Chatbotistic plans that have no active memberships. Continue?' );
		}
		$res = Installer::run( true );
		\WP_CLI::success( sprintf(
			'Reset complete: %d pages created, %d plans created, %d plans updated, %d cap rows synced.',
			$res['pages_created'], $res['plans_created'], $res['plans_updated'], $res['caps_synced']
		) );
	}

	/**
	 * Show a user's resolved cap entitlements: plan, widget cap, agent
	 * cap, domain cap, and license key.
	 *
	 * ## OPTIONS
	 *
	 * <user>
	 * : User ID, login, or email address.
	 *
	 * ## EXAMPLES
	 *
	 *     wp chatbotistic entitlements 42
	 *     wp chatbotistic entitlements jane@example.com
	 */
	public function entitlements( $args, $assoc ) {
		$user = $args[0] ?? '';
		$wp_user = is_numeric( $user ) ? get_user_by( 'id', (int) $user ) : ( get_user_by( 'email', $user ) ?: get_user_by( 'login', $user ) );
		if ( ! $wp_user ) {
			\WP_CLI::error( 'User not found.' );
		}
		if ( ! class_exists( '\WordPressistic\Memberistic\Database\Memberships_Repository' ) ) {
			\WP_CLI::error( 'Memberistic is not active.' );
		}
		$membership = \WordPressistic\Memberistic\Database\Memberships_Repository::get_by_user_id( $wp_user->ID );
		if ( ! is_array( $membership ) ) {
			\WP_CLI::warning( sprintf( 'No membership found for #%d (%s).', $wp_user->ID, $wp_user->user_email ) );
			return;
		}
		$caps = class_exists( '\WordPressistic\MLB\Caps' )
			? \WordPressistic\MLB\Caps::for_plan_id( (int) ( $membership['plan_id'] ?? 0 ) )
			: array();
		$key = (string) get_user_meta( $wp_user->ID, 'mlb_license_key', true );
		\WP_CLI\Utils\format_items( 'table', array(
			array( 'field' => 'user_id',     'value' => (string) $wp_user->ID ),
			array( 'field' => 'email',       'value' => $wp_user->user_email ),
			array( 'field' => 'membership',  'value' => (string) ( $membership['status'] ?? '?' ) ),
			array( 'field' => 'plan',        'value' => (string) ( $caps['plan_name'] ?? '?' ) ),
			array( 'field' => 'tier',        'value' => (string) ( $caps['tier'] ?? '?' ) ),
			array( 'field' => 'max_widgets', 'value' => (string) ( $caps['max_widgets'] ?? '?' ) ),
			array( 'field' => 'max_agents',  'value' => (string) ( $caps['max_agents'] ?? '?' ) ),
			array( 'field' => 'max_domains', 'value' => (string) ( $caps['max_domains'] ?? '?' ) ),
			array( 'field' => 'white_label', 'value' => $caps['white_label'] ? 'yes' : 'no' ),
			array( 'field' => 'license_key', 'value' => $key ?: '—' ),
		), array( 'field', 'value' ) );
	}
}

\WP_CLI::add_command( 'chatbotistic', __NAMESPACE__ . '\\CLI' );
