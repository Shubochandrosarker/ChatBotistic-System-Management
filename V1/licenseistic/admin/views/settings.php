<?php
/**
 * Settings view.
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = wpistic_lsi_get_settings();
?>
<div class="wrap wpistic-lsi-wrap">
	<h1><?php esc_html_e( 'Licenseistic Settings', 'licenseistic' ); ?></h1>
	<?php WPistic_LSI_Admin::maybe_print_notices(); ?>

	<form method="post" action="options.php">
		<?php settings_fields( 'wpistic_lsi_settings_group' ); ?>

		<h2><?php esc_html_e( 'General', 'licenseistic' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><label><?php esc_html_e( 'Default Status', 'licenseistic' ); ?></label></th>
				<td>
					<select name="wpistic_lsi_settings[default_status]">
						<?php foreach ( wpistic_lsi_get_statuses() as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $settings['default_status'], $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label><?php esc_html_e( 'Default Activation Limit', 'licenseistic' ); ?></label></th>
				<td><input type="number" name="wpistic_lsi_settings[default_activation_limit]" value="<?php echo (int) $settings['default_activation_limit']; ?>" min="0" /></td>
			</tr>
			<tr>
				<th><label><?php esc_html_e( 'Default Expiry Days', 'licenseistic' ); ?></label></th>
				<td><input type="number" name="wpistic_lsi_settings[default_expiry_days]" value="<?php echo (int) $settings['default_expiry_days']; ?>" min="0" /></td>
			</tr>
			<tr>
				<th><label><?php esc_html_e( 'Enable Customer Dashboard', 'licenseistic' ); ?></label></th>
				<td><label><input type="checkbox" name="wpistic_lsi_settings[enable_customer_dashboard]" value="yes" <?php checked( 'yes', $settings['enable_customer_dashboard'] ); ?> /> <?php esc_html_e( 'Yes', 'licenseistic' ); ?></label></td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Security', 'licenseistic' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><label><?php esc_html_e( 'Mask License Keys on Display', 'licenseistic' ); ?></label></th>
				<td><label><input type="checkbox" name="wpistic_lsi_settings[mask_keys]" value="yes" <?php checked( 'yes', $settings['mask_keys'] ); ?> /> <?php esc_html_e( 'Yes', 'licenseistic' ); ?></label></td>
			</tr>
			<tr>
				<th><label><?php esc_html_e( 'API Rate Limit (per minute)', 'licenseistic' ); ?></label></th>
				<td><input type="number" name="wpistic_lsi_settings[rate_limit_per_minute]" value="<?php echo (int) $settings['rate_limit_per_minute']; ?>" min="0" /></td>
			</tr>
			<tr>
				<th><label><?php esc_html_e( 'Require Product ID', 'licenseistic' ); ?></label></th>
				<td><label><input type="checkbox" name="wpistic_lsi_settings[require_product_id]" value="yes" <?php checked( 'yes', $settings['require_product_id'] ); ?> /> <?php esc_html_e( 'Yes', 'licenseistic' ); ?></label></td>
			</tr>
			<tr>
				<th><label><?php esc_html_e( 'Allow Same Domain Reactivation', 'licenseistic' ); ?></label></th>
				<td><label><input type="checkbox" name="wpistic_lsi_settings[allow_same_domain_reactivation]" value="yes" <?php checked( 'yes', $settings['allow_same_domain_reactivation'] ); ?> /> <?php esc_html_e( 'Yes', 'licenseistic' ); ?></label></td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'API', 'licenseistic' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><label><?php esc_html_e( 'Enable REST API', 'licenseistic' ); ?></label></th>
				<td><label><input type="checkbox" name="wpistic_lsi_settings[enable_rest_api]" value="yes" <?php checked( 'yes', $settings['enable_rest_api'] ); ?> /> <?php esc_html_e( 'Yes', 'licenseistic' ); ?></label></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'REST Namespace', 'licenseistic' ); ?></th>
				<td><code><?php echo esc_html( rest_url( WPISTIC_LSI_REST_NAMESPACE . '/' ) ); ?></code></td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Logs', 'licenseistic' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><label><?php esc_html_e( 'Enable Logs', 'licenseistic' ); ?></label></th>
				<td><label><input type="checkbox" name="wpistic_lsi_settings[enable_logs]" value="yes" <?php checked( 'yes', $settings['enable_logs'] ); ?> /> <?php esc_html_e( 'Yes', 'licenseistic' ); ?></label></td>
			</tr>
			<tr>
				<th><label><?php esc_html_e( 'Log Retention Days', 'licenseistic' ); ?></label></th>
				<td><input type="number" name="wpistic_lsi_settings[log_retention_days]" value="<?php echo (int) $settings['log_retention_days']; ?>" min="0" /></td>
			</tr>
		</table>

		<?php submit_button(); ?>
	</form>
</div>
