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
	<?php echo cb_logo( array( 'class' => 'cb-logo--auth' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside helper. ?>
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
