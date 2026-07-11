# Chatbotistic Dashboard — Master Plan

**Audit of all 3 repositories + complete feature plan + business model for $5M/yr ARR**

Date: 2026-07-05 · Prepared from a full code audit of:

1. `ChatBotistic-Complete-System-Management` (WordPress ecosystem: theme + 9 plugins)
2. `WPistic-WhatsApp-CRM` (Next.js 16 + Supabase multi-tenant CRM — the architecture template)
3. `chatbotistic-saas-connector` (trimmed 4-plugin distribution + built ZIPs)

---

## Part 1 — Audit Summary

### 1.1 What exists today

**Repo 1: ChatBotistic-Complete-System-Management** — the full WordPress ecosystem:

| Component | Version | Role | Maturity |
|---|---|---|---|
| `chatbotistic` (theme) | 1.2.5 | Marketing site + member portal on chatbotistic.com | Production |
| `chatbotistic-connector` | 3.1.1 | Member dashboard (shortcode) → Tochat.be API (widgets/agents/FAQs/leads/analytics) | Production |
| `chatbotistic-profile` | 1.1.2 | One-install config: 4 plans, branded pages/emails, auto-approve | Production |
| `chatbotistic-widget` | 1.1.2 | Customer-installed widget plugin (license-gated, white-label) | Production |
| `licenseistic` | 1.1.0 | License server (REST `licenseistic/v1`, 8 tables) | Production |
| `memberistic-membership-solutions` | 1.12.2 | Membership engine (Stripe/Woo, 10 tables) | Production |
| `memberistic-licenseistic-bridge` | 1.2.1 | Membership → license auto-issue + plan caps/brand envelope | Production |
| `insightistic` | 3.3.0 | GA4/GSC/PageSpeed + AI insights (standalone product) | Production |
| `wpistic-bookingistic-main` | 2.0.0 | Full booking system (11 tables, REST `bookingistic/v1`) | Production |
| `wpistic-contact-form-main` | 1.5.1 | Universal form inbox + AI (7 tables, webhooks) | Production |

**Repo 2: WPistic-WhatsApp-CRM** — Next.js 16 / React 19 / Supabase / Tailwind v4 / shadcn multi-tenant CRM:

- **Complete:** shared team inbox (realtime), contacts (+tags/custom fields/CSV import), pipelines (Kanban), broadcasts (WhatsApp templates + SMS), no-code automations (branches/waits/webhooks/AI reply), RAG knowledge base (pgvector + Cloudflare Workers AI), 3-provider messaging abstraction (Meta Cloud API / Twilio / Jasmin SMS) with AES-256-GCM-encrypted credentials and signed webhooks, org multi-tenancy with RLS, WordPress HMAC SSO bridge (Memberistic → CRM), SMS/TCPA compliance suite, Hostinger standalone deploy pipeline.
- **Missing:** widget management, bookings module, team-member invite UI, plan-limit *enforcement* (limits stored, never enforced), metered/PAYG billing, role-based permissions (owner/admin/agent stored but not differentiated).
- Already contains a **Chatbotistic leads client** (`src/lib/chatbotistic/client.ts` → `GET /api/get-json-lead`).

**Repo 3: chatbotistic-saas-connector** — trimmed distribution of the 4 first-party plugins + `dist/` ZIPs. Same lineage as Repo 1 but drifted (~55–80 changed lines per core file); needs a real merge, not a copy, if synced.

### 1.2 The Tochat.be API surface you already integrate (the raw material for the new dashboard)

Data API: `https://services.tochat.be` (API-Platform/Hydra). Auth: master email+password → `POST /api/authentication_token` → JWT Bearer (~50 min cache, auto-refresh). Tenant isolation via `userClient` tag (today `cbc-{wp_user_id}`). Separate long-lived **Lead Export API key** for `GET /api/get-json-lead`.

| Capability | Endpoints (all under `services.tochat.be`) |
|---|---|
| Widgets CRUD | `GET/POST/PUT/PATCH/DELETE /api/v2/widgets[/{id}]` |
| Agents (operators) | `GET/POST/PUT/DELETE /api/v2/whatsapp_operators[/{id}]` |
| FAQ groups | `/api/v2/whatsapp_operators/{id}/faq_grps`, `POST/PUT/DELETE /api/v2/faq_grps[/{id}]` |
| Leads | `GET /api/v2/stats` (phone, country, referer, dataProperties, bookingData, isLeadNew) |
| Lead bulk export | `GET /api/get-json-lead?fromDate=&toDate=&page=` (API-key auth) |
| Analytics | `GET /api/v2/{widget}/stats-graph`, `/{widget}/referer-graph`, `/api/v2/widget_stats/{id}` |
| Booking forms | `GET/POST/PUT/DELETE /api/v2/booking_configs[/{id}]` (keyed to operator) |
| Campaigns | `GET/POST/PUT/DELETE /api/v2/campaigns[/{id}]` |
| Audiences | `GET/POST/DELETE /api/v2/many_contacts[/{id}]` |
| Banners | `GET/POST/PUT/DELETE /api/v2/banners[/{id}]` |
| Targeting rules | `GET/POST/DELETE /api/v2/widget_rules[/{id}]` |
| Payment links / transactions | `/api/v2/payment_links`, `/api/v2/transactions` |
| Landing links | `GET /api/landing-links/{widgetId}` |
| Embed | `https://services.tochat.be/widget/{key}/load.js` |

License server: `https://chatbotistic.com/wp-json/licenseistic/v1` — `POST /activate`, `/heartbeat`, `/deactivate`; responses enriched by the Bridge with tier caps + white-label brand fields.

### 1.3 Key findings that shape the plan

1. **You already own ~80% of the code needed.** The WPistic CRM is the dashboard architecture (tenancy, inbox, automations, AI); the connector is the Tochat.be client; Bookingistic is a full booking engine; Licenseistic/Memberistic is billing+entitlements. The new product is mostly *assembly + productization*, not greenfield.
2. **Critical constraint: Tochat.be has no two-way messages/inbox API.** It delivers *leads and stats*, not live message streams. A "shared team inbox" therefore must run on the CRM's own WhatsApp connectivity (Meta Cloud API / Twilio — already built in WPistic CRM), with Tochat leads flowing in as contacts/conversations. This is actually a strategic advantage (see §4.5: own-the-stack).
3. **Entitlement enforcement is the biggest revenue leak.** Plan limits (widgets/agents/contacts/domains) are stored everywhere but enforced almost nowhere in the CRM. Fix before scaling paid tiers.
4. **The two connector copies have drifted.** Pick Repo 1 (`ChatBotistic-Complete-System-Management`) as the canonical source; the saas-connector repo becomes a build-artifact/distribution repo only.
5. **Security fixes needed:** customer-widget stores the Tochat password in plaintext (`cbw_tochat_password` option) while the connector encrypts it — align to encrypted; per-org Tochat sub-scoping relies on the API honoring `userClient` filters — keep the re-fetch-and-verify ownership checks in the new proxy.

---

## Part 2 — The Product: "Chatbotistic Dashboard" (app.chatbotistic.com experience, self-owned)

A standalone multi-tenant SaaS dashboard (Next.js 16 + Supabase, cloned from WPistic CRM architecture) where **your clients** manage everything in one place — widgets, booking forms, leads, shared team inbox, campaigns, automations, and ad attribution — under your brand, with agency white-label resale built in.

### 2.1 Architecture

```
┌────────────────────────────────────────────────────────────────┐
│  chatbotistic.com (WordPress — keep as-is)                     │
│  Marketing site · Memberistic billing · Licenseistic · Bridge  │
└──────────────┬─────────────────────────────────────────────────┘
               │ HMAC SSO (existing pattern: src/lib/sso)
               ▼
┌────────────────────────────────────────────────────────────────┐
│  dashboard.chatbotistic.com (NEW — Next.js 16 + Supabase)      │
│  Org tenancy (RLS, migration-009 pattern) · Roles enforced     │
│                                                                │
│  Widget Studio │ Bookings │ Leads/CRM │ Shared Inbox │ Ads     │
│  Campaigns │ Automations │ AI/RAG │ Analytics │ White-label    │
│                                                                │
│  Server-only proxy layer (keys never reach browser):           │
│   ├─ TochatClient  → services.tochat.be (JWT, userClient/org)  │
│   ├─ MetaProvider / TwilioProvider (inbox, broadcasts)         │
│   └─ LeadSync worker → /api/get-json-lead + /api/v2/stats      │
└──────────────┬─────────────────────────────────────────────────┘
               │
   Customer sites: chatbotistic-widget plugin (license-gated,
   white-label) OR plain embed script for non-WordPress sites
```

**Tenancy mapping:** one Supabase `organizations` row per customer; Tochat `userClient` tag becomes `org-{org_uuid}` (migration path from `cbc-{wp_user_id}` via a mapping table). Every Tochat write re-fetches and verifies `userClient` ownership (keep the connector's hard isolation pattern).

**Reuse directly from WPistic CRM:** org tenancy + RLS (`user_org_ids()` + `set_org_id` trigger), SSO bridge, provider strategy pattern + AES-256-GCM secret encryption, inbox/realtime, automations engine, RAG stack, broadcasts, deploy pipeline.

**Port from WordPress plugins:** the full Tochat API client (`class-api.php` → TypeScript `TochatClient`), widget payload mapping, plan-caps model (Bridge `Caps`), booking-config semantics.

### 2.2 Feature plan by module

#### A. Widget Studio (differentiator #1 — better than Tochat's own UI)
- Create/edit/delete/toggle widgets with **live visual preview** (render the actual `load.js` in a sandboxed iframe as settings change).
- All Tochat settings: name, widget/button/offline messages, legend, brand color + landing colors, position, auto-open, theme — plus banners, targeting rules (`widget_rules`), landing links.
- Multi-channel buttons (WhatsApp primary; extend per Tochat capabilities).
- One-click embed: script snippet, WordPress plugin key, Google Tag Manager recipe, Shopify/Wix instructions.
- Template gallery: pre-designed widget presets per vertical (real estate, clinics, e-commerce, restaurants) — pure config data, cheap to build, great for marketing.
- Agent manager: operators with E.164 validation, job title, greeting, schedules; FAQ group builder per agent.

#### B. Booking system (differentiator #2)
- **Phase 1:** Tochat `booking_configs` CRUD per agent (what the connector API client already supports but never got a UI) + leads' `bookingData` surfaced as "Bookings" list.
- **Phase 2:** Native booking engine ported from **Bookingistic** (services, staff, availability, calendar, email automations, payments) as Supabase tables — bookings created from WhatsApp conversations or the widget's booking form, with reminders sent via WhatsApp template messages (huge no-show reducer, a proven paid feature).
- Booking form builder: drag-and-drop fields (`text/email/tel/url/number/checkbox` — matches the operator `form.items` schema), hosted form URL + widget-embedded mode.

#### C. Leads management (differentiator #3 — the ad-attribution story from `Tools Details Draft.txt`)
- **LeadSync worker:** scheduled pull from `/api/v2/stats` + `/api/get-json-lead` → upsert into Supabase `contacts` (phone-normalized dedup, existing pattern) with source widget, agent, referer, `dataProperties`, booking flag.
- Full CRM on top (already built): tags, custom fields, notes, CSV import/export, Kanban pipelines, deal values.
- **Ad attribution:** UTM link builder (tracking URLs per campaign/ad set/keyword), first-touch attribution stored on the contact, ROAS dashboard (leads/bookings/deals by source), Meta CAPI + Google Ads offline-conversion upload (send "lead became a booking/sale" back to ad platforms — agencies pay premium for this).
- Lead scoring (rules + AI), round-robin lead routing to team members.

#### D. Shared team inbox (differentiator #4)
- Built on the CRM's existing inbox: conversation list, thread, composer, templates, statuses (open/pending/closed), realtime, unread counts.
- **Connectivity:** each org connects its own WhatsApp number via Meta Cloud API (embedded signup flow) or Twilio — the provider layer exists. Tochat widget leads auto-create contacts/conversations; when the org has a connected number, the team replies in-app.
- **Team collaboration (new build):** member invites UI, roles enforced (owner/admin/agent), conversation assignment + auto-assignment (round-robin/least-busy), private internal notes on threads, @mentions, collision detection ("Rakib is typing…"), saved replies, SLA timers, working hours + away auto-reply.
- Inbox views: mine / unassigned / all / by tag / by widget source.

#### E. Campaigns & automations
- Broadcasts (exists): WhatsApp template + SMS campaigns with per-recipient tracking; add Tochat `campaigns`/`many_contacts` sync where useful.
- Automations (exists): welcome series, lead qualification flows, booking reminders, review requests, abandoned-conversation follow-ups; add "new Tochat lead" as a trigger type.
- AI (exists): RAG knowledge base per org, AI auto-reply with human handoff, plus AI lead summaries and smart-reply drafts in the inbox.

#### F. Analytics
- Unified dashboard: widget views/clicks/leads (Tochat stats-graph), conversations, response times, bookings, pipeline value, campaign performance, ROAS by source.
- Per-client scheduled PDF/email reports (agency retention feature).
- Optional Insightistic-style GA4 blending later.

#### G. Agency & white-label (the $5M engine — see Part 3)
- Sub-accounts: agency org contains client orgs; agency switcher UI; per-client seats/limits.
- White-label: custom logo, colors, **custom domain** (dashboard.agencybrand.com), branded emails/reports, hidden Chatbotistic branding — the Bridge brand-envelope already models this; enforce it in the new app.
- Client billing tools: agencies set their own markup; export invoices or Stripe Connect later.

#### H. Billing & entitlements (revenue plumbing)
- Keep Memberistic/Stripe as source of truth initially (SSO claims → org entitlements — already built); add in-app Stripe checkout in Phase 3 to drop WordPress dependency for new signups.
- **Enforce limits everywhere** (the current leak): widgets, agents, team seats, active contacts/mo (ManyChat-style meter), AI credits, messages. Central `entitlements.ts` guard + usage meters + upgrade prompts at 80%/100%.
- Usage-based add-ons: extra contacts, AI credits, extra seats — this is where SaaS margin expands.

### 2.3 Build roadmap

| Phase | Scope | Duration (1–2 devs + AI-assisted) |
|---|---|---|
| **P0 — Foundation** | Fork WPistic CRM → new repo `chatbotistic-dashboard`; rebrand; SSO from chatbotistic.com; port `TochatClient` (TS) + org↔userClient mapping; entitlement enforcement middleware | 2–3 weeks |
| **P1 — Widget Studio + Leads** | Widgets/agents/FAQs CRUD + live preview + embed; LeadSync worker; leads list + pipeline integration; basic analytics. **← First sellable version** | 4–6 weeks |
| **P2 — Inbox + Team** | Meta embedded signup, team invites/roles, assignment, notes, saved replies, collision detection; Tochat-lead → conversation flow | 4–6 weeks |
| **P3 — Bookings + Attribution** | booking_configs UI, Bookingistic-ported native bookings + WhatsApp reminders; UTM builder, ROAS dashboard, Meta CAPI/Google offline conversions; in-app Stripe billing | 6–8 weeks |
| **P4 — Agency tier** | Sub-accounts, white-label domains, branded reports, client billing; template gallery; API + Zapier/n8n webhooks (contact-form plugin's webhook pattern) | 6–8 weeks |

~6 months to full platform; revenue starts at P1 (~2 months).

**Also fix immediately (independent of the new app):**
1. Encrypt `cbw_tochat_password` in chatbotistic-widget (plaintext today).
2. Declare Repo 1 canonical for the 4 shared plugins; make saas-connector a dist-only repo.
3. Enforce plan caps in the existing connector dashboard (widget/agent limits are checked, but contact/domain caps and CRM limits are not).

---

## Part 3 — Business Models: The Path to $5M/yr

### 3.1 Positioning

Don't sell "a WhatsApp widget" (crowded, $0–5/mo commodity). Sell **"the WhatsApp revenue platform"**: *track every ad dollar to a WhatsApp conversation to a booked appointment* — Tracking + Automation + Analytics (your own positioning doc), executed for two buyers:

- **SMBs/service businesses:** turn WhatsApp chats into booked appointments (booking + reminders + inbox).
- **Marketing agencies (primary):** prove ad ROI with WhatsApp attribution, manage all clients from one white-label dashboard, resell under their brand.

Agencies are the $5M lever: one agency sale = 5–50 end clients, near-zero CAC for those seats, and high retention (their clients are locked to their dashboard).

### 3.2 Pricing (replaces current Free/$0 · Pro/$9 · Agency/$99 · Lifetime)

Current pricing is drastically undervalued for the feature set planned (competitors: respond.io $79–249/mo, Wati $49–299/mo, Trengo €99+/mo, ManyChat $15–whatever metered).

| Plan | Price | Included | Meters |
|---|---|---|---|
| **Free** | $0 | 1 widget, 1 agent, 100 contacts, community support | Chatbotistic branding on |
| **Starter** | $29/mo | 3 widgets, 3 seats, 1k contacts, inbox, bookings, basic automations | +$10/1k extra contacts |
| **Growth** | $79/mo | 10 widgets, 10 seats, 10k contacts, attribution + ROAS, AI replies (1k credits), remove branding | +AI credit packs |
| **Agency** | $199/mo | 10 client sub-accounts, white-label (logo+colors), 25 seats, branded reports | +$15/mo per extra client sub-account |
| **Agency Pro** | $499/mo | 30 sub-accounts, custom domain, full white-label, API, priority support | +$12/mo per extra sub-account |
| **Enterprise/Reseller** | $999+/mo | Unlimited-ish, SLA, dedicated onboarding, custom contracts | Custom |

Annual = 2 months free (drives cash + retention). **Lifetime**: stop selling open-ended lifetime; use *limited* LTDs only as a launch weapon (see 3.4).

### 3.3 Revenue streams (stacked)

1. **Core SaaS subscriptions** — the base (model below).
2. **Agency white-label** — highest ARPU + lowest churn; the Bridge/brand-envelope tech is already built.
3. **Usage add-ons** — contacts over quota, AI credits, extra sub-accounts/seats. Mature SaaS gets 15–30% of revenue from expansion; this is how ARPU grows without new logos.
4. **WhatsApp API margin** — when orgs connect Meta Cloud API through you as a Tech Provider/BSP-partner route, add $0.005–0.01/conversation on top of Meta rates. At scale this is meaningful pure margin.
5. **Launch LTDs** (AppSumo/dealify) — one-time cash infusions ($100–300k typical for a good launch) + thousands of users who seed reviews, affiliates, and agency upgrades. Cap the tier (no white-label in LTD).
6. **Marketplace/add-on products** — Insightistic (analytics), Bookingistic Pro features, WPistic Contact Form Pro sold as paid add-ons ($9–29/mo each) to the same base; also templates/verticals packs.
7. **Affiliate program** (page already exists) — 25–30% recurring; agencies and YouTubers in the WhatsApp-marketing niche convert well.
8. **Services** — done-for-you setup ($299–999 one-time), chatbot-flow building, agency onboarding. Keep <10% of revenue but it funds early growth and feeds case studies.

### 3.4 The $5M math (three scenarios)

$5M/yr ≈ **$417k MRR**.

**Scenario A — Agency-led (recommended):**

| Segment | Count | ARPU/mo | MRR |
|---|---|---|---|
| Agency Pro / Enterprise | 250 | $600 (base + extra sub-accounts) | $150,000 |
| Agency | 500 | $250 | $125,000 |
| Growth | 900 | $90 (incl. add-ons) | $81,000 |
| Starter | 1,200 | $32 | $38,400 |
| Add-ons/API margin/marketplace across base | — | — | $25,000 |
| **Total** | **~2,850 paying** | | **$419k MRR ≈ $5.0M ARR** |

~2,850 paying customers where 750 are agencies. Each agency brings 5–30 end businesses, so the platform serves ~10–15k businesses while you only sell to ~3k accounts. This is achievable for a 6–10 person company in 24–30 months post-launch with the funnel in 3.5.

**Scenario B — SMB volume:** 6,000 paying at $70 blended ARPU = $420k MRR. Needs ~120k free signups at 5% conversion — heavier top-of-funnel (SEO/ads), higher churn. Harder; treat as backup.

**Scenario C — Hybrid + non-recurring:** $3.5M subscriptions (Scenario A at 70%) + $600k LTD launches + $400k services/onboarding + $500k API-margin & marketplace = $5M revenue-year while MRR still ramps. Realistic bridge for years 1–2.

**Sanity milestones:** Month 6: $10k MRR (LTD + early agencies) → Month 12: $50k MRR → Month 18: $150k MRR → Month 30: $417k MRR. Assumes net revenue retention >100% from usage add-ons (the meters make this happen) and monthly logo churn <4% (agency-heavy mix keeps it low).

### 3.5 Go-to-market

1. **SEO moat (you already have the assets):** chatbotistic.com blog targeting "WhatsApp lead tracking", "WhatsApp widget for [platform]", "WhatsApp booking system", "[vertical] WhatsApp automation" — the Tools Details Draft is literally post #1. Insightistic dogfooding = content flywheel. Free tools (click-to-chat link generator, widget-speed tester, WhatsApp-link QR generator) as lead magnets.
2. **WordPress distribution channel (unique advantage):** a free `chatbotistic-widget` on WordPress.org (43% of the web) with in-plugin upgrade path to the dashboard. Contact-form and booking plugins as additional free-plugin funnels. This is a channel your SaaS competitors don't have.
3. **AppSumo launch** at P1/P2 for cash + install base + reviews.
4. **Agency outbound:** target Meta/Google ads agencies in WhatsApp-heavy markets (LATAM, India, SEA, MENA, Southern Europe, Bangladesh) with the ROAS-attribution pitch + white-label demo. Partner program: 30% recurring or wholesale pricing.
5. **Affiliate program** (page exists) + YouTube tutorials (social channels exist).
6. **In-product virality:** "Powered by Chatbotistic" on Free-tier widgets — every embedded widget is an ad.

### 3.6 Strategic risk: Tochat.be dependency — and the exit ramp

Today the widget/lead engine is resold from Tochat.be (services.tochat.be) on a master account. Risks: their pricing, rate limits, API changes, or them competing down-market; plus your COGS scale with their reseller fees.

**Mitigation (already half-built):** the WPistic CRM's Meta Cloud API + Twilio layer means the *inbox, broadcasts, automations, bookings, and CRM* — the high-value features — run on **your own stack**. Plan the ramp:
- **Year 1:** Tochat for widgets/leads (fast time-to-market), own stack for inbox/campaigns/bookings.
- **Year 2:** build your own embeddable widget (`widget.chatbotistic.com/load.js` — a Preact bundle + click/lead beacon into Supabase is 4–6 weeks of work), migrate new signups to it, keep Tochat for legacy.
- **Result:** full-margin product, no reseller ceiling — important at $5M scale where reseller fees would otherwise eat 20–40% of COGS.

### 3.7 What to do first (next 30 days)

1. Fork WPistic CRM → `chatbotistic-dashboard`, wire SSO + rebrand (week 1–2).
2. Port `TochatClient` to TypeScript + org mapping + entitlement middleware (week 2–3).
3. Widget Studio MVP + LeadSync (week 3–4) → private beta with 5–10 existing members.
4. In parallel: fix the plaintext password, declare canonical repo, raise prices on the public pricing page for new signups (grandfather existing), and start the AppSumo application (long lead time).

---

*Sources: full audit of the three repositories in this workspace, including `chatbotistic-connector/includes/class-api.php` (Tochat API surface), `WPistic-WhatsApp-CRM/docs/saas-membership-bridge.md` (entitlement design), `supabase/migrations/009_org_tenancy.sql` (tenancy pattern), and the business-context notes (`Tools Details Draft.txt`, `Website links details.txt`).*

---

## Part 4 — Live Audit Update (2026-07-11)

Follow-up pass, six days after Part 1–3 above, requested by the product
owner: a bug + design audit of the now-renamed `ChatBotistic-App` repo (the
`chatbotistic-dashboard` fork this plan called for), plus concrete
integration steps now that real Tochat.be / Chatbotistic credentials and
domains are in hand. Full detail lives in two new docs in `ChatBotistic-App`:
`docs/dashboard-bug-audit-2026-07-11.md` and
`docs/design-responsive-audit-2026-07-11.md`. Summary:

### 4.1 What's confirmed fixed since Part 1

- **The plaintext-password bug is fixed.** `chatbotistic-widget` now encrypts
  `cbw_tochat_password` with the same `openssl_encrypt`/`AUTH_KEY` scheme as
  the connector, and transparently migrates any legacy plaintext value on
  next read (`class-api.php:20-37,46,62-72`). It only survives in an
  archived `V1/` snapshot inside both plugin repos — recommend deleting or
  clearly labeling those `V1/` trees so nobody rebuilds a distribution ZIP
  from the stale copy.
- **Connector drift is resolved.** `chatbotistic-connector`,
  `chatbotistic-profile`, and `chatbotistic-widget` are now byte-identical
  between `ChatBotistic-System-Management` and `chatbotistic-saas-connector`
  (both at v3.2.2) — the "pick a canonical repo" action item from Part 1.3
  is done; `chatbotistic-saas-connector` is correctly operating as the
  dist-only mirror.
- **The "docs page" the product owner flagged is almost certainly the
  Next.js dashboard's `/docs`, not the WordPress theme's `page-docs.php`.**
  The theme page is fully populated (14 articles, working scrollspy, no
  broken includes) and already documents the white-label CNAME flow (see
  4.3). Its header comment notes it's *hand-synced* with the dashboard's
  `/docs` — a staleness risk worth a follow-up pass once the dashboard docs
  are finalized, but not itself broken.

### 4.2 What's confirmed broken (see the two new audit docs for full detail)

- **Dark-mode "not working"** — real root cause found: `ThemeProvider`
  initializes state to `"light"` before reading `localStorage`, so a stored
  Dark preference visibly flashes back to light for 400–750ms on every page
  load. Small, contained fix in `theme-provider.tsx`.
- **Multi-user org bugs** — automations, WhatsApp config, tags, and
  templates are still filtered by `user_id` in several places instead of
  `org_id`, so any teammate other than the one who ran initial setup sees
  "not connected" screens and 404s on their own automations. This is
  probably the biggest contributor to "the dashboard is not working" if
  more than one person has logged into the account — see bug-audit doc
  items 1–3 and 6.
- **Base UI accessibility warning** on every marketing page
  (`nativeButton` vs. `<Link>` composition) — also the source of the visible
  red error badge in the dev overlay.
- **Rebrand to "Chatbotistic" hasn't started** on the dashboard's public
  surface — nav, title, hero, docs, footer all still say "WPistic WhatsApp
  CRM." Scope as its own task.

### 4.3 Tochat.be integration — working steps with your actual credentials

You provided a Chatbotistic API key, your white-label API base
(`https://app.chatbotistic.com/api/`), your target dashboard domain
(`https://chatbot.wpistic.cloud`), and your marketing domain
(`https://www.chatbotistic.com`). Mapping those onto what already exists:

1. **The lead-sync integration is already wired to your exact domain.**
   `src/lib/chatbotistic/client.ts` in `ChatBotistic-App` defaults its base
   URL to `https://app.chatbotistic.com` — matching your white-label API
   domain exactly — and reads the key from `CHATBOTISTIC_API_KEY`. **Action:**
   set `CHATBOTISTIC_API_KEY` in your hosting platform's environment-variable
   UI (Vercel / Hostinger hPanel — wherever `chatbotistic-dashboard` deploys),
   never in a committed file or `.env` checked into git. **Do not paste the
   key into any repo, doc, or chat log that gets persisted** — treat the one
   you shared as needing rotation once you've moved it into a secrets store,
   since it's now been transmitted through this session.
2. **This only covers leads.** The full Tochat.be surface (widgets, agents,
   FAQs, bookings, campaigns, audiences, banners, payment links —
   Part 1.2's table) is a *separate* API at `services.tochat.be`, authenticated
   by **JWT from a master email+password** (`POST /api/authentication_token`),
   not the lead API key. `chatbotistic-connector/includes/class-api.php`
   already implements this correctly in PHP (JWT cached ~50min, auto-retry
   on 401, `userClient` tenant tagging) — it's the reference implementation
   to port. **Action needed from you:** confirm the master
   email+password Tochat.be account credentials (the same ones already
   configured as `CBC_API_EMAIL`/`CBC_API_PASSWORD` in the WordPress
   connector, or in its DB-stored, encrypted settings) so the equivalent
   `TochatClient` can be built in TypeScript for the dashboard. Same rule:
   these go into deployment secrets, never into the repo.
3. **Port `TochatClient` to TypeScript** (`ChatBotistic-App/src/lib/tochat/client.ts`),
   mirroring `class-api.php`'s auth caching and endpoint coverage, plus an
   `org_id ↔ userClient` mapping table (`userClient` becomes
   `org-{org_uuid}`, migrated from the WordPress-era `cbc-{wp_user_id}`
   pattern via a lookup table — Part 2.1's tenancy design already covers
   this).
4. **Build Widget Studio + Leads UI on top of it** (Part 2.2.A/C) — this is
   the first sellable slice per the Part 2.3 roadmap (P1, 4–6 weeks) and is
   unblocked as soon as step 2's credentials are in hand.
5. **WhatsApp campaigns** — `ChatBotistic-App` already has a full broadcast
   engine (Meta Cloud API / Twilio / Jasmin SMS, template + free-text sends,
   per-recipient tracking — confirmed working in the bug audit). The
   integration work here is *not* building campaign sending from scratch;
   it's (a) syncing Tochat `campaigns`/`many_contacts` as an additional
   audience-import source into the existing broadcast flow, and (b) fixing
   the multi-user bugs in 4.2 so a whole team can actually use it.

### 4.4 White-label CNAME multi-tenant offer — what already exists

The theme's `page-docs.php` "White-label Setup" article already documents a
working 4-step CNAME flow, gated to the Agency plan: pick a subdomain → add
a CNAME record pointing at `chatbot.wpistic.cloud` → enter the subdomain in
Settings → SSL auto-issues within ~24h of DNS propagation. `page-agency-white-label.php`
is marketing copy only (no pricing/DNS specifics) — the real mechanics live
in the docs article. To ship this for real (Part 2.2.G):

1. **Custom-domain resolution middleware** in `ChatBotistic-App`
   (`src/proxy.ts`) — a lookup from incoming `Host` header → org, so
   `client-brand.com` and `chatbot.wpistic.cloud` both resolve to the same
   app with different branding/tenant context.
2. **Automated SSL issuance** for tenant-added CNAMEs — if deploying via
   Vercel, its Domains API handles this per-project automatically; if
   self-hosting on Hostinger/VPS per `DEPLOY.md`, this needs Caddy or
   certbot automation triggered when a tenant adds a domain in Settings.
3. **Brand-envelope enforcement in the dashboard UI** — logo, colors, hidden
   Chatbotistic branding are already modeled in the Bridge's brand-envelope
   (Part 1.1, `memberistic-licenseistic-bridge`) but need to actually be
   read and applied by the Next.js app's layout/theme, not just carried as
   unused SSO claims.
4. **Pricing** — keep this priced per Part 3.2 (Agency $199/mo includes
   white-label; Agency Pro $499/mo adds full custom domain) rather than
   underpricing the CNAME offer standalone; the "very affordable" instinct
   is right for customer acquisition but the white-label margin is what
   funds the SSL/domain-management engineering above.

### 4.5 Suggested next session

The two new audit docs plus this section are the "complete plan with
working steps" requested. Recommended next actions, in order: (1) fix the
theme-provider hydration bug and the `user_id`→`org_id` query bugs — both
small, contained, high-impact; (2) get the `services.tochat.be` master
credentials into deployment secrets and port `TochatClient`; (3) start the
Widget Studio UI. Each is independently shippable and matches the P0/P1
phases already scoped in Part 2.3.
