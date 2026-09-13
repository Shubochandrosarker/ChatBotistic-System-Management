# Chatbotistic Stack — server-side SaaS plugins

The complete WordPress side of the Chatbotistic SaaS: membership →
license → Tochat.be widget management, plus the customer-side widget
plugin that gets installed on end-user WordPress sites. Four plugins,
three of them run on your chatbotistic.com site, one ships to customers.

This repository is the canonical WordPress source for Chatbotistic's
marketing, member, connector, and customer widget plugins. The generated
release mirror is `Shubochandrosarker/chatbotistic-saas-connector`.

```
┌──────────────────── chatbotistic.com (your SaaS site) ────────────────────┐
│                                                                            │
│  Memberistic ──┐                                                           │
│   (5 plans)    │    Memberistic → Licenseistic Bridge                      │
│                │    (auto-issues a license key on plan activation,         │
│                │     revokes on cancel, enriches activation response       │
│                │     with tier / plan_name / max_widgets / max_agents /    │
│                │     max_domains / max_seats / message quota)              │
│                ▼                                                           │
│            Licenseistic ──── REST /wp-json/licenseistic/v1/* ──┐           │
│                                                                │           │
│  Chatbotistic Connector ──── Tochat.be REST API                │           │
│   ([chatbotistic_dashboard] shortcode, admin Settings/Log/Plugins,         │
│    links out to the standalone dashboard app)                              │
│                                                                            │
└────────────────────────────────────────────────────────────────┼───────────┘
                                                                 │
┌─────────────────── Customer's own WordPress site ──────────────┼───────────┐
│                                                                ▼           │
│  Chatbotistic Widget plugin ── POST /activate, /heartbeat, /deactivate     │
│   (embeds widget, renders analytics, validates key per domain,             │
│    stores its Tochat credentials AES-256-CBC encrypted at rest)            │
└────────────────────────────────────────────────────────────────────────────┘

┌───────────────── app.chatbotistic.com (separate app) ───────────────────────┐
│  Next.js 16 + Supabase multi-tenant dashboard — widgets, landing pages,   │
│  leads, campaigns, team inbox, white-label. Lives in                      │
│  ChatBotistic-App (github.com/Shubochandrosarker/ChatBotistic-App),       │
│  not in this repo. SSO'd in from the WordPress side above.                │
└────────────────────────────────────────────────────────────────────────────┘
```

### Domain map (canonical)

| Domain | Role |
|---|---|
| `www.chatbotistic.com` | Marketing site / SaaS (WordPress + Memberistic + Licenseistic) |
| `app.chatbotistic.com` | Customer-facing dashboard and branded widget-loader façade. |
| `services.tochat.be` | Provider backend API and public widget source. |

## What's in this repo

| Folder | Runs on | What it does | Version |
|---|---|---|---|
| `chatbotistic-connector/` | Your SaaS site | Front-end member dashboard. `[chatbotistic_dashboard]` shortcode. Talks to the white-label API at services.tochat.be. Memberistic-gated. Also links members to the standalone dashboard app. | 3.4.0 |
| `chatbotistic-profile/` | Your SaaS site | One-install configuration profile — replaces Memberistic's default plans with the 5 Chatbotistic plans, creates branded pages, sends every transactional email through a branded HTML template (welcome, purchase receipt, license status, enquiry auto-reply, newsletter welcome), auto-approves on payment, auto-activates Free signups. Mints SSO tokens to bridge members into the dashboard app. | 1.3.5 |
| `chatbotistic-widget/` | Customer sites | Customer's own WP plugin. Validates a WPistic license, pulls only the customer's own widget catalog, embeds licensed widgets, and shows strictly scoped analytics. Tochat credentials are encrypted at rest (AES-256-CBC), with transparent migration from any legacy plaintext value. | 1.4.0 |
| `memberistic-licenseistic-bridge/` | Your SaaS site | Auto-issues a Licenseistic license on Memberistic activation, revokes on cancel, enriches license activation responses with per-plan caps for the widget plugin. | 1.2.3 |
| `dist/` | — | Built installable ZIPs of all four. |
| `V1/` | — | Frozen snapshot of the original distribution (pre-V2), for rollback/reference only. Do not develop against it. |

The two foundation plugins (**Memberistic** and **Licenseistic**) are
not vendored here — they are the user's existing plugins.

## Current plans (chatbotistic-profile)

| Plan | Price | Widgets | Agents | Domains | Seats | Messages/mo |
|---|---|---|---|---|---|---|
| Free Forever | $0 | 1 | 1 | 1 | 1 | 100 |
| Starter | $19/mo | 3 | 5 | 3 | 2 | 1,000 |
| Growth | $49/mo | 10 | 20 | 10 | 5 | 5,000 |
| Agency | $149/mo | 30 | Unlimited | 50 | 15 | 25,000 |
| Lifetime | Legacy, contact-only | — | — | — | — | — |

## Install order

1. **Memberistic** (existing) — define your plans (Free Forever, Starter, Growth, Agency).
2. **Licenseistic** (existing).
3. **`dist/memberistic-licenseistic-bridge.zip`** — Settings → M → L Bridge.
   Map each Memberistic plan to its caps (see table above).
4. **`dist/chatbotistic-profile.zip`** — one-click seeds the plans, pages,
   and branded emails above.
5. **`dist/chatbotistic-connector.zip`** — Chatbotistic → Settings → enter
   your white-label account email + password, and (optionally) override
   the `dashboard_url` setting if you're self-hosting the dashboard app
   somewhere other than `https://app.chatbotistic.com`.
   Place `[chatbotistic_dashboard]` on a members-only page.
6. **`dist/chatbotistic-widget.zip`** — give this to customers. They
   install on their own WordPress site, paste their license key from the
   email the Bridge sends after checkout, and the widget activates.

## What's new in V2

- **Standalone dashboard app** (`app.chatbotistic.com`) — see the
  `chatbotistic-dashboard/` folder in `ChatBotistic-Complete-System-Management`.
  This repo's connector plugin now links members to it alongside its own
  in-page shortcode dashboard.
- **Branded HTML email suite** — every automated email (welcome, purchase
  receipt, license status, contact-form auto-reply, newsletter welcome)
  now renders through a shared, inline-CSS, WhatsApp-green branded
  template instead of plain text.
- **Security fix** — `chatbotistic-widget` previously stored its Tochat.be
  account password in plaintext (`cbw_tochat_password`); it's now
  encrypted at rest with the same AES-256-CBC scheme the connector uses,
  with transparent migration for existing installs.
- **New pricing** — 5 tiers (Free Forever/Starter/Growth/Agency/Lifetime)
  with explicit per-plan monthly message quotas, replacing the old
  Free/Pro/Agency/Lifetime lineup.

## What's new in this sync

- **Domains repointed to the Chatbotistic brand** (all plugins) —
  the white-label dashboard is `app.chatbotistic.com` and the provider API
  remains `services.tochat.be`. The connector
  ships a one-time migration rewriting any stored `dashboard_url`
  still pointing at the retired wpistic.cloud deployment.
- **Analytics strictly per-customer** (`chatbotistic-widget`) — the
  analytics page previously queried whatever widget UUID arrived via
  `?widget=`, so with a shared account connected it could render other
  customers' stats. The requested key is now validated against the
  site's own allowed set (configured keys + license catalog) before
  any API call.
- **SSO secret no longer embedded in admin HTML** (`chatbotistic-profile`) —
  the auto-generated SSO secret was printed inline in the admin
  notice; it is now revealed only through a nonce-gated AJAX action.

- **Dashboard app repointed to `app.chatbotistic.com`** (`chatbotistic-profile`,
  `chatbotistic-widget`, `chatbotistic-connector`) — the real dashboard app
  is `ChatBotistic-App` (a Chatbotistic white-label of WPistic WhatsApp CRM),
  deployed on Hostinger hPanel — not the retired CRM or wpistic.cloud aliases. Every
  hard-coded fallback default repointed.
- **SSO login redirect fixed** (`chatbotistic-profile`) — `SSO_Bridge::filter_dashboard_url()`
  was appending `?token=` to the bare dashboard URL instead of routing to
  the app's `/api/sso/login` route, which is the only place that reads the
  token. Every "Open Full Dashboard" click silently discarded the token
  and left the member looking logged out. Now always targets
  `{scheme}://{host}/api/sso/login`.
- **Dead-domain fix (historical)** (`chatbotistic-connector`, `chatbotistic-widget`) —
  `CBC_APP_BASE` / `CBW_APP_BASE_URL` and every user-facing "manage widgets
  in…" hint now resolves to the canonical `app.chatbotistic.com` dashboard;
  provider API traffic remains on `services.tochat.be`.
- **Widget embed snippet fix** (`chatbotistic-connector`, widget plugin) —
  all install surfaces now emit the branded
  `/install-widget/bundle.js?key=` loader. The app proxies the public widget
  script from `services.tochat.be` without moving authenticated API traffic
  onto the dashboard origin.
- **`cb_dashboard_url` filter restored** (`chatbotistic-connector`) — the
  member portal's "Open Full Dashboard" link had regressed to a plain,
  unfiltered URL, silently dropping SSO-token minting and white-label
  overrides for portal users.
- **REST health check completeness** (`chatbotistic-profile`) — the
  System Health screen claimed to verify `/activate`, `/heartbeat`, and
  `/entitlements` but only actually checked two of the three.
- **Waiver-hiding selectors updated** (`chatbotistic-profile`) — Stripper's
  CSS pointed at Memberistic admin classes that no longer exist post
  rebuild; repointed at the current `data-stat-key`/`.mb-table__col--waiver`
  hooks (Memberistic itself now also gates this natively via a real
  `waiver_enabled` setting, so this is a belt-and-suspenders layer, not
  the primary fix).
- **SSO token minting bridge** (`chatbotistic-profile`) — mints signed SSO
  tokens so members land pre-authenticated in the standalone dashboard app,
  plus corrected 5-tier plan definitions and a permanent (no longer
  one-time) SSO-secret reveal UI in Settings.
- **Plan cap fix** (`memberistic-licenseistic-bridge`) — updated default
  plan caps to match the corrected pricing tiers, and fixed a broken/
  duplicate Save button on the mapping screen.
- **Analytics picker fix** (`chatbotistic-widget`) — the analytics widget
  picker now shows friendly widget names instead of raw UUIDs.

## Connector features

- Widget/agent/FAQ CRUD, 30-day analytics, lead-capture forms, leads list
- Lead Export API key (separate auth) — Settings → Lead Export API Key
- Tochat API methods: widgets, whatsapp_operators, faq_grps, stats,
  campaigns, audiences (many_contacts), booking_configs, banners,
  widget_rules, payment_links, transactions, `get-json-lead`
- Admin **Plugins** page — live status of the plugin stack
- `uninstall.php` — clean tear-down

## Building the ZIPs

```bash
for p in chatbotistic-connector chatbotistic-profile chatbotistic-widget memberistic-licenseistic-bridge; do
  zip -rq "dist/${p}.zip" "$p" -x '*.git*'
done
```

## License

GPL v2 or later (see `LICENSE`).
