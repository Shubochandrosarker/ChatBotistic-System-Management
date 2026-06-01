<?php
/**
 * Activations list view.
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$rows = $wpdb->get_results( 'SELECT * FROM ' . wpistic_lsi_table( 'activations' ) . ' ORDER BY activation_id DESC LIMIT 200', ARRAY_A ); // phpcs:ignore
?>
<div class="wrap wpistic-lsi-wrap">
	<h1><?php esc_html_e( 'Activations', 'licenseistic' ); ?></h1>
	<?php WPistic_LSI_Admin::maybe_print_notices(); ?>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'ID', 'licenseistic' ); ?></th>
				<th><?php esc_html_e( 'License', 'licenseistic' ); ?></th>
				<th><?php esc_html_e( 'Site URL', 'licenseistic' ); ?></th>
				<th><?php esc_html_e( 'Instance', 'licenseistic' ); ?></th>
				<th><?php esc_html_e( 'IP', 'licenseistic' ); ?></th>
				<th><?php esc_html_e( 'Status', 'licenseistic' ); ?></th>
				<th><?php esc_html_e( 'Activated', 'licenseistic' ); ?></th>
				<th><?php esc_html_e( 'Last Ping', 'licenseistic' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $rows ) ) : ?>
				<tr><td colspan="8"><?php esc_html_e( 'No activations yet.', 'licenseistic' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $rows as $a ) : ?>
					<tr>
						<td>#<?php echo (int) $a['activation_id']; ?></td>
						<td>#<?php echo (int) $a['license_id']; ?></td>
						<td><?php echo esc_html( $a['site_url'] ?: '—' ); ?></td>
						<td><?php echo esc_html( $a['instance_id'] ?: '—' ); ?></td>
						<td><?php echo esc_html( $a['ip_address'] ?: '—' ); ?></td>
						<td><?php echo esc_html( $a['status'] ); ?></td>
						<td><?php echo esc_html( $a['activated_at'] ); ?></td>
						<td><?php echo esc_html( $a['last_ping_at'] ?: '—' ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>
