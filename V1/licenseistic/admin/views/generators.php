<?php
/**
 * Generators view (list + create + bulk generate).
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$generators = $wpdb->get_results( 'SELECT * FROM ' . wpistic_lsi_table( 'generators' ) . ' ORDER BY generator_id DESC', ARRAY_A ); // phpcs:ignore
$products   = WPistic_LSI_Product_Service::get_products( array( 'per_page' => 200 ) );
$edit_id    = isset( $_GET['edit'] ) ? (int) $_GET['edit'] : 0; // phpcs:ignore
$editing    = $edit_id ? WPistic_LSI_Key_Generator::get_generator( $edit_id ) : null;
?>
<div class="wrap wpistic-lsi-wrap">
	<h1><?php esc_html_e( 'Generators', 'licenseistic' ); ?></h1>
	<?php WPistic_LSI_Admin::maybe_print_notices(); ?>

	<div class="wpistic-lsi-flex">
		<div class="wpistic-lsi-flex-grow">
			<table class="wp-list-table widefat fixed striped">
				<thead><tr>
					<th><?php esc_html_e( 'ID', 'licenseistic' ); ?></th>
					<th><?php esc_html_e( 'Name', 'licenseistic' ); ?></th>
					<th><?php esc_html_e( 'Format', 'licenseistic' ); ?></th>
					<th><?php esc_html_e( 'Activation Limit', 'licenseistic' ); ?></th>
					<th><?php esc_html_e( 'Expiry Days', 'licenseistic' ); ?></th>
					<th></th>
				</tr></thead>
				<tbody>
					<?php if ( empty( $generators ) ) : ?>
						<tr><td colspan="6"><?php esc_html_e( 'No generators yet.', 'licenseistic' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $generators as $g ) :
							$sample = WPistic_LSI_Key_Generator::generate_key( $g );
							?>
							<tr>
								<td>#<?php echo (int) $g['generator_id']; ?></td>
								<td><?php echo esc_html( $g['name'] ); ?></td>
								<td><code><?php echo esc_html( $sample ); ?></code></td>
								<td><?php echo (int) $g['activation_limit']; ?></td>
								<td><?php echo (int) $g['expiry_days']; ?></td>
								<td><a href="<?php echo esc_url( add_query_arg( array( 'page' => 'licenseistic-generators', 'edit' => (int) $g['generator_id'] ), admin_url( 'admin.php' ) ) ); ?>" class="button button-small"><?php esc_html_e( 'Edit', 'licenseistic' ); ?></a></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>

			<h2 style="margin-top:30px"><?php esc_html_e( 'Bulk Generate Licenses', 'licenseistic' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="wpistic_lsi_bulk_generate" />
				<?php wp_nonce_field( WPistic_LSI_Admin::NONCE_BULK ); ?>
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'Quantity', 'licenseistic' ); ?></th>
						<td><input type="number" name="quantity" min="1" max="500" value="10" /></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Generator', 'licenseistic' ); ?></th>
						<td><select name="generator_id">
							<option value="0"><?php esc_html_e( 'Default', 'licenseistic' ); ?></option>
							<?php foreach ( $generators as $g ) : ?>
								<option value="<?php echo (int) $g['generator_id']; ?>"><?php echo esc_html( $g['name'] ); ?></option>
							<?php endforeach; ?>
						</select></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Product', 'licenseistic' ); ?></th>
						<td><select name="product_id">
							<option value="0"><?php esc_html_e( '— None —', 'licenseistic' ); ?></option>
							<?php foreach ( $products as $p ) : ?>
								<option value="<?php echo (int) $p['product_id']; ?>"><?php echo esc_html( $p['product_name'] ); ?></option>
							<?php endforeach; ?>
						</select></td>
					</tr>
				</table>
				<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Generate Licenses', 'licenseistic' ); ?></button></p>
			</form>
		</div>
		<div class="wpistic-lsi-flex-side">
			<h2><?php echo $editing ? esc_html__( 'Edit Generator', 'licenseistic' ) : esc_html__( 'Add Generator', 'licenseistic' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="wpistic_lsi_save_generator" />
				<input type="hidden" name="generator_id" value="<?php echo (int) ( $editing['generator_id'] ?? 0 ); ?>" />
				<?php wp_nonce_field( WPistic_LSI_Admin::NONCE_GENERATOR ); ?>
				<p><label><?php esc_html_e( 'Name', 'licenseistic' ); ?></label><input type="text" name="name" required class="widefat" value="<?php echo esc_attr( $editing['name'] ?? '' ); ?>" /></p>
				<p><label><?php esc_html_e( 'Prefix', 'licenseistic' ); ?></label><input type="text" name="prefix" class="widefat" value="<?php echo esc_attr( $editing['prefix'] ?? 'LSI' ); ?>" /></p>
				<p><label><?php esc_html_e( 'Suffix', 'licenseistic' ); ?></label><input type="text" name="suffix" class="widefat" value="<?php echo esc_attr( $editing['suffix'] ?? '' ); ?>" /></p>
				<p><label><?php esc_html_e( 'Segment Length', 'licenseistic' ); ?></label><input type="number" name="segment_length" value="<?php echo (int) ( $editing['segment_length'] ?? 5 ); ?>" /></p>
				<p><label><?php esc_html_e( 'Segment Count', 'licenseistic' ); ?></label><input type="number" name="segment_count" value="<?php echo (int) ( $editing['segment_count'] ?? 4 ); ?>" /></p>
				<p><label><?php esc_html_e( 'Separator', 'licenseistic' ); ?></label><input type="text" name="separator" maxlength="3" value="<?php echo esc_attr( $editing['separator'] ?? '-' ); ?>" /></p>
				<p><label><?php esc_html_e( 'Charset', 'licenseistic' ); ?></label><input type="text" name="charset" class="widefat" value="<?php echo esc_attr( $editing['charset'] ?? 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789' ); ?>" /></p>
				<p><label><?php esc_html_e( 'Activation Limit', 'licenseistic' ); ?></label><input type="number" name="activation_limit" value="<?php echo (int) ( $editing['activation_limit'] ?? 1 ); ?>" /></p>
				<p><label><?php esc_html_e( 'Expiry Days', 'licenseistic' ); ?></label><input type="number" name="expiry_days" value="<?php echo (int) ( $editing['expiry_days'] ?? 365 ); ?>" /></p>
				<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Save Generator', 'licenseistic' ); ?></button></p>
			</form>
		</div>
	</div>
</div>
