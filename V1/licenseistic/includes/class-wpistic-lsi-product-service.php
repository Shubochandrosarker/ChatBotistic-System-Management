<?php
/**
 * Product service.
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPistic_LSI_Product_Service
 */
class WPistic_LSI_Product_Service {

	/**
	 * Creates a product row.
	 *
	 * @param array $data Product data.
	 * @return int|WP_Error
	 */
	public static function create_product( $data ) {
		if ( empty( $data['product_name'] ) ) {
			return new WP_Error( 'missing_name', __( 'Product name is required.', 'licenseistic' ) );
		}

		$slug = ! empty( $data['product_slug'] ) ? sanitize_title( $data['product_slug'] ) : sanitize_title( $data['product_name'] );

		$row = array(
			'product_name'             => sanitize_text_field( $data['product_name'] ),
			'product_slug'             => $slug,
			'product_type'             => isset( $data['product_type'] ) ? sanitize_key( $data['product_type'] ) : 'software',
			'product_version'          => isset( $data['product_version'] ) ? sanitize_text_field( $data['product_version'] ) : null,
			'download_url'             => isset( $data['download_url'] ) ? esc_url_raw( $data['download_url'] ) : null,
			'changelog'                => isset( $data['changelog'] ) ? wp_kses_post( $data['changelog'] ) : null,
			'status'                   => isset( $data['status'] ) ? sanitize_key( $data['status'] ) : 'active',
			'default_activation_limit' => isset( $data['default_activation_limit'] ) ? (int) $data['default_activation_limit'] : 1,
			'default_expiry_days'      => isset( $data['default_expiry_days'] ) ? (int) $data['default_expiry_days'] : null,
			'generator_id'             => isset( $data['generator_id'] ) ? (int) $data['generator_id'] : null,
			'created_at'               => wpistic_lsi_now(),
		);

		$id = WPistic_LSI_DB::insert( 'products', $row );
		if ( ! $id ) {
			return new WP_Error( 'db_error', __( 'Could not save product.', 'licenseistic' ) );
		}

		WPistic_LSI_Logger::log( 'product_created', sprintf( 'Product #%d created', $id ), array(
			'object_type' => 'product',
			'object_id'   => $id,
		) );

		return $id;
	}

	/**
	 * Returns a product row.
	 *
	 * @param int $product_id Product ID.
	 * @return array|null
	 */
	public static function get_product( $product_id ) {
		return WPistic_LSI_DB::get_row( 'products', array( 'product_id' => (int) $product_id ) );
	}

	/**
	 * Returns a product by slug.
	 *
	 * @param string $slug Product slug.
	 * @return array|null
	 */
	public static function get_product_by_slug( $slug ) {
		return WPistic_LSI_DB::get_row( 'products', array( 'product_slug' => sanitize_title( $slug ) ) );
	}

	/**
	 * Lists products.
	 *
	 * @param array $args Args.
	 * @return array
	 */
	public static function get_products( $args = array() ) {
		global $wpdb;
		$defaults = array(
			'status'   => '',
			'per_page' => 50,
			'offset'   => 0,
			'search'   => '',
		);
		$args = array_merge( $defaults, $args );

		$where  = array( '1=1' );
		$values = array();
		if ( $args['status'] ) {
			$where[]  = 'status = %s';
			$values[] = $args['status'];
		}
		if ( $args['search'] ) {
			$where[]  = '(product_name LIKE %s OR product_slug LIKE %s)';
			$values[] = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$values[] = '%' . $wpdb->esc_like( $args['search'] ) . '%';
		}

		$sql = 'SELECT * FROM ' . wpistic_lsi_table( 'products' ) . ' WHERE ' . implode( ' AND ', $where ) .
			' ORDER BY product_id DESC LIMIT %d OFFSET %d';
		$values[] = (int) $args['per_page'];
		$values[] = (int) $args['offset'];

		return $wpdb->get_results( $wpdb->prepare( $sql, $values ), ARRAY_A ); // phpcs:ignore WordPress.DB
	}

	/**
	 * Updates a product.
	 *
	 * @param int   $product_id Product ID.
	 * @param array $data       Update payload.
	 * @return bool
	 */
	public static function update_product( $product_id, $data ) {
		$allowed = array( 'product_name', 'product_slug', 'product_type', 'product_version', 'download_url', 'changelog', 'status', 'default_activation_limit', 'default_expiry_days', 'generator_id' );
		$update  = array();

		foreach ( $allowed as $field ) {
			if ( ! array_key_exists( $field, $data ) ) {
				continue;
			}
			switch ( $field ) {
				case 'product_name':
				case 'product_version':
					$update[ $field ] = sanitize_text_field( $data[ $field ] );
					break;
				case 'product_slug':
					$update[ $field ] = sanitize_title( $data[ $field ] );
					break;
				case 'download_url':
					$update[ $field ] = esc_url_raw( $data[ $field ] );
					break;
				case 'changelog':
					$update[ $field ] = wp_kses_post( $data[ $field ] );
					break;
				case 'status':
				case 'product_type':
					$update[ $field ] = sanitize_key( $data[ $field ] );
					break;
				default:
					$update[ $field ] = (int) $data[ $field ];
			}
		}

		if ( ! $update ) {
			return false;
		}

		$update['updated_at'] = wpistic_lsi_now();
		$result = WPistic_LSI_DB::update( 'products', $update, array( 'product_id' => (int) $product_id ) );
		return false !== $result;
	}

	/**
	 * Deletes a product.
	 *
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public static function delete_product( $product_id ) {
		$result = WPistic_LSI_DB::delete( 'products', array( 'product_id' => (int) $product_id ) );
		return false !== $result;
	}
}
