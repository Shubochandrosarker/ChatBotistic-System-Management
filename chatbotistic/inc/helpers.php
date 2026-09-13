<?php
/**
 * Presentation helpers — icons, buttons, membership gate.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/**
 * Inline SVG icon. Stroke-based, inherits currentColor.
 *
 * @param string $name  Icon key.
 * @param int    $size  Pixel size.
 * @param string $class Extra class.
 */
function cb_icon( $name, $size = 18, $class = '' ) {
	echo cb_get_icon( $name, $size, $class ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Return inline SVG icon markup.
 *
 * @param string $name  Icon key.
 * @param int    $size  Pixel size.
 * @param string $class Extra class.
 * @return string
 */
function cb_get_icon( $name, $size = 18, $class = '' ) {
	$single = array(
		'arrow-r'  => 'M5 12h14M13 5l7 7-7 7',
		'arrow-tr' => 'M7 17 17 7M9 7h8v8',
		'check'    => 'M5 12l4 4L19 6',
		'x'        => 'M6 6l12 12M18 6 6 18',
		'plus'     => 'M12 5v14M5 12h14',
		'minus'    => 'M5 12h14',
		'menu'     => 'M4 6h16M4 12h16M4 18h16',
		'bolt'     => 'M13 2 4 14h7l-1 8 9-12h-7z',
		'phone'    => 'M5 4h4l2 5-3 2a12 12 0 0 0 6 6l2-3 5 2v4a2 2 0 0 1-2 2A17 17 0 0 1 3 6a2 2 0 0 1 2-2z',
		'rocket'   => 'M5 19c0-3 3-7 8-12 3 0 5 2 5 5-5 5-9 8-12 8zM5 19l-2 2M14 11l-1 1',
		'home'     => 'M3 11 12 3l9 8v10H3z M9 21v-7h6v7',
		'shield'   => 'M12 2 4 5v6c0 5 4 9 8 11 4-2 8-6 8-11V5z',
		'tag'      => 'M20 12V4h-8L3 13l8 8 9-9z',
		'code'     => 'M9 18 3 12l6-6M15 6l6 6-6 6',
		'spark'    => 'M12 3v6M12 15v6M3 12h6M15 12h6',
		'star'     => 'M12 3l2.7 6.5L22 10l-5 4.5L18.5 22 12 18l-6.5 4L7 14.5 2 10l7.3-.5z',
	);
	$multi = array(
		'ai'     => '<circle cx="12" cy="12" r="4"/><path d="M12 2v3M12 19v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M2 12h3M19 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1"/>',
		'wa'     => '<path d="M20 12a8 8 0 0 1-12 7l-4 1 1-4a8 8 0 1 1 15-4z"/><path d="M8 10c0 4 2 6 6 6l1-2-2-1-1 1c-1-.5-2-1.5-2-2l1-1-1-2z" fill="currentColor" stroke="none"/>',
		'form'   => '<rect x="4" y="4" width="16" height="16" rx="2"/><path d="M8 9h8M8 13h6M8 17h4"/>',
		'cal'    => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/>',
		'users'  => '<path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="3.5"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/>',
		'mail'   => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 7 9-7"/>',
		'card'   => '<rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 11h18M7 16h3"/>',
		'page'   => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6M8 13h8M8 17h5"/>',
		'chart'  => '<path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/>',
		'inbox'  => '<path d="M3 13h6l2 3h2l2-3h6M3 13V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8"/>',
		'plug'   => '<path d="M9 7V3M15 7V3M6 11h12v3a6 6 0 0 1-12 0zM12 20v2"/>',
		'lock'   => '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
		'globe'  => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
		'store'  => '<path d="M3 9l1-5h16l1 5M4 9v11h16V9M9 13h6"/>',
		'cart'   => '<path d="M3 4h2l2 12h12l2-8H6"/><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/>',
		'wp'     => '<circle cx="12" cy="12" r="9"/><path d="M3.5 12 9 21M21 11l-7 10M12 3l3 9-3 9"/>',
		'chat'   => '<path d="M21 12a8 8 0 0 1-12 6.9L4 20l1.1-5A8 8 0 1 1 21 12z"/>',
		'gear'   => '<circle cx="12" cy="12" r="3.2"/><path d="M19 12a7 7 0 0 0-.1-1.3l2-1.6-2-3.4-2.4 1a7 7 0 0 0-2.2-1.3L13.8 2h-3.6l-.4 2.4a7 7 0 0 0-2.2 1.3l-2.4-1-2 3.4 2 1.6A7 7 0 0 0 5 12c0 .4 0 .9.1 1.3l-2 1.6 2 3.4 2.4-1a7 7 0 0 0 2.2 1.3l.4 2.4h3.6l.4-2.4a7 7 0 0 0 2.2-1.3l2.4 1 2-3.4-2-1.6c.1-.4.1-.9.1-1.3z"/>',
		'key'    => '<circle cx="8" cy="15" r="4"/><path d="M10.8 12.2 20 3M16 7l3 3M14 9l3 3"/>',
		'play'   => '<circle cx="12" cy="12" r="9"/><path d="m10 9 5 3-5 3z"/>',
		'user'   => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
		'spa'    => '<path d="M12 2c3 4 3 8 0 12-3-4-3-8 0-12zM6 13c4 0 6 2 6 7M18 13c-4 0-6 2-6 7"/>',
		'plane'  => '<path d="M22 2 11 13M22 2l-7 20-4-9-9-4z"/>',
		'book'   => '<path d="M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2zM19 3v16"/>',
		'sun'    => '<circle cx="12" cy="12" r="4"/><path d="M12 2v3M12 19v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M2 12h3M19 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1"/>',
		'moon'   => '<path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/>',
	);

	$cls = trim( 'cb-ico ' . $class );
	$out = '<svg class="' . esc_attr( $cls ) . '" width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">';
	if ( isset( $single[ $name ] ) ) {
		$out .= '<path d="' . esc_attr( $single[ $name ] ) . '"/>';
	} elseif ( isset( $multi[ $name ] ) ) {
		$out .= $multi[ $name ];
	}
	return $out . '</svg>';
}

/**
 * Pick a sensible, varied icon key for a feature card that has no explicit
 * icon, cycling through a curated palette by index so a grid gets distinct
 * icons instead of a repeated one.
 *
 * @param int $i Zero-based item index.
 * @return string Icon key understood by cb_get_icon().
 */
function cb_feature_icon( $i ) {
	$set = array( 'spark', 'bolt', 'chat', 'wa', 'ai', 'form', 'cal', 'inbox', 'mail', 'plug', 'card', 'page', 'tag', 'code', 'chart', 'users', 'globe', 'shield', 'rocket', 'star', 'gear', 'key' );
	return $set[ (int) $i % count( $set ) ];
}

/**
 * Render a CTA button.
 *
 * @param string $label Text.
 * @param string $url   URL.
 * @param string $style primary|ghost|link.
 * @param array  $args  size, icon, target, class.
 */
function cb_button( $label, $url = '#', $style = 'primary', $args = array() ) {
	$args  = wp_parse_args( $args, array( 'size' => '', 'icon' => '', 'target' => '', 'class' => '' ) );
	$class = 'cb-btn cb-btn--' . sanitize_html_class( $style );
	if ( $args['size'] ) {
		$class .= ' cb-btn--' . sanitize_html_class( $args['size'] );
	}
	if ( $args['class'] ) {
		$class .= ' ' . esc_attr( $args['class'] );
	}
	$target = $args['target'] ? ' target="' . esc_attr( $args['target'] ) . '" rel="noopener"' : '';
	printf(
		'<a href="%s" class="%s"%s>%s%s</a>',
		esc_url( $url ),
		esc_attr( $class ),
		$target, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_html( $label ),
		$args['icon'] ? cb_get_icon( $args['icon'], 15 ) : '' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}

/**
 * Resolve the current user's Memberistic membership.
 *
 * Memberistic is the single source of truth for paid status. Falls back to
 * user meta (set by Memberistic on activation) if the repository class is
 * unavailable, so the theme never fatals when the plugin is inactive.
 *
 * @param int $user_id Optional. Defaults to current user.
 * @return array{active:bool,status:string,plan_id:int,plan_name:string,renewal:int,days_left:?int}
 */
function cb_membership( $user_id = 0 ) {
	$user_id = $user_id ?: get_current_user_id();
	$out     = array( 'id' => 0, 'active' => false, 'status' => '', 'plan_id' => 0, 'plan_name' => '', 'renewal' => 0, 'days_left' => null );

	if ( ! $user_id ) {
		return $out;
	}

	$paid_states = array( 'active', 'comped', 'trial' );
	$repo        = '\WordPressistic\Memberistic\Database\Memberships_Repository';
	$row         = null;

	if ( class_exists( $repo ) && method_exists( $repo, 'get_by_user_id' ) ) {
		$row = $repo::get_by_user_id( $user_id );

		// Fallback: a membership created by an admin may not be linked via
		// primary_user_id. Match by the account email, exactly as Memberistic's
		// own account shortcode does, so manually-granted plans still show.
		if ( ! is_array( $row ) && method_exists( $repo, 'get_by_person_email' ) ) {
			$user = get_userdata( $user_id );
			if ( $user && $user->user_email ) {
				$row = $repo::get_by_person_email( $user->user_email );
			}
		}
	}

	if ( is_array( $row ) ) {
		$out['id']       = (int) ( $row['id'] ?? 0 );
		$out['status']   = (string) ( $row['status'] ?? '' );
		$out['plan_id']  = (int) ( $row['plan_id'] ?? 0 );
		$out['active']   = in_array( $out['status'], $paid_states, true );
		if ( ! empty( $row['plan_name'] ) ) {
			$out['plan_name'] = (string) $row['plan_name'];
		}
		$renewal         = isset( $row['renewal_date'] ) ? strtotime( (string) $row['renewal_date'] ) : 0;
		if ( $renewal ) {
			$out['renewal']   = $renewal;
			$out['days_left'] = max( 0, (int) ceil( ( $renewal - time() ) / DAY_IN_SECONDS ) );
		}
	} else {
		// Fallback: user meta written by Memberistic on membership activation.
		$plan_id = (int) get_user_meta( $user_id, 'memberistic_active_plan_id', true );
		if ( $plan_id ) {
			$out['active']  = true;
			$out['status']  = 'active';
			$out['plan_id'] = $plan_id;
		}
	}

	if ( ! $out['plan_name'] ) {
		$out['plan_name'] = (string) get_user_meta( $user_id, 'memberistic_active_plan_name', true );
	}
	if ( ! $out['plan_name'] && $out['plan_id'] ) {
		$out['plan_name'] = sprintf( __( 'Plan #%d', 'chatbotistic' ), $out['plan_id'] );
	}

	return apply_filters( 'cb_membership', $out, $user_id );
}

/**
 * URL of a Memberistic frontend page (plans, checkout, account, login).
 *
 * @param string $key      Memberistic settings page key, e.g. plans_page_id.
 * @param string $slug     Fallback page slug.
 * @param string $fallback Final fallback URL.
 * @return string
 */
function cb_member_url( $key, $slug = '', $fallback = '' ) {
	if ( function_exists( 'memberistic_get_page_url' ) ) {
		return memberistic_get_page_url( $key, $slug, $fallback ?: home_url( '/' ) );
	}
	if ( $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			return get_permalink( $page );
		}
	}
	return $fallback ?: home_url( '/' );
}

/**
 * Convenience: plans / pricing URL.
 *
 * @return string
 */
function cb_plans_url() {
	return cb_member_url( 'plans_page_id', 'memberistic-memberships', home_url( '/pricing/' ) );
}

/**
 * Convenience: direct checkout URL for Free Forever onboarding.
 *
 * @return string
 */
function cb_free_checkout_url() {
	$checkout = cb_member_url( 'checkout_page_id', 'checkout', home_url( '/checkout/' ) );
	return add_query_arg(
		array(
			'memberistic_plan'  => 'free',
			'memberistic_cycle' => 'monthly',
		),
		$checkout
	);
}

/**
 * Canonical URL of the standalone Chatbotistic dashboard app.
 *
 * The dashboard lives at app.chatbotistic.com (Next.js app) — the
 * WordPress site stays marketing + billing + license server, and members
 * click through to the app from every "Open Dashboard" CTA.
 *
 * Resolution order:
 *   1. `cb_dashboard_url` option (set via wp option / WP-CLI — the theme
 *      has no settings screen, so this is the per-site override hook)
 *   2. The hard default https://app.chatbotistic.com
 * The result is filterable via `cb_dashboard_url` for staging deploys.
 *
 * @param string $path Optional path appended to the base, e.g. 'docs'.
 * @return string
 */
function cb_dashboard_url( $path = '' ) {
	$base = trim( (string) get_option( 'cb_dashboard_url', '' ) );
	if ( '' === $base ) {
		$base = 'https://app.chatbotistic.com';
	}
	$url = untrailingslashit( $base ) . '/' . ltrim( (string) $path, '/' );
	return (string) apply_filters( 'cb_dashboard_url', $url, $path );
}

/**
 * Brand logo markup.
 *
 * Prefers a Customizer custom logo when one is set; otherwise falls back to
 * the bundled neon wordmark shipped with the theme. Returns a linked logo so
 * it can be dropped into the header, footer, and auth/checkout top bars with
 * consistent branding everywhere.
 *
 * @param array $args {
 *     @type string $class Extra class on the <a> wrapper. Default ''.
 *     @type bool   $link  Wrap in a home link. Default true.
 * }
 * @return string
 */
function cb_logo( $args = array() ) {
	$args  = wp_parse_args( $args, array( 'class' => '', 'link' => true ) );
	$name  = get_bloginfo( 'name' );

	if ( has_custom_logo() ) {
		$inner = get_custom_logo();
		// get_custom_logo() already returns a linked <img>; strip its anchor so
		// we control the wrapper consistently.
		$inner = preg_replace( '#</?a[^>]*>#i', '', $inner );
	} else {
		$src   = CB_URI . '/assets/images/chatbotistic-logo.png';
		$inner = sprintf(
			'<img src="%1$s" alt="%2$s" class="cb-logo__img" width="242" height="60" decoding="async" />',
			esc_url( $src ),
			esc_attr( $name )
		);
	}

	$class = trim( 'cb-logo ' . $args['class'] );

	if ( ! $args['link'] ) {
		return '<span class="' . esc_attr( $class ) . '">' . $inner . '</span>';
	}

	return sprintf(
		'<a class="%1$s" href="%2$s" aria-label="%3$s" rel="home">%4$s</a>',
		esc_attr( $class ),
		esc_url( home_url( '/' ) ),
		esc_attr( $name ),
		$inner
	);
}
