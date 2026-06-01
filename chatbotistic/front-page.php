<?php
/**
 * Home page — V4 port.
 *
 * Sections: hero (with operational flow strip + routing layer + chat
 * widget mock), problem grid, solution + dashboard mock, feature grid,
 * "How it works" operational pipeline, use-case preview, WhatsApp +
 * booking strips, integrations, pricing preview, ecosystem phases,
 * final CTA. Uses V4's exact class names so v4-styles.css applies
 * directly.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cb_features = array(
	array( 'ai',    __( 'AI Chatbot Widgets', 'chatbotistic' ),    __( 'Trained on your site, FAQs, and services. Answers in your tone, 24/7.', 'chatbotistic' ) ),
	array( 'wa',    __( 'WhatsApp Chat Widgets', 'chatbotistic' ), __( 'Click-to-chat with department routing and pre-chat lead capture.', 'chatbotistic' ) ),
	array( 'form',  __( 'Lead Capture Forms', 'chatbotistic' ),    __( 'Conversion-optimized inline, popup, and sidebar lead forms.', 'chatbotistic' ) ),
	array( 'cal',   __( 'Booking Forms', 'chatbotistic' ),         __( 'Consultations, services, and strategy calls with calendar sync.', 'chatbotistic' ) ),
	array( 'inbox', __( 'Multi-Agent Inbox', 'chatbotistic' ),     __( 'Shared team inbox for live chat, WhatsApp, and email replies.', 'chatbotistic' ) ),
	array( 'mail',  __( 'Email Notifications', 'chatbotistic' ),   __( 'Instant pings to your team the moment a new lead lands.', 'chatbotistic' ) ),
	array( 'plug',  __( 'CRM Integrations', 'chatbotistic' ),      __( 'HubSpot, Zoho, Pipedrive, Google Sheets, and more out of the box.', 'chatbotistic' ) ),
	array( 'card',  __( 'Stripe & PayPal', 'chatbotistic' ),       __( 'Take deposits or payments inside the same chatbot flow.', 'chatbotistic' ) ),
	array( 'page',  __( 'Landing Page Tools', 'chatbotistic' ),    __( 'Spin up high-converting micro-pages with built-in chat.', 'chatbotistic' ) ),
	array( 'tag',   __( 'White Label', 'chatbotistic' ),           __( 'Your logo, your domain, your brand. Resell with full ownership.', 'chatbotistic' ) ),
	array( 'code',  __( 'API & Webhooks', 'chatbotistic' ),        __( 'Push leads anywhere. Custom flows with REST and webhooks.', 'chatbotistic' ) ),
	array( 'chart', __( 'Analytics & Reports', 'chatbotistic' ),   __( 'Conversion funnels, agent performance, and ROI dashboards.', 'chatbotistic' ) ),
);

$cb_problems = array(
	array( '01', __( 'No conversation starts', 'chatbotistic' ),        __( 'Visitors leave without ever opening a chat. You never learn they were interested.', 'chatbotistic' ) ),
	array( '02', __( 'Forms create friction', 'chatbotistic' ),         __( 'Long contact forms ask for too much, too early — so most people simply don’t.', 'chatbotistic' ) ),
	array( '03', __( 'Replies happen too late', 'chatbotistic' ),       __( 'By the time someone answers from a shared inbox, the buyer has moved on.', 'chatbotistic' ) ),
	array( '04', __( 'WhatsApp leads aren’t tracked', 'chatbotistic' ), __( 'Chats happen on phones, off the record, with no source, stage, or follow-up.', 'chatbotistic' ) ),
	array( '05', __( 'Site and sales are disconnected', 'chatbotistic' ), __( 'Website traffic and WhatsApp conversations live in two separate worlds.', 'chatbotistic' ) ),
	array( '06', __( 'Agencies deploy too slowly', 'chatbotistic' ),    __( 'Every client site needs chat wired up by hand — there’s no fast, repeatable system.', 'chatbotistic' ) ),
);

$cb_how = array(
	array( '01', 'card',  __( 'Choose a plan', 'chatbotistic' ),         __( 'Pick the Chatbotistic plan that fits your business or agency.', 'chatbotistic' ) ),
	array( '02', 'user',  __( 'Enter your portal', 'chatbotistic' ),     __( 'Your branded member dashboard — widgets, leads, and settings.', 'chatbotistic' ) ),
	array( '03', 'wa',    __( 'Create your widget', 'chatbotistic' ),    __( 'Configure a WhatsApp chatbot widget with routing and capture.', 'chatbotistic' ) ),
	array( '04', 'lock',  __( 'Get your license', 'chatbotistic' ),      __( 'Chatbotistic issues a Licenseistic key for your approved domains.', 'chatbotistic' ) ),
	array( '05', 'wp',    __( 'Install the addon', 'chatbotistic' ),     __( 'Add the lightweight WordPress addon and activate your license.', 'chatbotistic' ) ),
	array( '06', 'check', __( 'Activate and go live', 'chatbotistic' ),  __( 'The widget appears on your site and starts capturing leads.', 'chatbotistic' ), true ),
);

$cb_solutions = array(
	array( 'wa',    __( 'Create widgets from the dashboard', 'chatbotistic' ),  __( 'Build WhatsApp chatbot widgets in your Chatbotistic portal.', 'chatbotistic' ) ),
	array( 'wp',    __( 'Connect a lightweight WordPress addon', 'chatbotistic' ), __( 'Install once, no heavy plugin — the addon links your site to the system.', 'chatbotistic' ) ),
	array( 'lock',  __( 'Validate usage with Licenseistic', 'chatbotistic' ),   __( 'Each deployment is licensed and scoped to your approved domains.', 'chatbotistic' ) ),
	array( 'chat',  __( 'Route visitors into WhatsApp', 'chatbotistic' ),       __( 'Turn website visits into real conversations with pre-chat capture.', 'chatbotistic' ) ),
	array( 'inbox', __( 'Manage leads from one branded portal', 'chatbotistic' ), __( 'Leads, automation, and settings in a single Chatbotistic dashboard.', 'chatbotistic' ) ),
	array( 'users', __( 'Scale to agency-level deployment', 'chatbotistic' ),   __( 'Go from one business site to many client domains, cleanly.', 'chatbotistic' ) ),
);

$cb_usecases = array(
	array( 'agencies',         __( 'Agencies', 'chatbotistic' ),         __( 'Resell to clients', 'chatbotistic' ) ),
	array( 'local-business',   __( 'Local businesses', 'chatbotistic' ), __( 'Capture walk-ins', 'chatbotistic' ) ),
	array( 'clinics-spas',     __( 'Clinics & spas', 'chatbotistic' ),   __( 'Service booking', 'chatbotistic' ) ),
	array( 'coaches',          __( 'Coaches', 'chatbotistic' ),          __( 'Discovery flows', 'chatbotistic' ) ),
	array( 'real-estate',      __( 'Real estate', 'chatbotistic' ),      __( 'Property leads', 'chatbotistic' ) ),
	array( 'ecommerce',        __( 'eCommerce', 'chatbotistic' ),        __( 'Cart recovery', 'chatbotistic' ) ),
	array( 'travel-agencies',  __( 'Travel agencies', 'chatbotistic' ),  __( 'Trip enquiries', 'chatbotistic' ) ),
	array( 'wordpress-sites',  __( 'WordPress sites', 'chatbotistic' ),  __( 'Native plugin', 'chatbotistic' ) ),
	array( 'saas-founders',    __( 'SaaS founders', 'chatbotistic' ),    __( 'Onboarding bots', 'chatbotistic' ) ),
);

$cb_integrations = array(
	__( 'WordPress', 'chatbotistic' ),
	__( 'WooCommerce', 'chatbotistic' ),
	__( 'HubSpot', 'chatbotistic' ),
	__( 'Zoho', 'chatbotistic' ),
	__( 'Stripe', 'chatbotistic' ),
	__( 'PayPal', 'chatbotistic' ),
	__( 'Google Sheets', 'chatbotistic' ),
	__( 'Email', 'chatbotistic' ),
	__( 'WhatsApp', 'chatbotistic' ),
	__( 'Webhooks', 'chatbotistic' ),
);

$cb_public_flow = array( __( 'Plan', 'chatbotistic' ), __( 'Portal', 'chatbotistic' ), __( 'Widget', 'chatbotistic' ), __( 'License', 'chatbotistic' ), __( 'WordPress Addon', 'chatbotistic' ), __( 'Live Chat', 'chatbotistic' ) );
?>

<main class="page-fade">

	<!-- HERO -->
	<section class="section hero">
		<div class="container">
			<div class="hero-grid">
				<div>
					<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'WordPress · WhatsApp lead system', 'chatbotistic' ); ?></span>
					<h1 class="h-display" style="margin-top:22px;">
						<span class="text-grad"><?php esc_html_e( 'Turn your WordPress website into a ', 'chatbotistic' ); ?></span>
						<span class="text-grad-accent"><?php esc_html_e( 'WhatsApp lead system.', 'chatbotistic' ); ?></span>
					</h1>
					<p class="lead" style="margin-top:22px;">
						<?php esc_html_e( 'Chatbotistic lets businesses create WhatsApp chatbot widgets, connect them with a licensed WordPress addon, and capture more conversations from every website visit.', 'chatbotistic' ); ?>
					</p>
					<div style="display:flex;gap:12px;margin-top:32px;flex-wrap:wrap;">
						<a class="btn btn-primary btn-lg" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'Start with Chatbotistic', 'chatbotistic' ); ?></a>
						<a class="btn btn-ghost btn-lg" href="#how-it-works"><?php esc_html_e( 'See how it works', 'chatbotistic' ); ?></a>
					</div>

					<!-- Hero operational flow strip -->
					<div class="op-flowstrip" style="margin-top:32px;">
						<div class="scan op-scan"></div>
						<div class="sysmap">
							<div class="sysmap-label"><span class="ld"></span><?php esc_html_e( 'Operational flow', 'chatbotistic' ); ?></div>
							<div class="sysmap-track">
								<?php
								$cb_n = count( $cb_public_flow ) - 1;
								foreach ( $cb_public_flow as $cb_i => $cb_step ) :
									$cb_live = ( $cb_i === $cb_n );
									?>
									<div class="sysmap-node<?php echo $cb_live ? ' live' : ''; ?>"><span class="nd<?php echo $cb_live ? ' op-pulse' : ''; ?>"></span><?php echo esc_html( $cb_step ); ?></div>
									<?php if ( $cb_i < $cb_n ) : ?>
										<div class="sysmap-rail"><span class="op-flow op-signal" style="animation-delay:<?php echo esc_attr( $cb_i * 0.5 ); ?>s"></span></div>
									<?php endif; ?>
								<?php endforeach; ?>
							</div>
						</div>
					</div>

					<div class="hero-meta">
						<span><?php esc_html_e( '✓ No credit card required', 'chatbotistic' ); ?></span>
						<span><?php esc_html_e( '✓ Licensed WordPress addon', 'chatbotistic' ); ?></span>
						<span><?php esc_html_e( '✓ Live widget in minutes', 'chatbotistic' ); ?></span>
					</div>
				</div>

				<div class="widget-stack">
					<!-- Hero routing layer (SVG rails behind the chat widget) -->
					<div class="hero-routing" aria-hidden="true">
						<svg viewBox="0 0 500 520" preserveAspectRatio="xMidYMid meet">
							<path class="op-rail-line" d="M56 64 V250 V446 H444 V86 H56 Z" />
							<path class="op-rail-flow op-flow" d="M56 64 V250 V446 H444 V86 H56 Z" />
							<g><circle class="hero-rnode" cx="56"  cy="64"  r="6" /><circle cx="56"  cy="64"  r="2.4" fill="#3fdcff" /><text class="hero-rnode-label" x="56"  y="52"  text-anchor="middle">WEBSITE</text></g>
							<g><circle class="hero-rnode" cx="56"  cy="250" r="6" /><circle cx="56"  cy="250" r="2.4" fill="#3fdcff" /><text class="hero-rnode-label" x="56"  y="238" text-anchor="middle">PORTAL</text></g>
							<g><circle class="hero-rnode" cx="70"  cy="446" r="6" /><circle cx="70"  cy="446" r="2.4" fill="#3fdcff" /><text class="hero-rnode-label" x="70"  y="434" text-anchor="middle">LICENSE</text></g>
							<g><circle class="hero-rnode" cx="444" cy="452" r="6" /><circle cx="444" cy="452" r="2.4" fill="#3fdcff" /><text class="hero-rnode-label" x="444" y="440" text-anchor="middle">WP ADDON</text></g>
							<g><circle class="hero-rnode" cx="452" cy="86"  r="6" /><circle cx="452" cy="86"  r="2.4" fill="#34d399" /><text class="hero-rnode-label" x="452" y="74"  text-anchor="middle">WHATSAPP</text></g>
						</svg>
					</div>

					<!-- Chat widget mock (static, V4 styling) -->
					<div class="chat-widget glass-edge">
						<div class="cw-head">
							<div class="cw-avatar">C</div>
							<div class="cw-head-text">
								<b><?php esc_html_e( 'Chatbotistic Assistant', 'chatbotistic' ); ?></b>
								<span><?php esc_html_e( 'Online · replies instantly', 'chatbotistic' ); ?></span>
							</div>
						</div>
						<div class="cw-body">
							<div class="bubble bot"><?php esc_html_e( '👋 Hey there! I’m here to help. Looking for a service quote or want to book a call?', 'chatbotistic' ); ?></div>
							<div class="bubble user"><?php esc_html_e( 'I need a quote for a new website', 'chatbotistic' ); ?></div>
							<div class="bubble bot"><?php esc_html_e( 'Got it — I can connect you with the team in 30 seconds. What’s your project budget?', 'chatbotistic' ); ?></div>
							<div class="cw-quick">
								<button type="button"><?php esc_html_e( 'Under $5k', 'chatbotistic' ); ?></button>
								<button type="button"><?php esc_html_e( '$5k – $15k', 'chatbotistic' ); ?></button>
								<button type="button"><?php esc_html_e( '$15k+', 'chatbotistic' ); ?></button>
							</div>
						</div>
						<div class="cw-input">
							<input placeholder="<?php esc_attr_e( 'Type a message…', 'chatbotistic' ); ?>" readonly>
							<button class="cw-send" type="button" aria-label="<?php esc_attr_e( 'Send', 'chatbotistic' ); ?>">→</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- PROBLEM -->
	<section class="section section-tight">
		<div class="container">
			<div style="max-width:720px;">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'The problem', 'chatbotistic' ); ?></span>
				<h2 class="h-1 text-grad" style="margin-top:18px;"><?php esc_html_e( 'Your website gets traffic. The problem is what happens after the visit.', 'chatbotistic' ); ?></h2>
				<p class="lead" style="margin-top:18px;"><?php esc_html_e( 'The page loads, the visitor reads, the visitor leaves. Built for WordPress businesses that want more conversations from existing traffic — here’s where it breaks down today.', 'chatbotistic' ); ?></p>
			</div>
			<div class="problem-grid">
				<?php foreach ( $cb_problems as $cb_p ) : ?>
					<div class="problem-card">
						<div class="strike">✕</div>
						<div class="ix"><?php echo esc_html( $cb_p[0] ); ?></div>
						<h3><?php echo esc_html( $cb_p[1] ); ?></h3>
						<p><?php echo esc_html( $cb_p[2] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- SOLUTION -->
	<section class="section">
		<div class="container">
			<div class="split-2">
				<div>
					<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'The solution', 'chatbotistic' ); ?></span>
					<h2 class="h-1 text-grad" style="margin-top:18px;"><?php esc_html_e( 'One licensed chatbot system for WordPress websites.', 'chatbotistic' ); ?></h2>
					<p class="lead" style="margin-top:18px;"><?php esc_html_e( 'Not just a chat button — a managed conversation layer for WordPress. Create once, connect with the addon, and deploy across approved domains from a single branded portal.', 'chatbotistic' ); ?></p>
					<div class="solution-list">
						<?php foreach ( $cb_solutions as $cb_s ) : ?>
							<div class="solution-item">
								<div class="si-ico"></div>
								<div><b><?php echo esc_html( $cb_s[1] ); ?></b><span><?php echo esc_html( $cb_s[2] ); ?></span></div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="dash-mock glass-edge">
					<div class="dash-head">
						<div class="dh-dots"><span></span><span></span><span></span></div>
						<div class="dh-tabs">
							<button class="dh-tab active"><?php esc_html_e( 'Inbox', 'chatbotistic' ); ?></button>
							<button class="dh-tab"><?php esc_html_e( 'Leads', 'chatbotistic' ); ?></button>
							<button class="dh-tab"><?php esc_html_e( 'Bookings', 'chatbotistic' ); ?></button>
						</div>
						<div class="dh-search"><?php esc_html_e( 'Search conversations…', 'chatbotistic' ); ?></div>
					</div>
					<div class="dash-body">
						<div class="dash-main">
							<div class="dm-stats">
								<div class="dm-stat"><div class="label"><?php esc_html_e( 'Conversations', 'chatbotistic' ); ?></div><div class="val">2,418</div><div class="delta"><?php esc_html_e( '▲ 18% week', 'chatbotistic' ); ?></div></div>
								<div class="dm-stat"><div class="label"><?php esc_html_e( 'Leads captured', 'chatbotistic' ); ?></div><div class="val">487</div><div class="delta"><?php esc_html_e( '▲ 24% week', 'chatbotistic' ); ?></div></div>
								<div class="dm-stat"><div class="label"><?php esc_html_e( 'Bookings', 'chatbotistic' ); ?></div><div class="val">96</div><div class="delta"><?php esc_html_e( '▲ 11% week', 'chatbotistic' ); ?></div></div>
							</div>
							<div class="dm-chart">
								<div class="dm-chart-head">
									<b><?php esc_html_e( 'Lead flow · last 14 days', 'chatbotistic' ); ?></b>
									<span class="mono" style="color:var(--text-dim);"><?php esc_html_e( '+38.2% MoM', 'chatbotistic' ); ?></span>
								</div>
								<div class="dm-spark">
									<?php foreach ( array( 18, 22, 15, 30, 28, 35, 42, 38, 45, 52, 49, 58, 64, 72 ) as $cb_h ) : ?>
										<span style="height:<?php echo esc_attr( $cb_h ); ?>%;"></span>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- FEATURE GRID -->
	<section class="section">
		<div class="container">
			<div style="text-align:center;max-width:680px;margin:0 auto;">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Everything you need', 'chatbotistic' ); ?></span>
				<h2 class="h-1 text-grad" style="margin-top:18px;"><?php esc_html_e( 'A complete conversation platform — not a chat widget.', 'chatbotistic' ); ?></h2>
				<p class="lead" style="margin:18px auto 0;"><?php esc_html_e( 'Twelve tightly integrated modules. Pick what you need, scale to the rest.', 'chatbotistic' ); ?></p>
			</div>
			<div class="features-grid">
				<?php foreach ( $cb_features as $cb_i => $cb_f ) : ?>
					<div class="f-card">
						<div class="f-ico"></div>
						<?php if ( 0 === $cb_i ) : ?><div class="ribbon"><?php esc_html_e( 'CORE', 'chatbotistic' ); ?></div><?php endif; ?>
						<h3><?php echo esc_html( $cb_f[1] ); ?></h3>
						<p><?php echo esc_html( $cb_f[2] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- HOW IT WORKS -->
	<section id="how-it-works" class="section">
		<div class="container">
			<div style="max-width:760px;">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'How it works', 'chatbotistic' ); ?></span>
				<h2 class="h-1 text-grad" style="margin-top:18px;"><?php esc_html_e( 'How Chatbotistic connects your website to WhatsApp automation.', 'chatbotistic' ); ?></h2>
				<p class="lead" style="margin-top:18px;"><?php esc_html_e( 'From plan to live widget in six operational steps — each one a node in the same connected system.', 'chatbotistic' ); ?></p>
			</div>
			<div class="op-pipeline">
				<div class="op-pipeline-track">
					<?php foreach ( $cb_how as $cb_s ) :
						$cb_live = isset( $cb_s[4] ) && $cb_s[4]; ?>
						<div class="op-stepcard<?php echo $cb_live ? ' live' : ''; ?>">
							<?php if ( $cb_live ) : ?>
								<div class="op-livechip"><span class="d op-pulse"></span><?php esc_html_e( 'Live', 'chatbotistic' ); ?></div>
							<?php endif; ?>
							<div class="op-node"></div>
							<div class="op-step-ix"><?php echo esc_html( $cb_s[0] ); ?></div>
							<h3><?php echo esc_html( $cb_s[2] ); ?></h3>
							<p><?php echo esc_html( $cb_s[3] ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<div style="display:flex;justify-content:center;margin-top:44px;">
				<a class="btn btn-primary btn-lg" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'Create your widget', 'chatbotistic' ); ?></a>
			</div>
		</div>
	</section>

	<!-- USE CASES -->
	<section class="section">
		<div class="container">
			<div style="display:flex;justify-content:space-between;align-items:flex-end;gap:32px;flex-wrap:wrap;">
				<div style="max-width:580px;">
					<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Built for', 'chatbotistic' ); ?></span>
					<h2 class="h-1 text-grad" style="margin-top:18px;"><?php esc_html_e( 'Service brands. Agencies. Storefronts.', 'chatbotistic' ); ?></h2>
				</div>
				<a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/use-cases/' ) ); ?>"><?php esc_html_e( 'See all use cases', 'chatbotistic' ); ?></a>
			</div>
			<div class="use-grid">
				<?php foreach ( $cb_usecases as $cb_u ) : ?>
					<a class="use-card" href="<?php echo esc_url( home_url( '/' . $cb_u[0] . '/' ) ); ?>">
						<div class="use-emoji"></div>
						<div><b><?php echo esc_html( $cb_u[1] ); ?></b><span><?php echo esc_html( $cb_u[2] ); ?></span></div>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- INTEGRATIONS -->
	<section class="section">
		<div class="container">
			<div style="text-align:center;max-width:680px;margin:0 auto;">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Integrations', 'chatbotistic' ); ?></span>
				<h2 class="h-1 text-grad" style="margin-top:18px;"><?php esc_html_e( 'Connects with the tools you already pay for.', 'chatbotistic' ); ?></h2>
				<p class="lead" style="margin:18px auto 0;"><?php esc_html_e( 'Native integrations for WordPress, WooCommerce, HubSpot, Zoho, Stripe, PayPal, Google Sheets, Email, WhatsApp, and Webhooks.', 'chatbotistic' ); ?></p>
			</div>
			<div class="int-grid">
				<?php foreach ( $cb_integrations as $cb_int ) : ?>
					<div class="int-tile">
						<div class="int-logo"></div>
						<span><?php echo esc_html( $cb_int ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- PRICING PREVIEW -->
	<section class="section">
		<div class="container">
			<div style="text-align:center;max-width:680px;margin:0 auto;">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Pricing preview', 'chatbotistic' ); ?></span>
				<h2 class="h-1 text-grad" style="margin-top:18px;"><?php esc_html_e( 'Start free. Scale when you grow.', 'chatbotistic' ); ?></h2>
				<p class="lead" style="margin:18px auto 0;"><?php esc_html_e( 'Four plans. Honest pricing. No per-seat surprises.', 'chatbotistic' ); ?></p>
			</div>
			<div style="display:flex;justify-content:center;margin-top:32px;flex-wrap:wrap;gap:12px;">
				<a class="btn btn-primary btn-lg" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'See all plans', 'chatbotistic' ); ?></a>
				<a class="btn btn-ghost btn-lg" href="<?php echo esc_url( home_url( '/book-demo/' ) ); ?>"><?php esc_html_e( 'Book a demo', 'chatbotistic' ); ?></a>
			</div>
		</div>
	</section>

	<!-- ECOSYSTEM PHASES (Standalone now / WPistic next) -->
	<section class="section section-tight">
		<div class="container">
			<div style="text-align:center;max-width:720px;margin:0 auto;">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Ecosystem', 'chatbotistic' ); ?></span>
				<h2 class="h-1 text-grad" style="margin-top:18px;"><?php esc_html_e( 'Standalone today. Ecosystem-ready for tomorrow.', 'chatbotistic' ); ?></h2>
				<p class="lead" style="margin:18px auto 0;"><?php esc_html_e( 'Chatbotistic runs as its own member portal today, so it launches fast and serves customers immediately — then connects into the centralized WPistic dashboard as the WordPressistic ecosystem grows.', 'chatbotistic' ); ?></p>
			</div>
			<div class="phases">
				<div class="phase phase-1">
					<div class="scan op-scan"></div>
					<div class="phase-head">
						<span class="phase-tag"><?php esc_html_e( 'Phase 1 · Now', 'chatbotistic' ); ?></span>
						<span class="phase-status now"><span class="d"></span><?php esc_html_e( 'Live', 'chatbotistic' ); ?></span>
					</div>
					<h3><?php esc_html_e( 'Standalone Chatbotistic', 'chatbotistic' ); ?></h3>
					<p><?php esc_html_e( 'A self-contained licensed system you can launch and sell today.', 'chatbotistic' ); ?></p>
					<div class="phase-chain">
						<?php
						$cb_chain = array( __( 'Chatbotistic.com', 'chatbotistic' ), __( 'Member Portal', 'chatbotistic' ), __( 'License', 'chatbotistic' ), __( 'WordPress Addon', 'chatbotistic' ) );
						$cb_chain_n = count( $cb_chain ) - 1;
						foreach ( $cb_chain as $cb_ci => $cb_node ) :
							?>
							<div class="chain-node"><span class="ci"></span><?php echo esc_html( $cb_node ); ?></div>
							<?php if ( $cb_ci < $cb_chain_n ) : ?>
								<div class="chain-link op-flow"></div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="phase phase-2">
					<div class="scan op-scan"></div>
					<div class="phase-head">
						<span class="phase-tag"><?php esc_html_e( 'Phase 2 · Next', 'chatbotistic' ); ?></span>
						<span class="phase-status next"><span class="d"></span><?php esc_html_e( 'Planned', 'chatbotistic' ); ?></span>
					</div>
					<h3><?php esc_html_e( 'WPistic ecosystem', 'chatbotistic' ); ?></h3>
					<p><?php esc_html_e( 'One centralized dashboard orchestrating every WordPressistic product.', 'chatbotistic' ); ?></p>
					<div class="phase-hub">
						<div class="hub-core"><span class="hc-dot op-pulse"></span><?php esc_html_e( 'WPistic Dashboard', 'chatbotistic' ); ?></div>
						<div class="hub-rail"></div>
						<div class="hub-spokes">
							<?php
							$cb_spokes = array( array( __( 'Chatbotistic', 'chatbotistic' ), true ), array( __( 'Memberistic', 'chatbotistic' ), false ), array( __( 'Licenseistic', 'chatbotistic' ), false ), array( __( 'Bookingistic', 'chatbotistic' ), false ), array( __( 'Insightistic', 'chatbotistic' ), false ) );
							foreach ( $cb_spokes as $cb_sp ) : ?>
								<div class="hub-spoke<?php echo $cb_sp[1] ? ' self' : ''; ?>"><span class="hs-dot"></span><?php echo esc_html( $cb_sp[0] ); ?></div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- FINAL CTA -->
	<section class="section">
		<div class="container">
			<div class="big-cta">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Ready when you are', 'chatbotistic' ); ?></span>
				<h2 class="text-grad" style="margin-top:18px;"><?php esc_html_e( 'Launch your WhatsApp chatbot system today.', 'chatbotistic' ); ?></h2>
				<p><?php esc_html_e( 'A licensed WhatsApp chatbot system for websites, agencies, and service brands. Create your widget, connect WordPress, and start capturing conversations.', 'chatbotistic' ); ?></p>
				<div class="cta-actions">
					<a class="btn btn-primary btn-lg" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'Start with Chatbotistic', 'chatbotistic' ); ?></a>
					<a class="btn btn-ghost btn-lg" href="<?php echo esc_url( home_url( '/book-demo/' ) ); ?>"><?php esc_html_e( 'Book a demo', 'chatbotistic' ); ?></a>
				</div>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
