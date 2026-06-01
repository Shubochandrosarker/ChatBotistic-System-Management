<?php
namespace Chatbotistic_Widget\Admin;

use Chatbotistic_Widget\License;

defined( 'ABSPATH' ) || exit;

final class License_Page {

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) return;
		Admin::header( __( 'License', 'chatbotistic-widget' ) );

		$is_active = License::is_active();
		$caps      = License::get_caps();
		$key       = License::get_key();
		$payload   = get_option( License::OPT_PAYLOAD, [] );
		$last      = (int) get_option( License::OPT_LASTSEEN, 0 );
		$grace     = License::grace_state();
		$domain    = (string) wp_parse_url( home_url(), PHP_URL_HOST );
		$instance  = (string) get_option( License::OPT_INSTANCE, '' );
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
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cbw-form-inline cbw-form-inline--right">
						<input type="hidden" name="action" value="cbw_deactivate_license" />
						<?php wp_nonce_field( 'cbw_deactivate_license' ); ?>
						<button type="submit" class="cbw-btn cbw-btn--ghost cbw-btn--danger js-cbw-confirm-deactivate">
							<?php esc_html_e( 'Deactivate License on this Site', 'chatbotistic-widget' ); ?>
						</button>
					</form>
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
					<dd><?php echo esc_html( self::format_cap( $caps['max_domains'] ) ); ?></dd>

					<dt><?php esc_html_e( 'Connected domain', 'chatbotistic-widget' ); ?></dt>
					<dd><?php echo esc_html( $domain ); ?></dd>

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
}
