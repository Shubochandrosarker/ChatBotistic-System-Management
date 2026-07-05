<?php
/**
 * Portal view: Invoices — native Chatbotistic payment history.
 *
 * Reads Memberistic payments directly (no [memberistic_payment_history] embed,
 * which renders the unrelated gun-range account template).
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/** @var array $cb_m Membership data from cb_membership(). */

$cb_payments = array();
$cb_repo     = '\WordPressistic\Memberistic\Database\Payments_Repository';
if ( ! empty( $cb_m['id'] ) && class_exists( $cb_repo ) && method_exists( $cb_repo, 'get_by_membership' ) ) {
	$cb_payments = (array) $cb_repo::get_by_membership( (int) $cb_m['id'] );
}
?>

<div class="cb-panel cb-glass">
	<h2><?php esc_html_e( 'Invoices & payments', 'chatbotistic' ); ?></h2>
	<p class="cb-soft"><?php esc_html_e( 'All payments are processed securely via Stripe.', 'chatbotistic' ); ?></p>

	<?php if ( $cb_payments ) : ?>
		<div style="overflow-x:auto;margin-top:14px;">
		<table class="cb-table" style="width:100%;border-collapse:collapse;">
			<thead>
				<tr>
					<th style="text-align:left;"><?php esc_html_e( 'Date', 'chatbotistic' ); ?></th>
					<th style="text-align:left;"><?php esc_html_e( 'Amount', 'chatbotistic' ); ?></th>
					<th style="text-align:left;"><?php esc_html_e( 'Method', 'chatbotistic' ); ?></th>
					<th style="text-align:left;"><?php esc_html_e( 'Status', 'chatbotistic' ); ?></th>
					<th style="text-align:left;"><?php esc_html_e( 'Reference', 'chatbotistic' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $cb_payments as $p ) :
					$when   = ! empty( $p['paid_at'] ) ? $p['paid_at'] : ( $p['created_at'] ?? '' );
					$date   = $when ? date_i18n( get_option( 'date_format' ), strtotime( (string) $when ) ) : '—';
					$amount = number_format_i18n( (float) ( $p['amount'] ?? 0 ), 2 );
					$cur    = strtoupper( (string) ( $p['currency'] ?? 'USD' ) );
					$method = (string) ( $p['payment_method'] ?? '' );
					$status = (string) ( $p['status'] ?? '' );
					$ref    = (string) ( $p['gateway_transaction_id'] ?? '' );
					$ok     = in_array( $status, array( 'completed', 'paid', 'succeeded' ), true );
					?>
					<tr style="border-top:1px solid rgba(255,255,255,0.08);">
						<td><?php echo esc_html( $date ); ?></td>
						<td><?php echo esc_html( $cur . ' ' . $amount ); ?></td>
						<td><?php echo esc_html( $method ? ucwords( str_replace( '_', ' ', $method ) ) : '—' ); ?></td>
						<td><span class="cb-badge <?php echo $ok ? 'cb-badge--active' : ''; ?>"><?php echo esc_html( $status ? ucfirst( $status ) : '—' ); ?></span></td>
						<td><code style="font-size:11px;"><?php echo esc_html( $ref ?: '—' ); ?></code></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		</div>
	<?php else : ?>
		<p class="cb-dim" style="margin-top:14px;">
			<?php esc_html_e( 'No payments yet. Paid-plan invoices will appear here after your first charge.', 'chatbotistic' ); ?>
		</p>
	<?php endif; ?>
</div>
