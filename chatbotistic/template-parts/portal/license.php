<?php
/**
 * Portal view: Licenses — native Chatbotistic license management.
 *
 * Shows the customer's license key, plan entitlements, activation steps and a
 * status table, read directly from Licenseistic + the bridge. Falls back to the
 * [licenseistic_dashboard] shortcode only if the service classes are absent.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

$cb_uid       = get_current_user_id();
$cb_licenses  = function_exists( 'cb_user_licenses' ) ? cb_user_licenses( $cb_uid ) : array();
$cb_caps      = function_exists( 'cb_account_caps' ) ? cb_account_caps( $cb_uid ) : array();
$cb_key       = (string) get_user_meta( $cb_uid, 'mlb_license_key', true );
$cb_download   = (string) apply_filters( 'cb_widget_download_url', home_url( '/account/?download=widget' ) );
$cb_has_service = class_exists( '\WPistic_LSI_License_Service' );

/**
 * Resolve a product name from its ID.
 */
$cb_product_name = static function ( $product_id ) {
	if ( $product_id && class_exists( '\WPistic_LSI_Product_Service' ) ) {
		$p = \WPistic_LSI_Product_Service::get_product( (int) $product_id );
		if ( is_array( $p ) && ! empty( $p['product_name'] ) ) {
			return (string) $p['product_name'];
		}
	}
	return __( 'Chatbotistic Widget', 'chatbotistic' );
};
?>

<div class="cb-panel cb-glass">
	<h2><?php esc_html_e( 'Your license key', 'chatbotistic' ); ?></h2>
	<p class="cb-soft"><?php esc_html_e( 'Use this key to activate the Chatbotistic Widget plugin on your WordPress site.', 'chatbotistic' ); ?></p>

	<?php if ( $cb_key ) : ?>
		<div class="cb-field" style="margin-top:14px;max-width:520px;">
			<label for="cb-licensekey"><?php esc_html_e( 'License key', 'chatbotistic' ); ?></label>
			<input id="cb-licensekey" type="text" readonly value="<?php echo esc_attr( $cb_key ); ?>" onclick="this.select();" style="font-family:monospace;">
		</div>
	<?php elseif ( ! $cb_licenses ) : ?>
		<p class="cb-dim" style="margin-top:14px;">
			<?php esc_html_e( 'No license yet. Activate a plan and your license key will appear here automatically.', 'chatbotistic' ); ?>
		</p>
		<div style="margin-top:14px;"><?php cb_button( __( 'Choose a plan', 'chatbotistic' ), cb_plans_url(), 'primary', array( 'icon' => 'arrow-r' ) ); ?></div>
	<?php endif; ?>

	<?php if ( $cb_caps ) : ?>
		<h3 style="margin-top:22px;"><?php esc_html_e( 'What your plan unlocks', 'chatbotistic' ); ?></h3>
		<div class="cb-stats" style="margin-top:12px;">
			<div class="cb-stat"><div class="cb-stat__label"><?php esc_html_e( 'Widgets', 'chatbotistic' ); ?></div><div class="cb-stat__value"><?php echo esc_html( cb_cap_label( $cb_caps['max_widgets'] ?? 1 ) ); ?></div></div>
			<div class="cb-stat"><div class="cb-stat__label"><?php esc_html_e( 'WhatsApp agents', 'chatbotistic' ); ?></div><div class="cb-stat__value"><?php echo esc_html( cb_cap_label( $cb_caps['max_agents'] ?? 1 ) ); ?></div></div>
			<div class="cb-stat"><div class="cb-stat__label"><?php esc_html_e( 'Domains', 'chatbotistic' ); ?></div><div class="cb-stat__value"><?php echo esc_html( cb_cap_label( $cb_caps['max_domains'] ?? 1 ) ); ?></div></div>
		</div>
	<?php endif; ?>

	<?php if ( $cb_key ) : ?>
		<h3 style="margin-top:22px;"><?php esc_html_e( 'Activate in 4 steps', 'chatbotistic' ); ?></h3>
		<ol class="cb-checklist">
			<li><?php esc_html_e( 'Download & install the Chatbotistic Widget plugin on your WordPress site.', 'chatbotistic' ); ?></li>
			<li><?php esc_html_e( 'Open Chatbotistic → License in wp-admin.', 'chatbotistic' ); ?></li>
			<li><?php esc_html_e( 'Paste the key above and click Activate.', 'chatbotistic' ); ?></li>
			<li><?php esc_html_e( 'Your plan limits unlock automatically.', 'chatbotistic' ); ?></li>
		</ol>
		<div class="cb-portal__actions" style="margin-top:16px;">
			<?php
			cb_button( __( 'Download Widget plugin', 'chatbotistic' ), $cb_download, 'primary', array( 'icon' => 'arrow-r' ) );
			cb_button( __( 'Build / customize widgets', 'chatbotistic' ), cb_view_url( 'widgets' ), 'ghost' );
			?>
		</div>
	<?php endif; ?>
</div>

<?php if ( $cb_licenses ) : ?>
	<div class="cb-panel cb-glass" style="margin-top:18px;">
		<h3><?php esc_html_e( 'All your licenses', 'chatbotistic' ); ?></h3>
		<div style="overflow-x:auto;">
		<table class="cb-table" style="width:100%;margin-top:10px;border-collapse:collapse;">
			<thead>
				<tr>
					<th style="text-align:left;"><?php esc_html_e( 'Product', 'chatbotistic' ); ?></th>
					<th style="text-align:left;"><?php esc_html_e( 'Status', 'chatbotistic' ); ?></th>
					<th style="text-align:left;"><?php esc_html_e( 'Activations', 'chatbotistic' ); ?></th>
					<th style="text-align:left;"><?php esc_html_e( 'Expires', 'chatbotistic' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $cb_licenses as $row ) :
					$status = $cb_has_service ? \WPistic_LSI_License_Service::resolve_status( $row ) : (string) ( $row['status'] ?? '' );
					$limit  = (int) ( $row['activation_limit'] ?? 0 );
					$count  = (int) ( $row['activation_count'] ?? 0 );
					$exp    = ! empty( $row['expires_at'] ) ? date_i18n( get_option( 'date_format' ), strtotime( (string) $row['expires_at'] ) ) : __( 'Never', 'chatbotistic' );
					$ok     = 'active' === $status;
					?>
					<tr style="border-top:1px solid rgba(255,255,255,0.08);">
						<td><?php echo esc_html( $cb_product_name( $row['product_id'] ?? 0 ) ); ?></td>
						<td><span class="cb-badge <?php echo $ok ? 'cb-badge--active' : ''; ?>"><?php echo esc_html( ucfirst( $status ) ); ?></span></td>
						<td><?php echo esc_html( $count . ' / ' . ( $limit > 0 ? $limit : '∞' ) ); ?></td>
						<td><?php echo esc_html( $exp ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		</div>
	</div>
<?php elseif ( ! $cb_has_service && shortcode_exists( 'licenseistic_dashboard' ) ) : ?>
	<div class="cb-panel cb-glass" style="margin-top:18px;">
		<?php echo do_shortcode( '[licenseistic_dashboard]' ); ?>
	</div>
<?php endif; ?>
