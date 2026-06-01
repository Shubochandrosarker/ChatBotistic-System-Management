<?php
/**
 * Encryption helper class for Insightistic Pro.
 *
 * @package Insightistic_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Insightistic_Encryption
 * Handles AES-256-CBC encryption/decryption of sensitive API keys.
 */
class Insightistic_Encryption {

	/**
	 * Encrypt a string.
	 *
	 * @param string $data Plain text to encrypt.
	 * @return string|false Base64-encoded cipher text, or false on failure.
	 */
	public static function encrypt( $data ) {
		if ( empty( $data ) ) {
			return false;
		}

		$key    = wp_salt( 'auth' );
		$cipher = 'AES-256-CBC';
		$iv_len = openssl_cipher_iv_length( $cipher );
		$iv     = openssl_random_pseudo_bytes( $iv_len );

		$encrypted = openssl_encrypt( $data, $cipher, $key, 0, $iv );
		if ( false === $encrypted ) {
			return false;
		}

		return base64_encode( $encrypted . '::' . $iv ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Decrypt a string.
	 *
	 * @param string $data Base64-encoded cipher text.
	 * @return string|false Plain text, or false on failure.
	 */
	public static function decrypt( $data ) {
		if ( empty( $data ) ) {
			return false;
		}

		$decoded = base64_decode( $data, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		if ( false === $decoded || strpos( $decoded, '::' ) === false ) {
			return false;
		}

		list( $encrypted_data, $iv ) = explode( '::', $decoded, 2 );

		$key    = wp_salt( 'auth' );
		$cipher = 'AES-256-CBC';

		return openssl_decrypt( $encrypted_data, $cipher, $key, 0, $iv );
	}

	/**
	 * Check whether a stored value looks encrypted.
	 *
	 * @param string $value Stored value.
	 * @return bool
	 */
	public static function is_encrypted( $value ) {
		if ( empty( $value ) ) {
			return false;
		}
		$decoded = base64_decode( $value, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		return ( false !== $decoded && strpos( $decoded, '::' ) !== false );
	}
}
