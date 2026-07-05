<?php
/**
 * Connector → Plans page — card view replacing Memberistic's table layout.
 *
 * @package Chatbotistic\Connector
 */

namespace Chatbotistic\Connector;

defined( 'ABSPATH' ) || exit;

class Page_Plans {

	public function render(): void {
		$plans = $this->plans();
		$by_plan = $this->member_counts_per_plan();
		?>
		<div class="wrap cbc-admin cbc-page-plans">
			<h1 class="cbc-pg-h1"><?php esc_html_e( 'Plans', 'chatbotistic-connector' ); ?>
				<a class="cbc-btn cbc-btn-primary" style="float:right;" href="<?php echo esc_url( admin_url( 'admin.php?page=memberistic-plans' ) ); ?>"><?php esc_html_e( 'Add / edit in Memberistic', 'chatbotistic-connector' ); ?></a>
			</h1>
			<p class="cbc-pg-sub"><?php esc_html_e( 'Live pricing tiers and member counts for each plan. Edits happen in Memberistic; this is a fast read-only view.', 'chatbotistic-connector' ); ?></p>

			<div class="cbc-plan-grid">
				<?php foreach ( $plans as $p ) :
					$pid       = (int) ( $p['id'] ?? 0 );
					$counts    = $by_plan[ $pid ] ?? array( 'total' => 0, 'active' => 0, 'other' => 0 );
					$benefits  = $this->decode_benefits( $p['benefits'] ?? '' );
					$is_featured = (int) ( $p['is_featured'] ?? 0 ) === 1;
					$caps      = $this->caps_for( $pid );
					?>
					<article class="cbc-plan-card<?php echo $is_featured ? ' is-featured' : ''; ?>">
						<?php if ( $is_featured ) : ?><div class="cbc-plan-card__badge"><?php esc_html_e( 'FEATURED', 'chatbotistic-connector' ); ?></div><?php endif; ?>
						<header class="cbc-plan-card__head">
							<h2 class="cbc-plan-card__name"><?php echo esc_html( (string) ( $p['name'] ?? '—' ) ); ?></h2>
							<span class="cbc-pill cbc-pill--good"><?php echo esc_html( strtoupper( (string) ( $p['status'] ?? 'active' ) ) ); ?></span>
						</header>
						<code class="cbc-plan-card__slug"><?php echo esc_html( (string) ( $p['slug'] ?? '' ) ); ?></code>
						<p class="cbc-plan-card__desc"><?php echo esc_html( (string) ( $p['description'] ?? '' ) ); ?></p>

						<div class="cbc-plan-card__prices">
							<div><div class="cbc-plan-card__price-label"><?php esc_html_e( 'MONTHLY', 'chatbotistic-connector' ); ?></div><div class="cbc-plan-card__price">$<?php echo esc_html( number_format_i18n( (float) ( $p['monthly_price'] ?? 0 ), 2 ) ); ?></div></div>
							<div><div class="cbc-plan-card__price-label"><?php esc_html_e( 'ANNUAL', 'chatbotistic-connector' ); ?></div><div class="cbc-plan-card__price">$<?php echo esc_html( number_format_i18n( (float) ( $p['annual_price'] ?? 0 ), 2 ) ); ?></div></div>
						</div>

						<?php if ( $caps ) : ?>
							<div class="cbc-plan-card__caps">
								<span class="cbc-pill"><?php printf( esc_html__( '%s widgets', 'chatbotistic-connector' ), $caps['max_widgets'] < 0 ? '∞' : esc_html( (string) $caps['max_widgets'] ) ); ?></span>
								<span class="cbc-pill"><?php printf( esc_html__( '%s agents', 'chatbotistic-connector' ), $caps['max_agents'] < 0 ? '∞' : esc_html( (string) $caps['max_agents'] ) ); ?></span>
								<span class="cbc-pill"><?php printf( esc_html__( '%s domains', 'chatbotistic-connector' ), $caps['max_domains'] < 0 ? '∞' : esc_html( (string) $caps['max_domains'] ) ); ?></span>
							</div>
						<?php endif; ?>

						<?php if ( $benefits ) : ?>
							<ul class="cbc-plan-card__benefits">
								<?php foreach ( array_slice( $benefits, 0, 6 ) as $b ) : ?>
									<li>✓ <?php echo esc_html( (string) $b ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>

						<footer class="cbc-plan-card__foot">
							<div><div class="cbc-plan-card__stat-label"><?php esc_html_e( 'TOTAL', 'chatbotistic-connector' ); ?></div><div class="cbc-plan-card__stat"><?php echo esc_html( number_format_i18n( (int) $counts['total'] ) ); ?></div></div>
							<div><div class="cbc-plan-card__stat-label"><?php esc_html_e( 'ACTIVE', 'chatbotistic-connector' ); ?></div><div class="cbc-plan-card__stat cbc-plan-card__stat--good"><?php echo esc_html( number_format_i18n( (int) $counts['active'] ) ); ?></div></div>
							<div><div class="cbc-plan-card__stat-label"><?php esc_html_e( 'OTHER', 'chatbotistic-connector' ); ?></div><div class="cbc-plan-card__stat cbc-plan-card__stat--warn"><?php echo esc_html( number_format_i18n( max( 0, (int) $counts['total'] - (int) $counts['active'] ) ) ); ?></div></div>
						</footer>
					</article>
				<?php endforeach; ?>
				<?php if ( empty( $plans ) ) : ?>
					<div class="cbc-empty"><?php esc_html_e( 'No plans yet. Run "Reset to Chatbotistic defaults" on the Profile page to seed the 4 plans.', 'chatbotistic-connector' ); ?></div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	private function plans(): array {
		$repo = '\WordPressistic\Memberistic\Database\Plans_Repository';
		return ( class_exists( $repo ) && method_exists( $repo, 'get_all' ) ) ? (array) $repo::get_all() : array();
	}

	private function member_counts_per_plan(): array {
		$repo = '\WordPressistic\Memberistic\Database\Memberships_Repository';
		if ( ! class_exists( $repo ) || ! method_exists( $repo, 'counts_per_plan' ) ) {
			return array();
		}
		$out = array();
		foreach ( (array) $repo::counts_per_plan() as $row ) {
			$pid = (int) ( $row['plan_id'] ?? 0 );
			$out[ $pid ] = $row;
		}
		return $out;
	}

	private function decode_benefits( $raw ): array {
		if ( is_array( $raw ) ) return $raw;
		$dec = json_decode( (string) $raw, true );
		return is_array( $dec ) ? $dec : array();
	}

	private function caps_for( int $plan_id ): array {
		$caps = (array) get_option( 'mlb_plan_caps', array() );
		return isset( $caps[ $plan_id ] ) ? (array) $caps[ $plan_id ] : array();
	}
}
