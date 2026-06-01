<?php
/**
 * License service: CRUD + verification + activation.
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPistic_LSI_License_Service
 */
class WPistic_LSI_License_Service {

	/**
	 * Creates a license row.
	 *
	 * @param array $data {
	 *     License attributes.
	 *
	 *     @type string $license_key      Optional. Plain key. If omitted one is generated.
	 *     @type int    $product_id       Optional. Product ID.
	 *     @type int    $customer_id      Optional. Customer user ID.
	 *     @type string $customer_email   Optional. Customer email.
	 *     @type int    $order_id         Optional. External order ID.
	 *     @type string $source           Optional. Source label.
	 *     @type string $status           Optional. License status.
	 *     @type int    $activation_limit Optional. Activation limit.
	 *     @type string $expires_at       Optional. MySQL datetime UTC.
	 *     @type string $starts_at        Optional. MySQL datetime UTC.
	 *     @type string $notes            Optional. Free-form notes.
	 *     @type int    $generator_id     Optional. Generator to use when creating a key.
	 * }
	 * @return array|WP_Error Array with license_id and plain license_key on success.
	 */
	public static function create_license( $data = array() ) {
		$plain_key = isset( $data['license_key'] ) && '' !== $data['license_key']
			? sanitize_text_field( $data['license_key'] )
			: '';

		if ( '' === $plain_key ) {
			$generator_settings = array();
			if ( ! empty( $data['generator_id'] ) ) {
				$gen = WPistic_LSI_Key_Generator::get_generator( (int) $data['generator_id'] );
				if ( $gen ) {
					$generator_settings = $gen;
				}
			}
			$plain_key = WPistic_LSI_Key_Generator::generate_unique_key( $generator_settings );
		}

		$hash = WPistic_LSI_Crypto::hash_key( $plain_key );
		if ( self::get_license_by_hash( $hash ) ) {
			return new WP_Error( 'duplicate_key', __( 'A license with this key already exists.', 'licenseistic' ) );
		}

		$now    = wpistic_lsi_now();
		$status = isset( $data['status'] ) ? wpistic_lsi_sanitize_status( $data['status'] ) : wpistic_lsi_get_setting( 'default_status', 'active' );

		$row = array(
			'license_key_hash'      => $hash,
			'license_key_encrypted' => WPistic_LSI_Crypto::encrypt( $plain_key ),
			'license_label'         => isset( $data['license_label'] ) ? sanitize_text_field( $data['license_label'] ) : null,
			'product_id'            => isset( $data['product_id'] ) ? (int) $data['product_id'] : null,
			'customer_id'           => isset( $data['customer_id'] ) ? (int) $data['customer_id'] : null,
			'customer_email'        => isset( $data['customer_email'] ) ? sanitize_email( $data['customer_email'] ) : null,
			'order_id'              => isset( $data['order_id'] ) ? (int) $data['order_id'] : null,
			'source'                => isset( $data['source'] ) ? sanitize_key( $data['source'] ) : 'manual',
			'status'                => $status,
			'activation_limit'      => isset( $data['activation_limit'] ) ? (int) $data['activation_limit'] : (int) wpistic_lsi_get_setting( 'default_activation_limit', 1 ),
			'activation_count'      => 0,
			'usage_limit'           => isset( $data['usage_limit'] ) ? (int) $data['usage_limit'] : null,
			'usage_count'           => 0,
			'expires_at'            => isset( $data['expires_at'] ) && $data['expires_at'] ? gmdate( 'Y-m-d H:i:s', strtotime( $data['expires_at'] ) ) : null,
			'starts_at'             => isset( $data['starts_at'] ) && $data['starts_at'] ? gmdate( 'Y-m-d H:i:s', strtotime( $data['starts_at'] ) ) : null,
			'created_by'            => get_current_user_id(),
			'created_at'            => $now,
			'notes'                 => isset( $data['notes'] ) ? wp_kses_post( $data['notes'] ) : null,
		);

		$id = WPistic_LSI_DB::insert( 'licenses', $row );
		if ( ! $id ) {
			return new WP_Error( 'db_error', __( 'Could not create license.', 'licenseistic' ) );
		}

		WPistic_LSI_Logger::log( 'license_created', sprintf( 'License #%d created', $id ), array(
			'object_type' => 'license',
			'object_id'   => $id,
		) );

		do_action( 'wpistic_lsi_license_created', $id, array_merge( $row, array( 'license_id' => $id, 'plain_key' => $plain_key ) ) );

		return array(
			'license_id'  => $id,
			'license_key' => $plain_key,
		);
	}

	/**
	 * Returns a license row by ID.
	 *
	 * @param int $license_id License ID.
	 * @return array|null
	 */
	public static function get_license( $license_id ) {
		return WPistic_LSI_DB::get_row( 'licenses', array( 'license_id' => (int) $license_id ) );
	}

	/**
	 * Returns a license row by plain key.
	 *
	 * @param string $license_key Plain license key.
	 * @return array|null
	 */
	public static function get_license_by_key( $license_key ) {
		$hash = WPistic_LSI_Crypto::hash_key( $license_key );
		return self::get_license_by_hash( $hash );
	}

	/**
	 * Returns a license row by hash.
	 *
	 * @param string $hash Hash.
	 * @return array|null
	 */
	public static function get_license_by_hash( $hash ) {
		return WPistic_LSI_DB::get_row( 'licenses', array( 'license_key_hash' => $hash ) );
	}

	/**
	 * Updates a license row.
	 *
	 * @param int   $license_id License ID.
	 * @param array $data       Update payload.
	 * @return bool
	 */
	public static function update_license( $license_id, $data ) {
		$allowed = array(
			'license_label', 'product_id', 'customer_id', 'customer_email', 'order_id',
			'source', 'status', 'activation_limit', 'usage_limit', 'expires_at', 'starts_at', 'notes',
		);
		$update  = array();

		foreach ( $allowed as $field ) {
			if ( ! array_key_exists( $field, $data ) ) {
				continue;
			}
			switch ( $field ) {
				case 'license_label':
					$update[ $field ] = sanitize_text_field( $data[ $field ] );
					break;
				case 'customer_email':
					$update[ $field ] = sanitize_email( $data[ $field ] );
					break;
				case 'source':
					$update[ $field ] = sanitize_key( $data[ $field ] );
					break;
				case 'status':
					$update[ $field ] = wpistic_lsi_sanitize_status( $data[ $field ] );
					break;
				case 'expires_at':
				case 'starts_at':
					$update[ $field ] = $data[ $field ] ? gmdate( 'Y-m-d H:i:s', strtotime( $data[ $field ] ) ) : null;
					break;
				case 'notes':
					$update[ $field ] = wp_kses_post( $data[ $field ] );
					break;
				default:
					$update[ $field ] = null === $data[ $field ] ? null : (int) $data[ $field ];
			}
		}

		if ( ! $update ) {
			return false;
		}

		$update['updated_at'] = wpistic_lsi_now();
		$result = WPistic_LSI_DB::update( 'licenses', $update, array( 'license_id' => (int) $license_id ) );
		if ( false === $result ) {
			return false;
		}

		WPistic_LSI_Logger::log( 'license_updated', sprintf( 'License #%d updated', $license_id ), array(
			'object_type' => 'license',
			'object_id'   => $license_id,
			'context'     => $update,
		) );

		do_action( 'wpistic_lsi_license_updated', (int) $license_id, $update );
		return true;
	}

	/**
	 * Deletes a license and its activations.
	 *
	 * @param int $license_id License ID.
	 * @return bool
	 */
	public static function delete_license( $license_id ) {
		global $wpdb;
		$license_id = (int) $license_id;
		$wpdb->delete( wpistic_lsi_table( 'activations' ), array( 'license_id' => $license_id ), array( '%d' ) ); // phpcs:ignore WordPress.DB
		$result = WPistic_LSI_DB::delete( 'licenses', array( 'license_id' => $license_id ) );
		if ( false === $result ) {
			return false;
		}
		WPistic_LSI_Logger::log( 'license_deleted', sprintf( 'License #%d deleted', $license_id ), array(
			'object_type' => 'license',
			'object_id'   => $license_id,
		) );
		do_action( 'wpistic_lsi_license_deleted', $license_id );
		return true;
	}

	/**
	 * Verifies a license against optional product/site constraints.
	 *
	 * @param string $license_key  Plain key.
	 * @param int    $product_id   Optional product ID.
	 * @param string $site_url     Optional site URL.
	 * @return array|WP_Error Sanitized license details or WP_Error on failure.
	 */
	public static function verify_license( $license_key, $product_id = 0, $site_url = '' ) {
		$license = self::get_license_by_key( $license_key );
		if ( ! $license ) {
			return new WP_Error( 'invalid_license', __( 'Invalid or unknown license key.', 'licenseistic' ) );
		}

		$status = self::resolve_status( $license );
		$status = apply_filters( 'wpistic_lsi_license_status', $status, $license );

		if ( 'active' !== $status ) {
			return new WP_Error( $status === 'expired' ? 'expired_license' : 'invalid_license',
				sprintf( __( 'License is %s.', 'licenseistic' ), $status ) );
		}

		if ( 'yes' === wpistic_lsi_get_setting( 'require_product_id', 'no' ) && ! $product_id ) {
			return new WP_Error( 'missing_product', __( 'Product ID is required.', 'licenseistic' ) );
		}

		if ( $product_id && (int) $license['product_id'] && (int) $license['product_id'] !== (int) $product_id ) {
			return new WP_Error( 'product_mismatch', __( 'License does not belong to this product.', 'licenseistic' ) );
		}

		return array(
			'license_id'       => (int) $license['license_id'],
			'product_id'       => (int) $license['product_id'],
			'status'           => $status,
			'activation_limit' => (int) $license['activation_limit'],
			'activation_count' => (int) $license['activation_count'],
			'expires_at'       => $license['expires_at'],
		);
	}

	/**
	 * Activates a license against a site/instance.
	 *
	 * @param string $license_key Plain key.
	 * @param int    $product_id  Optional product ID.
	 * @param string $site_url    Site URL.
	 * @param string $instance_id Optional instance id.
	 * @return array|WP_Error
	 */
	public static function activate_license( $license_key, $product_id = 0, $site_url = '', $instance_id = '' ) {
		$verify = self::verify_license( $license_key, $product_id, $site_url );
		if ( is_wp_error( $verify ) ) {
			return $verify;
		}

		$license_id = $verify['license_id'];
		$license    = self::get_license( $license_id );

		global $wpdb;

		// Reactivation of existing entry for the same site/instance.
		$existing = self::find_activation( $license_id, $site_url, $instance_id );
		if ( $existing ) {
			if ( 'active' === $existing['status'] ) {
				return array(
					'activation_id' => (int) $existing['activation_id'],
					'license_id'    => $license_id,
					'site_url'      => $existing['site_url'],
					'activated_at'  => $existing['activated_at'],
					'reactivated'   => false,
				);
			}

			if ( 'yes' === wpistic_lsi_get_setting( 'allow_same_domain_reactivation', 'yes' ) ) {
				WPistic_LSI_DB::update( 'activations', array(
					'status'         => 'active',
					'activated_at'   => wpistic_lsi_now(),
					'deactivated_at' => null,
				), array( 'activation_id' => (int) $existing['activation_id'] ) );

				$wpdb->query( $wpdb->prepare( // phpcs:ignore WordPress.DB
					'UPDATE ' . wpistic_lsi_table( 'licenses' ) . ' SET activation_count = activation_count + 1 WHERE license_id = %d',
					$license_id
				) );

				do_action( 'wpistic_lsi_license_activated', $license_id, (int) $existing['activation_id'] );
				return array(
					'activation_id' => (int) $existing['activation_id'],
					'license_id'    => $license_id,
					'site_url'      => $existing['site_url'],
					'activated_at'  => wpistic_lsi_now(),
					'reactivated'   => true,
				);
			}
		}

		$limit = (int) apply_filters( 'wpistic_lsi_activation_limit', (int) $license['activation_limit'], $license );
		if ( $limit > 0 && (int) $license['activation_count'] >= $limit ) {
			return new WP_Error( 'limit_reached', __( 'Activation limit reached.', 'licenseistic' ) );
		}

		$activation_id = WPistic_LSI_DB::insert( 'activations', array(
			'license_id'   => $license_id,
			'product_id'   => $product_id ? (int) $product_id : ( $license['product_id'] ?: null ),
			'site_url'     => $site_url ? esc_url_raw( $site_url ) : null,
			'instance_id'  => $instance_id ? sanitize_text_field( $instance_id ) : null,
			'environment'  => 'production',
			'ip_address'   => wpistic_lsi_get_ip(),
			'user_agent'   => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 500 ) : null,
			'status'       => 'active',
			'activated_at' => wpistic_lsi_now(),
		) );

		if ( ! $activation_id ) {
			return new WP_Error( 'db_error', __( 'Could not create activation.', 'licenseistic' ) );
		}

		$wpdb->query( $wpdb->prepare( // phpcs:ignore WordPress.DB
			'UPDATE ' . wpistic_lsi_table( 'licenses' ) . ' SET activation_count = activation_count + 1 WHERE license_id = %d',
			$license_id
		) );

		WPistic_LSI_Logger::log( 'license_activated', sprintf( 'License #%d activated', $license_id ), array(
			'object_type' => 'license',
			'object_id'   => $license_id,
			'context'     => array( 'site_url' => $site_url, 'instance_id' => $instance_id ),
		) );

		do_action( 'wpistic_lsi_license_activated', $license_id, $activation_id );

		return array(
			'activation_id' => (int) $activation_id,
			'license_id'    => $license_id,
			'site_url'      => $site_url,
			'activated_at'  => wpistic_lsi_now(),
			'reactivated'   => false,
		);
	}

	/**
	 * Deactivates an activation.
	 *
	 * @param string $license_key Plain key.
	 * @param string $site_url    Site URL.
	 * @param string $instance_id Optional instance id.
	 * @return array|WP_Error
	 */
	public static function deactivate_license( $license_key, $site_url = '', $instance_id = '' ) {
		$license = self::get_license_by_key( $license_key );
		if ( ! $license ) {
			return new WP_Error( 'invalid_license', __( 'Invalid license key.', 'licenseistic' ) );
		}

		$activation = self::find_activation( (int) $license['license_id'], $site_url, $instance_id, 'active' );
		if ( ! $activation ) {
			return new WP_Error( 'not_active', __( 'No active activation found for this site.', 'licenseistic' ) );
		}

		WPistic_LSI_DB::update( 'activations', array(
			'status'         => 'deactivated',
			'deactivated_at' => wpistic_lsi_now(),
		), array( 'activation_id' => (int) $activation['activation_id'] ) );

		global $wpdb;
		$wpdb->query( $wpdb->prepare( // phpcs:ignore WordPress.DB
			'UPDATE ' . wpistic_lsi_table( 'licenses' ) . ' SET activation_count = GREATEST(activation_count - 1, 0) WHERE license_id = %d',
			(int) $license['license_id']
		) );

		WPistic_LSI_Logger::log( 'license_deactivated', sprintf( 'License #%d deactivated', $license['license_id'] ), array(
			'object_type' => 'license',
			'object_id'   => (int) $license['license_id'],
			'context'     => array( 'site_url' => $site_url, 'instance_id' => $instance_id ),
		) );

		do_action( 'wpistic_lsi_license_deactivated', (int) $license['license_id'], (int) $activation['activation_id'] );

		return array(
			'license_id'   => (int) $license['license_id'],
			'activation_id' => (int) $activation['activation_id'],
		);
	}

	/**
	 * Marks a license as revoked.
	 *
	 * @param int $license_id License ID.
	 * @return bool
	 */
	public static function revoke_license( $license_id ) {
		$result = WPistic_LSI_DB::update( 'licenses', array(
			'status'     => 'revoked',
			'revoked_at' => wpistic_lsi_now(),
			'updated_at' => wpistic_lsi_now(),
		), array( 'license_id' => (int) $license_id ) );
		if ( false === $result ) {
			return false;
		}
		WPistic_LSI_Logger::log( 'license_revoked', sprintf( 'License #%d revoked', $license_id ), array(
			'object_type' => 'license',
			'object_id'   => (int) $license_id,
		) );
		do_action( 'wpistic_lsi_license_revoked', (int) $license_id );
		return true;
	}

	/**
	 * Marks a license as suspended.
	 *
	 * @param int $license_id License ID.
	 * @return bool
	 */
	public static function suspend_license( $license_id ) {
		$result = WPistic_LSI_DB::update( 'licenses', array(
			'status'       => 'suspended',
			'suspended_at' => wpistic_lsi_now(),
			'updated_at'   => wpistic_lsi_now(),
		), array( 'license_id' => (int) $license_id ) );
		if ( false === $result ) {
			return false;
		}
		WPistic_LSI_Logger::log( 'license_suspended', sprintf( 'License #%d suspended', $license_id ), array(
			'object_type' => 'license',
			'object_id'   => (int) $license_id,
		) );
		do_action( 'wpistic_lsi_license_suspended', (int) $license_id );
		return true;
	}

	/**
	 * Marks a license as expired.
	 *
	 * @param int $license_id License ID.
	 * @return bool
	 */
	public static function expire_license( $license_id ) {
		$result = WPistic_LSI_DB::update( 'licenses', array(
			'status'     => 'expired',
			'updated_at' => wpistic_lsi_now(),
		), array( 'license_id' => (int) $license_id ) );
		if ( false === $result ) {
			return false;
		}
		WPistic_LSI_Logger::log( 'license_expired', sprintf( 'License #%d expired', $license_id ), array(
			'object_type' => 'license',
			'object_id'   => (int) $license_id,
		) );
		do_action( 'wpistic_lsi_license_expired', (int) $license_id );
		return true;
	}

	/**
	 * Convenience wrapper for masking a license key.
	 *
	 * @param string $license_key Plain key.
	 * @return string
	 */
	public static function mask_license_key( $license_key ) {
		return WPistic_LSI_Crypto::mask( $license_key );
	}

	/**
	 * Resolves the effective status (auto-expiring if past expiry).
	 *
	 * @param array $license License row.
	 * @return string
	 */
	public static function resolve_status( $license ) {
		if ( empty( $license ) ) {
			return 'inactive';
		}
		$status = isset( $license['status'] ) ? $license['status'] : 'inactive';

		if ( in_array( $status, array( 'revoked', 'suspended', 'disabled', 'pending', 'inactive' ), true ) ) {
			return $status;
		}

		if ( ! empty( $license['expires_at'] ) && wpistic_lsi_is_past( $license['expires_at'] ) ) {
			if ( 'expired' !== $status ) {
				self::expire_license( (int) $license['license_id'] );
			}
			return 'expired';
		}

		if ( ! empty( $license['starts_at'] ) && strtotime( $license['starts_at'] . ' UTC' ) > time() ) {
			return 'pending';
		}

		return 'active';
	}

	/**
	 * Lists licenses with optional filters.
	 *
	 * @param array $args Args.
	 * @return array
	 */
	public static function get_licenses( $args = array() ) {
		global $wpdb;
		$defaults = array(
			'status'     => '',
			'product_id' => 0,
			'customer_id' => 0,
			'search'     => '',
			'per_page'   => 50,
			'offset'     => 0,
		);
		$args = array_merge( $defaults, $args );

		$where  = array( '1=1' );
		$values = array();

		if ( $args['status'] ) {
			$where[]  = 'status = %s';
			$values[] = $args['status'];
		}
		if ( $args['product_id'] ) {
			$where[]  = 'product_id = %d';
			$values[] = (int) $args['product_id'];
		}
		if ( $args['customer_id'] ) {
			$where[]  = 'customer_id = %d';
			$values[] = (int) $args['customer_id'];
		}
		if ( $args['search'] ) {
			$where[]  = '(license_label LIKE %s OR customer_email LIKE %s OR license_key_hash = %s)';
			$values[] = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$values[] = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$values[] = WPistic_LSI_Crypto::hash_key( $args['search'] );
		}

		$sql = 'SELECT * FROM ' . wpistic_lsi_table( 'licenses' ) . ' WHERE ' . implode( ' AND ', $where ) .
			' ORDER BY license_id DESC LIMIT %d OFFSET %d';
		$values[] = (int) $args['per_page'];
		$values[] = (int) $args['offset'];

		return $wpdb->get_results( $wpdb->prepare( $sql, $values ), ARRAY_A ); // phpcs:ignore WordPress.DB
	}

	/**
	 * Finds an existing activation for a license + site/instance combo.
	 *
	 * @param int    $license_id License ID.
	 * @param string $site_url   Site URL.
	 * @param string $instance_id Instance ID.
	 * @param string $status     Optional status filter.
	 * @return array|null
	 */
	protected static function find_activation( $license_id, $site_url, $instance_id, $status = '' ) {
		global $wpdb;
		$sql    = 'SELECT * FROM ' . wpistic_lsi_table( 'activations' ) . ' WHERE license_id = %d';
		$values = array( (int) $license_id );

		if ( $site_url ) {
			$sql      .= ' AND site_url = %s';
			$values[]  = esc_url_raw( $site_url );
		}
		if ( $instance_id ) {
			$sql      .= ' AND instance_id = %s';
			$values[]  = sanitize_text_field( $instance_id );
		}
		if ( $status ) {
			$sql      .= ' AND status = %s';
			$values[]  = $status;
		}

		$sql .= ' ORDER BY activation_id DESC LIMIT 1';

		return $wpdb->get_row( $wpdb->prepare( $sql, $values ), ARRAY_A ); // phpcs:ignore WordPress.DB
	}

	/**
	 * Pings a license: updates last_ping_at and returns status info.
	 *
	 * @param string $license_key       Plain key.
	 * @param int    $product_id        Optional product ID.
	 * @param string $site_url          Site URL.
	 * @param string $instance_id       Instance id.
	 * @param string $installed_version Installed version.
	 * @return array|WP_Error
	 */
	public static function ping_license( $license_key, $product_id = 0, $site_url = '', $instance_id = '', $installed_version = '' ) {
		$verify = self::verify_license( $license_key, $product_id, $site_url );
		if ( is_wp_error( $verify ) ) {
			return $verify;
		}

		$license_id = $verify['license_id'];
		$activation = self::find_activation( $license_id, $site_url, $instance_id, 'active' );
		if ( $activation ) {
			WPistic_LSI_DB::update( 'activations', array(
				'last_ping_at' => wpistic_lsi_now(),
			), array( 'activation_id' => (int) $activation['activation_id'] ) );
		}

		$product = null;
		if ( $verify['product_id'] ) {
			$product = WPistic_LSI_Product_Service::get_product( $verify['product_id'] );
		}

		do_action( 'wpistic_lsi_license_pinged', $license_id, $activation ? (int) $activation['activation_id'] : 0 );

		return array(
			'license_id'     => $license_id,
			'status'         => $verify['status'],
			'expires_at'     => $verify['expires_at'],
			'latest_version' => $product ? $product['product_version'] : null,
			'download_url'   => $product ? $product['download_url'] : null,
			'changelog'      => $product ? $product['changelog'] : null,
			'installed_version' => $installed_version ? sanitize_text_field( $installed_version ) : null,
		);
	}

	/**
	 * Returns the plain license key for display (decrypted).
	 *
	 * @param array $license License row.
	 * @return string
	 */
	public static function get_display_key( $license ) {
		if ( empty( $license['license_key_encrypted'] ) ) {
			return '';
		}
		$plain = WPistic_LSI_Crypto::decrypt( $license['license_key_encrypted'] );
		if ( 'yes' === wpistic_lsi_get_setting( 'mask_keys', 'yes' ) ) {
			return WPistic_LSI_Crypto::mask( $plain );
		}
		return $plain;
	}
}
