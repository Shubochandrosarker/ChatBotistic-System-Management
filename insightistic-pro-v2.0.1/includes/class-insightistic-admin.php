<?php
/**
 * Admin class for Insightistic Pro.
 *
 * @package Insightistic_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Insightistic_Admin
 */
class Insightistic_Admin {

	/**
	 * Register hooks.
	 */
	public function init() {
		add_action( 'admin_menu',            array( $this, 'register_menus' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register admin menus.
	 */
	public function register_menus() {
		add_menu_page(
			__( 'Insightistic Analytics', 'insightistic' ),
			__( 'Insightistic', 'insightistic' ),
			'manage_options',
			'insightistic',
			array( $this, 'render_dashboard' ),
			'dashicons-chart-area',
			30
		);

		add_submenu_page(
			'insightistic',
			__( 'Analytics Dashboard', 'insightistic' ),
			__( 'Dashboard', 'insightistic' ),
			'manage_options',
			'insightistic',
			array( $this, 'render_dashboard' )
		);

		add_submenu_page(
			'insightistic',
			__( 'Insightistic Settings', 'insightistic' ),
			__( 'Settings', 'insightistic' ),
			'manage_options',
			'insightistic-settings',
			array( $this, 'render_settings' )
		);

		add_submenu_page(
			'insightistic',
			__( 'Insightistic Addons', 'insightistic' ),
			__( '🧩 Addons', 'insightistic' ),
			'manage_options',
			'insightistic-addons',
			array( $this, 'render_addons' )
		);
	}

	/**
	 * Enqueue CSS and JS only on plugin pages.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_assets( $hook ) {
		if ( false === strpos( $hook, 'insightistic' ) ) {
			return;
		}

		// Chart.js is bundled locally — no external CDN dependency.
		wp_register_script(
			'insightistic-chartjs',
			INSIGHTISTIC_URL . 'assets/js/vendor/chart.umd.min.js',
			array(),
			'4.4.4',
			true
		);

		// Serve minified assets in production; source files when SCRIPT_DEBUG is on.
		$css_file = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG )
			? 'assets/css/admin.css'
			: 'assets/css/admin.min.css';

		$js_file = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG )
			? 'assets/js/admin.js'
			: 'assets/js/admin.min.js';

		wp_enqueue_style(
			'insightistic-pro-admin',
			INSIGHTISTIC_URL . $css_file,
			array(),
			INSIGHTISTIC_VERSION
		);

		wp_enqueue_script(
			'insightistic-pro-admin',
			INSIGHTISTIC_URL . $js_file,
			array( 'jquery', 'insightistic-chartjs' ),
			INSIGHTISTIC_VERSION,
			true
		);

		wp_localize_script(
			'insightistic-pro-admin',
			'insightisticPro',
			array(
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'nonce'          => wp_create_nonce( 'insightistic_pro_nonce' ),
				'aiEnabled'      => (int) get_option( 'insightistic_pro_ai_enabled', 0 ),
				'gscConfigured'  => (bool) get_option( 'insightistic_pro_gsc_property_url' ),
				'psiConfigured'  => (bool) get_option( 'insightistic_pro_pagespeed_api_key_enc' ),
				'defaultUrl'     => esc_url( get_option( 'insightistic_pro_pagespeed_default_url', home_url( '/' ) ) ),
				'i18n'           => array(
					'loading'      => __( 'Loading data…', 'insightistic' ),
					'analyzing'    => __( 'AI is analysing your data…', 'insightistic' ),
					'error'        => __( 'Something went wrong. Please try again.', 'insightistic' ),
					'noData'       => __( 'No data found for the selected period.', 'insightistic' ),
					'revenue'      => __( 'Revenue', 'insightistic' ),
					'sessions'     => __( 'Sessions', 'insightistic' ),
					'increase'     => __( 'increase', 'insightistic' ),
					'decrease'     => __( 'decrease', 'insightistic' ),
					'vsLastPeriod' => __( 'vs last period', 'insightistic' ),
					'runTest'      => __( 'Run PageSpeed Test', 'insightistic' ),
					'testing'      => __( 'Running test…', 'insightistic' ),
					// Button labels (previously hardcoded English strings in JS).
					'refreshData'  => __( 'Refresh Data', 'insightistic' ),
					'loadData'     => __( 'Load Data', 'insightistic' ),
					// JSON importer notices (replace alert() calls).
					'jsonOk'       => __( 'Credentials extracted! Please also fill in your GA4 Property ID, then click Save Settings.', 'insightistic' ),
					'jsonErr'      => __( 'Could not parse JSON. Please paste the entire contents of your service account key file.', 'insightistic' ),
				),
			)
		);
	}

	/**
	 * Render the dashboard page.
	 */
	public function render_dashboard() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'insightistic' ) );
		}
		require INSIGHTISTIC_PATH . 'templates/dashboard.php';
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'insightistic' ) );
		}

		if ( isset( $_POST['insightistic_pro_settings_nonce'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['insightistic_pro_settings_nonce'] ) ), 'insightistic_pro_save_settings' ) ) {
			$this->save_settings();
		}

		require INSIGHTISTIC_PATH . 'templates/settings.php';
	}

	/**
	 * Render the addons showcase page.
	 */
	public function render_addons() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'insightistic' ) );
		}
		require INSIGHTISTIC_PATH . 'templates/addons.php';
	}

	/**
	 * Save all settings from POST data.
	 */
	private function save_settings() {
		// ── GA4 credentials ──────────────────────────────────────────────
		update_option( 'insightistic_pro_property_id', sanitize_text_field( wp_unslash( $_POST['property_id'] ?? '' ) ) );
		update_option( 'insightistic_pro_api_email', sanitize_email( wp_unslash( $_POST['api_email'] ?? '' ) ) );

		if ( ! empty( $_POST['api_private_key'] ) ) {
			$raw_key = wp_unslash( $_POST['api_private_key'] );
			$enc     = Insightistic_Encryption::encrypt( $raw_key );
			if ( $enc ) {
				update_option( 'insightistic_pro_api_private_key', $enc );
				// Invalidate both tokens on new key.
				delete_transient( 'insightistic_pro_access_token_ga4' );
				delete_transient( 'insightistic_pro_access_token_gsc' );
			}
		}

		// ── Search Console ────────────────────────────────────────────────
		update_option( 'insightistic_pro_gsc_property_url', esc_url_raw( wp_unslash( $_POST['gsc_property_url'] ?? '' ) ) );

		// ── PageSpeed Insights ────────────────────────────────────────────
		if ( ! empty( $_POST['pagespeed_api_key'] ) ) {
			$psi_raw = sanitize_text_field( wp_unslash( $_POST['pagespeed_api_key'] ) );
			if ( strpos( $psi_raw, '*' ) === false ) {
				$enc = Insightistic_Encryption::encrypt( $psi_raw );
				if ( $enc ) {
					// Store ONLY the encrypted version. Decrypted on use in class-insightistic-pagespeed.php.
					update_option( 'insightistic_pro_pagespeed_api_key_enc', $enc );
					// Remove any legacy plaintext value that may exist from a previous version.
					delete_option( 'insightistic_pro_pagespeed_api_key' );
				}
			}
		}
		update_option( 'insightistic_pro_pagespeed_default_url', esc_url_raw( wp_unslash( $_POST['pagespeed_default_url'] ?? home_url( '/' ) ) ) );

		// ── Engagement Tracking ───────────────────────────────────────────
		update_option( 'insightistic_pro_engagement_enabled', isset( $_POST['engagement_enabled'] ) ? 1 : 0 );
		update_option( 'insightistic_pro_measurement_id', sanitize_text_field( wp_unslash( $_POST['measurement_id'] ?? '' ) ) );

		if ( ! empty( $_POST['measurement_secret'] ) ) {
			$sec = sanitize_text_field( wp_unslash( $_POST['measurement_secret'] ) );
			if ( strpos( $sec, '*' ) === false ) {
				$enc = Insightistic_Encryption::encrypt( $sec );
				if ( $enc ) {
					update_option( 'insightistic_pro_measurement_secret', $enc );
				}
			}
		}

		// ── AI settings ───────────────────────────────────────────────────
		$ai_enabled = isset( $_POST['ai_enabled'] ) ? 1 : 0;
		update_option( 'insightistic_pro_ai_enabled', $ai_enabled );

		$provider = sanitize_key( wp_unslash( $_POST['ai_provider'] ?? 'none' ) );
		$allowed  = array( 'none', 'openai', 'gemini', 'openrouter', 'claude' );
		if ( ! in_array( $provider, $allowed, true ) ) {
			$provider = 'none';
		}
		update_option( 'insightistic_pro_ai_provider', $provider );

		// API keys.
		$key_fields = array(
			'openai_api_key'     => 'insightistic_pro_openai_key',
			'gemini_api_key'     => 'insightistic_pro_gemini_key',
			'openrouter_api_key' => 'insightistic_pro_openrouter_key',
			'claude_api_key'     => 'insightistic_pro_claude_key',
		);
		foreach ( $key_fields as $post_key => $option_name ) {
			if ( ! empty( $_POST[ $post_key ] ) ) {
				$val = sanitize_text_field( wp_unslash( $_POST[ $post_key ] ) );
				if ( strpos( $val, '*' ) === false ) {
					$enc = Insightistic_Encryption::encrypt( $val );
					if ( $enc ) {
						update_option( $option_name, $enc );
					}
				}
			}
		}

		// AI models.
		$model_fields = array(
			'openai_model'     => 'insightistic_pro_openai_model',
			'gemini_model'     => 'insightistic_pro_gemini_model',
			'openrouter_model' => 'insightistic_pro_openrouter_model',
			'claude_model'     => 'insightistic_pro_claude_model',
		);
		foreach ( $model_fields as $post_key => $option_name ) {
			if ( isset( $_POST[ $post_key ] ) ) {
				update_option( $option_name, sanitize_text_field( wp_unslash( $_POST[ $post_key ] ) ) );
			}
		}

		// Guide links.
		update_option( 'insightistic_pro_video_guide_url', esc_url_raw( wp_unslash( $_POST['video_guide_url'] ?? '' ) ) );
		update_option( 'insightistic_pro_docs_url', esc_url_raw( wp_unslash( $_POST['docs_url'] ?? '' ) ) );

		add_settings_error(
			'insightistic_pro_messages',
			'settings_saved',
			__( 'Settings saved successfully.', 'insightistic' ),
			'success'
		);
	}
}
