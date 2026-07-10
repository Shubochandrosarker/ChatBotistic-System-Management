# ChatBotistic — Complete System Management

The complete Chatbotistic ecosystem: the chatbotistic.com WordPress
site and membership/licensing chain, the standalone **customer
dashboard app** (`chatbot.wpistic.cloud` — a Chatbotistic-branded
white-label of the [WPistic WhatsApp CRM](https://github.com/Shubochandrosarker/WPistic-WhatsApp-CRM)
codebase, kept in its own `ChatBotistic-App` repo, not in this one),
and the supporting WordPressistic plugin family (booking, analytics,
contact forms). Chatbotistic itself is a white-labeled front end for
[Tochat.be](https://tochat.be) (WhatsApp chat widgets, agents, leads,
bookings, campaigns).

See `docs/CHATBOTISTIC-DASHBOARD-MASTER-PLAN.md` for the product plan
and business model behind the V2 release (the master plan predates the
`chatbot.wpistic.cloud` dashboard decision — treat its own dashboard
architecture section as historical, not current).

**Installing?** Read [INSTALL.md](INSTALL.md) first — in particular,
`chatbotistic/` is a **theme** (upload under Appearance → Themes), not a
plugin, and the plugins have a recommended activation order.

## What's in this repo

| Folder | What it is | Runs on |
|---|---|---|
| `chatbotistic/` | Marketing site + member-portal theme (pricing, docs, checkout) | chatbotistic.com |
| `chatbotistic-connector/` | Member dashboard plugin — widgets/agents/FAQs/leads/analytics via Tochat.be | chatbotistic.com |
| `chatbotistic-profile/` | One-install config: plans, branded pages, branded transactional emails | chatbotistic.com |
| `chatbotistic-widget/` | Customer-installed widget plugin (license-gated, white-label, encrypted credentials) | Customer WP sites |
| `licenseistic/` | License key server (REST API, activation tracking) | chatbotistic.com |
| `memberistic-membership-solutions/` | Membership/billing engine (Stripe/WooCommerce) | chatbotistic.com |
| `memberistic-licenseistic-bridge/` | Auto-issues/revokes licenses from membership events, carries plan caps | chatbotistic.com |
| `insightistic/` | GA4/Search Console/PageSpeed analytics + AI insights (standalone WordPressistic product) | Any WP site |
| `wpistic-bookingistic-main/` | Booking/calendar/email-automation engine | Any WP site |
| `wpistic-contact-form-main/` | Universal form inbox + AI (autoresponder, newsletter) | Any WP site |
| `V1/` | Frozen snapshot of the entire system before the V2 release. Reference/rollback only — do not develop against it. | — |

The customer dashboard app (widgets, leads, campaigns, team inbox) is
**not** in this repo — it lives in `ChatBotistic-App`, a separate repo
deployed at `chatbot.wpistic.cloud`. See "Chatbotistic Dashboard"
below.

## The entitlement chain

```
memberistic-membership-solutions (billing/plans)
        │  memberistic_membership_activated
        ▼
memberistic-licenseistic-bridge  ── issues/revokes a license, writes plan
        │                            caps + white-label brand fields
        ▼
licenseistic  (REST license server, /wp-json/licenseistic/v1/*)
        │  /activate  /heartbeat  /deactivate  /entitlements
        ▼
chatbotistic-widget  (customer's site — renders the widget, enforces caps)
        │
        ▼
Tochat.be  (services.tochat.be — the actual widget/lead/booking backend)
```

`chatbotistic-connector` offers the same widget/agent/lead management
inside chatbotistic.com's own member portal; `chatbotistic-profile` is
what turns a stock Memberistic install into "chatbotistic.com" (plans,
pages, branded emails).

## Chatbotistic Dashboard

A standalone SaaS dashboard where customers manage everything in one
place — a shared inbox, contacts, sales pipelines, broadcasts, and
no-code automations, gated by plan/license entitlements. WordPress
stays the marketing site, billing (Memberistic), and license server;
the dashboard app is where the day-to-day product work happens,
reached via an HMAC SSO bridge from chatbotistic.com
(`chatbotistic-profile/includes/class-sso-bridge.php`, hooked onto the
theme's `cb_dashboard_url` filter).

The dashboard app itself is `ChatBotistic-App`
(github.com/Shubochandrosarker/ChatBotistic-App) — a Chatbotistic
white-label of the WPistic WhatsApp CRM codebase — deployed on
Hostinger hPanel at `chatbot.wpistic.cloud`. See that repo's
`README.md` and `DEPLOY.md` for setup, environment variables, Supabase
migrations, and the deploy process.

An earlier, unrelated dashboard build (`chatbotistic-dashboard/`, a
Next.js + Supabase app targeting `dashboard.chatbotistic.com`) lived in
this repo but was never deployed anywhere; it has been removed in
favor of the `chatbot.wpistic.cloud` app above.

## Current pricing (chatbotistic-profile)

| Plan | Price | Widgets | Agents | Domains | Seats | Messages/mo |
|---|---|---|---|---|---|---|
| Free Forever | $0 | 1 | 1 | 1 | 1 | 100 |
| Starter | $19/mo ($190/yr) | 3 | 5 | 3 | 2 | 1,000 |
| Growth | $49/mo ($490/yr) | 10 | 20 | 10 | 5 | 5,000 |
| Agency | $149/mo ($1,490/yr) | 30 | Unlimited | 50 | 15 | 25,000 |
| Lifetime | Legacy, contact-only | — | — | — | — | — |

## V1 → V2 changes

- Theme: dashboard CTAs, new pricing tiers, in-app docs guide grid,
  light-default/dark-toggle redesign, animated UI.
- `chatbotistic-profile`: branded HTML email suite for every automated
  send (welcome, purchase receipt, license status, contact-form
  auto-reply, newsletter welcome); SSO token-minting bridge to the
  dashboard app; corrected 5-tier plan definitions.
- `chatbotistic-widget`: encrypted Tochat.be credential storage
  (previously plaintext), with transparent migration.
- `chatbotistic-connector`: `dashboard_url` setting + member-facing
  link to the dashboard app.
- `memberistic-membership-solutions`: `waiver_enabled` and
  `checkins_enabled` settings so gym/front-desk-oriented features
  (waiver tracking, Check-Ins, the Staff Dashboard page) can be hidden
  for businesses that don't need them — off by default for
  Chatbotistic.
- `V1/` — the entire pre-V2 system, archived for rollback/reference.

## Related repositories

- **ChatBotistic-App** — the standalone customer dashboard app
  (`chatbot.wpistic.cloud`). A Chatbotistic white-label of the WPistic
  WhatsApp CRM codebase. Separate repo, separate deploy (Hostinger
  hPanel) — see its own README/DEPLOY.md.
- **WPistic-WhatsApp-CRM** — the sibling WhatsApp CRM product
  `ChatBotistic-App` is white-labeled from. Its multi-tenant
  architecture (org RLS, HMAC SSO, provider-strategy messaging,
  encrypted credentials) is the shared engine both products build on.
- **chatbotistic-saas-connector** — a distribution-only mirror of the
  four WordPress plugins in this repo (`chatbotistic-connector`,
  `chatbotistic-profile`, `chatbotistic-widget`,
  `memberistic-licenseistic-bridge`), packaged as installable ZIPs.
  This repo is the canonical source; that repo is sync'd from it.
