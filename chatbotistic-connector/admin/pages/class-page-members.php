<?php
/**
 * Connector → Members page.
 *
 * Card-view list of every Memberistic membership with bulk edit
 * (change status, change plan, extend renewal, cancel), search and
 * status / plan filters, and a side-slide Quick Edit panel matching
 * the design reference. All data flows through Memberships_Repository.
 *
 * @package Chatbotistic\Connector
 */

namespace Chatbotistic\Connector;

defined( 'ABSPATH' ) || exit;

class Page_Members {

	public function render(): void {
		$counts = $this->status_counts();
		$plans  = $this->plans();
		?>
		<div class="wrap cbc-admin cbc-page-members">
			<h1 class="cbc-pg-h1"><?php esc_html_e( 'Members', 'chatbotistic-connector' ); ?></h1>
			<p class="cbc-pg-sub"><?php esc_html_e( 'Search, filter, and triage memberships without leaving the page.', 'chatbotistic-connector' ); ?></p>

			<div class="cbc-kpi-row">
				<?php
				$cards = [
					[ 'label' => __( 'Total members',     'chatbotistic-connector' ), 'value' => $counts['total']     ?? 0, 'tone' => 'neutral' ],
					[ 'label' => __( 'Active',            'chatbotistic-connector' ), 'value' => $counts['active']    ?? 0, 'tone' => 'good' ],
					[ 'label' => __( 'Pending',           'chatbotistic-connector' ), 'value' => $counts['pending']   ?? 0, 'tone' => 'warn' ],
					[ 'label' => __( 'Past due',          'chatbotistic-connector' ), 'value' => $counts['past_due']  ?? 0, 'tone' => 'bad' ],
					[ 'label' => __( 'Expired',           'chatbotistic-connector' ), 'value' => $counts['expired']   ?? 0, 'tone' => 'bad' ],
					[ 'label' => __( 'New this month',    'chatbotistic-connector' ), 'value' => $this->new_this_month(), 'tone' => 'good' ],
				];
				foreach ( $cards as $c ) :
					?>
					<div class="cbc-kpi cbc-kpi--<?php echo esc_attr( $c['tone'] ); ?>">
						<div class="cbc-kpi__label"><?php echo esc_html( $c['label'] ); ?></div>
						<div class="cbc-kpi__value"><?php echo esc_html( number_format_i18n( (int) $c['value'] ) ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="cbc-toolbar">
				<div class="cbc-toolbar__left">
					<input type="search" id="cbc-mb-search" class="cbc-input" placeholder="<?php esc_attr_e( 'Search by name, email, phone, or member ID…', 'chatbotistic-connector' ); ?>" />
					<select id="cbc-mb-status" class="cbc-input">
						<option value=""><?php esc_html_e( 'All statuses', 'chatbotistic-connector' ); ?></option>
						<option value="active"><?php esc_html_e( 'Active', 'chatbotistic-connector' ); ?></option>
						<option value="pending"><?php esc_html_e( 'Pending', 'chatbotistic-connector' ); ?></option>
						<option value="past_due"><?php esc_html_e( 'Past due', 'chatbotistic-connector' ); ?></option>
						<option value="expired"><?php esc_html_e( 'Expired', 'chatbotistic-connector' ); ?></option>
						<option value="cancelled"><?php esc_html_e( 'Cancelled', 'chatbotistic-connector' ); ?></option>
						<option value="paused"><?php esc_html_e( 'Paused', 'chatbotistic-connector' ); ?></option>
						<option value="trial"><?php esc_html_e( 'Trial', 'chatbotistic-connector' ); ?></option>
						<option value="comped"><?php esc_html_e( 'Comped', 'chatbotistic-connector' ); ?></option>
					</select>
					<select id="cbc-mb-plan" class="cbc-input">
						<option value=""><?php esc_html_e( 'All plans', 'chatbotistic-connector' ); ?></option>
						<?php foreach ( $plans as $p ) : ?>
							<option value="<?php echo esc_attr( (string) $p['id'] ); ?>"><?php echo esc_html( $p['name'] ); ?></option>
						<?php endforeach; ?>
					</select>
					<button class="cbc-btn cbc-btn-ghost" id="cbc-mb-refresh"><?php esc_html_e( 'Refresh', 'chatbotistic-connector' ); ?></button>
				</div>
				<div class="cbc-toolbar__right" id="cbc-mb-bulk" hidden>
					<span id="cbc-mb-bulk-count" class="cbc-pill">0 selected</span>
					<select id="cbc-mb-bulk-action" class="cbc-input">
						<option value=""><?php esc_html_e( 'Bulk action…', 'chatbotistic-connector' ); ?></option>
						<option value="activate"><?php esc_html_e( 'Activate', 'chatbotistic-connector' ); ?></option>
						<option value="cancel"><?php esc_html_e( 'Cancel', 'chatbotistic-connector' ); ?></option>
						<option value="pause"><?php esc_html_e( 'Pause', 'chatbotistic-connector' ); ?></option>
						<option value="renew_month"><?php esc_html_e( 'Extend +1 month', 'chatbotistic-connector' ); ?></option>
						<option value="renew_year"><?php esc_html_e( 'Extend +1 year', 'chatbotistic-connector' ); ?></option>
						<option value="resend_email"><?php esc_html_e( 'Resend welcome email', 'chatbotistic-connector' ); ?></option>
					</select>
					<select id="cbc-mb-bulk-plan" class="cbc-input" style="display:none;">
						<option value=""><?php esc_html_e( 'Move to plan…', 'chatbotistic-connector' ); ?></option>
						<?php foreach ( $plans as $p ) : ?>
							<option value="<?php echo esc_attr( (string) $p['id'] ); ?>"><?php echo esc_html( $p['name'] ); ?></option>
						<?php endforeach; ?>
					</select>
					<button class="cbc-btn cbc-btn-primary" id="cbc-mb-bulk-apply"><?php esc_html_e( 'Apply', 'chatbotistic-connector' ); ?></button>
				</div>
			</div>

			<div class="cbc-notice" id="cbc-admin-notice" hidden></div>

			<div class="cbc-table-wrap">
				<table class="cbc-table" id="cbc-mb-table">
					<thead>
						<tr>
							<th class="cbc-w-check"><input type="checkbox" id="cbc-mb-all" /></th>
							<th><?php esc_html_e( 'Member ID', 'chatbotistic-connector' ); ?></th>
							<th><?php esc_html_e( 'Primary member', 'chatbotistic-connector' ); ?></th>
							<th><?php esc_html_e( 'Plan', 'chatbotistic-connector' ); ?></th>
							<th><?php esc_html_e( 'Status', 'chatbotistic-connector' ); ?></th>
							<th><?php esc_html_e( 'Billing', 'chatbotistic-connector' ); ?></th>
							<th><?php esc_html_e( 'Renewal', 'chatbotistic-connector' ); ?></th>
							<th class="cbc-w-action"></th>
						</tr>
					</thead>
					<tbody>
						<tr><td colspan="8" class="cbc-loading"><?php esc_html_e( 'Loading members…', 'chatbotistic-connector' ); ?></td></tr>
					</tbody>
				</table>
				<div class="cbc-pager" id="cbc-mb-pager"></div>
			</div>
		</div>

		<!-- Side-slide quick-edit drawer -->
		<div class="cbc-drawer" id="cbc-mb-drawer" hidden>
			<div class="cbc-drawer__shade" data-cbc-close="drawer"></div>
			<aside class="cbc-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="cbc-mb-drawer-title">
				<header class="cbc-drawer__head">
					<div>
						<h2 id="cbc-mb-drawer-title" class="cbc-drawer__name">—</h2>
						<div class="cbc-drawer__meta">
							<span class="cbc-pill" id="cbc-mb-drawer-status">—</span>
							<span id="cbc-mb-drawer-plan">—</span>
						</div>
					</div>
					<div class="cbc-drawer__actions">
						<button class="cbc-btn cbc-btn-ghost" id="cbc-mb-drawer-edit"><?php esc_html_e( 'Edit basics', 'chatbotistic-connector' ); ?></button>
						<button class="cbc-btn cbc-btn-ghost" id="cbc-mb-drawer-renew"><?php esc_html_e( 'Renew', 'chatbotistic-connector' ); ?></button>
						<button class="cbc-btn cbc-btn-danger" id="cbc-mb-drawer-cancel"><?php esc_html_e( 'Cancel', 'chatbotistic-connector' ); ?></button>
						<button class="cbc-drawer__x" data-cbc-close="drawer" aria-label="<?php esc_attr_e( 'Close', 'chatbotistic-connector' ); ?>">&times;</button>
					</div>
				</header>
				<div class="cbc-drawer__body" id="cbc-mb-drawer-body">
					<div class="cbc-loading"><?php esc_html_e( 'Loading…', 'chatbotistic-connector' ); ?></div>
				</div>
			</aside>
		</div>
		<?php
	}

	private function status_counts(): array {
		$repo = '\WordPressistic\Memberistic\Database\Memberships_Repository';
		return ( class_exists( $repo ) && method_exists( $repo, 'counts_by_status' ) ) ? $repo::counts_by_status() : array();
	}

	private function new_this_month(): int {
		$repo = '\WordPressistic\Memberistic\Database\Memberships_Repository';
		return ( class_exists( $repo ) && method_exists( $repo, 'count_new_this_month' ) ) ? (int) $repo::count_new_this_month() : 0;
	}

	private function plans(): array {
		$repo = '\WordPressistic\Memberistic\Database\Plans_Repository';
		if ( ! class_exists( $repo ) || ! method_exists( $repo, 'get_all' ) ) {
			return array();
		}
		$rows = (array) $repo::get_all( array( 'status' => 'active' ) );
		return $rows;
	}
}
