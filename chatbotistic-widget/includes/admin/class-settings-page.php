<?php
namespace Chatbotistic_Widget\Admin;

use Chatbotistic_Widget\License;
use Chatbotistic_Widget\Targeting;
use Chatbotistic_Widget\API;

defined( 'ABSPATH' ) || exit;

/**
 * Widgets / settings page: default widget key, by-post mapping, by-URL mapping,
 * and the Chatbotistic API connection (email/password).
 */
final class Settings_Page {

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) return;
		Admin::header( __( 'Widget Settings', 'chatbotistic-widget' ) );

		$caps          = License::get_caps();
		$max_widgets   = (int) $caps['max_widgets'];
		$default_key   = Targeting::default_key();
		$by_post       = Targeting::by_post();
		$by_url        = Targeting::by_url();
		$configured    = count( Targeting::configured_keys() );
		$over_quota    = ( -1 !== $max_widgets && $configured > $max_widgets );
		$api_connected = API::is_connected();
		// Auto-fetched widget catalog from /licenseistic/v1/widgets — feeds
		// the default-key dropdown so customers pick from a list instead of
		// pasting a UUID by hand.
		$remote_widgets = method_exists( License::class, 'get_widget_list' ) ? (array) License::get_widget_list() : array();
		?>
		<?php settings_errors( 'cbw_settings' ); ?>
		<?php Admin::upgrade_banner_if_free(); ?>

		<?php if ( $over_quota ) : ?>
			<div class="cbw-notice cbw-notice--warn">
				<strong><?php esc_html_e( 'Quota exceeded.', 'chatbotistic-widget' ); ?></strong>
				<?php
				printf(
					/* translators: 1: configured count, 2: max allowed */
					esc_html__( 'You have %1$d distinct widget keys configured but your plan allows only %2$d. Only the first %2$d will render on the front-end.', 'chatbotistic-widget' ),
					$configured,
					$max_widgets
				);
				?>
				<a href="<?php echo esc_url( CBW_PRICING_URL ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Upgrade →', 'chatbotistic-widget' ); ?></a>
			</div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cbw-form">
			<input type="hidden" name="action" value="cbw_save_settings" />
			<?php wp_nonce_field( 'cbw_save_settings' ); ?>

			<div class="cbw-card">
				<h2 class="cbw-card__title"><?php esc_html_e( 'Default Widget', 'chatbotistic-widget' ); ?></h2>
				<p class="cbw-card__sub"><?php esc_html_e( 'This widget loads on every page that doesn\'t match a more specific rule below. Leave empty to disable the widget site-wide.', 'chatbotistic-widget' ); ?></p>

				<div class="cbw-field" id="cbw-default-key-field">
					<label for="cbw-default-key"><?php esc_html_e( 'Widget Key', 'chatbotistic-widget' ); ?></label>

					<?php if ( ! empty( $remote_widgets ) ) :
						// Cached catalog present → dropdown. The customer's existing
						// pasted key may not be in the catalog yet (e.g. heartbeat
						// hasn't refreshed); preserve it as the selected option so
						// the form still saves correctly.
						$default_in_list = false;
						foreach ( $remote_widgets as $rw ) {
							if ( isset( $rw['key'] ) && $rw['key'] === $default_key ) { $default_in_list = true; break; }
						}
						?>
						<select id="cbw-default-key" name="default_widget_key" class="cbw-input cbw-select" data-cbw-key-select>
							<option value=""><?php esc_html_e( '— No default widget —', 'chatbotistic-widget' ); ?></option>
							<?php foreach ( $remote_widgets as $rw ) :
								$k = isset( $rw['key'] ) ? (string) $rw['key'] : '';
								$n = isset( $rw['name'] ) && '' !== $rw['name'] ? (string) $rw['name'] : $k;
								if ( '' === $k ) continue; ?>
								<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $default_key, $k ); ?>><?php echo esc_html( $n ); ?></option>
							<?php endforeach; ?>
							<?php if ( ! $default_in_list && '' !== $default_key ) : ?>
								<option value="<?php echo esc_attr( $default_key ); ?>" selected><?php echo esc_html( sprintf( /* translators: %s: widget key (UUID) */ __( 'Manually entered: %s', 'chatbotistic-widget' ), $default_key ) ); ?></option>
							<?php endif; ?>
							<option value="__manual__"><?php esc_html_e( '… enter a key manually', 'chatbotistic-widget' ); ?></option>
						</select>
						<input type="text" id="cbw-default-key-manual" name="default_widget_key_manual" class="cbw-input cbw-input--mono" value="" placeholder="e.g. 8f637d8b-9409-4f0b-b2ff-9ae0617191f3" hidden />
						<small class="cbw-hint">
							<?php
							/* translators: %d = widget count */
							echo esc_html( sprintf( _n( '%d widget loaded from your Chatbotistic account.', '%d widgets loaded from your Chatbotistic account.', count( $remote_widgets ), 'chatbotistic-widget' ), count( $remote_widgets ) ) );
							?>
							<?php
							printf(
				/* translators: %s: app.chatbotistic.com link */
								' · ' . esc_html__( 'Manage widgets in %s.', 'chatbotistic-widget' ),
				'<a href="' . esc_url( CBW_APP_BASE_URL ) . '" target="_blank" rel="noopener">app.chatbotistic.com</a>'
							);
							?>
							<?php
							/* Inline refresh button — posts to admin-post.php?action=cbw_refresh_widgets
							   and bounces back to the License screen with a notice. */
							?>
							· <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cbw_refresh_widgets' ), 'cbw_refresh_widgets' ) ); ?>"><?php esc_html_e( 'Refresh now', 'chatbotistic-widget' ); ?></a>
						</small>
						<script>
						(function () {
							var sel = document.querySelector('[data-cbw-key-select]');
							if (!sel) return;
							var manual = document.getElementById('cbw-default-key-manual');
							function sync() {
								var isManual = (sel.value === '__manual__');
								if (manual) {
									manual.hidden = !isManual;
									if (isManual) { manual.focus(); }
								}
							}
							sel.addEventListener('change', sync);
							sync();
						})();
						</script>
					<?php else :
						// No catalog yet → original paste field as fallback.
						?>
						<input type="text" id="cbw-default-key" name="default_widget_key" class="cbw-input cbw-input--mono" value="<?php echo esc_attr( $default_key ); ?>" placeholder="e.g. 8f637d8b-9409-4f0b-b2ff-9ae0617191f3" />
						<small class="cbw-hint">
							<?php
							printf(
				/* translators: %s = app.chatbotistic.com link */
								esc_html__( 'Copy from your widget in %s.', 'chatbotistic-widget' ),
				'<a href="' . esc_url( CBW_APP_BASE_URL ) . '" target="_blank" rel="noopener">app.chatbotistic.com</a>'
							);
							?>
							<?php if ( License::is_active() ) : ?>
								<br><em><?php esc_html_e( 'Your widget list will populate this field as a dropdown once the next license heartbeat completes (every ~12 hours), or you can re-save the License screen to refresh now.', 'chatbotistic-widget' ); ?></em>
							<?php endif; ?>
						</small>
					<?php endif; ?>
				</div>
			</div>

			<div class="cbw-card">
				<div class="cbw-card__head">
					<h2 class="cbw-card__title"><?php esc_html_e( 'Show Widget on Specific Pages', 'chatbotistic-widget' ); ?></h2>
					<button type="button" class="cbw-btn cbw-btn--ghost cbw-btn--compact js-cbw-add-row" data-target="cbw-rules-by-post"
						<?php disabled( -1 !== $max_widgets && count( $by_post ) >= max( 1, $max_widgets * 5 ) ); ?>>
						+ <?php esc_html_e( 'Add Rule', 'chatbotistic-widget' ); ?>
					</button>
				</div>
				<p class="cbw-card__sub"><?php esc_html_e( 'Override the default widget on specific posts or pages.', 'chatbotistic-widget' ); ?></p>

				<table class="cbw-rules-table" id="cbw-rules-by-post" data-row-type="post">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Widget Key', 'chatbotistic-widget' ); ?></th>
							<th><?php esc_html_e( 'Page / Post', 'chatbotistic-widget' ); ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $by_post ) ) : ?>
							<tr class="cbw-empty-row"><td colspan="3"><em><?php esc_html_e( 'No per-page rules yet.', 'chatbotistic-widget' ); ?></em></td></tr>
						<?php else :
							foreach ( $by_post as $row ) self::render_post_row( $row['widget_key'] ?? '', (int) ( $row['post_id'] ?? 0 ) );
						endif; ?>
					</tbody>
				</table>
			</div>

			<div class="cbw-card">
				<div class="cbw-card__head">
					<h2 class="cbw-card__title"><?php esc_html_e( 'Show Widget on URL Patterns', 'chatbotistic-widget' ); ?></h2>
					<button type="button" class="cbw-btn cbw-btn--ghost cbw-btn--compact js-cbw-add-row" data-target="cbw-rules-by-url">+ <?php esc_html_e( 'Add Rule', 'chatbotistic-widget' ); ?></button>
				</div>
				<p class="cbw-card__sub"><?php esc_html_e( 'Wildcards supported. Use * to match anything — e.g. https://example.com/products/* to match every product page.', 'chatbotistic-widget' ); ?></p>

				<table class="cbw-rules-table" id="cbw-rules-by-url" data-row-type="url">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Widget Key', 'chatbotistic-widget' ); ?></th>
							<th><?php esc_html_e( 'URL Pattern', 'chatbotistic-widget' ); ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $by_url ) ) : ?>
							<tr class="cbw-empty-row"><td colspan="3"><em><?php esc_html_e( 'No URL rules yet.', 'chatbotistic-widget' ); ?></em></td></tr>
						<?php else :
							foreach ( $by_url as $row ) self::render_url_row( $row['widget_key'] ?? '', $row['url'] ?? '' );
						endif; ?>
					</tbody>
				</table>
			</div>

			<p class="cbw-form-actions">
				<button type="submit" class="cbw-btn cbw-btn--primary"><?php esc_html_e( 'Save Widget Settings', 'chatbotistic-widget' ); ?></button>
			</p>
		</form>

		<!-- API connection (separate form) -->
		<div class="cbw-card">
			<h2 class="cbw-card__title"><?php esc_html_e( 'Connect Chatbotistic Account', 'chatbotistic-widget' ); ?></h2>
			<p class="cbw-card__sub">
				<?php esc_html_e( 'Connect with your Chatbotistic email + password to unlock the in-dashboard Analytics tab (live leads, referral sources, conversion stats).', 'chatbotistic-widget' ); ?>
			</p>

			<?php if ( $api_connected ) : ?>
				<div class="cbw-row cbw-row--connected">
					<div>
						<span class="cbw-pill cbw-pill--ok"><?php esc_html_e( 'Connected', 'chatbotistic-widget' ); ?></span>
						<code><?php echo esc_html( API::get_email() ); ?></code>
					</div>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="cbw_disconnect_api" />
						<?php wp_nonce_field( 'cbw_disconnect_api' ); ?>
						<button type="submit" class="cbw-btn cbw-btn--ghost cbw-btn--danger"><?php esc_html_e( 'Disconnect', 'chatbotistic-widget' ); ?></button>
					</form>
				</div>
			<?php else : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cbw-form-row">
					<input type="hidden" name="action" value="cbw_connect_api" />
					<?php wp_nonce_field( 'cbw_connect_api' ); ?>

					<div class="cbw-field">
						<label for="cbw-api-email"><?php esc_html_e( 'Email', 'chatbotistic-widget' ); ?></label>
						<input type="email" id="cbw-api-email" name="api_email" required />
					</div>
					<div class="cbw-field">
						<label for="cbw-api-password"><?php esc_html_e( 'Password', 'chatbotistic-widget' ); ?></label>
						<input type="password" id="cbw-api-password" name="api_password" autocomplete="current-password" required />
					</div>
					<div class="cbw-field cbw-field--actions">
						<button type="submit" class="cbw-btn cbw-btn--primary"><?php esc_html_e( 'Connect', 'chatbotistic-widget' ); ?></button>
					</div>
				</form>
				<p class="cbw-hint">
					<?php
					printf(
						/* translators: %s = registration link */
						esc_html__( 'Don\'t have an account yet? %s — free forever.', 'chatbotistic-widget' ),
						'<a href="' . esc_url( CBW_REGISTER_URL ) . '" target="_blank" rel="noopener">' . esc_html__( 'Create one in 30 seconds', 'chatbotistic-widget' ) . '</a>'
					);
					?>
				</p>
			<?php endif; ?>
		</div>

		<!-- Row templates for JS-cloning -->
		<template id="cbw-tpl-row-post"><?php self::render_post_row( '', 0 ); ?></template>
		<template id="cbw-tpl-row-url"><?php self::render_url_row( '', '' ); ?></template>

		<?php Admin::footer(); ?>
		<?php
	}

	private static function render_post_row( string $key, int $post_id ): void {
		$posts = get_posts( [
			'post_type'      => [ 'page', 'post' ],
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		] );
		?>
		<tr class="cbw-rule-row">
			<td><input type="text" name="by_post_widget_key[]" class="cbw-input cbw-input--mono" value="<?php echo esc_attr( $key ); ?>" placeholder="<?php esc_attr_e( 'Widget key', 'chatbotistic-widget' ); ?>" /></td>
			<td>
				<select name="by_post_post_id[]" class="cbw-input">
					<option value=""><?php esc_html_e( '— Select —', 'chatbotistic-widget' ); ?></option>
					<?php foreach ( $posts as $p ) : ?>
						<option value="<?php echo (int) $p->ID; ?>" <?php selected( $post_id, $p->ID ); ?>><?php echo esc_html( $p->post_title . ' (#' . $p->ID . ')' ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
			<td><button type="button" class="cbw-btn cbw-btn--icon cbw-btn--danger js-cbw-remove-row" aria-label="<?php esc_attr_e( 'Remove', 'chatbotistic-widget' ); ?>">✕</button></td>
		</tr>
		<?php
	}

	private static function render_url_row( string $key, string $url ): void {
		?>
		<tr class="cbw-rule-row">
			<td><input type="text" name="by_url_widget_key[]" class="cbw-input cbw-input--mono" value="<?php echo esc_attr( $key ); ?>" placeholder="<?php esc_attr_e( 'Widget key', 'chatbotistic-widget' ); ?>" /></td>
			<td><input type="text" name="by_url_url[]" class="cbw-input" value="<?php echo esc_attr( $url ); ?>" placeholder="https://example.com/products/*" /></td>
			<td><button type="button" class="cbw-btn cbw-btn--icon cbw-btn--danger js-cbw-remove-row" aria-label="<?php esc_attr_e( 'Remove', 'chatbotistic-widget' ); ?>">✕</button></td>
		</tr>
		<?php
	}

	// ── Handlers ──────────────────────────────────────────────────────────────

	public static function handle_save(): void {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden' );
		check_admin_referer( 'cbw_save_settings' );

		// If the dropdown is on `__manual__`, take the value from the
		// adjacent text field instead — that's where the user typed.
		$default_key = sanitize_text_field( wp_unslash( $_POST['default_widget_key'] ?? '' ) );
		if ( '__manual__' === $default_key ) {
			$default_key = sanitize_text_field( wp_unslash( $_POST['default_widget_key_manual'] ?? '' ) );
		}
		Targeting::save_default( $default_key );

		$post_keys = (array) ( $_POST['by_post_widget_key'] ?? [] );
		$post_ids  = (array) ( $_POST['by_post_post_id']    ?? [] );
		$post_rows = [];
		foreach ( $post_keys as $i => $k ) {
			$post_rows[] = [ 'widget_key' => $k, 'post_id' => $post_ids[ $i ] ?? 0 ];
		}
		Targeting::save_by_post( $post_rows );

		$url_keys = (array) ( $_POST['by_url_widget_key'] ?? [] );
		$urls     = (array) ( $_POST['by_url_url']        ?? [] );
		$url_rows = [];
		foreach ( $url_keys as $i => $k ) {
			$url_rows[] = [ 'widget_key' => $k, 'url' => $urls[ $i ] ?? '' ];
		}
		Targeting::save_by_url( $url_rows );

		add_settings_error( 'cbw_settings', 'cbw_settings_saved', __( 'Widget settings saved.', 'chatbotistic-widget' ), 'updated' );
		set_transient( 'settings_errors', get_settings_errors(), 30 );
		wp_safe_redirect( admin_url( 'admin.php?page=' . Admin::SETTINGS_SLUG ) );
		exit;
	}

	public static function handle_connect_api(): void {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden' );
		check_admin_referer( 'cbw_connect_api' );

		$email = sanitize_email( wp_unslash( $_POST['api_email'] ?? '' ) );
		$pass  = (string) wp_unslash( $_POST['api_password'] ?? '' );

		if ( ! is_email( $email ) || '' === $pass ) {
			add_settings_error( 'cbw_settings', 'cbw_api_invalid', __( 'Please enter a valid email and password.', 'chatbotistic-widget' ), 'error' );
		} else {
			API::set_credentials( $email, $pass );
			$tok = API::get_token();
			if ( is_wp_error( $tok ) ) {
				API::clear_credentials();
				add_settings_error( 'cbw_settings', 'cbw_api_failed', $tok->get_error_message(), 'error' );
			} else {
				add_settings_error( 'cbw_settings', 'cbw_api_ok', __( 'Chatbotistic account connected.', 'chatbotistic-widget' ), 'updated' );
			}
		}
		set_transient( 'settings_errors', get_settings_errors(), 30 );
		wp_safe_redirect( admin_url( 'admin.php?page=' . Admin::SETTINGS_SLUG ) );
		exit;
	}

	public static function handle_disconnect_api(): void {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden' );
		check_admin_referer( 'cbw_disconnect_api' );

		API::clear_credentials();
		add_settings_error( 'cbw_settings', 'cbw_api_off', __( 'Chatbotistic account disconnected.', 'chatbotistic-widget' ), 'updated' );
		set_transient( 'settings_errors', get_settings_errors(), 30 );
		wp_safe_redirect( admin_url( 'admin.php?page=' . Admin::SETTINGS_SLUG ) );
		exit;
	}
}
