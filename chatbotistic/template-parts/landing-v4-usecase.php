<?php
/**
 * Shared use-case landing renderer (V4).
 *
 * Receives a use-case data array via $args['case'] from page-usecase.php.
 * Renders the V4 use-case detail page: hero with breadcrumb, pain cards,
 * help cards, example flow, metrics + recommended modules + plan, FAQs,
 * next-case promo.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/** @var array $args */
$cb_case  = $args['case']  ?? null;
$cb_slug  = $args['slug']  ?? '';
$cb_next  = $args['next']  ?? null;
if ( ! is_array( $cb_case ) ) {
	return;
}

// FAQPage JSON-LD for this use-case's FAQs.
if ( ! empty( $cb_case['faqs'] ) && is_array( $cb_case['faqs'] ) && function_exists( 'cb_add_faq_schema' ) ) {
	cb_add_faq_schema( array_map(
		static fn ( $q ) => array( 'question' => $q[0], 'answer' => $q[1] ),
		$cb_case['faqs']
	) );
}

// SpeakableSpecification — hero headline + sub-headline are clean
// voice-answer snippets for "tell me about Chatbotistic for <industry>".
if ( function_exists( 'cb_add_speakable' ) ) {
	cb_add_speakable( array( '.page-hero h1', '.page-hero p' ) );
}
?>

<main class="page-fade">

	<!-- Hero -->
	<section class="page-hero">
		<div class="container">
			<a href="<?php echo esc_url( home_url( '/use-cases/' ) ); ?>" style="display:inline-flex;align-items:center;gap:7px;font-size:13px;color:var(--text-dim);text-decoration:none;margin-bottom:18px;font-family:var(--font-mono);letter-spacing:0.04em;">
				← <?php esc_html_e( 'All use cases', 'chatbotistic' ); ?>
			</a>
			<span class="section-eyebrow"><span class="dot"></span><?php echo esc_html( $cb_case['market'] ); ?></span>
			<h1 class="text-grad"><?php echo esc_html( $cb_case['hero'] ); ?></h1>
			<p><?php echo esc_html( $cb_case['sub'] ); ?></p>
			<div class="cta-actions" style="margin-top:26px;">
				<a class="btn btn-primary btn-lg" href="<?php echo esc_url( function_exists( 'cb_free_checkout_url' ) ? cb_free_checkout_url() : home_url( '/register/' ) ); ?>"><?php esc_html_e( 'Start free', 'chatbotistic' ); ?></a>
				<a class="btn btn-ghost btn-lg" href="<?php echo esc_url( home_url( '/book-demo/' ) ); ?>"><?php esc_html_e( 'Book a demo', 'chatbotistic' ); ?></a>
			</div>
		</div>
	</section>

	<!-- Pain points -->
	<section class="section section-tight">
		<div class="container">
			<div style="max-width:680px;">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'The problem', 'chatbotistic' ); ?></span>
				<h2 class="h-2 text-grad" style="margin-top:16px;">
					<?php
					/* translators: %s: lowercase industry name */
					printf( esc_html__( 'What’s slowing %s down today.', 'chatbotistic' ), esc_html( strtolower( $cb_case['title'] ) ) );
					?>
				</h2>
			</div>
			<div class="features-grid" style="margin-top:40px;">
				<?php foreach ( $cb_case['pains'] as $cb_p ) : ?>
					<div class="f-card" style="border-color:rgba(255,111,134,0.18);">
						<div class="f-ico" style="background:rgba(255,111,134,0.1);border-color:rgba(255,111,134,0.22);color:#ff8aa0;">✕</div>
						<h3><?php echo esc_html( $cb_p[0] ); ?></h3>
						<p><?php echo esc_html( $cb_p[1] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- How Chatbotistic helps -->
	<section class="section section-tight">
		<div class="container">
			<div style="max-width:680px;">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'How Chatbotistic helps', 'chatbotistic' ); ?></span>
				<h2 class="h-2 text-grad" style="margin-top:16px;"><?php esc_html_e( 'A system built around how you actually work.', 'chatbotistic' ); ?></h2>
			</div>
			<div class="features-grid" style="margin-top:40px;">
				<?php foreach ( $cb_case['helps'] as $cb_i => $cb_h ) : ?>
					<div class="f-card">
						<div class="f-ico"><?php echo cb_get_icon( cb_feature_icon( $cb_i ), 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						<h3><?php echo esc_html( $cb_h[0] ); ?></h3>
						<p><?php echo esc_html( $cb_h[1] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- Example workflow -->
	<section class="section section-tight">
		<div class="container">
			<div style="text-align:center;max-width:680px;margin:0 auto;">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Example workflow', 'chatbotistic' ); ?></span>
				<h2 class="h-2 text-grad" style="margin-top:16px;"><?php esc_html_e( 'From first touch to captured lead.', 'chatbotistic' ); ?></h2>
			</div>
			<div class="uc-flow">
				<?php $cb_flow_n = count( $cb_case['flow'] ) - 1; foreach ( $cb_case['flow'] as $cb_i => $cb_step ) : ?>
					<div class="uc-flow-step">
						<div class="n"><?php echo esc_html( str_pad( (string) ( $cb_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></div>
						<span><?php echo esc_html( $cb_step ); ?></span>
					</div>
					<?php if ( $cb_i < $cb_flow_n ) : ?>
						<div class="uc-flow-arrow">→</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- Metrics + recommended modules + plan -->
	<section class="section section-tight">
		<div class="container">
			<div class="split-2" style="align-items:stretch;">
				<div class="glass glass-edge" style="padding:32px;border-radius:20px;display:flex;flex-direction:column;gap:22px;justify-content:center;">
					<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Typical results', 'chatbotistic' ); ?></span>
					<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:18px;">
						<?php foreach ( $cb_case['metrics'] as $cb_m ) : ?>
							<div>
								<div class="text-grad-accent" style="font-family:var(--font-display);font-size:34px;font-weight:600;letter-spacing:-0.03em;"><?php echo esc_html( $cb_m[0] ); ?></div>
								<div style="font-size:12px;color:var(--text-soft);margin-top:6px;line-height:1.4;"><?php echo esc_html( $cb_m[1] ); ?></div>
							</div>
						<?php endforeach; ?>
					</div>
					<p style="font-size:11.5px;color:var(--text-dim);margin:0;"><?php esc_html_e( 'Illustrative figures based on typical Chatbotistic deployments. Your results will vary.', 'chatbotistic' ); ?></p>
				</div>
				<div class="glass glass-edge" style="padding:32px;border-radius:20px;">
					<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Recommended modules', 'chatbotistic' ); ?></span>
					<div style="display:flex;flex-wrap:wrap;gap:8px;margin:18px 0 24px;">
						<?php foreach ( $cb_case['modules'] as $cb_mod ) : ?>
							<span style="padding:7px 13px;border-radius:999px;background:rgba(79,139,255,0.1);border:1px solid rgba(79,139,255,0.25);font-size:12.5px;color:#a8c1ff;"><?php echo esc_html( $cb_mod ); ?></span>
						<?php endforeach; ?>
					</div>
					<div style="padding:18px;border-radius:14px;background:linear-gradient(135deg,rgba(160,112,255,0.12),rgba(79,139,255,0.05));border:1px solid rgba(160,112,255,0.22);">
						<div style="font-family:var(--font-mono);font-size:11px;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-dim);"><?php esc_html_e( 'Recommended plan', 'chatbotistic' ); ?></div>
						<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:8px;flex-wrap:wrap;">
							<div>
								<b style="font-family:var(--font-display);font-size:20px;"><?php echo esc_html( $cb_case['plan'] ); ?></b>
								<div style="font-size:12.5px;color:var(--text-soft);margin-top:2px;"><?php echo esc_html( $cb_case['plan_note'] ); ?></div>
							</div>
							<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'See pricing', 'chatbotistic' ); ?></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- FAQ -->
	<section class="section section-tight">
		<div class="container">
			<div style="text-align:center;max-width:680px;margin:0 auto;">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'FAQ', 'chatbotistic' ); ?></span>
				<h2 class="h-1 text-grad" style="margin-top:18px;">
					<?php
					/* translators: %s: industry title */
					printf( esc_html__( '%s questions, answered.', 'chatbotistic' ), esc_html( $cb_case['title'] ) );
					?>
				</h2>
			</div>
			<div class="faq-list" style="max-width:760px;margin:40px auto 0;">
				<?php foreach ( $cb_case['faqs'] as $cb_q ) : ?>
					<details class="faq-item">
						<summary><?php echo esc_html( $cb_q[0] ); ?></summary>
						<p><?php echo esc_html( $cb_q[1] ); ?></p>
					</details>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- Next use case -->
	<?php if ( $cb_next ) : ?>
		<section class="section section-tight">
			<div class="container">
				<a href="<?php echo esc_url( home_url( '/' . $cb_next['slug'] . '/' ) ); ?>" class="glass glass-edge" style="display:flex;align-items:center;gap:18px;padding:24px;border-radius:18px;text-decoration:none;">
					<div style="width:48px;height:48px;border-radius:13px;background:linear-gradient(135deg,rgba(79,139,255,0.15),rgba(160,112,255,0.15));border:1px solid rgba(255,255,255,0.08);flex-shrink:0;"></div>
					<div style="flex:1;">
						<div style="font-family:var(--font-mono);font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);"><?php esc_html_e( 'Next use case', 'chatbotistic' ); ?></div>
						<b style="font-size:16px;"><?php echo esc_html( $cb_next['title'] ); ?></b>
					</div>
					<span>→</span>
				</a>
			</div>
		</section>
	<?php endif; ?>

	<!-- Big CTA -->
	<section class="section">
		<div class="container">
			<div class="big-cta">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Ready when you are', 'chatbotistic' ); ?></span>
				<h2 class="text-grad" style="margin-top:18px;"><?php esc_html_e( 'Launch your conversation system today.', 'chatbotistic' ); ?></h2>
				<div class="cta-actions">
					<a class="btn btn-primary btn-lg" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'Start with Chatbotistic', 'chatbotistic' ); ?></a>
					<a class="btn btn-ghost btn-lg" href="<?php echo esc_url( home_url( '/book-demo/' ) ); ?>"><?php esc_html_e( 'Book a demo', 'chatbotistic' ); ?></a>
				</div>
			</div>
		</div>
	</section>

</main>
