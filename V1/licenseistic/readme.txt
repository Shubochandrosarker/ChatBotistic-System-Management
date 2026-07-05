=== Licenseistic ===
Contributors: wpistic
Tags: license, license manager, software license, license key, serial key, activation, saas, plugin license, theme license
Requires at least: 5.8
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Secure WordPress license management for plugins, themes, SaaS, digital products, and agencies. REST API, activation tracking, customer dashboard.

== Description ==

Licenseistic is a standalone WordPress license management engine. It provides a complete solution for generating, distributing, validating, and tracking software license keys.

Features:

* Manual license creation with full admin control.
* Bulk license generation with configurable generators.
* Secure license storage using HMAC hashing and AES encryption.
* REST API endpoints for verify, activate, deactivate, ping, and status.
* Activation tracking with site URL, instance ID, and last-ping timestamps.
* Customer dashboard shortcode `[licenseistic_dashboard]`.
* API keys for external integrations.
* Comprehensive event logging.
* Extensible via actions and filters.

Licenseistic Core works independently. WooCommerce and Paid Memberships Pro integrations are available as separate addons.

== Installation ==

1. Upload the `licenseistic` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the Plugins menu in WordPress.
3. Go to the Licenseistic menu to configure settings, products, generators, and API keys.

== Frequently Asked Questions ==

= Does this plugin require WooCommerce? =

No. Licenseistic Core works independently. WooCommerce support is available via the separate Licenseistic for WooCommerce addon.

= Are license keys stored securely? =

Yes. Plain license keys are never stored. Each key is hashed with HMAC-SHA256 for lookup and AES encrypted for secure display.

== Changelog ==

= 1.1.0 =
* Adds the `/entitlements` endpoint and per-plan caps (tier, plan_name, max_widgets/agents/domains, white_label) sourced from the Memberistic→Licenseistic bridge envelope, so client plugins can unlock features by plan.
* Short alias routes (`/activate`, `/deactivate`, `/heartbeat`, `/validate`) alongside the canonical `/license/*` routes.

= 1.0.0 =
* Initial release.
