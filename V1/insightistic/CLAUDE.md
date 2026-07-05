# CLAUDE.md — Insightistic

## Project Context
Insightistic is a WordPress plugin that aggregates Google Analytics 4, Google
Search Console, and PageSpeed Insights data into a single admin dashboard, with
optional on-demand AI analysis via OpenAI, Google Gemini, OpenRouter, Anthropic
Claude, or Groq. WooCommerce Intelligence Pro adds a commerce panel when
WooCommerce is active.

## Architecture
- **Entry point:** `insightistic.php` defines constants, runs one-time
  migrations (`insightistic_migrate_legacy_keys()`,
  `insightistic_migrate_encryption()`), loads the classes in `includes/`, and
  instantiates each module's `->init()` on `plugins_loaded`.
- **Classes (`includes/`):**
  - `class-insightistic-encryption.php` — `Insightistic_Encryption`:
    authenticated encryption (AES-256-CBC + HMAC-SHA256, HKDF key derivation).
  - `class-insightistic-auth.php` — `Insightistic_Auth`: service-account JWT →
    OAuth2 access tokens for GA4 / GSC (cached in a transient).
  - `class-insightistic-ga.php`, `-gsc.php`, `-pagespeed.php` — Google data layers.
  - `class-insightistic-engagement.php` — front-end tracker **proxy** (see below).
  - `class-insightistic-ai.php` — `Insightistic_AI`: provider routing via a
    `switch` over the configured provider slug.
  - `class-insightistic-woocommerce.php`, `-email-automations.php`,
    `-addons.php`, `-system-status.php`, `-admin.php`.
- **Templates (`templates/`):** dashboard, settings, addons, system-status.
- **Front-end assets (`assets/`):** vanilla JS + Chart.js (bundled locally at
  `assets/js/vendor/chart.umd.min.js`, no CDN).

## Key Constraints
- **No OAuth pop-ups:** authentication uses a Google **service-account** key.
- **Secrets:** every credential (service-account key, AI keys, PageSpeed key,
  Measurement Protocol secret) **MUST** be stored via
  `Insightistic_Encryption::encrypt()`. Never store plaintext in `wp_options`.
  Key material comes from the `INSIGHTISTIC_ENCRYPTION_KEY` constant if defined,
  otherwise a random per-site secret in the `insightistic_crypto_secret` option —
  intentionally **not** keyed on `wp_salt()` so salt rotation is non-destructive.
- **Caching:** analytics AJAX responses are cached in transients (15-minute TTL
  for GA4 / GSC / Woo, 1 hour for PageSpeed). Honour `force=1` to bypass.
- **Front-end tracker:** `assets/js/tracking.min.js` must stay **under 2.5 KB**.
  It posts events to the server-side collector (`wp_ajax[_nopriv]_insightistic_track`)
  which forwards to the GA4 Measurement Protocol. **The Measurement Protocol
  secret is never exposed to the browser.** Do not add heavy libraries.

## Development Rules
1. **AJAX security:** every handler calls `check_ajax_referer( 'insightistic_nonce', 'nonce' )`
   and `current_user_can( 'manage_options' )`. The public tracker endpoint instead
   uses a same-origin check + per-IP rate limit (page caching makes nonces
   unreliable for anonymous visitors).
2. **Error handling:** Google quota / rate-limit errors are retried with
   exponential backoff in `Insightistic_GA::api_request()`, logged silently to
   `error_log`, and surfaced as a graceful "temporarily unavailable" message. A
   single failed report must not abort the whole dashboard.
3. **AI module:** on-demand only — no background cron for AI generation. Provider
   switching is a `switch` in `Insightistic_AI`. A per-request `$active_profile`
   override selects the skill profile (`basic` / `seo_expert` / `commerce_expert`)
   without mutating any stored option. AI runs are de-duplicated with a short
   result cache and an in-flight lock to avoid double-billing.
4. **PHP compatibility:** minimum **PHP 8.0**.

## Build
- JS/CSS are minified to `*.min.js` / `*.min.css` alongside the sources.
  `npm run build` (see `package.json`) regenerates them; the System Status page
  flags stale minified assets.
- `composer test` lints PHP and runs the encryption test harness in `tests/`.
- `composer phpcs` checks WordPress Coding Standards (`phpcs.xml.dist`).
