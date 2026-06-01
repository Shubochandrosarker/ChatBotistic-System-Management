<?php
/**
 * Logs view.
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$paged    = max( 1, isset( $_GET['paged'] ) ? (int) $_GET['paged'] : 1 ); // phpcs:ignore
$per_page = 50;
$offset   = ( $paged - 1 ) * $per_page;

$rows = $wpdb->get_results( $wpdb->prepare( // phpcs:ignore WordPress.DB
	'SELECT * FROM ' . wpistic_lsi_table( 'logs' ) . ' ORDER BY log_id DESC LIMIT %d OFFSET %d',
	$per_page,
	$offset
), ARRAY_A );
?>
<div class="wrap wpistic-lsi-wrap">
	<h1><?php esc_html_e( 'Logs', 'licenseistic' ); ?></h1>
	<?php WPistic_LSI_Admin::maybe_print_notices(); ?>
	<table class="wp-list-table widefat fixed striped">
		<thead><tr>
			<th><?php esc_html_e( 'Time', 'licenseistic' ); ?></th>
			<th><?php esc_html_e( 'Event', 'licenseistic' ); ?></th>
			<th><?php esc_html_e( 'Object', 'licenseistic' ); ?></th>
			<th><?php esc_html_e( 'Message', 'licenseistic' ); ?></th>
			<th><?php esc_html_e( 'User', 'licenseistic' ); ?></th>
			<th><?php esc_html_e( 'IP', 'licenseistic' ); ?></th>
		</tr></thead>
		<tbody>
			<?php if ( empty( $rows ) ) : ?>
				<tr><td colspan="6"><?php esc_html_e( 'No log entries.', 'licenseistic' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $rows as $r ) : ?>
					<tr>
						<td><?php echo esc_html( $r['created_at'] ); ?></td>
						<td><code><?php echo esc_html( $r['event_type'] ); ?></code></td>
						<td><?php echo esc_html( $r['object_type'] . ' #' . (int) $r['object_id'] ); ?></td>
						<td><?php echo esc_html( $r['message'] ); ?></td>
						<td><?php echo (int) $r['user_id']; ?></td>
						<td><?php echo esc_html( $r['ip_address'] ?: '—' ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>
