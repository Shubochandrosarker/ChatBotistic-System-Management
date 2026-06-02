<?php
/**
 * System Health + monitoring integration for the Chatbotistic stack.
 *
 * Wires the Profile plugin's existing service_status() detector into:
 *   - WP Site Health (Tools → Site Health) so the stack shows up alongside
 *     the standard PHP / HTTPS / Updates tests.
 *   - A WordPress dashboard widget so /wp-admin/ shows the status at a glance.
 *   - REST endpoint /wp-json/chatbotistic/v1/status for external monitors
 *     and /wp-json/chatbotistic/v1/entitlements/me for portal-side fetches.
 *   - A first-run admin notice that links to the Profile setup page when
 *     the stack is fresh.
 *
 * @package Chatbotistic\Profile
 */

namespace Chatbotistic\Profile;

defined( 'ABSPATH' ) || exit;

class System_Health {

	const FIRST_RUN_FLAG = 'cbp_welcomed';

	public function register(): void {
		add_filter( 'site_status_tests', array( $this, 'register_site_health_tests' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widget' ) );
		add_action( 'rest_api_init',      array( $this, 'register_rest_routes' ) );
		add_action( 'admin_notices',      array( $this, 'maybe_first_run_notice' ) );
	}

	// ── Site Health ─────────────────────────────────────────────────────────

	/**
	 * Register Chatbotistic checks with WP Site Health.
	 *
	 * @param array $tests
	 * @return array
	 */
	public function register_site_health_tests( $tests ) {
		$tests['direct']['chatbotistic_stack'] = array(
			'label' => __( 'Chatbotistic stack', 'chatbotistic-profile' ),
			'test'  => array( $this, 'site_health_stack' ),
		);
		$tests['direct']['chatbotistic_connector'] = array(
			'label' => __( 'Chatbotistic Tochat connection', 'chatbotistic-profile' ),
			'test'  => array( $this, 'site_health_connector' ),
		);
		$tests['direct']['chatbotistic_licenses'] = array(
			'label' => __( 'Chatbotistic license routes', 'chatbotistic-profile' ),
			'test'  => array( $this, 'site_health_licenses' ),
		);
		return $tests;
	}

	public function site_health_stack(): array {
		$active = $this->stack_state();
		$missing = array_keys( array_filter( $active, static fn ( $v ) => ! $v ) );

		if ( empty( $missing ) ) {
			return $this->result(
				'good',
				__( 'Chatbotistic stack is fully active.', 'chatbotistic-profile' ),
				__( 'Memberistic, Licenseistic, Bridge, Connector, and Profile plugins are all running.', 'chatbotistic-profile' )
			);
		}
		return $this->result(
			'critical',
			__( 'Chatbotistic stack is incomplete.', 'chatbotistic-profile' ),
			sprintf(
				/* translators: %s: comma-separated list of missing components */
				__( 'These plugins should be active for the system to work end-to-end: %s. Activate them and the Profile auto-configuration will sync the rest.', 'chatbotistic-profile' ),
				implode( ', ', $missing )
			),
			'critical'
		);
	}

	public function site_health_connector(): array {
		if ( ! class_exists( '\Chatbotistic\Connector\Store' ) ) {
			return $this->result(
				'recommended',
				__( 'Chatbotistic Connector is not active.', 'chatbotistic-profile' ),
				__( 'Widgets, leads, and analytics need the Connector. Activate it from the Plugins screen.', 'chatbotistic-profile' )
			);
		}
		$email   = (string) \Chatbotistic\Connector\Store::setting( 'api_email' );
		$has_pw  = '' !== (string) \Chatbotistic\Connector\Store::setting( 'api_password' );
		if ( '' === $email || ! $has_pw ) {
			return $this->result(
				'recommended',
				__( 'Chatbotistic Tochat credentials are not configured.', 'chatbotistic-profile' ),
				__( 'Set the Tochat API email and password in Chatbotistic Connector → Settings, or via the CBC_API_EMAIL / CBC_API_PASSWORD constants in wp-config.php.', 'chatbotistic-profile' )
			);
		}
		return $this->result( 'good', __( 'Chatbotistic Tochat credentials are configured.', 'chatbotistic-profile' ), '' );
	}

	public function site_health_licenses(): array {
		if ( ! function_exists( 'rest_get_server' ) ) {
			return $this->result( 'recommended', __( 'REST API is not initialised.', 'chatbotistic-profile' ), '' );
		}
		$routes = rest_get_server()->get_routes();
		$missing = array();
		foreach ( array( '/licenseistic/v1/activate', '/licenseistic/v1/entitlements', '/licenseistic/v1/heartbeat' ) as $r ) {
			if ( ! isset( $routes[ $r ] ) ) { $missing[] = $r; }
		}
		if ( empty( $missing ) ) {
			return $this->result( 'good', __( 'License REST routes are live.', 'chatbotistic-profile' ), '' );
		}
		return $this->result(
			'critical',
			__( 'License REST routes are missing.', 'chatbotistic-profile' ),
			sprintf(
				/* translators: %s: comma-separated list of missing routes */
				__( 'These routes should be registered for the customer-side Widget plugin to activate licenses: %s. Confirm Licenseistic and the Memberistic→Licenseistic Bridge are active.', 'chatbotistic-profile' ),
				implode( ', ', $missing )
			),
			'critical'
		);
	}

	// ── Dashboard widget ───────────────────────────────────────────────────

	public function register_dashboard_widget(): void {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		wp_add_dashboard_widget( 'cbp_status', __( 'Chatbotistic System', 'chatbotistic-profile' ), array( $this, 'render_dashboard_widget' ) );
	}

	public function render_dashboard_widget(): void {
		$rows = $this->status_rows();
		echo '<div style="font-size:13px;">';
		foreach ( $rows as $r ) {
			$dot = 'ok' === $r['state'] ? '🟢' : ( 'warn' === $r['state'] ? '🟡' : '🔴' );
			echo '<div style="display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px solid #eee;">';
			echo '<span>' . esc_html( $dot ) . '</span>';
			echo '<strong style="flex:1;">' . esc_html( $r['label'] ) . '</strong>';
			echo '<span style="color:#646970;font-size:12px;">' . esc_html( $r['detail'] ) . '</span>';
			echo '</div>';
		}
		echo '</div>';
		echo '<p style="margin-top:12px;">';
		echo '<a class="button button-primary" href="' . esc_url( admin_url( 'admin.php?page=chatbotistic-profile' ) ) . '">' . esc_html__( 'Open Profile', 'chatbotistic-profile' ) . '</a> ';
		echo '<a class="button" href="' . esc_url( admin_url( 'site-health.php' ) ) . '">' . esc_html__( 'Site Health', 'chatbotistic-profile' ) . '</a>';
		echo '</p>';
	}

	// ── REST endpoints ─────────────────────────────────────────────────────

	public function register_rest_routes(): void {
		register_rest_route( 'chatbotistic/v1', '/status', array(
			'methods'             => \WP_REST_Server::READABLE,
			'permission_callback' => static fn () => current_user_can( 'manage_options' ),
			'callback'            => array( $this, 'rest_status' ),
		) );

		register_rest_route( 'chatbotistic/v1', '/entitlements/me', array(
			'methods'             => \WP_REST_Server::READABLE,
			'permission_callback' => 'is_user_logged_in',
			'callback'            => array( $this, 'rest_my_entitlements' ),
		) );
	}

	public function rest_status() {
		return new \WP_REST_Response( array(
			'ok'    => true,
			'time'  => gmdate( 'c' ),
			'stack' => $this->stack_state(),
			'rows'  => array_map(
				static fn ( $r ) => array( 'key' => $r['key'], 'label' => $r['label'], 'state' => $r['state'], 'detail' => $r['detail'] ),
				$this->status_rows()
			),
		), 200 );
	}

	public function rest_my_entitlements() {
		$user_id    = get_current_user_id();
		$bridge_caps = class_exists( '\WordPressistic\MLB\Caps' )
			? \WordPressistic\MLB\Caps::FREE
			: array( 'tier' => 'free', 'plan_name' => 'Free', 'max_widgets' => 1, 'max_agents' => 1, 'max_domains' => 1, 'white_label' => false, 'branding' => true );

		if ( $user_id && class_exists( '\WordPressistic\Memberistic\Database\Memberships_Repository' ) ) {
			$membership = \WordPressistic\Memberistic\Database\Memberships_Repository::get_by_user_id( $user_id );
			if ( is_array( $membership ) && class_exists( '\WordPressistic\MLB\Caps' ) ) {
				$bridge_caps = \WordPressistic\MLB\Caps::for_plan_id( (int) ( $membership['plan_id'] ?? 0 ) );
			}
		}

		$license_key = (string) get_user_meta( $user_id, 'mlb_license_key', true );

		return new \WP_REST_Response( array(
			'ok'           => true,
			'user_id'      => $user_id,
			'plan'         => $bridge_caps['plan_name'] ?? 'Free',
			'tier'         => $bridge_caps['tier']      ?? 'free',
			'max_widgets'  => (int) ( $bridge_caps['max_widgets']  ?? 1 ),
			'max_agents'   => (int) ( $bridge_caps['max_agents']   ?? 1 ),
			'max_domains'  => (int) ( $bridge_caps['max_domains']  ?? 1 ),
			'white_label'  => (bool) ( $bridge_caps['white_label'] ?? false ),
			'license_key'  => '' !== $license_key ? $license_key : null,
		), 200 );
	}

	// ── First-run admin notice ─────────────────────────────────────────────

	public function maybe_first_run_notice(): void {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		if ( '1' === (string) get_option( self::FIRST_RUN_FLAG, '' ) ) { return; }
		if ( ! defined( 'MEMBERISTIC_VERSION' ) ) { return; } // no point until M is active

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		// Don't compete with the Profile page itself.
		if ( $screen && 'memberistic_page_chatbotistic-profile' === $screen->id ) {
			update_option( self::FIRST_RUN_FLAG, '1', false );
			return;
		}
		?>
		<div class="notice notice-info is-dismissible" id="cbp-welcome">
			<p style="font-size:14px;">
				<strong>🎉 <?php esc_html_e( 'Welcome to Chatbotistic.', 'chatbotistic-profile' ); ?></strong>
				<?php esc_html_e( 'Your stack is installed. One last step: open the Profile screen to verify pages, plans, and integrations are configured correctly.', 'chatbotistic-profile' ); ?>
				<a class="button button-primary" style="margin-left:8px;" href="<?php echo esc_url( admin_url( 'admin.php?page=chatbotistic-profile' ) ); ?>">
					<?php esc_html_e( 'Open setup', 'chatbotistic-profile' ); ?>
				</a>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin-post.php?action=cbp_dismiss_welcome&_wpnonce=' . wp_create_nonce( 'cbp_dismiss_welcome' ) ) ); ?>">
					<?php esc_html_e( 'Dismiss', 'chatbotistic-profile' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	// ── Shared helpers ─────────────────────────────────────────────────────

	/** What's loaded right now. */
	private function stack_state(): array {
		return array(
			'Memberistic'  => defined( 'MEMBERISTIC_VERSION' ),
			'Licenseistic' => defined( 'WPISTIC_LSI_VERSION' ),
			'M→L Bridge'   => defined( 'MLB_VERSION' ),
			'Connector'    => defined( 'CBC_VERSION' ),
			'Profile'      => defined( 'CBP_VERSION' ),
		);
	}

	/** Compact status rows used by the dashboard widget + REST. */
	private function status_rows(): array {
		$rows = array();
		foreach ( $this->stack_state() as $name => $on ) {
			$rows[] = array(
				'key'    => sanitize_key( $name ),
				'label'  => $name,
				'state'  => $on ? 'ok' : 'fail',
				'detail' => $on ? __( 'Active', 'chatbotistic-profile' ) : __( 'Not active', 'chatbotistic-profile' ),
			);
		}

		// Connector creds.
		$has_creds = class_exists( '\Chatbotistic\Connector\Store' )
			&& (string) \Chatbotistic\Connector\Store::setting( 'api_email' ) !== ''
			&& (string) \Chatbotistic\Connector\Store::setting( 'api_password' ) !== '';
		$rows[] = array(
			'key'    => 'connector_creds',
			'label'  => __( 'Tochat credentials', 'chatbotistic-profile' ),
			'state'  => $has_creds ? 'ok' : 'warn',
			'detail' => $has_creds ? __( 'Configured', 'chatbotistic-profile' ) : __( 'Not configured', 'chatbotistic-profile' ),
		);

		// License REST.
		$lsi_ok = false;
		if ( function_exists( 'rest_get_server' ) ) {
			$routes = rest_get_server()->get_routes();
			$lsi_ok = isset( $routes['/licenseistic/v1/entitlements'] );
		}
		$rows[] = array(
			'key'    => 'license_rest',
			'label'  => __( 'License REST', 'chatbotistic-profile' ),
			'state'  => $lsi_ok ? 'ok' : 'fail',
			'detail' => $lsi_ok ? __( 'Live', 'chatbotistic-profile' ) : __( 'Routes missing', 'chatbotistic-profile' ),
		);

		// Cron health.
		$next = wp_next_scheduled( 'memberistic_daily_expire_memberships' );
		$rows[] = array(
			'key'    => 'cron',
			'label'  => __( 'Daily reconcile cron', 'chatbotistic-profile' ),
			'state'  => $next ? 'ok' : 'warn',
			'detail' => $next ? sprintf( __( 'Next run: %s', 'chatbotistic-profile' ), date_i18n( 'Y-m-d H:i', $next ) ) : __( 'Not scheduled', 'chatbotistic-profile' ),
		);

		return $rows;
	}

	private function result( string $status, string $label, string $description, string $type = 'recommended' ): array {
		return array(
			'label'       => $label,
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'Chatbotistic', 'chatbotistic-profile' ),
				'color' => 'blue',
			),
			'description' => '<p>' . esc_html( $description ) . '</p>',
			'test'        => 'chatbotistic',
		);
	}
}
