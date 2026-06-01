<?php
/**
 * REST sugar layer.
 *
 * Two jobs:
 *   1. Register short-path aliases (`/wp-json/licenseistic/v1/activate`,
 *      `/heartbeat`, `/deactivate`) that proxy to the canonical Licenseistic
 *      routes (`/license/activate`, `/license/ping`, `/license/deactivate`).
 *      The Chatbotistic Widget plugin calls the short paths.
 *   2. Decorate the Licenseistic responses with the cap row stored on the
 *      license, so the widget gets `tier / plan_name / max_widgets /
 *      max_agents / max_domains` directly from the activation call.
 *
 * @package WordPressistic\MLB
 */

namespace WordPressistic\MLB;

defined( 'ABSPATH' ) || exit;

class Rest {

	public function register(): void {
		add_action( 'rest_api_init', [ $this, 'register_routes' ], 11 );
	}

	public function register_routes(): void {
		if ( ! defined( 'WPISTIC_LSI_REST_NAMESPACE' ) ) {
			return;
		}
		$ns = WPISTIC_LSI_REST_NAMESPACE;
		$common_args = [
			'license_key' => [ 'required' => true,  'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
			'product'     => [ 'required' => false, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
			'product_id'  => [ 'required' => false, 'type' => 'integer' ],
			'site_url'    => [ 'required' => false, 'type' => 'string', 'sanitize_callback' => 'esc_url_raw' ],
			'domain'      => [ 'required' => false, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
			'instance_id' => [ 'required' => false, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
		];

		foreach ( [ 'activate', 'deactivate' ] as $route ) {
			register_rest_route( $ns, '/' . $route, [
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, $route ],
				'permission_callback' => '__return_true',
				'args'                => $common_args,
			] );
		}

		// heartbeat == Licenseistic ping
		register_rest_route( $ns, '/heartbeat', [
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'heartbeat' ],
			'permission_callback' => '__return_true',
			'args'                => $common_args,
		] );
	}

	public function activate( \WP_REST_Request $request ) {
		$key = (string) $request->get_param( 'license_key' );
		$site = (string) ( $request->get_param( 'site_url' ) ?: $request->get_param( 'domain' ) );

		if ( ! class_exists( '\WPistic_LSI_License_Service' ) ) {
			return $this->error( 'licenseistic_missing', __( 'Licenseistic plugin is not active on this site.', 'memberistic-licenseistic-bridge' ), 503 );
		}

		$instance = (string) $request->get_param( 'instance_id' );
		$res = \WPistic_LSI_License_Service::activate_license( $key, 0, $site, $instance );
		if ( is_wp_error( $res ) ) {
			return $this->error( $res->get_error_code(), $res->get_error_message(), 400 );
		}

		return $this->shape_success( $key, $res );
	}

	public function deactivate( \WP_REST_Request $request ) {
		$key = (string) $request->get_param( 'license_key' );
		$site = (string) ( $request->get_param( 'site_url' ) ?: $request->get_param( 'domain' ) );

		if ( ! class_exists( '\WPistic_LSI_License_Service' ) ) {
			return $this->error( 'licenseistic_missing', __( 'Licenseistic plugin is not active on this site.', 'memberistic-licenseistic-bridge' ), 503 );
		}

		$res = \WPistic_LSI_License_Service::deactivate_license( $key, $site, (string) $request->get_param( 'instance_id' ) );
		if ( is_wp_error( $res ) ) {
			return $this->error( $res->get_error_code(), $res->get_error_message(), 400 );
		}

		return new \WP_REST_Response( [
			'ok'      => true,
			'status'  => 'inactive',
			'message' => __( 'License deactivated for this site.', 'memberistic-licenseistic-bridge' ),
		], 200 );
	}

	public function heartbeat( \WP_REST_Request $request ) {
		$key  = (string) $request->get_param( 'license_key' );
		$site = (string) ( $request->get_param( 'site_url' ) ?: $request->get_param( 'domain' ) );

		if ( ! class_exists( '\WPistic_LSI_License_Service' ) ) {
			return $this->error( 'licenseistic_missing', __( 'Licenseistic plugin is not active on this site.', 'memberistic-licenseistic-bridge' ), 503 );
		}

		$verify = \WPistic_LSI_License_Service::verify_license( $key, 0, $site );
		if ( is_wp_error( $verify ) ) {
			return $this->error( $verify->get_error_code(), $verify->get_error_message(), 400 );
		}
		return $this->shape_success( $key, $verify );
	}

	/**
	 * Wrap a Licenseistic result in the shape the Chatbotistic Widget
	 * expects: { ok, status, tier, plan_name, max_widgets, max_agents,
	 * max_domains, expires_at, customer_email }.
	 *
	 * @param string $key     Plain license key.
	 * @param array  $verify  Verify/activate result.
	 * @return \WP_REST_Response
	 */
	private function shape_success( string $key, array $verify ): \WP_REST_Response {
		$license = \WPistic_LSI_License_Service::get_license_by_key( $key );
		$caps    = Caps::FREE;
		$email   = '';

		if ( is_array( $license ) ) {
			$email = (string) ( $license['customer_email'] ?? '' );

			// Try to read caps from the notes envelope written by Bridge.
			if ( ! empty( $license['notes'] ) ) {
				$decoded = json_decode( (string) $license['notes'], true );
				if ( is_array( $decoded ) && isset( $decoded['_mlb']['caps'] ) ) {
					$caps = array_merge( $caps, (array) $decoded['_mlb']['caps'] );
				}
			}
			// Fall back: if license has a customer with a Memberistic membership,
			// resolve caps live from the plan.
			if ( $caps === Caps::FREE && ! empty( $license['customer_id'] ) ) {
				$repo = '\WordPressistic\Memberistic\Database\Memberships_Repository';
				if ( class_exists( $repo ) && method_exists( $repo, 'get_by_user_id' ) ) {
					$membership = $repo::get_by_user_id( (int) $license['customer_id'] );
					if ( is_array( $membership ) ) {
						$caps = Caps::for_plan_id( (int) ( $membership['plan_id'] ?? 0 ) );
					}
				}
			}
		}

		$payload = [
			'ok'             => true,
			'status'         => (string) ( $verify['status'] ?? 'active' ),
			'tier'           => $caps['tier'],
			'plan_name'      => $caps['plan_name'],
			'max_widgets'    => (int) $caps['max_widgets'],
			'max_agents'     => (int) $caps['max_agents'],
			'max_domains'    => (int) $caps['max_domains'],
			'expires_at'     => (string) ( $verify['expires_at'] ?? '' ),
			'customer_email' => $email,
			// White-label brand fields so the customer-side widget plugin
			// can render the right name / logo / homepage / support address.
			'brand_label'    => (string) ( $caps['brand_label']    ?? '' ),
			'brand_tagline'  => (string) ( $caps['brand_tagline']  ?? '' ),
			'brand_homepage' => (string) ( $caps['brand_homepage'] ?? '' ),
			'support_email'  => (string) ( $caps['support_email']  ?? '' ),
			'logo_url'       => (string) ( $caps['logo_url']       ?? '' ),
			'custom_domain'  => (string) ( $caps['custom_domain']  ?? '' ),
			'api_base_url'   => (string) ( $caps['api_base_url']   ?? '' ),
		];

		return new \WP_REST_Response( $payload, 200 );
	}

	private function error( string $code, string $message, int $status ): \WP_REST_Response {
		return new \WP_REST_Response( [
			'ok'      => false,
			'code'    => $code,
			'message' => $message,
		], $status );
	}
}
