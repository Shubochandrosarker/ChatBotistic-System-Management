<?php
/**
 * Licenses list view.
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$status   = isset( $_GET['status'] ) ? wpistic_lsi_sanitize_status( wp_unslash( $_GET['status'] ) ) : ''; // phpcs:ignore
$search   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore
$paged    = max( 1, isset( $_GET['paged'] ) ? (int) $_GET['paged'] : 1 ); // phpcs:ignore
$per_page = 25;
$offset   = ( $paged - 1 ) * $per_page;

$licenses = WPistic_LSI_License_Service::get_licenses( array(
	'status'   => $status,
	'search'   => $search,
	'per_page' => $per_page,
	'offset'   => $offset,
) );

$add_url = add_query_arg( array( 'page' => 'licenseistic-licenses', 'action' => 'new' ), admin_url( 'admin.php' ) );
?>
<div class="wrap wpistic-lsi-wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Licenses', 'licenseistic' ); ?></h1>
	<a href="<?php echo esc_url( $add_url ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'licenseistic' ); ?></a>
	<hr class="wp-header-end" />
	<?php WPistic_LSI_Admin::maybe_print_notices(); ?>

	<form method="get">
		<input type="hidden" name="page" value="licenseistic-licenses" />
		<p class="search-box">
			<label class="screen-reader-text" for="lsi-search"><?php esc_html_e( 'Search', 'licenseistic' ); ?></label>
			<input type="search" id="lsi-search" name="s" value="<?php echo esc_attr( $search ); ?>" />
			<select name="status">
				<option value=""><?php esc_html_e( 'All statuses', 'licenseistic' ); ?></option>
				<?php foreach ( wpistic_lsi_get_statuses() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status, $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<button type="submit" class="button"><?php esc_html_e( 'Filter', 'licenseistic' ); ?></button>
		</p>
	</form>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'ID', 'licenseistic' ); ?></th>
				<th><?php esc_html_e( 'License Key', 'licenseistic' ); ?></th>
				<th><?php esc_html_e( 'Customer', 'licenseistic' ); ?></th>
				<th><?php esc_html_e( 'Product', 'licenseistic' ); ?></th>
				<th><?php esc_html_e( 'Status', 'licenseistic' ); ?></th>
				<th><?php esc_html_e( 'Activations', 'licenseistic' ); ?></th>
				<th><?php esc_html_e( 'Expires', 'licenseistic' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'licenseistic' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $licenses ) ) : ?>
				<tr><td colspan="8"><?php esc_html_e( 'No licenses found.', 'licenseistic' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $licenses as $row ) :
					$product = $row['product_id'] ? WPistic_LSI_Product_Service::get_product( (int) $row['product_id'] ) : null;
					$edit_url = add_query_arg( array( 'page' => 'licenseistic-licenses', 'action' => 'edit', 'license_id' => (int) $row['license_id'] ), admin_url( 'admin.php' ) );
					?>
					<tr>
						<td>#<?php echo (int) $row['license_id']; ?></td>
						<td><code><?php echo esc_html( WPistic_LSI_License_Service::get_display_key( $row ) ); ?></code></td>
						<td><?php echo esc_html( $row['customer_email'] ?: '—' ); ?></td>
						<td><?php echo esc_html( $product ? $product['product_name'] : '—' ); ?></td>
						<td><span class="lsi-status lsi-status-<?php echo esc_attr( $row['status'] ); ?>"><?php echo esc_html( $row['status'] ); ?></span></td>
						<td><?php echo (int) $row['activation_count']; ?> / <?php echo (int) $row['activation_limit']; ?></td>
						<td><?php echo esc_html( $row['expires_at'] ?: '—' ); ?></td>
						<td>
							<a href="<?php echo esc_url( $edit_url ); ?>" class="button button-small"><?php esc_html_e( 'Edit', 'licenseistic' ); ?></a>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline" onsubmit="return confirm('<?php echo esc_js( __( 'Delete this license?', 'licenseistic' ) ); ?>');">
								<input type="hidden" name="action" value="wpistic_lsi_delete_license" />
								<input type="hidden" name="license_id" value="<?php echo (int) $row['license_id']; ?>" />
								<?php wp_nonce_field( 'wpistic_lsi_delete_license' ); ?>
								<button type="submit" class="button button-small button-link-delete"><?php esc_html_e( 'Delete', 'licenseistic' ); ?></button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>
