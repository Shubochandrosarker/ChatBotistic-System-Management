import "server-only";
import type { SupabaseClient } from "@supabase/supabase-js";
import type { Feature, Limit, PlanId } from "@/lib/plans";
import { PLAN_ORDER } from "@/lib/plans";
import type { SsoClaims } from "./token";

/**
 * Apply verified SSO claims to the database: upsert the organization
 * keyed by `sso_subject` (stable across repeat logins from the same
 * WordPress user/site) and make sure the dashboard user is an owner
 * member of it.
 *
 * Takes a service-role client (RLS-bypassing) because provisioning
 * runs before the user has a session.
 *
 * The `entitlements` column matches `OrgLike["entitlements"]` in
 * `src/lib/plans.ts`: per-org overrides on top of the plan's defaults.
 * The token today only asserts `plan` — no per-org limit overrides —
 * so `entitlements` is written as `null` and every limit/feature comes
 * from `PLANS[plan]`. Once Memberistic starts asserting overrides
 * (e.g. a manually bumped widget cap), fold them into `entitlements`
 * here without changing the shape callers read.
 */

export interface ProvisionResult {
  orgId: string;
}

function normalizePlan(plan: string | undefined): PlanId {
  return (PLAN_ORDER as string[]).includes(plan ?? "")
    ? (plan as PlanId)
    : "free";
}

export async function provisionFromClaims(
  admin: SupabaseClient,
  userId: string,
  claims: SsoClaims
): Promise<ProvisionResult> {
  const orgRow = {
    name: claims.name?.trim() || claims.email,
    plan: normalizePlan(claims.plan),
    license_key: claims.license_key ?? null,
    license_status: claims.license_status ?? "inactive",
    entitlements: null as { features?: Feature[] } & Partial<
      Record<Limit, number>
    > | null,
    entitlements_synced_at: new Date().toISOString(),
  };

  const { data: org, error: orgError } = await admin
    .from("organizations")
    .upsert(
      { sso_subject: claims.sub, ...orgRow },
      { onConflict: "sso_subject" }
    )
    .select("id")
    .single();

  if (orgError || !org) {
    throw new Error(
      `Failed to provision organization: ${orgError?.message ?? "unknown error"}`
    );
  }

  const orgId = (org as { id: string }).id;

  // Ensure the user is an owner of the org. A second login from a
  // different dashboard user under the same WordPress subject joins
  // the same org rather than creating a duplicate.
  const { error: memberError } = await admin.from("org_members").upsert(
    { org_id: orgId, user_id: userId, role: "owner", is_primary: true },
    { onConflict: "org_id,user_id" }
  );

  if (memberError) {
    throw new Error(
      `Failed to attach user to organization: ${memberError.message}`
    );
  }

  return { orgId };
}
