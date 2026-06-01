<?php
/**
 * Public side handler.
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPistic_LSI_Public
 */
class WPistic_LSI_Public {

	/**
	 * Enqueues public assets.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		wp_register_style(
			'wpistic-lsi-public',
			WPISTIC_LSI_URL . 'public/assets/css/public.css',
			array(),
			WPISTIC_LSI_VERSION
		);
		wp_register_script(
			'wpistic-lsi-public',
			WPISTIC_LSI_URL . 'public/assets/js/public.js',
			array( 'jquery' ),
			WPISTIC_LSI_VERSION,
			true
		);
		wp_localize_script( 'wpistic-lsi-public', 'WPisticLSI', array(
			'rest_url' => esc_url_raw( rest_url( WPISTIC_LSI_REST_NAMESPACE . '/' ) ),
			'nonce'    => wp_create_nonce( 'wp_rest' ),
		) );
	}
}
