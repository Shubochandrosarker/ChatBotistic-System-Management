<?php
/**
 * Recent leads table.
 *
 * @var array|\WP_Error $leads
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="cbw-section">
	<h2 class="cbw-section-title"><?php esc_html_e( 'Recent Leads', 'chatbotistic-widget' ); ?></h2>
	<p class="cbw-section-sub"><?php esc_html_e( 'The latest 25 conversations captured through your widget.', 'chatbotistic-widget' ); ?></p>

	<?php
	if ( is_wp_error( $leads ) ) {
		echo '<div class="cbw-notice cbw-notice--warn">' . esc_html( $leads->get_error_message() ) . '</div>';
	} else {
		$rows = is_array( $leads['hydra:member'] ?? null ) ? $leads['hydra:member'] : ( is_array( $leads ) ? $leads : [] );
		if ( empty( $rows ) ) : ?>
			<div class="cbw-empty">
				<div class="cbw-empty__icon">📨</div>
				<p><?php esc_html_e( 'No leads yet. They\'ll show up here as soon as your visitors start chatting.', 'chatbotistic-widget' ); ?></p>
			</div>
		<?php else : ?>
			<div class="cbw-table-wrap">
				<table class="cbw-table cbw-table--zebra">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Phone', 'chatbotistic-widget' ); ?></th>
							<th><?php esc_html_e( 'Country', 'chatbotistic-widget' ); ?></th>
							<th><?php esc_html_e( 'Source', 'chatbotistic-widget' ); ?></th>
							<th><?php esc_html_e( 'When', 'chatbotistic-widget' ); ?></th>
							<th><?php esc_html_e( 'Status', 'chatbotistic-widget' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( array_slice( $rows, 0, 25 ) as $lead ) :
							$phone   = $lead['phone'] ?? $lead['whatsapp'] ?? '—';
							$country = $lead['country'] ?? '';
							$source  = $lead['referer'] ?? '';
							$created = $lead['created'] ?? '';
							$is_new      = ! empty( $lead['isLeadNew'] );
							$contacted   = ! empty( $lead['contacted'] );
							$badge_class = $is_new ? 'cbw-badge--new' : ( $contacted ? 'cbw-badge--contacted' : 'cbw-badge--open' );
							$badge_label = $is_new ? __( 'New', 'chatbotistic-widget' ) : ( $contacted ? __( 'Contacted', 'chatbotistic-widget' ) : __( 'Open', 'chatbotistic-widget' ) );
							?>
							<tr>
								<td><span class="cbw-mono"><?php echo esc_html( $phone ); ?></span></td>
								<td><?php echo $country ? '<span class="cbw-flag">' . esc_html( $country ) . '</span>' : '—'; ?></td>
								<td><span class="cbw-source" title="<?php echo esc_attr( $source ); ?>"><?php echo esc_html( $source ?: '—' ); ?></span></td>
								<td><?php echo esc_html( $created ? human_time_diff( strtotime( $created ), time() ) . ' ' . __( 'ago', 'chatbotistic-widget' ) : '—' ); ?></td>
								<td><span class="cbw-badge <?php echo esc_attr( $badge_class ); ?>"><?php echo esc_html( $badge_label ); ?></span></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif;
	}
	?>
</div>
