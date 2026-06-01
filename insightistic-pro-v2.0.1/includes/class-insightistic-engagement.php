<?php
/**
 * Engagement Tracking class for Insightistic Pro.
 * Injects a lightweight frontend tracking script for custom GA4 events.
 *
 * @package Insightistic_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Insightistic_Engagement
 */
class Insightistic_Engagement {

	/**
	 * Register hooks.
	 */
	public function init() {
		if ( get_option( 'insightistic_pro_engagement_enabled' ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_tracking_script' ) );
		}
	}

	/**
	 * Enqueue the frontend tracking script.
	 * Runs on all public-facing pages when engagement tracking is enabled.
	 */
	public function enqueue_tracking_script() {
		$measurement_id     = get_option( 'insightistic_pro_measurement_id', '' );
		$enc_secret         = get_option( 'insightistic_pro_measurement_secret', '' );
		$measurement_secret = $enc_secret ? Insightistic_Encryption::decrypt( $enc_secret ) : '';

		if ( ! $measurement_id || ! $measurement_secret ) {
			return;
		}

		// Load minified version in production; readable source in SCRIPT_DEBUG mode.
		$script_file = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG )
			? 'assets/js/tracking.src.js'
			: 'assets/js/tracking.min.js';

		wp_enqueue_script(
			'insightistic-tracking',
			INSIGHTISTIC_URL . $script_file,
			array(),
			INSIGHTISTIC_VERSION,
			true
		);

		wp_localize_script(
			'insightistic-tracking',
			'ispTracking',
			array(
				'measurementId'     => esc_js( $measurement_id ),
				'apiSecret'         => esc_js( $measurement_secret ),
				'trackOutbound'     => true,
				'trackScroll'       => true,
				'trackDownloads'    => true,
				'trackEvents'       => true,
				'eventSelectors'    => array( '.isp-track' ),
			)
		);
	}
}
