<?php
/**
 * Crypto helpers: hashing, encryption, masking.
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPistic_LSI_Crypto
 */
class WPistic_LSI_Crypto {

	const CIPHER = 'aes-256-cbc';

	/**
	 * Returns the plugin secret key, creating one if missing.
	 *
	 * @return string
	 */
	public static function get_secret() {
		$secret = get_option( 'wpistic_lsi_secret_key' );
		if ( empty( $secret ) ) {
			$secret = WPistic_LSI_Install::ensure_secret_key();
		}
		return $secret;
	}

	/**
	 * Returns the HMAC hash used for license lookup.
	 *
	 * @param string $license_key Plain license key.
	 * @return string
	 */
	public static function hash_key( $license_key ) {
		return hash_hmac( 'sha256', $license_key, wp_salt( 'auth' ) );
	}

	/**
	 * Encrypts a value using OpenSSL when available.
	 *
	 * @param string $value Plaintext.
	 * @return string Base64 encoded ciphertext.
	 */
	public static function encrypt( $value ) {
		if ( '' === $value ) {
			return '';
		}

		if ( ! function_exists( 'openssl_encrypt' ) ) {
			return base64_encode( $value ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
		}

		$key   = hash( 'sha256', self::get_secret(), true );
		$iv    = random_bytes( openssl_cipher_iv_length( self::CIPHER ) );
		$cipher = openssl_encrypt( $value, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv );

		if ( false === $cipher ) {
			return '';
		}

		return base64_encode( $iv . $cipher ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
	}

	/**
	 * Decrypts a previously encrypted value.
	 *
	 * @param string $value Base64 ciphertext.
	 * @return string
	 */
	public static function decrypt( $value ) {
		if ( '' === $value || null === $value ) {
			return '';
		}

		$decoded = base64_decode( $value, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
		if ( false === $decoded ) {
			return '';
		}

		if ( ! function_exists( 'openssl_decrypt' ) ) {
			return $decoded;
		}

		$key    = hash( 'sha256', self::get_secret(), true );
		$iv_len = openssl_cipher_iv_length( self::CIPHER );

		if ( strlen( $decoded ) <= $iv_len ) {
			return '';
		}

		$iv      = substr( $decoded, 0, $iv_len );
		$cipher  = substr( $decoded, $iv_len );
		$decrypt = openssl_decrypt( $cipher, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv );

		return false === $decrypt ? '' : $decrypt;
	}

	/**
	 * Masks a license key, keeping prefix and suffix segments.
	 *
	 * @param string $license_key Plain key.
	 * @return string
	 */
	public static function mask( $license_key ) {
		if ( '' === $license_key ) {
			return '';
		}

		if ( strpos( $license_key, '-' ) !== false ) {
			$parts = explode( '-', $license_key );
			$count = count( $parts );
			if ( $count <= 2 ) {
				return $license_key;
			}
			for ( $i = 1; $i < $count - 1; $i++ ) {
				$parts[ $i ] = str_repeat( '*', max( 4, strlen( $parts[ $i ] ) ) );
			}
			return implode( '-', $parts );
		}

		$len = strlen( $license_key );
		if ( $len <= 8 ) {
			return $license_key;
		}
		return substr( $license_key, 0, 4 ) . str_repeat( '*', $len - 8 ) . substr( $license_key, -4 );
	}

	/**
	 * Generates a hashed verifier for API secrets.
	 *
	 * @param string $secret Plain secret.
	 * @return string
	 */
	public static function hash_secret( $secret ) {
		return wp_hash_password( $secret );
	}

	/**
	 * Verifies a plain secret against a stored hash.
	 *
	 * @param string $secret Plain secret.
	 * @param string $hash   Stored hash.
	 * @return bool
	 */
	public static function verify_secret( $secret, $hash ) {
		return wp_check_password( $secret, $hash );
	}
}
