<?php
/**
 * KPI cards.
 *
 * @var array|\WP_Error $stats
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="cbw-section">
	<h2 class="cbw-section-title"><?php esc_html_e( 'Performance Overview', 'chatbotistic-widget' ); ?></h2>
	<p class="cbw-section-sub"><?php esc_html_e( 'Last available data from your Chatbotistic widget.', 'chatbotistic-widget' ); ?></p>

	<?php if ( is_wp_error( $stats ) ) : ?>
		<div class="cbw-notice cbw-notice--warn"><?php echo esc_html( $stats->get_error_message() ); ?></div>
	<?php else :
		$total_leads  = (int) ( $stats['totalLeads']        ?? $stats['leads']        ?? 0 );
		$new_leads    = (int) ( $stats['newLeads']          ?? 0 );
		$total_clicks = (int) ( $stats['totalClicks']       ?? $stats['clicks']       ?? 0 );
		$total_views  = (int) ( $stats['totalViews']        ?? $stats['views']        ?? 0 );
		$convo_rate   = $total_views > 0 ? round( ( $total_leads / $total_views ) * 100, 1 ) : 0;
		$cards = [
			[ 'label' => __( 'Total Leads', 'chatbotistic-widget' ),       'value' => $total_leads,  'icon' => '👥' ],
			[ 'label' => __( 'New Leads',   'chatbotistic-widget' ),       'value' => $new_leads,    'icon' => '✨' ],
			[ 'label' => __( 'Widget Clicks', 'chatbotistic-widget' ),     'value' => $total_clicks, 'icon' => '🖱️' ],
			[ 'label' => __( 'Widget Views', 'chatbotistic-widget' ),      'value' => $total_views,  'icon' => '👁️' ],
			[ 'label' => __( 'Conversion %', 'chatbotistic-widget' ),      'value' => $convo_rate . '%', 'icon' => '⚡' ],
		];
		?>
		<div class="cbw-kpi-grid">
			<?php foreach ( $cards as $c ) : ?>
				<div class="cbw-kpi">
					<div class="cbw-kpi__icon"><?php echo esc_html( $c['icon'] ); ?></div>
					<div class="cbw-kpi__value"><?php echo esc_html( is_int( $c['value'] ) ? number_format_i18n( $c['value'] ) : $c['value'] ); ?></div>
					<div class="cbw-kpi__label"><?php echo esc_html( $c['label'] ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
