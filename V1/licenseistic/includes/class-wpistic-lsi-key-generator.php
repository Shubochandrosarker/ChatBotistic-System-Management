<?php
/**
 * License key generator.
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPistic_LSI_Key_Generator
 */
class WPistic_LSI_Key_Generator {

	const DEFAULT_CHARSET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

	/**
	 * Returns sanitized generator settings.
	 *
	 * @param array $settings Raw settings.
	 * @return array
	 */
	public static function validate_generator_settings( $settings ) {
		$settings = is_array( $settings ) ? $settings : array();
		return array(
			'prefix'         => isset( $settings['prefix'] ) ? sanitize_text_field( $settings['prefix'] ) : 'LSI',
			'suffix'         => isset( $settings['suffix'] ) ? sanitize_text_field( $settings['suffix'] ) : '',
			'segment_length' => isset( $settings['segment_length'] ) ? max( 2, min( 32, (int) $settings['segment_length'] ) ) : 5,
			'segment_count'  => isset( $settings['segment_count'] ) ? max( 1, min( 12, (int) $settings['segment_count'] ) ) : 4,
			'separator'      => isset( $settings['separator'] ) ? substr( sanitize_text_field( $settings['separator'] ), 0, 5 ) : '-',
			'charset'        => isset( $settings['charset'] ) && '' !== $settings['charset']
				? preg_replace( '/[^a-zA-Z0-9]/', '', $settings['charset'] )
				: self::DEFAULT_CHARSET,
		);
	}

	/**
	 * Generates a single license key string.
	 *
	 * @param array $settings Generator settings.
	 * @return string
	 */
	public static function generate_key( $settings = array() ) {
		$settings = self::validate_generator_settings( $settings );

		$charset    = $settings['charset'] ? $settings['charset'] : self::DEFAULT_CHARSET;
		$charset_len = strlen( $charset );

		$segments = array();
		for ( $i = 0; $i < $settings['segment_count']; $i++ ) {
			$segment = '';
			for ( $j = 0; $j < $settings['segment_length']; $j++ ) {
				$segment .= $charset[ random_int( 0, $charset_len - 1 ) ];
			}
			$segments[] = $segment;
		}

		$key = implode( $settings['separator'], $segments );
		if ( $settings['prefix'] ) {
			$key = $settings['prefix'] . $settings['separator'] . $key;
		}
		if ( $settings['suffix'] ) {
			$key .= $settings['separator'] . $settings['suffix'];
		}

		return apply_filters( 'wpistic_lsi_generated_license_key', $key, $settings );
	}

	/**
	 * Generates a unique key not already present in the licenses table.
	 *
	 * @param array $settings Generator settings.
	 * @param int   $max_tries Maximum collision retries.
	 * @return string
	 */
	public static function generate_unique_key( $settings = array(), $max_tries = 10 ) {
		global $wpdb;
		$table = wpistic_lsi_table( 'licenses' );

		for ( $i = 0; $i < $max_tries; $i++ ) {
			$key  = self::generate_key( $settings );
			$hash = WPistic_LSI_Crypto::hash_key( $key );
			$exists = $wpdb->get_var( $wpdb->prepare( // phpcs:ignore WordPress.DB
				"SELECT license_id FROM {$table} WHERE license_key_hash = %s",
				$hash
			) );
			if ( ! $exists ) {
				return $key;
			}
		}

		// Fallback: append a random suffix.
		return self::generate_key( $settings ) . '-' . strtoupper( wp_generate_password( 6, false ) );
	}

	/**
	 * Generates a batch of unique license keys.
	 *
	 * @param int   $quantity Quantity.
	 * @param array $settings Generator settings.
	 * @return array Array of strings.
	 */
	public static function generate_bulk_keys( $quantity, $settings = array() ) {
		$quantity = max( 1, min( 5000, (int) $quantity ) );
		$keys     = array();
		for ( $i = 0; $i < $quantity; $i++ ) {
			$keys[] = self::generate_unique_key( $settings );
		}
		return $keys;
	}

	/**
	 * Loads a generator row by id.
	 *
	 * @param int $generator_id Generator ID.
	 * @return array|null
	 */
	public static function get_generator( $generator_id ) {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( // phpcs:ignore WordPress.DB
			'SELECT * FROM ' . wpistic_lsi_table( 'generators' ) . ' WHERE generator_id = %d',
			(int) $generator_id
		), ARRAY_A );
		return $row ? $row : null;
	}
}
