=== Insightistic – GA4 Analytics & AI Insights ===
Contributors: wordpressistic
Tags: google analytics, analytics, search console, pagespeed, ai insights
Requires at least: 5.6
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

View GA4, Search Console, and PageSpeed data right inside your WordPress dashboard — with optional on-demand AI insights.

== Description ==

Insightistic brings your most important website metrics directly into WordPress — no tab-switching, no third-party dashboards, no sampled data.

Connect your Google Analytics 4 property and Google Search Console via a single service account (no OAuth pop-ups, no re-authentication). Add a PageSpeed API key for real-time Core Web Vitals scoring, and optionally activate AI insights powered by OpenAI, Google Gemini, OpenRouter, or Anthropic Claude.

= GA4 Overview =

* 8 stat cards — Sessions, Users, Pageviews, Bounce Rate, Avg. Session Duration, New vs Returning, Revenue, Transactions — all with period-over-period comparison
* Timeline chart: sessions and revenue over time
* Traffic source donut chart
* Top Countries, Top Pages, Top Channels — with share bars
* Top Blog Posts — automatically filters GA4 data for /blog/, /post/, /news/ paths
* Source/Medium attribution table with revenue %, revenue per session, and conversion rate

= Google Search Console =

* Clicks, Impressions, CTR, and Average Position at a glance
* Top 10 queries and top 10 pages
* Device breakdown (desktop / mobile / tablet)

= PageSpeed Insights =

* Animated score rings for mobile and desktop
* Core Web Vitals grid: LCP, INP, CLS, FCP, TBT, Speed Index — each with a pass/fail badge
* Test any URL on your site, not just the homepage

= Engagement Tracking =

* Optional lightweight frontend script (under 2 KB) that fires custom GA4 events
* Tracks: outbound link clicks, scroll depth (25 / 50 / 75 / 100 %), file downloads, and element clicks
* Zero PII collected — no cookies, no fingerprinting

= AI Insights (on demand) =

* Click "Get AI Insights" on the dashboard to analyse your GA4 and GSC data
* Choose your AI provider: OpenAI, Google Gemini, OpenRouter (free models supported), or Anthropic Claude
* AI never runs automatically — you stay in full control of API usage and cost

= Security =

* Service account authentication — keys never expire, no OAuth callbacks
* All API keys and credentials are encrypted with AES-256-CBC before being stored in your database, using your WordPress auth salt as the encryption key
* Nonce-protected AJAX handlers; all inputs sanitised and escaped

= Performance =

* All analytics data is fetched via admin-only AJAX — zero impact on frontend page load
* 15-minute transient caching minimises Google API quota usage
* Minified JS and CSS served in production (source files served when SCRIPT_DEBUG is on)
* Chart.js bundled locally — no external CDN requests

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Go to **Insightistic → Settings** and open the **GA4** tab
4. Paste your Google Service Account JSON key (or use the "Import from JSON" button)
5. Enter your GA4 Property ID (numeric, e.g. 123456789)
6. Click **Save Settings**, then open the dashboard

**GA4 Service Account setup:**

1. Go to [Google Cloud Console](https://console.cloud.google.com/) → IAM & Admin → Service Accounts
2. Create a new service account and download its JSON key file
3. In the plugin Settings → GA4 tab, click **Import from JSON** and paste the key file contents
4. In your GA4 property, go to Admin → Property Access Management and add the service account email with the **Viewer** role
5. Enter your GA4 Property ID and click Save

**Search Console (optional):**
Use the same service account email — add it as a Full User in Google Search Console → Settings → Users and permissions.

**PageSpeed (optional):**
Create an API key in Google Cloud Console with the PageSpeed Insights API enabled and enter it in Settings → PageSpeed.

== Frequently Asked Questions ==

= Does this slow down my site? =

No. All data is fetched in the WordPress admin via AJAX. The optional engagement tracking script is under 2 KB and is only loaded when you enable it.

= Why use a service account instead of OAuth? =

Service account tokens never expire and require no browser callback. OAuth tokens typically expire every 7 days and need periodic re-authorisation, which can silently break dashboards on shared or managed hosting.

= Are my API keys stored securely? =

Yes. Every credential is encrypted with AES-256-CBC using your site's unique WordPress auth salt before being written to the database. Keys are never stored in plaintext.

= Does the AI analysis run automatically? =

No. AI analysis is always triggered manually by clicking "Get AI Insights". This keeps you in full control of API usage and cost.

= Which AI providers are supported? =

OpenAI (GPT-4o Mini and above), Google Gemini (1.5 Flash and above), Anthropic Claude (Haiku and above), and OpenRouter with free and paid models including Mistral, Llama, Gemma, Qwen, DeepSeek, and Phi.

= What does the engagement tracking script collect? =

When enabled, the script fires custom events to your GA4 property for: outbound link clicks, scroll depth milestones (25 / 50 / 75 / 100 %), file downloads (.pdf, .zip, etc.), and designated element clicks. No personally identifiable information is collected, no cookies are set, and no data is sent anywhere other than your own GA4 property.

= Is GA4 the only analytics platform supported? =

Yes. Insightistic is purpose-built for GA4. Universal Analytics was shut down by Google in July 2023.

= What date range does Search Console cover? =

Search Console data has a 2–3 day processing delay and covers up to 16 months of history. The plugin requests the last 90 days by default.

= Will this work on multisite? =

The plugin can be network-activated but each site requires its own credentials configured separately via its own Insightistic Settings page.

== Screenshots ==

1. Overview dashboard — 8 stat cards, timeline chart, traffic source donut, top countries, pages, and channels
2. Search Console tab — clicks, impressions, CTR, average position, top queries, top pages, and device breakdown
3. PageSpeed tab — animated score rings for mobile and desktop, plus Core Web Vitals grid with pass/fail badges
4. AI Insights panel — overall score, key findings, and prioritised recommendations
5. Settings page — tabbed interface covering GA4, Search Console, PageSpeed, Engagement, AI, and Docs
6. Addons page — upcoming extension modules with category filter tabs

== Changelog ==

= 2.0.0 =
* Added: Google Search Console integration (clicks, impressions, CTR, position, queries, pages, device breakdown)
* Added: PageSpeed Insights tab with animated SVG score rings and Core Web Vitals grid (LCP, INP, CLS, FCP, TBT, Speed Index)
* Added: Optional lightweight engagement tracking script (under 2 KB) for outbound links, scroll depth, file downloads, element clicks
* Added: 4 new GA4 stat cards — Pageviews, Avg. Session Duration, Bounce Rate, New vs Returning
* Added: Traffic Channel Report card (Organic, Direct, Social, Referral, Email, Paid Search)
* Added: Top Posts card — filters GA4 for blog/post/news URL paths
* Added: Addons showcase page with category filter tabs
* Added: Tabbed dashboard (Overview / Search Console / PageSpeed)
* Added: 6-tab Settings page (GA4, Search Console, PageSpeed, Engagement, AI Insights, Docs)
* Added: Shared JWT authentication helper — one service account powers both GA4 and Search Console
* Improved: Responsive layout across all screen sizes
* Improved: Top Pages card now shows bounce rate and average time on page
* Security: All API credentials encrypted with AES-256-CBC — no plaintext storage
* Security: PageSpeed API key now encrypted at rest (migrates any legacy plaintext value on first save)
* Performance: Chart.js bundled locally — no external CDN dependency
* Performance: Minified JS and CSS served in production with SCRIPT_DEBUG fallback to source files

= 1.1.0 =
* Added: AI Insights panel — OpenAI, Gemini, OpenRouter (free models), Anthropic Claude
* Added: Source/Medium attribution table with revenue attribution
* Added: Traffic donut chart by source
* Added: 15-minute transient caching to minimise API quota usage
* Added: AES-256 encryption for all stored credentials

= 1.0.0 =
* Initial release — GA4 sessions, users, revenue, Top Countries, Top Pages, time-series chart

== Upgrade Notice ==

= 2.0.0 =
Major feature release. Adds Search Console, PageSpeed, Engagement Tracking, and 6 new dashboard cards. No breaking changes — existing GA4 credentials and settings are preserved automatically.
