<?php
/**
 * Dashboard REST controller.
 *
 * @package Memberistic
 */

namespace WordPressistic\Memberistic\REST;

use WordPressistic\Memberistic\Database\Activity_Repository;
use WordPressistic\Memberistic\Database\Memberships_Repository;
use WordPressistic\Memberistic\Database\Payments_Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Dashboard_Controller extends REST_Controller {
	/**
	 * Run a controller callback inside an output buffer so any stray
	 * PHP notice/warning is captured and discarded before the REST
	 * response is serialised. Without this, a single E_NOTICE produces
	 * a "The response is not a valid JSON response" error on the
	 * dashboard (the symptom reported on the Memberistic dashboard
	 * page on first install).
	 *
	 * @param callable $cb     The real controller method.
	 * @param mixed    $request The WP_REST_Request, or null.
	 * @return mixed
	 */
	private function buffered( callable $cb, $request = null ) {
		ob_start();
		try {
			return $request ? $cb( $request ) : $cb();
		} finally {
			if ( ob_get_level() > 0 ) { ob_end_clean(); }
		}
	}

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/dashboard/stats',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_stats' ),
				'permission_callback' => array( $this, 'admin_permissions_check' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/dashboard/expiring-soon',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_expiring_soon' ),
				'permission_callback' => array( $this, 'admin_permissions_check' ),
				'args'                => array(
					'days'  => array(
						'type'              => 'integer',
						'required'          => false,
						'minimum'           => 1,
						'maximum'           => 365,
						'default'           => 30,
						'sanitize_callback' => 'absint',
					),
					'limit' => array(
						'type'              => 'integer',
						'required'          => false,
						'minimum'           => 1,
						'maximum'           => 100,
						'default'           => 50,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/dashboard/revenue-history',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_revenue_history' ),
				'permission_callback' => array( $this, 'admin_permissions_check' ),
				'args'                => array(
					'months' => array(
						'type'              => 'integer',
						'required'          => false,
						'minimum'           => 1,
						'maximum'           => 36,
						'default'           => 12,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/dashboard/recent-activity',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_recent_activity' ),
				'permission_callback' => array( $this, 'admin_permissions_check' ),
				'args'                => array(
					'limit' => array(
						'type'              => 'integer',
						'required'          => false,
						'minimum'           => 1,
						'maximum'           => 200,
						'default'           => 50,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);
	}

	/**
	 * Get dashboard stats.
	 */
	public function get_stats() {
		return $this->buffered( function () { return $this->compute_stats(); } );
	}

	private function compute_stats() {
		$data = array(
			'active_members'       => Memberships_Repository::count_by_status( 'active' ),
			'monthly_revenue'      => Payments_Repository::sum_paid_by_cycle( 'monthly' ),
			'annual_revenue'       => Payments_Repository::sum_paid_by_cycle( 'annual' ),
			'expiring_soon'        => Memberships_Repository::count_expiring_soon(),
			'past_due'             => Memberships_Repository::count_by_status( 'past_due' ),
			'checkins_today'       => \WordPressistic\Memberistic\Database\Checkins_Repository::count_today(),
			'new_members_month'    => Memberships_Repository::count_new_this_month(),
			'churn_risk'           => Memberships_Repository::count_by_status( 'cancelled' ) + Memberships_Repository::count_by_status( 'expired' ),
			'waiver_missing'       => Memberships_Repository::count_waiver_missing(),
			'payment_failed'       => Payments_Repository::count_by_status( 'failed' ),
			'revenue_by_plan'      => Payments_Repository::revenue_by_plan(),
			'recent_activity'      => Activity_Repository::get_recent( 5 ),
		);

		return rest_ensure_response( $data );
	}

	public function get_expiring_soon( $request ) {
		return $this->buffered( function ( $req ) {
			$days  = (int) $req->get_param( 'days' );
			$limit = (int) $req->get_param( 'limit' );
			return rest_ensure_response(
				Memberships_Repository::get_expiring_soon(
					$days > 0 ? $days : 30,
					$limit > 0 ? $limit : 50
				)
			);
		}, $request );
	}

	public function get_revenue_history( $request ) {
		return $this->buffered( function ( $req ) {
			$months = (int) $req->get_param( 'months' );
			return rest_ensure_response( Payments_Repository::revenue_history( $months > 0 ? $months : 12 ) );
		}, $request );
	}

	public function get_recent_activity( $request ) {
		return $this->buffered( function ( $req ) {
			$limit = (int) $req->get_param( 'limit' );
			return rest_ensure_response( Activity_Repository::get_recent( $limit > 0 ? $limit : 50 ) );
		}, $request );
	}
}
