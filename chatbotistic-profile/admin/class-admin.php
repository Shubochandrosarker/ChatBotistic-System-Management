<?php
/**
 * Settings page — surfaces install status + "Reset to defaults" button.
 *
 * @package Chatbotistic\Profile
 */

namespace Chatbotistic\Profile;

defined( 'ABSPATH' ) || exit;

class Admin {

	public function register(): void {
		add_action( 'admin_menu', [ $this, 'menu' ], 60 );
		add_action( 'admin_init', [ $this, 'handle_actions' ] );
		add_action( 'wp_ajax_cbp_reveal_secret', [ $this, 'ajax_reveal_secret' ] );
	}

	/**
	 * AJAX endpoint backing the "Reveal secret" buttons on this page.
	 *
	 * The secret value is intentionally NEVER written into the page's
	 * initial HTML (no data-* attribute, no hidden DOM node holding the
	 * plaintext) — it only exists in the response of this nonce-gated,
	 * manage_options-only request, fetched on demand when an admin
	 * explicitly clicks Reveal/Copy. This keeps the credential out of
	 * page source, browser cache, and anything else that inspects the
	 * static markup rather than making an authenticated request.
	 */
	public function ajax_reveal_secret(): void {
		check_ajax_referer( 'cbp_reveal_secret', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'forbidden', 403 );
		}

		$key = isset( $_POST['key'] ) ? sanitize_key( wp_unslash( $_POST['key'] ) ) : '';
		if ( 'sso' === $key && class_exists( '\\Chatbotistic\\Profile\\SSO_Bridge' ) ) {
			wp_send_json_success( [ 'value' => (string) SSO_Bridge::secret() ] );
		}

		wp_send_json_error( 'unknown_key', 400 );
	}

	public function menu(): void {
		add_submenu_page(
			'chatbotistic-connector',
			'Chatbotistic Profile',
			'Profile',
			'manage_options',
			'chatbotistic-profile',
			[ $this, 'render' ]
		);
	}

	public function handle_actions(): void {
		if ( ! isset( $_POST['cbp_action'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		check_admin_referer( 'cbp_admin' );

		$action = sanitize_key( wp_unslash( $_POST['cbp_action'] ) );
		if ( 'reset' === $action ) {
			$result = Installer::run( true );
			set_transient( 'cbp_admin_notice', sprintf(
				'Reset complete — %d pages created, %d plans created, %d plans updated, %d caps synced.',
				$result['pages_created'], $result['plans_created'], $result['plans_updated'], $result['caps_synced']
			), 30 );
		} elseif ( 'repair' === $action ) {
			$result = Installer::repair();
			$wpcf = isset( $result['wpcf_preset'] ) && is_array( $result['wpcf_preset'] ) ? $result['wpcf_preset'] : array();
			$wpcf_written = array_sum( array_map( 'intval', $wpcf ) );
			set_transient( 'cbp_admin_notice', sprintf(
				'Repair complete (no data deleted) — %d pages created, %d plans created, %d plans updated, %d caps synced, license product #%d. WPCF preset: %d setting(s) written (reply branding %d / auto-responder %d / AI rules %d / capture toggles %d).',
				$result['pages_created'], $result['plans_created'], $result['plans_updated'], $result['caps_synced'], $result['product_id'],
				$wpcf_written,
				$wpcf['reply_branding']   ?? 0,
				$wpcf['autoresponder']    ?? 0,
				$wpcf['ai_rules']         ?? 0,
				$wpcf['capture_toggles']  ?? 0
			), 30 );
		} elseif ( 'sync_caps' === $action ) {
			$n = Plans::sync_bridge_caps();
			set_transient( 'cbp_admin_notice', "Bridge caps synced for {$n} plans.", 30 );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=chatbotistic-profile' ) );
		exit;
	}

	public function render(): void {
		$notice  = get_transient( 'cbp_admin_notice' );
		if ( $notice ) {
			delete_transient( 'cbp_admin_notice' );
		}

		$plans_count  = 0;
		$plans_repo   = '\WordPressistic\Memberistic\Database\Plans_Repository';
		if ( class_exists( $plans_repo ) ) {
			$plans_count = count( (array) $plans_repo::get_all() );
		}
		$caps         = (array) get_option( 'mlb_plan_caps', [] );
		$settings     = (array) get_option( 'memberistic_settings', [] );
		$services     = self::service_status( $settings );

		$page_status = [];
		foreach ( Pages::chatbotistic_pages() as $key => $row ) {
			$post_id = isset( $settings[ $key ] ) ? (int) $settings[ $key ] : 0;
			$page_status[ $key ] = [
				'slug'    => $row['slug'],
				'title'   => $row['title'],
				'post_id' => $post_id,
				'url'     => $post_id ? get_permalink( $post_id ) : '',
				'ok'      => $post_id && 'publish' === get_post_status( $post_id ),
			];
		}
		?>
		<div class="wrap cbp-admin">
			<h1>Chatbotistic Profile</h1>
			<p class="description">One-button configuration for <strong>chatbotistic.com</strong>. Sets up the 4 plans, the 7 member-facing pages, the Bridge caps, branding, and waiver-strip.</p>

			<?php if ( $notice ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $notice ); ?></p></div>
			<?php endif; ?>

			<h2>Stack readiness</h2>
			<table class="widefat striped" style="max-width:780px;">
				<tbody>
					<tr><td><strong>Memberistic active</strong></td><td><?php echo defined( 'MEMBERISTIC_VERSION' ) ? '<span style="color:#1e8e3e;">● Yes (' . esc_html( MEMBERISTIC_VERSION ) . ')</span>' : '<span style="color:#b71c1c;">○ No</span>'; ?></td></tr>
					<tr><td><strong>Licenseistic active</strong></td><td><?php echo defined( 'WPISTIC_LSI_VERSION' ) ? '<span style="color:#1e8e3e;">● Yes (' . esc_html( WPISTIC_LSI_VERSION ) . ')</span>' : '<span style="color:#b71c1c;">○ No</span>'; ?></td></tr>
					<tr><td><strong>M → L Bridge active</strong></td><td><?php echo defined( 'MLB_VERSION' ) ? '<span style="color:#1e8e3e;">● Yes (' . esc_html( MLB_VERSION ) . ')</span>' : '<span style="color:#b71c1c;">○ No</span>'; ?></td></tr>
					<tr><td><strong>Chatbotistic Connector active</strong></td><td><?php echo defined( 'CBC_VERSION' ) ? '<span style="color:#1e8e3e;">● Yes (' . esc_html( CBC_VERSION ) . ')</span>' : '<span style="color:#b71c1c;">○ No</span>'; ?></td></tr>
					<tr><td><strong>Plans in DB</strong></td><td><?php echo esc_html( (string) $plans_count ); ?></td></tr>
					<tr><td><strong>Bridge cap rows configured</strong></td><td><?php echo esc_html( (string) count( $caps ) ); ?></td></tr>
				</tbody>
			</table>

			<h2 style="margin-top:32px;">Services &amp; integrations</h2>
			<table class="widefat striped" style="max-width:780px;">
				<thead><tr><th>Service</th><th>Status</th><th>Detail</th></tr></thead>
				<tbody>
					<?php foreach ( $services as $row ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $row['label'] ); ?></strong></td>
							<td>
								<?php if ( 'ok' === $row['state'] ) : ?>
									<span style="color:#1e8e3e;">● OK</span>
								<?php elseif ( 'warn' === $row['state'] ) : ?>
									<span style="color:#b8860b;">▲ Check</span>
								<?php else : ?>
									<span style="color:#b71c1c;">○ Not set</span>
								<?php endif; ?>
							</td>
							<td>
								<?php echo esc_html( $row['detail'] ); ?>
								<?php if ( ! empty( $row['revealable'] ) ) : ?>
									<div class="cbp-secret-reveal" data-service="<?php echo esc_attr( $row['revealable'] ); ?>">
										<button type="button" class="button button-small cbp-secret-toggle" aria-expanded="false"><?php esc_html_e( 'Reveal secret', 'chatbotistic-profile' ); ?></button>
										<button type="button" class="button button-small cbp-secret-copy"><?php esc_html_e( 'Copy', 'chatbotistic-profile' ); ?></button>
										<span class="cbp-secret-copied" style="display:none;color:#1e8e3e;"><?php esc_html_e( 'Copied!', 'chatbotistic-profile' ); ?></span>
										<span class="cbp-secret-error" style="display:none;color:#b71c1c;"><?php esc_html_e( 'Could not load the secret — reload the page and try again.', 'chatbotistic-profile' ); ?></span>
										<div class="cbp-secret-value" style="display:none;margin-top:4px;">
											<code style="user-select:all;padding:4px 8px;background:#f0f0f1;display:inline-block;word-break:break-all;"></code>
										</div>
									</div>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<h2 style="margin-top:32px;">Member-facing pages</h2>
			<table class="widefat striped" style="max-width:980px;">
				<thead><tr><th>Setting</th><th>Slug</th><th>Title</th><th>Status</th><th>URL</th></tr></thead>
				<tbody>
					<?php foreach ( $page_status as $key => $row ) : ?>
						<tr>
							<td><code><?php echo esc_html( $key ); ?></code></td>
							<td><code>/<?php echo esc_html( $row['slug'] ); ?>/</code></td>
							<td><?php echo esc_html( $row['title'] ); ?></td>
							<td><?php echo $row['ok'] ? '<span style="color:#1e8e3e;">● OK</span>' : '<span style="color:#b71c1c;">○ Missing</span>'; ?></td>
							<td>
								<?php if ( $row['url'] ) : ?>
									<a href="<?php echo esc_url( $row['url'] ); ?>" target="_blank"><?php echo esc_html( $row['url'] ); ?></a>
								<?php else : ?>
									—
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<h2 style="margin-top:32px;">Plan → cap mapping</h2>
			<table class="widefat striped" style="max-width:780px;">
				<thead><tr><th>Plan</th><th>Tier</th><th>Widgets</th><th>Agents</th><th>Domains</th></tr></thead>
				<tbody>
					<?php foreach ( Plans::definitions() as $plan ) :
						$row = $plan['_caps']; ?>
						<tr>
							<td><strong><?php echo esc_html( $plan['name'] ); ?></strong> <code><?php echo esc_html( $plan['slug'] ); ?></code></td>
							<td><?php echo esc_html( $row['tier'] ); ?></td>
							<td><?php echo -1 === $row['max_widgets'] ? 'Unlimited' : esc_html( (string) $row['max_widgets'] ); ?></td>
							<td><?php echo -1 === $row['max_agents'] ? 'Unlimited' : esc_html( (string) $row['max_agents'] ); ?></td>
							<td><?php echo -1 === $row['max_domains'] ? 'Unlimited' : esc_html( (string) $row['max_domains'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<form method="post" action="" style="margin-top:32px;">
				<?php wp_nonce_field( 'cbp_admin' ); ?>
				<p>
					<button type="submit" name="cbp_action" value="repair" class="button button-primary">Run setup / repair (safe)</button>
					<button type="submit" name="cbp_action" value="sync_caps" class="button">Sync Bridge caps now</button>
					<button type="submit" name="cbp_action" value="reset" class="button" onclick="return confirm('Reset to Chatbotistic defaults? This re-creates any missing pages, replaces plans with the 4 Chatbotistic plans (deleting non-Chatbotistic plans that have no active members), and re-syncs bridge caps. Existing memberships are NOT affected.');">Reset to defaults (destructive)</button>
				</p>
				<p class="description">
					<strong>Repair (safe)</strong> is idempotent: it creates any missing pages, plans, mappings and the license product without deleting anything — run it any time after updating plugins. <strong>Reset</strong> additionally removes non-Chatbotistic plans (only those with no active members).
				</p>
			</form>
		</div>
		<script>
		(function () {
			var ajaxUrl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
			var nonce   = <?php echo wp_json_encode( wp_create_nonce( 'cbp_reveal_secret' ) ); ?>;

			// Fetches the secret from the server on first use only — never
			// present in the page's initial HTML. Caches the resolved value
			// on the wrapper element for subsequent toggles/copies within
			// the same page view (a fresh page load re-fetches).
			function fetchSecret( wrap ) {
				var cached = wrap.getAttribute( 'data-loaded' );
				if ( cached ) {
					return Promise.resolve( cached );
				}
				var body = new URLSearchParams();
				body.set( 'action', 'cbp_reveal_secret' );
				body.set( 'nonce', nonce );
				body.set( 'key', wrap.getAttribute( 'data-service' ) || '' );
				return fetch( ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body } )
					.then( function ( r ) { return r.json(); } )
					.then( function ( json ) {
						if ( ! json || ! json.success || typeof json.data.value !== 'string' ) {
							throw new Error( 'bad response' );
						}
						wrap.setAttribute( 'data-loaded', json.data.value );
						return json.data.value;
					} );
			}

			function showError( wrap ) {
				var err = wrap.querySelector( '.cbp-secret-error' );
				if ( err ) {
					err.style.display = 'inline';
					setTimeout( function () { err.style.display = 'none'; }, 3000 );
				}
			}

			document.querySelectorAll( '.cbp-secret-toggle' ).forEach( function ( btn ) {
				btn.addEventListener( 'click', function () {
					var wrap  = btn.closest( '.cbp-secret-reveal' );
					var value = wrap ? wrap.querySelector( '.cbp-secret-value' ) : null;
					var code  = wrap ? wrap.querySelector( '.cbp-secret-value code' ) : null;
					if ( ! wrap || ! value || ! code ) { return; }

					var shown = value.style.display !== 'none';
					if ( shown ) {
						value.style.display = 'none';
						btn.setAttribute( 'aria-expanded', 'false' );
						return;
					}

					fetchSecret( wrap ).then( function ( secret ) {
						code.textContent = secret;
						value.style.display = 'block';
						btn.setAttribute( 'aria-expanded', 'true' );
					} ).catch( function () { showError( wrap ); } );
				} );
			} );

			document.querySelectorAll( '.cbp-secret-copy' ).forEach( function ( btn ) {
				btn.addEventListener( 'click', function () {
					var wrap = btn.closest( '.cbp-secret-reveal' );
					if ( ! wrap ) { return; }

					fetchSecret( wrap ).then( function ( text ) {
						var done = function () {
							var note = wrap.querySelector( '.cbp-secret-copied' );
							if ( note ) {
								note.style.display = 'inline';
								setTimeout( function () { note.style.display = 'none'; }, 1500 );
							}
						};
						if ( navigator.clipboard && navigator.clipboard.writeText ) {
							navigator.clipboard.writeText( text ).then( done );
						} else {
							var tmp = document.createElement( 'textarea' );
							tmp.value = text;
							document.body.appendChild( tmp );
							tmp.select();
							document.execCommand( 'copy' );
							document.body.removeChild( tmp );
							done();
						}
					} ).catch( function () { showError( wrap ); } );
				} );
			} );
		})();
		</script>
		<?php
	}

	/**
	 * Build the services / integrations status rows for the dashboard.
	 *
	 * @param array $settings Memberistic settings.
	 * @return array<int,array{label:string,state:string,detail:string}>
	 */
	private static function service_status( array $settings ): array {
		$rows = [];

		// Contact Form.
		$cf = class_exists( 'WPISTIC_CF_Database' );
		$rows[] = [
			'label'  => 'WPistic Contact Form',
			'state'  => $cf ? 'ok' : 'fail',
			'detail' => $cf ? 'Active — every theme form (contact, demo, support, portal tickets) saves to the submissions table for reply-from-dashboard.' : 'Not active — contact/demo/support forms email only, no DB record.',
		];

		// Newsletter (added in WPCF 1.5.1).
		$nl = class_exists( 'WPISTIC_CF_Newsletter' );
		$rows[] = [
			'label'  => 'Newsletter capture',
			'state'  => $nl ? 'ok' : 'warn',
			'detail' => $nl ? 'Footer newsletter form persists subscribers + supports CSV export from the WPCF admin.' : 'Newsletter subscribes fire an action but are not persisted; upgrade WPistic Contact Form to 1.5.1+.',
		];

		// Stripe (payments for Pro/Agency).
		$stripe_key = '';
		if ( class_exists( '\WordPressistic\Memberistic\Payments\Stripe_Service' ) && method_exists( '\WordPressistic\Memberistic\Payments\Stripe_Service', 'is_enabled' ) ) {
			$stripe_on = \WordPressistic\Memberistic\Payments\Stripe_Service::is_enabled();
		} else {
			$stripe_on = ! empty( $settings['stripe_secret_key'] ) || ! empty( $settings['stripe_publishable_key'] );
		}
		$webhook_on = ! empty( $settings['stripe_webhook_secret'] );
		$rows[] = [
			'label'  => 'Stripe payments',
			'state'  => $stripe_on ? ( $webhook_on ? 'ok' : 'warn' ) : 'fail',
			'detail' => $stripe_on
				? ( $webhook_on ? 'Keys + webhook configured.' : 'Keys set, but webhook secret missing — paid plans won\'t auto-activate.' )
				: 'Not configured — only Free plan can be purchased.',
		];

		// SMTP / outbound mail.
		$smtp = defined( 'WPMS_ON' ) || class_exists( 'PHPMailer\\PHPMailer\\PHPMailer' ) && ( has_filter( 'phpmailer_init' ) );
		$rows[] = [
			'label'  => 'Outbound email (SMTP)',
			'state'  => $smtp ? 'ok' : 'warn',
			'detail' => $smtp ? 'A mailer/SMTP hook is active.' : 'No SMTP plugin detected — wp_mail() uses PHP mail(); add SMTP for reliable delivery.',
		];

		// Cron — Memberistic daily expiry / bridge sync.
		$cron_off = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;
		$next_expire = wp_next_scheduled( 'memberistic_daily_expire_memberships' );
		$rows[] = [
			'label'  => 'Scheduled tasks (WP-Cron)',
			'state'  => $cron_off ? 'warn' : ( $next_expire ? 'ok' : 'warn' ),
			'detail' => $cron_off
				? 'DISABLE_WP_CRON is on — ensure a real system cron hits wp-cron.php.'
				: ( $next_expire ? 'Membership expiry / license sync scheduled.' : 'Daily expiry job not scheduled yet — activate Memberistic or run repair.' ),
		];

		// REST routes (Licenseistic).
		$rest_ok = false;
		if ( function_exists( 'rest_get_server' ) ) {
			$routes  = rest_get_server()->get_routes();
			$rest_ok = isset( $routes['/licenseistic/v1/activate'] ) && isset( $routes['/licenseistic/v1/entitlements'] );
		}
		$rows[] = [
			'label'  => 'Licenseistic REST routes',
			'state'  => $rest_ok ? 'ok' : 'fail',
			'detail' => $rest_ok ? '/activate, /heartbeat, /entitlements registered.' : 'Canonical routes not found — is Licenseistic active with REST enabled?',
		];

		// License product mapping.
		$product_id = (int) get_option( 'mlb_plan_product_id', 0 );
		$rows[] = [
			'label'  => 'License product mapping',
			'state'  => $product_id ? 'ok' : 'fail',
			'detail' => $product_id ? 'Chatbotistic Widget product linked (#' . $product_id . ').' : 'Not linked — run repair to create the license product.',
		];

		// Dashboard SSO shared secret — status only. The value itself is
		// NEVER read into this array/the page HTML; the "Reveal secret" /
		// "Copy" buttons fetch it on demand via ajax_reveal_secret(), so a
		// static view of this page (source, cache, screenshot) never
		// contains the plaintext secret.
		$sso_class = '\\Chatbotistic\\Profile\\SSO_Bridge';
		if ( class_exists( $sso_class ) && method_exists( $sso_class, 'secret_is_constant' ) ) {
			// A non-empty secret always exists once SSO_Bridge boots (it
			// auto-generates one on first read), so this row is really
			// reporting *source*, not presence/absence.
			$sso_is_constant = $sso_class::secret_is_constant();
			$rows[] = [
				'label'      => 'Dashboard SSO',
				'state'      => 'ok',
				'detail'     => $sso_is_constant
					? 'Source: CB_SSO_SHARED_SECRET constant in wp-config.php.'
					: 'Source: auto-generated, stored encrypted in options. Copy it into the dashboard app\'s SSO_SHARED_SECRET env whenever needed.',
				'revealable' => 'sso',
			];
		}

		return $rows;
	}
}
