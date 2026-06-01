<?php
/**
 * Bootstrap.
 *
 * @package Chatbotistic\Profile
 */

namespace Chatbotistic\Profile;

defined( 'ABSPATH' ) || exit;

class Plugin {

	public function boot(): void {
		// Page slug override — applied to Memberistic's required-pages map.
		Pages::register_slug_overrides();

		// Override the seeded plans on first install via Memberistic's filter.
		Plans::register_default_plans_filter();

		// Override every transactional email body + subject to use chatbotistic.com URLs.
		Emails::register();

		// Auto-approve memberships when payment recorded; auto-activate Free plans on creation.
		Auto_Approve::register();

		// Hide waiver UI surfaces (CSS + email-template filter).
		Stripper::register();

		// Sync bridge cap defaults to the new 4 plans whenever an admin loads
		// our settings page (cheap, idempotent).
		if ( is_admin() ) {
			( new Admin() )->register();
		}
	}
}
