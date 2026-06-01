<?php
/**
 * Addons showcase template.
 *
 * @package Insightistic_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$addons = array(
	array(
		'icon'   => '🛒',
		'name'   => __( 'WooCommerce Analytics', 'insightistic' ),
		'desc'   => __( 'Deep eCommerce metrics: product performance, cart abandonment, funnel reports and cohort analysis.', 'insightistic' ),
		'type'   => 'Pro',
		'status' => 'coming_soon',
	),
	array(
		'icon'   => '🔍',
		'name'   => __( 'Search Console Pro', 'insightistic' ),
		'desc'   => __( 'Advanced keyword tracking, rank monitoring, and CTR improvement recommendations.', 'insightistic' ),
		'type'   => 'Pro',
		'status' => 'coming_soon',
	),
	array(
		'icon'   => '🖱️',
		'name'   => __( 'Heatmaps & Click Tracking', 'insightistic' ),
		'desc'   => __( 'Visual heatmaps, click maps and scroll maps — all inside your WordPress dashboard.', 'insightistic' ),
		'type'   => 'Pro',
		'status' => 'coming_soon',
	),
	array(
		'icon'   => '📧',
		'name'   => __( 'Email Reports', 'insightistic' ),
		'desc'   => __( 'Automatically send weekly or monthly analytics summaries to your inbox.', 'insightistic' ),
		'type'   => 'Free',
		'status' => 'coming_soon',
	),
	array(
		'icon'   => '⚡',
		'name'   => __( 'Custom Events Builder', 'insightistic' ),
		'desc'   => __( 'Visual drag-and-drop interface to set up custom GA4 events without coding.', 'insightistic' ),
		'type'   => 'Pro',
		'status' => 'coming_soon',
	),
	array(
		'icon'   => '🏷️',
		'name'   => __( 'White Label', 'insightistic' ),
		'desc'   => __( 'Rebrand Insightistic with your own logo, colors, and plugin name for client sites.', 'insightistic' ),
		'type'   => 'Pro',
		'status' => 'coming_soon',
	),
);
?>
<div class="wrap isp-wrap isp-addons-wrap">

	<div class="isp-header">
		<div class="isp-header-brand">
			<img src="<?php echo esc_url( INSIGHTISTIC_URL . 'assets/images/wordpressistic-logo.png' ); ?>"
				alt="<?php esc_attr_e( 'WordPressistic', 'insightistic' ); ?>"
				class="isp-logo">
			<div>
				<h1 class="isp-header-title"><?php esc_html_e( 'Insightistic Addons', 'insightistic' ); ?></h1>
				<p class="isp-header-sub"><?php esc_html_e( 'Extend your analytics with powerful add-ons', 'insightistic' ); ?></p>
			</div>
		</div>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=insightistic' ) ); ?>" class="isp-btn isp-btn-ghost">
			← <?php esc_html_e( 'Dashboard', 'insightistic' ); ?>
		</a>
	</div>

	<!-- HERO SECTION -->
	<div class="isp-addons-hero">
		<div class="isp-addons-hero-text">
			<h2><?php esc_html_e( 'Extend Insightistic with Addons', 'insightistic' ); ?></h2>
			<p><?php esc_html_e( 'Purpose-built extensions that plug right into your existing setup — same clean interface, more power.', 'insightistic' ); ?></p>
		</div>
		<div class="isp-addons-hero-stats">
			<div class="isp-addon-stat">
				<span class="isp-addon-stat-num">6+</span>
				<span class="isp-addon-stat-lbl"><?php esc_html_e( 'Addons planned', 'insightistic' ); ?></span>
			</div>
			<div class="isp-addon-stat">
				<span class="isp-addon-stat-num">1</span>
				<span class="isp-addon-stat-lbl"><?php esc_html_e( 'Free addon', 'insightistic' ); ?></span>
			</div>
		</div>
	</div>

	<!-- FILTER TABS -->
	<div class="isp-addons-filter">
		<button class="isp-addon-filter-btn isp-addon-filter-active" data-filter="all">
			<?php esc_html_e( 'All', 'insightistic' ); ?>
		</button>
		<button class="isp-addon-filter-btn" data-filter="Free">
			<?php esc_html_e( 'Free', 'insightistic' ); ?>
		</button>
		<button class="isp-addon-filter-btn" data-filter="Pro">
			<?php esc_html_e( 'Pro', 'insightistic' ); ?>
		</button>
		<button class="isp-addon-filter-btn" data-filter="coming_soon">
			<?php esc_html_e( 'Coming Soon', 'insightistic' ); ?>
		</button>
	</div>

	<!-- ADDONS GRID -->
	<div class="isp-addons-grid" id="isp-addons-grid">
		<?php foreach ( $addons as $addon ) :
			$type_class   = 'pro' === strtolower( $addon['type'] ) ? 'isp-addon-type-pro' : 'isp-addon-type-free';
			$status_label = 'coming_soon' === $addon['status'] ? __( 'Coming Soon', 'insightistic' ) : __( 'Install', 'insightistic' );
			$status_class = 'coming_soon' === $addon['status'] ? 'isp-addon-coming' : 'isp-addon-install';
			?>
			<div class="isp-addon-card" data-type="<?php echo esc_attr( $addon['type'] ); ?>" data-status="<?php echo esc_attr( $addon['status'] ); ?>">
				<div class="isp-addon-card-top">
					<div class="isp-addon-icon"><?php echo esc_html( $addon['icon'] ); ?></div>
					<span class="isp-addon-type-badge <?php echo esc_attr( $type_class ); ?>"><?php echo esc_html( $addon['type'] ); ?></span>
				</div>
				<h3 class="isp-addon-name"><?php echo esc_html( $addon['name'] ); ?></h3>
				<p class="isp-addon-desc"><?php echo esc_html( $addon['desc'] ); ?></p>
				<div class="isp-addon-footer">
					<button class="isp-btn <?php echo esc_attr( $status_class ); ?>" disabled>
						<?php echo esc_html( $status_label ); ?>
					</button>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<!-- NOTIFY SECTION -->
	<div class="isp-addons-notify">
		<div class="isp-addons-notify-inner">
			<div class="isp-notify-icon">🚀</div>
			<div>
				<h3><?php esc_html_e( 'Be the first to know', 'insightistic' ); ?></h3>
				<p><?php esc_html_e( "We're building these addons now. Follow us for launch announcements.", 'insightistic' ); ?></p>
				<a href="https://wordpressistic.com" target="_blank" rel="noopener noreferrer" class="isp-btn isp-btn-primary">
					<?php esc_html_e( 'Visit WordPressistic.com', 'insightistic' ); ?>
				</a>
			</div>
		</div>
	</div>

	<div class="isp-footer">
		<p>
			<?php esc_html_e( 'Insightistic', 'insightistic' ); ?> v<?php echo esc_html( INSIGHTISTIC_VERSION ); ?> &bull;
			<a href="https://wordpressistic.com" target="_blank" rel="noopener noreferrer">WordPressistic</a>
		</p>
	</div>

</div><!-- /.isp-wrap -->

<!-- Addon filter is handled by admin.js (initAddonsFilter) — no inline scripts. -->
