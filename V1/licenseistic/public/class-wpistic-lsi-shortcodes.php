<?php
/**
 * Public shortcodes.
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPistic_LSI_Shortcodes
 */
class WPistic_LSI_Shortcodes {

	/**
	 * Registers shortcodes and AJAX handlers.
	 *
	 * @return void
	 */
	public function register() {
		add_shortcode( 'licenseistic_dashboard', array( $this, 'render_dashboard' ) );
		add_action( 'wp_ajax_wpistic_lsi_deactivate_site', array( $this, 'ajax_deactivate_site' ) );
	}

	/**
	 * Renders the customer dashboard shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_dashboard( $atts ) {
		if ( 'yes' !== wpistic_lsi_get_setting( 'enable_customer_dashboard', 'yes' ) ) {
			return '';
		}

		if ( ! is_user_logged_in() ) {
			return '<div class="wpistic-lsi-notice">' . esc_html__( 'Please log in to view your licenses.', 'licenseistic' )
				. ' <a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">' . esc_html__( 'Log in', 'licenseistic' ) . '</a></div>';
		}

		wp_enqueue_style( 'wpistic-lsi-public' );
		wp_enqueue_script( 'wpistic-lsi-public' );

		$user     = wp_get_current_user();
		$licenses = WPistic_LSI_Customer_Service::get_user_licenses( $user->ID );

		ob_start();
		$template = WPISTIC_LSI_PATH . 'templates/customer-dashboard/licenses.php';
		if ( file_exists( $template ) ) {
			include $template;
		}
		return ob_get_clean();
	}

	/**
	 * AJAX handler for customer-initiated site deactivation.
	 *
	 * @return void
	 */
	public function ajax_deactivate_site() {
		check_ajax_referer( 'wpistic_lsi_customer', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'Login required.', 'licenseistic' ) ), 401 );
		}

		$activation_id = isset( $_POST['activation_id'] ) ? (int) $_POST['activation_id'] : 0;
		if ( ! $activation_id ) {
			wp_send_json_error( array( 'message' => __( 'Missing activation.', 'licenseistic' ) ) );
		}

		global $wpdb;
		$activation = $wpdb->get_row( $wpdb->prepare( // phpcs:ignore WordPress.DB
			'SELECT * FROM ' . wpistic_lsi_table( 'activations' ) . ' WHERE activation_id = %d',
			$activation_id
		), ARRAY_A );

		if ( ! $activation ) {
			wp_send_json_error( array( 'message' => __( 'Activation not found.', 'licenseistic' ) ) );
		}

		$license = WPistic_LSI_License_Service::get_license( (int) $activation['license_id'] );
		$user    = wp_get_current_user();

		$is_owner = ( (int) $license['customer_id'] === (int) $user->ID )
			|| ( strtolower( (string) $license['customer_email'] ) === strtolower( $user->user_email ) );

		if ( ! $is_owner ) {
			wp_send_json_error( array( 'message' => __( 'You do not own this license.', 'licenseistic' ) ), 403 );
		}

		WPistic_LSI_DB::update( 'activations', array(
			'status'         => 'deactivated',
			'deactivated_at' => wpistic_lsi_now(),
		), array( 'activation_id' => $activation_id ) );

		$wpdb->query( $wpdb->prepare( // phpcs:ignore WordPress.DB
			'UPDATE ' . wpistic_lsi_table( 'licenses' ) . ' SET activation_count = GREATEST(activation_count - 1, 0) WHERE license_id = %d',
			(int) $license['license_id']
		) );

		do_action( 'wpistic_lsi_license_deactivated', (int) $license['license_id'], $activation_id );

		wp_send_json_success( array( 'message' => __( 'Site deactivated.', 'licenseistic' ) ) );
	}
}
