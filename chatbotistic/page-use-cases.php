<?php
/**
 * Template Name: Use Cases Overview (V4)
 *
 * Lists every industry use case as a tappable row card.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cb_cases = function_exists( 'cb_v4_use_cases' ) ? cb_v4_use_cases() : array();
?>

<main class="page-fade">

	<section class="page-hero">
		<div class="container" style="text-align:center;">
			<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Use cases', 'chatbotistic' ); ?></span>
			<h1 class="text-grad" style="max-width:760px;margin:18px auto 0;"><?php esc_html_e( 'One platform. Nine industries. Endless conversion paths.', 'chatbotistic' ); ?></h1>
			<p style="max-width:640px;margin:18px auto 0;"><?php esc_html_e( 'See how teams use Chatbotistic to capture, qualify, and convert with less effort.', 'chatbotistic' ); ?></p>
		</div>
	</section>

	<section class="section">
		<div class="container" style="display:flex;flex-direction:column;gap:18px;">
			<h2 class="text-grad" style="margin:0 0 4px;"><?php esc_html_e( 'Use cases by industry', 'chatbotistic' ); ?></h2>
			<?php
			$cb_i = 0;
			foreach ( $cb_cases as $cb_slug => $cb_c ) :
				$cb_i++; ?>
				<a class="glass glass-edge use-row" href="<?php echo esc_url( home_url( '/' . $cb_slug . '/' ) ); ?>"
				   style="padding:28px;display:grid;grid-template-columns:60px 1.4fr 1.4fr 220px;gap:28px;align-items:center;text-decoration:none;">
					<div style="width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,rgba(79,139,255,0.15),rgba(160,112,255,0.15));border:1px solid rgba(255,255,255,0.08);display:grid;place-items:center;color:#cfdcff;"><?php echo cb_get_icon( isset( $cb_c['icon'] ) ? $cb_c['icon'] : 'spark', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<div>
						<div style="font-family:var(--font-mono);font-size:11px;letter-spacing:0.12em;color:var(--text-dim);text-transform:uppercase;">
							<?php echo esc_html( str_pad( (string) $cb_i, 2, '0', STR_PAD_LEFT ) ); ?> · <?php esc_html_e( 'Use case', 'chatbotistic' ); ?>
						</div>
						<h3 class="h-3" style="margin-top:6px;"><?php echo esc_html( $cb_c['title'] ); ?></h3>
						<p style="color:var(--text-soft);font-size:13.5px;margin:8px 0 0;"><b style="color:#ff8aa0;font-weight:600;"><?php esc_html_e( 'Problem:', 'chatbotistic' ); ?></b> <?php echo esc_html( $cb_c['problem'] ); ?></p>
						<p style="color:var(--text-soft);font-size:13.5px;margin:4px 0 0;"><b style="color:var(--green);font-weight:600;"><?php esc_html_e( 'Solution:', 'chatbotistic' ); ?></b> <?php echo esc_html( $cb_c['solution'] ); ?></p>
					</div>
					<div class="use-row-meta" style="display:flex;flex-wrap:wrap;gap:6px;">
						<?php foreach ( $cb_c['feats'] as $cb_f ) : ?>
							<span style="padding:5px 10px;border-radius:999px;background:rgba(79,139,255,0.1);border:1px solid rgba(79,139,255,0.25);font-size:11.5px;color:#a8c1ff;font-family:var(--font-mono);letter-spacing:0.04em;"><?php echo esc_html( $cb_f ); ?></span>
						<?php endforeach; ?>
					</div>
					<span class="btn btn-ghost btn-sm use-row-cta" style="justify-self:end;"><?php esc_html_e( 'View use case →', 'chatbotistic' ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="section">
		<div class="container">
			<div class="big-cta">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Ready when you are', 'chatbotistic' ); ?></span>
				<h2 class="text-grad" style="margin-top:18px;"><?php esc_html_e( 'Find your industry’s fastest path to conversion.', 'chatbotistic' ); ?></h2>
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
