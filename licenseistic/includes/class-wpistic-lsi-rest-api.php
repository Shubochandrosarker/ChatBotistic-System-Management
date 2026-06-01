<?php
/**
 * REST API endpoints.
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPistic_LSI_REST_API
 */
class WPistic_LSI_REST_API {

	/**
	 * Registers all REST routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		if ( 'yes' !== wpistic_lsi_get_setting( 'enable_rest_api', 'yes' ) ) {
			return;
		}

		$ns = WPISTIC_LSI_REST_NAMESPACE;

		register_rest_route( $ns, '/license/verify', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'route_verify' ),
			'permission_callback' => array( $this, 'permission_public' ),
			'args'                => $this->license_args(),
		) );

		register_rest_route( $ns, '/license/activate', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'route_activate' ),
			'permission_callback' => array( $this, 'permission_public' ),
			'args'                => $this->license_args( true ),
		) );

		register_rest_route( $ns, '/license/deactivate', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'route_deactivate' ),
			'permission_callback' => array( $this, 'permission_public' ),
			'args'                => $this->license_args( true ),
		) );

		register_rest_route( $ns, '/license/ping', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'route_ping' ),
			'permission_callback' => array( $this, 'permission_public' ),
			'args'                => $this->license_args( true ),
		) );

		register_rest_route( $ns, '/license/status', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'route_status' ),
			'permission_callback' => array( $this, 'permission_public' ),
			'args'                => array(
				'license_key' => array( 'required' => true, 'type' => 'string' ),
				'product_id'  => array( 'required' => false, 'type' => 'integer' ),
			),
		) );

		/*
		 * Canonical short routes. These are the documented client-facing routes
		 * (activate / deactivate / validate / heartbeat / license / entitlements).
		 * The longer /license/* routes above are kept as backward-compatible
		 * aliases for any client already pointing at them.
		 */
		register_rest_route( $ns, '/activate', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'route_activate' ),
			'permission_callback' => array( $this, 'permission_public' ),
			'args'                => $this->license_args( true ),
		) );

		register_rest_route( $ns, '/deactivate', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'route_deactivate' ),
			'permission_callback' => array( $this, 'permission_public' ),
			'args'                => $this->license_args( true ),
		) );

		register_rest_route( $ns, '/validate', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'route_verify' ),
			'permission_callback' => array( $this, 'permission_public' ),
			'args'                => $this->license_args(),
		) );

		register_rest_route( $ns, '/heartbeat', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'route_ping' ),
			'permission_callback' => array( $this, 'permission_public' ),
			'args'                => $this->license_args( true ),
		) );

		register_rest_route( $ns, '/license', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'route_status' ),
			'permission_callback' => array( $this, 'permission_public' ),
			'args'                => array(
				'license_key' => array( 'required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
				'product_id'  => array( 'required' => false, 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			),
		) );

		register_rest_route( $ns, '/entitlements', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'route_entitlements' ),
			'permission_callback' => array( $this, 'permission_public' ),
			'args'                => array(
				'license_key' => array( 'required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
				'product_id'  => array( 'required' => false, 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			),
		) );

		// Admin / API-key protected endpoints.
		register_rest_route( $ns, '/licenses', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'route_list_licenses' ),
				'permission_callback' => array( $this, 'permission_read' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'route_create_license' ),
				'permission_callback' => array( $this, 'permission_write' ),
			),
		) );

		register_rest_route( $ns, '/licenses/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'route_get_license' ),
				'permission_callback' => array( $this, 'permission_read' ),
				'args'                => array( 'id' => array( 'required' => true, 'type' => 'integer' ) ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'route_update_license' ),
				'permission_callback' => array( $this, 'permission_write' ),
				'args'                => array( 'id' => array( 'required' => true, 'type' => 'integer' ) ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'route_delete_license' ),
				'permission_callback' => array( $this, 'permission_write' ),
				'args'                => array( 'id' => array( 'required' => true, 'type' => 'integer' ) ),
			),
		) );
	}

	/**
	 * Common args for license endpoints.
	 *
	 * @param bool $require_site Whether site_url is required.
	 * @return array
	 */
	protected function license_args( $require_site = false ) {
		return array(
			'license_key' => array(
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'product_id'  => array(
				'required'          => false,
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'site_url'    => array(
				'required'          => $require_site,
				'type'              => 'string',
				'sanitize_callback' => 'esc_url_raw',
			),
			'instance_id' => array(
				'required'          => false,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
		);
	}

	// --- Permission callbacks -------------------------------------------------

	/**
	 * Public endpoints — rate-limited but no auth required.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool|WP_Error
	 */
	public function permission_public( $request ) {
		$ip = wpistic_lsi_get_ip() ?: 'unknown';
		if ( ! WPistic_LSI_API_Auth::check_rate_limit( 'pub_' . $ip ) ) {
			return new WP_Error( 'rate_limited', __( 'Too many requests. Please try again later.', 'licenseistic' ), array( 'status' => 429 ) );
		}
		return true;
	}

	/**
	 * Read access — admin or API key with read.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool|WP_Error
	 */
	public function permission_read( $request ) {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}
		$api = WPistic_LSI_API_Auth::authenticate_request( $request );
		if ( $api && WPistic_LSI_API_Auth::has_permission( $api, 'read' ) ) {
			return true;
		}
		return new WP_Error( 'forbidden', __( 'Authentication required.', 'licenseistic' ), array( 'status' => 401 ) );
	}

	/**
	 * Write access — admin or API key with write.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool|WP_Error
	 */
	public function permission_write( $request ) {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}
		$api = WPistic_LSI_API_Auth::authenticate_request( $request );
		if ( $api && WPistic_LSI_API_Auth::has_permission( $api, 'write' ) ) {
			return true;
		}
		return new WP_Error( 'forbidden', __( 'Authentication required.', 'licenseistic' ), array( 'status' => 401 ) );
	}

	// --- Public routes --------------------------------------------------------

	/**
	 * Verify endpoint.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function route_verify( $request ) {
		$key      = $request->get_param( 'license_key' );
		$product  = (int) $request->get_param( 'product_id' );
		$site_url = (string) $request->get_param( 'site_url' );

		$verify = WPistic_LSI_License_Service::verify_license( $key, $product, $site_url );

		if ( is_wp_error( $verify ) ) {
			return $this->error_response( $verify );
		}

		return rest_ensure_response( wpistic_lsi_response_success(
			__( 'License is valid.', 'licenseistic' ),
			array(
				'status'           => $verify['status'],
				'product_id'       => $verify['product_id'],
				'expires_at'       => $verify['expires_at'],
				'activation_limit' => $verify['activation_limit'],
				'activation_count' => $verify['activation_count'],
			)
		) );
	}

	/**
	 * Activate endpoint.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function route_activate( $request ) {
		$result = WPistic_LSI_License_Service::activate_license(
			$request->get_param( 'license_key' ),
			(int) $request->get_param( 'product_id' ),
			(string) $request->get_param( 'site_url' ),
			(string) $request->get_param( 'instance_id' )
		);
		if ( is_wp_error( $result ) ) {
			return $this->error_response( $result );
		}
		return rest_ensure_response( wpistic_lsi_response_success( __( 'License activated successfully.', 'licenseistic' ), $result ) );
	}

	/**
	 * Deactivate endpoint.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function route_deactivate( $request ) {
		$result = WPistic_LSI_License_Service::deactivate_license(
			$request->get_param( 'license_key' ),
			(string) $request->get_param( 'site_url' ),
			(string) $request->get_param( 'instance_id' )
		);
		if ( is_wp_error( $result ) ) {
			return $this->error_response( $result );
		}
		return rest_ensure_response( wpistic_lsi_response_success( __( 'License deactivated successfully.', 'licenseistic' ), $result ) );
	}

	/**
	 * Ping endpoint.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function route_ping( $request ) {
		$result = WPistic_LSI_License_Service::ping_license(
			$request->get_param( 'license_key' ),
			(int) $request->get_param( 'product_id' ),
			(string) $request->get_param( 'site_url' ),
			(string) $request->get_param( 'instance_id' ),
			(string) $request->get_param( 'installed_version' )
		);
		if ( is_wp_error( $result ) ) {
			return $this->error_response( $result );
		}
		return rest_ensure_response( wpistic_lsi_response_success( __( 'Ping accepted.', 'licenseistic' ), $result ) );
	}

	/**
	 * Status endpoint.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function route_status( $request ) {
		return $this->route_verify( $request );
	}

	/**
	 * Entitlements endpoint — returns the plan caps for a license so a client
	 * plugin (e.g. the Chatbotistic Widget) can unlock features by plan.
	 *
	 * Caps are written onto the license by the Memberistic→Licenseistic bridge
	 * inside the license `notes` field as a `_mlb.caps` envelope. When absent
	 * (e.g. a manually created license), neutral Free-tier defaults are
	 * returned so the client always gets a usable shape.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function route_entitlements( $request ) {
		$key     = (string) $request->get_param( 'license_key' );
		$product = (int) $request->get_param( 'product_id' );

		$verify = WPistic_LSI_License_Service::verify_license( $key, $product );
		if ( is_wp_error( $verify ) ) {
			return $this->error_response( $verify );
		}

		$license = WPistic_LSI_License_Service::get_license_by_key( $key );
		$caps    = $this->extract_caps( is_array( $license ) ? $license : array() );

		$payload = array(
			'status'           => $verify['status'],
			'product_id'       => $verify['product_id'],
			'expires_at'       => $verify['expires_at'],
			'activation_limit' => $verify['activation_limit'],
			'activation_count' => $verify['activation_count'],
			'plan'             => $caps['tier'],
			'plan_name'        => $caps['plan_name'],
			'entitlements'     => array(
				'max_widgets' => $caps['max_widgets'],
				'max_agents'  => $caps['max_agents'],
				'max_domains' => $caps['max_domains'],
				'white_label' => $caps['white_label'],
				'branding'    => $caps['branding'],
			),
		);

		return rest_ensure_response( wpistic_lsi_response_success(
			__( 'Entitlements retrieved.', 'licenseistic' ),
			$payload
		) );
	}

	/**
	 * Extract plan caps from a license row's notes envelope, with Free-tier
	 * defaults when none are present.
	 *
	 * @param array $license License row.
	 * @return array
	 */
	protected function extract_caps( $license ) {
		$defaults = array(
			'tier'        => 'free',
			'plan_name'   => 'Free',
			'max_widgets' => 1,
			'max_agents'  => 1,
			'max_domains' => max( 1, (int) ( $license['activation_limit'] ?? 1 ) ),
			'white_label' => false,
			'branding'    => true,
		);

		$notes = isset( $license['notes'] ) ? json_decode( (string) $license['notes'], true ) : null;
		if ( ! is_array( $notes ) || empty( $notes['_mlb']['caps'] ) || ! is_array( $notes['_mlb']['caps'] ) ) {
			return $defaults;
		}

		$caps = $notes['_mlb']['caps'];
		return array(
			'tier'        => isset( $caps['tier'] ) ? sanitize_key( (string) $caps['tier'] ) : $defaults['tier'],
			'plan_name'   => isset( $caps['plan_name'] ) ? sanitize_text_field( (string) $caps['plan_name'] ) : $defaults['plan_name'],
			'max_widgets' => isset( $caps['max_widgets'] ) ? (int) $caps['max_widgets'] : $defaults['max_widgets'],
			'max_agents'  => isset( $caps['max_agents'] ) ? (int) $caps['max_agents'] : $defaults['max_agents'],
			'max_domains' => isset( $caps['max_domains'] ) ? (int) $caps['max_domains'] : $defaults['max_domains'],
			'white_label' => ! empty( $caps['white_label'] ),
			'branding'    => isset( $caps['branding'] ) ? (bool) $caps['branding'] : ( 'free' === ( $caps['tier'] ?? 'free' ) ),
		);
	}

	// --- Protected routes -----------------------------------------------------

	/**
	 * List licenses (admin/API).
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function route_list_licenses( $request ) {
		$args = array(
			'status'   => sanitize_key( (string) $request->get_param( 'status' ) ),
			'product_id' => (int) $request->get_param( 'product_id' ),
			'search'   => sanitize_text_field( (string) $request->get_param( 'search' ) ),
			'per_page' => max( 1, min( 200, (int) $request->get_param( 'per_page' ) ) ?: 50 ),
			'offset'   => max( 0, (int) $request->get_param( 'offset' ) ),
		);
		$rows = WPistic_LSI_License_Service::get_licenses( $args );
		$out  = array_map( array( $this, 'shape_license' ), $rows );
		return rest_ensure_response( wpistic_lsi_response_success( __( 'Licenses retrieved.', 'licenseistic' ), $out ) );
	}

	/**
	 * Create license (admin/API).
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function route_create_license( $request ) {
		$result = WPistic_LSI_License_Service::create_license( $request->get_params() );
		if ( is_wp_error( $result ) ) {
			return $this->error_response( $result );
		}
		return rest_ensure_response( wpistic_lsi_response_success( __( 'License created.', 'licenseistic' ), $result ) );
	}

	/**
	 * Get license (admin/API).
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function route_get_license( $request ) {
		$license = WPistic_LSI_License_Service::get_license( (int) $request['id'] );
		if ( ! $license ) {
			return $this->error_response( new WP_Error( 'not_found', __( 'License not found.', 'licenseistic' ), array( 'status' => 404 ) ) );
		}
		return rest_ensure_response( wpistic_lsi_response_success( __( 'License retrieved.', 'licenseistic' ), $this->shape_license( $license ) ) );
	}

	/**
	 * Update license (admin/API).
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function route_update_license( $request ) {
		$ok = WPistic_LSI_License_Service::update_license( (int) $request['id'], $request->get_params() );
		if ( ! $ok ) {
			return $this->error_response( new WP_Error( 'update_failed', __( 'License update failed.', 'licenseistic' ) ) );
		}
		return rest_ensure_response( wpistic_lsi_response_success( __( 'License updated.', 'licenseistic' ), array( 'id' => (int) $request['id'] ) ) );
	}

	/**
	 * Delete license (admin/API).
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function route_delete_license( $request ) {
		$ok = WPistic_LSI_License_Service::delete_license( (int) $request['id'] );
		if ( ! $ok ) {
			return $this->error_response( new WP_Error( 'delete_failed', __( 'License delete failed.', 'licenseistic' ) ) );
		}
		return rest_ensure_response( wpistic_lsi_response_success( __( 'License deleted.', 'licenseistic' ), array( 'id' => (int) $request['id'] ) ) );
	}

	/**
	 * Shapes a license row for API output (does not expose hashes).
	 *
	 * @param array $row License row.
	 * @return array
	 */
	protected function shape_license( $row ) {
		return array(
			'license_id'       => (int) $row['license_id'],
			'license_key'      => WPistic_LSI_License_Service::get_display_key( $row ),
			'license_label'    => $row['license_label'],
			'product_id'       => (int) $row['product_id'],
			'customer_id'      => (int) $row['customer_id'],
			'customer_email'   => $row['customer_email'],
			'status'           => WPistic_LSI_License_Service::resolve_status( $row ),
			'activation_limit' => (int) $row['activation_limit'],
			'activation_count' => (int) $row['activation_count'],
			'expires_at'       => $row['expires_at'],
			'created_at'       => $row['created_at'],
		);
	}

	/**
	 * Converts a WP_Error to a structured REST response.
	 *
	 * @param WP_Error $error Error.
	 * @return WP_REST_Response
	 */
	protected function error_response( $error ) {
		$code   = $error->get_error_code();
		$status = 400;
		$data   = $error->get_error_data();
		if ( is_array( $data ) && isset( $data['status'] ) ) {
			$status = (int) $data['status'];
		} elseif ( 'invalid_license' === $code || 'expired_license' === $code ) {
			$status = 404;
		} elseif ( 'limit_reached' === $code || 'product_mismatch' === $code || 'duplicate_key' === $code ) {
			$status = 409;
		}

		$response = wpistic_lsi_response_error( $error->get_error_message(), $code );
		return new WP_REST_Response( $response, $status );
	}
}
