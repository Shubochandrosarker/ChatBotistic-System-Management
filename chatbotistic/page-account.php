<?php
/**
 * Template Name: Account Portal
 *
 * Unified member portal. Sidebar + ?view= tabs under one branded shell.
 * Membership status comes from Memberistic (cb_membership). Plugin UIs are
 * embedded as shortcodes: [chatbotistic_dashboard], [memberistic_account],
 * [memberistic_payment_history], [licenseistic_dashboard].
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( home_url( '/login/' ) );
	exit;
}

$cb_user   = wp_get_current_user();
$cb_uid    = (int) $cb_user->ID;
$cb_base   = get_permalink();
$cb_m      = cb_membership( $cb_uid );
$cb_active = ! empty( $cb_m['active'] );

$cb_views = array(
	'overview'     => array( 'home',  __( 'Overview', 'chatbotistic' ) ),
	'tools'        => array( 'bolt',  __( 'Tools', 'chatbotistic' ) ),
	'subscription' => array( 'star',  __( 'Subscription', 'chatbotistic' ) ),
	'invoices'     => array( 'card',  __( 'Invoices', 'chatbotistic' ) ),
	'licenses'     => array( 'key',   __( 'Licenses', 'chatbotistic' ) ),
	'profile'      => array( 'users', __( 'Profile', 'chatbotistic' ) ),
	'support'      => array( 'chat',  __( 'Support', 'chatbotistic' ) ),
);

$cb_view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : 'overview'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( ! isset( $cb_views[ $cb_view ] ) ) {
	$cb_view = 'overview';
}

/**
 * Build a portal view URL.
 *
 * @param string $view View key.
 * @return string
 */
function cb_view_url( $view ) {
	return esc_url( add_query_arg( 'view', $view, get_permalink() ) );
}

get_header();
?>

<section class="cb-portal">
	<div class="cb-container cb-portal__shell">

		<aside class="cb-portal__sidebar" aria-label="<?php esc_attr_e( 'Account navigation', 'chatbotistic' ); ?>">
			<div class="cb-portal__user">
				<?php echo get_avatar( $cb_uid, 48 ); ?>
				<div>
					<div class="cb-portal__user-name"><?php echo esc_html( $cb_user->display_name ); ?></div>
					<div class="cb-portal__user-meta">
						<?php if ( $cb_active ) : ?>
							<span class="cb-badge cb-badge--active"><?php echo esc_html( $cb_m['plan_name'] ?: __( 'Active', 'chatbotistic' ) ); ?></span>
						<?php else : ?>
							<span class="cb-badge cb-badge--muted"><?php esc_html_e( 'No active plan', 'chatbotistic' ); ?></span>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<nav class="cb-portal__nav" aria-label="<?php esc_attr_e( 'Portal', 'chatbotistic' ); ?>">
				<ul>
					<?php foreach ( $cb_views as $cb_key => $cb_v ) : ?>
						<li class="<?php echo $cb_view === $cb_key ? 'is-active' : ''; ?>">
							<a href="<?php echo cb_view_url( $cb_key ); ?>">
								<?php cb_icon( $cb_v[0], 17 ); ?>
								<span><?php echo esc_html( $cb_v[1] ); ?></span>
								<?php if ( 'tools' === $cb_key && $cb_active ) : ?>
									<span class="cb-portal__nav-pill"><?php esc_html_e( 'Live', 'chatbotistic' ); ?></span>
								<?php endif; ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
				<div class="cb-portal__nav-foot">
					<a class="cb-portal__logout" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Log out', 'chatbotistic' ); ?></a>
				</div>
			</nav>
		</aside>

		<div class="cb-portal__main">
			<header class="cb-portal__head">
				<div>
					<span class="cb-eyebrow"><?php esc_html_e( 'Member Portal', 'chatbotistic' ); ?></span>
					<h1 class="cb-portal__title cb-grad">
						<?php
						if ( 'overview' === $cb_view ) {
							$cb_name = $cb_user->first_name ?: $cb_user->display_name;
							/* translators: %s: member name. */
							printf( esc_html__( 'Welcome back, %s', 'chatbotistic' ), esc_html( $cb_name ) );
						} else {
							echo esc_html( $cb_views[ $cb_view ][1] );
						}
						?>
					</h1>
				</div>
				<?php
				if ( $cb_active ) {
					cb_button( __( 'Open Tools', 'chatbotistic' ), cb_view_url( 'tools' ), 'primary', array( 'icon' => 'arrow-r' ) );
				} else {
					cb_button( __( 'Activate a plan', 'chatbotistic' ), cb_plans_url(), 'primary', array( 'icon' => 'arrow-r' ) );
				}
				?>
			</header>

			<div class="cb-portal__body">
				<?php
				$cb_part = locate_template( 'template-parts/portal/' . $cb_view . '.php' );
				if ( $cb_part ) {
					include $cb_part;
				}
				?>
			</div>
		</div>

	</div>
</section>

<?php
get_footer();
