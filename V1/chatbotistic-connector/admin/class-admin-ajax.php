<?php
/**
 * Admin-only AJAX endpoints powering the Connector's Members / Payments /
 * Licenses pages. Every endpoint requires manage_options + a valid nonce.
 *
 * @package Chatbotistic\Connector
 */

namespace Chatbotistic\Connector;

defined( 'ABSPATH' ) || exit;

class Admin_Ajax {

	public function register(): void {
		$actions = array(
			// Members.
			'cbc_admin_members_list'   => 'members_list',
			'cbc_admin_member_get'     => 'member_get',
			'cbc_admin_member_update'  => 'member_update',
			'cbc_admin_members_bulk'   => 'members_bulk',
			// Payments.
			'cbc_admin_payments_list'  => 'payments_list',
			'cbc_admin_payments_bulk'  => 'payments_bulk',
			'cbc_admin_payments_csv'   => 'payments_csv',
			// Licenses.
			'cbc_admin_licenses_list'  => 'licenses_list',
			'cbc_admin_licenses_bulk'  => 'licenses_bulk',
			'cbc_admin_license_regen'  => 'license_regen',
		);
		foreach ( $actions as $a => $m ) {
			add_action( "wp_ajax_{$a}", array( $this, $m ) );
		}
	}

	// ── Guard ─────────────────────────────────────────────────────────────

	private function guard(): void {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'cbc_admin_nonce' ) || ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'chatbotistic-connector' ) ), 403 );
		}
	}

	// ── Members ───────────────────────────────────────────────────────────

	public function members_list(): void {
		$this->guard();
		$repo = '\WordPressistic\Memberistic\Database\Memberships_Repository';
		if ( ! class_exists( $repo ) ) {
			wp_send_json_error( array( 'message' => 'Memberistic not active.' ), 503 );
		}

		$page     = max( 1, (int) ( $_POST['page'] ?? 1 ) );
		$per_page = max( 1, min( 100, (int) ( $_POST['per_page'] ?? 25 ) ) );
		$args = array(
			'search'        => isset( $_POST['search'] )  ? sanitize_text_field( wp_unslash( $_POST['search'] ) )  : '',
			'status'        => isset( $_POST['status'] )  ? sanitize_key( wp_unslash( $_POST['status'] ) )         : '',
			'plan_id'       => isset( $_POST['plan_id'] ) ? (int) $_POST['plan_id']                                : 0,
			'limit'         => $per_page,
			'offset'        => ( $page - 1 ) * $per_page,
		);

		$rows  = (array) $repo::get_all( $args );
		$total = method_exists( $repo, 'count_all' ) ? (int) $repo::count_all( $args ) : count( $rows );

		wp_send_json_success( array(
			'rows'  => array_map( array( $this, 'shape_member' ), $rows ),
			'total' => $total,
			'page'  => $page,
			'pages' => max( 1, (int) ceil( $total / $per_page ) ),
		) );
	}

	public function member_get(): void {
		$this->guard();
		$id = (int) ( $_POST['id'] ?? 0 );
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => 'Missing id.' ), 422 );
		}
		$repo   = '\WordPressistic\Memberistic\Database\Memberships_Repository';
		$people = '\WordPressistic\Memberistic\Database\People_Repository';
		$pay    = '\WordPressistic\Memberistic\Database\Payments_Repository';
		if ( ! class_exists( $repo ) ) {
			wp_send_json_error( array( 'message' => 'Memberistic not active.' ), 503 );
		}
		$row = method_exists( $repo, 'get_with_summary' ) ? $repo::get_with_summary( $id ) : $repo::get( $id );
		if ( ! $row ) {
			wp_send_json_error( array( 'message' => 'Membership not found.' ), 404 );
		}
		$primary = ( class_exists( $people ) && method_exists( $people, 'get_primary_by_membership' ) )
			? $people::get_primary_by_membership( $id )
			: null;
		$payments = ( class_exists( $pay ) && method_exists( $pay, 'get_by_membership' ) )
			? array_slice( (array) $pay::get_by_membership( $id ), 0, 10 )
			: array();

		wp_send_json_success( array(
			'member'   => $this->shape_member( $row ),
			'primary'  => $primary,
			'payments' => $payments,
		) );
	}

	public function member_update(): void {
		$this->guard();
		$repo = '\WordPressistic\Memberistic\Database\Memberships_Repository';
		if ( ! class_exists( $repo ) ) {
			wp_send_json_error( array( 'message' => 'Memberistic not active.' ), 503 );
		}
		$id = (int) ( $_POST['id'] ?? 0 );
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => 'Missing id.' ), 422 );
		}

		$update = array();
		if ( isset( $_POST['status'] ) ) {
			$update['status'] = sanitize_key( wp_unslash( $_POST['status'] ) );
		}
		if ( isset( $_POST['plan_id'] ) ) {
			$update['plan_id'] = (int) $_POST['plan_id'];
		}
		if ( isset( $_POST['billing_cycle'] ) ) {
			$update['billing_cycle'] = sanitize_key( wp_unslash( $_POST['billing_cycle'] ) );
		}
		if ( isset( $_POST['renewal_date'] ) ) {
			$update['renewal_date'] = sanitize_text_field( wp_unslash( $_POST['renewal_date'] ) );
		}

		if ( ! $update ) {
			wp_send_json_error( array( 'message' => 'Nothing to change.' ), 422 );
		}

		$repo::update( $id, $update );
		if ( isset( $update['status'] ) && 'active' === $update['status'] ) {
			do_action( 'memberistic_membership_activated', $id );
		}
		wp_send_json_success( array( 'message' => __( 'Member updated.', 'chatbotistic-connector' ) ) );
	}

	public function members_bulk(): void {
		$this->guard();
		$repo = '\WordPressistic\Memberistic\Database\Memberships_Repository';
		if ( ! class_exists( $repo ) ) {
			wp_send_json_error( array( 'message' => 'Memberistic not active.' ), 503 );
		}
		$ids    = isset( $_POST['ids'] ) ? array_map( 'intval', (array) $_POST['ids'] ) : array();
		$action = isset( $_POST['bulk'] ) ? sanitize_key( wp_unslash( $_POST['bulk'] ) ) : '';
		if ( ! $ids || ! $action ) {
			wp_send_json_error( array( 'message' => 'Missing ids or action.' ), 422 );
		}

		$done = 0;
		foreach ( $ids as $id ) {
			$ok = false;
			switch ( $action ) {
				case 'activate':
					$ok = $repo::change_status( $id, 'active' );
					if ( $ok ) do_action( 'memberistic_membership_activated', $id );
					break;
				case 'cancel':
					$ok = $repo::change_status( $id, 'cancelled' );
					break;
				case 'pause':
					$ok = $repo::change_status( $id, 'paused' );
					break;
				case 'renew_month':
					$row = $repo::get( $id );
					if ( $row ) {
						$base = $row['renewal_date'] && strtotime( $row['renewal_date'] ) > time() ? strtotime( $row['renewal_date'] ) : time();
						$ok = $repo::update( $id, array( 'renewal_date' => gmdate( 'Y-m-d H:i:s', strtotime( '+1 month', $base ) ), 'status' => 'active' ) );
						if ( $ok ) do_action( 'memberistic_membership_activated', $id );
					}
					break;
				case 'renew_year':
					$row = $repo::get( $id );
					if ( $row ) {
						$base = $row['renewal_date'] && strtotime( $row['renewal_date'] ) > time() ? strtotime( $row['renewal_date'] ) : time();
						$ok = $repo::update( $id, array( 'renewal_date' => gmdate( 'Y-m-d H:i:s', strtotime( '+1 year', $base ) ), 'status' => 'active' ) );
						if ( $ok ) do_action( 'memberistic_membership_activated', $id );
					}
					break;
				case 'plan_change':
					$plan_id = (int) ( $_POST['plan_id'] ?? 0 );
					if ( $plan_id ) {
						$ok = $repo::update( $id, array( 'plan_id' => $plan_id ) );
						if ( $ok ) do_action( 'memberistic_membership_activated', $id );
					}
					break;
				case 'resend_email':
					do_action( 'memberistic_membership_activated', $id );
					$ok = true;
					break;
			}
			if ( $ok ) {
				$done++;
			}
		}
		wp_send_json_success( array( 'message' => sprintf( __( 'Bulk action applied to %d members.', 'chatbotistic-connector' ), $done ) ) );
	}

	// ── Payments ──────────────────────────────────────────────────────────

	public function payments_list(): void {
		$this->guard();
		$repo = '\WordPressistic\Memberistic\Database\Payments_Repository';
		if ( ! class_exists( $repo ) ) {
			wp_send_json_error( array( 'message' => 'Memberistic not active.' ), 503 );
		}
		$page     = max( 1, (int) ( $_POST['page'] ?? 1 ) );
		$per_page = max( 1, min( 100, (int) ( $_POST['per_page'] ?? 50 ) ) );

		$args = array(
			'search'   => isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '',
			'status'   => isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) )        : '',
			'date_from'=> isset( $_POST['date_from'] ) ? sanitize_text_field( wp_unslash( $_POST['date_from'] ) ) : '',
			'date_to'  => isset( $_POST['date_to'] )   ? sanitize_text_field( wp_unslash( $_POST['date_to'] ) )   : '',
			'limit'    => $per_page,
			'offset'   => ( $page - 1 ) * $per_page,
		);

		$rows  = (array) $repo::get_all( $args );
		$total = method_exists( $repo, 'count_all' ) ? (int) $repo::count_all( $args ) : count( $rows );

		wp_send_json_success( array(
			'rows'  => $rows,
			'total' => $total,
			'page'  => $page,
			'pages' => max( 1, (int) ceil( $total / $per_page ) ),
		) );
	}

	public function payments_bulk(): void {
		$this->guard();
		$repo = '\WordPressistic\Memberistic\Database\Payments_Repository';
		if ( ! class_exists( $repo ) ) {
			wp_send_json_error( array( 'message' => 'Memberistic not active.' ), 503 );
		}
		$ids    = isset( $_POST['ids'] ) ? array_map( 'intval', (array) $_POST['ids'] ) : array();
		$action = isset( $_POST['bulk'] ) ? sanitize_key( wp_unslash( $_POST['bulk'] ) ) : '';
		if ( ! $ids || 'refund' !== $action ) {
			wp_send_json_error( array( 'message' => 'Only mark-refunded is supported via bulk; issue actual refunds in your payment gateway.' ), 422 );
		}

		global $wpdb;
		$table = method_exists( $repo, 'table' ) ? $repo::table() : ( $wpdb->prefix . 'memberistic_payments' );
		$done  = 0;
		foreach ( $ids as $id ) {
			$ok = $wpdb->update( $table, array( 'status' => 'refunded' ), array( 'id' => $id ), array( '%s' ), array( '%d' ) );
			if ( false !== $ok ) $done++;
		}
		wp_send_json_success( array( 'message' => sprintf( __( 'Marked %d payment(s) refunded.', 'chatbotistic-connector' ), $done ) ) );
	}

	public function payments_csv(): void {
		$this->guard();
		$repo = '\WordPressistic\Memberistic\Database\Payments_Repository';
		if ( ! class_exists( $repo ) ) {
			wp_send_json_error( array( 'message' => 'Memberistic not active.' ), 503 );
		}
		$rows = (array) $repo::get_all( array( 'limit' => 5000 ) );
		$csv  = "Member UUID,Amount,Currency,Method,Gateway,Status,Paid At\n";
		foreach ( $rows as $r ) {
			$csv .= sprintf( "%s,%.2f,%s,%s,%s,%s,%s\n",
				str_replace( ',', ' ', (string) ( $r['membership_uuid'] ?? $r['membership_id'] ?? '' ) ),
				(float) ( $r['amount'] ?? 0 ),
				(string) ( $r['currency'] ?? 'USD' ),
				(string) ( $r['method'] ?? '' ),
				(string) ( $r['gateway'] ?? '' ),
				(string) ( $r['status'] ?? '' ),
				(string) ( $r['paid_at'] ?? $r['created_at'] ?? '' )
			);
		}
		wp_send_json_success( array( 'csv' => $csv, 'filename' => 'chatbotistic-payments-' . gmdate( 'Y-m-d' ) . '.csv' ) );
	}

	// ── Licenses ──────────────────────────────────────────────────────────

	public function licenses_list(): void {
		$this->guard();
		if ( ! class_exists( '\WPistic_LSI_License_Service' ) ) {
			wp_send_json_error( array( 'message' => 'Licenseistic not active.' ), 503 );
		}
		$page     = max( 1, (int) ( $_POST['page'] ?? 1 ) );
		$per_page = max( 1, min( 100, (int) ( $_POST['per_page'] ?? 50 ) ) );
		$args = array(
			'search'   => isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '',
			'status'   => isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) )        : '',
			'per_page' => $per_page,
			'offset'   => ( $page - 1 ) * $per_page,
		);
		$rows = (array) \WPistic_LSI_License_Service::get_licenses( $args );

		wp_send_json_success( array(
			'rows'  => array_map( array( $this, 'shape_license' ), $rows ),
			'page'  => $page,
		) );
	}

	public function licenses_bulk(): void {
		$this->guard();
		if ( ! class_exists( '\WPistic_LSI_License_Service' ) ) {
			wp_send_json_error( array( 'message' => 'Licenseistic not active.' ), 503 );
		}
		$ids    = isset( $_POST['ids'] ) ? array_map( 'intval', (array) $_POST['ids'] ) : array();
		$action = isset( $_POST['bulk'] ) ? sanitize_key( wp_unslash( $_POST['bulk'] ) ) : '';
		if ( ! $ids || ! $action ) {
			wp_send_json_error( array( 'message' => 'Missing ids or action.' ), 422 );
		}
		$done = 0;
		foreach ( $ids as $id ) {
			$ok = false;
			switch ( $action ) {
				case 'activate':
					$ok = \WPistic_LSI_License_Service::update_license( $id, array( 'status' => 'active' ) );
					break;
				case 'suspend':
					$ok = \WPistic_LSI_License_Service::suspend_license( $id );
					break;
				case 'revoke':
					$ok = \WPistic_LSI_License_Service::revoke_license( $id );
					break;
			}
			if ( $ok ) $done++;
		}
		wp_send_json_success( array( 'message' => sprintf( __( '%d license(s) updated.', 'chatbotistic-connector' ), $done ) ) );
	}

	public function license_regen(): void {
		$this->guard();
		if ( ! class_exists( '\WPistic_LSI_License_Service' ) ) {
			wp_send_json_error( array( 'message' => 'Licenseistic not active.' ), 503 );
		}
		$id = (int) ( $_POST['id'] ?? 0 );
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => 'Missing id.' ), 422 );
		}
		$lic = \WPistic_LSI_License_Service::get_license( $id );
		if ( ! $lic ) {
			wp_send_json_error( array( 'message' => 'License not found.' ), 404 );
		}
		// Mark the old one revoked, mint a new one with the same caps + customer.
		\WPistic_LSI_License_Service::revoke_license( $id );
		$new = \WPistic_LSI_License_Service::create_license( array(
			'customer_id'      => (int) ( $lic['customer_id'] ?? 0 ),
			'customer_email'   => (string) ( $lic['customer_email'] ?? '' ),
			'product_id'       => (int) ( $lic['product_id'] ?? 0 ),
			'activation_limit' => (int) ( $lic['activation_limit'] ?? 1 ),
			'expires_at'       => (string) ( $lic['expires_at'] ?? '' ),
			'source'           => 'regenerated',
			'license_label'    => (string) ( $lic['license_label'] ?? '' ),
			'notes'            => (string) ( $lic['notes'] ?? '' ),
		) );
		if ( is_wp_error( $new ) ) {
			wp_send_json_error( array( 'message' => $new->get_error_message() ), 500 );
		}
		if ( ! empty( $lic['customer_id'] ) ) {
			update_user_meta( (int) $lic['customer_id'], 'mlb_license_id',  (int) $new['license_id'] );
			update_user_meta( (int) $lic['customer_id'], 'mlb_license_key', (string) $new['license_key'] );
		}
		wp_send_json_success( array( 'message' => __( 'Key regenerated.', 'chatbotistic-connector' ), 'new_key' => $new['license_key'] ) );
	}

	// ── Shapers ───────────────────────────────────────────────────────────

	private function shape_member( $row ): array {
		$row = (array) $row;
		return array(
			'id'             => (int) ( $row['id'] ?? 0 ),
			'uuid'           => (string) ( $row['membership_uuid'] ?? '' ),
			'plan_id'        => (int) ( $row['plan_id'] ?? 0 ),
			'plan_name'      => (string) ( $row['plan_name'] ?? '' ),
			'status'         => (string) ( $row['status'] ?? '' ),
			'billing_cycle'  => (string) ( $row['billing_cycle'] ?? '' ),
			'renewal_date'   => (string) ( $row['renewal_date'] ?? '' ),
			'full_name'      => (string) ( $row['full_name'] ?? '' ),
			'email'          => (string) ( $row['email'] ?? '' ),
			'phone'          => (string) ( $row['phone'] ?? '' ),
			'created_at'     => (string) ( $row['created_at'] ?? '' ),
			'people_count'   => (int) ( $row['people_count'] ?? 0 ),
		);
	}

	private function shape_license( $row ): array {
		$row    = (array) $row;
		$status = \WPistic_LSI_License_Service::resolve_status( $row );
		$caps   = array();
		if ( ! empty( $row['notes'] ) ) {
			$dec = json_decode( (string) $row['notes'], true );
			if ( is_array( $dec ) && isset( $dec['_mlb']['caps'] ) ) {
				$caps = (array) $dec['_mlb']['caps'];
			}
		}
		return array(
			'id'               => (int) ( $row['license_id'] ?? 0 ),
			'label'            => (string) ( $row['license_label'] ?? '' ),
			'customer_email'   => (string) ( $row['customer_email'] ?? '' ),
			'customer_id'      => (int) ( $row['customer_id'] ?? 0 ),
			'key_masked'       => \WPistic_LSI_License_Service::get_display_key( $row ),
			'tier'             => (string) ( $caps['tier'] ?? '' ),
			'plan_name'        => (string) ( $caps['plan_name'] ?? '' ),
			'activation_limit' => (int) ( $row['activation_limit'] ?? 1 ),
			'activation_count' => (int) ( $row['activation_count'] ?? 0 ),
			'status'           => $status,
			'expires_at'       => (string) ( $row['expires_at'] ?? '' ),
			'created_at'       => (string) ( $row['created_at'] ?? '' ),
		);
	}
}
