<?php
/**
 * Admin settings — master API credentials, per-plan limits, API log.
 *
 * @package Chatbotistic\Connector
 */

namespace Chatbotistic\Connector;

defined( 'ABSPATH' ) || exit;

class Admin {

	/**
	 * Hook the admin menu and assets.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		add_filter( 'plugin_action_links_' . CBC_BASENAME, array( $this, 'action_links' ) );

		// Register the admin-only AJAX endpoints used by Members / Payments / Licenses.
		( new Admin_Ajax() )->register();
	}

	/**
	 * Register the admin pages.
	 */
	public function menu(): void {
		add_menu_page(
			__( 'Chatbotistic Connector', 'chatbotistic-connector' ),
			__( 'Chatbotistic', 'chatbotistic-connector' ),
			'manage_options',
			'chatbotistic-connector',
			array( $this, 'settings_page' ),
			'dashicons-format-chat',
			58
		);
		add_submenu_page( 'chatbotistic-connector', __( 'Members',  'chatbotistic-connector' ), __( 'Members',  'chatbotistic-connector' ), 'manage_options', 'chatbotistic-connector-members',  array( $this, 'members_page' ) );
		add_submenu_page( 'chatbotistic-connector', __( 'Payments', 'chatbotistic-connector' ), __( 'Payments', 'chatbotistic-connector' ), 'manage_options', 'chatbotistic-connector-payments', array( $this, 'payments_page' ) );
		add_submenu_page( 'chatbotistic-connector', __( 'Licenses', 'chatbotistic-connector' ), __( 'Licenses', 'chatbotistic-connector' ), 'manage_options', 'chatbotistic-connector-licenses', array( $this, 'licenses_page' ) );
		add_submenu_page( 'chatbotistic-connector', __( 'Plans',    'chatbotistic-connector' ), __( 'Plans',    'chatbotistic-connector' ), 'manage_options', 'chatbotistic-connector-plans',    array( $this, 'plans_page' ) );
		add_submenu_page( 'chatbotistic-connector', __( 'Settings', 'chatbotistic-connector' ), __( 'Settings', 'chatbotistic-connector' ), 'manage_options', 'chatbotistic-connector', array( $this, 'settings_page' ) );
		add_submenu_page( 'chatbotistic-connector', __( 'Plugins',  'chatbotistic-connector' ), __( 'Plugins',  'chatbotistic-connector' ), 'manage_options', 'chatbotistic-connector-plugins',  array( $this, 'plugins_page' ) );
		add_submenu_page( 'chatbotistic-connector', __( 'API Log',  'chatbotistic-connector' ), __( 'API Log',  'chatbotistic-connector' ), 'manage_options', 'chatbotistic-connector-log',      array( $this, 'log_page' ) );
	}

	public function members_page():  void { ( new Page_Members() )->render(); }
	public function payments_page(): void { ( new Page_Payments() )->render(); }
	public function licenses_page(): void { ( new Page_Licenses() )->render(); }
	public function plans_page():    void { ( new Page_Plans() )->render(); }

	/**
	 * Plugins status page — shows which sister plugins are active and what
	 * each provides. Quick check that the full stack is wired up correctly.
	 */
	public function plugins_page(): void {
		$rows = array(
			array(
				'name'    => 'Memberistic',
				'slug'    => 'memberistic-membership-solutions/memberistic-membership-solutions.php',
				'detect'  => defined( 'MEMBERISTIC_VERSION' ),
				'version' => defined( 'MEMBERISTIC_VERSION' ) ? MEMBERISTIC_VERSION : '',
				'desc'    => __( 'Membership plans, checkout, renewals. Source of truth for paid status.', 'chatbotistic-connector' ),
			),
			array(
				'name'    => 'Licenseistic',
				'slug'    => 'licenseistic/licenseistic.php',
				'detect'  => defined( 'WPISTIC_LSI_VERSION' ),
				'version' => defined( 'WPISTIC_LSI_VERSION' ) ? WPISTIC_LSI_VERSION : '',
				'desc'    => __( 'Issues license keys + per-domain activations. Used by the customer-side widget plugin.', 'chatbotistic-connector' ),
			),
			array(
				'name'    => 'Memberistic → Licenseistic Bridge',
				'slug'    => 'memberistic-licenseistic-bridge/memberistic-licenseistic-bridge.php',
				'detect'  => defined( 'MLB_VERSION' ),
				'version' => defined( 'MLB_VERSION' ) ? MLB_VERSION : '',
				'desc'    => __( 'Auto-issues a license key when a Memberistic plan activates; revokes on cancel.', 'chatbotistic-connector' ),
			),
			array(
				'name'    => 'Chatbotistic Profile for Memberistic',
				'slug'    => 'chatbotistic-profile/chatbotistic-profile.php',
				'detect'  => defined( 'CBP_VERSION' ),
				'version' => defined( 'CBP_VERSION' ) ? CBP_VERSION : '',
				'desc'    => __( 'One-install configuration profile — the 4 Chatbotistic plans, the 7 member-facing pages, branded emails, auto-approve, waiver UI strip.', 'chatbotistic-connector' ),
			),
			array(
				'name'    => 'Chatbotistic Connector',
				'slug'    => 'chatbotistic-connector/chatbotistic-connector.php',
				'detect'  => defined( 'CBC_VERSION' ),
				'version' => defined( 'CBC_VERSION' ) ? CBC_VERSION : '',
				'desc'    => __( 'This plugin. Front-end member dashboard backed by Tochat.be.', 'chatbotistic-connector' ),
			),
			array(
				'name'    => 'Chatbotistic Widget (customer-side)',
				'slug'    => 'chatbotistic-widget/chatbotistic-widget.php',
				'detect'  => defined( 'CBW_VERSION' ),
				'version' => defined( 'CBW_VERSION' ) ? CBW_VERSION : '',
				'desc'    => __( 'Installed on customer sites (NOT this site). Embeds the widget and validates the license issued by Licenseistic.', 'chatbotistic-connector' ),
			),
		);
		?>
		<div class="wrap cbc-admin">
			<h1><?php esc_html_e( 'Chatbotistic stack — plugin status', 'chatbotistic-connector' ); ?></h1>
			<p class="description"><?php esc_html_e( 'The full Chatbotistic SaaS stack is five plugins. Four run on this site, one ships to customers. The connector won\'t enforce plan limits unless Memberistic is active; the customer-side widget won\'t auto-issue keys unless the Bridge is active.', 'chatbotistic-connector' ); ?></p>
			<table class="widefat striped">
				<thead><tr>
					<th><?php esc_html_e( 'Plugin', 'chatbotistic-connector' ); ?></th>
					<th><?php esc_html_e( 'Status', 'chatbotistic-connector' ); ?></th>
					<th><?php esc_html_e( 'Version', 'chatbotistic-connector' ); ?></th>
					<th><?php esc_html_e( 'Purpose', 'chatbotistic-connector' ); ?></th>
				</tr></thead>
				<tbody>
				<?php foreach ( $rows as $row ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $row['name'] ); ?></strong><br><code style="font-size:11px;"><?php echo esc_html( $row['slug'] ); ?></code></td>
						<td><?php echo $row['detect'] ? '<span style="color:#1e8e3e;font-weight:600;">● ' . esc_html__( 'Active', 'chatbotistic-connector' ) . '</span>' : '<span style="color:#b71c1c;">○ ' . esc_html__( 'Inactive', 'chatbotistic-connector' ) . '</span>'; ?></td>
						<td><?php echo esc_html( $row['version'] ?: '—' ); ?></td>
						<td><?php echo esc_html( $row['desc'] ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Settings link on the plugins screen.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public function action_links( array $links ): array {
		$url = admin_url( 'admin.php?page=chatbotistic-connector' );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'chatbotistic-connector' ) . '</a>' );
		return $links;
	}

	/**
	 * Enqueue admin assets on the plugin's screens only.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function assets( string $hook ): void {
		if ( false === strpos( $hook, 'chatbotistic-connector' ) ) {
			return;
		}
		wp_enqueue_style( 'cbc-admin',       CBC_URL . 'assets/admin.css',       array(), CBC_VERSION );
		wp_enqueue_style( 'cbc-admin-pages', CBC_URL . 'admin/assets/admin-pages.css', array(), CBC_VERSION );
		wp_enqueue_script( 'cbc-admin',       CBC_URL . 'assets/admin.js',       array(), CBC_VERSION, true );
		wp_enqueue_script( 'cbc-admin-pages', CBC_URL . 'admin/assets/admin-pages.js', array(), CBC_VERSION, true );

		// Pull active Memberistic plans for the bulk move-to-plan + drawer
		// plan picker. Cheap query, runs only on our admin pages.
		$plans = array();
		$repo  = '\WordPressistic\Memberistic\Database\Plans_Repository';
		if ( class_exists( $repo ) && method_exists( $repo, 'get_all' ) ) {
			foreach ( (array) $repo::get_all( array( 'status' => 'active' ) ) as $p ) {
				$plans[] = array( 'id' => (int) ( $p['id'] ?? 0 ), 'name' => (string) ( $p['name'] ?? '' ) );
			}
		}

		$cfg = array(
			'ajax'  => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'cbc_admin_nonce' ),
			'plans' => $plans,
		);
		wp_localize_script( 'cbc-admin',       'CBCAdmin', $cfg );
		wp_localize_script( 'cbc-admin-pages', 'CBCAdmin', $cfg );
	}

	/**
	 * Render the settings page.
	 */
	public function settings_page(): void {
		$email         = (string) Store::setting( 'api_email' );
		$has_pass      = '' !== (string) Store::setting( 'api_password' );
		$has_lead_key  = '' !== (string) Store::setting( 'lead_api_key' );
		$dashboard_url = Store::dashboard_url();
		$limits        = Membership::plan_limits();
		$plans         = Membership::memberistic_plans();
		// When the credentials are pinned in wp-config.php, lock the input
		// so admins don't accidentally overwrite them via the form.
		$email_locked  = Store::setting_is_locked( 'api_email' );
		$pass_locked   = Store::setting_is_locked( 'api_password' );
		$lead_locked   = Store::setting_is_locked( 'lead_api_key' );
		?>
		<div class="wrap cbc-admin">
			<h1><?php esc_html_e( 'Chatbotistic Connector', 'chatbotistic-connector' ); ?></h1>
			<div class="cbc-admin__notice" id="cbc-admin-notice" hidden></div>

			<form id="cbc-admin-form">
				<h2><?php esc_html_e( 'Tochat API (master account)', 'chatbotistic-connector' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="cbc-email"><?php esc_html_e( 'API Email', 'chatbotistic-connector' ); ?></label></th>
						<td>
							<input type="email" id="cbc-email" name="api_email" class="regular-text" value="<?php echo esc_attr( $email ); ?>" <?php disabled( $email_locked ); ?> <?php echo $email_locked ? '' : 'required'; ?>>
							<?php if ( $email_locked ) : ?>
								<p class="description"><?php esc_html_e( '🔒 Managed by the CBC_API_EMAIL constant in wp-config.php.', 'chatbotistic-connector' ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="cbc-pass"><?php esc_html_e( 'API Password', 'chatbotistic-connector' ); ?></label></th>
						<td>
							<input type="password" id="cbc-pass" name="api_password" class="regular-text" autocomplete="new-password" placeholder="<?php echo $has_pass ? esc_attr__( '•••••• (leave blank to keep)', 'chatbotistic-connector' ) : ''; ?>" <?php disabled( $pass_locked ); ?>>
							<?php if ( $pass_locked ) : ?>
								<p class="description"><?php esc_html_e( '🔒 Managed by the CBC_API_PASSWORD constant in wp-config.php.', 'chatbotistic-connector' ); ?></p>
							<?php else : ?>
								<p class="description"><?php esc_html_e( 'Stored encrypted. Leave blank to keep the saved password.', 'chatbotistic-connector' ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="cbc-lead-key"><?php esc_html_e( 'Lead Export API Key', 'chatbotistic-connector' ); ?></label></th>
						<td>
							<input type="password" id="cbc-lead-key" name="lead_api_key" class="regular-text" autocomplete="new-password" placeholder="<?php echo $has_lead_key ? esc_attr__( '•••••• (leave blank to keep)', 'chatbotistic-connector' ) : ''; ?>" <?php disabled( $lead_locked ); ?>>
							<?php if ( $lead_locked ) : ?>
								<p class="description"><?php esc_html_e( '🔒 Managed by the CBC_LEAD_API_KEY constant in wp-config.php.', 'chatbotistic-connector' ); ?></p>
							<?php else : ?>
								<p class="description"><?php esc_html_e( 'Long-lived API key for GET /api/get-json-lead (lead export). Optional — leave blank if you don\'t need bulk lead JSON exports.', 'chatbotistic-connector' ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
				</table>

				<p class="description" style="background:#f0f6fc;border-left:4px solid #2271b1;padding:10px 12px;margin:12px 0;">
					<?php esc_html_e( 'For production deployments, define the constants below in wp-config.php so secrets stay out of the database:', 'chatbotistic-connector' ); ?>
					<code style="display:block;margin-top:6px;">define( 'CBC_API_EMAIL',    'master@your-account.com' );<br>define( 'CBC_API_PASSWORD', 'your-password' );<br>define( 'CBC_LEAD_API_KEY', 'optional-lead-key' );</code>
				</p>

				<h2><?php esc_html_e( 'Standalone Dashboard', 'chatbotistic-connector' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="cbc-dashboard-url"><?php esc_html_e( 'Dashboard URL', 'chatbotistic-connector' ); ?></label></th>
						<td>
							<input type="url" id="cbc-dashboard-url" name="dashboard_url" class="regular-text" value="<?php echo esc_attr( $dashboard_url ); ?>" placeholder="https://chatbot.wpistic.cloud">
							<p class="description"><?php esc_html_e( 'Link shown to members pointing to the standalone Chatbotistic dashboard app.', 'chatbotistic-connector' ); ?></p>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Plan limits', 'chatbotistic-connector' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Widgets and WhatsApp agents allowed per Memberistic plan. Use -1 or “unlimited” for no limit.', 'chatbotistic-connector' ); ?></p>
				<table class="widefat striped">
					<thead><tr>
						<th><?php esc_html_e( 'Plan', 'chatbotistic-connector' ); ?></th>
						<th><?php esc_html_e( 'Widgets', 'chatbotistic-connector' ); ?></th>
						<th><?php esc_html_e( 'Agents', 'chatbotistic-connector' ); ?></th>
					</tr></thead>
					<tbody>
						<?php if ( $plans ) : ?>
							<?php foreach ( $plans as $plan ) :
								$pid = (int) ( $plan['id'] ?? 0 );
								$w   = isset( $limits[ $pid ]['widgets'] ) ? (int) $limits[ $pid ]['widgets'] : 1;
								$a   = isset( $limits[ $pid ]['agents'] ) ? (int) $limits[ $pid ]['agents'] : 1;
								?>
								<tr>
									<td><strong><?php echo esc_html( (string) ( $plan['name'] ?? ( '#' . $pid ) ) ); ?></strong></td>
									<td><input type="text" class="small-text" name="plan_limits[<?php echo esc_attr( $pid ); ?>][widgets]" value="<?php echo esc_attr( -1 === $w ? 'unlimited' : $w ); ?>"></td>
									<td><input type="text" class="small-text" name="plan_limits[<?php echo esc_attr( $pid ); ?>][agents]" value="<?php echo esc_attr( -1 === $a ? 'unlimited' : $a ); ?>"></td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr><td colspan="3"><em><?php esc_html_e( 'No Memberistic plans found. Activate Memberistic and create plans first.', 'chatbotistic-connector' ); ?></em></td></tr>
						<?php endif; ?>
					</tbody>
				</table>

				<p class="submit">
					<button type="submit" class="button button-primary" id="cbc-save"><?php esc_html_e( 'Save Settings', 'chatbotistic-connector' ); ?></button>
					<button type="button" class="button" id="cbc-test"><?php esc_html_e( 'Test Connection', 'chatbotistic-connector' ); ?></button>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Render the API log page.
	 */
	public function log_page(): void {
		$rows = Store::get_log( 100 );
		?>
		<div class="wrap cbc-admin">
			<h1><?php esc_html_e( 'Chatbotistic Connector — API Log', 'chatbotistic-connector' ); ?></h1>
			<table class="widefat striped">
				<thead><tr>
					<th><?php esc_html_e( 'When', 'chatbotistic-connector' ); ?></th>
					<th><?php esc_html_e( 'User', 'chatbotistic-connector' ); ?></th>
					<th><?php esc_html_e( 'Method', 'chatbotistic-connector' ); ?></th>
					<th><?php esc_html_e( 'Endpoint', 'chatbotistic-connector' ); ?></th>
					<th><?php esc_html_e( 'Status', 'chatbotistic-connector' ); ?></th>
					<th><?php esc_html_e( 'Message', 'chatbotistic-connector' ); ?></th>
				</tr></thead>
				<tbody>
					<?php if ( ! $rows ) : ?>
						<tr><td colspan="6"><em><?php esc_html_e( 'No API calls logged yet.', 'chatbotistic-connector' ); ?></em></td></tr>
					<?php else : ?>
						<?php foreach ( $rows as $row ) : ?>
							<tr>
								<td><?php echo esc_html( $row->created_at ); ?></td>
								<td><?php echo esc_html( $row->user_email ?: '—' ); ?></td>
								<td><code><?php echo esc_html( $row->method ); ?></code></td>
								<td><code style="font-size:11px;"><?php echo esc_html( $row->endpoint ); ?></code></td>
								<td><?php echo $row->ok ? '✅' : '⚠️'; ?> <?php echo esc_html( (string) ( $row->status_code ?: '—' ) ); ?></td>
								<td><?php echo esc_html( (string) $row->message ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
