<?php
/**
 * Template Name: Support (V4)
 *
 * Support hub: search docs, six common issue cards, contact support CTA.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cb_topics = array(
	array( __( 'Search docs', 'chatbotistic' ),         __( 'Find a guide or tutorial in the documentation.', 'chatbotistic' ),         home_url( '/docs/' ),         __( 'Open docs', 'chatbotistic' ) ),
	array( __( 'Billing help', 'chatbotistic' ),        __( 'Invoices, plan changes, refunds, and payment methods.', 'chatbotistic' ), home_url( '/account/?view=billing' ), __( 'Open billing', 'chatbotistic' ) ),
	array( __( 'License issue', 'chatbotistic' ),       __( 'Activation, domain limits, expiry, or heartbeat errors.', 'chatbotistic' ), home_url( '/account/?view=license' ), __( 'Manage licenses', 'chatbotistic' ) ),
	array( __( 'WordPress addon issue', 'chatbotistic' ), __( 'Plugin install, license activation, or widget not showing.', 'chatbotistic' ), home_url( '/account/?view=install' ), __( 'Open install guide', 'chatbotistic' ) ),
	array( __( 'Demo request', 'chatbotistic' ),        __( 'Tell us about your business to set up a 14-day demo.', 'chatbotistic' ), home_url( '/book-demo/' ), __( 'Book a demo', 'chatbotistic' ) ),
	array( __( 'Contact support', 'chatbotistic' ),     __( 'Open a ticket with our team — we reply in under 2 hours.', 'chatbotistic' ), home_url( '/contact/' ), __( 'Contact us', 'chatbotistic' ) ),
);
?>

<main class="page-fade">

	<section class="page-hero">
		<div class="container" style="text-align:center;">
			<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Support', 'chatbotistic' ); ?></span>
			<h1 class="text-grad" style="max-width:760px;margin:18px auto 0;"><?php esc_html_e( 'Get help fast.', 'chatbotistic' ); ?></h1>
			<p style="max-width:640px;margin:18px auto 0;"><?php esc_html_e( 'Search the docs, check your account, or open a ticket — we reply in under 2 hours during business days.', 'chatbotistic' ); ?></p>
			<form class="docs-search" style="margin:32px auto 0;max-width:520px;" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input type="search" name="s" placeholder="<?php esc_attr_e( 'Search docs and guides…', 'chatbotistic' ); ?>"><kbd>↵</kbd>
			</form>
		</div>
	</section>

	<section class="section">
		<div class="container">
			<div class="features-grid" style="grid-template-columns:repeat(3,1fr);">
				<?php $cb_topic_icons = array( 'page', 'card', 'key', 'plug', 'play', 'chat' ); foreach ( $cb_topics as $cb_i => $cb_t ) : ?>
					<a class="f-card" href="<?php echo esc_url( $cb_t[2] ); ?>" style="text-decoration:none;">
						<div class="f-ico"><?php echo cb_get_icon( $cb_topic_icons[ $cb_i ] ?? 'spark', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						<h3><?php echo esc_html( $cb_t[0] ); ?></h3>
						<p><?php echo esc_html( $cb_t[1] ); ?></p>
						<span class="btn btn-ghost btn-sm" style="margin-top:14px;align-self:flex-start;"><?php echo esc_html( $cb_t[3] ); ?> →</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="section">
		<div class="container">
			<div class="big-cta">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Still stuck?', 'chatbotistic' ); ?></span>
				<h2 class="text-grad" style="margin-top:18px;"><?php esc_html_e( 'Open a ticket — we’re online now.', 'chatbotistic' ); ?></h2>
				<p><?php esc_html_e( 'Tell us what’s happening and a human will reply with the next step.', 'chatbotistic' ); ?></p>
				<div class="cta-actions">
					<a class="btn btn-primary btn-lg" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact support', 'chatbotistic' ); ?></a>
					<a class="btn btn-ghost btn-lg" href="<?php echo esc_url( home_url( '/docs/' ) ); ?>"><?php esc_html_e( 'Browse docs', 'chatbotistic' ); ?></a>
				</div>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
