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
?>

<main class="page-fade">

	<section class="page-hero">
		<div class="container" style="text-align:center;">
			<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Documentation', 'chatbotistic' ); ?></span>
			<h1 class="text-grad" style="max-width:760px;margin:18px auto 0;"><?php esc_html_e( 'Everything you need to ship faster.', 'chatbotistic' ); ?></h1>
			<p style="max-width:640px;margin:18px auto 0;"><?php esc_html_e( 'Quickstarts, integration guides, API reference, and copy-paste embed snippets.', 'chatbotistic' ); ?></p>
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
							<a href="#" class="<?php echo ( 0 === $cb_i && 0 === $cb_j ) ? 'active' : ''; ?>"><?php echo esc_html( $cb_item ); ?></a>
						<?php endforeach; ?>
						<?php if ( $cb_i < count( $cb_sections ) - 1 ) : ?><div class="sep"></div><?php endif; ?>
					</div>
				<?php endforeach; ?>
			</aside>

			<article class="docs-content">
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
