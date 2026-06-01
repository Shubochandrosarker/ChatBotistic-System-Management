<?php
/**
 * Admin menu builder.
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPistic_LSI_Admin_Menu
 */
class WPistic_LSI_Admin_Menu {

	/**
	 * Capability required for the admin menu.
	 *
	 * @var string
	 */
	protected $cap = 'manage_options';

	/**
	 * Registers the top-level Licenseistic menu and submenus.
	 *
	 * @return void
	 */
	public function register_menu() {
		add_menu_page(
			__( 'Licenseistic', 'licenseistic' ),
			__( 'Licenseistic', 'licenseistic' ),
			$this->cap,
			'licenseistic',
			array( $this, 'render_dashboard' ),
			'dashicons-shield-alt',
			56
		);

		$submenus = array(
			'licenseistic'              => array( __( 'Dashboard', 'licenseistic' ), 'render_dashboard' ),
			'licenseistic-licenses'     => array( __( 'Licenses', 'licenseistic' ), 'render_licenses' ),
			'licenseistic-products'     => array( __( 'Products', 'licenseistic' ), 'render_products' ),
			'licenseistic-generators'   => array( __( 'Generators', 'licenseistic' ), 'render_generators' ),
			'licenseistic-activations'  => array( __( 'Activations', 'licenseistic' ), 'render_activations' ),
			'licenseistic-api-keys'     => array( __( 'API Keys', 'licenseistic' ), 'render_api_keys' ),
			'licenseistic-logs'         => array( __( 'Logs', 'licenseistic' ), 'render_logs' ),
			'licenseistic-settings'     => array( __( 'Settings', 'licenseistic' ), 'render_settings' ),
		);

		foreach ( $submenus as $slug => $entry ) {
			add_submenu_page(
				'licenseistic',
				$entry[0],
				$entry[0],
				$this->cap,
				$slug,
				array( $this, $entry[1] )
			);
		}
	}

	/**
	 * Renders the dashboard view.
	 *
	 * @return void
	 */
	public function render_dashboard() {
		$this->render( 'dashboard' );
	}

	/**
	 * Renders the licenses view (list or edit).
	 *
	 * @return void
	 */
	public function render_licenses() {
		$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		if ( in_array( $action, array( 'edit', 'new' ), true ) ) {
			$this->render( 'license-edit' );
			return;
		}
		$this->render( 'licenses' );
	}

	/**
	 * Renders the products view.
	 *
	 * @return void
	 */
	public function render_products() {
		$this->render( 'products' );
	}

	/**
	 * Renders the generators view.
	 *
	 * @return void
	 */
	public function render_generators() {
		$this->render( 'generators' );
	}

	/**
	 * Renders the activations view.
	 *
	 * @return void
	 */
	public function render_activations() {
		$this->render( 'activations' );
	}

	/**
	 * Renders the API keys view.
	 *
	 * @return void
	 */
	public function render_api_keys() {
		$this->render( 'api-keys' );
	}

	/**
	 * Renders the logs view.
	 *
	 * @return void
	 */
	public function render_logs() {
		$this->render( 'logs' );
	}

	/**
	 * Renders the settings view.
	 *
	 * @return void
	 */
	public function render_settings() {
		$this->render( 'settings' );
	}

	/**
	 * Loads an admin view template.
	 *
	 * @param string $view View slug.
	 * @return void
	 */
	protected function render( $view ) {
		if ( ! current_user_can( $this->cap ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'licenseistic' ) );
		}

		$file = WPISTIC_LSI_PATH . 'admin/views/' . $view . '.php';
		if ( file_exists( $file ) ) {
			include $file;
		} else {
			echo '<div class="wrap"><h1>' . esc_html__( 'Licenseistic', 'licenseistic' ) . '</h1><p>' . esc_html__( 'View not found.', 'licenseistic' ) . '</p></div>';
		}
	}
}
