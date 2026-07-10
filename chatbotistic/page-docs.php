<?php
/**
 * Template Name: Documentation (V4)
 *
 * V4 port of pages/about-contact-docs.jsx → Docs. Static docs UI with a
 * sidebar-tabbed content area — one panel per topic, no page-wide TOC.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cb_sections = array(
	array(
		__( 'Getting started', 'chatbotistic' ),
		array(
			__( 'Quickstart', 'chatbotistic' ),
			__( 'Create your account', 'chatbotistic' ),
			__( 'Understanding plans & limits', 'chatbotistic' ),
		),
	),
	array(
		__( 'Widget Studio', 'chatbotistic' ),
		array(
			__( 'Creating a widget', 'chatbotistic' ),
			__( 'Customizing appearance', 'chatbotistic' ),
			__( 'Managing agents & FAQs', 'chatbotistic' ),
			__( 'Embedding on your site', 'chatbotistic' ),
		),
	),
	array(
		__( 'WordPress plugin', 'chatbotistic' ),
		array(
			__( 'Installing the plugin', 'chatbotistic' ),
			__( 'Activating your license', 'chatbotistic' ),
			__( 'Picking your widget', 'chatbotistic' ),
		),
	),
	array(
		__( 'Landing pages & bookings', 'chatbotistic' ),
		array(
			__( 'Landing pages', 'chatbotistic' ),
			__( 'Booking forms', 'chatbotistic' ),
		),
	),
	array(
		__( 'Leads & inbox', 'chatbotistic' ),
		array(
			__( 'Managing leads', 'chatbotistic' ),
			__( 'Connecting WhatsApp', 'chatbotistic' ),
			__( 'Using the team inbox', 'chatbotistic' ),
		),
	),
	array(
		__( 'Campaigns & billing', 'chatbotistic' ),
		array(
			__( 'Sending campaigns', 'chatbotistic' ),
			__( 'Message limits & quotas', 'chatbotistic' ),
			__( 'Plans & pricing', 'chatbotistic' ),
			__( 'White-label (Agency)', 'chatbotistic' ),
		),
	),
);

$cb_embed = '<script async src="https://services.tochat.be/widget/YOUR_WIDGET_ID/load.js"></script>';

$cb_faqs = array(
	array( __( 'How do I embed Chatbotistic on a non-WordPress site?', 'chatbotistic' ), __( 'Paste the one-line script tag from Widget Studio → Embed into your site’s HTML, just before the closing body tag. It loads asynchronously and won’t block your page.', 'chatbotistic' ) ),
	array( __( 'Can I customize the widget colors and branding?', 'chatbotistic' ), __( 'Yes. Widget Studio lets every plan set colors, position, and messages. Agency plans can also remove Chatbotistic branding entirely and white-label the dashboard.', 'chatbotistic' ) ),
	array( __( 'What happens to my leads and conversations if I cancel?', 'chatbotistic' ), __( 'You can export your leads to CSV at any time from the Leads page. We recommend exporting before cancelling, since active workspaces are required to keep data accessible.', 'chatbotistic' ) ),
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
			<p style="max-width:640px;margin:18px auto 0;"><?php esc_html_e( 'Quickstarts, Widget Studio guides, the WordPress plugin, and how leads, campaigns, and billing work.', 'chatbotistic' ); ?></p>
		</div>
	</section>

	<section class="section section-tight">
		<div class="container">
			<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'In the app', 'chatbotistic' ); ?></span>
			<h2 style="margin-top:12px;"><?php esc_html_e( 'Guides inside your Dashboard', 'chatbotistic' ); ?></h2>
			<p style="max-width:640px;"><?php esc_html_e( 'The quickstart below covers embedding the widget on this site. For the deeper, always up-to-date walkthroughs — building your widget, connecting WhatsApp, and managing your team — open the in-app docs from your Chatbotistic Dashboard.', 'chatbotistic' ); ?></p>
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
		<div class="docs-grid" style="grid-template-columns:240px 1fr;">

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
				<p><?php esc_html_e( 'Welcome to Chatbotistic. This guide gets your first widget live in a few minutes. You’ll create a widget in the Dashboard, then embed it on your site — either with a script tag or the WordPress plugin.', 'chatbotistic' ); ?></p>

				<h2><?php esc_html_e( '1. Create a widget', 'chatbotistic' ); ?></h2>
				<p>
					<?php
					echo wp_kses(
						__( 'Open your Chatbotistic Dashboard and go to <strong>Widget Studio</strong>. Click <strong>New widget</strong>, give it a name, and use the live preview to set colors, position, and welcome messages. Every widget gets its own unique widget ID.', 'chatbotistic' ),
						array( 'strong' => array() )
					);
					?>
				</p>

				<h2><?php esc_html_e( '2. Copy the embed snippet', 'chatbotistic' ); ?></h2>
				<p>
					<?php
					printf(
						wp_kses(
							/* translators: %s: YOUR_WIDGET_ID code token */
							__( 'From Widget Studio → Embed, copy the script tag below and paste it into your site, just before the closing body tag. Replace %s with your widget’s actual ID — the Dashboard shows you the exact snippet with it already filled in.', 'chatbotistic' ),
							array( 'code' => array() )
						),
						'<code>YOUR_WIDGET_ID</code>'
					);
					?>
				</p>
				<pre class="codeblock" style="white-space:pre;overflow:auto;"><?php echo esc_html( $cb_embed ); ?></pre>

				<h2><?php esc_html_e( '3. Or install the WordPress plugin', 'chatbotistic' ); ?></h2>
				<p>
					<?php
					echo wp_kses(
						__( 'If your site runs on WordPress, skip the code entirely. Install the <strong>Chatbotistic Widget</strong> plugin, paste the license key from your welcome email into <strong>Settings → Chatbotistic</strong>, and pick your widget from the dropdown. The plugin injects the script tag for you automatically.', 'chatbotistic' ),
						array( 'strong' => array() )
					);
					?>
				</p>

				<h2><?php esc_html_e( '4. Test it', 'chatbotistic' ); ?></h2>
				<p>
					<?php
					echo wp_kses(
						__( 'Reload your site and click the widget bubble in the corner. Send a message. Every conversation is captured as a lead automatically — check <strong>Leads</strong> in your Dashboard to see it appear.', 'chatbotistic' ),
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

			<article class="docs-content" data-doc-panel="0-1">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Getting started', 'chatbotistic' ); ?></span>
				<h1 style="margin-top:18px;"><?php esc_html_e( 'Create your account', 'chatbotistic' ); ?></h1>
				<p><?php esc_html_e( 'There’s no separate signup form for the Dashboard. Your Chatbotistic account is your chatbotistic.com account — the Dashboard signs you in automatically through it.', 'chatbotistic' ); ?></p>
				<h2><?php esc_html_e( 'How it works', 'chatbotistic' ); ?></h2>
				<p>
					<?php
					echo wp_kses(
						__( 'Create an account (or log in) on chatbotistic.com, then click <strong>Open Dashboard</strong> anywhere on the site. Because you’re already signed in, single sign-on takes you straight into dashboard.chatbotistic.com without a second signup step.', 'chatbotistic' ),
						array( 'strong' => array() )
					);
					?>
				</p>
				<h2><?php esc_html_e( 'What you get on day one', 'chatbotistic' ); ?></h2>
				<p><?php esc_html_e( 'New accounts start on Free Forever: one widget, one agent, one domain, one seat, and 100 WhatsApp messages a month — enough to fully test the product before you decide whether to upgrade.', 'chatbotistic' ); ?></p>
				<h2><?php esc_html_e( 'Multiple people on one team', 'chatbotistic' ); ?></h2>
				<p><?php esc_html_e( 'Invite teammates from Settings → Team using a shareable invite link. There’s no automated invite email yet, so send the link directly to whoever you’re adding.', 'chatbotistic' ); ?></p>
			</article>

			<article class="docs-content" data-doc-panel="0-2">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Getting started', 'chatbotistic' ); ?></span>
				<h1 style="margin-top:18px;"><?php esc_html_e( 'Understanding plans & limits', 'chatbotistic' ); ?></h1>
				<p><?php esc_html_e( 'Every plan caps five things: widgets, agents, domains, seats, and monthly WhatsApp messages. Check current usage anytime under Settings → Organization.', 'chatbotistic' ); ?></p>
				<h2><?php esc_html_e( 'The five plans', 'chatbotistic' ); ?></h2>
				<p>
					<?php
					echo wp_kses(
						__( '<strong>Free Forever</strong> — $0: 1 widget, 1 agent, 1 domain, 1 seat, 100 messages/mo.<br><strong>Starter</strong> — $19/mo: 3 widgets, 5 agents, 3 domains, 2 seats, 1,000 messages/mo.<br><strong>Growth</strong> — $49/mo: 10 widgets, 20 agents, 10 domains, 5 seats, 5,000 messages/mo — adds the landing page editor, AI replies, no Chatbotistic branding, and ad attribution.<br><strong>Agency</strong> — $149/mo: 30 widgets, unlimited agents, 50 domains, 15 seats, 25,000 messages/mo — full white-label, custom domain, and 10 client sub-accounts.<br><strong>Lifetime</strong> — a legacy plan, contact-only.', 'chatbotistic' ),
						array( 'strong' => array() )
					);
					?>
				</p>
				<h2><?php esc_html_e( 'What happens at the limit', 'chatbotistic' ); ?></h2>
				<p><?php esc_html_e( 'Hitting your widget, agent, domain, or seat cap blocks creating new ones until you upgrade or free up room. Hitting your monthly message quota pauses new campaign sends until the quota resets or you move to a bigger plan — existing leads and widget conversations still keep working.', 'chatbotistic' ); ?></p>
			</article>

			<article class="docs-content" data-doc-panel="1-0">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Widget Studio', 'chatbotistic' ); ?></span>
				<h1 style="margin-top:18px;"><?php esc_html_e( 'Creating a widget', 'chatbotistic' ); ?></h1>
				<p><?php esc_html_e( 'A widget is the chat bubble your visitors see. Everything about it — copy, colors, agents, FAQs — lives in Widget Studio.', 'chatbotistic' ); ?></p>
				<h2><?php esc_html_e( 'Steps', 'chatbotistic' ); ?></h2>
				<p>
					<?php
					echo wp_kses(
						__( '1. In the Dashboard, open <strong>Widget Studio</strong> and click <strong>New widget</strong>.<br>2. Name it something you’ll recognize later, like “Support – main site”.<br>3. Use the live preview panel to see your changes as you make them — nothing goes live until you save.<br>4. Save to generate the widget’s unique ID, used by both the embed script and the WordPress plugin.', 'chatbotistic' ),
						array( 'strong' => array() )
					);
					?>
				</p>
				<p><?php esc_html_e( 'Your plan’s widget cap applies here — Free Forever allows 1, Starter 3, Growth 10, Agency 30.', 'chatbotistic' ); ?></p>
			</article>

			<article class="docs-content" data-doc-panel="1-1">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Widget Studio', 'chatbotistic' ); ?></span>
				<h1 style="margin-top:18px;"><?php esc_html_e( 'Customizing appearance', 'chatbotistic' ); ?></h1>
				<p><?php esc_html_e( 'Widget Studio’s editor is visual — every change previews live before you save, so you never have to guess how it will look on your site.', 'chatbotistic' ); ?></p>
				<h2><?php esc_html_e( 'What you can change', 'chatbotistic' ); ?></h2>
				<p>
					<?php
					echo wp_kses(
						__( '<strong>Colors</strong> — bubble, header, and message bubble colors, matched to your brand.<br><strong>Position</strong> — bottom-left or bottom-right corner placement.<br><strong>Messages</strong> — the greeting, placeholder text, and any proactive opener message.<br><strong>Branding</strong> — Growth and Agency plans can remove the “Powered by Chatbotistic” badge.', 'chatbotistic' ),
						array( 'strong' => array() )
					);
					?>
				</p>
				<p><?php esc_html_e( 'Changes apply instantly to every site using that widget’s embed snippet — there’s nothing to redeploy.', 'chatbotistic' ); ?></p>
			</article>

			<article class="docs-content" data-doc-panel="1-2">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Widget Studio', 'chatbotistic' ); ?></span>
				<h1 style="margin-top:18px;"><?php esc_html_e( 'Managing agents & FAQs', 'chatbotistic' ); ?></h1>
				<p><?php esc_html_e( 'Agents are the people (or roles) a conversation can be assigned to. FAQs are the quick-answer prompts shown to visitors before they start typing.', 'chatbotistic' ); ?></p>
				<h2><?php esc_html_e( 'Agents', 'chatbotistic' ); ?></h2>
				<p><?php esc_html_e( 'Add agents from Widget Studio and attach them to a widget. Each agent can also have their own booking form, so a visitor can go from chatting straight to scheduling with that specific person.', 'chatbotistic' ); ?></p>
				<h2><?php esc_html_e( 'FAQs', 'chatbotistic' ); ?></h2>
				<p><?php esc_html_e( 'Add a short list of common questions and answers per widget. They appear as tappable suggestions inside the chat window, giving visitors an instant answer without waiting on a reply.', 'chatbotistic' ); ?></p>
				<p><?php esc_html_e( 'Agent count is capped by plan: 1 on Free Forever, 5 on Starter, 20 on Growth, unlimited on Agency.', 'chatbotistic' ); ?></p>
			</article>

			<article class="docs-content" data-doc-panel="1-3">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Widget Studio', 'chatbotistic' ); ?></span>
				<h1 style="margin-top:18px;"><?php esc_html_e( 'Embedding on your site', 'chatbotistic' ); ?></h1>
				<p><?php esc_html_e( 'Every widget embeds with a single script tag pointing at your widget’s unique load URL.', 'chatbotistic' ); ?></p>
				<pre class="codeblock" style="white-space:pre;overflow:auto;"><?php echo esc_html( $cb_embed ); ?></pre>
				<p>
					<?php
					echo wp_kses(
						__( 'Copy your exact snippet from Widget Studio → Embed — it already has your widget ID filled in. Paste it just before the closing <code>&lt;/body&gt;</code> tag on every page you want the widget to appear on. It loads asynchronously, so it never blocks the rest of your page from rendering.', 'chatbotistic' ),
						array( 'code' => array() )
					);
					?>
				</p>
				<p><?php esc_html_e( 'On WordPress, you can skip pasting HTML entirely — see the WordPress Plugin section for the zero-code path.', 'chatbotistic' ); ?></p>
				<p><?php esc_html_e( 'Your plan’s domain cap controls how many different sites can run a given widget. Domain caps run 1 → 3 → 10 → 50 across Free Forever → Starter → Growth → Agency.', 'chatbotistic' ); ?></p>
			</article>

			<article class="docs-content" data-doc-panel="2-0">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'WordPress plugin', 'chatbotistic' ); ?></span>
				<h1 style="margin-top:18px;"><?php esc_html_e( 'Installing the plugin', 'chatbotistic' ); ?></h1>
				<p><?php esc_html_e( 'The Chatbotistic Widget plugin is what your customers install on their own WordPress site — it’s not this marketing site’s theme, it’s a separate small plugin that injects the embed script for them.', 'chatbotistic' ); ?></p>
				<h2><?php esc_html_e( 'Steps', 'chatbotistic' ); ?></h2>
				<p>
					<?php
					echo wp_kses(
						__( '1. In wp-admin, go to <strong>Plugins → Add New Plugin</strong>.<br>2. Search for “Chatbotistic Widget” and click <strong>Install Now</strong>, then <strong>Activate</strong>.<br>3. A new <strong>Chatbotistic</strong> item appears in your WordPress admin menu.', 'chatbotistic' ),
						array( 'strong' => array() )
					);
					?>
				</p>
				<p><?php esc_html_e( 'No code, no theme edits, no child theme required — the plugin handles the script injection for you.', 'chatbotistic' ); ?></p>
			</article>

			<article class="docs-content" data-doc-panel="2-1">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'WordPress plugin', 'chatbotistic' ); ?></span>
				<h1 style="margin-top:18px;"><?php esc_html_e( 'Activating your license', 'chatbotistic' ); ?></h1>
				<p><?php esc_html_e( 'Your license key ties the plugin to your Chatbotistic account so it knows which widgets it’s allowed to show.', 'chatbotistic' ); ?></p>
				<h2><?php esc_html_e( 'Steps', 'chatbotistic' ); ?></h2>
				<p>
					<?php
					echo wp_kses(
						__( '1. Find your license key in the welcome email sent after checkout (or Free Forever signup).<br>2. In wp-admin, go to <strong>Settings → Chatbotistic</strong>.<br>3. Paste the key into <strong>License key</strong> and click <strong>Save</strong>. The plugin confirms the key against your account instantly.', 'chatbotistic' ),
						array( 'strong' => array() )
					);
					?>
				</p>
				<p><?php esc_html_e( 'Lost your key? Log in to chatbotistic.com and open your Dashboard — it’s listed under Settings → Organization.', 'chatbotistic' ); ?></p>
			</article>

			<article class="docs-content" data-doc-panel="2-2">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'WordPress plugin', 'chatbotistic' ); ?></span>
				<h1 style="margin-top:18px;"><?php esc_html_e( 'Picking your widget', 'chatbotistic' ); ?></h1>
				<p><?php esc_html_e( 'Once your license is active, the plugin pulls in every widget on your account so you can choose which one runs on this site.', 'chatbotistic' ); ?></p>
				<h2><?php esc_html_e( 'Steps', 'chatbotistic' ); ?></h2>
				<p>
					<?php
					echo wp_kses(
						__( '1. Go to <strong>Settings → Chatbotistic</strong>.<br>2. Open the <strong>Widget</strong> dropdown — it lists every widget you’ve built in Widget Studio.<br>3. Select the one you want live on this site and click <strong>Save</strong>. It appears immediately, no cache clearing needed.', 'chatbotistic' ),
						array( 'strong' => array() )
					);
					?>
				</p>
				<p><?php esc_html_e( 'Running Chatbotistic on several sites? Install the plugin on each and pick a different widget per site — that’s exactly what the domain limit on your plan is counting.', 'chatbotistic' ); ?></p>
			</article>

			<article class="docs-content" data-doc-panel="3-0">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Landing pages & bookings', 'chatbotistic' ); ?></span>
				<h1 style="margin-top:18px;"><?php esc_html_e( 'Landing pages', 'chatbotistic' ); ?></h1>
				<p><?php esc_html_e( 'Every widget can have its own hosted landing page — a shareable link with your widget embedded, plus a QR code, useful for print, storefronts, or campaigns that don’t point at your main site.', 'chatbotistic' ); ?></p>
				<h2><?php esc_html_e( 'Setting one up', 'chatbotistic' ); ?></h2>
				<p><?php esc_html_e( 'Open Landing Pages in the Dashboard, pick the widget it should represent, and Chatbotistic generates the page and a matching QR code automatically. Growth and Agency plans unlock the full landing page editor for deeper customization.', 'chatbotistic' ); ?></p>
			</article>

			<article class="docs-content" data-doc-panel="3-1">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Landing pages & bookings', 'chatbotistic' ); ?></span>
				<h1 style="margin-top:18px;"><?php esc_html_e( 'Booking forms', 'chatbotistic' ); ?></h1>
				<p><?php esc_html_e( 'Booking forms let a visitor go from chatting to scheduled, without leaving the conversation.', 'chatbotistic' ); ?></p>
				<h2><?php esc_html_e( 'How it works', 'chatbotistic' ); ?></h2>
				<p>
					<?php
					echo wp_kses(
						__( 'Each agent can have a booking form attached to them, built in the Dashboard under <strong>Bookings</strong>. Set your availability, and the form appears inline in the widget conversation whenever that agent is assigned, letting the visitor pick a time without switching apps.', 'chatbotistic' ),
						array( 'strong' => array() )
					);
					?>
				</p>
				<p><?php esc_html_e( 'Every booking is automatically linked back to the WhatsApp conversation and lead record it came from, so context isn’t lost.', 'chatbotistic' ); ?></p>
			</article>

			<article class="docs-content" data-doc-panel="4-0">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Leads & inbox', 'chatbotistic' ); ?></span>
				<h1 style="margin-top:18px;"><?php esc_html_e( 'Managing leads', 'chatbotistic' ); ?></h1>
				<p><?php esc_html_e( 'Every conversation started through any of your widgets becomes a lead automatically — no setup needed, and it works even without connecting WhatsApp.', 'chatbotistic' ); ?></p>
				<h2><?php esc_html_e( 'What you can do', 'chatbotistic' ); ?></h2>
				<p>
					<?php
					echo wp_kses(
						__( 'Open <strong>Leads</strong> in the Dashboard to filter by widget, date, or status, move a lead through the pipeline (<strong>New → Contacted → Won/Lost</strong>), and export any filtered view to CSV.', 'chatbotistic' ),
						array( 'strong' => array() )
					);
					?>
				</p>
				<p><?php esc_html_e( 'Growth and Agency plans also get ad attribution, so you can see which campaign or source a lead originally came from.', 'chatbotistic' ); ?></p>
			</article>

			<article class="docs-content" data-doc-panel="4-1">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Leads & inbox', 'chatbotistic' ); ?></span>
				<h1 style="margin-top:18px;"><?php esc_html_e( 'Connecting WhatsApp', 'chatbotistic' ); ?></h1>
				<p><?php esc_html_e( 'Leads capture works out of the box. To get two-way chat inside the Dashboard’s team inbox, connect your own WhatsApp Business number.', 'chatbotistic' ); ?></p>
				<h2><?php esc_html_e( 'Two ways to connect', 'chatbotistic' ); ?></h2>
				<p>
					<?php
					echo wp_kses(
						__( 'In <strong>Settings → WhatsApp</strong>, choose either <strong>Meta Cloud API</strong> or <strong>Twilio</strong> and enter your credentials. Click <strong>Test connection</strong> — this makes a real check against the provider before saving, so you’ll know immediately if something’s misconfigured.', 'chatbotistic' ),
						array( 'strong' => array() )
					);
					?>
				</p>
				<p><?php esc_html_e( 'Without a connection, leads and widget conversations keep working fine — you just won’t be able to reply from inside the Dashboard’s inbox.', 'chatbotistic' ); ?></p>
			</article>

			<article class="docs-content" data-doc-panel="4-2">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Leads & inbox', 'chatbotistic' ); ?></span>
				<h1 style="margin-top:18px;"><?php esc_html_e( 'Using the team inbox', 'chatbotistic' ); ?></h1>
				<p><?php esc_html_e( 'The Inbox is a shared view of every WhatsApp conversation, once you’ve connected a WhatsApp number in Settings.', 'chatbotistic' ); ?></p>
				<h2><?php esc_html_e( 'What it gives you', 'chatbotistic' ); ?></h2>
				<p><?php esc_html_e( 'Teammates can see and reply to the same conversations, hand off a chat to another agent, and reference the linked lead record without switching screens. Seat limits by plan control how many teammates can be active in the inbox at once — 1 on Free Forever, up to 15 on Agency.', 'chatbotistic' ); ?></p>
			</article>

			<article class="docs-content" data-doc-panel="5-0">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Campaigns & billing', 'chatbotistic' ); ?></span>
				<h1 style="margin-top:18px;"><?php esc_html_e( 'Sending campaigns', 'chatbotistic' ); ?></h1>
				<p><?php esc_html_e( 'Campaigns send a WhatsApp broadcast to a filtered slice of your leads — useful for re-engaging cold leads or announcing something to everyone who’s Won.', 'chatbotistic' ); ?></p>
				<h2><?php esc_html_e( 'Steps', 'chatbotistic' ); ?></h2>
				<p>
					<?php
					echo wp_kses(
						__( '1. Open <strong>Campaigns</strong> in the Dashboard and click <strong>New campaign</strong>.<br>2. Filter your leads by widget, status, or date to build the recipient list.<br>3. Write the message and send, or schedule it for later.', 'chatbotistic' ),
						array( 'strong' => array() )
					);
					?>
				</p>
				<p><?php esc_html_e( 'Campaigns require a connected WhatsApp number and draw from your plan’s monthly message quota, shared with regular widget conversations.', 'chatbotistic' ); ?></p>
			</article>

			<article class="docs-content" data-doc-panel="5-1">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Campaigns & billing', 'chatbotistic' ); ?></span>
				<h1 style="margin-top:18px;"><?php esc_html_e( 'Message limits & quotas', 'chatbotistic' ); ?></h1>
				<p><?php esc_html_e( 'Every plan includes a monthly WhatsApp message allowance, shared across widget conversations and campaign sends.', 'chatbotistic' ); ?></p>
				<h2><?php esc_html_e( 'Quotas by plan', 'chatbotistic' ); ?></h2>
				<p>
					<?php
					echo wp_kses(
						__( '<strong>Free Forever</strong> — 100 messages/mo. <strong>Starter</strong> — 1,000/mo. <strong>Growth</strong> — 5,000/mo. <strong>Agency</strong> — 25,000/mo.', 'chatbotistic' ),
						array( 'strong' => array() )
					);
					?>
				</p>
				<p><?php esc_html_e( 'Track usage in real time under Settings → Organization. When you’re close to the limit, upgrading is the fastest way to keep campaigns sending — the quota resets each billing cycle.', 'chatbotistic' ); ?></p>
			</article>

			<article class="docs-content" data-doc-panel="5-2">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Campaigns & billing', 'chatbotistic' ); ?></span>
				<h1 style="margin-top:18px;"><?php esc_html_e( 'Plans & pricing', 'chatbotistic' ); ?></h1>
				<p><?php esc_html_e( 'Chatbotistic has five plans, each raising the caps on widgets, agents, domains, seats, and monthly messages.', 'chatbotistic' ); ?></p>
				<p>
					<?php
					echo wp_kses(
						__( '<strong>Free Forever</strong> — $0/mo: 1 widget, 1 agent, 1 domain, 1 seat, 100 messages.<br><strong>Starter</strong> — $19/mo: 3 widgets, 5 agents, 3 domains, 2 seats, 1,000 messages.<br><strong>Growth</strong> — $49/mo: 10 widgets, 20 agents, 10 domains, 5 seats, 5,000 messages, plus the landing page editor, AI replies, no branding, and ad attribution.<br><strong>Agency</strong> — $149/mo: 30 widgets, unlimited agents, 50 domains, 15 seats, 25,000 messages, plus full white-label and 10 client sub-accounts.<br><strong>Lifetime</strong> — a legacy plan, available by contacting us directly.', 'chatbotistic' ),
						array( 'strong' => array() )
					);
					?>
				</p>
				<p><?php esc_html_e( 'You can change plans anytime from Settings → Organization — upgrades apply immediately.', 'chatbotistic' ); ?></p>
			</article>

			<article class="docs-content" data-doc-panel="5-3">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Campaigns & billing', 'chatbotistic' ); ?></span>
				<h1 style="margin-top:18px;"><?php esc_html_e( 'White-label (Agency)', 'chatbotistic' ); ?></h1>
				<p><?php esc_html_e( 'The Agency plan lets you run the Dashboard under your own brand for clients — your logo, your domain, your colors.', 'chatbotistic' ); ?></p>
				<h2><?php esc_html_e( 'What you can set', 'chatbotistic' ); ?></h2>
				<p>
					<?php
					echo wp_kses(
						__( 'In <strong>Settings → White-Label</strong>, upload your logo, choose your accent colors, and point a custom domain at your Dashboard instance. Agency also includes 10 client sub-accounts, so you can manage separate client workspaces without mixing their widgets, leads, or usage.', 'chatbotistic' ),
						array( 'strong' => array() )
					);
					?>
				</p>
			</article>

		</div>
	</div>

</main>

<?php
get_footer();
