<?php
/**
 * Connector → Licenses page.
 *
 * Lists every Licenseistic license, bulk revoke / suspend / re-activate,
 * regenerate a key for one row. Reads via WPistic_LSI_License_Service.
 *
 * @package Chatbotistic\Connector
 */

namespace Chatbotistic\Connector;

defined( 'ABSPATH' ) || exit;

class Page_Licenses {

	public function render(): void {
		$counts = $this->status_counts();
		?>
		<div class="wrap cbc-admin cbc-page-licenses">
			<h1 class="cbc-pg-h1"><?php esc_html_e( 'Licenses', 'chatbotistic-connector' ); ?></h1>
			<p class="cbc-pg-sub"><?php esc_html_e( 'Every license key issued by Licenseistic (auto-issued by the M → L Bridge on Memberistic activation).', 'chatbotistic-connector' ); ?></p>

			<div class="cbc-kpi-row">
				<div class="cbc-kpi cbc-kpi--neutral"><div class="cbc-kpi__label"><?php esc_html_e( 'Total', 'chatbotistic-connector' ); ?></div><div class="cbc-kpi__value"><?php echo esc_html( number_format_i18n( (int) ( $counts['total'] ?? 0 ) ) ); ?></div></div>
				<div class="cbc-kpi cbc-kpi--good"><div class="cbc-kpi__label"><?php esc_html_e( 'Active', 'chatbotistic-connector' ); ?></div><div class="cbc-kpi__value"><?php echo esc_html( number_format_i18n( (int) ( $counts['active'] ?? 0 ) ) ); ?></div></div>
				<div class="cbc-kpi cbc-kpi--warn"><div class="cbc-kpi__label"><?php esc_html_e( 'Suspended', 'chatbotistic-connector' ); ?></div><div class="cbc-kpi__value"><?php echo esc_html( number_format_i18n( (int) ( $counts['suspended'] ?? 0 ) ) ); ?></div></div>
				<div class="cbc-kpi cbc-kpi--bad"><div class="cbc-kpi__label"><?php esc_html_e( 'Revoked', 'chatbotistic-connector' ); ?></div><div class="cbc-kpi__value"><?php echo esc_html( number_format_i18n( (int) ( $counts['revoked'] ?? 0 ) ) ); ?></div></div>
				<div class="cbc-kpi cbc-kpi--bad"><div class="cbc-kpi__label"><?php esc_html_e( 'Expired', 'chatbotistic-connector' ); ?></div><div class="cbc-kpi__value"><?php echo esc_html( number_format_i18n( (int) ( $counts['expired'] ?? 0 ) ) ); ?></div></div>
			</div>

			<div class="cbc-toolbar">
				<div class="cbc-toolbar__left">
					<input type="search" id="cbc-lc-search" class="cbc-input" placeholder="<?php esc_attr_e( 'Search by email, key, or label…', 'chatbotistic-connector' ); ?>" />
					<select id="cbc-lc-status" class="cbc-input">
						<option value=""><?php esc_html_e( 'All statuses', 'chatbotistic-connector' ); ?></option>
						<option value="active"><?php esc_html_e( 'Active', 'chatbotistic-connector' ); ?></option>
						<option value="suspended"><?php esc_html_e( 'Suspended', 'chatbotistic-connector' ); ?></option>
						<option value="revoked"><?php esc_html_e( 'Revoked', 'chatbotistic-connector' ); ?></option>
						<option value="expired"><?php esc_html_e( 'Expired', 'chatbotistic-connector' ); ?></option>
					</select>
					<button class="cbc-btn cbc-btn-ghost" id="cbc-lc-refresh"><?php esc_html_e( 'Refresh', 'chatbotistic-connector' ); ?></button>
				</div>
				<div class="cbc-toolbar__right" id="cbc-lc-bulk" hidden>
					<span id="cbc-lc-bulk-count" class="cbc-pill">0 selected</span>
					<select id="cbc-lc-bulk-action" class="cbc-input">
						<option value=""><?php esc_html_e( 'Bulk action…', 'chatbotistic-connector' ); ?></option>
						<option value="activate"><?php esc_html_e( 'Re-activate', 'chatbotistic-connector' ); ?></option>
						<option value="suspend"><?php esc_html_e( 'Suspend', 'chatbotistic-connector' ); ?></option>
						<option value="revoke"><?php esc_html_e( 'Revoke', 'chatbotistic-connector' ); ?></option>
					</select>
					<button class="cbc-btn cbc-btn-primary" id="cbc-lc-bulk-apply"><?php esc_html_e( 'Apply', 'chatbotistic-connector' ); ?></button>
				</div>
			</div>

			<div class="cbc-notice" id="cbc-admin-notice-lc" hidden></div>

			<div class="cbc-table-wrap">
				<table class="cbc-table" id="cbc-lc-table">
					<thead>
						<tr>
							<th class="cbc-w-check"><input type="checkbox" id="cbc-lc-all" /></th>
							<th><?php esc_html_e( 'Customer', 'chatbotistic-connector' ); ?></th>
							<th><?php esc_html_e( 'Key', 'chatbotistic-connector' ); ?></th>
							<th><?php esc_html_e( 'Tier', 'chatbotistic-connector' ); ?></th>
							<th><?php esc_html_e( 'Activations', 'chatbotistic-connector' ); ?></th>
							<th><?php esc_html_e( 'Status', 'chatbotistic-connector' ); ?></th>
							<th><?php esc_html_e( 'Expires', 'chatbotistic-connector' ); ?></th>
							<th class="cbc-w-action"></th>
						</tr>
					</thead>
					<tbody><tr><td colspan="8" class="cbc-loading"><?php esc_html_e( 'Loading licenses…', 'chatbotistic-connector' ); ?></td></tr></tbody>
				</table>
				<div class="cbc-pager" id="cbc-lc-pager"></div>
			</div>
		</div>
		<?php
	}

	private function status_counts(): array {
		if ( ! class_exists( '\WPistic_LSI_License_Service' ) ) {
			return array();
		}
		$out = array( 'total' => 0, 'active' => 0, 'suspended' => 0, 'revoked' => 0, 'expired' => 0 );
		$rows = (array) \WPistic_LSI_License_Service::get_licenses( array( 'per_page' => 500 ) );
		foreach ( $rows as $r ) {
			$s = \WPistic_LSI_License_Service::resolve_status( $r );
			$out['total']++;
			if ( isset( $out[ $s ] ) ) {
				$out[ $s ]++;
			}
		}
		return $out;
	}
}
