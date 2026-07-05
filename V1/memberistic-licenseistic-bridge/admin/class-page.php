<?php
/**
 * Admin: plan caps mapping screen.
 *
 * @package WordPressistic\MLB
 */

namespace WordPressistic\MLB\Admin;

use WordPressistic\MLB\Caps;
use WordPressistic\MLB\Bridge;

defined( 'ABSPATH' ) || exit;

class Page {

	public function register(): void {
		add_action( 'admin_menu', [ $this, 'menu' ] );
		add_action( 'admin_init', [ $this, 'save' ] );
	}

	public function menu(): void {
		add_options_page(
			__( 'Memberistic → Licenseistic Bridge', 'memberistic-licenseistic-bridge' ),
			__( 'M → L Bridge', 'memberistic-licenseistic-bridge' ),
			'manage_options',
			'mlb-settings',
			[ $this, 'render' ]
		);
	}

	public function save(): void {
		if ( ! isset( $_POST['mlb_save'] ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Forbidden.' );
		}
		check_admin_referer( 'mlb_save' );

		$rows_in = isset( $_POST['caps'] ) && is_array( $_POST['caps'] ) ? wp_unslash( $_POST['caps'] ) : [];
		$caps    = [];
		foreach ( $rows_in as $plan_id => $row ) {
			$plan_id = (int) $plan_id;
			if ( ! $plan_id ) {
				continue;
			}
			$caps[ $plan_id ] = Caps::sanitize_row( (array) $row );
		}
		Caps::save( $caps );

		$product_id = isset( $_POST['mlb_product_id'] ) ? max( 0, (int) $_POST['mlb_product_id'] ) : 0;
		update_option( 'mlb_plan_product_id', $product_id );

		if ( isset( $_POST['mlb_sync_now'] ) ) {
			( new Bridge() )->sync_all_licenses();
		}

		wp_safe_redirect( add_query_arg( 'updated', '1', admin_url( 'options-general.php?page=mlb-settings' ) ) );
		exit;
	}

	public function render(): void {
		$plans      = Caps::memberistic_plans();
		$caps       = Caps::all();
		$by_slug    = Caps::default_by_slug();
		$product_id = (int) get_option( 'mlb_plan_product_id', 0 );

		if ( ! empty( $_GET['updated'] ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'memberistic-licenseistic-bridge' ) . '</p></div>';
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Memberistic → Licenseistic Bridge', 'memberistic-licenseistic-bridge' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'When a member activates a Memberistic plan, a Licenseistic license is auto-generated with the caps below and emailed to them. The widget plugin running on the customer\'s site validates the key and reads these caps to enforce widget / agent / domain limits.', 'memberistic-licenseistic-bridge' ); ?>
			</p>

			<?php if ( empty( $plans ) ) : ?>
				<div class="notice notice-warning"><p>
					<?php esc_html_e( 'No Memberistic plans yet. Activate Memberistic and create your plans first.', 'memberistic-licenseistic-bridge' ); ?>
				</p></div>
			<?php endif; ?>

			<form method="post" action="">
				<?php wp_nonce_field( 'mlb_save' ); ?>

				<h2><?php esc_html_e( 'Licenseistic product', 'memberistic-licenseistic-bridge' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Optional — attach every issued license to this Licenseistic product (used for analytics + updates). Leave blank to create unlinked licenses.', 'memberistic-licenseistic-bridge' ); ?></p>
				<p>
					<label for="mlb_product_id"><?php esc_html_e( 'Product ID:', 'memberistic-licenseistic-bridge' ); ?></label>
					<input type="number" id="mlb_product_id" name="mlb_product_id" value="<?php echo esc_attr( $product_id ); ?>" class="small-text" min="0">
				</p>

				<h2><?php esc_html_e( 'Per-plan caps', 'memberistic-licenseistic-bridge' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Use -1 or "unlimited" for no cap.', 'memberistic-licenseistic-bridge' ); ?></p>

				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Plan', 'memberistic-licenseistic-bridge' ); ?></th>
							<th><?php esc_html_e( 'Tier slug', 'memberistic-licenseistic-bridge' ); ?></th>
							<th><?php esc_html_e( 'Display name', 'memberistic-licenseistic-bridge' ); ?></th>
							<th><?php esc_html_e( 'Widgets', 'memberistic-licenseistic-bridge' ); ?></th>
							<th><?php esc_html_e( 'Agents', 'memberistic-licenseistic-bridge' ); ?></th>
							<th><?php esc_html_e( 'Domains', 'memberistic-licenseistic-bridge' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $plans as $plan ) :
							$pid  = (int) ( $plan['id'] ?? 0 );
							if ( ! $pid ) {
								continue;
							}
							$slug = sanitize_key( (string) ( $plan['slug'] ?? '' ) );
							$row  = $caps[ $pid ] ?? ( $by_slug[ $slug ] ?? Caps::FREE );
							?>
							<tr>
								<td>
									<strong><?php echo esc_html( (string) ( $plan['name'] ?? ( '#' . $pid ) ) ); ?></strong>
									<br><small><code>id=<?php echo esc_html( (string) $pid ); ?> slug=<?php echo esc_html( $slug ); ?></code></small>
								</td>
								<td>
									<input type="text" name="caps[<?php echo esc_attr( (string) $pid ); ?>][tier]" value="<?php echo esc_attr( (string) $row['tier'] ); ?>" class="regular-text">
								</td>
								<td>
									<input type="text" name="caps[<?php echo esc_attr( (string) $pid ); ?>][plan_name]" value="<?php echo esc_attr( (string) $row['plan_name'] ); ?>" class="regular-text">
								</td>
								<td>
									<input type="text" name="caps[<?php echo esc_attr( (string) $pid ); ?>][max_widgets]" value="<?php echo esc_attr( -1 === (int) $row['max_widgets'] ? 'unlimited' : (string) $row['max_widgets'] ); ?>" class="small-text">
								</td>
								<td>
									<input type="text" name="caps[<?php echo esc_attr( (string) $pid ); ?>][max_agents]" value="<?php echo esc_attr( -1 === (int) $row['max_agents'] ? 'unlimited' : (string) $row['max_agents'] ); ?>" class="small-text">
								</td>
								<td>
									<input type="text" name="caps[<?php echo esc_attr( (string) $pid ); ?>][max_domains]" value="<?php echo esc_attr( -1 === (int) $row['max_domains'] ? 'unlimited' : (string) $row['max_domains'] ); ?>" class="small-text">
								</td>
							</tr>
							<tr>
								<td colspan="6" style="background:#fafbff;padding:14px 16px;">
									<strong style="display:block;margin-bottom:8px;color:#475569;font-size:12px;letter-spacing:.05em;text-transform:uppercase;"><?php esc_html_e( 'White-label branding (delivered to the customer-side widget plugin)', 'memberistic-licenseistic-bridge' ); ?></strong>
									<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:10px;">
										<label><?php esc_html_e( 'Brand label', 'memberistic-licenseistic-bridge' ); ?>
											<input type="text" name="caps[<?php echo esc_attr( (string) $pid ); ?>][brand_label]" value="<?php echo esc_attr( (string) ( $row['brand_label'] ?? '' ) ); ?>" placeholder="WhatsApp Widget" style="width:100%;">
										</label>
										<label><?php esc_html_e( 'Brand tagline', 'memberistic-licenseistic-bridge' ); ?>
											<input type="text" name="caps[<?php echo esc_attr( (string) $pid ); ?>][brand_tagline]" value="<?php echo esc_attr( (string) ( $row['brand_tagline'] ?? '' ) ); ?>" style="width:100%;">
										</label>
										<label><?php esc_html_e( 'Brand homepage', 'memberistic-licenseistic-bridge' ); ?>
											<input type="url" name="caps[<?php echo esc_attr( (string) $pid ); ?>][brand_homepage]" value="<?php echo esc_attr( (string) ( $row['brand_homepage'] ?? '' ) ); ?>" placeholder="https://" style="width:100%;">
										</label>
										<label><?php esc_html_e( 'Support email', 'memberistic-licenseistic-bridge' ); ?>
											<input type="email" name="caps[<?php echo esc_attr( (string) $pid ); ?>][support_email]" value="<?php echo esc_attr( (string) ( $row['support_email'] ?? '' ) ); ?>" style="width:100%;">
										</label>
										<label><?php esc_html_e( 'Logo URL', 'memberistic-licenseistic-bridge' ); ?>
											<input type="url" name="caps[<?php echo esc_attr( (string) $pid ); ?>][logo_url]" value="<?php echo esc_attr( (string) ( $row['logo_url'] ?? '' ) ); ?>" style="width:100%;">
										</label>
										<?php if ( 'lifetime' === ( $row['tier'] ?? '' ) ) : ?>
											<label><?php esc_html_e( 'Custom license host (Lifetime only)', 'memberistic-licenseistic-bridge' ); ?>
												<input type="url" name="caps[<?php echo esc_attr( (string) $pid ); ?>][custom_domain]" value="<?php echo esc_attr( (string) ( $row['custom_domain'] ?? '' ) ); ?>" placeholder="https://customer-portal.example.com" style="width:100%;">
											</label>
										<?php endif; ?>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<p class="submit">
					<button type="submit" name="mlb_save" value="1" class="button button-primary"><?php esc_html_e( 'Save caps', 'memberistic-licenseistic-bridge' ); ?></button>
					<button type="submit" name="mlb_sync_now" value="1" class="button"><?php esc_html_e( 'Save + sync every license now', 'memberistic-licenseistic-bridge' ); ?></button>
				</p>
			</form>

			<h2><?php esc_html_e( 'How this works', 'memberistic-licenseistic-bridge' ); ?></h2>
			<ol>
				<li><?php esc_html_e( 'Member checks out / activates a plan in Memberistic.', 'memberistic-licenseistic-bridge' ); ?></li>
				<li><?php esc_html_e( 'This bridge listens to memberistic_membership_activated, creates a Licenseistic license with activation_limit = the plan\'s max_domains, expires_at = the membership renewal_date.', 'memberistic-licenseistic-bridge' ); ?></li>
				<li><?php esc_html_e( 'The license key is emailed to the member and stored on user meta mlb_license_key.', 'memberistic-licenseistic-bridge' ); ?></li>
				<li><?php esc_html_e( 'On any customer WP site, the Chatbotistic Widget plugin POSTs the key to /wp-json/licenseistic/v1/activate (registered by this bridge as a sugar alias of /license/activate). The response includes tier, plan_name, max_widgets, max_agents and max_domains.', 'memberistic-licenseistic-bridge' ); ?></li>
				<li><?php esc_html_e( 'On membership cancel / expire / suspend, the daily reconcile job flips the license to the matching state. Customer widgets stop validating on the next /heartbeat.', 'memberistic-licenseistic-bridge' ); ?></li>
			</ol>
		</div>
		<?php
	}
}
