<?php
/**
 * Global helper functions for Licenseistic.
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns a Licenseistic table name with prefix applied.
 *
 * @param string $name Short table name (e.g. "licenses").
 * @return string Fully prefixed table name.
 */
function wpistic_lsi_table( $name ) {
	global $wpdb;
	return $wpdb->prefix . 'wpistic_lsi_' . $name;
}

/**
 * Returns the list of valid license statuses.
 *
 * @return array<string,string>
 */
function wpistic_lsi_get_statuses() {
	return array(
		'active'    => __( 'Active', 'licenseistic' ),
		'inactive'  => __( 'Inactive', 'licenseistic' ),
		'expired'   => __( 'Expired', 'licenseistic' ),
		'revoked'   => __( 'Revoked', 'licenseistic' ),
		'suspended' => __( 'Suspended', 'licenseistic' ),
		'pending'   => __( 'Pending', 'licenseistic' ),
		'disabled'  => __( 'Disabled', 'licenseistic' ),
	);
}

/**
 * Returns plugin settings array merged with defaults.
 *
 * @return array
 */
function wpistic_lsi_get_settings() {
	$defaults = array(
		'default_status'           => 'active',
		'default_activation_limit' => 1,
		'default_expiry_days'      => 365,
		'enable_customer_dashboard' => 'yes',
		'mask_keys'                => 'yes',
		'enable_rest_api'          => 'yes',
		'enable_logs'              => 'yes',
		'log_retention_days'       => 90,
		'rate_limit_per_minute'    => 60,
		'allow_same_domain_reactivation' => 'yes',
		'require_product_id'       => 'no',
	);

	$saved = get_option( 'wpistic_lsi_settings', array() );

	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	return array_merge( $defaults, $saved );
}

/**
 * Returns a single Licenseistic setting value.
 *
 * @param string $key     Setting key.
 * @param mixed  $default Default value.
 * @return mixed
 */
function wpistic_lsi_get_setting( $key, $default = '' ) {
	$settings = wpistic_lsi_get_settings();
	return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
}

/**
 * Returns the current MySQL UTC datetime.
 *
 * @return string
 */
function wpistic_lsi_now() {
	return gmdate( 'Y-m-d H:i:s' );
}

/**
 * Sanitizes a license status against the known list.
 *
 * @param string $status Status string.
 * @return string
 */
function wpistic_lsi_sanitize_status( $status ) {
	$status   = strtolower( sanitize_key( $status ) );
	$statuses = array_keys( wpistic_lsi_get_statuses() );
	return in_array( $status, $statuses, true ) ? $status : 'active';
}

/**
 * Returns the client IP address.
 *
 * @return string
 */
function wpistic_lsi_get_ip() {
	$candidates = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
	foreach ( $candidates as $key ) {
		if ( ! empty( $_SERVER[ $key ] ) ) {
			$ip = is_array( $_SERVER[ $key ] ) ? reset( $_SERVER[ $key ] ) : $_SERVER[ $key ]; // phpcs:ignore
			$ip = sanitize_text_field( wp_unslash( $ip ) );
			$ip = trim( explode( ',', $ip )[0] );
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}
	}
	return '';
}

/**
 * Returns a structured success response payload.
 *
 * @param string $message Human readable message.
 * @param array  $data    Data payload.
 * @return array
 */
function wpistic_lsi_response_success( $message, $data = array() ) {
	return array(
		'success' => true,
		'message' => $message,
		'data'    => $data,
	);
}

/**
 * Returns a structured error response payload.
 *
 * @param string $message Error message.
 * @param string $code    Error code.
 * @param array  $data    Data payload.
 * @return array
 */
function wpistic_lsi_response_error( $message, $code = 'error', $data = array() ) {
	return array(
		'success' => false,
		'message' => $message,
		'code'    => $code,
		'data'    => $data,
	);
}

/**
 * Compares a date string to "now" and returns true if the date is in the past.
 *
 * @param string|null $date MySQL datetime.
 * @return bool
 */
function wpistic_lsi_is_past( $date ) {
	if ( empty( $date ) ) {
		return false;
	}
	return strtotime( $date . ' UTC' ) < time();
}
