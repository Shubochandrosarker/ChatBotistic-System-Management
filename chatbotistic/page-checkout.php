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
 * hosted Checkout. This template only provides the branded frame and embeds
 * the [memberistic_checkout] shortcode that drives the transaction.
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
?>

<div class="auth-top" style="height:72px;display:flex;align-items:center;justify-content:space-between;padding:0 32px;border-bottom:1px solid var(--line-soft);position:sticky;top:0;z-index:30;background:rgba(4,6,12,0.7);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);">
	<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" style="display:inline-flex;align-items:center;gap:10px;text-decoration:none;color:var(--text);font-family:var(--font-display);font-weight:600;">
		<span class="logo-mark"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6.5C4 5 5 4 6.5 4h11C19 4 20 5 20 6.5v8c0 1.5-1 2.5-2.5 2.5H13l-4 3v-3H6.5C5 17 4 16 4 14.5z"/></svg></span>
		Chatbotistic
	</a>
	<span class="top-status" style="display:inline-flex;align-items:center;gap:7px;font-family:var(--font-mono);font-size:11px;color:var(--text-soft);"><?php esc_html_e( '🔒 Secure checkout', 'chatbotistic' ); ?></span>
</div>

<main class="page-fade" id="cb-checkout-page">
	<div class="form-page" style="padding:48px 32px 80px;max-width:920px;margin:0 auto;">
		<h1 style="font-size:28px;letter-spacing:-0.02em;"><?php esc_html_e( 'Complete your subscription', 'chatbotistic' ); ?></h1>
		<p style="color:var(--text-soft);margin-top:8px;">
			<?php esc_html_e( 'Secure payment is handled by Stripe. You can cancel anytime from your account.', 'chatbotistic' ); ?>
		</p>

		<div class="cb-memberistic-checkout" style="margin-top:28px;">
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
				<div class="glass glass-edge" style="padding:28px;border-radius:16px;">
					<p style="color:var(--text-soft);margin:0 0 16px;">
						<?php esc_html_e( 'Checkout is temporarily unavailable. Please choose a plan to continue.', 'chatbotistic' ); ?>
					</p>
					<?php cb_button( __( 'View plans', 'chatbotistic' ), cb_plans_url(), 'primary', array( 'icon' => 'arrow-r' ) ); ?>
				</div>
				<?php
			}
			?>
		</div>
	</div>
</main>

<?php
get_template_part( 'template-parts/auth-foot' );
