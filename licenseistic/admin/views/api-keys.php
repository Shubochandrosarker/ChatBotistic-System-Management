<?php
/**
 * API keys view.
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$keys      = WPistic_LSI_API_Auth::get_api_keys();
$new_key   = get_transient( 'wpistic_lsi_new_api_key_' . get_current_user_id() );
if ( $new_key ) {
	delete_transient( 'wpistic_lsi_new_api_key_' . get_current_user_id() );
}
?>
<div class="wrap wpistic-lsi-wrap">
	<h1><?php esc_html_e( 'API Keys', 'licenseistic' ); ?></h1>
	<?php WPistic_LSI_Admin::maybe_print_notices(); ?>

	<?php if ( $new_key ) : ?>
		<div class="notice notice-success">
			<p><strong><?php esc_html_e( 'Save these credentials — the secret key will not be shown again:', 'licenseistic' ); ?></strong></p>
			<p><?php esc_html_e( 'Public key:', 'licenseistic' ); ?> <code><?php echo esc_html( $new_key['public_key'] ); ?></code></p>
			<p><?php esc_html_e( 'Secret key:', 'licenseistic' ); ?> <code><?php echo esc_html( $new_key['secret_key'] ); ?></code></p>
		</div>
	<?php endif; ?>

	<div class="wpistic-lsi-flex">
		<div class="wpistic-lsi-flex-grow">
			<table class="wp-list-table widefat fixed striped">
				<thead><tr>
					<th><?php esc_html_e( 'ID', 'licenseistic' ); ?></th>
					<th><?php esc_html_e( 'Name', 'licenseistic' ); ?></th>
					<th><?php esc_html_e( 'Public Key', 'licenseistic' ); ?></th>
					<th><?php esc_html_e( 'Permissions', 'licenseistic' ); ?></th>
					<th><?php esc_html_e( 'Status', 'licenseistic' ); ?></th>
					<th><?php esc_html_e( 'Last Used', 'licenseistic' ); ?></th>
					<th></th>
				</tr></thead>
				<tbody>
					<?php if ( empty( $keys ) ) : ?>
						<tr><td colspan="7"><?php esc_html_e( 'No API keys yet.', 'licenseistic' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $keys as $k ) :
							$perms = ! empty( $k['permissions'] ) ? implode( ', ', (array) json_decode( $k['permissions'], true ) ) : ''; ?>
							<tr>
								<td>#<?php echo (int) $k['api_key_id']; ?></td>
								<td><?php echo esc_html( $k['name'] ); ?></td>
								<td><code><?php echo esc_html( $k['public_key'] ); ?></code></td>
								<td><?php echo esc_html( $perms ); ?></td>
								<td><?php echo esc_html( $k['status'] ); ?></td>
								<td><?php echo esc_html( $k['last_used_at'] ?: '—' ); ?></td>
								<td>
									<?php if ( 'active' === $k['status'] ) : ?>
										<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
											<input type="hidden" name="action" value="wpistic_lsi_revoke_api_key" />
											<input type="hidden" name="api_key_id" value="<?php echo (int) $k['api_key_id']; ?>" />
											<?php wp_nonce_field( 'wpistic_lsi_revoke_api_key' ); ?>
											<button class="button button-small button-link-delete" type="submit"><?php esc_html_e( 'Revoke', 'licenseistic' ); ?></button>
										</form>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<div class="wpistic-lsi-flex-side">
			<h2><?php esc_html_e( 'Create API Key', 'licenseistic' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="wpistic_lsi_create_api_key" />
				<?php wp_nonce_field( WPistic_LSI_Admin::NONCE_API_KEY ); ?>
				<p><label><?php esc_html_e( 'Name', 'licenseistic' ); ?></label><input type="text" name="name" class="widefat" required /></p>
				<p>
					<label><input type="checkbox" name="permissions[]" value="read" checked /> <?php esc_html_e( 'Read', 'licenseistic' ); ?></label><br />
					<label><input type="checkbox" name="permissions[]" value="write" checked /> <?php esc_html_e( 'Write', 'licenseistic' ); ?></label>
				</p>
				<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Create API Key', 'licenseistic' ); ?></button></p>
			</form>
		</div>
	</div>
</div>
