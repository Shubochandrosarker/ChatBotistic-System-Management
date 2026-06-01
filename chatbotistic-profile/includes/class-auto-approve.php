<?php
/**
 * Auto-approve memberships on payment.
 * Auto-activate Free-plan memberships at the moment of signup (no payment).
 *
 * @package Chatbotistic\Profile
 */

namespace Chatbotistic\Profile;

defined( 'ABSPATH' ) || exit;

class Auto_Approve {

	public static function register(): void {
		add_action( 'memberistic_membership_payment_recorded', [ __CLASS__, 'on_payment' ], 5, 3 );
		add_action( 'memberistic_membership_created',          [ __CLASS__, 'on_created' ], 5, 1 );
	}

	/**
	 * Any successful payment → flip the membership to active. The Bridge
	 * already hooks `memberistic_membership_activated` to issue a license.
	 *
	 * @param int    $membership_id Membership ID.
	 * @param int    $payment_id    Payment ID.
	 * @param string $gateway       Gateway slug.
	 */
	public static function on_payment( int $membership_id, int $payment_id, string $gateway ): void {
		$repo = '\WordPressistic\Memberistic\Database\Memberships_Repository';
		if ( ! class_exists( $repo ) ) {
			return;
		}
		$row = $repo::get( $membership_id );
		if ( ! is_array( $row ) ) {
			return;
		}
		if ( 'active' === ( $row['status'] ?? '' ) ) {
			return;
		}
		$repo::change_status( $membership_id, 'active' );
		do_action( 'memberistic_membership_activated', $membership_id );
	}

	/**
	 * Membership row created — if it's on the free plan, activate immediately
	 * since there's no payment to wait for.
	 *
	 * @param int $membership_id Membership ID.
	 */
	public static function on_created( int $membership_id ): void {
		$mem_repo  = '\WordPressistic\Memberistic\Database\Memberships_Repository';
		$plan_repo = '\WordPressistic\Memberistic\Database\Plans_Repository';
		if ( ! class_exists( $mem_repo ) || ! class_exists( $plan_repo ) ) {
			return;
		}
		$row = $mem_repo::get( $membership_id );
		if ( ! is_array( $row ) ) {
			return;
		}
		$plan = $plan_repo::get( (int) ( $row['plan_id'] ?? 0 ) );
		if ( ! is_array( $plan ) ) {
			return;
		}
		$is_free = 'free' === ( $plan['slug'] ?? '' )
			|| ( 0.0 === (float) ( $plan['monthly_price'] ?? 0 ) && 0.0 === (float) ( $plan['annual_price'] ?? 0 ) );

		if ( $is_free && 'active' !== ( $row['status'] ?? '' ) ) {
			$mem_repo::change_status( $membership_id, 'active' );
			do_action( 'memberistic_membership_activated', $membership_id );
		}
	}
}
