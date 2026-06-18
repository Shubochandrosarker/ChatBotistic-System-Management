<?php
/**
 * Shared auth-page layout (V4).
 *
 * Renders a minimal top bar + split panel shell. Two slots:
 *   $args['aside']  HTML for the left brand-story aside
 *   $args['form']   HTML for the right form card
 *   $args['top_right'] HTML for the top-bar right-side links
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;
/** @var array $args */
$cb_aside     = $args['aside']     ?? '';
$cb_form      = $args['form']      ?? '';
$cb_top_right = $args['top_right'] ?? '';
?>

<div class="auth-top" style="height:72px;display:flex;align-items:center;justify-content:space-between;padding:0 32px;border-bottom:1px solid var(--line-soft);position:sticky;top:0;z-index:30;background:rgba(4,6,12,0.7);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);">
	<?php echo cb_logo( array( 'class' => 'cb-logo--auth' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside helper. ?>
	<div style="display:flex;align-items:center;gap:14px;"><?php echo wp_kses_post( $cb_top_right ); ?></div>
</div>

<main class="page-fade">
	<div class="auth-wrap">
		<aside class="auth-aside">
			<div class="scan op-scan"></div>
			<?php echo $cb_aside; // already escaped where needed ?>
			<div class="eco-pill" style="display:inline-flex;align-items:center;gap:8px;"><span class="eco-dot"></span><?php esc_html_e( 'A WordPressistic product', 'chatbotistic' ); ?></div>
		</aside>
		<section class="auth-main">
			<div class="auth-card">
				<?php echo $cb_form; // already escaped where needed ?>
			</div>
		</section>
	</div>
</main>
