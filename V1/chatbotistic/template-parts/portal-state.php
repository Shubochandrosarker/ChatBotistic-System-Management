<?php
/**
 * Shared portal state component (empty / loading / error / locked / success).
 *
 * Used inside every portal section (overview, widgets, leads, etc.) to
 * render the four no-data states with consistent V4 styling.
 *
 *   $args['type']     'empty' | 'loading' | 'error' | 'locked' | 'success'
 *   $args['title']    string
 *   $args['lead']     string
 *   $args['cta']      optional array: [label, url, ('primary'|'ghost')]
 *   $args['icon']     optional override glyph
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/** @var array $args */
$type  = $args['type']  ?? 'empty';
$title = $args['title'] ?? '';
$lead  = $args['lead']  ?? '';
$cta   = $args['cta']   ?? null;

$glyphs = array(
	'empty'   => '◌',
	'loading' => '…',
	'error'   => '✕',
	'locked'  => '🔒',
	'success' => '✓',
);
$tone = array(
	'empty'   => 'muted',
	'loading' => 'wait',
	'error'   => 'err',
	'locked'  => 'wait',
	'success' => 'ok',
);
$icon = $args['icon'] ?? ( $glyphs[ $type ] ?? '◌' );
?>

<div class="cb-portal-state cb-portal-state--<?php echo esc_attr( $type ); ?>">
	<div class="cb-portal-state__ico result-ico <?php echo esc_attr( $tone[ $type ] ?? 'wait' ); ?>"><?php echo esc_html( $icon ); ?></div>
	<h3 class="cb-portal-state__title"><?php echo esc_html( $title ); ?></h3>
	<?php if ( $lead ) : ?>
		<p class="cb-portal-state__lead"><?php echo esc_html( $lead ); ?></p>
	<?php endif; ?>
	<?php if ( $cta && is_array( $cta ) && ! empty( $cta[0] ) && ! empty( $cta[1] ) ) :
		$cta_cls = isset( $cta[2] ) && 'ghost' === $cta[2] ? 'btn-ghost' : 'btn-primary'; ?>
		<a class="btn <?php echo esc_attr( $cta_cls ); ?>" href="<?php echo esc_url( $cta[1] ); ?>"><?php echo esc_html( $cta[0] ); ?></a>
	<?php endif; ?>
</div>
