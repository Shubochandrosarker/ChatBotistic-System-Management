<?php
/**
 * Event logger.
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPistic_LSI_Logger
 */
class WPistic_LSI_Logger {

	/**
	 * Records a log event.
	 *
	 * @param string $event_type   Event type slug.
	 * @param string $message      Human readable summary.
	 * @param array  $args         {
	 *     Optional. Args.
	 *
	 *     @type string $object_type Object type, e.g. "license".
	 *     @type int    $object_id   Object ID.
	 *     @type array  $context     Free-form context payload.
	 *     @type int    $user_id     Acting user ID.
	 * }
	 * @return int|false Insert id or false.
	 */
	public static function log( $event_type, $message = '', $args = array() ) {
		if ( 'yes' !== wpistic_lsi_get_setting( 'enable_logs', 'yes' ) ) {
			return false;
		}

		$defaults = array(
			'object_type' => null,
			'object_id'   => null,
			'context'     => array(),
			'user_id'     => get_current_user_id(),
		);
		$args     = array_merge( $defaults, $args );

		$data = array(
			'object_type' => $args['object_type'] ? sanitize_key( $args['object_type'] ) : null,
			'object_id'   => $args['object_id'] ? (int) $args['object_id'] : null,
			'event_type'  => sanitize_key( $event_type ),
			'message'     => $message ? wp_strip_all_tags( $message ) : null,
			'context'     => $args['context'] ? wp_json_encode( $args['context'] ) : null,
			'user_id'     => $args['user_id'] ? (int) $args['user_id'] : null,
			'ip_address'  => wpistic_lsi_get_ip(),
			'created_at'  => wpistic_lsi_now(),
		);

		return WPistic_LSI_DB::insert( 'logs', $data );
	}

	/**
	 * Removes log rows older than retention setting.
	 *
	 * @return void
	 */
	public static function prune() {
		global $wpdb;
		$days = (int) wpistic_lsi_get_setting( 'log_retention_days', 90 );
		if ( $days <= 0 ) {
			return;
		}
		$cutoff = gmdate( 'Y-m-d H:i:s', time() - ( $days * DAY_IN_SECONDS ) );
		$wpdb->query( $wpdb->prepare( // phpcs:ignore WordPress.DB
			'DELETE FROM ' . wpistic_lsi_table( 'logs' ) . ' WHERE created_at < %s',
			$cutoff
		) );
	}

	/**
	 * Returns recent log entries.
	 *
	 * @param int $limit Limit.
	 * @return array
	 */
	public static function recent( $limit = 10 ) {
		global $wpdb;
		$limit = max( 1, min( 100, (int) $limit ) );
		return $wpdb->get_results( $wpdb->prepare( // phpcs:ignore WordPress.DB
			'SELECT * FROM ' . wpistic_lsi_table( 'logs' ) . ' ORDER BY log_id DESC LIMIT %d',
			$limit
		), ARRAY_A );
	}
}
