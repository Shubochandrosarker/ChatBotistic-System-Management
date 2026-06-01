# Partners

Memberistic is built and maintained by **[WordPressistic](https://www.wordpressistic.com)**. This page documents the original launch-partner relationship that shaped the engine.

---

## WordPressistic — engineering and product

**Website:** **[wordpressistic.com](https://www.wordpressistic.com)**

[WordPressistic](https://www.wordpressistic.com) is the WordPress engineering studio behind Memberistic. WordPressistic owns the plugin architecture, database schema, Stripe + WooCommerce integration layer, REST API surface, React admin consoles, transactional email engine, cron scheduler, activity audit log, and the commercial Memberistic roadmap.

Memberistic is intentionally brand-neutral and is the membership backbone for multiple host products in the WordPressistic ecosystem, including **Chatbotistic** (WhatsApp chatbot SaaS).

---

## Guns 2 Ammo — original launch partner (historical)

**Website:** **[guns2ammo.com](https://guns2ammo.com)**
**Industry:** Indoor shooting range + firearms retail
**Region:** United States

[Guns 2 Ammo](https://guns2ammo.com) was the launch partner for Memberistic and the first production deployment of the engine. Their daily operations — lane bookings, range-officer staffing, per-person waivers, family memberships, walk-in retail customers, and front-desk check-ins — drove the design of features that other off-the-shelf membership plugins don't ship:

- Linked / family-member CRUD with per-person waiver status, phone, DOB, and relationship.
- Waiver-gated check-ins, with a daily waiver-follow-up cron and admin bulk-action support.
- A staff dashboard tuned for under-60-second front-desk transactions.
- A schema ready for POS, KIOSK, and lane-booking integrations.

These workflows are still in the codebase and are still production-tested. They're now **opt-in features**, gated by the `waiver_enabled` setting and the `account_show_lane_tools` setting — disabled by default for SaaS / digital host products like Chatbotistic, and re-enabled by a host-product profile plugin for service-business deployments.

---

## Built for businesses like yours

Memberistic is suitable for:

- **SaaS / subscription products** — Chatbotistic and the rest of the WordPressistic ecosystem run on it.
- **Service businesses with check-ins or waivers** — shooting ranges, fitness studios, climbing gyms, dive shops, golf simulators, racquet clubs, art studios, makerspaces.
- **Membership-driven retail** — front-desk renewals, walk-in signups, staff dashboards.

For commercial / licensing inquiries about deploying Memberistic to your own business, contact **[WordPressistic](https://www.wordpressistic.com)**.

---

## Trademark notice

_Guns 2 Ammo_ and _Guns2Ammo_ are trademarks of Guns 2 Ammo, used here for historical attribution. _WordPressistic_ and _Memberistic_ are trademarks of WordPressistic. All other trademarks are property of their respective owners.
