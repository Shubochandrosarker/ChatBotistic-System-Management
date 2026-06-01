<?php
/**
 * PageSpeed Insights API class.
 *
 * @package Insightistic_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Insightistic_PageSpeed
 * Handles Google PageSpeed Insights API requests.
 */
class Insightistic_PageSpeed {

	/** PageSpeed API endpoint. */
	const API_ENDPOINT = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';

	/**
	 * Register AJAX hooks.
	 */
	public function init() {
		add_action( 'wp_ajax_insightistic_get_pagespeed', array( $this, 'ajax_get_pagespeed' ) );
	}

	/* ------------------------------------------------------------------ */
	/* AJAX Handler                                                         */
	/* ------------------------------------------------------------------ */

	/**
	 * Run PageSpeed analysis for a given URL.
	 */
	public function ajax_get_pagespeed() {
		check_ajax_referer( 'insightistic_pro_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'insightistic' ) );
		}

		$enc_key = get_option( 'insightistic_pro_pagespeed_api_key_enc' );
		if ( ! $enc_key ) {
			wp_send_json_error( __( 'PageSpeed API key is not configured. Please visit Settings.', 'insightistic' ) );
		}
		$api_key = Insightistic_Encryption::decrypt( $enc_key );
		if ( ! $api_key ) {
			wp_send_json_error( __( 'Failed to read the PageSpeed API key. Please re-save it in Settings.', 'insightistic' ) );
		}

		$url = esc_url_raw( wp_unslash( $_POST['page_url'] ?? '' ) );
		if ( ! $url ) {
			$url = home_url( '/' );
		}

		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			wp_send_json_error( __( 'Invalid URL provided.', 'insightistic' ) );
		}

		// Cache for 1 hour.
		$cache_key = 'insightistic_psi_' . md5( $url );
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			wp_send_json_success( $cached );
		}

		// Run mobile and desktop in parallel-ish sequence.
		$mobile_raw  = $this->fetch_report( $url, 'mobile', $api_key );
		$desktop_raw = $this->fetch_report( $url, 'desktop', $api_key );

		if ( is_wp_error( $mobile_raw ) && is_wp_error( $desktop_raw ) ) {
			wp_send_json_error( $mobile_raw->get_error_message() );
		}

		$result = array(
			'url'     => $url,
			'mobile'  => ! is_wp_error( $mobile_raw )  ? $this->parse_report( $mobile_raw )  : null,
			'desktop' => ! is_wp_error( $desktop_raw ) ? $this->parse_report( $desktop_raw ) : null,
		);

		set_transient( $cache_key, $result, HOUR_IN_SECONDS );
		wp_send_json_success( $result );
	}

	/* ------------------------------------------------------------------ */
	/* Core Methods                                                         */
	/* ------------------------------------------------------------------ */

	/**
	 * Fetch a PageSpeed report for a URL and strategy.
	 *
	 * @param string $url      URL to test.
	 * @param string $strategy 'mobile' or 'desktop'.
	 * @param string $api_key  Google Cloud API key.
	 * @return array|WP_Error  Decoded response or WP_Error.
	 */
	private function fetch_report( $url, $strategy, $api_key ) {
		$endpoint = add_query_arg(
			array(
				'url'      => rawurlencode( $url ),
				'strategy' => $strategy,
				'key'      => $api_key,
				'category' => 'performance',
			),
			self::API_ENDPOINT
		);

		$response = wp_remote_get(
			$endpoint,
			array( 'timeout' => 60 )
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			$msg = $body['error']['message'] ?? __( 'PageSpeed API error.', 'insightistic' );
			return new WP_Error( 'psi_error', $msg );
		}

		return $body;
	}

	/**
	 * Parse a raw PageSpeed API response into a structured array.
	 *
	 * @param array $raw Raw API response.
	 * @return array
	 */
	private function parse_report( $raw ) {
		$categories   = $raw['lighthouseResult']['categories'] ?? array();
		$audits       = $raw['lighthouseResult']['audits'] ?? array();

		$score = isset( $categories['performance']['score'] )
			? intval( round( $categories['performance']['score'] * 100 ) )
			: 0;

		// Core Web Vitals.
		$cwv = array(
			'lcp' => $this->parse_metric( $audits['largest-contentful-paint'] ?? null, 2500, 4000 ),
			'inp' => $this->parse_metric( $audits['interaction-to-next-paint'] ?? ( $audits['total-blocking-time'] ?? null ), 200, 500 ),
			'cls' => $this->parse_metric( $audits['cumulative-layout-shift'] ?? null, 0.1, 0.25, false ),
			'fcp' => $this->parse_metric( $audits['first-contentful-paint'] ?? null, 1800, 3000 ),
			'tbt' => $this->parse_metric( $audits['total-blocking-time'] ?? null, 200, 600 ),
			'si'  => $this->parse_metric( $audits['speed-index'] ?? null, 3400, 5800 ),
		);

		return array(
			'score' => $score,
			'cwv'   => $cwv,
		);
	}

	/**
	 * Parse a single audit metric.
	 *
	 * @param array|null $audit     Audit data from Lighthouse.
	 * @param float      $good      Threshold for a "good" score.
	 * @param float      $moderate  Threshold for a "moderate" score.
	 * @param bool       $is_ms     Whether the value is in milliseconds.
	 * @return array
	 */
	private function parse_metric( $audit, $good, $moderate, $is_ms = true ) {
		if ( ! $audit ) {
			return array( 'display' => 'N/A', 'value' => null, 'status' => 'unknown', 'label' => '' );
		}

		$raw_value = $audit['numericValue'] ?? null;
		$display   = $audit['displayValue'] ?? 'N/A';
		$label     = $audit['title'] ?? '';

		$status = 'good';
		if ( null !== $raw_value ) {
			if ( $raw_value > $moderate ) {
				$status = 'poor';
			} elseif ( $raw_value > $good ) {
				$status = 'moderate';
			}
		}

		return array(
			'display' => $display,
			'value'   => $raw_value,
			'status'  => $status,
			'label'   => $label,
		);
	}
}
