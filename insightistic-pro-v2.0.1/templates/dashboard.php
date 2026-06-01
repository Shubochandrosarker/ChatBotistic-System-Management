<?php
/**
 * Dashboard template – tabbed layout.
 *
 * @package Insightistic_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$property_id  = get_option( 'insightistic_pro_property_id' );
$gsc_url      = get_option( 'insightistic_pro_gsc_property_url' );
$psi_key      = get_option( 'insightistic_pro_pagespeed_api_key_enc' );
$ai_enabled   = (int) get_option( 'insightistic_pro_ai_enabled', 0 );
$ai_provider  = get_option( 'insightistic_pro_ai_provider', 'none' );
$default_url  = get_option( 'insightistic_pro_pagespeed_default_url', home_url( '/' ) );
?>
<div class="wrap isp-wrap">

	<!-- ============================================================ HEADER -->
	<div class="isp-header">
		<div class="isp-header-brand">
			<img src="<?php echo esc_url( INSIGHTISTIC_URL . 'assets/images/wordpressistic-logo.png' ); ?>"
				alt="<?php esc_attr_e( 'WordPressistic', 'insightistic' ); ?>"
				class="isp-logo">
			<div>
				<h1 class="isp-header-title"><?php esc_html_e( 'Insightistic Analytics', 'insightistic' ); ?></h1>
				<p class="isp-header-sub"><?php esc_html_e( 'GA4 · Search Console · PageSpeed · AI Insights', 'insightistic' ); ?></p>
			</div>
		</div>
		<div class="isp-header-actions">
			<?php if ( ! $property_id ) : ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=insightistic-settings' ) ); ?>" class="isp-btn isp-btn-primary">
				⚙️ <?php esc_html_e( 'Connect GA4', 'insightistic' ); ?>
			</a>
			<?php endif; ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=insightistic-settings' ) ); ?>" class="isp-btn isp-btn-ghost">
				<?php esc_html_e( 'Settings', 'insightistic' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=insightistic-addons' ) ); ?>" class="isp-btn isp-btn-ghost">
				🧩 <?php esc_html_e( 'Addons', 'insightistic' ); ?>
			</a>
		</div>
	</div>

	<?php if ( ! $property_id ) : ?>
	<!-- ====================================================== SETUP NOTICE -->
	<div class="isp-setup-notice">
		<div class="isp-setup-icon">📊</div>
		<div>
			<h2><?php esc_html_e( 'Connect your Google Analytics 4 property', 'insightistic' ); ?></h2>
			<p><?php esc_html_e( 'Go to Settings and enter your GA4 Property ID, Service Account Email, and Private Key to start seeing your analytics data.', 'insightistic' ); ?></p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=insightistic-settings' ) ); ?>" class="isp-btn isp-btn-primary">
				<?php esc_html_e( 'Open Settings', 'insightistic' ); ?>
			</a>
		</div>
	</div>
	<?php else : ?>

	<!-- ======================================================= DASHBOARD TABS -->
	<div class="isp-dash-tabs">
		<button class="isp-dash-tab isp-dash-tab-active" data-tab="overview">
			📊 <?php esc_html_e( 'Overview', 'insightistic' ); ?>
		</button>
		<button class="isp-dash-tab" data-tab="search-console" <?php echo $gsc_url ? '' : 'title="' . esc_attr__( 'Configure Search Console in Settings', 'insightistic' ) . '"'; ?>>
			🔍 <?php esc_html_e( 'Search Console', 'insightistic' ); ?>
			<?php if ( ! $gsc_url ) : ?><span class="isp-tab-badge"><?php esc_html_e( 'Setup', 'insightistic' ); ?></span><?php endif; ?>
		</button>
		<button class="isp-dash-tab" data-tab="pagespeed" <?php echo $psi_key ? '' : 'title="' . esc_attr__( 'Configure PageSpeed in Settings', 'insightistic' ) . '"'; ?>>
			⚡ <?php esc_html_e( 'PageSpeed', 'insightistic' ); ?>
			<?php if ( ! $psi_key ) : ?><span class="isp-tab-badge"><?php esc_html_e( 'Setup', 'insightistic' ); ?></span><?php endif; ?>
		</button>
	</div>

	<!-- ========================================= TAB: OVERVIEW (GA4) -->
	<div class="isp-tab-content" id="isp-dash-overview">

		<!-- TOOLBAR -->
		<div class="isp-toolbar">
			<div class="isp-toolbar-left">
				<label for="isp-date-range" class="screen-reader-text"><?php esc_html_e( 'Date range', 'insightistic' ); ?></label>
				<select id="isp-date-range" class="isp-select">
					<option value="7"><?php esc_html_e( 'Last 7 days', 'insightistic' ); ?></option>
					<option value="28" selected><?php esc_html_e( 'Last 28 days', 'insightistic' ); ?></option>
					<option value="30"><?php esc_html_e( 'Last 30 days', 'insightistic' ); ?></option>
					<option value="90"><?php esc_html_e( 'Last 90 days', 'insightistic' ); ?></option>
					<option value="180"><?php esc_html_e( 'Last 6 months', 'insightistic' ); ?></option>
				</select>
				<button id="isp-load-data" class="isp-btn isp-btn-primary" aria-live="polite">
					<span class="isp-btn-icon">↺</span>
					<span class="isp-btn-text"><?php esc_html_e( 'Refresh Data', 'insightistic' ); ?></span>
				</button>
			</div>
			<?php if ( $ai_enabled && 'none' !== $ai_provider ) : ?>
			<button id="isp-ai-analyze" class="isp-btn isp-btn-ai" style="display:none;">
				✨ <?php esc_html_e( 'Get AI Insights', 'insightistic' ); ?>
			</button>
			<?php endif; ?>
		</div>

		<!-- ROW 1 STAT CARDS: Sessions, Visitors, Pageviews, Avg Duration -->
		<div id="isp-overview-cards" class="isp-overview-cards isp-overview-cards-8" style="display:none;">
			<div class="isp-stat-card" id="isp-card-sessions">
				<div class="isp-stat-icon isp-icon-blue">📈</div>
				<div class="isp-stat-body">
					<div class="isp-stat-label"><?php esc_html_e( 'Sessions', 'insightistic' ); ?></div>
					<div class="isp-stat-value" id="isp-val-sessions">—</div>
					<div class="isp-stat-change" id="isp-chg-sessions"></div>
				</div>
			</div>
			<div class="isp-stat-card" id="isp-card-users">
				<div class="isp-stat-icon isp-icon-purple">👤</div>
				<div class="isp-stat-body">
					<div class="isp-stat-label"><?php esc_html_e( 'Unique Visitors', 'insightistic' ); ?></div>
					<div class="isp-stat-value" id="isp-val-users">—</div>
					<div class="isp-stat-change" id="isp-chg-users"></div>
				</div>
			</div>
			<div class="isp-stat-card" id="isp-card-pageviews">
				<div class="isp-stat-icon isp-icon-cyan">📄</div>
				<div class="isp-stat-body">
					<div class="isp-stat-label"><?php esc_html_e( 'Pageviews', 'insightistic' ); ?></div>
					<div class="isp-stat-value" id="isp-val-pageviews">—</div>
					<div class="isp-stat-change" id="isp-chg-pageviews"></div>
				</div>
			</div>
			<div class="isp-stat-card" id="isp-card-duration">
				<div class="isp-stat-icon isp-icon-indigo">⏱️</div>
				<div class="isp-stat-body">
					<div class="isp-stat-label"><?php esc_html_e( 'Avg. Session', 'insightistic' ); ?></div>
					<div class="isp-stat-value" id="isp-val-duration">—</div>
					<div class="isp-stat-change" id="isp-chg-duration"></div>
				</div>
			</div>
			<div class="isp-stat-card" id="isp-card-bounce">
				<div class="isp-stat-icon isp-icon-orange">↩️</div>
				<div class="isp-stat-body">
					<div class="isp-stat-label"><?php esc_html_e( 'Bounce Rate', 'insightistic' ); ?></div>
					<div class="isp-stat-value" id="isp-val-bounce">—</div>
					<div class="isp-stat-change" id="isp-chg-bounce"></div>
				</div>
			</div>
			<div class="isp-stat-card" id="isp-card-newreturn">
				<div class="isp-stat-icon isp-icon-teal">🔄</div>
				<div class="isp-stat-body">
					<div class="isp-stat-label"><?php esc_html_e( 'New vs Return', 'insightistic' ); ?></div>
					<div class="isp-stat-value" id="isp-val-newreturn">—</div>
					<div id="isp-newreturn-bar" class="isp-newreturn-bar"></div>
				</div>
			</div>
			<div class="isp-stat-card" id="isp-card-revenue">
				<div class="isp-stat-icon isp-icon-green">💰</div>
				<div class="isp-stat-body">
					<div class="isp-stat-label"><?php esc_html_e( 'Revenue', 'insightistic' ); ?></div>
					<div class="isp-stat-value" id="isp-val-revenue">—</div>
					<div class="isp-stat-change" id="isp-chg-revenue"></div>
				</div>
			</div>
			<div class="isp-stat-card" id="isp-card-tx">
				<div class="isp-stat-icon isp-icon-amber">🛒</div>
				<div class="isp-stat-body">
					<div class="isp-stat-label"><?php esc_html_e( 'Transactions', 'insightistic' ); ?></div>
					<div class="isp-stat-value" id="isp-val-tx">—</div>
					<div class="isp-stat-change" id="isp-chg-tx"></div>
				</div>
			</div>
		</div>

		<!-- CHARTS -->
		<div id="isp-charts" class="isp-charts" style="display:none;">
			<div class="isp-chart-card isp-chart-wide">
				<div class="isp-chart-header">
					<span class="isp-chart-title"><?php esc_html_e( 'Traffic &amp; Revenue Over Time', 'insightistic' ); ?></span>
					<div class="isp-chart-legend">
						<span class="isp-legend-dot isp-dot-blue"></span><?php esc_html_e( 'Sessions', 'insightistic' ); ?>
						<span class="isp-legend-dot isp-dot-green"></span><?php esc_html_e( 'Revenue', 'insightistic' ); ?>
					</div>
				</div>
				<div class="isp-chart-body">
					<canvas id="isp-chart-timeline" aria-label="<?php esc_attr_e( 'Traffic and revenue chart', 'insightistic' ); ?>" role="img"></canvas>
				</div>
			</div>
			<div class="isp-chart-card">
				<div class="isp-chart-header">
					<span class="isp-chart-title"><?php esc_html_e( 'Traffic by Source', 'insightistic' ); ?></span>
				</div>
				<div class="isp-chart-body">
					<canvas id="isp-chart-sources" aria-label="<?php esc_attr_e( 'Traffic sources donut chart', 'insightistic' ); ?>" role="img"></canvas>
				</div>
			</div>
		</div>

		<!-- DETAIL CARDS: Countries + Pages -->
		<div id="isp-detail-cards" class="isp-detail-cards" style="display:none;">
			<div class="isp-detail-card">
				<div class="isp-detail-card-header">
					<span class="isp-detail-card-title">🌍 <?php esc_html_e( 'Top Countries', 'insightistic' ); ?></span>
					<span class="isp-detail-card-sub"><?php esc_html_e( 'by sessions', 'insightistic' ); ?></span>
				</div>
				<ul class="isp-rank-list" id="isp-countries-list">
					<li class="isp-rank-loading"><?php esc_html_e( 'Loading…', 'insightistic' ); ?></li>
				</ul>
			</div>
			<div class="isp-detail-card">
				<div class="isp-detail-card-header">
					<span class="isp-detail-card-title">📄 <?php esc_html_e( 'Top Pages', 'insightistic' ); ?></span>
					<span class="isp-detail-card-sub"><?php esc_html_e( 'by pageviews', 'insightistic' ); ?></span>
				</div>
				<ul class="isp-rank-list" id="isp-pages-list">
					<li class="isp-rank-loading"><?php esc_html_e( 'Loading…', 'insightistic' ); ?></li>
				</ul>
			</div>
		</div>

		<!-- TRAFFIC CHANNELS + TOP POSTS -->
		<div id="isp-content-cards" class="isp-detail-cards" style="display:none;">
			<div class="isp-detail-card">
				<div class="isp-detail-card-header">
					<span class="isp-detail-card-title">📡 <?php esc_html_e( 'Traffic Channels', 'insightistic' ); ?></span>
					<span class="isp-detail-card-sub"><?php esc_html_e( 'by sessions', 'insightistic' ); ?></span>
				</div>
				<div id="isp-channels-table" class="isp-channels-wrap"></div>
			</div>
			<div class="isp-detail-card">
				<div class="isp-detail-card-header">
					<span class="isp-detail-card-title">✍️ <?php esc_html_e( 'Top Posts', 'insightistic' ); ?></span>
					<span class="isp-detail-card-sub"><?php esc_html_e( 'blog content', 'insightistic' ); ?></span>
				</div>
				<ul class="isp-rank-list" id="isp-posts-list">
					<li class="isp-rank-loading"><?php esc_html_e( 'Loading…', 'insightistic' ); ?></li>
				</ul>
			</div>
		</div>

		<!-- ATTRIBUTION TABLE -->
		<div class="isp-section-header" id="isp-table-header" style="display:none;">
			<h2 class="isp-section-title">📊 <?php esc_html_e( 'Source / Medium Attribution', 'insightistic' ); ?></h2>
		</div>
		<div id="isp-data-container" class="isp-data-container">
			<div class="isp-initial-state">
				<div class="isp-spinner-wrap">
					<div class="isp-spinner"></div>
					<p><?php esc_html_e( 'Loading your analytics data…', 'insightistic' ); ?></p>
				</div>
			</div>
		</div>

		<!-- AI INSIGHTS -->
		<div id="isp-ai-insights" class="isp-ai-container" style="display:none;"></div>

	</div><!-- /#isp-dash-overview -->

	<!-- ========================================= TAB: SEARCH CONSOLE -->
	<div class="isp-tab-content" id="isp-dash-search-console" style="display:none;">

		<?php if ( ! $gsc_url ) : ?>
		<div class="isp-setup-notice">
			<div class="isp-setup-icon">🔍</div>
			<div>
				<h2><?php esc_html_e( 'Connect Google Search Console', 'insightistic' ); ?></h2>
				<p><?php esc_html_e( 'Add your Search Console property URL and grant access to your service account to see keyword and ranking data.', 'insightistic' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=insightistic-settings#gsc' ) ); ?>" class="isp-btn isp-btn-primary">
					<?php esc_html_e( 'Configure Search Console', 'insightistic' ); ?>
				</a>
			</div>
		</div>
		<?php else : ?>

		<div class="isp-toolbar">
			<div class="isp-toolbar-left">
				<label for="isp-gsc-date-range" class="screen-reader-text"><?php esc_html_e( 'Date range', 'insightistic' ); ?></label>
				<select id="isp-gsc-date-range" class="isp-select">
					<option value="7"><?php esc_html_e( 'Last 7 days', 'insightistic' ); ?></option>
					<option value="28" selected><?php esc_html_e( 'Last 28 days', 'insightistic' ); ?></option>
					<option value="90"><?php esc_html_e( 'Last 90 days', 'insightistic' ); ?></option>
				</select>
				<button id="isp-load-gsc" class="isp-btn isp-btn-primary" aria-live="polite">
					<span class="isp-btn-icon">↺</span>
					<span class="isp-btn-text"><?php esc_html_e( 'Load Data', 'insightistic' ); ?></span>
				</button>
			</div>
			<div class="isp-gsc-delay-note">
				<span>ℹ️</span>
				<?php esc_html_e( 'Search Console data has a 2–3 day delay and shows up to 16 months.', 'insightistic' ); ?>
			</div>
		</div>

		<!-- GSC STAT CARDS -->
		<div id="isp-gsc-cards" class="isp-overview-cards isp-overview-cards-4" style="display:none;">
			<div class="isp-stat-card">
				<div class="isp-stat-icon isp-icon-blue">🖱️</div>
				<div class="isp-stat-body">
					<div class="isp-stat-label"><?php esc_html_e( 'Total Clicks', 'insightistic' ); ?></div>
					<div class="isp-stat-value" id="isp-gsc-clicks">—</div>
					<div class="isp-stat-change" id="isp-gsc-clicks-chg"></div>
				</div>
			</div>
			<div class="isp-stat-card">
				<div class="isp-stat-icon isp-icon-purple">👁️</div>
				<div class="isp-stat-body">
					<div class="isp-stat-label"><?php esc_html_e( 'Impressions', 'insightistic' ); ?></div>
					<div class="isp-stat-value" id="isp-gsc-impr">—</div>
					<div class="isp-stat-change" id="isp-gsc-impr-chg"></div>
				</div>
			</div>
			<div class="isp-stat-card">
				<div class="isp-stat-icon isp-icon-green">📊</div>
				<div class="isp-stat-body">
					<div class="isp-stat-label"><?php esc_html_e( 'Avg. CTR', 'insightistic' ); ?></div>
					<div class="isp-stat-value" id="isp-gsc-ctr">—</div>
					<div class="isp-stat-change" id="isp-gsc-ctr-chg"></div>
				</div>
			</div>
			<div class="isp-stat-card">
				<div class="isp-stat-icon isp-icon-amber">🏆</div>
				<div class="isp-stat-body">
					<div class="isp-stat-label"><?php esc_html_e( 'Avg. Position', 'insightistic' ); ?></div>
					<div class="isp-stat-value" id="isp-gsc-pos">—</div>
					<div class="isp-stat-change" id="isp-gsc-pos-chg"></div>
				</div>
			</div>
		</div>

		<!-- GSC TABLES -->
		<div id="isp-gsc-tables" class="isp-detail-cards" style="display:none;">
			<div class="isp-detail-card">
				<div class="isp-detail-card-header">
					<span class="isp-detail-card-title">🔑 <?php esc_html_e( 'Top Queries', 'insightistic' ); ?></span>
					<span class="isp-detail-card-sub"><?php esc_html_e( 'by clicks', 'insightistic' ); ?></span>
				</div>
				<div id="isp-gsc-queries" class="isp-table-wrap"></div>
			</div>
			<div class="isp-detail-card">
				<div class="isp-detail-card-header">
					<span class="isp-detail-card-title">📄 <?php esc_html_e( 'Top Pages', 'insightistic' ); ?></span>
					<span class="isp-detail-card-sub"><?php esc_html_e( 'by clicks', 'insightistic' ); ?></span>
				</div>
				<div id="isp-gsc-pages" class="isp-table-wrap"></div>
			</div>
		</div>

		<!-- DEVICE BREAKDOWN -->
		<div id="isp-gsc-devices" class="isp-detail-card isp-device-card" style="display:none;">
			<div class="isp-detail-card-header">
				<span class="isp-detail-card-title">📱 <?php esc_html_e( 'Device Breakdown', 'insightistic' ); ?></span>
				<span class="isp-detail-card-sub"><?php esc_html_e( 'by clicks', 'insightistic' ); ?></span>
			</div>
			<div id="isp-gsc-device-bars" class="isp-device-bars"></div>
		</div>

		<!-- GSC LOADING STATE -->
		<div id="isp-gsc-loading" class="isp-data-container">
			<div class="isp-initial-state">
				<div class="isp-spinner-wrap">
					<div class="isp-spinner"></div>
					<p><?php esc_html_e( 'Click "Load Data" to fetch Search Console data.', 'insightistic' ); ?></p>
				</div>
			</div>
		</div>

		<?php endif; ?>
	</div><!-- /#isp-dash-search-console -->

	<!-- ========================================= TAB: PAGESPEED -->
	<div class="isp-tab-content" id="isp-dash-pagespeed" style="display:none;">

		<?php if ( ! $psi_key ) : ?>
		<div class="isp-setup-notice">
			<div class="isp-setup-icon">⚡</div>
			<div>
				<h2><?php esc_html_e( 'Set Up PageSpeed Insights', 'insightistic' ); ?></h2>
				<p><?php esc_html_e( 'Add your Google Cloud API key to start testing your page performance scores and Core Web Vitals.', 'insightistic' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=insightistic-settings#pagespeed' ) ); ?>" class="isp-btn isp-btn-primary">
					<?php esc_html_e( 'Configure PageSpeed', 'insightistic' ); ?>
				</a>
			</div>
		</div>
		<?php else : ?>

		<div class="isp-toolbar">
			<div class="isp-toolbar-left">
				<label for="isp-psi-url" class="screen-reader-text"><?php esc_html_e( 'URL to test', 'insightistic' ); ?></label>
				<input type="url" id="isp-psi-url" class="isp-input isp-url-input"
					placeholder="<?php echo esc_attr( home_url( '/' ) ); ?>"
					value="<?php echo esc_attr( $default_url ); ?>">
				<button id="isp-run-pagespeed" class="isp-btn isp-btn-primary" aria-live="polite">
					<span class="isp-btn-icon">⚡</span>
					<span class="isp-btn-text"><?php esc_html_e( 'Run Test', 'insightistic' ); ?></span>
				</button>
			</div>
		</div>

		<!-- SPEED SCORE RINGS -->
		<div id="isp-psi-results" style="display:none;">

			<div class="isp-speed-scores">
				<div class="isp-speed-score-card">
					<div class="isp-speed-label">📱 <?php esc_html_e( 'Mobile', 'insightistic' ); ?></div>
					<div class="isp-ring-container">
						<svg class="isp-score-ring" viewBox="0 0 120 120" aria-hidden="true">
							<circle class="isp-ring-track" cx="60" cy="60" r="54"/>
							<circle class="isp-ring-progress" id="isp-mobile-ring" cx="60" cy="60" r="54"/>
						</svg>
						<div class="isp-ring-score" id="isp-mobile-score">—</div>
					</div>
					<div class="isp-speed-score-label" id="isp-mobile-label"></div>
				</div>
				<div class="isp-speed-score-card">
					<div class="isp-speed-label">🖥️ <?php esc_html_e( 'Desktop', 'insightistic' ); ?></div>
					<div class="isp-ring-container">
						<svg class="isp-score-ring" viewBox="0 0 120 120" aria-hidden="true">
							<circle class="isp-ring-track" cx="60" cy="60" r="54"/>
							<circle class="isp-ring-progress" id="isp-desktop-ring" cx="60" cy="60" r="54"/>
						</svg>
						<div class="isp-ring-score" id="isp-desktop-score">—</div>
					</div>
					<div class="isp-speed-score-label" id="isp-desktop-label"></div>
				</div>
				<div class="isp-speed-legend">
					<div class="isp-speed-legend-item"><span class="isp-speed-dot isp-speed-good"></span><?php esc_html_e( '90–100: Good', 'insightistic' ); ?></div>
					<div class="isp-speed-legend-item"><span class="isp-speed-dot isp-speed-moderate"></span><?php esc_html_e( '50–89: Needs Improvement', 'insightistic' ); ?></div>
					<div class="isp-speed-legend-item"><span class="isp-speed-dot isp-speed-poor"></span><?php esc_html_e( '0–49: Poor', 'insightistic' ); ?></div>
				</div>
			</div>

			<!-- CORE WEB VITALS -->
			<div class="isp-cwv-section">
				<div class="isp-section-header">
					<h2 class="isp-section-title">📐 <?php esc_html_e( 'Core Web Vitals', 'insightistic' ); ?></h2>
				</div>
				<div class="isp-cwv-tabs">
					<button class="isp-cwv-tab isp-cwv-tab-active" data-cwv-tab="mobile"><?php esc_html_e( 'Mobile', 'insightistic' ); ?></button>
					<button class="isp-cwv-tab" data-cwv-tab="desktop"><?php esc_html_e( 'Desktop', 'insightistic' ); ?></button>
				</div>
				<div id="isp-cwv-mobile" class="isp-cwv-grid"></div>
				<div id="isp-cwv-desktop" class="isp-cwv-grid" style="display:none;"></div>
			</div>

		</div><!-- /#isp-psi-results -->

		<div id="isp-psi-loading" class="isp-data-container">
			<div class="isp-initial-state">
				<div class="isp-spinner-wrap">
					<div class="isp-spinner"></div>
					<p><?php esc_html_e( 'Enter a URL and click "Run Test" to check your page performance.', 'insightistic' ); ?></p>
				</div>
			</div>
		</div>

		<?php endif; ?>
	</div><!-- /#isp-dash-pagespeed -->

	<?php endif; ?>

	<!-- ============================================================ FOOTER -->
	<div class="isp-footer">
		<p>
			<?php esc_html_e( 'Insightistic', 'insightistic' ); ?> v<?php echo esc_html( INSIGHTISTIC_VERSION ); ?> &bull;
			<a href="https://wordpressistic.com" target="_blank" rel="noopener noreferrer">WordPressistic</a>
		</p>
	</div>

</div><!-- /.isp-wrap -->
