<?php
namespace Chatbotistic_Widget\Admin;

use Chatbotistic_Widget\License;
use Chatbotistic_Widget\API;

defined( 'ABSPATH' ) || exit;

/**
 * Admin bootstrap: menus, asset loading, action plumbing.
 */
final class Admin {

	const MENU_SLUG      = 'chatbotistic-widget';
	const SETTINGS_SLUG  = 'chatbotistic-widget';
	const LICENSE_SLUG   = 'chatbotistic-widget-license';
	const ANALYTICS_SLUG = 'chatbotistic-widget-analytics';

	public function __construct() {
		add_action( 'admin_menu',                          [ $this, 'register_menu' ] );
		add_action( 'admin_enqueue_scripts',               [ $this, 'enqueue_assets' ] );
		add_action( 'admin_post_cbw_save_settings',        [ Settings_Page::class, 'handle_save' ] );
		add_action( 'admin_post_cbw_connect_api',          [ Settings_Page::class, 'handle_connect_api' ] );
		add_action( 'admin_post_cbw_disconnect_api',       [ Settings_Page::class, 'handle_disconnect_api' ] );
		add_action( 'admin_post_cbw_activate_license',     [ License_Page::class,  'handle_activate' ] );
		add_action( 'admin_post_cbw_deactivate_license',   [ License_Page::class,  'handle_deactivate' ] );
		add_action( 'admin_post_cbw_refresh_widgets',      [ License_Page::class,  'handle_refresh_widgets' ] );
		add_action( 'admin_post_cbw_test_license',         [ License_Page::class,  'handle_test_connection' ] );
		add_filter( 'plugin_action_links_' . CBW_PLUGIN_BASENAME, [ $this, 'plugin_action_links' ] );
	}

	public function register_menu(): void {
		$brand = \Chatbotistic_Widget\Brand::label();
		add_menu_page(
			$brand,
			$brand,
			'manage_options',
			self::SETTINGS_SLUG,
			[ Settings_Page::class, 'render' ],
			'dashicons-format-chat',
			58
		);

		add_submenu_page( self::SETTINGS_SLUG, __( 'Widgets', 'chatbotistic-widget' ),   __( 'Widgets', 'chatbotistic-widget' ),   'manage_options', self::SETTINGS_SLUG,  [ Settings_Page::class, 'render' ] );
		add_submenu_page( self::SETTINGS_SLUG, __( 'Analytics', 'chatbotistic-widget' ), __( 'Analytics', 'chatbotistic-widget' ), 'manage_options', self::ANALYTICS_SLUG, [ Analytics_Page::class, 'render' ] );
		add_submenu_page( self::SETTINGS_SLUG, __( 'License', 'chatbotistic-widget' ),   __( 'License', 'chatbotistic-widget' ),   'manage_options', self::LICENSE_SLUG,   [ License_Page::class, 'render' ] );
	}

	public function plugin_action_links( array $links ): array {
		$activate = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=' . self::LICENSE_SLUG ) ),
			License::is_active() ? esc_html__( 'License', 'chatbotistic-widget' ) : '<strong style="color:#d63638;">' . esc_html__( 'Activate License', 'chatbotistic-widget' ) . '</strong>'
		);
		$settings = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=' . self::SETTINGS_SLUG ) ),
			esc_html__( 'Settings', 'chatbotistic-widget' )
		);
		array_unshift( $links, $settings, $activate );
		return $links;
	}

	public function enqueue_assets( string $hook ): void {
		if ( ! str_contains( $hook, self::MENU_SLUG ) ) return;

		wp_enqueue_style( 'cbw-admin', CBW_PLUGIN_URL . 'assets/css/admin.css', [], CBW_VERSION );
		wp_enqueue_script( 'cbw-admin', CBW_PLUGIN_URL . 'assets/js/admin.js', [], CBW_VERSION, true );
		wp_localize_script( 'cbw-admin', 'cbwAdmin', [
			'i18n' => [
				'confirm_remove'     => __( 'Remove this row?', 'chatbotistic-widget' ),
				'confirm_deactivate' => __( 'Deactivate this license? Free-tier caps (1 widget · 1 agent · 1 domain) will apply.', 'chatbotistic-widget' ),
				'copied'             => __( 'Copied!', 'chatbotistic-widget' ),
				'free_limit'         => __( 'Free plan: only 1 widget. Upgrade to add more.', 'chatbotistic-widget' ),
			],
			'free_limits' => License::FREE_CAPS,
			'caps'        => License::get_caps(),
		] );
	}

	// ── Reusable admin chrome ────────────────────────────────────────────────

	public static function header( string $title ): void {
		$caps         = License::get_caps();
		$plan         = $caps['plan_name'] ?? 'Free';
		$tier         = License::get_tier();
		$is_active    = License::is_active();
		$api_connected = API::is_connected();
		$brand_label   = \Chatbotistic_Widget\Brand::label();
		$brand_tagline = \Chatbotistic_Widget\Brand::tagline();
		$brand_logo    = \Chatbotistic_Widget\Brand::logo_url();
		?>
		<div class="cbw-shell">
			<div class="cbw-topbar">
				<div class="cbw-topbar__brand">
					<img class="cbw-logo" src="<?php echo esc_url( $brand_logo ); ?>" alt="<?php echo esc_attr( $brand_label ); ?>" />
					<div class="cbw-topbar__title">
						<strong><?php echo esc_html( $brand_label ); ?></strong>
						<?php if ( $brand_tagline ) : ?>
							<span class="cbw-tagline"><?php echo esc_html( $brand_tagline ); ?></span>
						<?php endif; ?>
					</div>
				</div>
				<div class="cbw-topbar__meta">
					<span class="cbw-plan-pill cbw-plan-pill--<?php echo esc_attr( $tier ); ?>"><?php echo esc_html( $plan ); ?></span>
					<?php if ( $is_active ) : ?>
						<span class="cbw-pill cbw-pill--ok"><?php esc_html_e( 'Licensed', 'chatbotistic-widget' ); ?></span>
					<?php else : ?>
						<a class="cbw-pill cbw-pill--warn" href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::LICENSE_SLUG ) ); ?>">
							<?php esc_html_e( 'Activate License', 'chatbotistic-widget' ); ?> →
						</a>
					<?php endif; ?>
					<?php if ( $api_connected ) : ?>
						<span class="cbw-pill cbw-pill--ok"><?php esc_html_e( 'API Connected', 'chatbotistic-widget' ); ?></span>
					<?php else : ?>
						<span class="cbw-pill cbw-pill--muted"><?php esc_html_e( 'API Disconnected', 'chatbotistic-widget' ); ?></span>
					<?php endif; ?>
				</div>
			</div>

			<nav class="cbw-tabs">
				<?php
				$current = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : self::SETTINGS_SLUG; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$tabs    = [
					self::SETTINGS_SLUG  => __( 'Widgets',   'chatbotistic-widget' ),
					self::ANALYTICS_SLUG => __( 'Analytics', 'chatbotistic-widget' ),
					self::LICENSE_SLUG   => __( 'License',   'chatbotistic-widget' ),
				];
				foreach ( $tabs as $slug => $label ) {
					printf(
						'<a class="cbw-tab%s" href="%s">%s</a>',
						$current === $slug ? ' is-active' : '',
						esc_url( admin_url( 'admin.php?page=' . $slug ) ),
						esc_html( $label )
					);
				}
				?>
			</nav>

			<h1 class="cbw-page-title"><?php echo esc_html( $title ); ?></h1>
		<?php
	}

	public static function footer(): void {
		$brand_label    = \Chatbotistic_Widget\Brand::label();
		$brand_homepage = \Chatbotistic_Widget\Brand::homepage();
		$support_email  = \Chatbotistic_Widget\Brand::support_email();
		?>
			<div class="cbw-footer">
				<span><?php echo esc_html( $brand_label ); ?> v<?php echo esc_html( CBW_VERSION ); ?></span>
				<?php if ( $brand_homepage ) : ?>
					<span>·</span>
					<a href="<?php echo esc_url( $brand_homepage ); ?>" target="_blank" rel="noopener"><?php echo esc_html( wp_parse_url( $brand_homepage, PHP_URL_HOST ) ?: $brand_homepage ); ?></a>
				<?php endif; ?>
				<?php if ( $support_email ) : ?>
					<span>·</span>
					<a href="mailto:<?php echo esc_attr( $support_email ); ?>"><?php esc_html_e( 'Support', 'chatbotistic-widget' ); ?></a>
				<?php endif; ?>
			</div>
		</div><!-- .cbw-shell -->
		<?php
	}

	public static function upgrade_banner_if_free(): void {
		if ( License::is_active() && 'free' !== License::get_tier() ) return;
		?>
		<div class="cbw-upgrade-banner">
			<div class="cbw-upgrade-banner__icon">★</div>
			<div class="cbw-upgrade-banner__body">
				<strong><?php esc_html_e( 'You\'re on the Free plan.', 'chatbotistic-widget' ); ?></strong>
				<?php esc_html_e( '1 widget · 1 agent · 1 website. Create a free Chatbotistic account to see detailed analytics on this dashboard, then upgrade any time for more widgets, agents and AI knowledge training.', 'chatbotistic-widget' ); ?>
			</div>
			<div class="cbw-upgrade-banner__actions">
				<a class="cbw-btn cbw-btn--primary" href="<?php echo esc_url( CBW_REGISTER_URL ); ?>" target="_blank" rel="noopener">
					<?php esc_html_e( 'Create Free Account', 'chatbotistic-widget' ); ?>
				</a>
				<a class="cbw-btn cbw-btn--ghost" href="<?php echo esc_url( CBW_PRICING_URL ); ?>" target="_blank" rel="noopener">
					<?php esc_html_e( 'See Pricing', 'chatbotistic-widget' ); ?>
				</a>
			</div>
		</div>
		<?php
	}
}
