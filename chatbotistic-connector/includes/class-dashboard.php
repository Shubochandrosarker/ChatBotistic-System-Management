<?php
/**
 * Front-end member dashboard — the [chatbotistic_dashboard] shortcode.
 *
 * Renders the shell; all data is loaded over AJAX (see class-ajax.php) and
 * painted by assets/dashboard.js. Designed to be embedded inside the
 * Chatbotistic theme's member portal.
 *
 * @package Chatbotistic\Connector
 */

namespace Chatbotistic\Connector;

defined( 'ABSPATH' ) || exit;

class Dashboard {

	/**
	 * Register the shortcode and assets.
	 */
	/** Allowed tab keys the shortcode can preselect. */
	private const VALID_TABS = array( 'widgets', 'analytics', 'leads' );

	public function __construct() {
		add_shortcode( 'chatbotistic_dashboard', array( $this, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
	}

	/**
	 * Register dashboard assets. They are *enqueued* in render() so they
	 * load whether the shortcode sits in post content or a theme template.
	 */
	public function register_assets(): void {
		$css = CBC_DIR . 'assets/dashboard.css';
		$js  = CBC_DIR . 'assets/dashboard.js';

		wp_register_style( 'cbc-dashboard', CBC_URL . 'assets/dashboard.css', array(), is_readable( $css ) ? (string) filemtime( $css ) : CBC_VERSION );
		wp_register_script( 'cbc-dashboard', CBC_URL . 'assets/dashboard.js', array(), is_readable( $js ) ? (string) filemtime( $js ) : CBC_VERSION, true );

		wp_localize_script( 'cbc-dashboard', 'CBC', array(
			'ajax'  => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'cbc_nonce' ),
			'plans' => esc_url( home_url( '/pricing/' ) ),
		) );
	}

	/**
	 * Render the dashboard shell.
	 *
	 * Shortcode attributes:
	 *   default_tab   widgets | analytics | leads   (default: widgets)
	 *                 Lets the theme deep-link into the right tab from a
	 *                 sidebar item, e.g. [chatbotistic_dashboard default_tab="leads"].
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render( $atts = array() ): string {
		if ( ! is_user_logged_in() ) {
			return '<div class="cbc-gate"><p>' . esc_html__( 'Please sign in to open your dashboard.', 'chatbotistic-connector' ) . '</p></div>';
		}

		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}

		$atts = shortcode_atts( array( 'default_tab' => 'widgets' ), is_array( $atts ) ? $atts : array(), 'chatbotistic_dashboard' );
		$default_tab = sanitize_key( (string) $atts['default_tab'] );
		if ( ! in_array( $default_tab, self::VALID_TABS, true ) ) {
			$default_tab = 'widgets';
		}

		// Enqueue here so assets load even when the shortcode is rendered
		// from a theme template rather than post content.
		wp_enqueue_style( 'cbc-dashboard' );
		wp_enqueue_script( 'cbc-dashboard' );

		ob_start();
		?>
		<div class="cbc-app" id="cbc-app" data-default-tab="<?php echo esc_attr( $default_tab ); ?>">

			<header class="cbc-top">
				<div class="cbc-top__id">
					<span class="cbc-plan" id="cbc-plan"><?php esc_html_e( 'Loading…', 'chatbotistic-connector' ); ?></span>
					<span class="cbc-usage" id="cbc-usage"></span>
				</div>
				<nav class="cbc-tabs" role="tablist">
					<button class="cbc-tab<?php echo 'widgets'   === $default_tab ? ' is-active' : ''; ?>" data-tab="widgets"   role="tab" aria-selected="<?php echo 'widgets'   === $default_tab ? 'true' : 'false'; ?>"><?php esc_html_e( 'Widgets', 'chatbotistic-connector' ); ?></button>
					<button class="cbc-tab<?php echo 'analytics' === $default_tab ? ' is-active' : ''; ?>" data-tab="analytics" role="tab" aria-selected="<?php echo 'analytics' === $default_tab ? 'true' : 'false'; ?>"><?php esc_html_e( 'Analytics', 'chatbotistic-connector' ); ?></button>
					<button class="cbc-tab<?php echo 'leads'     === $default_tab ? ' is-active' : ''; ?>" data-tab="leads"     role="tab" aria-selected="<?php echo 'leads'     === $default_tab ? 'true' : 'false'; ?>"><?php esc_html_e( 'Leads', 'chatbotistic-connector' ); ?></button>
				</nav>
				<a class="cbc-btn cbc-btn--ghost cbc-btn--sm cbc-top__dashboard-link" id="cbc-dashboard-link" href="<?php echo esc_url( apply_filters( 'cb_dashboard_url', Store::dashboard_url(), '' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open Full Dashboard', 'chatbotistic-connector' ); ?></a>
			</header>

			<div class="cbc-notice" id="cbc-notice" role="status" hidden></div>

			<section class="cbc-panel<?php echo 'widgets'   === $default_tab ? ' is-active' : ''; ?>" id="cbc-panel-widgets"   role="tabpanel"<?php echo 'widgets'   === $default_tab ? '' : ' hidden'; ?>>
				<div class="cbc-panel__head">
					<h2><?php esc_html_e( 'Your WhatsApp Widgets', 'chatbotistic-connector' ); ?></h2>
					<button class="cbc-btn cbc-btn--primary" id="cbc-new-widget"><?php esc_html_e( '+ New Widget', 'chatbotistic-connector' ); ?></button>
				</div>
				<div class="cbc-list" id="cbc-widgets">
					<div class="cbc-loading"><?php esc_html_e( 'Loading widgets…', 'chatbotistic-connector' ); ?></div>
				</div>
			</section>

			<section class="cbc-panel<?php echo 'analytics' === $default_tab ? ' is-active' : ''; ?>" id="cbc-panel-analytics" role="tabpanel"<?php echo 'analytics' === $default_tab ? '' : ' hidden'; ?>>
				<div class="cbc-panel__head"><h2><?php esc_html_e( 'Performance — last 30 days', 'chatbotistic-connector' ); ?></h2></div>
				<div id="cbc-analytics"><div class="cbc-loading"><?php esc_html_e( 'Loading analytics…', 'chatbotistic-connector' ); ?></div></div>
			</section>

			<section class="cbc-panel<?php echo 'leads'     === $default_tab ? ' is-active' : ''; ?>" id="cbc-panel-leads"     role="tabpanel"<?php echo 'leads'     === $default_tab ? '' : ' hidden'; ?>>
				<div class="cbc-panel__head"><h2><?php esc_html_e( 'Leads', 'chatbotistic-connector' ); ?></h2></div>
				<div id="cbc-leads"><div class="cbc-loading"><?php esc_html_e( 'Loading leads…', 'chatbotistic-connector' ); ?></div></div>
			</section>

			<div class="cbc-modal" id="cbc-modal" hidden>
				<div class="cbc-modal__box">
					<button class="cbc-modal__x" id="cbc-modal-x" aria-label="<?php esc_attr_e( 'Close', 'chatbotistic-connector' ); ?>">&times;</button>
					<div class="cbc-modal__body" id="cbc-modal-body"></div>
				</div>
			</div>

		</div>
		<?php
		return ob_get_clean();
	}
}
