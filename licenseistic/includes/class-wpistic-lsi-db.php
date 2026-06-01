<?php
/**
 * Database installer / schema.
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPistic_LSI_DB
 *
 * Lightweight repository helpers for accessing Licenseistic tables.
 */
class WPistic_LSI_DB {

	/**
	 * Returns a table name with prefix.
	 *
	 * @param string $name Short table name.
	 * @return string
	 */
	public static function table( $name ) {
		return wpistic_lsi_table( $name );
	}

	/**
	 * Inserts a row and returns the insert id.
	 *
	 * @param string $table  Short table name.
	 * @param array  $data   Row data.
	 * @param array  $format wpdb format array.
	 * @return int|false
	 */
	public static function insert( $table, $data, $format = null ) {
		global $wpdb;
		$result = $wpdb->insert( self::table( $table ), $data, $format ); // phpcs:ignore WordPress.DB
		return $result ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Updates rows.
	 *
	 * @param string $table        Short table name.
	 * @param array  $data         Update data.
	 * @param array  $where        Where conditions.
	 * @param array  $data_format  Data formats.
	 * @param array  $where_format Where formats.
	 * @return int|false
	 */
	public static function update( $table, $data, $where, $data_format = null, $where_format = null ) {
		global $wpdb;
		return $wpdb->update( self::table( $table ), $data, $where, $data_format, $where_format ); // phpcs:ignore WordPress.DB
	}

	/**
	 * Deletes rows.
	 *
	 * @param string $table        Short table name.
	 * @param array  $where        Where conditions.
	 * @param array  $where_format Where formats.
	 * @return int|false
	 */
	public static function delete( $table, $where, $where_format = null ) {
		global $wpdb;
		return $wpdb->delete( self::table( $table ), $where, $where_format ); // phpcs:ignore WordPress.DB
	}

	/**
	 * Returns a single row matching conditions.
	 *
	 * @param string $table   Short table name.
	 * @param array  $where   Associative where conditions.
	 * @param string $output  wpdb output type.
	 * @return mixed
	 */
	public static function get_row( $table, $where, $output = ARRAY_A ) {
		global $wpdb;
		$conditions = array();
		$values     = array();
		foreach ( $where as $col => $val ) {
			$conditions[] = '`' . esc_sql( $col ) . '` = %s';
			$values[]     = $val;
		}
		$sql = 'SELECT * FROM ' . self::table( $table );
		if ( $conditions ) {
			$sql .= ' WHERE ' . implode( ' AND ', $conditions );
		}
		$sql .= ' LIMIT 1';
		return $wpdb->get_row( $wpdb->prepare( $sql, $values ), $output ); // phpcs:ignore WordPress.DB
	}

	/**
	 * Counts rows by status column.
	 *
	 * @param string $table  Short table name.
	 * @param string $status Status filter (or empty for all).
	 * @return int
	 */
	public static function count( $table, $status = '' ) {
		global $wpdb;
		$sql = 'SELECT COUNT(*) FROM ' . self::table( $table );
		if ( $status ) {
			return (int) $wpdb->get_var( $wpdb->prepare( $sql . ' WHERE status = %s', $status ) ); // phpcs:ignore WordPress.DB
		}
		return (int) $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB
	}
}
