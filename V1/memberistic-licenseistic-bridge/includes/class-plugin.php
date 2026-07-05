<?php
/**
 * Bootstrap.
 *
 * @package WordPressistic\MLB
 */

namespace WordPressistic\MLB;

defined( 'ABSPATH' ) || exit;

class Plugin {

	public function boot(): void {
		$this->ensure_product();

		( new Bridge() )->register();
		( new Rest() )->register();

		if ( is_admin() ) {
			( new Admin\Page() )->register();
		}
	}

	/**
	 * Idempotently ensure the canonical "Chatbotistic Widget" license product
	 * exists in Licenseistic and is linked to the bridge via mlb_plan_product_id.
	 *
	 * This is the Chatbotistic-specific glue, so it lives in the bridge rather
	 * than the generic Licenseistic core. Safe to call on every boot: it only
	 * creates the product when it is genuinely missing.
	 *
	 * @return int Linked product ID (0 if Licenseistic is unavailable).
	 */
	public function ensure_product(): int {
		if ( ! class_exists( '\WPistic_LSI_Product_Service' ) ) {
			return 0;
		}

		$slug       = 'chatbotistic-widget';
		$product_id = (int) get_option( 'mlb_plan_product_id', 0 );

		// Already linked and still present? Nothing to do.
		if ( $product_id && \WPistic_LSI_Product_Service::get_product( $product_id ) ) {
			return $product_id;
		}

		// Linked id is stale or unset — recover by slug if the product exists.
		$existing = \WPistic_LSI_Product_Service::get_product_by_slug( $slug );
		if ( is_array( $existing ) && ! empty( $existing['product_id'] ) ) {
			update_option( 'mlb_plan_product_id', (int) $existing['product_id'] );
			return (int) $existing['product_id'];
		}

		$created = \WPistic_LSI_Product_Service::create_product( array(
			'product_name'             => 'Chatbotistic Widget',
			'product_slug'             => $slug,
			'product_type'             => 'software',
			'status'                   => 'active',
			'default_activation_limit' => 1,
		) );

		if ( is_wp_error( $created ) || ! $created ) {
			return 0;
		}

		update_option( 'mlb_plan_product_id', (int) $created );
		return (int) $created;
	}
}
