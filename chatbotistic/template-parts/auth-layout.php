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
	<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" style="display:inline-flex;align-items:center;gap:10px;text-decoration:none;color:var(--text);font-family:var(--font-display);font-weight:600;">
		<span class="logo-mark">
			<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6.5C4 5 5 4 6.5 4h11C19 4 20 5 20 6.5v8c0 1.5-1 2.5-2.5 2.5H13l-4 3v-3H6.5C5 17 4 16 4 14.5z"/></svg>
		</span>
		Chatbotistic
	</a>
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
