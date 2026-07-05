<?php
/**
 * Template Name: Documentation (V4)
 *
 * V4 port of pages/about-contact-docs.jsx → Docs. Static quickstart
 * with sidebar nav + TOC.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cb_sections = array(
	array( __( 'Getting started', 'chatbotistic' ), array( __( 'Quickstart', 'chatbotistic' ), __( 'Installation', 'chatbotistic' ), __( 'Concepts', 'chatbotistic' ), __( 'First widget', 'chatbotistic' ) ) ),
	array( __( 'Embedding', 'chatbotistic' ), array( __( 'JavaScript snippet', 'chatbotistic' ), __( 'WordPress plugin', 'chatbotistic' ), __( 'Shortcode block', 'chatbotistic' ), __( 'React component', 'chatbotistic' ) ) ),
	array( __( 'Building flows', 'chatbotistic' ), array( __( 'AI training', 'chatbotistic' ), __( 'Branching logic', 'chatbotistic' ), __( 'Variables', 'chatbotistic' ), __( 'Lead qualification', 'chatbotistic' ) ) ),
	array( __( 'Channels', 'chatbotistic' ), array( __( 'WhatsApp setup', 'chatbotistic' ), __( 'Email', 'chatbotistic' ), __( 'Booking forms', 'chatbotistic' ), __( 'Multi-agent inbox', 'chatbotistic' ) ) ),
	array( __( 'API', 'chatbotistic' ), array( __( 'Authentication', 'chatbotistic' ), __( 'REST API', 'chatbotistic' ), __( 'Webhooks', 'chatbotistic' ), __( 'SDK', 'chatbotistic' ) ) ),
	array( __( 'Billing', 'chatbotistic' ), array( __( 'Plans', 'chatbotistic' ), __( 'Invoicing', 'chatbotistic' ), __( 'Cancellation', 'chatbotistic' ), __( 'Refunds', 'chatbotistic' ) ) ),
);

$cb_embed = '<script>' . "\n" .
	'  (function(w,d){' . "\n" .
	'    w.chatbotisticConfig = { widgetId: "YOUR_WIDGET_ID" };' . "\n" .
	'    var s = d.createElement("script");' . "\n" .
	'    s.src = "https://cdn.chatbotistic.com/embed.js";' . "\n" .
	'    s.async = true; d.head.appendChild(s);' . "\n" .
	'  })(window, document);' . "\n" .
	'</script>';

$cb_shortcodes = '[chatbotistic widget="lead-capture"]' . "\n" .
	'[chatbotistic widget="booking" theme="dark"]' . "\n" .
	'[chatbotistic widget="ai-chat" position="bottom-right"]';

$cb_faqs = array(
	array( __( 'How do I embed Chatbotistic on a non-WordPress site?', 'chatbotistic' ), __( 'Paste a single script tag into your site’s HTML before the closing body tag. Your widget loads asynchronously and respects your site’s performance.', 'chatbotistic' ) ),
	array( __( 'Can I customize the widget colors and branding?', 'chatbotistic' ), __( 'Yes. Every plan above Free includes color, position, and font customization. White Label plans get full CSS control and custom domains.', 'chatbotistic' ) ),
	array( __( 'What happens to conversations after I cancel?', 'chatbotistic' ), __( 'You can export every conversation and lead at any time. After cancellation, your data is retained for 90 days and then permanently deleted.', 'chatbotistic' ) ),
);

// Deeper, always-current guides live inside the Chatbotistic Dashboard app
// itself (dashboard.chatbotistic.com) rather than this marketing site, so
// they stay in sync with the product. Link out to them via cb_dashboard_url().
$cb_app_guides = array(
	array( __( 'Getting Started', 'chatbotistic' ), 'rocket', 'docs/getting-started', __( 'Create your account, set up your workspace, and take the guided tour of the dashboard.', 'chatbotistic' ) ),
	array( __( 'Create Your First Widget', 'chatbotistic' ), 'plug', 'docs/first-widget', __( 'Build a chat widget, style it to match your brand, and copy the embed snippet.', 'chatbotistic' ) ),
	array( __( 'Connect WhatsApp', 'chatbotistic' ), 'wa', 'docs/connect-whatsapp', __( 'Link a WhatsApp Business number and assign it to an agent.', 'chatbotistic' ) ),
	array( __( 'Booking Forms', 'chatbotistic' ), 'cal', 'docs/booking-forms', __( 'Set up services, availability, and calendar sync for in-chat bookings.', 'chatbotistic' ) ),
	array( __( 'Campaigns & Message Limits', 'chatbotistic' ), 'chart', 'docs/campaigns-message-limits', __( 'Send WhatsApp campaigns and track your plan’s monthly message allowance.', 'chatbotistic' ) ),
	array( __( 'White-Label Setup', 'chatbotistic' ), 'tag', 'docs/white-label-setup', __( 'Put your own logo, domain, and branding on the dashboard for clients.', 'chatbotistic' ) ),
);
?>

<main class="page-fade">

	<section class="page-hero">
		<div class="container" style="text-align:center;">
			<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Documentation', 'chatbotistic' ); ?></span>
			<h1 class="text-grad" style="max-width:760px;margin:18px auto 0;"><?php esc_html_e( 'Everything you need to ship faster.', 'chatbotistic' ); ?></h1>
			<p style="max-width:640px;margin:18px auto 0;"><?php esc_html_e( 'Quickstarts, integration guides, API reference, and copy-paste embed snippets.', 'chatbotistic' ); ?></p>
		</div>
	</section>

	<section class="section section-tight">
		<div class="container">
			<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'In the app', 'chatbotistic' ); ?></span>
			<h2 style="margin-top:12px;"><?php esc_html_e( 'Guides inside your Dashboard', 'chatbotistic' ); ?></h2>
			<p style="max-width:640px;"><?php esc_html_e( 'The quickstart below covers embedding the widget on this site. For the deeper, always up-to-date walkthroughs — building flows, connecting channels, and managing your team — open the in-app docs from your Chatbotistic Dashboard.', 'chatbotistic' ); ?></p>
			<div class="features-grid" style="grid-template-columns:repeat(3,1fr);margin-top:32px;">
				<?php foreach ( $cb_app_guides as $cb_g ) : ?>
					<a class="f-card" href="<?php echo esc_url( cb_dashboard_url( $cb_g[2] ) ); ?>" style="text-decoration:none;">
						<div class="f-ico"><?php echo cb_get_icon( $cb_g[1], 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						<h3 style="margin-top:8px;"><?php echo esc_html( $cb_g[0] ); ?></h3>
						<p style="color:var(--text-soft);font-size:13.5px;margin-top:6px;"><?php echo esc_html( $cb_g[3] ); ?></p>
						<span class="btn btn-ghost btn-sm" style="margin-top:14px;align-self:flex-start;"><?php esc_html_e( 'Open in dashboard →', 'chatbotistic' ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<div class="container">
		<div class="docs-grid">

			<aside class="docs-side">
				<div class="docs-search"><input placeholder="<?php esc_attr_e( 'Search docs', 'chatbotistic' ); ?>" type="search"><kbd>K</kbd></div>
				<?php foreach ( $cb_sections as $cb_i => $cb_s ) : ?>
					<div>
						<h5><?php echo esc_html( $cb_s[0] ); ?></h5>
						<?php foreach ( $cb_s[1] as $cb_j => $cb_item ) : ?>
							<a href="#" data-doc="<?php echo esc_attr( $cb_i . '-' . $cb_j ); ?>" class="<?php echo ( 0 === $cb_i && 0 === $cb_j ) ? 'active' : ''; ?>"><?php echo esc_html( $cb_item ); ?></a>
						<?php endforeach; ?>
						<?php if ( $cb_i < count( $cb_sections ) - 1 ) : ?><div class="sep"></div><?php endif; ?>
					</div>
				<?php endforeach; ?>
			</aside>

			<article class="docs-content is-active" data-doc-panel="0-0">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Getting started', 'chatbotistic' ); ?></span>
				<h1 style="margin-top:18px;"><?php esc_html_e( 'Quickstart', 'chatbotistic' ); ?></h1>
				<p><?php esc_html_e( 'Welcome to Chatbotistic. This guide gets your first widget live in under three minutes. You’ll create an account, build a chatbot, and embed it on your site.', 'chatbotistic' ); ?></p>

				<h2><?php esc_html_e( '1. Install the embed script', 'chatbotistic' ); ?></h2>
				<p>
					<?php
					printf(
						wp_kses(
							/* translators: %s: YOUR_WIDGET_ID code token */
							__( 'Paste the snippet below into your site, just before the closing body tag. Replace %s with the ID from your dashboard.', 'chatbotistic' ),
							array( 'code' => array() )
						),
						'<code>YOUR_WIDGET_ID</code>'
					);
					?>
				</p>
				<pre class="codeblock" style="white-space:pre;overflow:auto;"><?php echo esc_html( $cb_embed ); ?></pre>

				<h2><?php esc_html_e( '2. WordPress plugin', 'chatbotistic' ); ?></h2>
				<p>
					<?php
					echo wp_kses(
						__( 'If you’re on WordPress, install the plugin instead — no code required. Go to <strong>Plugins → Add New</strong>, search for Chatbotistic, install and activate. Paste your API key in <strong>Settings → Chatbotistic</strong>.', 'chatbotistic' ),
						array( 'strong' => array() )
					);
					?>
				</p>

				<h3><?php esc_html_e( 'Embed with shortcode', 'chatbotistic' ); ?></h3>
				<pre class="codeblock" style="white-space:pre;overflow:auto;"><?php echo esc_html( $cb_shortcodes ); ?></pre>

				<h2><?php esc_html_e( '3. Train your AI', 'chatbotistic' ); ?></h2>
				<p>
					<?php
					echo wp_kses(
						__( 'From your dashboard, go to <strong>AI → Knowledge</strong>. Paste in your website URL or upload your FAQ document. Training takes 30 to 90 seconds depending on size.', 'chatbotistic' ),
						array( 'strong' => array() )
					);
					?>
				</p>

				<h2><?php esc_html_e( '4. Test it', 'chatbotistic' ); ?></h2>
				<p>
					<?php
					echo wp_kses(
						__( 'Open the preview in your dashboard. Type a question. Watch the response. Tweak the system prompt under <strong>AI → Personality</strong> to match your brand voice.', 'chatbotistic' ),
						array( 'strong' => array() )
					);
					?>
				</p>

				<h2><?php esc_html_e( 'Common questions', 'chatbotistic' ); ?></h2>
				<div class="faq-list" style="margin:24px 0 0;">
					<?php foreach ( $cb_faqs as $cb_q ) : ?>
						<details class="faq-item">
							<summary><?php echo esc_html( $cb_q[0] ); ?></summary>
							<p><?php echo esc_html( $cb_q[1] ); ?></p>
						</details>
					<?php endforeach; ?>
				</div>

				<div style="padding:24px;border-radius:18px;background:linear-gradient(135deg,rgba(160,112,255,0.08),rgba(79,139,255,0.05));border:1px solid rgba(160,112,255,0.2);margin-top:48px;display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap;">
					<div>
						<h3 class="h-3"><?php esc_html_e( 'Need a hand?', 'chatbotistic' ); ?></h3>
						<p style="color:var(--text-soft);margin:4px 0 0;"><?php esc_html_e( 'Our support team replies in under 2 hours during business days.', 'chatbotistic' ); ?></p>
					</div>
					<a class="btn btn-primary" href="<?php echo esc_url( home_url( '/support/' ) ); ?>"><?php esc_html_e( 'Open support', 'chatbotistic' ); ?></a>
				</div>
			</article>

			<?php
			// Generated panels for every other sidebar item, so each tab reveals
			// its own section. Panel 0-0 (Quickstart) is the hand-written article above.
			foreach ( $cb_sections as $cb_i => $cb_s ) :
				foreach ( $cb_s[1] as $cb_j => $cb_item ) :
					if ( 0 === $cb_i && 0 === $cb_j ) {
						continue;
					}
					?>
					<article class="docs-content" data-doc-panel="<?php echo esc_attr( $cb_i . '-' . $cb_j ); ?>">
						<span class="section-eyebrow"><span class="dot"></span><?php echo esc_html( $cb_s[0] ); ?></span>
						<h1 style="margin-top:18px;"><?php echo esc_html( $cb_item ); ?></h1>
						<p>
							<?php
							printf(
								/* translators: %s: documentation section title */
								esc_html__( 'Documentation for %s is on its way. In the meantime, reach out to our team and we will walk you through it.', 'chatbotistic' ),
								esc_html( $cb_item )
							);
							?>
						</p>
						<div style="padding:24px;border-radius:18px;background:linear-gradient(135deg,rgba(160,112,255,0.08),rgba(79,139,255,0.05));border:1px solid rgba(160,112,255,0.2);margin-top:48px;display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap;">
							<div>
								<h3 class="h-3"><?php esc_html_e( 'Need a hand?', 'chatbotistic' ); ?></h3>
								<p style="color:var(--text-soft);margin:4px 0 0;"><?php esc_html_e( 'Our support team replies in under 2 hours during business days.', 'chatbotistic' ); ?></p>
							</div>
							<a class="btn btn-primary" href="<?php echo esc_url( home_url( '/support/' ) ); ?>"><?php esc_html_e( 'Open support', 'chatbotistic' ); ?></a>
						</div>
					</article>
					<?php
				endforeach;
			endforeach;
			?>

			<aside class="docs-toc">
				<h5><?php esc_html_e( 'On this page', 'chatbotistic' ); ?></h5>
				<a href="#"><?php esc_html_e( 'Install the embed script', 'chatbotistic' ); ?></a>
				<a href="#"><?php esc_html_e( 'WordPress plugin', 'chatbotistic' ); ?></a>
				<a href="#"><?php esc_html_e( 'Train your AI', 'chatbotistic' ); ?></a>
				<a href="#"><?php esc_html_e( 'Test it', 'chatbotistic' ); ?></a>
				<a href="#"><?php esc_html_e( 'Common questions', 'chatbotistic' ); ?></a>
			</aside>

		</div>
	</div>

</main>

<?php
get_footer();
