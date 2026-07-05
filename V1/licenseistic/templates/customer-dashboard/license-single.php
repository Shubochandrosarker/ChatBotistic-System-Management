<?php
/**
 * Single license detail template.
 *
 * Variables: $selected_license, $user.
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$activations = $wpdb->get_results( $wpdb->prepare( // phpcs:ignore WordPress.DB
	'SELECT * FROM ' . wpistic_lsi_table( 'activations' ) . ' WHERE license_id = %d ORDER BY activation_id DESC',
	(int) $selected_license['license_id']
), ARRAY_A );

$ajax_nonce = wp_create_nonce( 'wpistic_lsi_customer' );
?>
<div class="wpistic-lsi-license-single">
	<h3><?php
		/* translators: %d: license ID. */
		printf( esc_html__( 'License #%d', 'licenseistic' ), (int) $selected_license['license_id'] );
	?></h3>
	<p><strong><?php esc_html_e( 'Key:', 'licenseistic' ); ?></strong>
		<code><?php echo esc_html( WPistic_LSI_License_Service::get_display_key( $selected_license ) ); ?></code></p>
	<p><strong><?php esc_html_e( 'Status:', 'licenseistic' ); ?></strong>
		<?php echo esc_html( WPistic_LSI_License_Service::resolve_status( $selected_license ) ); ?></p>

	<h4><?php esc_html_e( 'Active Sites', 'licenseistic' ); ?></h4>
	<?php if ( empty( $activations ) ) : ?>
		<p><?php esc_html_e( 'No sites registered.', 'licenseistic' ); ?></p>
	<?php else : ?>
		<table class="wpistic-lsi-table">
			<thead><tr>
				<th><?php esc_html_e( 'Site URL', 'licenseistic' ); ?></th>
				<th><?php esc_html_e( 'Status', 'licenseistic' ); ?></th>
				<th><?php esc_html_e( 'Activated', 'licenseistic' ); ?></th>
				<th></th>
			</tr></thead>
			<tbody>
				<?php foreach ( $activations as $a ) : ?>
					<tr>
						<td><?php echo esc_html( $a['site_url'] ?: $a['instance_id'] ); ?></td>
						<td><?php echo esc_html( $a['status'] ); ?></td>
						<td><?php echo esc_html( $a['activated_at'] ); ?></td>
						<td>
							<?php if ( 'active' === $a['status'] ) : ?>
								<button class="wpistic-lsi-deactivate" data-activation="<?php echo (int) $a['activation_id']; ?>" data-nonce="<?php echo esc_attr( $ajax_nonce ); ?>"><?php esc_html_e( 'Deactivate', 'licenseistic' ); ?></button>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
