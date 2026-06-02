<?php
/**
 * Shared auth/result card layout (V4) — used for email verify, payment
 * success, payment failed, cancel confirmation, forgot-password sent, etc.
 *
 *   $args['top_right'] HTML
 *   $args['icon']      'ok' | 'err' | 'wait'
 *   $args['title']     string
 *   $args['lead']      string
 *   $args['receipt']   optional array of [key, value, value_class]
 *   $args['actions']   HTML (buttons)
 *   $args['foot']      optional small print HTML
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;
/** @var array $args */
$icon     = isset( $args['icon'] )     ? (string) $args['icon'] : 'ok';
$title    = isset( $args['title'] )    ? (string) $args['title'] : '';
$lead     = isset( $args['lead'] )     ? (string) $args['lead'] : '';
$receipt  = isset( $args['receipt'] )  ? (array)  $args['receipt'] : array();
$actions  = isset( $args['actions'] )  ? (string) $args['actions'] : '';
$foot     = isset( $args['foot'] )     ? (string) $args['foot'] : '';
$top_right = isset( $args['top_right'] ) ? (string) $args['top_right'] : '';

$glyph = array( 'ok' => '✓', 'err' => '✕', 'wait' => '…' );
?>

<div class="auth-top" style="height:72px;display:flex;align-items:center;justify-content:space-between;padding:0 32px;border-bottom:1px solid var(--line-soft);position:sticky;top:0;z-index:30;background:rgba(4,6,12,0.7);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);">
	<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" style="display:inline-flex;align-items:center;gap:10px;text-decoration:none;color:var(--text);font-family:var(--font-display);font-weight:600;">
		<span class="logo-mark"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6.5C4 5 5 4 6.5 4h11C19 4 20 5 20 6.5v8c0 1.5-1 2.5-2.5 2.5H13l-4 3v-3H6.5C5 17 4 16 4 14.5z"/></svg></span>
		Chatbotistic
	</a>
	<div style="display:flex;align-items:center;gap:14px;"><?php echo wp_kses_post( $top_right ); ?></div>
</div>

<main class="page-fade">
	<div class="result-wrap">
		<div class="glass glass-edge result-card">
			<div class="result-ico <?php echo esc_attr( $icon ); ?>" style="font-size:30px;font-weight:700;"><?php echo esc_html( $glyph[ $icon ] ?? '✓' ); ?></div>
			<h1><?php echo esc_html( $title ); ?></h1>
			<p><?php echo esc_html( $lead ); ?></p>
			<?php if ( $receipt ) : ?>
				<div class="receipt">
					<?php foreach ( $receipt as $row ) :
						$k = $row[0] ?? '';
						$v = $row[1] ?? '';
						$cls = isset( $row[2] ) ? ' style="color:' . esc_attr( $row[2] ) . ';"' : ''; ?>
						<div class="receipt-row"><span class="k"><?php echo esc_html( $k ); ?></span><span class="v"<?php echo $cls; ?>><?php echo esc_html( $v ); ?></span></div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php if ( $actions ) : ?>
				<div class="result-actions" style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-top:8px;"><?php echo $actions; // built locally, escaped at source ?></div>
			<?php endif; ?>
			<?php if ( $foot ) : ?>
				<p style="font-size:12.5px;color:var(--text-dim);margin-top:20px;"><?php echo $foot; ?></p>
			<?php endif; ?>
		</div>
	</div>
</main>
