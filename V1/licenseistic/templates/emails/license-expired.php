<?php
/**
 * Email template: license expired.
 *
 * Variables: $license, $customer_name, $site_name, $renew_url.
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
<p><?php esc_html_e( 'Your license has expired and can no longer be used to activate or use the software.', 'licenseistic' ); ?></p>
<p><strong><?php esc_html_e( 'Expired on:', 'licenseistic' ); ?></strong> <?php echo esc_html( $license['expires_at'] ); ?></p>
<?php if ( ! empty( $renew_url ) ) : ?>
	<p><a href="<?php echo esc_url( $renew_url ); ?>"><?php esc_html_e( 'Renew your license', 'licenseistic' ); ?></a></p>
<?php endif; ?>
<p><?php echo esc_html( $site_name ); ?></p>
