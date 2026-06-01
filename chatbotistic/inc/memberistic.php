<?php
/**
 * Memberistic integration.
 *
 * Memberistic has no theme-override system — it requires its templates
 * straight from the plugin folder. So the theme never edits plugin files;
 * instead it (1) skins Memberistic's namespaced frontend markup into the
 * Chatbotistic design system via CSS, and (2) owns the brand colour and
 * label so the membership flow always matches the site.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the Memberistic plugin is active.
 *
 * @return bool
 */
function cb_memberistic_active() {
	return defined( 'MEMBERISTIC_VERSION' ) || function_exists( 'memberistic_get_setting' );
}

/**
 * Brand values the theme enforces on Memberistic so the two always match.
 *
 * @return array{primary_brand_color:string,brand_label:string}
 */
function cb_memberistic_brand() {
	return (array) apply_filters( 'cb_memberistic_brand', array(
		'primary_brand_color' => '#4f8bff',
		'brand_label'         => 'Chatbotistic',
	) );
}

/**
 * Load the Memberistic skin, and the plugin's own assets where the theme
 * embeds Memberistic shortcodes from a template.
 *
 * The plugin only enqueues its frontend assets when it detects a shortcode
 * in post *content* or one of its mapped pages. Shortcodes placed in a PHP
 * template (the account portal) are invisible to that check, so we load the
 * plugin assets explicitly there.
 */
function cb_memberistic_assets() {
	if ( ! cb_memberistic_active() ) {
		return;
	}

	if ( defined( 'MEMBERISTIC_URL' ) && is_page_template( 'page-account.php' ) ) {
		$ver = defined( 'MEMBERISTIC_VERSION' ) ? MEMBERISTIC_VERSION : null;
		wp_enqueue_style( 'memberistic-frontend', MEMBERISTIC_URL . 'assets/frontend.css', array(), $ver );
		wp_enqueue_script( 'memberistic-frontend', MEMBERISTIC_URL . 'assets/frontend.js', array(), $ver, true );
	}

	wp_enqueue_style(
		'cb-memberistic',
		CB_URI . '/assets/css/memberistic.css',
		array( 'cb-theme' ),
		cb_asset_ver( '/assets/css/memberistic.css' )
	);
}
add_action( 'wp_enqueue_scripts', 'cb_memberistic_assets', 20 );

/**
 * Checkout URL for a given Memberistic plan slug.
 *
 * @param string $slug Plan slug. Empty for the bare checkout page.
 * @return string
 */
function cb_memberistic_checkout_url( $slug = '' ) {
	$url = cb_member_url( 'checkout_page_id', 'memberistic-checkout', cb_plans_url() );
	return $slug ? add_query_arg( 'memberistic_plan', rawurlencode( $slug ), $url ) : $url;
}

/**
 * Inquiry URL for a contact-only plan (e.g. Lifetime / LTD).
 *
 * Routes to the Contact page with the plan pre-selected so the lead lands in
 * the LTD inquiry flow rather than the free/Stripe checkout. Filterable so a
 * dedicated LTD form can take over.
 *
 * @param string $slug Plan slug.
 * @return string
 */
function cb_ltd_inquiry_url( $slug = 'lifetime' ) {
	$url = add_query_arg( 'plan', rawurlencode( $slug ?: 'lifetime' ), home_url( '/contact/' ) );
	return (string) apply_filters( 'cb_ltd_inquiry_url', $url, $slug );
}

/**
 * Format a numeric cap for display. -1 (and 0 for contact-only plans) = unlimited.
 *
 * @param int|string $n Cap value.
 * @return string
 */
function cb_cap_label( $n ) {
	$n = (int) $n;
	return -1 === $n ? __( 'Unlimited', 'chatbotistic' ) : (string) $n;
}

/**
 * Licenseistic license rows for a user, newest first.
 *
 * @param int $user_id WP user ID.
 * @return array<int,array>
 */
function cb_user_licenses( $user_id = 0 ) {
	$user_id = $user_id ?: get_current_user_id();
	if ( ! $user_id || ! class_exists( '\WPistic_LSI_License_Service' ) ) {
		return array();
	}
	$rows = \WPistic_LSI_License_Service::get_licenses( array( 'customer_id' => $user_id, 'per_page' => 50 ) );
	return is_array( $rows ) ? $rows : array();
}

/**
 * Plan caps for the current user, resolved from their active license's stored
 * entitlement envelope, falling back to the Memberistic plan's settings.limits.
 *
 * @param int $user_id WP user ID.
 * @return array{tier?:string,plan_name?:string,max_widgets:int,max_agents:int,max_domains:int,white_label:bool,branding:bool}|array
 */
function cb_account_caps( $user_id = 0 ) {
	$user_id = $user_id ?: get_current_user_id();
	if ( ! $user_id ) {
		return array();
	}

	// 1. From the user's license notes envelope (written by the bridge).
	foreach ( cb_user_licenses( $user_id ) as $row ) {
		$notes = isset( $row['notes'] ) ? json_decode( (string) $row['notes'], true ) : null;
		if ( is_array( $notes ) && ! empty( $notes['_mlb']['caps'] ) && is_array( $notes['_mlb']['caps'] ) ) {
			return $notes['_mlb']['caps'];
		}
	}

	// 2. Fallback: the Memberistic plan settings.limits for the active plan.
	$m    = cb_membership( $user_id );
	$repo = '\WordPressistic\Memberistic\Database\Plans_Repository';
	if ( ! empty( $m['plan_id'] ) && class_exists( $repo ) && method_exists( $repo, 'get' ) ) {
		$plan = $repo::get( (int) $m['plan_id'] );
		$settings = is_array( $plan ) ? json_decode( (string) ( $plan['settings'] ?? '' ), true ) : null;
		if ( is_array( $settings ) && ! empty( $settings['limits'] ) && is_array( $settings['limits'] ) ) {
			$l = $settings['limits'];
			return array(
				'plan_name'   => $m['plan_name'],
				'max_widgets' => (int) ( $l['widgets'] ?? 1 ),
				'max_agents'  => (int) ( $l['agents'] ?? 1 ),
				'max_domains' => (int) ( $l['domains'] ?? 1 ),
				'white_label' => ! empty( $l['white_label'] ),
				'branding'    => ! empty( $l['branding'] ),
			);
		}
	}

	return array();
}

/**
 * Active Memberistic plans, normalised for the theme's pricing cards.
 *
 * Returns an empty array if Memberistic is inactive or has no plans — the
 * pricing template falls back to its built-in defaults in that case.
 *
 * @return array<int,array>
 */
function cb_memberistic_plans() {
	$repo = '\WordPressistic\Memberistic\Database\Plans_Repository';
	if ( ! class_exists( $repo ) || ! method_exists( $repo, 'get_all' ) ) {
		return array();
	}

	$rows = $repo::get_all( array( 'status' => 'active' ) );
	if ( ! is_array( $rows ) || ! $rows ) {
		return array();
	}

	$plans = array();
	foreach ( $rows as $row ) {
		$slug     = (string) ( $row['slug'] ?? '' );
		$monthly  = (float) ( $row['monthly_price'] ?? 0 );
		$annual   = (float) ( $row['annual_price'] ?? 0 );
		$benefits = json_decode( (string) ( $row['benefits'] ?? '' ), true );
		$settings = json_decode( (string) ( $row['settings'] ?? '' ), true );
		$settings = is_array( $settings ) ? $settings : array();

		// A plan is "contact only" (e.g. Lifetime / LTD) when it is flagged in
		// the plan settings, never merely because its price is 0. This keeps
		// Lifetime from being rendered or checked out like the Free plan.
		$contact_only = ! empty( $settings['contact_only'] );

		$plans[] = array(
			'name'           => (string) ( $row['name'] ?? '' ),
			'slug'           => $slug,
			'description'    => (string) ( $row['description'] ?? '' ),
			'monthly'        => $monthly,
			'annual_total'   => $annual,
			'annual_monthly' => $annual > 0 ? $annual / 12 : 0.0,
			'saving'         => max( 0.0, ( $monthly * 12 ) - $annual ),
			'benefits'       => is_array( $benefits ) ? array_values( $benefits ) : array(),
			'featured'       => ! empty( $row['is_featured'] ),
			'billing_cycle'  => (string) ( $settings['billing_cycle'] ?? '' ),
			'contact_only'   => $contact_only,
			'price_display'  => (string) ( $settings['price_display'] ?? '' ),
			'checkout'       => $contact_only
				? cb_ltd_inquiry_url( $slug )
				: cb_memberistic_checkout_url( $slug ),
		);
	}
	return $plans;
}

/**
 * Whether a page is a Memberistic frontend page.
 *
 * True for any of Memberistic's mapped pages (plans, checkout, account,
 * login, renewal, thank-you, failed-payment, staff dashboard) or any page
 * whose content embeds a Memberistic shortcode. Used so the generic page
 * template renders these full-width instead of inside the narrow prose column.
 *
 * @param int|WP_Post|null $post Optional post.
 * @return bool
 */
function cb_is_memberistic_page( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return false;
	}

	if ( function_exists( 'memberistic_get_setting' ) ) {
		$keys = array(
			'plans_page_id', 'checkout_page_id', 'account_page_id', 'renewal_page_id',
			'login_page_id', 'thank_you_page_id', 'failed_payment_page_id', 'staff_dashboard_page_id',
		);
		foreach ( $keys as $key ) {
			if ( (int) memberistic_get_setting( $key, 0 ) === (int) $post->ID ) {
				return true;
			}
		}
	}

	return $post->post_content && false !== strpos( $post->post_content, '[memberistic_' );
}

/**
 * Format a price for a pricing card — no trailing ".00" on whole amounts.
 *
 * @param float $amount Price.
 * @return string
 */
function cb_price( $amount ) {
	$amount = (float) $amount;
	$whole  = abs( $amount - round( $amount ) ) < 0.005;
	return '$' . number_format_i18n( $amount, $whole ? 0 : 2 );
}


/**
 * Force Memberistic's brand colour and label to the theme's values.
 *
 * Filtering the stored settings option keeps the membership pages on-brand
 * with zero admin steps. To hand control back to the Memberistic settings
 * screen, return false from the `cb_memberistic_owns_brand` filter.
 *
 * @param mixed $settings Stored Memberistic settings.
 * @return mixed
 */
function cb_memberistic_filter_settings( $settings ) {
	if ( ! apply_filters( 'cb_memberistic_owns_brand', true ) ) {
		return $settings;
	}
	if ( ! is_array( $settings ) ) {
		$settings = array();
	}
	return array_merge( $settings, cb_memberistic_brand() );
}
add_filter( 'option_memberistic_settings', 'cb_memberistic_filter_settings' );
add_filter( 'default_option_memberistic_settings', 'cb_memberistic_filter_settings' );

/**
 * Keep the filtered brand label aligned for code paths that use it directly.
 *
 * @return string
 */
function cb_memberistic_brand_label() {
	if ( ! apply_filters( 'cb_memberistic_owns_brand', true ) ) {
		return 'Memberistic';
	}
	$brand = cb_memberistic_brand();
	return $brand['brand_label'];
}
add_filter( 'memberistic_brand_label', 'cb_memberistic_brand_label' );
