<?php
/**
 * Zero-touch configuration.
 *
 * After the theme + plugins are installed, this wires the cross-product glue
 * that no single plugin can set on its own:
 *
 *   1. Points Memberistic's page-ID settings at the theme's branded pages
 *      (checkout, account, login, plans, thank-you, payment-failed, renewal)
 *      so the membership + Stripe flow uses Chatbotistic pages — including the
 *      Stripe success / cancel redirect URLs, which Memberistic builds from
 *      thank_you_page_id / failed_payment_page_id.
 *   2. Surfaces a Setup status screen (Appearance → Chatbotistic Setup) that
 *      reports what was auto-configured and the few things that need a secret
 *      only the site owner has (Stripe keys, Google key, backend API key).
 *
 * Everything here is idempotent and only fills values that are still empty, so
 * it never clobbers a choice the admin has made and self-heals if a plugin is
 * activated after the theme.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/**
 * Memberistic page-setting key => theme page slug.
 *
 * @return array<string,string>
 */
function cb_autoconfig_page_map() {
	return array(
		'plans_page_id'          => 'pricing',
		'checkout_page_id'       => 'checkout',
		'account_page_id'        => 'account',
		'login_page_id'          => 'login',
		'renewal_page_id'        => 'upgrade',
		'thank_you_page_id'      => 'payment-success',
		'failed_payment_page_id' => 'payment-failed',
	);
}

/**
 * Map Memberistic's page-ID settings to the theme's branded pages.
 *
 * Only fills keys that are currently empty, so an admin override always wins.
 */
function cb_autoconfig_member_pages() {
	if ( ! function_exists( 'memberistic_get_setting' ) ) {
		return; // Memberistic not active yet — retry on a later admin load.
	}

	// Read the raw stored option without the brand filter so we don't persist
	// the filtered brand keys back into the database.
	remove_filter( 'option_memberistic_settings', 'cb_memberistic_filter_settings' );
	$settings = get_option( 'memberistic_settings', array() );
	add_filter( 'option_memberistic_settings', 'cb_memberistic_filter_settings' );

	if ( ! is_array( $settings ) ) {
		$settings = array();
	}

	$changed = false;
	foreach ( cb_autoconfig_page_map() as $key => $slug ) {
		if ( ! empty( $settings[ $key ] ) ) {
			continue;
		}
		$page = get_page_by_path( $slug );
		if ( $page && 'publish' === get_post_status( $page ) ) {
			$settings[ $key ] = (int) $page->ID;
			$changed          = true;
		}
	}

	if ( $changed ) {
		update_option( 'memberistic_settings', $settings );
	}
}
add_action( 'admin_init', 'cb_autoconfig_member_pages', 6 ); // After cb_maybe_reprovision_on_update (5).
add_action( 'after_switch_theme', 'cb_autoconfig_member_pages', 20 );

/**
 * Build the setup checklist.
 *
 * Each item: label, state (done|todo|secret|info), note, optional action [url,label].
 *
 * @return array<int,array<string,mixed>>
 */
function cb_setup_status() {
	$items = array();

	// 1. Theme pages.
	$pages_ok = (bool) get_page_by_path( 'login' ) && (bool) get_page_by_path( 'account' );
	$items[] = array(
		'label' => __( 'Branded pages provisioned', 'chatbotistic' ),
		'state' => $pages_ok ? 'done' : 'todo',
		'note'  => $pages_ok
			? __( 'Login, account, pricing, checkout and legal pages are created.', 'chatbotistic' )
			: __( 'Re-save Settings → Permalinks, or re-activate the theme, to create pages.', 'chatbotistic' ),
	);

	// 2. Memberistic.
	$mem_active = defined( 'MEMBERISTIC_VERSION' );
	$plans      = array();
	if ( $mem_active && class_exists( '\WordPressistic\Memberistic\Database\Plans_Repository' ) ) {
		$plans = (array) \WordPressistic\Memberistic\Database\Plans_Repository::get_all( array( 'status' => 'active' ) );
	}
	$items[] = array(
		'label' => __( 'Memberistic membership plans', 'chatbotistic' ),
		'state' => ( $mem_active && $plans ) ? 'done' : ( $mem_active ? 'todo' : 'todo' ),
		'note'  => ! $mem_active
			? __( 'Activate the Memberistic Membership Solutions plugin.', 'chatbotistic' )
			: ( $plans
				? sprintf( _n( '%d active plan ready.', '%d active plans ready.', count( $plans ), 'chatbotistic' ), count( $plans ) )
				: __( 'No active plans yet — Memberistic seeds Free/Pro/Agency on first install.', 'chatbotistic' ) ),
		'action' => $mem_active ? array( admin_url( 'admin.php?page=memberistic-plans' ), __( 'Manage plans', 'chatbotistic' ) ) : null,
	);

	// 3. Branded pages linked into Memberistic.
	$linked = false;
	if ( function_exists( 'memberistic_get_setting' ) ) {
		$linked = (int) memberistic_get_setting( 'account_page_id', 0 )
			&& (int) memberistic_get_setting( 'checkout_page_id', 0 )
			&& (int) memberistic_get_setting( 'login_page_id', 0 );
	}
	$items[] = array(
		'label' => __( 'Membership pages auto-linked', 'chatbotistic' ),
		'state' => $linked ? 'done' : ( $mem_active ? 'todo' : 'info' ),
		'note'  => $linked
			? __( 'Checkout, account, login and Stripe redirect pages point at the branded pages.', 'chatbotistic' )
			: __( 'Will link automatically once Memberistic is active and pages exist.', 'chatbotistic' ),
	);

	// 4. Licenseistic.
	$lsi_active = defined( 'WPISTIC_LSI_VERSION' );
	$items[] = array(
		'label'  => __( 'Licenseistic license engine', 'chatbotistic' ),
		'state'  => $lsi_active ? 'done' : 'todo',
		'note'   => $lsi_active
			? __( 'License issuing and validation REST API is live.', 'chatbotistic' )
			: __( 'Activate the Licenseistic plugin.', 'chatbotistic' ),
	);

	// 5. Bridge + license product.
	$bridge_active = defined( 'MLB_VERSION' );
	$product_id    = (int) get_option( 'mlb_plan_product_id', 0 );
	$items[] = array(
		'label' => __( 'Membership → License bridge', 'chatbotistic' ),
		'state' => ( $bridge_active && $product_id ) ? 'done' : ( $bridge_active ? 'todo' : 'todo' ),
		'note'  => ! $bridge_active
			? __( 'Activate the Memberistic → Licenseistic Bridge plugin.', 'chatbotistic' )
			: ( $product_id
				? __( 'The "Chatbotistic Widget" license product is linked and issuing keys on plan activation.', 'chatbotistic' )
				: __( 'Activate Memberistic + Licenseistic so the bridge can create the license product.', 'chatbotistic' ) ),
	);

	// 6. Stripe (secret — owner only).
	$stripe_ready = false;
	if ( function_exists( 'memberistic_get_setting' ) ) {
		$mode = (string) memberistic_get_setting( 'stripe_mode', 'test' );
		$key  = 'live' === $mode
			? (string) memberistic_get_setting( 'stripe_live_secret_key', '' )
			: (string) memberistic_get_setting( 'stripe_test_secret_key', '' );
		$stripe_ready = 'yes' === (string) memberistic_get_setting( 'stripe_enabled', 'no' ) && '' !== $key;
	}
	$items[] = array(
		'label'  => __( 'Stripe payments (your keys)', 'chatbotistic' ),
		'state'  => $stripe_ready ? 'done' : 'secret',
		'note'   => $stripe_ready
			? __( 'Stripe is enabled and a secret key is set.', 'chatbotistic' )
			: __( 'Enter your Stripe keys and enable payments — only you hold these.', 'chatbotistic' ),
		'action' => $mem_active ? array( admin_url( 'admin.php?page=memberistic-settings' ), __( 'Open payment settings', 'chatbotistic' ) ) : null,
	);

	// 7. Widget plugin (for the host site; customers install their own copy).
	$items[] = array(
		'label' => __( 'Chatbotistic Widget plugin', 'chatbotistic' ),
		'state' => defined( 'CBW_VERSION' ) ? 'done' : 'info',
		'note'  => defined( 'CBW_VERSION' )
			? __( 'Active. Customers paste their license key to activate it on their own sites.', 'chatbotistic' )
			: __( 'Optional on this site — your customers install it on theirs.', 'chatbotistic' ),
	);

	// 8. Connector (backend API key — secret).
	$items[] = array(
		'label'  => __( 'Chatbotistic Connector (backend API key)', 'chatbotistic' ),
		'state'  => defined( 'CBC_VERSION' ) ? 'secret' : 'info',
		'note'   => defined( 'CBC_VERSION' )
			? __( 'Active — add your backend API credentials in its settings.', 'chatbotistic' )
			: __( 'Activate the Connector plugin and add your backend API key.', 'chatbotistic' ),
	);

	// 9. Insightistic (Google service-account — secret).
	$items[] = array(
		'label'  => __( 'Insightistic analytics (Google key)', 'chatbotistic' ),
		'state'  => defined( 'INSIGHTISTIC_VERSION' ) ? 'secret' : 'info',
		'note'   => defined( 'INSIGHTISTIC_VERSION' )
			? __( 'Active — add your Google service-account key to pull GA4 / Search Console data.', 'chatbotistic' )
			: __( 'Optional — activate Insightistic for analytics in wp-admin.', 'chatbotistic' ),
	);

	return $items;
}

/**
 * Register the Setup status page under Appearance.
 */
function cb_setup_menu() {
	add_theme_page(
		__( 'Chatbotistic Setup', 'chatbotistic' ),
		__( 'Chatbotistic Setup', 'chatbotistic' ),
		'manage_options',
		'chatbotistic-setup',
		'cb_setup_render'
	);
}
add_action( 'admin_menu', 'cb_setup_menu' );

/**
 * Render the Setup status page.
 */
function cb_setup_render() {
	$items  = cb_setup_status();
	$colors = array(
		'done'   => array( '#1a7f37', '✓', __( 'Configured', 'chatbotistic' ) ),
		'todo'   => array( '#b32d2e', '!', __( 'Action needed', 'chatbotistic' ) ),
		'secret' => array( '#bd8600', '🔑', __( 'Needs your key', 'chatbotistic' ) ),
		'info'   => array( '#646970', 'i', __( 'Optional', 'chatbotistic' ) ),
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Chatbotistic Setup', 'chatbotistic' ); ?></h1>
		<p style="max-width:760px;font-size:14px;">
			<?php esc_html_e( 'Most of the system configures itself when the theme and plugins are active. The only things that need you are the secrets below — your Stripe keys, backend API key and (optionally) a Google analytics key.', 'chatbotistic' ); ?>
		</p>
		<table class="widefat striped" style="max-width:880px;margin-top:16px;">
			<tbody>
			<?php foreach ( $items as $item ) :
				$c = $colors[ $item['state'] ] ?? $colors['info'];
				?>
				<tr>
					<td style="width:160px;vertical-align:top;padding:14px 12px;">
						<span style="display:inline-block;padding:3px 10px;border-radius:999px;color:#fff;font-size:12px;font-weight:600;background:<?php echo esc_attr( $c[0] ); ?>;">
							<?php echo esc_html( $c[1] . ' ' . $c[2] ); ?>
						</span>
					</td>
					<td style="padding:14px 12px;">
						<strong><?php echo esc_html( $item['label'] ); ?></strong><br>
						<span style="color:#50575e;"><?php echo esc_html( $item['note'] ); ?></span>
						<?php if ( ! empty( $item['action'] ) && is_array( $item['action'] ) ) : ?>
							<br><a href="<?php echo esc_url( $item['action'][0] ); ?>" class="button button-small" style="margin-top:8px;"><?php echo esc_html( $item['action'][1] ); ?></a>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * Nudge the admin to the Setup page while anything still needs action.
 */
function cb_setup_admin_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $screen && isset( $screen->id ) && false !== strpos( (string) $screen->id, 'chatbotistic-setup' ) ) {
		return; // Don't show the nudge on the Setup page itself.
	}
	if ( get_user_meta( get_current_user_id(), 'cb_setup_notice_dismissed', true ) ) {
		return;
	}

	$pending = 0;
	foreach ( cb_setup_status() as $item ) {
		if ( in_array( $item['state'], array( 'todo', 'secret' ), true ) ) {
			$pending++;
		}
	}
	if ( ! $pending ) {
		return;
	}
	$url     = admin_url( 'themes.php?page=chatbotistic-setup' );
	$dismiss = wp_nonce_url( add_query_arg( 'cb_dismiss_setup', '1' ), 'cb_dismiss_setup' );
	?>
	<div class="notice notice-info">
		<p>
			<strong><?php esc_html_e( 'Chatbotistic', 'chatbotistic' ); ?></strong> —
			<?php
			printf(
				/* translators: %d: number of remaining setup items */
				esc_html( _n( '%d setup item still needs your attention.', '%d setup items still need your attention.', $pending, 'chatbotistic' ) ),
				(int) $pending
			);
			?>
			<a href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Open setup', 'chatbotistic' ); ?></a> ·
			<a href="<?php echo esc_url( $dismiss ); ?>"><?php esc_html_e( 'Dismiss', 'chatbotistic' ); ?></a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'cb_setup_admin_notice' );

/**
 * Persist the per-user dismissal of the setup notice.
 */
function cb_setup_dismiss() {
	if ( isset( $_GET['cb_dismiss_setup'] ) && check_admin_referer( 'cb_dismiss_setup' ) ) {
		update_user_meta( get_current_user_id(), 'cb_setup_notice_dismissed', 1 );
		wp_safe_redirect( remove_query_arg( array( 'cb_dismiss_setup', '_wpnonce' ) ) );
		exit;
	}
}
add_action( 'admin_init', 'cb_setup_dismiss' );
