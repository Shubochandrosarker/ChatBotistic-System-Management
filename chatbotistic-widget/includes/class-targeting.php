<?php
namespace Chatbotistic_Widget;

defined( 'ABSPATH' ) || exit;

/**
 * Targeting rules — pairs widget keys with posts/pages or URL patterns.
 *
 * Stored in two options as flat arrays so they're easy to render in <table> form:
 *   cbw_widget_keys_by_post = [ [widget_key=>..., post_id=>...], ... ]
 *   cbw_widget_keys_by_url  = [ [widget_key=>..., url=>...],     ... ]
 */
final class Targeting {

	const OPT_BY_POST = 'cbw_widget_keys_by_post';
	const OPT_BY_URL  = 'cbw_widget_keys_by_url';
	const OPT_DEFAULT = 'cbw_default_widget_key';

	public static function default_key(): string {
		return (string) get_option( self::OPT_DEFAULT, '' );
	}

	public static function by_post(): array {
		$rows = get_option( self::OPT_BY_POST, [] );
		return is_array( $rows ) ? $rows : [];
	}

	public static function by_url(): array {
		$rows = get_option( self::OPT_BY_URL, [] );
		return is_array( $rows ) ? $rows : [];
	}

	public static function save_default( string $key ): void {
		update_option( self::OPT_DEFAULT, sanitize_text_field( $key ) );
	}

	public static function save_by_post( array $rows ): void {
		update_option( self::OPT_BY_POST, self::sanitize_rows( $rows, 'post_id' ) );
	}

	public static function save_by_url( array $rows ): void {
		update_option( self::OPT_BY_URL, self::sanitize_rows( $rows, 'url' ) );
	}

	private static function sanitize_rows( array $rows, string $value_key ): array {
		$out = [];
		foreach ( $rows as $row ) {
			$key   = trim( sanitize_text_field( $row['widget_key'] ?? '' ) );
			$value = $row[ $value_key ] ?? '';
			if ( 'post_id' === $value_key ) {
				$value = (int) $value;
				if ( ! $key || $value <= 0 ) continue;
			} else {
				$value = esc_url_raw( $value );
				if ( ! $key || ! $value ) continue;
			}
			$out[] = [ 'widget_key' => $key, $value_key => $value ];
		}
		return $out;
	}

	/**
	 * Distinct widget keys currently configured (default + posts + urls).
	 */
	public static function configured_keys(): array {
		$keys = [];
		$def  = self::default_key();
		if ( $def ) $keys[] = $def;
		foreach ( self::by_post() as $row ) { if ( ! empty( $row['widget_key'] ) ) $keys[] = $row['widget_key']; }
		foreach ( self::by_url()  as $row ) { if ( ! empty( $row['widget_key'] ) ) $keys[] = $row['widget_key']; }
		return array_values( array_unique( array_filter( $keys ) ) );
	}

	/**
	 * Resolve which widget key (if any) should render on the current request.
	 * Order: post-match → URL-match → default.
	 */
	public static function resolve_for_current_request(): string {
		$post_id = get_queried_object_id();
		if ( $post_id ) {
			foreach ( self::by_post() as $row ) {
				if ( (int) ( $row['post_id'] ?? 0 ) === (int) $post_id ) {
					return (string) $row['widget_key'];
				}
			}
		}

		$current = self::current_url();
		foreach ( self::by_url() as $row ) {
			$pattern = (string) ( $row['url'] ?? '' );
			if ( $pattern && self::url_matches( $pattern, $current ) ) {
				return (string) $row['widget_key'];
			}
		}

		return self::default_key();
	}

	private static function current_url(): string {
		$scheme = is_ssl() ? 'https' : 'http';
		$host   = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : wp_parse_url( home_url(), PHP_URL_HOST );
		$path   = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
		return $scheme . '://' . $host . $path;
	}

	/** Wildcard glob: '*' matches any character sequence. */
	private static function url_matches( string $pattern, string $url ): bool {
		$regex = '#^' . str_replace( '\*', '.*', preg_quote( $pattern, '#' ) ) . '$#i';
		return (bool) preg_match( $regex, $url );
	}
}
