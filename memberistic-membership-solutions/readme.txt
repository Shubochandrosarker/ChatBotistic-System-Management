=== Memberistic Membership Solutions ===
Contributors: wordpressistic
Tags: membership, operations, staff dashboard, stripe, woocommerce, rest api
Requires at least: 6.0
Requires PHP: 8.0
Tested up to: 6.6
Stable tag: 1.12.2
License: GPLv2 or later

A modern membership operations engine for service and SaaS businesses.

== Description ==

Memberistic is a generic membership operations engine designed to be reskinned by a host product (Chatbotistic, future WordPressistic sub-brands, or any third-party SaaS / service business). It runs the full lifecycle of a membership — plans, signup, payment, renewals, family / linked members, check-ins, waivers, staff dashboards, REST API, and Stripe + WooCommerce integration — from a single WordPress plugin.

Brand-specific text is controlled by `memberistic_settings` (and matching `memberistic_*` filters). The login subtitle, brand label on the digital member card, member-ID prefix, QR verification header, and whether the account template shows the booking / lane / check-in tools are all configurable per install — no template edits required.

Highlights:

* Custom database with 10 dedicated tables.
* Ten membership statuses including suspended and needs-review.
* Linked / family-member CRUD with per-person waiver, phone, DOB, relationship, and history.
* React-driven admin: Dashboard, Members, Plans, Payments, Check-Ins, Activity, Settings, Emails, Integrations.
* 14 frontend shortcodes and auto-mapped branded pages.
* Stripe Checkout for monthly + annual subscriptions, with full webhook handling.
* WooCommerce bridge with auto-created hidden products and signed `/webhooks/woocommerce` route.
* 13 transactional email templates, 20 merge tags, three daily cron jobs (renewal reminders, auto-expire, waiver follow-up).
* REST API under `/wp-json/memberistic/v1/` with 30+ endpoints.
* Six custom staff roles plus admin: Manager, Staff, Cashier, Instructor, KIOSK Operator, POS Staff.
* Content-restriction overlay for plan-gated posts and pages.

Built by [WordPressistic](https://www.wordpressistic.com).

== Installation ==

1. Upload `memberistic-membership-solutions/` to `wp-content/plugins/`.
2. Activate Memberistic Membership Solutions in WordPress admin.
3. Open Memberistic > Settings and save your brand label, business name, currency, Stripe keys, and (optionally) WooCommerce settings.
4. Use Memberistic > Tools > Page Mapping to create the branded frontend pages.
5. (Optional) Install a host-product profile plugin (e.g. Chatbotistic Profile) to auto-configure brand label, plans, and pages.

The Stripe webhook URL is `https://YOUR-SITE/wp-json/memberistic/v1/webhooks/stripe`.

== Frequently Asked Questions ==

= Where is the documentation? =

See `README.md`, `docs/AUDIT_REPORT.md`, `docs/INSTALL.md`, and `docs/HOOKS.md` in the plugin directory.

= How do I rebrand the login page / member card / QR header? =

Set `brand_label`, `login_tagline`, `member_id_prefix`, and `qr_verification_label` in the `memberistic_settings` option, or hook the matching `memberistic_brand_label`, `memberistic_login_tagline`, `memberistic_member_id_prefix`, and `memberistic_qr_verification_label` filters. To hide the lane/range/check-in tiles on the account template, set `account_show_lane_tools` to `no` (or filter `memberistic_account_show_lane_tools`).

= How do I add a new email template? =

Filter `memberistic_email_templates` to register the template id and label, then filter `memberistic_email_template_subject` and `_body` for that id. All 20 documented merge tags are available via `strtr()`.

= Can I run cron jobs more often than daily? =

Yes — unschedule `memberistic_daily_renewal_reminders`, `memberistic_daily_expire_memberships`, and `memberistic_daily_waiver_followup` and re-schedule with your preferred cadence.

= How do I uninstall and clear data? =

Set Settings > Advanced > "Delete data on uninstall" to Yes before removing the plugin. Otherwise the tables remain in place for safe re-activation.

== Changelog ==

See CHANGELOG.md for the full history.

= 1.12.2 =
Dashboard reliability: every Memberistic REST endpoint now drains stray output buffers centrally before the response is serialised, so upstream plugin/theme notices can no longer corrupt the JSON body ("The response is not a valid JSON response."). Member login form posts directly to wp-login.php to avoid a redirect loop when a custom login page filters login_url.

= 1.10.0 =
Admin operations release. Members page gains server-side pagination, eight KPI cards (Total / Active / Pending / Past Due / Expired / Cancelled / New This Month with MoM growth % / Waiver Missing), and a bulk-action option to change waiver status for many memberships at once. Plans page is rebuilt as an animated card grid with per-plan member counts (total / active / other). Payments page gets pagination, six KPI cards (lifetime revenue, this-month revenue + MoM growth, new-member vs renewal payments, failed payments, visible-on-page), and a richer CSV export. Import flow now keeps every row: expired members import as expired, members with no matching plan import under a new "No Plan" sentinel plan with status = needs_review, members with no email still import. Order/payment imports never drop a row — orphan emails create a stub Instore member and emailless rows attach to a shared Instore Walk-in membership. Emails page becomes a React console with KPI cards (sent today/week/month, delivery rate, contact coverage) and a filterable, paginated directory; CSV export now includes 13 properly labelled columns including waiver dates and renewal info.

= 1.9.0 =
Admin-side waiver management. Each linked person row on the Members detail panel now has an inline Edit action that opens a full editor (full name, email, phone, relationship, waiver status, waiver signed/expires dates, member status) plus a Remove action for non-primary people. The `PUT /people/{id}` endpoint now accepts `waiver_signed_at` and `waiver_expires_at`, auto-stamps `waiver_signed_at` when the status flips to `signed`, and writes a `waiver_signed` / `waiver_expired` activity entry whenever waiver status changes. UI-only update — schema unchanged.

= 1.8.0 =
Member import: upload a Paid Memberships Pro members CSV or an orders export and bring the data into Memberistic with a dry-run preview before commit.

= 1.7.1 =
Documentation release.

= 1.7.0 =
Full audit pass against the canonical Memberistic feature spec. Default plans seed automatically. Email automation gets six new templates, full merge-tag support, an email log table, and daily cron jobs for renewal reminders, auto-expire, and waiver follow-up. Stripe `invoice.payment_succeeded` now extends renewal dates. WooCommerce bridge auto-creates the six hidden products, handles refund / cancellation, and exposes a signed `/webhooks/woocommerce` REST route. Adds `PUT /people/{id}` and `DELETE /people/{id}`. Members search adds 9 filter dimensions and matches linked-member names plus Stripe / Woo / POS customer IDs. Adds KIOSK Operator and POS Staff placeholder roles. Adds `suspended` and `needs_review` statuses end to end. New `memberistic_email_logs` and `memberistic_integrations` tables. Full audit report shipped in `docs/AUDIT_REPORT.md`.

= 1.6.0 =
Retired the legacy server-rendered views. Email automation foundation, saved-filter views, React Settings console.

= 1.5.6 =
Membership user roles, plan-specific roles, and post / page restriction controls; modern restriction overlay.

= 1.5.0 =
Online Stripe memberships linked to WordPress users; lifecycle emails; Booking Engine + WooCommerce bridge foundations; expanded REST endpoints; reporting.

= 1.0.0 =
Initial Phase 1 foundation build.
