<?php
/**
 * Portal view: Overview.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/** @var array $cb_m Membership data from cb_membership(). */
/** @var bool  $cb_active Whether the membership is active. */

$cb_days     = $cb_m['days_left'];
$cb_licenses = function_exists( 'cb_user_licenses' ) ? cb_user_licenses() : array();
$cb_lic_key  = (string) get_user_meta( get_current_user_id(), 'mlb_license_key', true );
?>

<?php if ( ! $cb_active ) : ?>
	<div class="cb-panel cb-glass cb-paywall">
		<h2><?php esc_html_e( 'Your plan is not active yet', 'chatbotistic' ); ?></h2>
		<p><?php esc_html_e( 'Activate a plan to unlock your Tools, WhatsApp Agent, API access, and member resources.', 'chatbotistic' ); ?></p>
		<?php cb_button( __( 'See plans', 'chatbotistic' ), cb_plans_url(), 'primary', array( 'size' => 'lg', 'icon' => 'arrow-r' ) ); ?>
	</div>
<?php endif; ?>

<div class="cb-stats">
	<div class="cb-stat">
		<div class="cb-stat__label"><?php esc_html_e( 'Current plan', 'chatbotistic' ); ?></div>
		<div class="cb-stat__value"><?php echo esc_html( $cb_active ? ( $cb_m['plan_name'] ?: __( 'Active', 'chatbotistic' ) ) : '—' ); ?></div>
		<a class="cb-stat__link" href="<?php echo cb_view_url( 'subscription' ); ?>"><?php esc_html_e( 'Manage', 'chatbotistic' ); ?> &rarr;</a>
	</div>
	<div class="cb-stat">
		<div class="cb-stat__label"><?php esc_html_e( 'Renews in', 'chatbotistic' ); ?></div>
		<div class="cb-stat__value"><?php echo null !== $cb_days ? esc_html( $cb_days . ' ' . _n( 'day', 'days', (int) $cb_days, 'chatbotistic' ) ) : '—'; ?></div>
		<a class="cb-stat__link" href="<?php echo cb_view_url( 'invoices' ); ?>"><?php esc_html_e( 'Invoices', 'chatbotistic' ); ?> &rarr;</a>
	</div>
	<div class="cb-stat">
		<div class="cb-stat__label"><?php esc_html_e( 'Tools status', 'chatbotistic' ); ?></div>
		<div class="cb-stat__value <?php echo $cb_active ? 'cb-stat__value--ok' : ''; ?>"><?php echo $cb_active ? esc_html__( 'Active', 'chatbotistic' ) : esc_html__( 'Locked', 'chatbotistic' ); ?></div>
		<a class="cb-stat__link" href="<?php echo cb_view_url( 'tools' ); ?>"><?php esc_html_e( 'Open', 'chatbotistic' ); ?> &rarr;</a>
	</div>
	<div class="cb-stat">
		<div class="cb-stat__label"><?php esc_html_e( 'License', 'chatbotistic' ); ?></div>
		<div class="cb-stat__value <?php echo $cb_lic_key ? 'cb-stat__value--ok' : ''; ?>">
			<?php echo $cb_lic_key ? esc_html__( 'Issued', 'chatbotistic' ) : ( $cb_active ? esc_html__( 'Pending', 'chatbotistic' ) : '—' ); ?>
		</div>
		<a class="cb-stat__link" href="<?php echo cb_view_url( 'licenses' ); ?>"><?php esc_html_e( 'View key', 'chatbotistic' ); ?> &rarr;</a>
	</div>
</div>

<div class="cb-portal__row">
	<div class="cb-panel cb-glass">
		<h2><?php esc_html_e( 'Get started in 3 steps', 'chatbotistic' ); ?></h2>
		<ol class="cb-checklist">
			<li><strong><?php esc_html_e( 'Connect WhatsApp', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'Open Tools and link your number or provision a new line.', 'chatbotistic' ); ?></li>
			<li><strong><?php esc_html_e( 'Build your first agent', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'Use a template or start from scratch in five minutes.', 'chatbotistic' ); ?></li>
			<li><strong><?php esc_html_e( 'Embed on your site', 'chatbotistic' ); ?></strong> — <?php esc_html_e( 'Copy the widget snippet, or install the WordPress plugin.', 'chatbotistic' ); ?></li>
		</ol>
		<div style="margin-top:18px;">
			<?php cb_button( __( 'Open Tools', 'chatbotistic' ), cb_view_url( 'tools' ), 'primary', array( 'icon' => 'arrow-r' ) ); ?>
		</div>
	</div>

	<div class="cb-panel cb-glass">
		<h3><?php esc_html_e( 'Member resources', 'chatbotistic' ); ?></h3>
		<ul class="cb-linklist">
			<li><a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>"><?php cb_icon( 'page', 16 ); ?> <?php esc_html_e( 'Documentation', 'chatbotistic' ); ?></a></li>
			<li><a href="<?php echo esc_url( home_url( '/tutorials/' ) ); ?>"><?php cb_icon( 'play', 16 ); ?> <?php esc_html_e( 'Video tutorials', 'chatbotistic' ); ?></a></li>
			<li><a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php cb_icon( 'spark', 16 ); ?> <?php esc_html_e( 'Automation playbook', 'chatbotistic' ); ?></a></li>
			<li><a href="<?php echo cb_view_url( 'support' ); ?>"><?php cb_icon( 'chat', 16 ); ?> <?php esc_html_e( 'Contact support', 'chatbotistic' ); ?></a></li>
		</ul>
	</div>
</div>
