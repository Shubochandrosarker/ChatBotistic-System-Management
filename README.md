# ChatBotistic — Complete System Management

The complete Chatbotistic ecosystem: the chatbotistic.com WordPress
site and membership/licensing chain, the standalone
**Chatbotistic Dashboard** app (`dashboard.chatbotistic.com`), and the
supporting WordPressistic plugin family (booking, analytics, contact
forms). Chatbotistic itself is a white-labeled front end for
[Tochat.be](https://tochat.be) (WhatsApp chat widgets, agents, leads,
bookings, campaigns).

See `docs/CHATBOTISTIC-DASHBOARD-MASTER-PLAN.md` for the full product
plan, feature roadmap, and business model behind this V2 release.

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
| **`chatbotistic-dashboard/`** | **New in V2** — standalone Next.js 16 + Supabase multi-tenant SaaS dashboard | dashboard.chatbotistic.com |
| `V1/` | Frozen snapshot of the entire system before the V2 release. Reference/rollback only — do not develop against it. | — |

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

## Chatbotistic Dashboard (V2)

A standalone SaaS dashboard where customers manage everything in one
place — widgets (with a live visual preview editor), landing pages,
booking forms, leads with ad attribution, a shared team inbox, WhatsApp
campaigns with message-quota plans, and full white-label/agency
resale. WordPress stays the marketing site, billing (Memberistic), and
license server; the dashboard app is where the day-to-day product work
happens, reached via an HMAC SSO bridge from chatbotistic.com.

See `chatbotistic-dashboard/README.md` for setup, environment
variables, Supabase migrations, and architecture.

## Current pricing (chatbotistic-profile)

| Plan | Price | Widgets | Agents | Domains | Seats | Messages/mo |
|---|---|---|---|---|---|---|
| Free Forever | $0 | 1 | 1 | 1 | 1 | 100 |
| Starter | $19/mo ($190/yr) | 3 | 5 | 3 | 2 | 1,000 |
| Growth | $49/mo ($490/yr) | 10 | 20 | 10 | 5 | 5,000 |
| Agency | $149/mo ($1,490/yr) | 30 | Unlimited | 50 | 15 | 25,000 |
| Lifetime | Legacy, contact-only | — | — | — | — | — |

## V1 → V2 changes

- New standalone dashboard app (`chatbotistic-dashboard/`).
- Theme: dashboard CTAs, new pricing tiers, in-app docs guide grid, v1.3.0.
- `chatbotistic-profile`: branded HTML email suite for every automated
  send (welcome, purchase receipt, license status, contact-form
  auto-reply, newsletter welcome), v1.2.0.
- `chatbotistic-widget`: encrypted Tochat.be credential storage
  (previously plaintext), with transparent migration, v1.2.0.
- `chatbotistic-connector`: `dashboard_url` setting + member-facing
  link to the new dashboard app, v3.2.0.
- `V1/` — the entire pre-V2 system, archived for rollback/reference.

## Related repositories

- **WPistic-WhatsApp-CRM** — a separate, sibling WhatsApp CRM product.
  Its multi-tenant architecture (org RLS, HMAC SSO, provider-strategy
  messaging, encrypted credentials) served as the reference design for
  the Chatbotistic Dashboard, but the two products share no code.
- **chatbotistic-saas-connector** — a distribution-only mirror of the
  four WordPress plugins in this repo (`chatbotistic-connector`,
  `chatbotistic-profile`, `chatbotistic-widget`,
  `memberistic-licenseistic-bridge`), packaged as installable ZIPs.
  This repo is the canonical source; that repo is sync'd from it.
