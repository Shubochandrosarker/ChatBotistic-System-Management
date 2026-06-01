<?php
/**
 * Connector → Payments page.
 *
 * KPI cards matching the design reference (Lifetime revenue, this month,
 * new-member, renewal, failed, visible on page) + filterable list with
 * bulk export and bulk refund.
 *
 * @package Chatbotistic\Connector
 */

namespace Chatbotistic\Connector;

defined( 'ABSPATH' ) || exit;

class Page_Payments {

	public function render(): void {
		$stats = $this->stats();
		?>
		<div class="wrap cbc-admin cbc-page-payments">
			<h1 class="cbc-pg-h1"><?php esc_html_e( 'Payments', 'chatbotistic-connector' ); ?></h1>
			<p class="cbc-pg-sub"><?php esc_html_e( 'Search and audit every recorded payment across members, gateways, and dates.', 'chatbotistic-connector' ); ?>
				<button class="cbc-btn cbc-btn-ghost" id="cbc-pm-export" style="float:right;"><?php esc_html_e( 'Export CSV', 'chatbotistic-connector' ); ?></button>
			</p>

			<div class="cbc-kpi-row">
				<div class="cbc-kpi cbc-kpi--neutral">
					<div class="cbc-kpi__label"><?php esc_html_e( 'Lifetime revenue', 'chatbotistic-connector' ); ?></div>
					<div class="cbc-kpi__value">$<?php echo esc_html( number_format_i18n( (float) ( $stats['revenue_total'] ?? 0 ), 2 ) ); ?></div>
					<div class="cbc-kpi__sub"><?php printf( esc_html__( '%s completed payments', 'chatbotistic-connector' ), esc_html( number_format_i18n( (int) ( $stats['completed_payments'] ?? 0 ) ) ) ); ?></div>
				</div>
				<div class="cbc-kpi cbc-kpi--good">
					<div class="cbc-kpi__label"><?php esc_html_e( 'Revenue this month', 'chatbotistic-connector' ); ?></div>
					<div class="cbc-kpi__value">$<?php echo esc_html( number_format_i18n( (float) ( $stats['revenue_this_month'] ?? 0 ), 2 ) ); ?></div>
					<div class="cbc-kpi__sub"><?php printf( esc_html__( 'vs $%s last month', 'chatbotistic-connector' ), esc_html( number_format_i18n( (float) ( $stats['revenue_prev_month'] ?? 0 ), 2 ) ) ); ?>
						<span class="cbc-pill cbc-pill--<?php echo (float) ( $stats['revenue_growth_pct'] ?? 0 ) >= 0 ? 'good' : 'bad'; ?>"><?php echo esc_html( ( (float) ( $stats['revenue_growth_pct'] ?? 0 ) >= 0 ? '▲ ' : '▼ ' ) . number_format_i18n( (float) ( $stats['revenue_growth_pct'] ?? 0 ), 1 ) . '%' ); ?></span>
					</div>
				</div>
				<div class="cbc-kpi cbc-kpi--neutral">
					<div class="cbc-kpi__label"><?php esc_html_e( 'New-member payments', 'chatbotistic-connector' ); ?></div>
					<div class="cbc-kpi__value"><?php echo esc_html( number_format_i18n( (int) ( $stats['new_member_payments'] ?? 0 ) ) ); ?></div>
				</div>
				<div class="cbc-kpi cbc-kpi--neutral">
					<div class="cbc-kpi__label"><?php esc_html_e( 'Renewal payments', 'chatbotistic-connector' ); ?></div>
					<div class="cbc-kpi__value"><?php echo esc_html( number_format_i18n( (int) ( $stats['renewal_payments'] ?? 0 ) ) ); ?></div>
				</div>
				<div class="cbc-kpi cbc-kpi--bad">
					<div class="cbc-kpi__label"><?php esc_html_e( 'Failed payments', 'chatbotistic-connector' ); ?></div>
					<div class="cbc-kpi__value"><?php echo esc_html( number_format_i18n( (int) ( $stats['failed_payments'] ?? 0 ) ) ); ?></div>
				</div>
				<div class="cbc-kpi cbc-kpi--neutral">
					<div class="cbc-kpi__label"><?php esc_html_e( 'Refunded', 'chatbotistic-connector' ); ?></div>
					<div class="cbc-kpi__value"><?php echo esc_html( number_format_i18n( (int) ( $stats['refunded_payments'] ?? 0 ) ) ); ?></div>
				</div>
			</div>

			<div class="cbc-toolbar">
				<div class="cbc-toolbar__left">
					<input type="search" id="cbc-pm-search" class="cbc-input" placeholder="<?php esc_attr_e( 'Search by member, ID, or transaction reference…', 'chatbotistic-connector' ); ?>" />
					<select id="cbc-pm-status" class="cbc-input">
						<option value=""><?php esc_html_e( 'All statuses', 'chatbotistic-connector' ); ?></option>
						<option value="completed"><?php esc_html_e( 'Completed', 'chatbotistic-connector' ); ?></option>
						<option value="failed"><?php esc_html_e( 'Failed', 'chatbotistic-connector' ); ?></option>
						<option value="refunded"><?php esc_html_e( 'Refunded', 'chatbotistic-connector' ); ?></option>
						<option value="pending"><?php esc_html_e( 'Pending', 'chatbotistic-connector' ); ?></option>
					</select>
					<input type="date" id="cbc-pm-from" class="cbc-input" />
					<input type="date" id="cbc-pm-to" class="cbc-input" />
					<button class="cbc-btn cbc-btn-ghost" id="cbc-pm-refresh"><?php esc_html_e( 'Refresh', 'chatbotistic-connector' ); ?></button>
				</div>
				<div class="cbc-toolbar__right" id="cbc-pm-bulk" hidden>
					<span id="cbc-pm-bulk-count" class="cbc-pill">0 selected</span>
					<select id="cbc-pm-bulk-action" class="cbc-input">
						<option value=""><?php esc_html_e( 'Bulk action…', 'chatbotistic-connector' ); ?></option>
						<option value="refund"><?php esc_html_e( 'Mark refunded', 'chatbotistic-connector' ); ?></option>
						<option value="export"><?php esc_html_e( 'Export selected', 'chatbotistic-connector' ); ?></option>
					</select>
					<button class="cbc-btn cbc-btn-primary" id="cbc-pm-bulk-apply"><?php esc_html_e( 'Apply', 'chatbotistic-connector' ); ?></button>
				</div>
			</div>

			<div class="cbc-notice" id="cbc-admin-notice-pay" hidden></div>

			<div class="cbc-table-wrap">
				<table class="cbc-table" id="cbc-pm-table">
					<thead>
						<tr>
							<th class="cbc-w-check"><input type="checkbox" id="cbc-pm-all" /></th>
							<th><?php esc_html_e( 'Member ID', 'chatbotistic-connector' ); ?></th>
							<th><?php esc_html_e( 'Primary member', 'chatbotistic-connector' ); ?></th>
							<th><?php esc_html_e( 'Amount', 'chatbotistic-connector' ); ?></th>
							<th><?php esc_html_e( 'Method', 'chatbotistic-connector' ); ?></th>
							<th><?php esc_html_e( 'Gateway', 'chatbotistic-connector' ); ?></th>
							<th><?php esc_html_e( 'Status', 'chatbotistic-connector' ); ?></th>
							<th><?php esc_html_e( 'Paid', 'chatbotistic-connector' ); ?></th>
							<th class="cbc-w-action"></th>
						</tr>
					</thead>
					<tbody><tr><td colspan="9" class="cbc-loading"><?php esc_html_e( 'Loading payments…', 'chatbotistic-connector' ); ?></td></tr></tbody>
				</table>
				<div class="cbc-pager" id="cbc-pm-pager"></div>
			</div>
		</div>
		<?php
	}

	private function stats(): array {
		$repo = '\WordPressistic\Memberistic\Database\Payments_Repository';
		return ( class_exists( $repo ) && method_exists( $repo, 'stats_summary' ) ) ? (array) $repo::stats_summary() : array();
	}
}
