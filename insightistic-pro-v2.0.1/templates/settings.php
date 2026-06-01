<?php
/**
 * Settings template.
 *
 * @package Insightistic_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

settings_errors( 'insightistic_pro_messages' );

$property_id      = get_option( 'insightistic_pro_property_id', '' );
$api_email        = get_option( 'insightistic_pro_api_email', '' );
$api_key_stored   = (bool) get_option( 'insightistic_pro_api_private_key' );

// GSC.
$gsc_property_url = get_option( 'insightistic_pro_gsc_property_url', '' );

// PageSpeed.
$psi_key_stored   = (bool) get_option( 'insightistic_pro_pagespeed_api_key_enc' );
$psi_default_url  = get_option( 'insightistic_pro_pagespeed_default_url', home_url( '/' ) );

// Engagement.
$engagement_on    = (bool) get_option( 'insightistic_pro_engagement_enabled', 0 );
$measurement_id   = get_option( 'insightistic_pro_measurement_id', '' );
$secret_stored    = (bool) get_option( 'insightistic_pro_measurement_secret' );

// AI.
$ai_enabled       = (int) get_option( 'insightistic_pro_ai_enabled', 0 );
$ai_provider      = get_option( 'insightistic_pro_ai_provider', 'none' );
$openai_stored    = (bool) get_option( 'insightistic_pro_openai_key' );
$gemini_stored    = (bool) get_option( 'insightistic_pro_gemini_key' );
$openrouter_stored = (bool) get_option( 'insightistic_pro_openrouter_key' );
$claude_stored    = (bool) get_option( 'insightistic_pro_claude_key' );
$openai_model     = get_option( 'insightistic_pro_openai_model', 'gpt-4o-mini' );
$gemini_model     = get_option( 'insightistic_pro_gemini_model', 'gemini-1.5-flash' );
$openrouter_model = get_option( 'insightistic_pro_openrouter_model', 'mistralai/mistral-7b-instruct:free' );
$claude_model     = get_option( 'insightistic_pro_claude_model', 'claude-haiku-4-5-20251001' );

// Docs.
$video_guide_url  = get_option( 'insightistic_pro_video_guide_url', '' );
$docs_url         = get_option( 'insightistic_pro_docs_url', '' );

// Active tab (default to GA4, or anchor-driven).
?>
<div class="wrap isp-wrap isp-settings-wrap">

	<div class="isp-header">
		<div class="isp-header-brand">
			<img src="<?php echo esc_url( INSIGHTISTIC_URL . 'assets/images/wordpressistic-logo.png' ); ?>"
				alt="<?php esc_attr_e( 'WordPressistic', 'insightistic' ); ?>"
				class="isp-logo">
			<div>
				<h1 class="isp-header-title"><?php esc_html_e( 'Insightistic Settings', 'insightistic' ); ?></h1>
				<p class="isp-header-sub"><?php esc_html_e( 'Configure connections, tracking, and AI insights', 'insightistic' ); ?></p>
			</div>
		</div>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=insightistic' ) ); ?>" class="isp-btn isp-btn-ghost">
			← <?php esc_html_e( 'Dashboard', 'insightistic' ); ?>
		</a>
	</div>

	<!-- SETTINGS TABS -->
	<div class="isp-settings-nav" id="isp-settings-nav">
		<button class="isp-tab-btn isp-tab-active" data-tab="ga4">
			📡 <?php esc_html_e( 'GA4', 'insightistic' ); ?>
		</button>
		<button class="isp-tab-btn" data-tab="gsc" id="isp-stab-gsc">
			🔍 <?php esc_html_e( 'Search Console', 'insightistic' ); ?>
		</button>
		<button class="isp-tab-btn" data-tab="pagespeed" id="isp-stab-pagespeed">
			⚡ <?php esc_html_e( 'PageSpeed', 'insightistic' ); ?>
		</button>
		<button class="isp-tab-btn" data-tab="engagement">
			🎯 <?php esc_html_e( 'Engagement', 'insightistic' ); ?>
		</button>
		<button class="isp-tab-btn" data-tab="ai">
			✨ <?php esc_html_e( 'AI Insights', 'insightistic' ); ?>
		</button>
		<button class="isp-tab-btn" data-tab="guides">
			📖 <?php esc_html_e( 'Docs', 'insightistic' ); ?>
		</button>
	</div>

	<form method="post" action="">
		<?php wp_nonce_field( 'insightistic_pro_save_settings', 'insightistic_pro_settings_nonce' ); ?>

		<!-- ====================================================== TAB: GA4 -->
		<div class="isp-tab-panel" id="isp-tab-ga4">

			<div class="isp-settings-card">
				<div class="isp-settings-card-header">
					<h2><?php esc_html_e( 'Google Analytics 4 Credentials', 'insightistic' ); ?></h2>
					<p class="isp-header-desc"><?php esc_html_e( 'Connect your GA4 property via a service account. No sampling, no OAuth pop-ups.', 'insightistic' ); ?></p>
				</div>

				<div class="isp-field">
					<label class="isp-label" for="property_id">
						<?php esc_html_e( 'GA4 Property ID', 'insightistic' ); ?>
						<span class="isp-required">*</span>
					</label>
					<input type="text" id="property_id" name="property_id" class="isp-input"
						value="<?php echo esc_attr( $property_id ); ?>"
						placeholder="123456789" pattern="\d+" />
					<p class="isp-hint"><?php esc_html_e( 'Numeric only. Find it in GA4 → Admin → Property Settings.', 'insightistic' ); ?></p>
				</div>

				<div class="isp-field">
					<label class="isp-label" for="api_email">
						<?php esc_html_e( 'Service Account Email', 'insightistic' ); ?>
						<span class="isp-required">*</span>
					</label>
					<input type="email" id="api_email" name="api_email" class="isp-input"
						value="<?php echo esc_attr( $api_email ); ?>"
						placeholder="analytics@your-project.iam.gserviceaccount.com" />
					<p class="isp-hint"><?php esc_html_e( 'Add this email to your GA4 property with Viewer role.', 'insightistic' ); ?></p>
				</div>

				<div class="isp-field">
					<label class="isp-label" for="api_private_key">
						<?php esc_html_e( 'Private Key', 'insightistic' ); ?>
						<span class="isp-required">*</span>
					</label>
					<?php if ( $api_key_stored ) : ?>
					<div class="isp-key-stored">
						<span class="isp-key-badge">✓ <?php esc_html_e( 'Key stored & encrypted', 'insightistic' ); ?></span>
						<button type="button" id="isp-change-key" class="isp-link-btn"><?php esc_html_e( 'Change key', 'insightistic' ); ?></button>
					</div>
					<textarea id="api_private_key" name="api_private_key" class="isp-textarea" rows="4"
						style="display:none;" placeholder="-----BEGIN PRIVATE KEY-----&#10;..."></textarea>
					<?php else : ?>
					<textarea id="api_private_key" name="api_private_key" class="isp-textarea" rows="4"
						placeholder="-----BEGIN PRIVATE KEY-----&#10;..."></textarea>
					<?php endif; ?>
					<p class="isp-hint isp-hint-security">🔒 <?php esc_html_e( 'Encrypted with AES-256 before storage. Never exposed in plain text.', 'insightistic' ); ?></p>
				</div>

				<div class="isp-json-import">
					<button type="button" id="isp-toggle-json" class="isp-link-btn">📎 <?php esc_html_e( 'Import from JSON key file', 'insightistic' ); ?></button>
					<div id="isp-json-wrap" style="display:none; margin-top:12px;">
						<textarea id="isp-json-input" class="isp-textarea" rows="6"
							placeholder="<?php esc_attr_e( 'Paste the entire contents of your service account JSON key file here…', 'insightistic' ); ?>"></textarea>
						<button type="button" id="isp-extract-json" class="isp-btn isp-btn-secondary" style="margin-top:8px;">
							<?php esc_html_e( 'Extract Credentials', 'insightistic' ); ?>
						</button>
					</div>
					<!-- Inline notice replaces browser alert() — shown/hidden by admin.js -->
					<div id="isp-json-notice" class="isp-notice" style="display:none; margin-top:10px;" role="status" aria-live="polite"></div>
				</div>
			</div>

			<div class="isp-settings-card">
				<div class="isp-test-card">
					<h3><?php esc_html_e( 'Test GA4 Connection', 'insightistic' ); ?></h3>
					<p><?php esc_html_e( 'Save your settings first, then test the connection.', 'insightistic' ); ?></p>
					<button type="button" id="isp-test-connection" class="isp-btn isp-btn-secondary">
						🔌 <?php esc_html_e( 'Test Connection', 'insightistic' ); ?>
					</button>
					<div id="isp-test-result" style="margin-top:12px;"></div>
				</div>
			</div>

			<div class="isp-settings-card">
				<div class="isp-settings-card-header">
					<h3><?php esc_html_e( 'How to Set Up', 'insightistic' ); ?></h3>
				</div>
				<div class="isp-instructions">
					<ol class="isp-steps">
						<li><strong><?php esc_html_e( 'Create a Service Account', 'insightistic' ); ?></strong><span><?php esc_html_e( 'Go to Google Cloud Console → IAM & Admin → Service Accounts → Create Service Account.', 'insightistic' ); ?></span></li>
						<li><strong><?php esc_html_e( 'Download the JSON key', 'insightistic' ); ?></strong><span><?php esc_html_e( 'Click the service account → Keys → Add Key → JSON. Use the "Import from JSON" button above.', 'insightistic' ); ?></span></li>
						<li><strong><?php esc_html_e( 'Add to GA4 property', 'insightistic' ); ?></strong><span><?php esc_html_e( 'In GA4 → Admin → Property Access Management → Add the service account email as Viewer.', 'insightistic' ); ?></span></li>
						<li><strong><?php esc_html_e( 'Enter your Property ID', 'insightistic' ); ?></strong><span><?php esc_html_e( 'Find it in GA4 → Admin → Property Settings (numeric only, e.g. 123456789).', 'insightistic' ); ?></span></li>
					</ol>
				</div>
			</div>

		</div><!-- /#isp-tab-ga4 -->

		<!-- ================================================ TAB: SEARCH CONSOLE -->
		<div class="isp-tab-panel" id="isp-tab-gsc" style="display:none;">

			<div class="isp-settings-card">
				<div class="isp-settings-card-header">
					<h2><?php esc_html_e( 'Google Search Console', 'insightistic' ); ?></h2>
					<p class="isp-header-desc"><?php esc_html_e( 'Uses the same service account as GA4 — just add it to your Search Console property and enter the URL below.', 'insightistic' ); ?></p>
				</div>

				<div class="isp-field">
					<label class="isp-label" for="gsc_property_url">
						<?php esc_html_e( 'Search Console Property URL', 'insightistic' ); ?>
					</label>
					<input type="url" id="gsc_property_url" name="gsc_property_url" class="isp-input"
						value="<?php echo esc_attr( $gsc_property_url ); ?>"
						placeholder="https://yoursite.com/" />
					<p class="isp-hint"><?php esc_html_e( 'Enter the exact property URL as registered in Search Console (e.g. https://yoursite.com/ with trailing slash). For domain properties, leave blank and use sc-domain:yoursite.com format.', 'insightistic' ); ?></p>
				</div>
			</div>

			<div class="isp-settings-card">
				<div class="isp-test-card">
					<h3><?php esc_html_e( 'Test Search Console Connection', 'insightistic' ); ?></h3>
					<p><?php esc_html_e( 'Save settings first, then verify the connection.', 'insightistic' ); ?></p>
					<button type="button" id="isp-test-gsc" class="isp-btn isp-btn-secondary">
						🔌 <?php esc_html_e( 'Test GSC Connection', 'insightistic' ); ?>
					</button>
					<div id="isp-test-gsc-result" style="margin-top:12px;"></div>
				</div>
			</div>

			<div class="isp-settings-card">
				<div class="isp-settings-card-header">
					<h3><?php esc_html_e( 'How to Set Up', 'insightistic' ); ?></h3>
				</div>
				<div class="isp-instructions">
					<ol class="isp-steps">
						<li><strong><?php esc_html_e( 'Use the same service account', 'insightistic' ); ?></strong><span><?php esc_html_e( 'No new JSON key needed — same credentials as GA4.', 'insightistic' ); ?></span></li>
						<li><strong><?php esc_html_e( 'Add to Search Console', 'insightistic' ); ?></strong><span><?php esc_html_e( 'Go to Google Search Console → Settings → Users and permissions → Add user. Paste the service account email and set as Full permission.', 'insightistic' ); ?></span></li>
						<li><strong><?php esc_html_e( 'Enter property URL', 'insightistic' ); ?></strong><span><?php esc_html_e( 'Enter the URL exactly as it appears in Search Console, including protocol and trailing slash.', 'insightistic' ); ?></span></li>
					</ol>
				</div>
				<div class="isp-field" style="padding-top:0;">
					<div class="isp-notice isp-notice-info">
						ℹ️ <?php esc_html_e( 'Search Console data has a 2–3 day delay and only shows data from the last 16 months.', 'insightistic' ); ?>
					</div>
				</div>
			</div>

		</div><!-- /#isp-tab-gsc -->

		<!-- ============================================== TAB: PAGESPEED -->
		<div class="isp-tab-panel" id="isp-tab-pagespeed" style="display:none;">

			<div class="isp-settings-card">
				<div class="isp-settings-card-header">
					<h2><?php esc_html_e( 'PageSpeed Insights', 'insightistic' ); ?></h2>
					<p class="isp-header-desc"><?php esc_html_e( 'Enter your Google Cloud API key to enable PageSpeed testing. The free tier allows 25,000 queries/day.', 'insightistic' ); ?></p>
				</div>

				<div class="isp-field">
					<label class="isp-label" for="pagespeed_api_key">
						<?php esc_html_e( 'Google Cloud API Key', 'insightistic' ); ?>
					</label>
					<?php if ( $psi_key_stored ) : ?>
					<div class="isp-key-stored">
						<span class="isp-key-badge">✓ <?php esc_html_e( 'API key stored', 'insightistic' ); ?></span>
						<button type="button" id="isp-change-psi-key" class="isp-link-btn"><?php esc_html_e( 'Change key', 'insightistic' ); ?></button>
					</div>
					<input type="text" id="pagespeed_api_key" name="pagespeed_api_key" class="isp-input"
						style="display:none;" placeholder="AIza..." autocomplete="off" />
					<?php else : ?>
					<input type="text" id="pagespeed_api_key" name="pagespeed_api_key" class="isp-input"
						placeholder="AIza..." autocomplete="off" />
					<?php endif; ?>
					<p class="isp-hint"><?php esc_html_e( 'Create an API key in Google Cloud Console → APIs & Services → Credentials. Enable the PageSpeed Insights API on the same project.', 'insightistic' ); ?></p>
				</div>

				<div class="isp-field">
					<label class="isp-label" for="pagespeed_default_url">
						<?php esc_html_e( 'Default URL to Test', 'insightistic' ); ?>
					</label>
					<input type="url" id="pagespeed_default_url" name="pagespeed_default_url" class="isp-input"
						value="<?php echo esc_attr( $psi_default_url ); ?>"
						placeholder="<?php echo esc_attr( home_url( '/' ) ); ?>" />
					<p class="isp-hint"><?php esc_html_e( 'This URL will be pre-filled in the PageSpeed tab. You can change it any time on the dashboard.', 'insightistic' ); ?></p>
				</div>
			</div>

		</div><!-- /#isp-tab-pagespeed -->

		<!-- ============================================= TAB: ENGAGEMENT -->
		<div class="isp-tab-panel" id="isp-tab-engagement" style="display:none;">

			<div class="isp-settings-card">
				<div class="isp-settings-card-header">
					<h2><?php esc_html_e( 'Engagement Tracking', 'insightistic' ); ?></h2>
					<p class="isp-header-desc"><?php esc_html_e( 'A lightweight script (&lt;2KB) that fires custom GA4 events: outbound links, scroll depth, file downloads, and button clicks.', 'insightistic' ); ?></p>
				</div>

				<div class="isp-field isp-field-toggle">
					<label class="isp-label"><?php esc_html_e( 'Enable Tracking Script', 'insightistic' ); ?></label>
					<label class="isp-toggle">
						<input type="checkbox" name="engagement_enabled" value="1" <?php checked( $engagement_on ); ?>>
						<span class="isp-toggle-track"><span class="isp-toggle-thumb"></span></span>
						<span class="isp-toggle-label">
							<span class="isp-on"><?php esc_html_e( 'Enabled', 'insightistic' ); ?></span>
							<span class="isp-off"><?php esc_html_e( 'Disabled', 'insightistic' ); ?></span>
						</span>
					</label>
					<p class="isp-hint" style="margin-top:8px;"><?php esc_html_e( 'When enabled, a small script is loaded on the frontend of your site.', 'insightistic' ); ?></p>
				</div>

				<div class="isp-field">
					<label class="isp-label" for="measurement_id">
						<?php esc_html_e( 'GA4 Measurement ID', 'insightistic' ); ?>
					</label>
					<input type="text" id="measurement_id" name="measurement_id" class="isp-input"
						value="<?php echo esc_attr( $measurement_id ); ?>"
						placeholder="G-XXXXXXXXXX" />
					<p class="isp-hint"><?php esc_html_e( 'Find this in GA4 → Admin → Data Streams → your stream → Measurement ID.', 'insightistic' ); ?></p>
				</div>

				<div class="isp-field">
					<label class="isp-label" for="measurement_secret">
						<?php esc_html_e( 'Measurement Protocol API Secret', 'insightistic' ); ?>
					</label>
					<?php if ( $secret_stored ) : ?>
					<div class="isp-key-stored">
						<span class="isp-key-badge">✓ <?php esc_html_e( 'Secret stored & encrypted', 'insightistic' ); ?></span>
						<button type="button" id="isp-change-secret" class="isp-link-btn"><?php esc_html_e( 'Change', 'insightistic' ); ?></button>
					</div>
					<input type="text" id="measurement_secret" name="measurement_secret" class="isp-input"
						style="display:none;" placeholder="secret" autocomplete="off" />
					<?php else : ?>
					<input type="text" id="measurement_secret" name="measurement_secret" class="isp-input"
						placeholder="secret" autocomplete="off" />
					<?php endif; ?>
					<p class="isp-hint"><?php esc_html_e( 'GA4 → Admin → Data Streams → your stream → Measurement Protocol → Create. Stored encrypted.', 'insightistic' ); ?></p>
				</div>
			</div>

			<div class="isp-settings-card">
				<div class="isp-settings-card-header">
					<h3><?php esc_html_e( 'What Gets Tracked', 'insightistic' ); ?></h3>
				</div>
				<div class="isp-field">
					<div class="isp-tracking-features">
						<div class="isp-tracking-item">
							<span class="isp-tracking-icon">🔗</span>
							<div>
								<strong><?php esc_html_e( 'Outbound Link Clicks', 'insightistic' ); ?></strong>
								<p><?php esc_html_e( 'Fires an event when a visitor clicks a link to an external domain.', 'insightistic' ); ?></p>
							</div>
						</div>
						<div class="isp-tracking-item">
							<span class="isp-tracking-icon">📏</span>
							<div>
								<strong><?php esc_html_e( 'Scroll Depth', 'insightistic' ); ?></strong>
								<p><?php esc_html_e( 'Fires at 25%, 50%, 75%, and 100% scroll milestones.', 'insightistic' ); ?></p>
							</div>
						</div>
						<div class="isp-tracking-item">
							<span class="isp-tracking-icon">📥</span>
							<div>
								<strong><?php esc_html_e( 'File Downloads', 'insightistic' ); ?></strong>
								<p><?php esc_html_e( 'Fires when .pdf, .zip, .doc, .xls, .mp3 and similar files are clicked.', 'insightistic' ); ?></p>
							</div>
						</div>
						<div class="isp-tracking-item">
							<span class="isp-tracking-icon">🖱️</span>
							<div>
								<strong><?php esc_html_e( 'Element Clicks', 'insightistic' ); ?></strong>
								<p><?php esc_html_e( 'Tracks clicks on elements with the CSS class .isp-track.', 'insightistic' ); ?></p>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div><!-- /#isp-tab-engagement -->

		<!-- ================================================= TAB: AI -->
		<div class="isp-tab-panel" id="isp-tab-ai" style="display:none;">

			<div class="isp-settings-card">
				<div class="isp-settings-card-header">
					<h2><?php esc_html_e( 'AI-Powered Insights', 'insightistic' ); ?></h2>
					<p class="isp-header-desc"><?php esc_html_e( 'Enable manual AI analysis of your analytics data. Click "Get AI Insights" on the dashboard to run an analysis — never auto-triggered.', 'insightistic' ); ?></p>
				</div>

				<div class="isp-field isp-field-toggle">
					<label class="isp-label"><?php esc_html_e( 'Enable AI Insights', 'insightistic' ); ?></label>
					<label class="isp-toggle">
						<input type="checkbox" name="ai_enabled" value="1" <?php checked( $ai_enabled ); ?>>
						<span class="isp-toggle-track"><span class="isp-toggle-thumb"></span></span>
						<span class="isp-toggle-label">
							<span class="isp-on"><?php esc_html_e( 'Enabled', 'insightistic' ); ?></span>
							<span class="isp-off"><?php esc_html_e( 'Disabled', 'insightistic' ); ?></span>
						</span>
					</label>
				</div>

				<div class="isp-field">
					<label class="isp-label"><?php esc_html_e( 'AI Provider', 'insightistic' ); ?></label>
					<div class="isp-provider-grid">
						<?php
						$providers = array(
							'none'       => array( 'icon' => '⭕', 'name' => __( 'None', 'insightistic' ),        'note' => __( 'Disabled', 'insightistic' ) ),
							'openai'     => array( 'icon' => '🤖', 'name' => 'OpenAI',                                 'note' => 'GPT-4o / GPT-4' ),
							'gemini'     => array( 'icon' => '♊', 'name' => 'Google Gemini',                           'note' => 'Gemini 1.5' ),
							'openrouter' => array( 'icon' => '🔀', 'name' => 'OpenRouter',                             'note' => __( 'Free models available', 'insightistic' ) ),
							'claude'     => array( 'icon' => '🌟', 'name' => 'Anthropic Claude',                        'note' => 'Claude 3' ),
						);
						foreach ( $providers as $key => $p ) :
							$selected = $ai_provider === $key ? 'isp-provider-selected' : '';
							?>
							<label class="isp-provider-card <?php echo esc_attr( $selected ); ?>">
								<input type="radio" name="ai_provider" value="<?php echo esc_attr( $key ); ?>" <?php checked( $ai_provider, $key ); ?>>
								<span class="isp-provider-icon"><?php echo esc_html( $p['icon'] ); ?></span>
								<span class="isp-provider-name"><?php echo esc_html( $p['name'] ); ?></span>
								<span class="isp-provider-note"><?php echo esc_html( $p['note'] ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<?php foreach ( array( 'openai', 'gemini', 'openrouter', 'claude' ) as $prov ) :
				$display     = array( 'openai' => 'OpenAI', 'gemini' => 'Google Gemini', 'openrouter' => 'OpenRouter', 'claude' => 'Anthropic Claude' );
				$key_stored  = array( 'openai' => $openai_stored, 'gemini' => $gemini_stored, 'openrouter' => $openrouter_stored, 'claude' => $claude_stored );
				$cur_model   = array( 'openai' => $openai_model, 'gemini' => $gemini_model, 'openrouter' => $openrouter_model, 'claude' => $claude_model );
				$model_opts  = array(
					'openai'     => array( 'gpt-4o-mini' => 'GPT-4o Mini (recommended)', 'gpt-4o' => 'GPT-4o', 'gpt-4-turbo' => 'GPT-4 Turbo' ),
					'gemini'     => array( 'gemini-1.5-flash' => 'Gemini 1.5 Flash (recommended)', 'gemini-1.5-pro' => 'Gemini 1.5 Pro', 'gemini-2.0-flash-exp' => 'Gemini 2.0 Flash' ),
					'openrouter' => array(
							// ── Free tier models (:free suffix = no API cost) ─────────────────────
							'mistralai/mistral-7b-instruct:free'          => '⭐ Mistral 7B Instruct (Free – recommended)',
							'google/gemma-3-12b-it:free'                  => 'Google Gemma 3 12B (Free)',
							'google/gemma-2-9b-it:free'                   => 'Google Gemma 2 9B (Free)',
							'meta-llama/llama-3.1-8b-instruct:free'       => 'Meta Llama 3.1 8B (Free)',
							'meta-llama/llama-3.2-3b-instruct:free'       => 'Meta Llama 3.2 3B (Free)',
							'qwen/qwen-2.5-7b-instruct:free'              => 'Qwen 2.5 7B (Free)',
							'microsoft/phi-3-mini-128k-instruct:free'     => 'Microsoft Phi-3 Mini 128K (Free)',
							'deepseek/deepseek-r1:free'                   => 'DeepSeek R1 (Free – reasoning)',
							// ── Paid models ────────────────────────────────────────────────────────
							'openai/gpt-4o-mini'                          => 'GPT-4o Mini (Paid)',
							'anthropic/claude-3-haiku'                    => 'Claude 3 Haiku (Paid)',
						),
					'claude'     => array( 'claude-haiku-4-5-20251001' => 'Claude Haiku 4.5 (recommended)', 'claude-sonnet-4-6' => 'Claude Sonnet 4.6', 'claude-opus-4-6' => 'Claude Opus 4.6' ),
				);
				$key_fields  = array( 'openai' => 'openai_api_key', 'gemini' => 'gemini_api_key', 'openrouter' => 'openrouter_api_key', 'claude' => 'claude_api_key' );
				$vis         = $ai_provider === $prov ? '' : 'display:none;';
			?>
			<div class="isp-settings-card isp-provider-settings" id="isp-settings-<?php echo esc_attr( $prov ); ?>" style="<?php echo esc_attr( $vis ); ?>">
				<div class="isp-settings-card-header">
					<h3><?php echo esc_html( $display[ $prov ] ); ?> <?php esc_html_e( 'Settings', 'insightistic' ); ?></h3>
				</div>
				<div class="isp-field">
					<label class="isp-label"><?php esc_html_e( 'API Key', 'insightistic' ); ?></label>
					<?php if ( $key_stored[ $prov ] ) : ?>
					<div class="isp-key-stored">
						<span class="isp-key-badge">✓ <?php esc_html_e( 'Key stored & encrypted', 'insightistic' ); ?></span>
					</div>
					<input type="text" name="<?php echo esc_attr( $key_fields[ $prov ] ); ?>" class="isp-input"
						placeholder="<?php esc_attr_e( 'Enter new key to replace', 'insightistic' ); ?>" autocomplete="off" />
					<?php else : ?>
					<input type="text" name="<?php echo esc_attr( $key_fields[ $prov ] ); ?>" class="isp-input"
						placeholder="<?php esc_attr_e( 'Enter API key', 'insightistic' ); ?>" autocomplete="off" />
					<?php endif; ?>
				</div>
				<div class="isp-field">
					<label class="isp-label"><?php esc_html_e( 'Model', 'insightistic' ); ?></label>
					<select name="<?php echo esc_attr( $prov ); ?>_model" class="isp-select">
						<?php foreach ( $model_opts[ $prov ] as $val => $label ) : ?>
						<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $cur_model[ $prov ], $val ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<?php endforeach; ?>

		</div><!-- /#isp-tab-ai -->

		<!-- ================================================ TAB: DOCS -->
		<div class="isp-tab-panel" id="isp-tab-guides" style="display:none;">

			<div class="isp-settings-card">
				<div class="isp-settings-card-header">
					<h2>Documentation Links</h2>
					<p class="isp-header-desc"> Check our guide links. These will help you to setup the plugin with a few steps.</p>
				</div>
				<div class="isp-field">
					<label class="isp-label" > Video Tutorial URL </label>
					<a href="https://www.youtube.com/@wordpressistic" target="_blank" class="isp-guide-link isp-guide-video">
						▶ Watch Tutorial
					</a>
				</div>
				<div class="isp-field">
					<label class="isp-label" > Documentation URL
</label>
					<a href="https://www.chatbotistic.com/insightistic-installation-documents" target="_blank" class="isp-guide-link isp-guide-docs">
						📖 Documentation
					</a>
				</div>
			</div>

		</div><!-- /#isp-tab-guides -->

		<!-- ================================================ SAVE BUTTON -->
		<div class="isp-form-actions">
			<button type="submit" class="isp-btn isp-btn-primary">
				💾 <?php esc_html_e( 'Save Settings', 'insightistic' ); ?>
			</button>
		</div>

	</form>

	<div class="isp-footer">
		<p>
			<?php esc_html_e( 'Insightistic', 'insightistic' ); ?> v<?php echo esc_html( INSIGHTISTIC_VERSION ); ?> &bull;
			<a href="https://wordpressistic.com" target="_blank" rel="noopener noreferrer">WordPressistic</a>
		</p>
	</div>

</div><!-- /.isp-wrap -->
