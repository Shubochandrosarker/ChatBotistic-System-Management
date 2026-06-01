<?php
/**
 * Customer service helpers (uses WordPress users).
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPistic_LSI_Customer_Service
 */
class WPistic_LSI_Customer_Service {

	/**
	 * Returns a customer record summary by user ID.
	 *
	 * @param int $user_id User ID.
	 * @return array|null
	 */
	public static function get_customer( $user_id ) {
		$user = get_user_by( 'id', (int) $user_id );
		if ( ! $user ) {
			return null;
		}
		return array(
			'id'    => $user->ID,
			'email' => $user->user_email,
			'name'  => $user->display_name,
		);
	}

	/**
	 * Returns a customer by email, creating a lightweight record if needed.
	 *
	 * @param string $email Customer email.
	 * @return array|null
	 */
	public static function get_customer_by_email( $email ) {
		$email = sanitize_email( $email );
		if ( ! $email ) {
			return null;
		}
		$user = get_user_by( 'email', $email );
		if ( $user ) {
			return self::get_customer( $user->ID );
		}
		return array(
			'id'    => 0,
			'email' => $email,
			'name'  => '',
		);
	}

	/**
	 * Returns licenses for the given user.
	 *
	 * @param int $user_id User ID.
	 * @return array
	 */
	public static function get_user_licenses( $user_id ) {
		global $wpdb;
		$user = get_user_by( 'id', (int) $user_id );
		if ( ! $user ) {
			return array();
		}
		$sql = 'SELECT * FROM ' . wpistic_lsi_table( 'licenses' ) .
			' WHERE customer_id = %d OR customer_email = %s ORDER BY license_id DESC';
		return $wpdb->get_results( $wpdb->prepare( $sql, $user->ID, $user->user_email ), ARRAY_A ); // phpcs:ignore WordPress.DB
	}
}
