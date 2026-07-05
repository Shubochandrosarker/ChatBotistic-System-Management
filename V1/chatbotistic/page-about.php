<?php
/**
 * Template Name: About (V4)
 *
 * V4 port of pages/about-contact-docs.jsx → About.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cb_stats = array(
	array( __( 'Businesses served', 'chatbotistic' ), '2,400+' ),
	array( __( 'Conversations / month', 'chatbotistic' ), '18M+' ),
	array( __( 'Average response time', 'chatbotistic' ), '< 2s' ),
	array( __( 'Countries', 'chatbotistic' ), '42' ),
	array( __( 'Agency partners', 'chatbotistic' ), '380+' ),
	array( __( 'Built on WordPress', 'chatbotistic' ), __( 'Yes', 'chatbotistic' ) ),
);

$cb_pillars = array(
	array( __( 'For WordPress users', 'chatbotistic' ), __( 'Native plugin. PMPro and WooCommerce compatible. Built by WordPressistic — we speak the ecosystem fluently.', 'chatbotistic' ) ),
	array( __( 'For agencies', 'chatbotistic' ), __( 'Workspaces, white label, and reseller pricing. Turn Chatbotistic into your own recurring product.', 'chatbotistic' ) ),
	array( __( 'For growing brands', 'chatbotistic' ), __( 'Premium SaaS UX. Fast setup. Tools that scale from one widget to one hundred.', 'chatbotistic' ) ),
);
?>

<main class="page-fade">

	<section class="page-hero">
		<div class="container" style="text-align:center;">
			<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'About', 'chatbotistic' ); ?></span>
			<h1 class="text-grad" style="max-width:860px;margin:18px auto 0;"><?php esc_html_e( 'Built for WordPress businesses that want more conversations from existing traffic.', 'chatbotistic' ); ?></h1>
			<p style="max-width:760px;margin:18px auto 0;"><?php esc_html_e( 'Chatbotistic is part of the WordPressistic ecosystem — built for WordPress users, agencies, and growing service brands.', 'chatbotistic' ); ?></p>
		</div>
	</section>

	<section class="section">
		<div class="container">
			<div class="split-2">
				<div>
					<h2 class="h-2 text-grad"><?php esc_html_e( 'Our mission', 'chatbotistic' ); ?></h2>
					<p style="color:var(--text-soft);font-size:17px;line-height:1.7;margin-top:18px;">
						<?php esc_html_e( 'Most businesses are great at making their product. They’re not great at responding to a buyer at 11pm on a Sunday.', 'chatbotistic' ); ?>
					</p>
					<p style="color:var(--text-soft);font-size:17px;line-height:1.7;margin-top:14px;">
						<?php esc_html_e( 'We build the tools that close that gap — AI chat, WhatsApp routing, booking, and a unified inbox — so every visitor gets a real response, every lead gets a real follow-up, and every business gets back its time.', 'chatbotistic' ); ?>
					</p>
					<div class="eco-pill" style="margin-top:24px;display:inline-flex;gap:8px;align-items:center;padding:7px 13px;border-radius:999px;border:1px solid var(--line-strong);background:rgba(255,255,255,0.04);font-family:var(--font-mono);font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-soft);">
						<span class="eco-dot" style="width:8px;height:8px;border-radius:50%;background:var(--green);box-shadow:0 0 10px var(--green);"></span>
						<?php esc_html_e( 'Part of the WordPressistic AI Business Automation Ecosystem', 'chatbotistic' ); ?>
					</div>
				</div>
				<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
					<?php foreach ( $cb_stats as $cb_s ) : ?>
						<div class="glass glass-edge" style="padding:22px;border-radius:18px;">
							<div style="font-family:var(--font-mono);font-size:11px;letter-spacing:0.12em;color:var(--text-dim);text-transform:uppercase;"><?php echo esc_html( $cb_s[0] ); ?></div>
							<div class="text-grad-accent" style="font-family:var(--font-display);font-size:30px;font-weight:600;letter-spacing:-0.03em;margin-top:8px;"><?php echo esc_html( $cb_s[1] ); ?></div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</section>

	<section class="section section-tight">
		<div class="container">
			<h2 class="h-2 text-grad" style="text-align:center;"><?php esc_html_e( 'What we build, why we build it.', 'chatbotistic' ); ?></h2>
			<div class="features-grid" style="margin-top:48px;grid-template-columns:repeat(3,1fr);">
				<?php $cb_pillar_icons = array( 'wp', 'users', 'rocket' ); foreach ( $cb_pillars as $cb_i => $cb_p ) : ?>
					<div class="f-card">
						<div class="f-ico"><?php echo cb_get_icon( $cb_pillar_icons[ $cb_i ] ?? 'spark', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						<h3><?php echo esc_html( $cb_p[0] ); ?></h3>
						<p><?php echo esc_html( $cb_p[1] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="section">
		<div class="container">
			<div class="big-cta">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Ready when you are', 'chatbotistic' ); ?></span>
				<h2 class="text-grad" style="margin-top:18px;"><?php esc_html_e( 'Built for businesses that want more leads — without more manual work.', 'chatbotistic' ); ?></h2>
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
