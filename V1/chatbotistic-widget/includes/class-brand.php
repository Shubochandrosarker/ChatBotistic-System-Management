<?php
namespace Chatbotistic_Widget;

defined( 'ABSPATH' ) || exit;

/**
 * Brand resolver — per-license white-label.
 *
 * The widget plugin used to hard-code "Chatbotistic" everywhere. Now every
 * brand-facing string is resolved at runtime from the license activation
 * response (see Memberistic → Licenseistic Bridge → Rest::shape_success).
 *
 * Fallback order:
 *   1. CBW_BRAND_OVERRIDE_*  constants — let a site admin override in wp-config.php
 *   2. license payload — { brand_label, brand_homepage, support_email, logo_url,
 *      custom_domain }
 *   3. neutral defaults — generic strings, no Chatbotistic mention
 *
 * Free / Pro / Agency / Lifetime are ALL white-label per spec, so there is
 * NO tier that should ever render "Chatbotistic".
 */
final class Brand {

	const NEUTRAL_LABEL    = 'WhatsApp Widget';
	const NEUTRAL_TAGLINE  = '';
	const NEUTRAL_HOMEPAGE = '';
	const NEUTRAL_SUPPORT  = '';

	public static function label(): string {
		if ( defined( 'CBW_BRAND_OVERRIDE_LABEL' ) ) {
			return (string) CBW_BRAND_OVERRIDE_LABEL;
		}
		$v = (string) License::get_cap( 'brand_label', '' );
		return $v !== '' ? $v : self::NEUTRAL_LABEL;
	}

	public static function tagline(): string {
		if ( defined( 'CBW_BRAND_OVERRIDE_TAGLINE' ) ) {
			return (string) CBW_BRAND_OVERRIDE_TAGLINE;
		}
		return (string) License::get_cap( 'brand_tagline', self::NEUTRAL_TAGLINE );
	}

	public static function homepage(): string {
		if ( defined( 'CBW_BRAND_OVERRIDE_HOMEPAGE' ) ) {
			return (string) CBW_BRAND_OVERRIDE_HOMEPAGE;
		}
		return (string) License::get_cap( 'brand_homepage', self::NEUTRAL_HOMEPAGE );
	}

	public static function support_email(): string {
		if ( defined( 'CBW_BRAND_OVERRIDE_SUPPORT' ) ) {
			return (string) CBW_BRAND_OVERRIDE_SUPPORT;
		}
		return (string) License::get_cap( 'support_email', self::NEUTRAL_SUPPORT );
	}

	public static function logo_url(): string {
		if ( defined( 'CBW_BRAND_OVERRIDE_LOGO' ) ) {
			return (string) CBW_BRAND_OVERRIDE_LOGO;
		}
		$url = (string) License::get_cap( 'logo_url', '' );
		if ( $url ) {
			return $url;
		}
		// Neutral built-in fallback — a generic chat bubble, no branding.
		return CBW_PLUGIN_URL . 'assets/images/logo-neutral.svg';
	}

	/**
	 * For Lifetime tier with a custom-domain license — overrides the
	 * Licenseistic validation host. Pro/Agency/Free hit chatbotistic.com.
	 */
	public static function license_base_url(): string {
		if ( defined( 'CBW_BRAND_OVERRIDE_LICENSE_BASE' ) ) {
			return rtrim( (string) CBW_BRAND_OVERRIDE_LICENSE_BASE, '/' );
		}
		$custom = (string) License::get_cap( 'custom_domain', '' );
		if ( $custom ) {
			$custom = rtrim( $custom, '/' );
			if ( ! preg_match( '#^https?://#', $custom ) ) {
				$custom = 'https://' . $custom;
			}
			return $custom . '/wp-json/licenseistic/v1';
		}
		return defined( 'CBW_LICENSE_BASE_URL' ) ? CBW_LICENSE_BASE_URL : 'https://chatbotistic.com/wp-json/licenseistic/v1';
	}

	/**
	 * Same idea for the Tochat-API host — Lifetime custom-domain installs
	 * may proxy through their own subdomain.
	 */
	public static function api_base_url(): string {
		if ( defined( 'CBW_BRAND_OVERRIDE_API_BASE' ) ) {
			return rtrim( (string) CBW_BRAND_OVERRIDE_API_BASE, '/' );
		}
		$custom = (string) License::get_cap( 'api_base_url', '' );
		if ( $custom ) {
			return rtrim( $custom, '/' );
		}
		return defined( 'CBW_API_BASE' ) ? CBW_API_BASE : 'https://services.tochat.be';
	}
}
