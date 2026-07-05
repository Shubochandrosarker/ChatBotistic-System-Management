<?php
/**
 * License edit/create view.
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$license_id = isset( $_GET['license_id'] ) ? (int) $_GET['license_id'] : 0; // phpcs:ignore
$license    = $license_id ? WPistic_LSI_License_Service::get_license( $license_id ) : null;
$is_edit    = (bool) $license;
$products   = WPistic_LSI_Product_Service::get_products( array( 'per_page' => 200 ) );

$plain_key = '';
if ( $is_edit && ! empty( $license['license_key_encrypted'] ) ) {
	$plain_key = WPistic_LSI_Crypto::decrypt( $license['license_key_encrypted'] );
}
?>
<div class="wrap wpistic-lsi-wrap">
	<h1><?php echo $is_edit ? esc_html__( 'Edit License', 'licenseistic' ) : esc_html__( 'New License', 'licenseistic' ); ?></h1>
	<?php WPistic_LSI_Admin::maybe_print_notices(); ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="wpistic_lsi_save_license" />
		<input type="hidden" name="license_id" value="<?php echo (int) $license_id; ?>" />
		<?php wp_nonce_field( WPistic_LSI_Admin::NONCE_LICENSE ); ?>

		<table class="form-table">
			<?php if ( $is_edit ) : ?>
				<tr>
					<th><label><?php esc_html_e( 'License Key', 'licenseistic' ); ?></label></th>
					<td><code><?php echo esc_html( $plain_key ); ?></code></td>
				</tr>
			<?php else : ?>
				<tr>
					<th><label for="license_key"><?php esc_html_e( 'License Key', 'licenseistic' ); ?></label></th>
					<td>
						<input type="text" id="license_key" name="license_key" class="regular-text" placeholder="<?php esc_attr_e( 'Leave empty to auto-generate', 'licenseistic' ); ?>" />
					</td>
				</tr>
			<?php endif; ?>

			<tr>
				<th><label for="license_label"><?php esc_html_e( 'Label', 'licenseistic' ); ?></label></th>
				<td><input type="text" id="license_label" name="license_label" class="regular-text" value="<?php echo esc_attr( $license['license_label'] ?? '' ); ?>" /></td>
			</tr>
			<tr>
				<th><label for="product_id"><?php esc_html_e( 'Product', 'licenseistic' ); ?></label></th>
				<td>
					<select id="product_id" name="product_id">
						<option value="0"><?php esc_html_e( '— Select —', 'licenseistic' ); ?></option>
						<?php foreach ( $products as $product ) : ?>
							<option value="<?php echo (int) $product['product_id']; ?>" <?php selected( $license['product_id'] ?? 0, $product['product_id'] ); ?>><?php echo esc_html( $product['product_name'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="customer_email"><?php esc_html_e( 'Customer Email', 'licenseistic' ); ?></label></th>
				<td><input type="email" id="customer_email" name="customer_email" class="regular-text" value="<?php echo esc_attr( $license['customer_email'] ?? '' ); ?>" /></td>
			</tr>
			<tr>
				<th><label for="customer_id"><?php esc_html_e( 'Customer User ID', 'licenseistic' ); ?></label></th>
				<td><input type="number" id="customer_id" name="customer_id" value="<?php echo (int) ( $license['customer_id'] ?? 0 ); ?>" /></td>
			</tr>
			<tr>
				<th><label for="status"><?php esc_html_e( 'Status', 'licenseistic' ); ?></label></th>
				<td>
					<select id="status" name="status">
						<?php foreach ( wpistic_lsi_get_statuses() as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $license['status'] ?? 'active', $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="activation_limit"><?php esc_html_e( 'Activation Limit', 'licenseistic' ); ?></label></th>
				<td><input type="number" id="activation_limit" name="activation_limit" min="0" value="<?php echo (int) ( $license['activation_limit'] ?? wpistic_lsi_get_setting( 'default_activation_limit', 1 ) ); ?>" /></td>
			</tr>
			<tr>
				<th><label for="expires_at"><?php esc_html_e( 'Expires At', 'licenseistic' ); ?></label></th>
				<td><input type="datetime-local" id="expires_at" name="expires_at" value="<?php echo esc_attr( $license['expires_at'] ? str_replace( ' ', 'T', $license['expires_at'] ) : '' ); ?>" /></td>
			</tr>
			<tr>
				<th><label for="notes"><?php esc_html_e( 'Notes', 'licenseistic' ); ?></label></th>
				<td><textarea id="notes" name="notes" rows="3" class="large-text"><?php echo esc_textarea( $license['notes'] ?? '' ); ?></textarea></td>
			</tr>
		</table>
		<p class="submit">
			<button type="submit" class="button button-primary"><?php echo $is_edit ? esc_html__( 'Update License', 'licenseistic' ) : esc_html__( 'Create License', 'licenseistic' ); ?></button>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=licenseistic-licenses' ) ); ?>" class="button"><?php esc_html_e( 'Cancel', 'licenseistic' ); ?></a>
		</p>
	</form>
</div>
