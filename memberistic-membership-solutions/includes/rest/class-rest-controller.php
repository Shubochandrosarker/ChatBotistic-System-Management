<?php
/**
 * Base REST controller.
 *
 * @package Memberistic
 */

namespace WordPressistic\Memberistic\REST;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class REST_Controller extends \WP_REST_Controller {
	/**
	 * REST namespace.
	 *
	 * Kept untyped for compatibility with WP_REST_Controller.
	 *
	 * @var string
	 */
	protected $namespace = 'memberistic/v1';

	/**
	 * Admin permission callback.
	 */
	public function admin_permissions_check() {
		if ( current_user_can( 'manage_memberistic' ) || current_user_can( 'view_memberistic_dashboard' ) || current_user_can( 'manage_options' ) ) {
			return true;
		}

		return new \WP_Error(
			'memberistic_rest_forbidden',
			__( 'You are not allowed to access this Memberistic endpoint.', 'memberistic' ),
			array( 'status' => rest_authorization_required_code() )
		);
	}

	public function public_permissions_check() {
		return true;
	}

	/**
	 * Discard any stray output that may have been emitted by other plugins,
	 * themes, or PHP notices before this controller responds.
	 *
	 * If something earlier in the request printed even a single byte, the
	 * REST response will fail to parse as JSON and the React dashboard will
	 * surface "The response is not a valid JSON response." This drains every
	 * active output buffer so the response leaves the server clean.
	 */
	protected function discard_stray_output(): void {
		while ( ob_get_level() > 0 ) {
			$contents = ob_get_clean();
			if ( false === $contents ) {
				break;
			}
		}
	}
}
