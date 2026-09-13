<?php
/**
 * Template Name: Documentation (V4)
 *
 * Single-page docs: sticky sidebar + scrollspy, every article in one
 * continuous scroll (see docsScrollspy() in assets/js/theme.js and the
 * .docs2-* rules in assets/css/cb-fixes.css). Content mirrors the real,
 * shipped product only — the in-app docs at app.chatbotistic.com/docs
 * (chatbotistic-dashboard/src/app/docs/) are the source of truth for
 * every dashboard-side article here, kept in sync by hand since they're
 * two different codebases; the WordPress-plugin install steps are
 * unique to this page.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

// Sidebar groups — the single source of truth for the nav; each item's
// id must match a `.docs2-article` id below.
$cb_doc_groups = array(
	array(
		'label' => __( 'Getting started', 'chatbotistic' ),
		'items' => array(
			'overview'      => __( 'Overview', 'chatbotistic' ),
			'quickstart'    => __( 'Quickstart', 'chatbotistic' ),
		),
	),
	array(
		'label' => __( 'Widgets', 'chatbotistic' ),
		'items' => array(
			'create-widget'    => __( 'Create your first widget', 'chatbotistic' ),
			'customize-embed'  => __( 'Customize & embed', 'chatbotistic' ),
			'landing-pages'    => __( 'Landing pages', 'chatbotistic' ),
			'booking-forms'    => __( 'Booking forms', 'chatbotistic' ),
		),
	),
	array(
		'label' => __( 'WordPress', 'chatbotistic' ),
		'items' => array(
			'wp-plugin' => __( 'Installing the WordPress plugin', 'chatbotistic' ),
		),
	),
	array(
		'label' => __( 'Grow', 'chatbotistic' ),
		'items' => array(
			'managing-leads'   => __( 'Managing leads', 'chatbotistic' ),
			'connect-whatsapp' => __( 'Connect WhatsApp', 'chatbotistic' ),
			'campaigns'        => __( 'Campaigns & message limits', 'chatbotistic' ),
		),
	),
	array(
		'label' => __( 'Account', 'chatbotistic' ),
		'items' => array(
			'team-roles'    => __( 'Team & roles', 'chatbotistic' ),
			'billing-plans' => __( 'Billing & plans', 'chatbotistic' ),
			'white-label'   => __( 'White-label setup', 'chatbotistic' ),
		),
	),
	array(
		'label' => __( 'Help', 'chatbotistic' ),
		'items' => array(
			'faq' => __( 'FAQ', 'chatbotistic' ),
		),
	),
);

$cb_embed_snippet = '<script defer src="https://app.chatbotistic.com/install-widget/bundle.js?key=YOUR_WIDGET_ID"></script>';

$cb_faqs = array(
	array( __( 'Do I need a Meta Business account?', 'chatbotistic' ), __( 'Only if you want to send Campaigns from the dashboard. If you\'re fine replying manually, connect your personal WhatsApp number instead — no Meta account needed.', 'chatbotistic' ) ),
	array( __( 'Is my WhatsApp access token safe?', 'chatbotistic' ), __( 'Yes — it\'s encrypted (AES-256-GCM) on the server before being stored, and is never sent back to the browser after you save it. Only a masked placeholder shows in the field afterward.', 'chatbotistic' ) ),
	array( __( 'Can I re-run a sync if I think leads are missing?', 'chatbotistic' ), __( 'Yes — the Leads page has a "Sync now" button that pulls the latest from Tochat on demand, in addition to however often it syncs automatically.', 'chatbotistic' ) ),
	array( __( 'What happens to a lead\'s status when it re-syncs?', 'chatbotistic' ), __( 'Syncing never overwrites a status you\'ve already set. If a lead comes back in as "open" from Tochat but you\'d already marked it "contacted", your status sticks.', 'chatbotistic' ) ),
	array( __( 'Can I have more than one organization?', 'chatbotistic' ), __( 'Not from a single signup today — each account gets one personal organization automatically. Team invites let other accounts join yours; there isn\'t a self-serve "create a second org" flow yet outside of Agency sub-accounts.', 'chatbotistic' ) ),
	array( __( 'What\'s the difference between a widget\'s agent and a team member?', 'chatbotistic' ), __( 'An "agent" is a WhatsApp number configured on Tochat that answers chats for a widget. A "team member" is a person with a login to your dashboard. They\'re unrelated — a team member doesn\'t need to also be a WhatsApp agent, and vice versa.', 'chatbotistic' ) ),
	array( __( 'My invite link doesn\'t work — what happened?', 'chatbotistic' ), __( 'Invite tokens are single-use and don\'t expire on a timer, but they do stop working once accepted, or if they were revoked by an admin. Ask whoever invited you to send a fresh one from Settings → Team.', 'chatbotistic' ) ),
);
?>

<main class="page-fade">

	<section class="page-hero">
		<div class="container" style="text-align:center;">
			<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Documentation', 'chatbotistic' ); ?></span>
			<h1 class="text-grad" style="max-width:760px;margin:18px auto 0;"><?php esc_html_e( 'Everything you need to ship faster.', 'chatbotistic' ); ?></h1>
			<p style="max-width:640px;margin:18px auto 0;"><?php esc_html_e( 'Widgets, WhatsApp, leads, campaigns, and your WordPress install — the real, current feature set, in one page.', 'chatbotistic' ); ?></p>
		</div>
	</section>

	<div class="container">

		<nav class="docs2-mobile-nav" aria-label="<?php esc_attr_e( 'Documentation sections', 'chatbotistic' ); ?>">
			<?php foreach ( $cb_doc_groups as $cb_group ) : ?>
				<?php foreach ( $cb_group['items'] as $cb_id => $cb_title ) : ?>
					<a href="#<?php echo esc_attr( $cb_id ); ?>"><?php echo esc_html( $cb_title ); ?></a>
				<?php endforeach; ?>
			<?php endforeach; ?>
		</nav>

		<div class="docs2-grid">

			<aside class="docs2-side" aria-label="<?php esc_attr_e( 'Documentation sections', 'chatbotistic' ); ?>">
				<?php foreach ( $cb_doc_groups as $cb_group ) : ?>
					<div class="docs2-group">
						<h5><?php echo esc_html( $cb_group['label'] ); ?></h5>
						<?php foreach ( $cb_group['items'] as $cb_id => $cb_title ) : ?>
							<a href="#<?php echo esc_attr( $cb_id ); ?>"><?php echo esc_html( $cb_title ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
			</aside>

			<div class="docs2-content">

				<article class="docs2-article" id="overview">
					<h2><?php esc_html_e( 'Overview', 'chatbotistic' ); ?></h2>
					<p class="docs2-lead"><?php esc_html_e( 'What Chatbotistic does, and how the pieces fit together.', 'chatbotistic' ); ?></p>
					<p><?php esc_html_e( 'Chatbotistic embeds a WhatsApp chat widget on your site, captures every visitor who starts a conversation as a lead in your dashboard, and lets you follow up — one-on-one or with bulk campaigns — without leaving the browser. The widget itself is powered by Tochat.be; your Chatbotistic account is where you configure it, see who\'s talking to you, and manage billing.', 'chatbotistic' ); ?></p>
					<ul>
						<li><strong><?php esc_html_e( 'Widgets', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'the embeddable chat bubble; each has its own agents, display rules, and banners.', 'chatbotistic' ); ?></li>
						<li><strong><?php esc_html_e( 'Leads', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'every visitor who starts a chat or fills a form, synced from Tochat so you can triage, filter, and export.', 'chatbotistic' ); ?></li>
						<li><strong><?php esc_html_e( 'Campaigns', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'bulk WhatsApp sends to a filtered slice of your leads, metered against your plan\'s monthly message quota.', 'chatbotistic' ); ?></li>
						<li><strong><?php esc_html_e( 'Account', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'your profile, WhatsApp connection, white-label (Agency plan), team, and billing.', 'chatbotistic' ); ?></li>
					</ul>
					<p><?php echo wp_kses( __( 'Not signed up yet? <a href="/pricing/">See plans &amp; pricing</a>.', 'chatbotistic' ), array( 'a' => array( 'href' => array() ) ) ); ?></p>
				</article>

				<article class="docs2-article" id="quickstart">
					<h2><?php esc_html_e( 'Quickstart', 'chatbotistic' ); ?></h2>
					<p class="docs2-lead"><?php esc_html_e( 'The fastest path from signup to your first WhatsApp lead — about five minutes.', 'chatbotistic' ); ?></p>
					<ol>
						<li><strong><?php esc_html_e( 'Create a widget', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'your first chat bubble, scoped to one site or domain. Free plans get 1 widget; paid plans get more — see Billing & Plans.', 'chatbotistic' ); ?></li>
						<li><strong><?php esc_html_e( 'Add an agent', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'a WhatsApp number that receives and answers chats. A widget needs at least one before it\'s useful.', 'chatbotistic' ); ?></li>
						<li><strong><?php esc_html_e( 'Embed the widget', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'paste the snippet from the widget\'s Customize & Embed tab onto your site, or install the WordPress plugin instead.', 'chatbotistic' ); ?></li>
						<li><strong><?php esc_html_e( 'Connect WhatsApp', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'in Settings, either your personal number (manual replies) or the Meta Cloud API (needed to send Campaigns).', 'chatbotistic' ); ?></li>
						<li><strong><?php esc_html_e( 'Watch leads roll in', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'on the Leads page, and follow up with a Campaign once you\'ve got a few.', 'chatbotistic' ); ?></li>
					</ol>
					<div class="doc-callout">
						<?php echo cb_get_icon( 'spark', 17 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php esc_html_e( 'Your Dashboard home page has a live "Getting Started" checklist that tracks these same five steps against your actual account — use it to see what\'s left.', 'chatbotistic' ); ?></span>
					</div>
				</article>

				<article class="docs2-article" id="create-widget">
					<h2><?php esc_html_e( 'Create your first widget', 'chatbotistic' ); ?></h2>
					<p class="docs2-lead"><?php esc_html_e( 'Widgets are the chat bubble your visitors see.', 'chatbotistic' ); ?></p>
					<p><?php echo wp_kses( __( 'In your Dashboard, open <strong>Widgets</strong> and click <strong>Create widget</strong>. Every widget belongs to your organization and is scoped to a domain — pick which site it should appear on.', 'chatbotistic' ), array( 'strong' => array() ) ); ?></p>
					<h3><?php esc_html_e( 'What you\'ll set up', 'chatbotistic' ); ?></h3>
					<ul>
						<li><strong><?php esc_html_e( 'Name', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'internal only, for your own reference (e.g. "Main site — support").', 'chatbotistic' ); ?></li>
						<li><strong><?php esc_html_e( 'Agent(s)', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'the WhatsApp number(s) that answer chats from this widget. Needs at least one.', 'chatbotistic' ); ?></li>
						<li><strong><?php esc_html_e( 'Display rules', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'control when/where the bubble shows (all pages, specific paths, after a delay).', 'chatbotistic' ); ?></li>
						<li><strong><?php esc_html_e( 'Banners', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'optional proactive messages that pop up before a visitor clicks the bubble.', 'chatbotistic' ); ?></li>
					</ul>
					<div class="doc-callout">
						<?php echo cb_get_icon( 'tag', 17 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php esc_html_e( 'Your plan caps how many widgets you can create — Free: 1, Starter: 3, Growth: 10, Agency: 30. At your limit, the create button prompts an upgrade instead.', 'chatbotistic' ); ?></span>
					</div>
				</article>

				<article class="docs2-article" id="customize-embed">
					<h2><?php esc_html_e( 'Customize & embed', 'chatbotistic' ); ?></h2>
					<p class="docs2-lead"><?php esc_html_e( 'Style your widget and drop it onto your site.', 'chatbotistic' ); ?></p>
					<h3><?php esc_html_e( 'Customize', 'chatbotistic' ); ?></h3>
					<p><?php esc_html_e( 'From a widget\'s settings you can adjust bubble color, position, greeting text, banners, and — if you want visitors to book a slot directly from the chat — a booking config (see Booking Forms). On Free and Starter plans the widget shows a small "Powered by Chatbotistic" mark; Growth and Agency remove it.', 'chatbotistic' ); ?></p>
					<h3><?php esc_html_e( 'Embed the snippet', 'chatbotistic' ); ?></h3>
					<p><?php echo wp_kses( __( 'Every widget has an install snippet with your widget ID already filled in — copy it from the widget\'s Customize & Embed tab and paste it before the closing <code>&lt;/body&gt;</code> tag on any page you want it to appear on:', 'chatbotistic' ), array( 'code' => array() ) ); ?></p>
					<div class="doc-code">
						<button type="button" class="doc-code-copy"><?php echo cb_get_icon( 'code', 13 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Copy', 'chatbotistic' ); ?></span></button>
						<pre><?php echo esc_html( $cb_embed_snippet ); ?></pre>
					</div>
					<p><?php esc_html_e( 'Works on WordPress, Webflow, or a custom site — reload the page and the bubble should appear within a couple of seconds.', 'chatbotistic' ); ?></p>
					<div class="doc-callout">
						<?php echo cb_get_icon( 'spark', 17 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php esc_html_e( 'Nothing showing up? Check the widget\'s display rules first — a rule scoping it to a specific path or domain is the most common reason a freshly embedded widget doesn\'t appear.', 'chatbotistic' ); ?></span>
					</div>
				</article>

				<article class="docs2-article" id="landing-pages">
					<h2><?php esc_html_e( 'Landing pages', 'chatbotistic' ); ?></h2>
					<p class="docs2-lead"><?php esc_html_e( 'A dedicated, shareable page that opens straight into a WhatsApp chat — no widget embed needed.', 'chatbotistic' ); ?></p>
					<p><?php esc_html_e( 'Useful for a link you can drop into an ad, a bio link, or a QR code. Instead of a chat bubble sitting on your existing site, a landing page is the whole page: a visitor lands on it and goes straight into a conversation with the widget\'s agent.', 'chatbotistic' ); ?></p>
					<h3><?php esc_html_e( 'Setting one up', 'chatbotistic' ); ?></h3>
					<ul>
						<li><?php esc_html_e( 'Pick which widget\'s agent(s) the landing page should route to.', 'chatbotistic' ); ?></li>
						<li><?php esc_html_e( 'Choose the page\'s headline, description, and call-to-action text.', 'chatbotistic' ); ?></li>
						<li><?php esc_html_e( 'Publish — you get a shareable link (and a QR code) that opens the chat directly.', 'chatbotistic' ); ?></li>
					</ul>
					<div class="doc-callout">
						<?php echo cb_get_icon( 'chart', 17 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php esc_html_e( 'Landing pages inherit attribution data (referer, UTM params), visible on the resulting lead — handy for tying a specific ad or QR code to real conversations. Requires the Growth plan or above.', 'chatbotistic' ); ?></span>
					</div>
				</article>

				<article class="docs2-article" id="booking-forms">
					<h2><?php esc_html_e( 'Booking forms', 'chatbotistic' ); ?></h2>
					<p class="docs2-lead"><?php esc_html_e( 'Let a WhatsApp conversation end in a scheduled appointment, not just a chat.', 'chatbotistic' ); ?></p>
					<p><?php esc_html_e( 'A booking config attaches a scheduling flow to one of your widget\'s agents. Instead of the chat ending in "someone will get back to you," the visitor picks a time slot right there, and it lands on their lead record as structured booking data.', 'chatbotistic' ); ?></p>
					<h3><?php esc_html_e( 'Setting one up', 'chatbotistic' ); ?></h3>
					<ol>
						<li><?php esc_html_e( 'Open the agent you want bookable, and add a booking config.', 'chatbotistic' ); ?></li>
						<li><?php esc_html_e( 'Define your available slots — working hours, slot length, and how far in advance someone can book.', 'chatbotistic' ); ?></li>
						<li><?php esc_html_e( 'Save. The agent\'s chat flow now offers booking as an option.', 'chatbotistic' ); ?></li>
					</ol>
					<p><?php esc_html_e( 'Booking data shows up in the lead\'s detail drawer under a dedicated "Booking" section — there\'s no separate booking calendar view; bookings live as part of the lead record they belong to.', 'chatbotistic' ); ?></p>
					<div class="doc-callout">
						<?php echo cb_get_icon( 'tag', 17 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php esc_html_e( 'Booking forms require the Starter plan or above. Free-plan accounts see a locked upgrade prompt instead of the booking config option.', 'chatbotistic' ); ?></span>
					</div>
				</article>

				<article class="docs2-article" id="wp-plugin">
					<h2><?php esc_html_e( 'Installing the WordPress plugin', 'chatbotistic' ); ?></h2>
					<p class="docs2-lead"><?php esc_html_e( 'A small plugin you install on your own WordPress site — no code snippet needed.', 'chatbotistic' ); ?></p>
					<p><?php esc_html_e( 'Activate your license, pick a widget, and the chat button goes live for your visitors.', 'chatbotistic' ); ?></p>
					<ol>
						<li><strong><?php esc_html_e( 'Download the plugin', 'chatbotistic' ); ?></strong> — <?php echo wp_kses( __( 'from your account\'s <strong>Install</strong> tab, grab the latest .zip.', 'chatbotistic' ), array( 'strong' => array() ) ); ?></li>
						<li><strong><?php esc_html_e( 'Install on your WordPress site', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'in wp-admin, go to Plugins → Add New → Upload Plugin, choose the .zip, then Install Now and Activate.', 'chatbotistic' ); ?></li>
						<li><strong><?php esc_html_e( 'Paste your license key', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'open Chatbotistic Widget → License in wp-admin and paste the key from your account\'s Install tab.', 'chatbotistic' ); ?></li>
						<li><strong><?php esc_html_e( 'Connect a widget', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'after activation the plugin fetches your widget list — pick one from the dropdown and Save. The chat button goes live immediately.', 'chatbotistic' ); ?></li>
					</ol>
					<h3><?php esc_html_e( 'System requirements', 'chatbotistic' ); ?></h3>
					<ul>
						<li><?php esc_html_e( 'WordPress 6.0 or newer', 'chatbotistic' ); ?></li>
						<li><?php esc_html_e( 'PHP 8.0 or newer', 'chatbotistic' ); ?></li>
						<li><?php esc_html_e( 'HTTPS site (required for the license heartbeat)', 'chatbotistic' ); ?></li>
					</ul>
				</article>

				<article class="docs2-article" id="managing-leads">
					<h2><?php esc_html_e( 'Managing leads', 'chatbotistic' ); ?></h2>
					<p class="docs2-lead"><?php esc_html_e( 'Every visitor who starts a chat becomes a lead — here\'s how to triage them.', 'chatbotistic' ); ?></p>
					<p><?php esc_html_e( 'The Leads page lists everyone who\'s messaged one of your widgets, pulled from Tochat and kept in sync. Each lead carries their phone, name, country, referer/attribution info, which widget and agent they talked to, any custom form fields, and booking data if they scheduled something.', 'chatbotistic' ); ?></p>
					<h3><?php esc_html_e( 'The status pipeline', 'chatbotistic' ); ?></h3>
					<ul>
						<li><strong><?php esc_html_e( 'New', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'just came in, not yet triaged.', 'chatbotistic' ); ?></li>
						<li><strong><?php esc_html_e( 'Contacted', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'you\'ve followed up.', 'chatbotistic' ); ?></li>
						<li><strong><?php esc_html_e( 'Won', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'became a customer/booking/whatever counts as a win.', 'chatbotistic' ); ?></li>
						<li><strong><?php esc_html_e( 'Lost', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'didn\'t convert. Reopenable if you need to revisit it.', 'chatbotistic' ); ?></li>
					</ul>
					<p><?php esc_html_e( 'Search by name or phone, filter by widget/status/date range, export exactly the filtered rows as CSV, or click "Sync now" to pull the latest from Tochat on demand.', 'chatbotistic' ); ?></p>
					<div class="doc-callout">
						<?php echo cb_get_icon( 'wa', 17 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php esc_html_e( 'Every lead\'s detail view has an "Open WhatsApp chat" link that jumps straight into wa.me with their number pre-filled — the fastest way to actually reply.', 'chatbotistic' ); ?></span>
					</div>
				</article>

				<article class="docs2-article" id="connect-whatsapp">
					<h2><?php esc_html_e( 'Connect WhatsApp', 'chatbotistic' ); ?></h2>
					<p class="docs2-lead"><?php esc_html_e( 'Two ways to connect a WhatsApp number, in Settings → WhatsApp Connection.', 'chatbotistic' ); ?></p>
					<h3><?php esc_html_e( 'Option 1 — Personal number (leads only)', 'chatbotistic' ); ?></h3>
					<p><?php esc_html_e( 'The simplest option. Enter the WhatsApp number you already use — leads still flow into the dashboard normally, but you reply from your own phone. Nothing to authorize with Meta, and no message quota applies since sends don\'t go through the dashboard. Good for solo operators; the limitation is you can\'t send bulk Campaigns with a personal connection.', 'chatbotistic' ); ?></p>
					<h3><?php esc_html_e( 'Option 2 — Meta Cloud API', 'chatbotistic' ); ?></h3>
					<p><?php esc_html_e( 'Needed if you want to send Campaigns from the dashboard. You\'ll need three things from your Meta Business/WhatsApp Business Platform account:', 'chatbotistic' ); ?></p>
					<ol>
						<li><strong><?php esc_html_e( 'Phone number ID', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'from your WhatsApp Business Platform app in Meta\'s developer console.', 'chatbotistic' ); ?></li>
						<li><strong><?php esc_html_e( 'WABA ID', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'your WhatsApp Business Account ID.', 'chatbotistic' ); ?></li>
						<li><strong><?php esc_html_e( 'Access token', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'a permanent (or long-lived) token with messaging permissions.', 'chatbotistic' ); ?></li>
					</ol>
					<p><?php esc_html_e( 'Paste these into Settings → WhatsApp Connection → Meta Cloud API and save. The access token is encrypted (AES-256-GCM) before it\'s stored and is never sent back to your browser again.', 'chatbotistic' ); ?></p>
					<div class="doc-callout">
						<?php echo cb_get_icon( 'shield', 17 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php esc_html_e( 'You can switch between Personal and Meta Cloud API at any time — saving a new mode replaces the old connection, and disconnecting clears any saved credentials.', 'chatbotistic' ); ?></span>
					</div>
				</article>

				<article class="docs2-article" id="campaigns">
					<h2><?php esc_html_e( 'Campaigns & message limits', 'chatbotistic' ); ?></h2>
					<p class="docs2-lead"><?php esc_html_e( 'Send one message to a filtered slice of your leads, and understand your monthly quota.', 'chatbotistic' ); ?></p>
					<h3><?php esc_html_e( 'Creating a campaign', 'chatbotistic' ); ?></h3>
					<ol>
						<li><?php esc_html_e( 'Go to Campaigns and click New campaign.', 'chatbotistic' ); ?></li>
						<li><?php echo wp_kses( __( 'Name it, then write the message body. Use the <code>{{name}}</code>, <code>{{phone}}</code>, and <code>{{widget}}</code> chips to insert per-lead variables.', 'chatbotistic' ), array( 'code' => array() ) ); ?></li>
						<li><?php esc_html_e( 'Pick your audience — all leads, leads from one widget, or leads at a specific status. The recipient count updates live.', 'chatbotistic' ); ?></li>
						<li><?php esc_html_e( 'Choose send now or schedule for later.', 'chatbotistic' ); ?></li>
					</ol>
					<h3><?php esc_html_e( 'Monthly message limits by plan', 'chatbotistic' ); ?></h3>
					<div class="docs2-plan-grid">
						<div class="docs2-plan"><h4><?php esc_html_e( 'Free', 'chatbotistic' ); ?></h4><div class="price">100<?php esc_html_e( '/mo', 'chatbotistic' ); ?></div></div>
						<div class="docs2-plan"><h4><?php esc_html_e( 'Starter', 'chatbotistic' ); ?></h4><div class="price">1,000<?php esc_html_e( '/mo', 'chatbotistic' ); ?></div></div>
						<div class="docs2-plan"><h4><?php esc_html_e( 'Growth', 'chatbotistic' ); ?></h4><div class="price">5,000<?php esc_html_e( '/mo', 'chatbotistic' ); ?></div></div>
						<div class="docs2-plan"><h4><?php esc_html_e( 'Agency', 'chatbotistic' ); ?></h4><div class="price">25,000<?php esc_html_e( '/mo', 'chatbotistic' ); ?></div></div>
					</div>
					<p><?php esc_html_e( 'The usage meter on the Campaigns page (and Dashboard home) turns amber past 70% and red past 90% of your limit. Sending requires both a connected Meta Cloud API connection and remaining quota for the month — missing either turns "Send now" into a draft-only save with an explanation, rather than silently failing. The quota resets at the start of each calendar month.', 'chatbotistic' ); ?></p>
				</article>

				<article class="docs2-article" id="team-roles">
					<h2><?php esc_html_e( 'Team & roles', 'chatbotistic' ); ?></h2>
					<p class="docs2-lead"><?php esc_html_e( 'Roles, and how invites work.', 'chatbotistic' ); ?></p>
					<h3><?php esc_html_e( 'Roles', 'chatbotistic' ); ?></h3>
					<ul>
						<li><strong><?php esc_html_e( 'Owner', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'the organization\'s creator. Can\'t be removed or demoted; there\'s always exactly one.', 'chatbotistic' ); ?></li>
						<li><strong><?php esc_html_e( 'Admin', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'can manage organization settings, WhatsApp connection, white-label, and invite/remove teammates.', 'chatbotistic' ); ?></li>
						<li><strong><?php esc_html_e( 'Agent', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'can use the dashboard (leads, campaigns) but can\'t change org-level settings or manage the team.', 'chatbotistic' ); ?></li>
					</ul>
					<p><?php esc_html_e( 'Your plan caps total seats — Free 1, Starter 2, Growth 5, Agency 15.', 'chatbotistic' ); ?></p>
					<h3><?php esc_html_e( 'Inviting a teammate', 'chatbotistic' ); ?></h3>
					<p><?php esc_html_e( 'From Settings → Team, an owner/admin creates an invite (email + role) and gets a link to copy and send yourself — there\'s no automated invite email yet. The invitee needs a Chatbotistic account first (they sign up normally), then goes to Settings → Team → "Accept an invite" and pastes the token you sent them.', 'chatbotistic' ); ?></p>
					<div class="doc-callout">
						<?php echo cb_get_icon( 'users', 17 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php esc_html_e( 'Pending invites show in Settings → Team with a "Copy link" button and a revoke option. Owners/admins can also change a member\'s role or remove them from the same tab.', 'chatbotistic' ); ?></span>
					</div>
				</article>

				<article class="docs2-article" id="billing-plans">
					<h2><?php esc_html_e( 'Billing & plans', 'chatbotistic' ); ?></h2>
					<p class="docs2-lead"><?php esc_html_e( 'What each plan includes, and where billing happens.', 'chatbotistic' ); ?></p>
					<div class="docs2-plan-grid">
						<div class="docs2-plan">
							<h4><?php esc_html_e( 'Free', 'chatbotistic' ); ?></h4>
							<div class="price">$0</div>
							<ul><li><?php esc_html_e( '1 widget, 1 agent, 1 domain', 'chatbotistic' ); ?></li><li><?php esc_html_e( '1 seat', 'chatbotistic' ); ?></li><li><?php esc_html_e( '100 msgs/mo', 'chatbotistic' ); ?></li><li><?php esc_html_e( 'Chatbotistic branding', 'chatbotistic' ); ?></li></ul>
						</div>
						<div class="docs2-plan">
							<h4><?php esc_html_e( 'Starter', 'chatbotistic' ); ?></h4>
							<div class="price">$19<span style="font-size:12px;color:var(--text-dim);">/mo</span></div>
							<ul><li><?php esc_html_e( '3 widgets, 5 agents, 3 domains', 'chatbotistic' ); ?></li><li><?php esc_html_e( '2 seats', 'chatbotistic' ); ?></li><li><?php esc_html_e( '1,000 msgs/mo', 'chatbotistic' ); ?></li><li><?php esc_html_e( 'Booking forms', 'chatbotistic' ); ?></li></ul>
						</div>
						<div class="docs2-plan">
							<h4><?php esc_html_e( 'Growth', 'chatbotistic' ); ?></h4>
							<div class="price">$49<span style="font-size:12px;color:var(--text-dim);">/mo</span></div>
							<ul><li><?php esc_html_e( '10 widgets, 20 agents, 10 domains', 'chatbotistic' ); ?></li><li><?php esc_html_e( '5 seats', 'chatbotistic' ); ?></li><li><?php esc_html_e( '5,000 msgs/mo', 'chatbotistic' ); ?></li><li><?php esc_html_e( 'Landing pages, no branding', 'chatbotistic' ); ?></li></ul>
						</div>
						<div class="docs2-plan">
							<h4><?php esc_html_e( 'Agency', 'chatbotistic' ); ?></h4>
							<div class="price">$149<span style="font-size:12px;color:var(--text-dim);">/mo</span></div>
							<ul><li><?php esc_html_e( '30 widgets, unlimited agents', 'chatbotistic' ); ?></li><li><?php esc_html_e( '50 domains, 15 seats', 'chatbotistic' ); ?></li><li><?php esc_html_e( '25,000 msgs/mo', 'chatbotistic' ); ?></li><li><?php esc_html_e( 'White-label', 'chatbotistic' ); ?></li></ul>
						</div>
					</div>
					<div class="doc-callout">
						<?php echo cb_get_icon( 'spark', 17 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php echo wp_kses( __( 'Every limit above is a default — individual accounts may have overrides on top of the plan, which always take priority. Check Settings → Organization in your dashboard for your actual current limits and usage. <a href="/pricing/">Full plan comparison →</a>', 'chatbotistic' ), array( 'a' => array( 'href' => array() ) ) ); ?></span>
					</div>
					<h3><?php esc_html_e( 'Where billing happens', 'chatbotistic' ); ?></h3>
					<p><?php echo wp_kses( __( 'The dashboard doesn\'t process payments itself. Your plan, billing, and invoices are managed right here on <strong>chatbotistic.com</strong> — when you upgrade or downgrade, it syncs to your dashboard organization automatically the next time you sign in.', 'chatbotistic' ), array( 'strong' => array() ) ); ?></p>
					<h3><?php esc_html_e( 'What happens if I hit a limit?', 'chatbotistic' ); ?></h3>
					<ul>
						<li><strong><?php esc_html_e( 'Widgets/domains/agents/seats:', 'chatbotistic' ); ?></strong> <?php esc_html_e( 'the relevant "create" action is blocked with an upgrade prompt — nothing existing breaks.', 'chatbotistic' ); ?></li>
						<li><strong><?php esc_html_e( 'Messages:', 'chatbotistic' ); ?></strong> <?php esc_html_e( 'Campaign sending is blocked once you\'ve used the month\'s quota; drafts and scheduling still work, and it resets next month.', 'chatbotistic' ); ?></li>
					</ul>
				</article>

				<article class="docs2-article" id="white-label">
					<h2><?php esc_html_e( 'White-label setup', 'chatbotistic' ); ?></h2>
					<p class="docs2-lead"><?php esc_html_e( 'Rebrand the dashboard and widget for client portfolios — Agency plan.', 'chatbotistic' ); ?></p>
					<h3><?php esc_html_e( 'What you can customize', 'chatbotistic' ); ?></h3>
					<ul>
						<li><strong><?php esc_html_e( 'Brand name', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'replaces "Chatbotistic" in places your clients see.', 'chatbotistic' ); ?></li>
						<li><strong><?php esc_html_e( 'Primary color', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'used across the widget and, where applicable, the dashboard accent.', 'chatbotistic' ); ?></li>
						<li><strong><?php esc_html_e( 'Email sender name', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'the "from" name on transactional emails sent for your org.', 'chatbotistic' ); ?></li>
						<li><strong><?php esc_html_e( 'Custom domain', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'point your own subdomain at the dashboard instead of Chatbotistic\'s.', 'chatbotistic' ); ?></li>
					</ul>
					<h3><?php esc_html_e( 'Setting up a custom domain', 'chatbotistic' ); ?></h3>
					<ol>
						<li><?php echo wp_kses( __( 'Pick a subdomain you control, e.g. <code>chat.yourbrand.com</code>.', 'chatbotistic' ), array( 'code' => array() ) ); ?></li>
						<li><?php echo wp_kses( __( 'Keep the Chatbotistic dashboard at <code>app.chatbotistic.com</code>. The backend API remains on <code>services.tochat.be</code>.', 'chatbotistic' ), array( 'code' => array() ) ); ?></li>
						<li><?php esc_html_e( 'Do not point the dashboard hostname at the backend API: that would bypass the branded dashboard and can cause a routing failure.', 'chatbotistic' ); ?></li>
						<li><?php esc_html_e( 'If you need another customer-facing hostname later, provision it as a separate app route and verify TLS, host routing, and the white-label provider configuration first.', 'chatbotistic' ); ?></li>
					</ol>
					<div class="doc-callout">
						<?php echo cb_get_icon( 'tag', 17 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php esc_html_e( 'On any other plan, Settings → White-Label shows a locked upgrade prompt instead of the form above. Everything here takes effect immediately once saved, aside from DNS propagation for a custom domain.', 'chatbotistic' ); ?></span>
					</div>
				</article>

				<article class="docs2-article" id="faq">
					<h2><?php esc_html_e( 'FAQ', 'chatbotistic' ); ?></h2>
					<p class="docs2-lead"><?php esc_html_e( 'Quick answers to the questions that come up most.', 'chatbotistic' ); ?></p>
					<?php foreach ( $cb_faqs as $cb_q ) : ?>
						<h3><?php echo esc_html( $cb_q[0] ); ?></h3>
						<p><?php echo esc_html( $cb_q[1] ); ?></p>
					<?php endforeach; ?>

					<div style="padding:24px;border-radius:18px;background:linear-gradient(135deg,rgba(160,112,255,0.08),rgba(79,139,255,0.05));border:1px solid rgba(160,112,255,0.2);margin-top:40px;display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap;">
						<div>
							<h3 class="h-3" style="margin:0;"><?php esc_html_e( 'Need a hand?', 'chatbotistic' ); ?></h3>
							<p style="color:var(--text-soft);margin:4px 0 0;"><?php esc_html_e( 'Our support team replies in under 2 hours during business days.', 'chatbotistic' ); ?></p>
						</div>
						<a class="btn btn-primary" href="<?php echo esc_url( home_url( '/support/' ) ); ?>"><?php esc_html_e( 'Open support', 'chatbotistic' ); ?></a>
					</div>
				</article>

			</div>
		</div>
	</div>

</main>

<?php
get_footer();
