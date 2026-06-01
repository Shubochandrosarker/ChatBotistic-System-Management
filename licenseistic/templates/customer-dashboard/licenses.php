<?php
/**
 * Customer dashboard template — licenses list.
 *
 * Variables: $licenses (array), $user (WP_User).
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wpistic-lsi-customer-dashboard">
	<h2><?php esc_html_e( 'My Licenses', 'licenseistic' ); ?></h2>

	<?php if ( empty( $licenses ) ) : ?>
		<p><?php esc_html_e( 'You do not have any licenses yet.', 'licenseistic' ); ?></p>
	<?php else : ?>
		<table class="wpistic-lsi-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'License Key', 'licenseistic' ); ?></th>
					<th><?php esc_html_e( 'Product', 'licenseistic' ); ?></th>
					<th><?php esc_html_e( 'Status', 'licenseistic' ); ?></th>
					<th><?php esc_html_e( 'Activations', 'licenseistic' ); ?></th>
					<th><?php esc_html_e( 'Expires', 'licenseistic' ); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $licenses as $license ) :
					$product       = $license['product_id'] ? WPistic_LSI_Product_Service::get_product( (int) $license['product_id'] ) : null;
					$status        = WPistic_LSI_License_Service::resolve_status( $license );
					$single_url    = add_query_arg( array( 'lsi_license' => (int) $license['license_id'] ) );
					$display_key   = WPistic_LSI_License_Service::get_display_key( $license );
					?>
					<tr>
						<td><code><?php echo esc_html( $display_key ); ?></code></td>
						<td><?php echo esc_html( $product ? $product['product_name'] : '—' ); ?></td>
						<td><span class="lsi-status lsi-status-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $status ); ?></span></td>
						<td><?php echo (int) $license['activation_count']; ?> / <?php echo (int) $license['activation_limit']; ?></td>
						<td><?php echo esc_html( $license['expires_at'] ?: '—' ); ?></td>
						<td><a href="<?php echo esc_url( $single_url ); ?>" class="wpistic-lsi-link"><?php esc_html_e( 'View', 'licenseistic' ); ?></a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>

	<?php
	$selected_id = isset( $_GET['lsi_license'] ) ? (int) $_GET['lsi_license'] : 0; // phpcs:ignore
	if ( $selected_id ) {
		$selected_license = WPistic_LSI_License_Service::get_license( $selected_id );
		if ( $selected_license &&
			( (int) $selected_license['customer_id'] === (int) $user->ID
				|| strtolower( (string) $selected_license['customer_email'] ) === strtolower( $user->user_email ) ) ) {
			include WPISTIC_LSI_PATH . 'templates/customer-dashboard/license-single.php';
		}
	}
	?>
</div>
