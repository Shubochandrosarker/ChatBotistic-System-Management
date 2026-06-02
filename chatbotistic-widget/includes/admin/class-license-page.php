<?php
namespace Chatbotistic_Widget\Admin;

use Chatbotistic_Widget\License;

defined( 'ABSPATH' ) || exit;

final class License_Page {

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) return;
		Admin::header( __( 'License', 'chatbotistic-widget' ) );

		$is_active     = License::is_active();
		$caps          = License::get_caps();
		$key           = License::get_key();
		$payload       = get_option( License::OPT_PAYLOAD, [] );
		$last          = (int) get_option( License::OPT_LASTSEEN, 0 );
		$grace         = License::grace_state();
		$domain        = (string) wp_parse_url( home_url(), PHP_URL_HOST );
		$instance      = (string) get_option( License::OPT_INSTANCE, '' );
		$widget_list   = method_exists( License::class, 'get_widget_list' ) ? (array) License::get_widget_list() : array();
		$widget_count  = count( $widget_list );
		// Domain usage — Licenseistic returns activation_count + _limit when
		// we have a fresh payload; surface as "X of Y used".
		$dom_used      = isset( $payload['activation_count'] ) ? (int) $payload['activation_count'] : null;
		$dom_limit     = isset( $payload['activation_limit'] ) ? (int) $payload['activation_limit'] : (int) ( $caps['max_domains'] ?? 1 );
		?>
		<?php settings_errors( 'cbw_license' ); ?>

		<?php if ( $key && $grace['active'] ) : ?>
			<div class="notice notice-warning" style="margin:0 0 16px;">
				<p>
					<?php
					printf(
						/* translators: %s = relative time until grace period ends */
						esc_html__( 'We could not reach the license server at the last check. Premium features stay on for now and will retry automatically. If this continues, they will switch to the Free plan in about %s.', 'chatbotistic-widget' ),
						esc_html( human_time_diff( time(), max( time(), (int) $grace['expires'] ) ) )
					);
					?>
				</p>
			</div>
		<?php endif; ?>

		<?php Admin::upgrade_banner_if_free(); ?>

		<div class="cbw-grid cbw-grid--2-1">
			<div class="cbw-card">
				<h2 class="cbw-card__title"><?php esc_html_e( 'License Key', 'chatbotistic-widget' ); ?></h2>
				<p class="cbw-card__sub">
					<?php
					printf(
						/* translators: %s = chatbotistic.com signup link */
						esc_html__( 'Get a free license at %s. Paid plans unlock more widgets, agents and additional websites.', 'chatbotistic-widget' ),
						'<a href="' . esc_url( CBW_REGISTER_URL ) . '" target="_blank" rel="noopener">chatbotistic.com</a>'
					);
					?>
				</p>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cbw-form">
					<input type="hidden" name="action" value="cbw_activate_license" />
					<?php wp_nonce_field( 'cbw_activate_license' ); ?>

					<div class="cbw-field">
						<label for="cbw-license-key"><?php esc_html_e( 'Enter your license key', 'chatbotistic-widget' ); ?></label>
						<div class="cbw-input-group">
							<input type="text" id="cbw-license-key" name="license_key" value="<?php echo esc_attr( $key ); ?>" placeholder="CBT-XXXX-XXXX-XXXX-XXXX" autocomplete="off" />
							<button type="submit" class="cbw-btn cbw-btn--primary">
								<?php echo $is_active ? esc_html__( 'Refresh', 'chatbotistic-widget' ) : esc_html__( 'Activate', 'chatbotistic-widget' ); ?>
							</button>
						</div>
						<small class="cbw-hint"><?php esc_html_e( 'This site\'s domain is registered against your license. Free plan = 1 domain.', 'chatbotistic-widget' ); ?></small>
					</div>
				</form>

				<?php if ( $is_active ) : ?>
					<!-- Diagnostic / maintenance row: refresh widgets, test
					     connection, deactivate. Each posts a separate action
					     so the buttons don't trample each other. -->
					<div class="cbw-form-actions" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:18px;align-items:center;">
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0;">
							<input type="hidden" name="action" value="cbw_refresh_widgets" />
							<?php wp_nonce_field( 'cbw_refresh_widgets' ); ?>
							<button type="submit" class="cbw-btn cbw-btn--ghost"><?php esc_html_e( 'Refresh widgets', 'chatbotistic-widget' ); ?></button>
						</form>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0;">
							<input type="hidden" name="action" value="cbw_test_license" />
							<?php wp_nonce_field( 'cbw_test_license' ); ?>
							<button type="submit" class="cbw-btn cbw-btn--ghost"><?php esc_html_e( 'Test connection', 'chatbotistic-widget' ); ?></button>
						</form>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0;margin-left:auto;">
							<input type="hidden" name="action" value="cbw_deactivate_license" />
							<?php wp_nonce_field( 'cbw_deactivate_license' ); ?>
							<button type="submit" class="cbw-btn cbw-btn--ghost cbw-btn--danger js-cbw-confirm-deactivate">
								<?php esc_html_e( 'Deactivate License on this Site', 'chatbotistic-widget' ); ?>
							</button>
						</form>
					</div>

					<?php if ( $widget_count ) : ?>
						<p class="cbw-hint" style="margin-top:14px;">
							<?php
							/* translators: %d: number of widgets cached locally */
							echo esc_html( sprintf( _n( '%d widget cached locally — pick one on the Widgets tab.', '%d widgets cached locally — pick one on the Widgets tab.', $widget_count, 'chatbotistic-widget' ), $widget_count ) );
							?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . Admin::SETTINGS_SLUG ) ); ?>" style="margin-left:6px;"><?php esc_html_e( 'Open Widgets →', 'chatbotistic-widget' ); ?></a>
						</p>
					<?php else : ?>
						<p class="cbw-hint" style="margin-top:14px;">
							<?php esc_html_e( 'No widgets cached yet. Click "Refresh widgets" to load them, or create your first one in your Chatbotistic account.', 'chatbotistic-widget' ); ?>
						</p>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<aside class="cbw-card cbw-card--side">
				<h2 class="cbw-card__title"><?php esc_html_e( 'Plan Status', 'chatbotistic-widget' ); ?></h2>
				<dl class="cbw-defs">
					<dt><?php esc_html_e( 'Status', 'chatbotistic-widget' ); ?></dt>
					<dd>
						<?php if ( $is_active ) : ?>
							<span class="cbw-pill cbw-pill--ok"><?php esc_html_e( 'Active', 'chatbotistic-widget' ); ?></span>
						<?php else : ?>
							<span class="cbw-pill cbw-pill--warn"><?php esc_html_e( 'Free / Not licensed', 'chatbotistic-widget' ); ?></span>
						<?php endif; ?>
					</dd>

					<dt><?php esc_html_e( 'Plan', 'chatbotistic-widget' ); ?></dt>
					<dd><strong><?php echo esc_html( $caps['plan_name'] ?? 'Free' ); ?></strong></dd>

					<dt><?php esc_html_e( 'Widgets', 'chatbotistic-widget' ); ?></dt>
					<dd><?php echo esc_html( self::format_cap( $caps['max_widgets'] ) ); ?></dd>

					<dt><?php esc_html_e( 'WhatsApp Agents', 'chatbotistic-widget' ); ?></dt>
					<dd><?php echo esc_html( self::format_cap( $caps['max_agents'] ) ); ?></dd>

					<dt><?php esc_html_e( 'Websites', 'chatbotistic-widget' ); ?></dt>
					<dd>
						<?php if ( null !== $dom_used ) : ?>
							<?php
							/* translators: 1: domains used, 2: domain cap */
							printf( esc_html__( '%1$d of %2$s used', 'chatbotistic-widget' ), (int) $dom_used, esc_html( self::format_cap( $dom_limit ) ) );
							?>
							<?php if ( $dom_limit > 0 && $dom_used >= $dom_limit ) : ?>
								<br><span class="cbw-pill cbw-pill--warn" style="margin-top:4px;display:inline-block;"><?php esc_html_e( 'At limit — upgrade or deactivate a site to add this one', 'chatbotistic-widget' ); ?></span>
							<?php endif; ?>
						<?php else : ?>
							<?php echo esc_html( self::format_cap( $caps['max_domains'] ) ); ?>
						<?php endif; ?>
					</dd>

					<dt><?php esc_html_e( 'This domain', 'chatbotistic-widget' ); ?></dt>
					<dd><code><?php echo esc_html( $domain ); ?></code><?php echo $is_active ? ' <span class="cbw-pill cbw-pill--ok" style="margin-left:6px;">' . esc_html__( 'registered', 'chatbotistic-widget' ) . '</span>' : ''; ?></dd>

					<?php if ( $instance ) : ?>
						<dt><?php esc_html_e( 'Instance ID', 'chatbotistic-widget' ); ?></dt>
						<dd><code><?php echo esc_html( $instance ); ?></code></dd>
					<?php endif; ?>

					<?php if ( ! empty( $payload['expires_at'] ) ) : ?>
						<dt><?php esc_html_e( 'Renews', 'chatbotistic-widget' ); ?></dt>
						<dd><?php echo esc_html( mysql2date( get_option( 'date_format' ), $payload['expires_at'] ) ); ?></dd>
					<?php endif; ?>

					<?php if ( ! empty( $payload['customer_email'] ) ) : ?>
						<dt><?php esc_html_e( 'Account', 'chatbotistic-widget' ); ?></dt>
						<dd><?php echo esc_html( $payload['customer_email'] ); ?></dd>
					<?php endif; ?>

					<?php if ( $last ) : ?>
						<dt><?php esc_html_e( 'Last verified', 'chatbotistic-widget' ); ?></dt>
						<dd><?php echo esc_html( human_time_diff( $last, time() ) . ' ' . __( 'ago', 'chatbotistic-widget' ) ); ?></dd>
					<?php endif; ?>
				</dl>

				<hr />
				<p class="cbw-card__sub">
					<?php esc_html_e( 'Upgrade to a paid plan to add more widgets, more WhatsApp agents and run this plugin on additional websites.', 'chatbotistic-widget' ); ?>
				</p>
				<a class="cbw-btn cbw-btn--primary cbw-btn--block" href="<?php echo esc_url( CBW_PRICING_URL ); ?>" target="_blank" rel="noopener">
					<?php esc_html_e( 'See Plans', 'chatbotistic-widget' ); ?>
				</a>
			</aside>
		</div>

		<?php Admin::footer(); ?>
		<?php
	}

	private static function format_cap( $value ): string {
		$value = (int) $value;
		return -1 === $value ? __( 'Unlimited', 'chatbotistic-widget' ) : (string) $value;
	}

	// ── Handlers ──────────────────────────────────────────────────────────────

	public static function handle_activate(): void {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden' );
		check_admin_referer( 'cbw_activate_license' );

		$key = sanitize_text_field( wp_unslash( $_POST['license_key'] ?? '' ) );
		$res = License::activate( $key );

		if ( is_wp_error( $res ) ) {
			add_settings_error( 'cbw_license', 'cbw_license_err', $res->get_error_message(), 'error' );
		} else {
			$plan = is_array( $res ) ? ( $res['plan_name'] ?? 'your plan' ) : 'your plan';
			add_settings_error( 'cbw_license', 'cbw_license_ok', sprintf( __( 'License activated — welcome to %s!', 'chatbotistic-widget' ), $plan ), 'updated' );
		}
		set_transient( 'settings_errors', get_settings_errors(), 30 );
		wp_safe_redirect( admin_url( 'admin.php?page=' . Admin::LICENSE_SLUG ) );
		exit;
	}

	public static function handle_deactivate(): void {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden' );
		check_admin_referer( 'cbw_deactivate_license' );

		License::deactivate();
		add_settings_error( 'cbw_license', 'cbw_license_off', __( 'License deactivated on this site. Free-tier caps now apply.', 'chatbotistic-widget' ), 'updated' );
		set_transient( 'settings_errors', get_settings_errors(), 30 );
		wp_safe_redirect( admin_url( 'admin.php?page=' . Admin::LICENSE_SLUG ) );
		exit;
	}

	/**
	 * Manual "Refresh widgets" — pulls the current Chatbotistic-side widget
	 * catalog without waiting for the next 12h heartbeat. Useful right
	 * after a customer creates a new widget in app.chatbotistic.com and
	 * wants it to appear in their WP dropdown immediately.
	 */
	public static function handle_refresh_widgets(): void {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden' );
		check_admin_referer( 'cbw_refresh_widgets' );

		$res = License::refresh_widget_list();
		if ( is_wp_error( $res ) ) {
			add_settings_error( 'cbw_license', 'cbw_refresh_err', $res->get_error_message(), 'error' );
		} else {
			$count = is_array( $res ) ? count( $res ) : 0;
			add_settings_error(
				'cbw_license',
				'cbw_refresh_ok',
				/* translators: %d: number of widgets fetched */
				sprintf( _n( 'Refreshed — %d widget loaded from your account.', 'Refreshed — %d widgets loaded from your account.', $count, 'chatbotistic-widget' ), $count ),
				'updated'
			);
		}
		set_transient( 'settings_errors', get_settings_errors(), 30 );
		wp_safe_redirect( admin_url( 'admin.php?page=' . Admin::LICENSE_SLUG ) );
		exit;
	}

	/**
	 * Reachability test — sends a heartbeat to /licenseistic/v1/heartbeat
	 * with the saved key (or a probe ping if no key). Reports whether the
	 * license server is reachable without changing any local state.
	 */
	public static function handle_test_connection(): void {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden' );
		check_admin_referer( 'cbw_test_license' );

		$key = License::get_key();
		if ( '' === $key ) {
			add_settings_error( 'cbw_license', 'cbw_test_no_key', __( 'No license key is saved on this site yet. Paste a key first, then activate.', 'chatbotistic-widget' ), 'error' );
		} else {
			// run_heartbeat() is throttled to once per ~11h; bypass by clearing the marker.
			delete_option( License::OPT_LASTSEEN );
			License::run_heartbeat();
			$state = License::grace_state();
			if ( $state['active'] ) {
				add_settings_error(
					'cbw_license',
					'cbw_test_warn',
					/* translators: %s: human time until grace expires */
					sprintf( __( 'License server unreachable. Premium features stay on for %s before falling back to Free.', 'chatbotistic-widget' ), human_time_diff( time(), max( time(), (int) $state['expires'] ) ) ),
					'error'
				);
			} elseif ( License::is_active() ) {
				add_settings_error( 'cbw_license', 'cbw_test_ok', __( 'Connection OK — license is active and the server responded.', 'chatbotistic-widget' ), 'updated' );
			} else {
				add_settings_error( 'cbw_license', 'cbw_test_inactive', __( 'License server responded but reported the key as inactive. Check the key, or open a support ticket.', 'chatbotistic-widget' ), 'error' );
			}
		}
		set_transient( 'settings_errors', get_settings_errors(), 30 );
		wp_safe_redirect( admin_url( 'admin.php?page=' . Admin::LICENSE_SLUG ) );
		exit;
	}
}
