<?php
/**
 * Template Name: Upgrade Plan (V4)
 *
 * Plan picker that redirects into checkout with the chosen plan/cycle.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( home_url( '/login/?redirect_to=' . rawurlencode( home_url( '/upgrade/' ) ) ) );
	exit;
}

get_template_part( 'template-parts/auth-head' );

$plans = array(
	array( 'pro',    __( 'Pro', 'chatbotistic' ),    __( 'For growing businesses.', 'chatbotistic' ),     '$9',  '$90',  __( 'Most popular', 'chatbotistic' ) ),
	array( 'agency', __( 'Agency', 'chatbotistic' ), __( 'White-label for agencies & teams.', 'chatbotistic' ), '$99', '$990', '' ),
);
?>

<div class="auth-top" style="height:72px;display:flex;align-items:center;justify-content:space-between;padding:0 32px;border-bottom:1px solid var(--line-soft);position:sticky;top:0;z-index:30;background:rgba(4,6,12,0.7);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);">
	<?php echo cb_logo( array( 'class' => 'cb-logo--auth' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside helper. ?>
	<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( home_url( '/account/?view=billing' ) ); ?>"><?php esc_html_e( 'Back to billing', 'chatbotistic' ); ?></a>
</div>

<main class="page-fade">
	<section class="section section-tight">
		<div class="container" style="text-align:center;">
			<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Upgrade', 'chatbotistic' ); ?></span>
			<h1 class="h-1 text-grad" style="margin:18px auto 0;max-width:640px;"><?php esc_html_e( 'Pick the plan that fits where you’re going.', 'chatbotistic' ); ?></h1>
			<p class="lead" style="margin:18px auto 0;"><?php esc_html_e( 'You can switch any time. Billing prorates automatically.', 'chatbotistic' ); ?></p>
		</div>
	</section>

	<section class="section section-tight">
		<div class="container">
			<div class="pricing-grid" style="grid-template-columns:repeat(2,1fr);max-width:760px;margin:0 auto;">
				<?php foreach ( $plans as $p ) : ?>
					<div class="price-card<?php echo $p[5] ? ' featured' : ''; ?>" style="padding:28px;">
						<?php if ( $p[5] ) : ?><div class="best-badge"><?php echo esc_html( $p[5] ); ?></div><?php endif; ?>
						<div class="plan-name"><?php echo esc_html( $p[1] ); ?></div>
						<div class="plan-desc"><?php echo esc_html( $p[2] ); ?></div>
						<div class="price-amount" style="font-size:42px;margin-top:16px;"><?php echo esc_html( $p[3] ); ?><sub style="font-size:13px;">/mo</sub></div>
						<div style="font-size:12.5px;color:var(--cyan);margin-top:4px;">
							<?php
							/* translators: %s: annual price */
							printf( esc_html__( 'or %s billed yearly — save 17%%', 'chatbotistic' ), esc_html( $p[4] ) );
							?>
						</div>
						<div style="display:flex;gap:10px;margin-top:22px;flex-wrap:wrap;">
							<a class="btn btn-primary" style="flex:1;justify-content:center;" href="<?php echo esc_url( add_query_arg( array( 'plan' => $p[0], 'cycle' => 'monthly' ), home_url( '/checkout/' ) ) ); ?>"><?php esc_html_e( 'Monthly', 'chatbotistic' ); ?></a>
							<a class="btn btn-ghost"   style="flex:1;justify-content:center;" href="<?php echo esc_url( add_query_arg( array( 'plan' => $p[0], 'cycle' => 'annual' ), home_url( '/checkout/' ) ) ); ?>"><?php esc_html_e( 'Annual', 'chatbotistic' ); ?></a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<p style="text-align:center;color:var(--text-dim);font-size:12.5px;margin-top:24px;"><?php esc_html_e( 'Need the Lifetime deal?', 'chatbotistic' ); ?> <a href="mailto:hello@chatbotistic.com" style="color:var(--blue-bright);"><?php esc_html_e( 'Talk to us', 'chatbotistic' ); ?></a></p>
		</div>
	</section>
</main>

<?php get_template_part( 'template-parts/auth-foot' );
