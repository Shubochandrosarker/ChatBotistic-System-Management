<?php
/**
 * Site header.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

$cb_logged_in = is_user_logged_in();
$cb_user      = $cb_logged_in ? wp_get_current_user() : null;
$cb_account   = home_url( '/account/' );
$cb_signup    = cb_free_checkout_url();
$cb_signin    = home_url( '/login/' );
$cb_member    = $cb_logged_in ? cb_membership() : array( 'active' => false, 'plan_name' => '' );

/**
 * Active-state helper for the primary nav. Returns a space-prefixed class
 * fragment when the page matches. Use cb_nav_active_attrs() in new code —
 * it also emits aria-current="page" for screen readers.
 *
 * @param string $template Page template filename.
 * @param string $slug     Page slug.
 * @return string
 */
function cb_nav_active( $template = '', $slug = '' ) {
	$is = ( $template && is_page_template( $template ) ) || ( $slug && is_page( $slug ) );
	return $is ? ' is-active' : '';
}

/**
 * Same match logic as cb_nav_active() but returns the full attribute
 * pair to drop into an <a> tag: the class fragment AND aria-current
 * when active. Use as: <a class="cb-nav__link<?php echo cb_nav_active_attrs(...); ?>"
 * (note: aria-current is appended after the closing class quote).
 */
function cb_nav_active_attrs( $template = '', $slug = '' ) {
	$is = ( $template && is_page_template( $template ) ) || ( $slug && is_page( $slug ) );
	return $is ? ' is-active" aria-current="page' : '';
}

$cb_products = array(
	array( 'whatsapp-automation', 'wa',   __( 'WhatsApp Automation', 'chatbotistic' ) ),
	array( 'ai-chatbot',          'ai',   __( 'AI Chatbot', 'chatbotistic' ) ),
	array( 'booking-forms',       'cal',  __( 'Booking Forms', 'chatbotistic' ) ),
	array( 'agency-white-label',  'tag',  __( 'Agency & White Label', 'chatbotistic' ) ),
	array( 'wordpress-plugin',    'wp',   __( 'WordPress Plugin', 'chatbotistic' ) ),
);
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#05070e">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="cb-skip" href="#cb-main"><?php esc_html_e( 'Skip to content', 'chatbotistic' ); ?></a>

<div class="cb-site">

<header class="cb-header" role="banner">
	<div class="cb-container cb-header__inner">

		<?php echo cb_logo(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside helper. ?>

		<nav class="cb-nav" aria-label="<?php esc_attr_e( 'Primary', 'chatbotistic' ); ?>">
			<a class="cb-nav__link<?php echo is_front_page() ? ' is-active" aria-current="page' : ''; ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'chatbotistic' ); ?></a>
			<a class="cb-nav__link<?php echo cb_nav_active_attrs( 'page-features.php', 'features' ); ?>" href="<?php echo esc_url( home_url( '/features/' ) ); ?>"><?php esc_html_e( 'Features', 'chatbotistic' ); ?></a>
			<a class="cb-nav__link<?php echo cb_nav_active_attrs( 'page-pricing.php', 'pricing' ); ?>" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'Pricing', 'chatbotistic' ); ?></a>
			<a class="cb-nav__link<?php echo cb_nav_active_attrs( 'page-use-cases.php', 'use-cases' ); ?>" href="<?php echo esc_url( home_url( '/use-cases/' ) ); ?>"><?php esc_html_e( 'Use cases', 'chatbotistic' ); ?></a>

			<div class="cb-has-menu" data-menu aria-expanded="false">
				<button type="button" class="cb-nav__link" data-menu-trigger aria-haspopup="true">
					<?php esc_html_e( 'Products', 'chatbotistic' ); ?>
					<svg class="cb-caret" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
				</button>
				<div class="cb-dropdown" role="menu">
					<?php foreach ( $cb_products as $cb_p ) : ?>
						<a href="<?php echo esc_url( home_url( '/' . $cb_p[0] . '/' ) ); ?>" role="menuitem">
							<span class="cb-dropdown__ico"><?php cb_icon( $cb_p[1], 16 ); ?></span>
							<?php echo esc_html( $cb_p[2] ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>

			<a class="cb-nav__link<?php echo cb_nav_active_attrs( 'page-docs.php', 'docs' ); ?>" href="<?php echo esc_url( home_url( '/docs/' ) ); ?>"><?php esc_html_e( 'Docs', 'chatbotistic' ); ?></a>
		</nav>

		<div class="cb-header__cta">

			<div class="cb-account-menu" data-menu aria-expanded="false">
				<button type="button" class="cb-icon-btn" data-menu-trigger aria-haspopup="true" aria-label="<?php esc_attr_e( 'Account', 'chatbotistic' ); ?>">
					<?php if ( $cb_logged_in ) : ?>
						<?php echo get_avatar( $cb_user->ID, 48 ); ?>
					<?php else : ?>
						<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
					<?php endif; ?>
					<?php if ( $cb_logged_in ) : ?><span class="cb-dot" aria-hidden="true"></span><?php endif; ?>
				</button>
				<div class="cb-dropdown" role="menu">
					<?php if ( $cb_logged_in ) : ?>
						<div class="cb-dropdown__head">
							<div class="cb-dropdown__label"><?php esc_html_e( 'Signed in', 'chatbotistic' ); ?></div>
							<strong><?php echo esc_html( $cb_user->display_name ); ?></strong>
							<?php if ( ! empty( $cb_member['active'] ) && $cb_member['plan_name'] ) : ?>
								<div style="font-size:11.5px;color:var(--cb-indigo-2);margin-top:2px;"><?php echo esc_html( $cb_member['plan_name'] ); ?></div>
							<?php endif; ?>
						</div>
						<a href="<?php echo esc_url( cb_dashboard_url() ); ?>" role="menuitem"><?php esc_html_e( 'Open Dashboard', 'chatbotistic' ); ?></a>
						<a href="<?php echo esc_url( $cb_account ); ?>" role="menuitem"><?php esc_html_e( 'Member Portal', 'chatbotistic' ); ?></a>
						<a href="<?php echo esc_url( add_query_arg( 'view', 'tools', $cb_account ) ); ?>" role="menuitem"><?php esc_html_e( 'Tools', 'chatbotistic' ); ?></a>
						<a href="<?php echo esc_url( add_query_arg( 'view', 'subscription', $cb_account ) ); ?>" role="menuitem"><?php esc_html_e( 'Subscription', 'chatbotistic' ); ?></a>
						<div class="cb-dropdown__sep"></div>
						<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" role="menuitem"><?php esc_html_e( 'Log out', 'chatbotistic' ); ?></a>
					<?php else : ?>
						<div class="cb-dropdown__head">
							<div class="cb-dropdown__label"><?php esc_html_e( 'Welcome', 'chatbotistic' ); ?></div>
							<strong><?php esc_html_e( 'Get started in 60 seconds', 'chatbotistic' ); ?></strong>
						</div>
						<a href="<?php echo esc_url( $cb_signin ); ?>" role="menuitem"><?php esc_html_e( 'Sign in', 'chatbotistic' ); ?></a>
						<a href="<?php echo esc_url( $cb_signup ); ?>" role="menuitem"><?php esc_html_e( 'Create account', 'chatbotistic' ); ?></a>
						<div class="cb-dropdown__sep"></div>
						<a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>" role="menuitem"><?php esc_html_e( 'Docs & Help', 'chatbotistic' ); ?></a>
					<?php endif; ?>
				</div>
			</div>

			<a class="cb-btn cb-btn--ghost cb-btn--sm" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Talk to sales', 'chatbotistic' ); ?></a>
			<a class="cb-btn cb-btn--primary cb-btn--sm" href="<?php echo esc_url( $cb_logged_in ? cb_dashboard_url() : $cb_signup ); ?>">
				<?php echo $cb_logged_in ? esc_html__( 'Open Dashboard', 'chatbotistic' ) : esc_html__( 'Get Started', 'chatbotistic' ); ?>
			</a>

			<button type="button" class="cb-icon-btn cb-burger" data-burger aria-expanded="false" aria-label="<?php esc_attr_e( 'Menu', 'chatbotistic' ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
			</button>
		</div>
	</div>

</header>

<?php /*
 * Mobile drawer lives OUTSIDE <header> on purpose: the header's
 * backdrop-filter establishes a containing block, which would otherwise trap
 * this position:fixed drawer inside the 72px bar and collapse it to nothing.
 */ ?>
<nav class="cb-mobile-nav" data-mobile-nav aria-label="<?php esc_attr_e( 'Mobile', 'chatbotistic' ); ?>">
	<?php if ( $cb_logged_in ) : ?>
		<div class="cb-mobile-nav__user">
			<?php echo get_avatar( $cb_user->ID, 40 ); ?>
			<div>
				<strong><?php echo esc_html( $cb_user->display_name ); ?></strong>
				<?php if ( ! empty( $cb_member['active'] ) && $cb_member['plan_name'] ) : ?>
					<span><?php echo esc_html( $cb_member['plan_name'] ); ?></span>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'chatbotistic' ); ?></a>
	<a href="<?php echo esc_url( home_url( '/features/' ) ); ?>"><?php esc_html_e( 'Features', 'chatbotistic' ); ?></a>
	<a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'Pricing', 'chatbotistic' ); ?></a>
	<a href="<?php echo esc_url( home_url( '/use-cases/' ) ); ?>"><?php esc_html_e( 'Use cases', 'chatbotistic' ); ?></a>
	<div class="cb-mobile-nav__group"><?php esc_html_e( 'Products', 'chatbotistic' ); ?></div>
	<?php foreach ( $cb_products as $cb_p ) : ?>
		<a href="<?php echo esc_url( home_url( '/' . $cb_p[0] . '/' ) ); ?>"><?php echo esc_html( $cb_p[2] ); ?></a>
	<?php endforeach; ?>
	<div class="cb-mobile-nav__group"><?php esc_html_e( 'More', 'chatbotistic' ); ?></div>
	<a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>"><?php esc_html_e( 'Docs', 'chatbotistic' ); ?></a>
	<a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'About', 'chatbotistic' ); ?></a>
	<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact', 'chatbotistic' ); ?></a>
	<div class="cb-mobile-nav__cta">
		<?php if ( $cb_logged_in ) : ?>
			<a class="cb-btn cb-btn--primary" href="<?php echo esc_url( cb_dashboard_url() ); ?>"><?php esc_html_e( 'Open Dashboard', 'chatbotistic' ); ?></a>
			<a class="cb-btn cb-btn--ghost" href="<?php echo esc_url( $cb_account ); ?>"><?php esc_html_e( 'Member Portal', 'chatbotistic' ); ?></a>
			<a class="cb-btn cb-btn--ghost" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Log out', 'chatbotistic' ); ?></a>
		<?php else : ?>
			<a class="cb-btn cb-btn--primary" href="<?php echo esc_url( $cb_signup ); ?>"><?php esc_html_e( 'Get Started', 'chatbotistic' ); ?></a>
			<a class="cb-btn cb-btn--ghost" href="<?php echo esc_url( $cb_signin ); ?>"><?php esc_html_e( 'Sign in', 'chatbotistic' ); ?></a>
		<?php endif; ?>
	</div>
</nav>

<main id="cb-main" role="main">
