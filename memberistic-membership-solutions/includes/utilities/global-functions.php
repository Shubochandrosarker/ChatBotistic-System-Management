<?php
/**
 * Global helper wrappers.
 *
 * @package Memberistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'memberistic_get_setting' ) ) {
	/**
	 * Get a Memberistic setting.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Default value.
	 */
	function memberistic_get_setting( $key, $default = null ) {
		return WordPressistic\Memberistic\memberistic_get_setting( $key, $default );
	}
}

if ( ! function_exists( 'memberistic_get_brand_label' ) ) {
	/**
	 * Get the filtered Memberistic brand label.
	 */
	function memberistic_get_brand_label() {
		return WordPressistic\Memberistic\memberistic_get_brand_label();
	}
}

if ( ! function_exists( 'memberistic_get_member_id_prefix' ) ) {
	function memberistic_get_member_id_prefix() {
		return WordPressistic\Memberistic\memberistic_get_member_id_prefix();
	}
}

if ( ! function_exists( 'memberistic_get_login_tagline' ) ) {
	function memberistic_get_login_tagline() {
		return WordPressistic\Memberistic\memberistic_get_login_tagline();
	}
}

if ( ! function_exists( 'memberistic_get_login_cta_note' ) ) {
	function memberistic_get_login_cta_note() {
		return WordPressistic\Memberistic\memberistic_get_login_cta_note();
	}
}

if ( ! function_exists( 'memberistic_get_qr_verification_label' ) ) {
	function memberistic_get_qr_verification_label() {
		return WordPressistic\Memberistic\memberistic_get_qr_verification_label();
	}
}

if ( ! function_exists( 'memberistic_account_show_lane_tools' ) ) {
	function memberistic_account_show_lane_tools() {
		return WordPressistic\Memberistic\memberistic_account_show_lane_tools();
	}
}
