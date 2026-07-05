<?php
/**
 * Admin: handles asset enqueue, settings, and form submissions.
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPistic_LSI_Admin
 */
class WPistic_LSI_Admin {

	const NONCE_LICENSE   = 'wpistic_lsi_save_license';
	const NONCE_PRODUCT   = 'wpistic_lsi_save_product';
	const NONCE_GENERATOR = 'wpistic_lsi_save_generator';
	const NONCE_API_KEY   = 'wpistic_lsi_api_key';
	const NONCE_SETTINGS  = 'wpistic_lsi_settings';
	const NONCE_BULK      = 'wpistic_lsi_bulk_generate';

	/**
	 * Enqueues admin assets on Licenseistic screens.
	 *
	 * @param string $hook Current admin hook.
	 * @return void
	 */
	public function enqueue_assets( $hook ) {
		if ( false === strpos( (string) $hook, 'licenseistic' ) ) {
			return;
		}
		wp_enqueue_style(
			'wpistic-lsi-admin',
			WPISTIC_LSI_URL . 'admin/assets/css/admin.css',
			array(),
			WPISTIC_LSI_VERSION
		);
		wp_enqueue_script(
			'wpistic-lsi-admin',
			WPISTIC_LSI_URL . 'admin/assets/js/admin.js',
			array( 'jquery' ),
			WPISTIC_LSI_VERSION,
			true
		);
	}

	/**
	 * Registers settings options.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'wpistic_lsi_settings_group',
			'wpistic_lsi_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * Sanitizes settings before save.
	 *
	 * @param array $input Raw input.
	 * @return array
	 */
	public function sanitize_settings( $input ) {
		$input = is_array( $input ) ? $input : array();

		$bools  = array( 'enable_customer_dashboard', 'mask_keys', 'enable_rest_api', 'enable_logs', 'allow_same_domain_reactivation', 'require_product_id' );
		$ints   = array( 'default_activation_limit', 'default_expiry_days', 'log_retention_days', 'rate_limit_per_minute' );
		$out    = array();

		$out['default_status'] = isset( $input['default_status'] ) ? wpistic_lsi_sanitize_status( $input['default_status'] ) : 'active';

		foreach ( $bools as $key ) {
			$out[ $key ] = ! empty( $input[ $key ] ) ? 'yes' : 'no';
		}
		foreach ( $ints as $key ) {
			$out[ $key ] = isset( $input[ $key ] ) ? max( 0, (int) $input[ $key ] ) : 0;
		}

		return $out;
	}

	// --- Form handlers --------------------------------------------------------

	/**
	 * Handles admin-post save license form.
	 *
	 * @return void
	 */
	public function handle_save_license() {
		$this->check_cap();
		check_admin_referer( self::NONCE_LICENSE );

		$id = isset( $_POST['license_id'] ) ? (int) $_POST['license_id'] : 0;

		$data = array(
			'license_label'    => isset( $_POST['license_label'] ) ? sanitize_text_field( wp_unslash( $_POST['license_label'] ) ) : '',
			'product_id'       => isset( $_POST['product_id'] ) ? (int) $_POST['product_id'] : 0,
			'customer_id'      => isset( $_POST['customer_id'] ) ? (int) $_POST['customer_id'] : 0,
			'customer_email'   => isset( $_POST['customer_email'] ) ? sanitize_email( wp_unslash( $_POST['customer_email'] ) ) : '',
			'status'           => isset( $_POST['status'] ) ? wpistic_lsi_sanitize_status( wp_unslash( $_POST['status'] ) ) : 'active',
			'activation_limit' => isset( $_POST['activation_limit'] ) ? (int) $_POST['activation_limit'] : 1,
			'expires_at'       => isset( $_POST['expires_at'] ) ? sanitize_text_field( wp_unslash( $_POST['expires_at'] ) ) : '',
			'notes'            => isset( $_POST['notes'] ) ? wp_kses_post( wp_unslash( $_POST['notes'] ) ) : '',
		);

		if ( $id ) {
			WPistic_LSI_License_Service::update_license( $id, $data );
			$message = 'updated';
		} else {
			if ( ! empty( $_POST['license_key'] ) ) {
				$data['license_key'] = sanitize_text_field( wp_unslash( $_POST['license_key'] ) );
			}
			$result = WPistic_LSI_License_Service::create_license( $data );
			if ( is_wp_error( $result ) ) {
				wp_safe_redirect( add_query_arg( array( 'page' => 'licenseistic-licenses', 'action' => 'new', 'wpistic_error' => rawurlencode( $result->get_error_message() ) ), admin_url( 'admin.php' ) ) );
				exit;
			}
			$id = $result['license_id'];
			$message = 'created';
		}

		wp_safe_redirect( add_query_arg( array(
			'page' => 'licenseistic-licenses',
			'wpistic_notice' => $message,
		), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Handles license deletion.
	 *
	 * @return void
	 */
	public function handle_delete_license() {
		$this->check_cap();
		check_admin_referer( 'wpistic_lsi_delete_license' );
		$id = isset( $_POST['license_id'] ) ? (int) $_POST['license_id'] : 0;
		if ( $id ) {
			WPistic_LSI_License_Service::delete_license( $id );
		}
		wp_safe_redirect( add_query_arg( array( 'page' => 'licenseistic-licenses', 'wpistic_notice' => 'deleted' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Handles product save.
	 *
	 * @return void
	 */
	public function handle_save_product() {
		$this->check_cap();
		check_admin_referer( self::NONCE_PRODUCT );

		$id   = isset( $_POST['product_id'] ) ? (int) $_POST['product_id'] : 0;
		$data = array(
			'product_name'    => sanitize_text_field( wp_unslash( $_POST['product_name'] ?? '' ) ),
			'product_slug'    => sanitize_title( wp_unslash( $_POST['product_slug'] ?? '' ) ),
			'product_type'    => sanitize_key( wp_unslash( $_POST['product_type'] ?? 'software' ) ),
			'product_version' => sanitize_text_field( wp_unslash( $_POST['product_version'] ?? '' ) ),
			'download_url'    => esc_url_raw( wp_unslash( $_POST['download_url'] ?? '' ) ),
			'changelog'       => wp_kses_post( wp_unslash( $_POST['changelog'] ?? '' ) ),
			'status'          => sanitize_key( wp_unslash( $_POST['status'] ?? 'active' ) ),
			'default_activation_limit' => (int) ( $_POST['default_activation_limit'] ?? 1 ),
			'default_expiry_days'      => (int) ( $_POST['default_expiry_days'] ?? 0 ),
			'generator_id'             => (int) ( $_POST['generator_id'] ?? 0 ),
		);

		if ( $id ) {
			WPistic_LSI_Product_Service::update_product( $id, $data );
		} else {
			WPistic_LSI_Product_Service::create_product( $data );
		}

		wp_safe_redirect( add_query_arg( array( 'page' => 'licenseistic-products', 'wpistic_notice' => $id ? 'updated' : 'created' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Handles generator save.
	 *
	 * @return void
	 */
	public function handle_save_generator() {
		$this->check_cap();
		check_admin_referer( self::NONCE_GENERATOR );

		global $wpdb;

		$id = isset( $_POST['generator_id'] ) ? (int) $_POST['generator_id'] : 0;
		$row = WPistic_LSI_Key_Generator::validate_generator_settings( wp_unslash( $_POST ) ); // phpcs:ignore
		$row['name']             = sanitize_text_field( wp_unslash( $_POST['name'] ?? __( 'Default', 'licenseistic' ) ) );
		$row['activation_limit'] = isset( $_POST['activation_limit'] ) ? (int) $_POST['activation_limit'] : 1;
		$row['expiry_days']      = isset( $_POST['expiry_days'] ) ? (int) $_POST['expiry_days'] : 0;
		$row['status']           = 'active';
		$row['updated_at']       = wpistic_lsi_now();

		if ( $id ) {
			WPistic_LSI_DB::update( 'generators', $row, array( 'generator_id' => $id ) );
		} else {
			$row['created_at'] = wpistic_lsi_now();
			WPistic_LSI_DB::insert( 'generators', $row );
		}

		wp_safe_redirect( add_query_arg( array( 'page' => 'licenseistic-generators', 'wpistic_notice' => 'saved' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Handles bulk generation form.
	 *
	 * @return void
	 */
	public function handle_bulk_generate() {
		$this->check_cap();
		check_admin_referer( self::NONCE_BULK );

		$quantity     = isset( $_POST['quantity'] ) ? (int) $_POST['quantity'] : 1;
		$generator_id = isset( $_POST['generator_id'] ) ? (int) $_POST['generator_id'] : 0;
		$product_id   = isset( $_POST['product_id'] ) ? (int) $_POST['product_id'] : 0;

		$settings = array();
		if ( $generator_id ) {
			$gen = WPistic_LSI_Key_Generator::get_generator( $generator_id );
			if ( $gen ) {
				$settings = $gen;
			}
		}

		$created = 0;
		for ( $i = 0; $i < $quantity; $i++ ) {
			$result = WPistic_LSI_License_Service::create_license( array(
				'license_key' => WPistic_LSI_Key_Generator::generate_unique_key( $settings ),
				'product_id'  => $product_id,
				'source'      => 'bulk',
			) );
			if ( ! is_wp_error( $result ) ) {
				$created++;
			}
		}

		wp_safe_redirect( add_query_arg( array(
			'page'           => 'licenseistic-generators',
			'wpistic_notice' => 'bulk_done',
			'created'        => $created,
		), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Handles API key creation.
	 *
	 * @return void
	 */
	public function handle_create_api_key() {
		$this->check_cap();
		check_admin_referer( self::NONCE_API_KEY );

		$name        = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
		$permissions = isset( $_POST['permissions'] ) && is_array( $_POST['permissions'] )
			? array_map( 'sanitize_key', wp_unslash( $_POST['permissions'] ) ) // phpcs:ignore
			: array( 'read', 'write' );

		$result = WPistic_LSI_API_Auth::create_api_key( $name ?: __( 'Untitled', 'licenseistic' ), $permissions );

		if ( is_wp_error( $result ) ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'licenseistic-api-keys', 'wpistic_error' => rawurlencode( $result->get_error_message() ) ), admin_url( 'admin.php' ) ) );
			exit;
		}

		set_transient( 'wpistic_lsi_new_api_key_' . get_current_user_id(), $result, 5 * MINUTE_IN_SECONDS );

		wp_safe_redirect( add_query_arg( array( 'page' => 'licenseistic-api-keys', 'wpistic_notice' => 'created' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Handles API key revoke.
	 *
	 * @return void
	 */
	public function handle_revoke_api_key() {
		$this->check_cap();
		check_admin_referer( 'wpistic_lsi_revoke_api_key' );
		$id = isset( $_POST['api_key_id'] ) ? (int) $_POST['api_key_id'] : 0;
		if ( $id ) {
			WPistic_LSI_API_Auth::revoke_api_key( $id );
		}
		wp_safe_redirect( add_query_arg( array( 'page' => 'licenseistic-api-keys', 'wpistic_notice' => 'revoked' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Verifies the current user has admin capability.
	 *
	 * @return void
	 */
	protected function check_cap() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'licenseistic' ) );
		}
	}

	/**
	 * Prints a flash notice if a notice/error param is present.
	 *
	 * @return void
	 */
	public static function maybe_print_notices() {
		if ( ! empty( $_GET['wpistic_notice'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$messages = array(
				'created'   => __( 'Created successfully.', 'licenseistic' ),
				'updated'   => __( 'Updated successfully.', 'licenseistic' ),
				'deleted'   => __( 'Deleted successfully.', 'licenseistic' ),
				'saved'     => __( 'Saved.', 'licenseistic' ),
				'revoked'   => __( 'Revoked.', 'licenseistic' ),
				'bulk_done' => sprintf(
					/* translators: %d: number of created licenses. */
					__( 'Generated %d licenses.', 'licenseistic' ),
					(int) ( $_GET['created'] ?? 0 ) // phpcs:ignore WordPress.Security.NonceVerification
				),
			);
			$slug = sanitize_key( wp_unslash( $_GET['wpistic_notice'] ) ); // phpcs:ignore
			if ( isset( $messages[ $slug ] ) ) {
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $messages[ $slug ] ) . '</p></div>';
			}
		}
		if ( ! empty( $_GET['wpistic_error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( rawurldecode( wp_unslash( $_GET['wpistic_error'] ) ) ) . '</p></div>'; // phpcs:ignore
		}
	}
}
