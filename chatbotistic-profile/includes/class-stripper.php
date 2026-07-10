<?php
/**
 * Hide waiver UI surfaces from Memberistic admin (Chatbotistic doesn't sell
 * shooting-range memberships — waiver is irrelevant). Strictly non-destructive:
 * data columns stay, we just hide them from view and suppress the waiver
 * reminder email.
 *
 * @package Chatbotistic\Profile
 */

namespace Chatbotistic\Profile;

defined( 'ABSPATH' ) || exit;

class Stripper {

	public static function register(): void {
		// Suppress waiver email template entirely.
		add_filter( 'memberistic_email_templates', [ __CLASS__, 'drop_waiver_template' ], 20 );
		add_filter( 'memberistic_should_send_email', [ __CLASS__, 'block_waiver_send' ], 5, 2 );

		// Hide waiver columns + cards in the Memberistic admin pages.
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'admin_css' ] );

		// Disable the daily waiver-followup cron job.
		add_action( 'init', [ __CLASS__, 'disable_waiver_cron' ], 99 );

		// Strip waiver-related capabilities so role admin pages don't show them.
		add_filter( 'memberistic_capabilities', [ __CLASS__, 'drop_waiver_caps' ], 20 );
	}

	public static function drop_waiver_template( array $templates ): array {
		return array_values( array_filter( $templates, function ( $row ) {
			return ( $row['id'] ?? '' ) !== 'waiver_missing';
		} ) );
	}

	public static function block_waiver_send( $should_send, $template = '' ) {
		if ( 'waiver_missing' === $template ) {
			return false;
		}
		return $should_send;
	}

	public static function admin_css( string $hook ): void {
		if ( false === strpos( $hook, 'memberistic' ) ) {
			return;
		}
		wp_register_style( 'cbp-hide-waiver', false, [], CBP_VERSION );
		wp_enqueue_style( 'cbp-hide-waiver' );
		$css = '
			[data-stat-key="waiver_missing"],
			.mb-table__col--waiver,
			.mb-filter--waiver,
			a[href*="page=memberistic-waivers"],
			label[for*="waiver"],
			input[name*="waiver"],
			select[name*="waiver"] {
				display: none !important;
			}
			/* Hide the duplicate top "Save Changes" button on Memberistic
			   Settings — keep only the bottom one. */
			.memberistic-settings-page .submit:first-of-type,
			form#memberistic-settings-form > .submit:first-of-type,
			.memberistic-page-settings .button-primary:first-of-type:not(:last-of-type) {
				display: none !important;
			}
		';
		wp_add_inline_style( 'cbp-hide-waiver', $css );
	}

	public static function disable_waiver_cron(): void {
		$ts = wp_next_scheduled( 'memberistic_daily_waiver_followup' );
		if ( $ts ) {
			wp_unschedule_event( $ts, 'memberistic_daily_waiver_followup' );
		}
	}

	public static function drop_waiver_caps( array $caps ): array {
		return array_values( array_filter( $caps, function ( $cap ) {
			return false === stripos( (string) $cap, 'waiver' );
		} ) );
	}
}
