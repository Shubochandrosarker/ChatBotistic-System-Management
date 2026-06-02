<?php
/**
 * Memberistic → Licenseistic event bridge.
 *
 * Lifecycle:
 *   - membership_activated → ensure_license_for_membership() — creates a
 *     Licenseistic license if the user doesn't already have one for this
 *     plan, with activation_limit = max_domains and expires_at = renewal_date.
 *     Saves the plain key on user meta `mlb_license_key`.
 *   - membership status change → suspend, revoke, or restore the license.
 *
 * Idempotent: re-running on the same membership is a no-op.
 *
 * @package WordPressistic\MLB
 */

namespace WordPressistic\MLB;

defined( 'ABSPATH' ) || exit;

class Bridge {

	private const META_LICENSE_KEY = 'mlb_license_key';
	private const META_LICENSE_ID  = 'mlb_license_id';

	public function register(): void {
		add_action( 'memberistic_membership_activated', [ $this, 'on_activated' ], 10, 1 );
		add_action( 'memberistic_membership_created',   [ $this, 'on_created' ],   10, 1 );
		add_action( 'memberistic_membership_payment_recorded', [ $this, 'on_payment' ], 10, 3 );

		// Catch status flips (cancel, expire, suspend) — Memberistic itself
		// doesn't fire a dedicated hook for every transition, so we hook the
		// generic membership update via daily cron + a save filter.
		add_action( 'memberistic_daily_expire_memberships', [ $this, 'sync_all_licenses' ] );
	}

	/**
	 * Membership row created — usually pending. Issue a license only once it
	 * activates, except for the Free plan where activation is implicit.
	 *
	 * @param int $membership_id Memberistic membership ID.
	 */
	public function on_created( int $membership_id ): void {
		$membership = $this->fetch_membership( $membership_id );
		if ( ! $membership ) {
			return;
		}
		if ( 'active' === ( $membership['status'] ?? '' ) || 'trial' === ( $membership['status'] ?? '' ) || 'comped' === ( $membership['status'] ?? '' ) ) {
			$this->ensure_license_for_membership( $membership );
		}
	}

	public function on_activated( int $membership_id ): void {
		$membership = $this->fetch_membership( $membership_id );
		if ( $membership ) {
			$this->ensure_license_for_membership( $membership );
		}
	}

	public function on_payment( int $membership_id, int $payment_id, string $gateway ): void {
		$membership = $this->fetch_membership( $membership_id );
		if ( $membership ) {
			$this->ensure_license_for_membership( $membership );
		}
	}

	/**
	 * Daily reconciliation — walks every membership, ensures the licence
	 * mirrors the membership status. Cheap because Memberistic exposes only
	 * a few hundred rows per site in practice; the daily expire hook is the
	 * natural place to run this.
	 */
	public function sync_all_licenses(): void {
		$repo = '\WordPressistic\Memberistic\Database\Memberships_Repository';
		if ( ! class_exists( $repo ) || ! method_exists( $repo, 'get_all' ) ) {
			return;
		}
		$rows = $repo::get_all( [ 'limit' => 500 ] );
		if ( ! is_array( $rows ) ) {
			return;
		}
		foreach ( $rows as $row ) {
			$this->reconcile( $row );
		}
	}

	/**
	 * Ensure a Licenseistic license exists for this membership and matches
	 * the current cap row + renewal date. Creates if missing, updates expiry.
	 *
	 * @param array $membership Memberistic row.
	 */
	public function ensure_license_for_membership( array $membership ): void {
		$user_id = (int) ( $membership['primary_user_id'] ?? 0 );
		$plan_id = (int) ( $membership['plan_id'] ?? 0 );
		if ( ! $user_id || ! $plan_id ) {
			return;
		}
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return;
		}

		$caps       = Caps::for_plan_id( $plan_id );
		$product_id = (int) get_option( 'mlb_plan_product_id', 0 );
		$renewal    = $membership['renewal_date'] ?? '';
		$expires_at = $renewal ? gmdate( 'Y-m-d H:i:s', strtotime( $renewal . ' UTC' ) ) : '';

		$existing_id  = (int) get_user_meta( $user_id, self::META_LICENSE_ID, true );
		$existing_key = (string) get_user_meta( $user_id, self::META_LICENSE_KEY, true );

		// Recover a stranded license: if the user meta link is missing but this
		// customer already has a license on file, adopt it instead of issuing a
		// duplicate. This is what prevents the "two keys, one revoked" symptom.
		if ( ! $existing_id && class_exists( '\WPistic_LSI_License_Service' ) && method_exists( '\WPistic_LSI_License_Service', 'get_licenses' ) ) {
			$found = \WPistic_LSI_License_Service::get_licenses( [ 'customer_id' => $user_id, 'per_page' => 20 ] );
			if ( is_array( $found ) ) {
				// Prefer a non-revoked/non-expired row; otherwise the newest.
				$pick = null;
				foreach ( $found as $row ) {
					$st = (string) ( $row['status'] ?? '' );
					if ( ! in_array( $st, [ 'revoked', 'expired' ], true ) ) { $pick = $row; break; }
					$pick = $pick ?: $row;
				}
				if ( $pick && ! empty( $pick['license_id'] ) ) {
					$existing_id = (int) $pick['license_id'];
					update_user_meta( $user_id, self::META_LICENSE_ID, $existing_id );
				}
			}
		}

		// Path 1: license already exists — keep it in sync.
		if ( $existing_id && class_exists( '\WPistic_LSI_License_Service' ) ) {
			$row = \WPistic_LSI_License_Service::get_license( $existing_id );
			if ( $row ) {
				$update = [
					'status'           => 'active',
					'activation_limit' => -1 === $caps['max_domains'] ? 999 : (int) $caps['max_domains'],
				];
				if ( $expires_at ) {
					$update['expires_at'] = $expires_at;
				}
				\WPistic_LSI_License_Service::update_license( $existing_id, $update );
				$this->store_caps_on_license( $existing_id, $caps, $plan_id, $membership );
				return;
			}
		}

		// Path 2: create a new license.
		if ( ! class_exists( '\WPistic_LSI_License_Service' ) ) {
			return;
		}
		$payload = [
			'product_id'       => $product_id ?: null,
			'customer_id'      => $user_id,
			'customer_email'   => $user->user_email,
			'source'           => 'memberistic',
			'status'           => 'active',
			'activation_limit' => -1 === $caps['max_domains'] ? 999 : (int) $caps['max_domains'],
			'license_label'    => $caps['plan_name'] . ' — ' . $user->user_email,
		];
		if ( $expires_at ) {
			$payload['expires_at'] = $expires_at;
		}

		$res = \WPistic_LSI_License_Service::create_license( $payload );
		if ( is_array( $res ) && ! empty( $res['license_id'] ) ) {
			update_user_meta( $user_id, self::META_LICENSE_ID,  (int) $res['license_id'] );
			update_user_meta( $user_id, self::META_LICENSE_KEY, (string) ( $res['license_key'] ?? '' ) );
			$this->store_caps_on_license( (int) $res['license_id'], $caps, $plan_id, $membership );
			$this->email_license_to_user( $user, (string) $res['license_key'], $caps );

			/**
			 * Fires once a Chatbotistic membership has been fully provisioned:
			 *   - Memberistic membership is active
			 *   - Licenseistic license is created with the right cap row
			 *   - cap envelope is written to the license's notes field
			 *   - welcome email has been queued
			 *
			 * Use this hook to provision downstream resources (e.g. create the
			 * matching Tochat business under the user's userClient tag,
			 * register the user in a marketing-automation tool, etc).
			 *
			 * @param int   $user_id       WordPress user ID.
			 * @param int   $license_id    Licenseistic license ID.
			 * @param array $caps          Resolved cap row (tier / max_widgets / ...).
			 * @param array $membership    Memberistic membership row.
			 */
			do_action( 'cbc_membership_activated', $user_id, (int) $res['license_id'], $caps, $membership );
		}
	}

	/**
	 * Re-evaluate a membership and pull the license into the right state.
	 *
	 * @param array $membership Memberistic row.
	 */
	private function reconcile( array $membership ): void {
		$user_id = (int) ( $membership['primary_user_id'] ?? 0 );
		if ( ! $user_id ) {
			return;
		}
		$license_id = (int) get_user_meta( $user_id, self::META_LICENSE_ID, true );
		if ( ! $license_id || ! class_exists( '\WPistic_LSI_License_Service' ) ) {
			// No license yet — only create if status is active.
			if ( in_array( $membership['status'] ?? '', [ 'active', 'trial', 'comped' ], true ) ) {
				$this->ensure_license_for_membership( $membership );
			}
			return;
		}

		// Read the license's current status so we only act (and email) on a
		// real transition — reconcile() runs daily across every membership.
		$current = \WPistic_LSI_License_Service::get_license( $license_id );
		$was     = is_array( $current ) ? (string) ( $current['status'] ?? '' ) : '';

		$status = (string) ( $membership['status'] ?? '' );
		switch ( $status ) {
			case 'cancelled':
			case 'expired':
				if ( 'expired' !== $was ) {
					\WPistic_LSI_License_Service::expire_license( $license_id );
					$this->email_status_change_to_user( $user_id, 'expired', $membership );
				}
				break;
			case 'suspended':
			case 'paused':
			case 'past_due':
				if ( 'suspended' !== $was ) {
					\WPistic_LSI_License_Service::suspend_license( $license_id );
					$this->email_status_change_to_user( $user_id, 'suspended', $membership );
				}
				break;
			case 'active':
			case 'trial':
			case 'comped':
				if ( 'active' !== $was ) {
					\WPistic_LSI_License_Service::update_license( $license_id, [ 'status' => 'active' ] );
				}
				$this->ensure_license_for_membership( $membership );
				break;
		}
	}

	/**
	 * Notify the user that their license was suspended or expired. Premium
	 * features stop working but their account/login stays intact.
	 *
	 * @param int    $user_id    WP user ID.
	 * @param string $new_status 'suspended' or 'expired'.
	 * @param array  $membership Membership row.
	 */
	private function email_status_change_to_user( int $user_id, string $new_status, array $membership ): void {
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return;
		}
		$site    = get_bloginfo( 'name' );
		$account = home_url( '/account/' );
		$plans   = home_url( '/pricing/' );

		if ( 'expired' === $new_status ) {
			/* translators: %s: site name. */
			$subject = sprintf( __( '[%s] Your license has expired', 'memberistic-licenseistic-bridge' ), $site );
			$lead    = __( 'Your membership has ended, so your Chatbotistic license is now expired. Premium widget features are paused, but your account and data are safe.', 'memberistic-licenseistic-bridge' );
		} else {
			/* translators: %s: site name. */
			$subject = sprintf( __( '[%s] Your license is suspended', 'memberistic-licenseistic-bridge' ), $site );
			$lead    = __( 'We could not confirm your latest payment, so your Chatbotistic license is suspended. Update your billing to restore premium widget features. Your account and data are safe.', 'memberistic-licenseistic-bridge' );
		}

		/* translators: %s: user display name. */
		$body = sprintf( __( 'Hi %s,', 'memberistic-licenseistic-bridge' ), $user->display_name ) . "\n\n"
			. $lead . "\n\n"
			. __( 'Manage your account: ', 'memberistic-licenseistic-bridge' ) . $account . "\n"
			. __( 'View plans: ', 'memberistic-licenseistic-bridge' ) . $plans . "\n\n"
			/* translators: %s: site name. */
			. sprintf( __( '— The %s team', 'memberistic-licenseistic-bridge' ), $site );

		wp_mail( $user->user_email, $subject, $body );
	}

	/**
	 * Persist the cap row on the license so the REST activate response can
	 * include tier/plan_name/max_widgets/max_agents/max_domains.
	 *
	 * Uses Licenseistic's own `notes` field with a magic JSON envelope so we
	 * don't need a schema change.
	 *
	 * @param int   $license_id License ID.
	 * @param array $caps       Cap row from Caps::for_plan_id().
	 * @param int   $plan_id    Memberistic plan ID.
	 * @param array $membership Membership row.
	 */
	private function store_caps_on_license( int $license_id, array $caps, int $plan_id, array $membership ): void {
		if ( ! class_exists( '\WPistic_LSI_License_Service' ) ) {
			return;
		}
		$envelope = wp_json_encode( [
			'_mlb' => [
				'plan_id'      => $plan_id,
				'membership_id' => (int) ( $membership['id'] ?? 0 ),
				'caps'         => $caps,
				'updated'      => time(),
			],
		] );
		\WPistic_LSI_License_Service::update_license( $license_id, [
			'notes' => $envelope,
		] );
	}

	/**
	 * Email the new license key + login instructions to the user.
	 *
	 * @param \WP_User $user User.
	 * @param string   $key  Plain license key.
	 * @param array    $caps Cap row.
	 */
	private function email_license_to_user( \WP_User $user, string $key, array $caps ): void {
		if ( ! $key ) {
			return;
		}
		$site    = get_bloginfo( 'name' );
		$subject = sprintf( __( '[%s] Your %s license key is ready', 'memberistic-licenseistic-bridge' ), $site, $caps['plan_name'] );

		// Branded URLs. Filterable so the central profile can point them at the
		// canonical www.chatbotistic.com paths.
		$account_url  = (string) apply_filters( 'mlb_account_url',  home_url( '/account/' ) );
		$login_url    = (string) apply_filters( 'mlb_login_url',    home_url( '/login/' ) );
		$reset_url    = (string) apply_filters( 'mlb_reset_url',    home_url( '/login?action=resetpassword' ) );
		$download_url = (string) apply_filters( 'mlb_widget_download_url', $account_url );

		$fmt = static function ( $n ) {
			return -1 === (int) $n ? __( 'Unlimited', 'memberistic-licenseistic-bridge' ) : (string) (int) $n;
		};

		$body  = sprintf( __( 'Hi %s,', 'memberistic-licenseistic-bridge' ), $user->display_name ) . "\n\n"
			. sprintf( __( 'Your %s plan is active and your Chatbotistic license is ready. Here is everything you need to go live.', 'memberistic-licenseistic-bridge' ), $caps['plan_name'] ) . "\n\n"
			. "──────────────────────────────────────\n"
			. __( 'License key: ', 'memberistic-licenseistic-bridge' ) . $key . "\n"
			. sprintf( __( 'Widgets:     %s', 'memberistic-licenseistic-bridge' ), $fmt( $caps['max_widgets'] ) ) . "\n"
			. sprintf( __( 'Agents:      %s', 'memberistic-licenseistic-bridge' ), $fmt( $caps['max_agents'] ) ) . "\n"
			. sprintf( __( 'Domains:     %s', 'memberistic-licenseistic-bridge' ), $fmt( $caps['max_domains'] ) ) . "\n"
			. "──────────────────────────────────────\n\n"
			. __( 'Activation steps:', 'memberistic-licenseistic-bridge' ) . "\n"
			. __( '  1. Download & install the Chatbotistic Widget plugin on your WordPress site.', 'memberistic-licenseistic-bridge' ) . "\n"
			. __( '  2. Open Chatbotistic → License in wp-admin.', 'memberistic-licenseistic-bridge' ) . "\n"
			. __( '  3. Paste the license key above and click Activate.', 'memberistic-licenseistic-bridge' ) . "\n"
			. __( '  4. Your plan limits unlock automatically — start adding widgets and agents.', 'memberistic-licenseistic-bridge' ) . "\n\n"
			. __( 'Download the widget plugin: ', 'memberistic-licenseistic-bridge' ) . $download_url . "\n"
			. __( 'Your account & license:     ', 'memberistic-licenseistic-bridge' ) . $account_url . "\n"
			. __( 'Portal login:               ', 'memberistic-licenseistic-bridge' ) . $login_url . "\n"
			. __( 'Set / reset password:       ', 'memberistic-licenseistic-bridge' ) . $reset_url . "\n\n"
			. sprintf( __( '— The %s team', 'memberistic-licenseistic-bridge' ), $site );

		wp_mail( $user->user_email, $subject, $body );
	}

	/**
	 * Read a Memberistic membership row by ID.
	 *
	 * @param int $membership_id Membership ID.
	 * @return array|null
	 */
	private function fetch_membership( int $membership_id ): ?array {
		$repo = '\WordPressistic\Memberistic\Database\Memberships_Repository';
		if ( ! class_exists( $repo ) || ! method_exists( $repo, 'get' ) ) {
			return null;
		}
		$row = $repo::get( $membership_id );
		return is_array( $row ) ? $row : null;
	}
}
