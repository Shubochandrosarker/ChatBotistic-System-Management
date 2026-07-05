<?php
/**
 * Admin dashboard view.
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$stats = array(
	'total'       => WPistic_LSI_DB::count( 'licenses' ),
	'active'      => WPistic_LSI_DB::count( 'licenses', 'active' ),
	'expired'     => WPistic_LSI_DB::count( 'licenses', 'expired' ),
	'revoked'     => WPistic_LSI_DB::count( 'licenses', 'revoked' ),
	'suspended'   => WPistic_LSI_DB::count( 'licenses', 'suspended' ),
	'activations' => WPistic_LSI_DB::count( 'activations' ),
	'products'    => WPistic_LSI_DB::count( 'products' ),
);

$recent_logs = WPistic_LSI_Logger::recent( 10 );
?>
<div class="wrap wpistic-lsi-wrap">
	<h1><?php esc_html_e( 'Licenseistic Dashboard', 'licenseistic' ); ?></h1>
	<?php WPistic_LSI_Admin::maybe_print_notices(); ?>

	<div class="wpistic-lsi-cards">
		<?php
		$cards = array(
			array( __( 'Total Licenses', 'licenseistic' ), $stats['total'] ),
			array( __( 'Active', 'licenseistic' ), $stats['active'] ),
			array( __( 'Expired', 'licenseistic' ), $stats['expired'] ),
			array( __( 'Revoked', 'licenseistic' ), $stats['revoked'] ),
			array( __( 'Suspended', 'licenseistic' ), $stats['suspended'] ),
			array( __( 'Total Activations', 'licenseistic' ), $stats['activations'] ),
			array( __( 'Products', 'licenseistic' ), $stats['products'] ),
		);
		foreach ( $cards as $card ) :
			?>
			<div class="wpistic-lsi-card">
				<div class="wpistic-lsi-card-label"><?php echo esc_html( $card[0] ); ?></div>
				<div class="wpistic-lsi-card-value"><?php echo esc_html( number_format_i18n( $card[1] ) ); ?></div>
			</div>
		<?php endforeach; ?>
	</div>

	<h2><?php esc_html_e( 'Recent Events', 'licenseistic' ); ?></h2>
	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Time', 'licenseistic' ); ?></th>
				<th><?php esc_html_e( 'Event', 'licenseistic' ); ?></th>
				<th><?php esc_html_e( 'Object', 'licenseistic' ); ?></th>
				<th><?php esc_html_e( 'Message', 'licenseistic' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $recent_logs ) ) : ?>
				<tr><td colspan="4"><?php esc_html_e( 'No events yet.', 'licenseistic' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $recent_logs as $log ) : ?>
					<tr>
						<td><?php echo esc_html( $log['created_at'] ); ?></td>
						<td><code><?php echo esc_html( $log['event_type'] ); ?></code></td>
						<td><?php echo esc_html( $log['object_type'] . ' #' . (int) $log['object_id'] ); ?></td>
						<td><?php echo esc_html( $log['message'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>
