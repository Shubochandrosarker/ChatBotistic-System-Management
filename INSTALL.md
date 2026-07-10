# Installing the Chatbotistic system

The #1 install mistake with this repo: **`chatbotistic/` is a WordPress
_theme_, not a plugin.** If you upload it under *Plugins → Add New →
Upload*, WordPress rejects it with "No valid plugins were found." Upload
it under **Appearance → Themes → Add New → Upload Theme** instead.
Everything else in this repo is a normal plugin. The standalone
customer dashboard app is a separate repo (`ChatBotistic-App`) with its
own deploy process — see "Chatbotistic Dashboard" below.

## Requirements

- WordPress 6.4+, PHP 8.0+ (8.1+ recommended)

## chatbotistic.com (the SaaS/marketing site)

Install in this order — later items detect earlier ones and self-configure:

| # | Component | Install as | Notes |
|---|-----------|-----------|-------|
| 1 | `memberistic-membership-solutions/` | Plugin | Billing/plans engine. Activate first. |
| 2 | `licenseistic/` | Plugin | License key REST server. |
| 3 | `memberistic-licenseistic-bridge/` | Plugin | Needs #1 and #2 active (shows an admin notice, not a fatal, if they're missing). |
| 4 | `chatbotistic/` | **Theme** (Appearance → Themes) | Provisions the marketing pages + member portal on activation. |
| 5 | `chatbotistic-profile/` | Plugin | Turns stock Memberistic into "chatbotistic.com": plans, branded pages/emails, dashboard SSO bridge. |
| 6 | `chatbotistic-connector/` | Plugin | Member widget/agent/lead dashboard backed by Tochat.be. Configure API credentials under **Chatbotistic → Settings**. |

Optional on any site: `insightistic/`, `wpistic-bookingistic-main/`,
`wpistic-contact-form-main/`.

> Packaging tip: when zipping a plugin for upload, zip the plugin folder
> itself (e.g. `chatbotistic-connector/`) so the zip contains one
> top-level folder. The two `-main` suffixed folders work as-is; if you
> rename folders, keep the canonical names listed above, because
> `chatbotistic-profile` keys its auto-repair on them.

## Customer sites (your users)

Customers install **only** `chatbotistic-widget/` (Plugin). They need:

1. A license key (issued automatically by the bridge when their
   membership activates — visible in their account on chatbotistic.com).
2. Activate the key under **Chatbotistic Widget → License**.
3. Pick a default widget under **Chatbotistic Widget → Widgets** — the
   dropdown fills itself from their account's widget catalog once the
   license is active. Optional per-page / per-URL overrides live on the
   same screen.

The plugin then prints the loader
(`https://services.tochat.be/widget/<key>/load.js`) on the public site.
No theme edits, no code snippets required.

## Chatbotistic Dashboard (`ChatBotistic-App`, separate repo)

The standalone dashboard app served at `chatbot.wpistic.cloud` lives in
its own repo (github.com/Shubochandrosarker/ChatBotistic-App) and
deploys to Hostinger hPanel — see that repo's `README.md` and
`DEPLOY.md` for the full build/deploy process; it isn't part of this
repo and doesn't run alongside it locally.

`SSO_SHARED_SECRET` on that app must equal the WordPress side's secret
(`CB_SSO_SHARED_SECRET` in wp-config.php, or the auto-generated one
surfaced by chatbotistic-profile's admin notice). With that in place,
every "Open Dashboard" link on chatbotistic.com carries a short-lived
signed token to `{dashboard}/api/sso/login`, which verifies it,
provisions/updates the member's org from their live plan + license, and
lands them in the dashboard already signed in.

## Troubleshooting installs

- **"No valid plugins were found"** — you uploaded the `chatbotistic/`
  theme as a plugin (see the top of this page), or your zip has a
  nested/double folder. Re-zip so the plugin's main `.php` file sits one
  folder deep.
- **Bridge/profile shows a notice instead of activating features** —
  activate `memberistic-membership-solutions` (and `licenseistic` for
  the bridge) first, then re-activate.
- **`V1/` folder** — frozen pre-V2 snapshot. Never install anything from
  it; it exists for reference/rollback only.
