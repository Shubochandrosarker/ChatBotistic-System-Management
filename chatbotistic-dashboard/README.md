# Chatbotistic Dashboard

The customer-facing dashboard at **dashboard.chatbotistic.com** — a multi-tenant
SaaS app for creating and managing WhatsApp chat widgets, landing pages,
booking forms, leads, and campaigns. It is powered by
[Tochat.be](https://services.tochat.be) (ChatWith, white-labeled) on the
backend, while account billing and licensing live on **chatbotistic.com**
(WordPress + Memberistic/Licenseistic), which hands off into this app via an
HMAC-signed SSO bridge.

## Tech stack

- **Next.js 16** (App Router, Turbopack, `output: "standalone"`)
- **React 19** + TypeScript
- **Tailwind CSS v4**
- **Supabase** (Postgres, Auth, RLS) — via `@supabase/ssr` / `@supabase/supabase-js`
- **framer-motion** for page transitions and micro-interactions
- **lucide-react** icons

## Features

- **Widget Studio** — create/edit WhatsApp widgets with a live preview,
  agent (operator) management, FAQ groups, booking-config editing, and an
  embed-code dialog. Plan limits are enforced via upgrade dialogs.
- **Leads** — view leads captured through widgets, synced from Tochat.be on
  demand.
- **Campaigns** — compose broadcast campaigns with audience targeting
  (by widget or lead status), scheduling, draft/save flow, and a live
  message-usage meter tied to the org's monthly quota. (Actual message
  *sending* is not yet wired — see "Known gaps" below.)
- **Settings**
  - **Profile** and **Organization** tabs
  - **WhatsApp connection** — personal-number or Meta Cloud API mode,
    credentials encrypted at rest (AES-256-GCM)
  - **White-label** — brand name, primary color, custom domain, sender name
    (gated to the Agency plan)
  - **Team** — invite teammates by email/role, accept invites via token/link,
    remove members
- **Docs** — a full in-app documentation section (getting started, widgets,
  landing pages, booking forms, leads, WhatsApp connection, campaigns,
  white-label, team & roles, billing, FAQ)
- **Dashboard home** — onboarding checklist + at-a-glance overview
- Light/dark theme with system-preference detection and a manual toggle
- SSO login from chatbotistic.com (Memberistic/Licenseistic), auto-provisioning
  the organization and entitlements on first login

## Setup

```bash
npm install
cp .env.example .env.local   # then fill in real values, see below
```

Run the SQL migrations in `supabase/migrations/` **in order** (001 → 006)
against your Supabase project (via the SQL editor, `supabase db push`, or
the Supabase MCP `apply_migration` tool):

```
001_org_tenancy.sql
002_rls.sql
003_widgets_leads_landing.sql
004_campaigns_usage_whatsapp.sql
005_email_log.sql
006_org_invites.sql
```

Then start the dev server:

```bash
npm run dev
```

Other scripts: `npm run build` (production build), `npm start` (serve the
regular build), `npm run start:standalone` (serve the standalone build —
see "Deploying" below), `npm run typecheck` (`tsc --noEmit`).

## Environment variables

See [`.env.example`](./.env.example) for the full, grouped list with
descriptions. Summary:

| Variable | Purpose |
|---|---|
| `NEXT_PUBLIC_SUPABASE_URL` | Supabase project URL (public) |
| `NEXT_PUBLIC_SUPABASE_ANON_KEY` | Supabase anon key (public) |
| `SUPABASE_SERVICE_ROLE_KEY` | Server-only key for admin operations (user provisioning, bypassing RLS where explicitly needed) |
| `TOCHAT_API_BASE` | Tochat.be API base URL (defaults to `https://services.tochat.be`) |
| `TOCHAT_API_EMAIL` / `TOCHAT_API_PASSWORD` | Master account credentials used to mint the JWT for all `/api/tochat/*` and `/api/leads` proxy calls |
| `TOCHAT_LEAD_API_KEY` | Separate long-lived key used only for lead export |
| `SSO_SHARED_SECRET` | HMAC secret shared with the chatbotistic.com Memberistic/Licenseistic SSO bridge |
| `SSO_MAX_SKEW_SECONDS` | Max allowed clock skew for SSO tokens (defaults to 300) |
| `ENCRYPTION_KEY` | 64 hex chars (32 bytes) — AES-256-GCM key for WhatsApp credentials at rest. Generate with `openssl rand -hex 32` |
| `NEXT_PUBLIC_SITE_URL` | Public origin used to build absolute redirect URLs (e.g. post-SSO-login redirect) |

## Architecture overview

- **Org tenancy + RLS** (`supabase/migrations/001_org_tenancy.sql`,
  `002_rls.sql`): every table is scoped to an `org_id`, enforced by Postgres
  row-level security policies. `src/lib/org-context.ts` resolves the
  authenticated user's organization on the server (`requireOrgContext` /
  `resolveOrgContext`) and is the shared entry point for every API route.
- **Tochat proxy routes** (`src/app/api/tochat/**`, `src/app/api/leads/**`):
  thin Next.js route handlers that authenticate the org, then call
  `src/lib/tochat/client.ts` — a TypeScript port of the Chatbotistic
  Connector's PHP API client. Multi-tenancy on the Tochat side rides on a
  `userClient` tag (`org-{orgId}`); every write re-fetches the resource and
  verifies ownership before mutating it.
- **Entitlements** (`src/lib/plans.ts`): a single source of truth for plan
  limits (`widgets`, `agents`, `domains`, `seats`, `messages_per_month`,
  `sub_accounts`) and gated features (`booking_forms`, `white_label`,
  `custom_domain`, etc.), consumed by both API routes (hard enforcement)
  and the UI (lock badges, upgrade CTAs, usage meters). Per-org overrides
  come from `organizations.entitlements`, written during SSO provisioning.
- **SSO bridge** (`src/app/api/sso/login/route.ts`,
  `src/lib/sso/token.ts`, `src/lib/sso/provision.ts`): verifies an
  HMAC-signed, short-lived token from chatbotistic.com, provisions/updates
  the Supabase auth user and organization (plan + entitlements), then hands
  off through Supabase's magic-link verifier to establish the session.
- **Credential encryption** (`src/lib/encryption.ts`): WhatsApp credentials
  (access tokens, etc.) are encrypted at rest with AES-256-GCM before being
  stored.

## Deploying

`next.config.ts` sets `output: "standalone"`. The deploy target runs the
single `.next/standalone/server.js` entry point — no `next` CLI and no full
`node_modules` needed at runtime. When the host has no CDN in front of it,
remember to copy `public/` and `.next/static/` next to
`.next/standalone/` before starting the server (`npm run start:standalone`
does this assuming the standard `next build` output layout).

Security headers (HSTS, `X-Content-Type-Options`, `X-Frame-Options`,
`Referrer-Policy`, `Permissions-Policy`, and a report-only CSP) are applied
globally in `next.config.ts`. The CSP currently ships as
`Content-Security-Policy-Report-Only` — flip it to enforcing once it's been
verified clean in production.

## Known gaps / not yet wired

- **Campaign sending is stubbed.** Creating a campaign with `status:
  "sending"` inserts the campaign row but does not actually send any
  WhatsApp messages yet — see the `TODO(send-implementation)` comment in
  `src/components/campaigns/campaign-wizard.tsx` (around the campaign
  insert) for exactly where the Meta Cloud API (or personal-number bridge)
  send loop, plus the corresponding `increment_message_usage` RPC calls,
  need to be implemented.
- **Team invite emails are not sent.** Invites are created server-side and
  shown as a copyable token/link in the Team settings tab
  (`src/components/settings/team-tab.tsx`) — the inviter has to share the
  link with the invitee manually. There is no outbound email integration.
