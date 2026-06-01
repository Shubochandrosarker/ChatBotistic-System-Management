<?php
/**
 * Shared product landing renderer (V4).
 *
 * Receives a product entry via $args['product']. Renders the V4 product
 * page: hero, split section (first 5 feats), optional metrics row
 * (agency plan only), full feature grid, big CTA.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/** @var array $args */
$cb_p = $args['product'] ?? null;
if ( ! is_array( $cb_p ) ) {
	return;
}
$cb_feats = $cb_p['feats'] ?? array();
$cb_top5  = array_slice( $cb_feats, 0, 5 );
?>

<main class="page-fade">

	<!-- Page hero -->
	<section class="page-hero">
		<div class="container">
			<span class="section-eyebrow"><span class="dot"></span><?php echo esc_html( $cb_p['eyebrow'] ); ?></span>
			<h1 class="text-grad"><?php echo esc_html( $cb_p['title'] ); ?></h1>
			<p><?php echo esc_html( $cb_p['sub'] ); ?></p>
			<div class="cta-actions" style="margin-top:26px;">
				<a class="btn btn-primary btn-lg" href="<?php echo esc_url( function_exists( 'cb_free_checkout_url' ) ? cb_free_checkout_url() : home_url( '/register/' ) ); ?>"><?php esc_html_e( 'Start free', 'chatbotistic' ); ?></a>
				<a class="btn btn-ghost btn-lg" href="<?php echo esc_url( home_url( '/book-demo/' ) ); ?>"><?php esc_html_e( 'Book a demo', 'chatbotistic' ); ?></a>
			</div>
		</div>
	</section>

	<!-- Metrics row (Agency only) -->
	<?php if ( ! empty( $cb_p['metrics'] ) ) : ?>
		<section class="section section-tight">
			<div class="container">
				<div class="problem-grid" style="margin-top:0;">
					<?php foreach ( $cb_p['metrics'] as $cb_m ) : ?>
						<div class="problem-card" style="display:flex;flex-direction:column;justify-content:center;padding:32px;">
							<span style="font-family:var(--font-mono);font-size:11px;letter-spacing:0.12em;color:var(--text-dim);text-transform:uppercase;"><?php echo esc_html( $cb_m[0] ); ?></span>
							<div class="text-grad-accent" style="font-family:var(--font-display);font-size:42px;font-weight:600;letter-spacing:-0.03em;margin-top:8px;"><?php echo esc_html( $cb_m[1] ); ?></div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- Split section -->
	<section class="section">
		<div class="container">
			<div class="split-2">
				<div>
					<h2 class="h-2 text-grad"><?php echo esc_html( $cb_p['split_title'] ); ?></h2>
					<p class="lead" style="margin-top:18px;"><?php echo esc_html( $cb_p['split_lead'] ); ?></p>
					<div class="solution-list">
						<?php foreach ( $cb_top5 as $cb_f ) : ?>
							<div class="solution-item">
								<div class="si-ico"></div>
								<div><b><?php echo esc_html( $cb_f[0] ); ?></b><span><?php echo esc_html( $cb_f[1] ); ?></span></div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="glass glass-edge" style="padding:32px;border-radius:20px;display:flex;flex-direction:column;gap:14px;min-height:380px;">
					<span class="section-eyebrow"><span class="dot"></span><?php echo esc_html( $cb_p['eyebrow'] ); ?></span>
					<h3 class="h-3" style="margin-top:6px;"><?php echo esc_html( $cb_p['grid_title'] ); ?></h3>
					<ul style="list-style:none;padding:0;margin:8px 0 0;display:flex;flex-direction:column;gap:10px;color:var(--text-soft);font-size:13.5px;">
						<?php foreach ( array_slice( $cb_feats, 5, 4 ) as $cb_f ) : ?>
							<li style="display:flex;gap:10px;"><span style="color:var(--green);">✓</span><span><b style="color:var(--text);"><?php echo esc_html( $cb_f[0] ); ?></b> — <?php echo esc_html( $cb_f[1] ); ?></span></li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</div>
	</section>

	<!-- Full feature grid -->
	<section class="section section-tight">
		<div class="container">
			<h2 class="h-2 text-grad" style="text-align:center;"><?php echo esc_html( $cb_p['grid_title'] ); ?></h2>
			<div class="features-grid" style="margin-top:48px;">
				<?php foreach ( $cb_feats as $cb_f ) : ?>
					<div class="f-card">
						<div class="f-ico"></div>
						<h3><?php echo esc_html( $cb_f[0] ); ?></h3>
						<p><?php echo esc_html( $cb_f[1] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- Big CTA -->
	<section class="section">
		<div class="container">
			<div class="big-cta">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Ready when you are', 'chatbotistic' ); ?></span>
				<h2 class="text-grad" style="margin-top:18px;"><?php echo esc_html( $cb_p['cta_title'] ); ?></h2>
				<div class="cta-actions">
					<a class="btn btn-primary btn-lg" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'Start with Chatbotistic', 'chatbotistic' ); ?></a>
					<a class="btn btn-ghost btn-lg" href="<?php echo esc_url( home_url( '/book-demo/' ) ); ?>"><?php esc_html_e( 'Book a demo', 'chatbotistic' ); ?></a>
				</div>
			</div>
		</div>
	</section>

</main>
