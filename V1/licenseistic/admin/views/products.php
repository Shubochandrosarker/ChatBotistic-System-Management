<?php
/**
 * Products view (list + inline create form).
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$products   = WPistic_LSI_Product_Service::get_products( array( 'per_page' => 200 ) );
$generators = $GLOBALS['wpdb']->get_results( 'SELECT generator_id, name FROM ' . wpistic_lsi_table( 'generators' ) . ' ORDER BY generator_id DESC', ARRAY_A ); // phpcs:ignore
$edit_id    = isset( $_GET['edit'] ) ? (int) $_GET['edit'] : 0; // phpcs:ignore
$editing    = $edit_id ? WPistic_LSI_Product_Service::get_product( $edit_id ) : null;
?>
<div class="wrap wpistic-lsi-wrap">
	<h1><?php esc_html_e( 'Products', 'licenseistic' ); ?></h1>
	<?php WPistic_LSI_Admin::maybe_print_notices(); ?>

	<div class="wpistic-lsi-flex">
		<div class="wpistic-lsi-flex-grow">
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'ID', 'licenseistic' ); ?></th>
						<th><?php esc_html_e( 'Name', 'licenseistic' ); ?></th>
						<th><?php esc_html_e( 'Slug', 'licenseistic' ); ?></th>
						<th><?php esc_html_e( 'Version', 'licenseistic' ); ?></th>
						<th><?php esc_html_e( 'Status', 'licenseistic' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $products ) ) : ?>
						<tr><td colspan="6"><?php esc_html_e( 'No products yet.', 'licenseistic' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $products as $p ) : ?>
							<tr>
								<td>#<?php echo (int) $p['product_id']; ?></td>
								<td><?php echo esc_html( $p['product_name'] ); ?></td>
								<td><code><?php echo esc_html( $p['product_slug'] ); ?></code></td>
								<td><?php echo esc_html( $p['product_version'] ?: '—' ); ?></td>
								<td><?php echo esc_html( $p['status'] ); ?></td>
								<td><a href="<?php echo esc_url( add_query_arg( array( 'page' => 'licenseistic-products', 'edit' => (int) $p['product_id'] ), admin_url( 'admin.php' ) ) ); ?>" class="button button-small"><?php esc_html_e( 'Edit', 'licenseistic' ); ?></a></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<div class="wpistic-lsi-flex-side">
			<h2><?php echo $editing ? esc_html__( 'Edit Product', 'licenseistic' ) : esc_html__( 'Add Product', 'licenseistic' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="wpistic_lsi_save_product" />
				<input type="hidden" name="product_id" value="<?php echo (int) ( $editing['product_id'] ?? 0 ); ?>" />
				<?php wp_nonce_field( WPistic_LSI_Admin::NONCE_PRODUCT ); ?>

				<p><label><?php esc_html_e( 'Name', 'licenseistic' ); ?></label>
					<input type="text" name="product_name" required class="widefat" value="<?php echo esc_attr( $editing['product_name'] ?? '' ); ?>" /></p>
				<p><label><?php esc_html_e( 'Slug', 'licenseistic' ); ?></label>
					<input type="text" name="product_slug" class="widefat" value="<?php echo esc_attr( $editing['product_slug'] ?? '' ); ?>" /></p>
				<p><label><?php esc_html_e( 'Type', 'licenseistic' ); ?></label>
					<input type="text" name="product_type" class="widefat" value="<?php echo esc_attr( $editing['product_type'] ?? 'software' ); ?>" /></p>
				<p><label><?php esc_html_e( 'Version', 'licenseistic' ); ?></label>
					<input type="text" name="product_version" class="widefat" value="<?php echo esc_attr( $editing['product_version'] ?? '' ); ?>" /></p>
				<p><label><?php esc_html_e( 'Download URL', 'licenseistic' ); ?></label>
					<input type="url" name="download_url" class="widefat" value="<?php echo esc_attr( $editing['download_url'] ?? '' ); ?>" /></p>
				<p><label><?php esc_html_e( 'Changelog', 'licenseistic' ); ?></label>
					<textarea name="changelog" rows="3" class="widefat"><?php echo esc_textarea( $editing['changelog'] ?? '' ); ?></textarea></p>
				<p><label><?php esc_html_e( 'Default Activation Limit', 'licenseistic' ); ?></label>
					<input type="number" name="default_activation_limit" value="<?php echo (int) ( $editing['default_activation_limit'] ?? 1 ); ?>" /></p>
				<p><label><?php esc_html_e( 'Default Expiry Days', 'licenseistic' ); ?></label>
					<input type="number" name="default_expiry_days" value="<?php echo (int) ( $editing['default_expiry_days'] ?? 365 ); ?>" /></p>
				<p><label><?php esc_html_e( 'Generator', 'licenseistic' ); ?></label>
					<select name="generator_id">
						<option value="0"><?php esc_html_e( '— None —', 'licenseistic' ); ?></option>
						<?php foreach ( $generators as $g ) : ?>
							<option value="<?php echo (int) $g['generator_id']; ?>" <?php selected( $editing['generator_id'] ?? 0, $g['generator_id'] ); ?>><?php echo esc_html( $g['name'] ); ?></option>
						<?php endforeach; ?>
					</select></p>
				<p><label><?php esc_html_e( 'Status', 'licenseistic' ); ?></label>
					<select name="status">
						<option value="active" <?php selected( $editing['status'] ?? 'active', 'active' ); ?>><?php esc_html_e( 'Active', 'licenseistic' ); ?></option>
						<option value="disabled" <?php selected( $editing['status'] ?? '', 'disabled' ); ?>><?php esc_html_e( 'Disabled', 'licenseistic' ); ?></option>
					</select></p>
				<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Save Product', 'licenseistic' ); ?></button></p>
			</form>
		</div>
	</div>
</div>
