/**
 * Plans & entitlements — single source of truth.
 *
 * Consumed by API routes (hard enforcement) AND UI (lock badges,
 * upgrade CTAs, usage meters). Billing itself lives on
 * chatbotistic.com (WordPress + Memberistic); this app only reads the
 * plan asserted via SSO / stored on the organization row.
 */

export type PlanId = "free" | "starter" | "growth" | "agency";

export type Feature =
  | "booking_forms"
  | "shared_inbox"
  | "landing_editor"
  | "ai_ready"
  | "no_branding"
  | "attribution"
  | "white_label"
  | "custom_domain"
  | "sub_accounts";

export type Limit =
  | "widgets"
  | "agents"
  | "domains"
  | "seats"
  | "messages_per_month"
  | "sub_accounts";

/** -1 means unlimited. */
export interface PlanDef {
  id: PlanId;
  name: string;
  priceMonthly: number;
  priceYearly: number;
  tagline: string;
  limits: Record<Limit, number>;
  features: Feature[];
  /** "Powered by Chatbotistic" branding on the widget. */
  branding: boolean;
}

export const PLANS: Record<PlanId, PlanDef> = {
  free: {
    id: "free",
    name: "Free",
    priceMonthly: 0,
    priceYearly: 0,
    tagline: "Try Chatbotistic on one site",
    limits: {
      widgets: 1,
      agents: 1,
      domains: 1,
      seats: 1,
      messages_per_month: 100,
      sub_accounts: 0,
    },
    features: [],
    branding: true,
  },
  starter: {
    id: "starter",
    name: "Starter",
    priceMonthly: 19,
    priceYearly: 190,
    tagline: "For small teams getting serious",
    limits: {
      widgets: 3,
      agents: 5,
      domains: 3,
      seats: 2,
      messages_per_month: 1000,
      sub_accounts: 0,
    },
    features: ["booking_forms", "shared_inbox"],
    branding: true,
  },
  growth: {
    id: "growth",
    name: "Growth",
    priceMonthly: 49,
    priceYearly: 490,
    tagline: "Scale conversations into revenue",
    limits: {
      widgets: 10,
      agents: 20,
      domains: 10,
      seats: 5,
      messages_per_month: 5000,
      sub_accounts: 0,
    },
    features: [
      "booking_forms",
      "shared_inbox",
      "landing_editor",
      "ai_ready",
      "no_branding",
      "attribution",
    ],
    branding: false,
  },
  agency: {
    id: "agency",
    name: "Agency",
    priceMonthly: 149,
    priceYearly: 1490,
    tagline: "White-label for client portfolios",
    limits: {
      widgets: 30,
      agents: -1,
      domains: 50,
      seats: 15,
      messages_per_month: 25000,
      sub_accounts: 10,
    },
    features: [
      "booking_forms",
      "shared_inbox",
      "landing_editor",
      "ai_ready",
      "no_branding",
      "attribution",
      "white_label",
      "custom_domain",
      "sub_accounts",
    ],
    branding: false,
  },
};

export const PLAN_ORDER: PlanId[] = ["free", "starter", "growth", "agency"];

export const FEATURE_LABELS: Record<Feature, string> = {
  booking_forms: "Booking forms",
  shared_inbox: "Shared inbox",
  landing_editor: "Landing page editor",
  ai_ready: "AI-ready",
  no_branding: "Remove Chatbotistic branding",
  attribution: "Lead attribution",
  white_label: "White-label",
  custom_domain: "Custom domain",
  sub_accounts: "Sub-accounts",
};

export interface OrgLike {
  plan?: string | null;
  /** Per-org overrides written by SSO provisioning. */
  entitlements?: Partial<Record<Limit, number>> & {
    features?: Feature[];
  } | null;
}

export function planOf(org: OrgLike | null | undefined): PlanDef {
  const id = (org?.plan ?? "free") as PlanId;
  return PLANS[id] ?? PLANS.free;
}

/** Which plan first unlocks a feature (for upgrade CTAs). */
export function planForFeature(feature: Feature): PlanDef {
  for (const id of PLAN_ORDER) {
    if (PLANS[id].features.includes(feature)) return PLANS[id];
  }
  return PLANS.agency;
}

/** Feature gate — org entitlement overrides win over the plan. */
export function can(org: OrgLike | null | undefined, feature: Feature): boolean {
  const overrides = org?.entitlements?.features;
  if (Array.isArray(overrides) && overrides.includes(feature)) return true;
  return planOf(org).features.includes(feature);
}

/** Effective numeric limit for the org (-1 = unlimited). */
export function limitOf(org: OrgLike | null | undefined, limit: Limit): number {
  const override = org?.entitlements?.[limit];
  if (typeof override === "number" && Number.isFinite(override)) return override;
  return planOf(org).limits[limit];
}

/** True while `current` usage stays within the limit for one more unit. */
export function withinLimit(
  org: OrgLike | null | undefined,
  limit: Limit,
  current: number
): boolean {
  const max = limitOf(org, limit);
  if (max < 0) return true; // unlimited
  return current < max;
}

export function messagesQuota(org: OrgLike | null | undefined): number {
  return limitOf(org, "messages_per_month");
}

/** "2026-07" — the period key used by message_usage. */
export function currentPeriodMonth(date = new Date()): string {
  return `${date.getUTCFullYear()}-${String(date.getUTCMonth() + 1).padStart(2, "0")}`;
}
