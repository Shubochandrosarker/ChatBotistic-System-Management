<?php
/**
 * Email template: license created.
 *
 * Variables: $license, $plain_key, $customer_name, $site_name, $dashboard_url.
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p><?php
	/* translators: %s: customer name. */
	printf( esc_html__( 'Hi %s,', 'licenseistic' ), esc_html( $customer_name ) );
?></p>
<p><?php esc_html_e( 'Your license has been created. Please keep this email safe — you will need the license key to activate your software.', 'licenseistic' ); ?></p>
<table>
	<tr><td><strong><?php esc_html_e( 'License Key:', 'licenseistic' ); ?></strong></td>
		<td><code><?php echo esc_html( $plain_key ); ?></code></td></tr>
	<?php if ( ! empty( $license['expires_at'] ) ) : ?>
		<tr><td><strong><?php esc_html_e( 'Expires:', 'licenseistic' ); ?></strong></td>
			<td><?php echo esc_html( $license['expires_at'] ); ?></td></tr>
	<?php endif; ?>
	<tr><td><strong><?php esc_html_e( 'Activation Limit:', 'licenseistic' ); ?></strong></td>
		<td><?php echo (int) $license['activation_limit']; ?></td></tr>
</table>
<?php if ( ! empty( $dashboard_url ) ) : ?>
	<p><a href="<?php echo esc_url( $dashboard_url ); ?>"><?php esc_html_e( 'View your licenses', 'licenseistic' ); ?></a></p>
<?php endif; ?>
<p><?php echo esc_html( $site_name ); ?></p>
