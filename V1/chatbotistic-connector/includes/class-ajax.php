<?php
/**
 * AJAX endpoints for the member dashboard and the admin screen.
 *
 * Every member action is nonce-checked, login-gated, membership-gated and
 * scoped to the member's own userClient tag — a member can never read or
 * change another member's widgets, agents or leads.
 *
 * @package Chatbotistic\Connector
 */

namespace Chatbotistic\Connector;

defined( 'ABSPATH' ) || exit;

class Ajax {

	/**
	 * Register every wp_ajax action.
	 */
	public function __construct() {
		$actions = array(
			'cbc_dashboard'        => 'dashboard',
			'cbc_widget_create'    => 'widget_create',
			'cbc_widget_update'    => 'widget_update',
			'cbc_widget_delete'    => 'widget_delete',
			'cbc_widget_toggle'    => 'widget_toggle',
			'cbc_widget_get'       => 'widget_get',
			'cbc_widget_embed'     => 'widget_embed',
			'cbc_operators'        => 'operators',
			'cbc_operator_create'  => 'operator_create',
			'cbc_operator_update'  => 'operator_update',
			'cbc_operator_delete'  => 'operator_delete',
			'cbc_faq_list'         => 'faq_list',
			'cbc_faq_save'         => 'faq_save',
			'cbc_faq_delete'       => 'faq_delete',
			'cbc_leads'            => 'leads',
			'cbc_analytics'        => 'analytics',
			'cbc_admin_save'       => 'admin_save',
			'cbc_admin_test'       => 'admin_test',
		);
		foreach ( $actions as $action => $method ) {
			// Wrap every handler so any stray PHP notice/warning is captured
			// and discarded before wp_send_json_* writes the response body.
			// Without this, a single E_NOTICE produces an "Unexpected token"
			// JSON parse error on the front-end (the symptom reported in
			// the Widgets → Leads / Analytics tabs).
			add_action( "wp_ajax_{$action}", function () use ( $method ) {
				ob_start();
				try {
					$this->{$method}();
				} finally {
					// Drop anything echoed before wp_send_json_* (it short-circuits).
					if ( ob_get_level() > 0 ) { ob_end_clean(); }
				}
			} );
		}
	}

	// ── Guards ────────────────────────────────────────────────────────────

	/**
	 * Verify nonce + login for member actions. Returns the user ID.
	 *
	 * @return int
	 */
	private function guard(): int {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'cbc_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed. Reload the page.', 'chatbotistic-connector' ) ), 403 );
		}
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( array( 'message' => __( 'Please sign in.', 'chatbotistic-connector' ) ), 401 );
		}
		return $user_id;
	}

	/**
	 * Guard + require an active membership. Returns [user_id, membership].
	 *
	 * @return array{0:int,1:array}
	 */
	private function guard_active(): array {
		$user_id    = $this->guard();
		$membership = Membership::for_user( $user_id );
		if ( empty( $membership['active'] ) ) {
			$status = isset( $membership['status'] ) ? (string) $membership['status'] : '';
			switch ( $status ) {
				case '':
					$message = __( 'You don’t have an active plan yet. Choose a plan to start creating widgets.', 'chatbotistic-connector' );
					break;
				case 'pending':
					$message = __( 'Your plan is awaiting payment. Complete checkout to activate it — if you chose a paid plan, make sure Stripe is connected.', 'chatbotistic-connector' );
					break;
				case 'past_due':
				case 'unpaid':
					$message = __( 'Your last payment didn’t go through, so your plan is paused. Update your payment method to reactivate.', 'chatbotistic-connector' );
					break;
				case 'paused':
				case 'cancelled':
				case 'canceled':
				case 'expired':
					/* translators: %s: membership status */
					$message = sprintf( __( 'Your plan is %s. Renew or choose a plan to manage widgets.', 'chatbotistic-connector' ), $status );
					break;
				default:
					/* translators: %s: membership status */
					$message = sprintf( __( 'Your plan is not active (status: %s). Activate or renew a plan to manage widgets.', 'chatbotistic-connector' ), $status );
			}
			wp_send_json_error( array(
				'message' => $message,
				'status'  => $status,
				'upgrade' => true,
			), 403 );
		}
		return array( $user_id, $membership );
	}

	/**
	 * Verify nonce + admin capability.
	 */
	private function admin_guard(): void {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'cbc_admin_nonce' ) || ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'chatbotistic-connector' ) ), 403 );
		}
	}

	/**
	 * Fetch a widget and confirm it belongs to this user. Ends the request on failure.
	 *
	 * @param string $widget_id   Widget UUID.
	 * @param string $user_client Expected userClient.
	 * @return array Widget resource.
	 */
	private function owned_widget( string $widget_id, string $user_client ): array {
		$widget = API::widget_get( $widget_id );
		if ( is_wp_error( $widget ) ) {
			wp_send_json_error( array( 'message' => $widget->get_error_message() ), 502 );
		}
		if ( ( $widget['userClient'] ?? '' ) !== $user_client ) {
			wp_send_json_error( array( 'message' => __( 'Widget not found.', 'chatbotistic-connector' ) ), 404 );
		}
		return $widget;
	}

	/**
	 * Fetch an operator and confirm its widget belongs to this user.
	 *
	 * @param string $operator_id Operator UUID.
	 * @param string $user_client Expected userClient.
	 * @return array Operator resource.
	 */
	private function owned_operator( string $operator_id, string $user_client ): array {
		$operator = API::operator_get( $operator_id );
		if ( is_wp_error( $operator ) ) {
			wp_send_json_error( array( 'message' => $operator->get_error_message() ), 502 );
		}
		$owner = $operator['business']['userClient'] ?? '';
		if ( $owner !== $user_client ) {
			wp_send_json_error( array( 'message' => __( 'Agent not found.', 'chatbotistic-connector' ) ), 404 );
		}
		return $operator;
	}

	// ── Dashboard ─────────────────────────────────────────────────────────

	/**
	 * Primary dashboard payload: membership, usage and widgets (+ agents).
	 */
	public function dashboard(): void {
		$user_id    = $this->guard();
		$membership = Membership::for_user( $user_id );
		$client     = Store::user_client( $user_id );

		$widgets = array();
		$agents  = 0;

		if ( $membership['active'] ) {
			$list = API::widgets_list( $client );
			if ( is_wp_error( $list ) ) {
				wp_send_json_error( array( 'message' => $list->get_error_message() ), 502 );
			}
			$ops = API::operators_list( $client );
			$ops = is_wp_error( $ops ) ? array() : $ops;

			$ops_by_widget = array();
			foreach ( $ops as $op ) {
				$wid = $op['business']['id'] ?? '';
				$ops_by_widget[ $wid ][] = $this->shape_operator( $op );
			}
			$agents = count( $ops );

			foreach ( $list as $w ) {
				$wid       = (string) ( $w['id'] ?? '' );
				$widgets[] = array(
					'id'        => $wid,
					'name'      => (string) ( $w['name'] ?? '' ),
					'color'     => (string) ( $w['color'] ?? '#25D366' ),
					'active'    => (bool) ( $w['active'] ?? false ),
					'operators' => $ops_by_widget[ $wid ] ?? array(),
					'embed'     => $this->embed_code( $wid ),
				);
			}
		}

		wp_send_json_success( array(
			'membership' => array(
				'active'       => (bool) $membership['active'],
				'plan'         => $membership['plan_name'],
				'widget_limit' => $membership['widget_limit'],
				'agent_limit'  => $membership['agent_limit'],
			),
			'usage'   => array( 'widgets' => count( $widgets ), 'agents' => $agents ),
			'widgets' => $widgets,
		) );
	}

	// ── Widgets ───────────────────────────────────────────────────────────

	/**
	 * Create a widget for the member, tagged with their userClient.
	 */
	public function widget_create(): void {
		list( $user_id, $membership ) = $this->guard_active();
		$client = Store::user_client( $user_id );

		// Enforce the plan's widget limit.
		if ( -1 !== (int) $membership['widget_limit'] ) {
			$existing = API::widgets_list( $client );
			$count    = is_wp_error( $existing ) ? 0 : count( $existing );
			if ( $count >= (int) $membership['widget_limit'] ) {
				wp_send_json_error( array(
					'message' => sprintf( __( 'You have reached your plan limit of %d widget(s).', 'chatbotistic-connector' ), (int) $membership['widget_limit'] ),
					'upgrade' => true,
				), 403 );
			}
		}

		$payload               = $this->widget_payload();
		$payload['userClient'] = $client;
		$payload['active']     = true;
		if ( empty( $payload['name'] ) ) {
			wp_send_json_error( array( 'message' => __( 'A widget name is required.', 'chatbotistic-connector' ) ), 422 );
		}

		$result = API::widget_create( $payload );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 502 );
		}
		$id = API::resource_id( $result );
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Widget created but no ID was returned.', 'chatbotistic-connector' ) ), 502 );
		}

		wp_send_json_success( array(
			'message' => __( 'Widget created.', 'chatbotistic-connector' ),
			'widget'  => array(
				'id'        => $id,
				'name'      => $payload['name'],
				'color'     => $payload['color'] ?? '#25D366',
				'active'    => true,
				'operators' => array(),
				'embed'     => $this->embed_code( $id ),
			),
		) );
	}

	/**
	 * Update a widget the member owns.
	 */
	public function widget_update(): void {
		list( $user_id ) = $this->guard_active();
		$client    = Store::user_client( $user_id );
		$widget_id = $this->post( 'widget_id' );
		if ( ! $widget_id ) {
			wp_send_json_error( array( 'message' => __( 'Widget ID is required.', 'chatbotistic-connector' ) ), 422 );
		}
		$this->owned_widget( $widget_id, $client );

		$payload = $this->widget_payload();
		if ( ! $payload ) {
			wp_send_json_error( array( 'message' => __( 'Nothing to update.', 'chatbotistic-connector' ) ), 422 );
		}
		$result = API::widget_update( $widget_id, $payload );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 502 );
		}
		wp_send_json_success( array( 'message' => __( 'Widget updated.', 'chatbotistic-connector' ) ) );
	}

	/**
	 * Activate or deactivate a widget.
	 */
	public function widget_toggle(): void {
		list( $user_id ) = $this->guard_active();
		$client    = Store::user_client( $user_id );
		$widget_id = $this->post( 'widget_id' );
		$active    = 'true' === $this->post( 'active' );
		$this->owned_widget( $widget_id, $client );

		$result = API::widget_patch( $widget_id, array( 'active' => $active ) );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 502 );
		}
		wp_send_json_success( array(
			'message' => $active ? __( 'Widget activated.', 'chatbotistic-connector' ) : __( 'Widget deactivated.', 'chatbotistic-connector' ),
			'active'  => $active,
		) );
	}

	/**
	 * Delete a widget the member owns.
	 */
	public function widget_delete(): void {
		list( $user_id ) = $this->guard_active();
		$client    = Store::user_client( $user_id );
		$widget_id = $this->post( 'widget_id' );
		$this->owned_widget( $widget_id, $client );

		$result = API::widget_delete( $widget_id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 502 );
		}
		wp_send_json_success( array( 'message' => __( 'Widget deleted.', 'chatbotistic-connector' ) ) );
	}

	/**
	 * Return the full widget resource (for the customize form).
	 */
	public function widget_get(): void {
		$user_id = $this->guard();
		$client  = Store::user_client( $user_id );
		$widget  = $this->owned_widget( $this->post( 'widget_id' ), $client );
		wp_send_json_success( array( 'widget' => $widget ) );
	}

	/**
	 * Return the embed snippet for a widget.
	 */
	public function widget_embed(): void {
		$user_id   = $this->guard();
		$client    = Store::user_client( $user_id );
		$widget_id = $this->post( 'widget_id' );
		$this->owned_widget( $widget_id, $client );
		wp_send_json_success( array( 'embed' => $this->embed_code( $widget_id ) ) );
	}

	// ── Operators (agents) ────────────────────────────────────────────────

	/**
	 * List agents for one of the member's widgets.
	 */
	public function operators(): void {
		$user_id   = $this->guard();
		$client    = Store::user_client( $user_id );
		$widget_id = $this->post( 'widget_id' );
		$this->owned_widget( $widget_id, $client );

		$ops = API::operators_for_widget( $widget_id );
		if ( is_wp_error( $ops ) ) {
			wp_send_json_error( array( 'message' => $ops->get_error_message() ), 502 );
		}
		wp_send_json_success( array( 'operators' => array_map( array( $this, 'shape_operator' ), $ops ) ) );
	}

	/**
	 * Add a WhatsApp agent to a widget.
	 */
	public function operator_create(): void {
		list( $user_id, $membership ) = $this->guard_active();
		$client    = Store::user_client( $user_id );
		$widget_id = $this->post( 'widget_id' );
		$this->owned_widget( $widget_id, $client );

		if ( -1 !== (int) $membership['agent_limit'] ) {
			$all = API::operators_list( $client );
			if ( ! is_wp_error( $all ) && count( $all ) >= (int) $membership['agent_limit'] ) {
				wp_send_json_error( array(
					'message' => sprintf( __( 'You have reached your plan limit of %d agent(s).', 'chatbotistic-connector' ), (int) $membership['agent_limit'] ),
					'upgrade' => true,
				), 403 );
			}
		}

		$name   = $this->post( 'name' );
		$number = $this->normalise_phone( $this->post( 'number' ) );
		if ( ! $name || ! $number ) {
			wp_send_json_error( array( 'message' => __( 'Agent name and a valid WhatsApp number are required.', 'chatbotistic-connector' ) ), 422 );
		}

		$payload = array(
			'business' => '/api/v2/widgets/' . $widget_id,
			'name'     => $name,
			'number'   => $number,
		);
		$post    = $this->post( 'post' );
		$message = $this->post( 'message', 'textarea' );
		if ( $post ) {
			$payload['post'] = $post;
		}
		if ( $message ) {
			$payload['message'] = $message;
		}
		$form = $this->lead_form();
		if ( $form ) {
			$payload['form'] = $form;
		}

		$result = API::operator_create( $payload );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 502 );
		}
		wp_send_json_success( array(
			'message'  => __( 'Agent added.', 'chatbotistic-connector' ),
			'operator' => $this->shape_operator( $result ),
		) );
	}

	/**
	 * Update an agent.
	 */
	public function operator_update(): void {
		list( $user_id ) = $this->guard_active();
		$client      = Store::user_client( $user_id );
		$operator_id = $this->post( 'operator_id' );
		$operator    = $this->owned_operator( $operator_id, $client );

		$payload = array( 'business' => '/api/v2/widgets/' . ( $operator['business']['id'] ?? '' ) );
		if ( '' !== $this->post( 'name' ) ) {
			$payload['name'] = $this->post( 'name' );
		}
		if ( '' !== $this->post( 'number' ) ) {
			$number = $this->normalise_phone( $this->post( 'number' ) );
			if ( ! $number ) {
				wp_send_json_error( array( 'message' => __( 'Enter a valid WhatsApp number with country code.', 'chatbotistic-connector' ) ), 422 );
			}
			$payload['number'] = $number;
		}
		if ( isset( $_POST['post'] ) ) {
			$payload['post'] = $this->post( 'post' );
		}
		if ( isset( $_POST['message'] ) ) {
			$payload['message'] = $this->post( 'message', 'textarea' );
		}
		$form = $this->lead_form();
		if ( $form ) {
			$payload['form'] = $form;
		}

		$result = API::operator_update( $operator_id, $payload );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 502 );
		}
		wp_send_json_success( array(
			'message'  => __( 'Agent updated.', 'chatbotistic-connector' ),
			'operator' => $this->shape_operator( $result ),
		) );
	}

	/**
	 * Delete an agent.
	 */
	public function operator_delete(): void {
		list( $user_id ) = $this->guard_active();
		$client      = Store::user_client( $user_id );
		$operator_id = $this->post( 'operator_id' );
		$this->owned_operator( $operator_id, $client );

		$result = API::operator_delete( $operator_id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 502 );
		}
		wp_send_json_success( array( 'message' => __( 'Agent removed.', 'chatbotistic-connector' ) ) );
	}

	// ── FAQ ───────────────────────────────────────────────────────────────

	/**
	 * List FAQ groups for an agent.
	 */
	public function faq_list(): void {
		$user_id     = $this->guard();
		$client      = Store::user_client( $user_id );
		$operator_id = $this->post( 'operator_id' );
		$this->owned_operator( $operator_id, $client );

		$groups = API::faq_list( $operator_id );
		if ( is_wp_error( $groups ) ) {
			wp_send_json_error( array( 'message' => $groups->get_error_message() ), 502 );
		}
		wp_send_json_success( array( 'groups' => $groups ) );
	}

	/**
	 * Create or update an FAQ group for an agent.
	 */
	public function faq_save(): void {
		list( $user_id ) = $this->guard_active();
		$client      = Store::user_client( $user_id );
		$operator_id = $this->post( 'operator_id' );
		$this->owned_operator( $operator_id, $client );

		$title = $this->post( 'title' ) ?: __( 'FAQs', 'chatbotistic-connector' );
		$faqs  = $this->faq_items();
		if ( ! $faqs ) {
			wp_send_json_error( array( 'message' => __( 'Add at least one question and answer.', 'chatbotistic-connector' ) ), 422 );
		}
		$payload = array(
			'whatsapp' => '/api/v2/whatsapp_operators/' . $operator_id,
			'title'    => $title,
			'faqs'     => $faqs,
		);

		$group_id = $this->post( 'group_id' );
		$result   = $group_id ? API::faq_update( $group_id, $payload ) : API::faq_create( $payload );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 502 );
		}
		wp_send_json_success( array( 'message' => __( 'FAQs saved.', 'chatbotistic-connector' ), 'group' => $result ) );
	}

	/**
	 * Delete an FAQ group.
	 */
	public function faq_delete(): void {
		list( $user_id ) = $this->guard_active();
		$client      = Store::user_client( $user_id );
		$operator_id = $this->post( 'operator_id' );
		$this->owned_operator( $operator_id, $client );

		$group_id = $this->post( 'group_id' );
		$result   = API::faq_delete( $group_id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 502 );
		}
		wp_send_json_success( array( 'message' => __( 'FAQ group deleted.', 'chatbotistic-connector' ) ) );
	}

	// ── Leads + analytics ─────────────────────────────────────────────────

	/**
	 * List the member's leads — always scoped to their userClient.
	 */
	public function leads(): void {
		$user_id = $this->guard();
		$client  = Store::user_client( $user_id );

		$filters = array( 'business.userClient' => $client );
		if ( $this->post( 'widget_id' ) ) {
			$filters['business.id'] = $this->post( 'widget_id' );
		}
		if ( $this->post( 'from' ) ) {
			$filters['created[after]'] = $this->post( 'from' );
		}

		$leads = API::stats_list( $filters );
		if ( is_wp_error( $leads ) ) {
			wp_send_json_error( array( 'message' => $leads->get_error_message() ), 502 );
		}
		wp_send_json_success( array( 'leads' => array_map( array( $this, 'shape_lead' ), $leads ) ) );
	}

	/**
	 * Aggregate analytics across the member's widgets.
	 */
	public function analytics(): void {
		$user_id = $this->guard();
		$client  = Store::user_client( $user_id );

		$widgets = API::widgets_list( $client );
		if ( is_wp_error( $widgets ) ) {
			wp_send_json_error( array( 'message' => $widgets->get_error_message() ), 502 );
		}

		$to    = gmdate( 'Y-m-d' );
		$from  = gmdate( 'Y-m-d', strtotime( '-29 days' ) );
		$totals = array( 'visits' => 0, 'clicks' => 0, 'leads' => 0 );
		$series = array();

		foreach ( $widgets as $w ) {
			$graph = API::stats_graph( (string) ( $w['id'] ?? '' ), $from, $to, 'day' );
			if ( is_wp_error( $graph ) || empty( $graph['data'] ) ) {
				continue;
			}
			foreach ( $graph['data'] as $row ) {
				$day = (string) ( $row['from'] ?? '' );
				$series[ $day ] = $series[ $day ] ?? array( 'visits' => 0, 'clicks' => 0, 'leads' => 0 );
				$series[ $day ]['visits'] += (int) ( $row['total-visits'] ?? 0 );
				$series[ $day ]['clicks'] += (int) ( $row['total-clicks'] ?? 0 );
				$series[ $day ]['leads']  += (int) ( $row['total-leads'] ?? 0 );
				$totals['visits'] += (int) ( $row['total-visits'] ?? 0 );
				$totals['clicks'] += (int) ( $row['total-clicks'] ?? 0 );
				$totals['leads']  += (int) ( $row['total-leads'] ?? 0 );
			}
		}
		ksort( $series );

		$points = array();
		foreach ( $series as $day => $vals ) {
			$points[] = array_merge( array( 'date' => $day ), $vals );
		}

		wp_send_json_success( array(
			'totals'  => $totals,
			'series'  => $points,
			'widgets' => count( $widgets ),
			'range'   => array( 'from' => $from, 'to' => $to ),
		) );
	}

	// ── Admin ─────────────────────────────────────────────────────────────

	/**
	 * Save the master API credentials and per-plan limits.
	 */
	public function admin_save(): void {
		$this->admin_guard();

		$email = isset( $_POST['api_email'] ) ? sanitize_email( wp_unslash( $_POST['api_email'] ) ) : '';
		if ( $email && ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Enter a valid API email.', 'chatbotistic-connector' ) ), 422 );
		}
		$update = array( 'api_email' => $email );

		$password = isset( $_POST['api_password'] ) ? (string) $_POST['api_password'] : '';
		if ( '' !== $password ) {
			$update['api_password'] = Store::encrypt( $password );
			API::clear_token();
		}

		$lead_key = isset( $_POST['lead_api_key'] ) ? sanitize_text_field( (string) $_POST['lead_api_key'] ) : '';
		if ( '' !== $lead_key ) {
			$update['lead_api_key'] = $lead_key;
		}

		$limits = array();
		$raw    = isset( $_POST['plan_limits'] ) && is_array( $_POST['plan_limits'] ) ? wp_unslash( $_POST['plan_limits'] ) : array();
		foreach ( $raw as $plan_id => $vals ) {
			$pid = (int) $plan_id;
			if ( $pid <= 0 ) {
				continue;
			}
			$limits[ $pid ] = array(
				'widgets' => $this->limit_value( $vals['widgets'] ?? '1' ),
				'agents'  => $this->limit_value( $vals['agents'] ?? '1' ),
			);
		}
		$update['plan_limits'] = $limits;

		Store::update_settings( $update );

		// First-time setup completion: when credentials land for the first
		// time, fire a Bridge reconcile so any active memberships that
		// pre-date the Connector activation get their license + caps
		// pushed through immediately. Cheap and idempotent.
		if ( '' !== $password && class_exists( '\WordPressistic\MLB\Bridge' ) ) {
			do_action( 'memberistic_daily_expire_memberships' );
		}

		/**
		 * Fires after Connector settings are saved. Plugins can listen for
		 * this to invalidate caches, re-verify token, etc.
		 */
		do_action( 'cbc_settings_saved', $update );

		wp_send_json_success( array( 'message' => __( 'Settings saved.', 'chatbotistic-connector' ) ) );
	}

	/**
	 * Test the master API connection.
	 */
	public function admin_test(): void {
		$this->admin_guard();
		API::clear_token();
		$token = API::token( true );
		if ( is_wp_error( $token ) ) {
			wp_send_json_error( array( 'message' => $token->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => __( 'Connected to Tochat successfully.', 'chatbotistic-connector' ) ) );
	}

	// ── Shapers ───────────────────────────────────────────────────────────

	/**
	 * Reduce a Tochat operator to the fields the dashboard needs.
	 *
	 * @param array $op Operator resource.
	 * @return array
	 */
	public function shape_operator( array $op ): array {
		return array(
			'id'      => (string) ( $op['id'] ?? API::resource_id( $op ) ),
			'name'    => (string) ( $op['name'] ?? '' ),
			'number'  => (string) ( $op['number'] ?? '' ),
			'post'    => (string) ( $op['post'] ?? '' ),
			'message' => (string) ( $op['message'] ?? '' ),
			'active'  => (bool) ( $op['active'] ?? true ),
			'form'    => $op['form']['items'] ?? array(),
		);
	}

	/**
	 * Reduce a Tochat stat to a lead row.
	 *
	 * @param array $s Stat resource.
	 * @return array
	 */
	public function shape_lead( array $s ): array {
		$fields = array();
		foreach ( (array) ( $s['dataProperties'] ?? array() ) as $f ) {
			$fields[ (string) ( $f['label'] ?? '' ) ] = (string) ( $f['val'] ?? '' );
		}
		return array(
			'id'      => (string) ( $s['id'] ?? API::resource_id( $s ) ),
			'phone'   => (string) ( $s['phone'] ?? '' ),
			'country' => (string) ( $s['country'] ?? '' ),
			'created' => (string) ( $s['created'] ?? '' ),
			'referer' => (string) ( $s['referer'] ?? '' ),
			'widget'  => (string) ( $s['business']['name'] ?? '' ),
			'agent'   => (string) ( $s['whatsapp']['name'] ?? '' ),
			'is_new'  => ! empty( $s['isLeadNew'] ),
			'fields'  => $fields,
			'booked'  => ! empty( $s['bookingData'] ),
		);
	}

	// ── Input helpers ─────────────────────────────────────────────────────

	/**
	 * Read and sanitise a POST value.
	 *
	 * @param string $key  Field name.
	 * @param string $type text|textarea|hex.
	 * @return string
	 */
	private function post( string $key, string $type = 'text' ): string {
		if ( ! isset( $_POST[ $key ] ) ) {
			return '';
		}
		$raw = wp_unslash( $_POST[ $key ] );
		if ( 'textarea' === $type ) {
			return sanitize_textarea_field( $raw );
		}
		if ( 'hex' === $type ) {
			return sanitize_hex_color( $raw ) ?: '';
		}
		return sanitize_text_field( $raw );
	}

	/**
	 * Build a widget payload from whitelisted POST fields.
	 *
	 * @return array
	 */
	private function widget_payload(): array {
		$payload = array();
		$text    = array(
			'name'              => 'name',
			'widget_message'    => 'widgetMessage',
			'button_message'    => 'buttonMessage',
			'legend'            => 'legend',
			'offline_message'   => 'offlineMessage',
		);
		foreach ( $text as $field => $api ) {
			if ( isset( $_POST[ $field ] ) ) {
				$payload[ $api ] = $this->post( $field );
			}
		}
		foreach ( array( 'color' => 'color', 'landing_primary' => 'landingPrimaryColor', 'landing_secondary' => 'landingSecondaryColor' ) as $field => $api ) {
			if ( isset( $_POST[ $field ] ) ) {
				$payload[ $api ] = $this->post( $field, 'hex' ) ?: '#25D366';
			}
		}
		if ( isset( $_POST['position'] ) ) {
			$payload['rightpos'] = 'left' !== $this->post( 'position' );
		}
		if ( isset( $_POST['auto_open'] ) ) {
			$payload['isopen'] = 'true' === $this->post( 'auto_open' );
		}
		if ( isset( $_POST['theme'] ) ) {
			$payload['theme'] = max( 1, (int) $this->post( 'theme' ) );
		}
		return $payload;
	}

	/**
	 * Build the agent lead-capture form from POSTed JSON.
	 *
	 * @return array|null
	 */
	private function lead_form(): ?array {
		if ( empty( $_POST['form_fields'] ) ) {
			return null;
		}
		$decoded = json_decode( wp_unslash( $_POST['form_fields'] ), true );
		if ( ! is_array( $decoded ) ) {
			return null;
		}
		$allowed = array( 'text', 'email', 'tel', 'url', 'number', 'checkbox' );
		$items   = array();
		foreach ( $decoded as $item ) {
			$type = in_array( $item['type'] ?? '', $allowed, true ) ? $item['type'] : 'text';
			$label = sanitize_text_field( (string) ( $item['label'] ?? '' ) );
			if ( '' === $label ) {
				continue;
			}
			$items[] = array(
				'type'     => $type,
				'label'    => $label,
				'required' => ! empty( $item['required'] ),
			);
		}
		return $items ? array( 'items' => $items, 'buttontext' => __( 'Send', 'chatbotistic-connector' ) ) : null;
	}

	/**
	 * Build the FAQ items array from POSTed JSON.
	 *
	 * @return array
	 */
	private function faq_items(): array {
		if ( empty( $_POST['faqs'] ) ) {
			return array();
		}
		$decoded = json_decode( wp_unslash( $_POST['faqs'] ), true );
		if ( ! is_array( $decoded ) ) {
			return array();
		}
		$items = array();
		foreach ( $decoded as $pair ) {
			$q = sanitize_text_field( (string) ( $pair['question'] ?? '' ) );
			$a = sanitize_textarea_field( (string) ( $pair['answer'] ?? '' ) );
			if ( '' !== $q && '' !== $a ) {
				$items[] = array( 'question' => $q, 'answer' => $a );
			}
		}
		return $items;
	}

	/**
	 * Normalise a phone number to + and digits, or '' if invalid.
	 *
	 * @param string $raw Raw input.
	 * @return string
	 */
	private function normalise_phone( string $raw ): string {
		$phone = preg_replace( '/[^\d+]/', '', $raw );
		$phone = '+' . ltrim( $phone, '+' );
		return preg_match( '/^\+\d{7,15}$/', $phone ) ? $phone : '';
	}

	/**
	 * Parse a limit field — number, or -1 for "unlimited".
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	private function limit_value( $value ): int {
		$value = strtolower( trim( (string) $value ) );
		if ( 'unlimited' === $value || '-1' === $value || '' === $value ) {
			return -1;
		}
		return max( 0, (int) $value );
	}

	/**
	 * Build the widget embed snippet.
	 *
	 * @param string $widget_id Widget UUID.
	 * @return string
	 */
	private function embed_code( string $widget_id ): string {
		return sprintf(
			'<script defer src="%s/build/bundle.js?key=%s"></script>',
			esc_url( CBC_API_BASE ),
			esc_attr( $widget_id )
		);
	}
}
