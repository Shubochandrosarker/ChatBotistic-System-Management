<?php
/**
 * Template Name: Checkout (V4)
 *
 * Branded shell around Memberistic's own checkout, which creates the Stripe
 * Checkout Session and handles the real payment. Plan + cycle arrive as
 * ?memberistic_plan=pro|agency&cycle=monthly|annual (the theme's pricing cards
 * build these links via cb_memberistic_checkout_url()). The legacy ?plan=
 * param is still honoured as a fallback.
 *
 * Card data is never collected on-site — Memberistic redirects to Stripe's
 * hosted Checkout. This template only provides the branded frame (logo, order
 * summary, trust signals, guarantee) and embeds the [memberistic_checkout]
 * shortcode that drives the transaction.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/auth-head' );

// Accept both the Memberistic param and the theme's legacy ?plan= param.
$cb_plan_slug = '';
if ( ! empty( $_GET['memberistic_plan'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$cb_plan_slug = sanitize_title( wp_unslash( $_GET['memberistic_plan'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
} elseif ( ! empty( $_GET['plan'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$cb_plan_slug = sanitize_title( wp_unslash( $_GET['plan'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}
$cb_cycle = ( isset( $_GET['cycle'] ) && 'annual' === $_GET['cycle'] ) ? 'annual' : 'monthly'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

// Branding-only plan copy for the summary card. Memberistic's shortcode remains
// the source of truth for the actual price charged, so no prices are shown here.
$cb_plan_meta = array(
	'pro'    => array( 'name' => __( 'Chatbotistic Pro', 'chatbotistic' ),    'feats' => array( __( '5 AI ChatBot Widgets', 'chatbotistic' ), __( '15 WhatsApp Agents', 'chatbotistic' ), __( '10 Website Domains', 'chatbotistic' ), __( 'Chat Forms, CRM, Webhooks', 'chatbotistic' ), __( 'WordPress widget plugin', 'chatbotistic' ) ) ),
	'agency' => array( 'name' => __( 'Chatbotistic Agency', 'chatbotistic' ), 'feats' => array( __( '30 AI ChatBot Widgets', 'chatbotistic' ), __( '100 WhatsApp Agents', 'chatbotistic' ), __( '50 Website Domains', 'chatbotistic' ), __( 'White-label dashboard & widgets', 'chatbotistic' ), __( 'Team agents on your account', 'chatbotistic' ) ) ),
);
$cb_meta = $cb_plan_meta[ $cb_plan_slug ] ?? null;
?>

<div class="cbk-top">
	<?php echo cb_logo(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside helper. ?>
	<span class="cbk-secure">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
		<?php esc_html_e( 'Secure checkout', 'chatbotistic' ); ?>
	</span>
</div>

<main class="cbk" id="cb-checkout-page">
	<div class="cbk-wrap<?php echo $cb_meta ? '' : ' cbk-wrap--single'; ?>">

		<div class="cbk-main">
			<a class="cbk-back" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
				<?php esc_html_e( 'Back to pricing', 'chatbotistic' ); ?>
			</a>
			<h1><?php esc_html_e( 'Complete your subscription', 'chatbotistic' ); ?></h1>
			<p class="cbk-sub"><?php esc_html_e( 'Secure payment is handled by Stripe. You can cancel anytime from your account.', 'chatbotistic' ); ?></p>

			<div class="cbk-card cb-memberistic-checkout">
				<?php
				if ( function_exists( 'cb_memberistic_active' ) && cb_memberistic_active() && shortcode_exists( 'memberistic_checkout' ) ) {
					echo do_shortcode(
						sprintf(
							'[memberistic_checkout plan="%s" cycle="%s"]',
							esc_attr( $cb_plan_slug ),
							esc_attr( $cb_cycle )
						)
					);
				} else {
					// Memberistic inactive — keep the page graceful instead of fataling.
					?>
					<p style="color:var(--text-soft);margin:0 0 16px;">
						<?php esc_html_e( 'Checkout is temporarily unavailable. Please choose a plan to continue.', 'chatbotistic' ); ?>
					</p>
					<?php cb_button( __( 'View plans', 'chatbotistic' ), cb_plans_url(), 'primary', array( 'icon' => 'arrow-r' ) ); ?>
					<?php
				}
				?>
			</div>

			<div class="cbk-trust">
				<span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg><?php esc_html_e( 'SSL encrypted', 'chatbotistic' ); ?></span>
				<span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><?php esc_html_e( 'Powered by Stripe', 'chatbotistic' ); ?></span>
				<span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 18 0 9 9 0 0 0-18 0z"/><path d="M12 7v5l3 2"/></svg><?php esc_html_e( 'Cancel anytime', 'chatbotistic' ); ?></span>
			</div>
		</div>

		<?php if ( $cb_meta ) : ?>
		<aside class="cbk-summary">
			<div class="cbk-plan">
				<span class="ico">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6.5C4 5 5 4 6.5 4h11C19 4 20 5 20 6.5v8c0 1.5-1 2.5-2.5 2.5H13l-4 3v-3H6.5C5 17 4 16 4 14.5z"/></svg>
				</span>
				<div>
					<div class="tag"><?php esc_html_e( 'Your plan', 'chatbotistic' ); ?></div>
					<div class="nm"><?php echo esc_html( $cb_meta['name'] ); ?></div>
				</div>
			</div>

			<ul class="cbk-feats" style="margin-top:20px;border-top:0;padding-top:0;">
				<?php foreach ( $cb_meta['feats'] as $cb_f ) : ?>
					<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><?php echo esc_html( $cb_f ); ?></li>
				<?php endforeach; ?>
			</ul>

			<div class="cbk-guarantee">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
				<span><b><?php esc_html_e( '14-day money-back guarantee', 'chatbotistic' ); ?></b><?php esc_html_e( 'Not a fit? Get a full refund, no questions asked.', 'chatbotistic' ); ?></span>
			</div>
		</aside>
		<?php endif; ?>

	</div>
</main>

<?php
get_template_part( 'template-parts/auth-foot' );
