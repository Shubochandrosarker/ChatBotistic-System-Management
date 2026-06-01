<?php
/**
 * Main plugin orchestrator (singleton).
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPistic_LSI_Plugin
 */
class WPistic_LSI_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var WPistic_LSI_Plugin|null
	 */
	protected static $instance = null;

	/**
	 * Loader instance.
	 *
	 * @var WPistic_LSI_Loader
	 */
	public $loader;

	/**
	 * REST API instance.
	 *
	 * @var WPistic_LSI_REST_API
	 */
	public $rest;

	/**
	 * Admin instance.
	 *
	 * @var WPistic_LSI_Admin|null
	 */
	public $admin;

	/**
	 * Admin menu instance.
	 *
	 * @var WPistic_LSI_Admin_Menu|null
	 */
	public $admin_menu;

	/**
	 * Public instance.
	 *
	 * @var WPistic_LSI_Public|null
	 */
	public $public;

	/**
	 * Shortcodes instance.
	 *
	 * @var WPistic_LSI_Shortcodes|null
	 */
	public $shortcodes;

	/**
	 * Returns the singleton instance.
	 *
	 * @return WPistic_LSI_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->boot();
		}
		return self::$instance;
	}

	/**
	 * Wires up hooks and dependencies.
	 *
	 * @return void
	 */
	protected function boot() {
		$this->loader = new WPistic_LSI_Loader();
		$this->rest   = new WPistic_LSI_REST_API();

		load_plugin_textdomain( 'licenseistic', false, dirname( WPISTIC_LSI_BASENAME ) . '/languages' );

		WPistic_LSI_Install::maybe_upgrade();

		// REST.
		$this->loader->add_action( 'rest_api_init', $this->rest, 'register_routes' );

		// Cron.
		$this->loader->add_action( 'init', $this, 'maybe_schedule_cron' );
		$this->loader->add_action( 'wpistic_lsi_daily_event', $this, 'run_daily_jobs' );

		if ( is_admin() ) {
			$this->admin      = new WPistic_LSI_Admin();
			$this->admin_menu = new WPistic_LSI_Admin_Menu();
			$this->loader->add_action( 'admin_menu', $this->admin_menu, 'register_menu' );
			$this->loader->add_action( 'admin_enqueue_scripts', $this->admin, 'enqueue_assets' );
			$this->loader->add_action( 'admin_init', $this->admin, 'register_settings' );
			$this->loader->add_action( 'admin_post_wpistic_lsi_save_license', $this->admin, 'handle_save_license' );
			$this->loader->add_action( 'admin_post_wpistic_lsi_delete_license', $this->admin, 'handle_delete_license' );
			$this->loader->add_action( 'admin_post_wpistic_lsi_save_product', $this->admin, 'handle_save_product' );
			$this->loader->add_action( 'admin_post_wpistic_lsi_save_generator', $this->admin, 'handle_save_generator' );
			$this->loader->add_action( 'admin_post_wpistic_lsi_create_api_key', $this->admin, 'handle_create_api_key' );
			$this->loader->add_action( 'admin_post_wpistic_lsi_revoke_api_key', $this->admin, 'handle_revoke_api_key' );
			$this->loader->add_action( 'admin_post_wpistic_lsi_bulk_generate', $this->admin, 'handle_bulk_generate' );
		}

		$this->public     = new WPistic_LSI_Public();
		$this->shortcodes = new WPistic_LSI_Shortcodes();
		$this->loader->add_action( 'wp_enqueue_scripts', $this->public, 'enqueue_assets' );
		$this->loader->add_action( 'init', $this->shortcodes, 'register' );

		$this->loader->run();
	}

	/**
	 * Schedules the daily cron if not already scheduled.
	 *
	 * @return void
	 */
	public function maybe_schedule_cron() {
		if ( ! wp_next_scheduled( 'wpistic_lsi_daily_event' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'wpistic_lsi_daily_event' );
		}
	}

	/**
	 * Runs daily maintenance: prune logs, expire licenses.
	 *
	 * @return void
	 */
	public function run_daily_jobs() {
		WPistic_LSI_Logger::prune();

		global $wpdb;
		$rows = $wpdb->get_results( $wpdb->prepare( // phpcs:ignore WordPress.DB
			'SELECT license_id FROM ' . wpistic_lsi_table( 'licenses' ) . ' WHERE status = %s AND expires_at IS NOT NULL AND expires_at < %s LIMIT 500',
			'active',
			wpistic_lsi_now()
		), ARRAY_A );
		foreach ( $rows as $row ) {
			WPistic_LSI_License_Service::expire_license( (int) $row['license_id'] );
		}
	}
}
