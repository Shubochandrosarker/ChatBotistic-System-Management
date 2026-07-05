<?php
/**
 * Referral sources table.
 *
 * @var array|\WP_Error $referrals
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="cbw-section">
	<h2 class="cbw-section-title"><?php esc_html_e( 'Top Referral Sources', 'chatbotistic-widget' ); ?></h2>
	<p class="cbw-section-sub"><?php esc_html_e( 'Where your widget engagement is coming from — last 12 months.', 'chatbotistic-widget' ); ?></p>

	<?php if ( is_wp_error( $referrals ) ) : ?>
		<div class="cbw-notice cbw-notice--warn"><?php echo esc_html( $referrals->get_error_message() ); ?></div>
	<?php else :
		$rows = [];
		if ( is_array( $referrals ) ) {
			// API may return either ['referers' => [...]] or a flat list keyed by domain.
			if ( isset( $referrals['referers'] ) && is_array( $referrals['referers'] ) ) {
				$rows = $referrals['referers'];
			} else {
				$rows = $referrals;
			}
		}
		if ( empty( $rows ) ) : ?>
			<div class="cbw-empty">
				<div class="cbw-empty__icon">🌐</div>
				<p><?php esc_html_e( 'No referral data yet — once visitors interact with your widget, sources will appear here.', 'chatbotistic-widget' ); ?></p>
			</div>
		<?php else : ?>
			<div class="cbw-table-wrap">
				<table class="cbw-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Source', 'chatbotistic-widget' ); ?></th>
							<th class="cbw-num"><?php esc_html_e( 'Interactions', 'chatbotistic-widget' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( array_slice( $rows, 0, 25 ) as $row ) :
							$source = is_array( $row ) ? ( $row['referer'] ?? $row['source'] ?? $row['domain'] ?? '' ) : (string) $row;
							$count  = is_array( $row ) ? (int) ( $row['count'] ?? $row['value'] ?? 0 ) : 0;
							?>
							<tr>
								<td><span class="cbw-mono"><?php echo esc_html( $source ?: __( 'Direct / Unknown', 'chatbotistic-widget' ) ); ?></span></td>
								<td class="cbw-num"><?php echo esc_html( number_format_i18n( $count ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif;
	endif; ?>
</div>
