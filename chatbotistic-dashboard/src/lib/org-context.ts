import "server-only";
import type { SupabaseClient } from "@supabase/supabase-js";
import { NextResponse } from "next/server";
import { createClient } from "@/lib/supabase/server";
import { supabaseAdmin } from "@/lib/supabase/admin";
import { userClientForOrg } from "@/lib/tochat/client";
import { TochatApiError } from "@/lib/tochat/types";
import type { Feature, Limit, OrgLike } from "@/lib/plans";

/**
 * Shared request context for every `/api/tochat/*` and `/api/leads/*`
 * route: the authenticated user, their resolved organization, and the
 * Tochat `userClient` tenancy tag for that org.
 */

export interface OrgRow extends OrgLike {
  id: string;
  name: string;
  tochat_user_client: string | null;
}

export interface OrgContext {
  /** Session-scoped Supabase client (RLS applies) for the request. */
  supabase: SupabaseClient;
  userId: string;
  org: OrgRow;
  /** Tochat tenancy tag — always `org-{org.id}`. */
  userClient: string;
}

export class OrgContextError extends Error {
  status: number;
  constructor(message: string, status: number) {
    super(message);
    this.name = "OrgContextError";
    this.status = status;
  }
}

/**
 * Resolve the caller's auth user + organization, throwing
 * `OrgContextError` (401/403) on any failure. Persists
 * `organizations.tochat_user_client` on first use — that column has
 * no user-facing write policy, so the persist step uses the
 * service-role client while every other read in this function goes
 * through the caller's own session client (RLS-scoped).
 */
export async function requireOrgContext(): Promise<OrgContext> {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();

  if (!user) {
    throw new OrgContextError("Unauthorized", 401);
  }

  const { data: membership } = await supabase
    .from("org_members")
    .select("org_id")
    .eq("user_id", user.id)
    .order("is_primary", { ascending: false })
    .limit(1)
    .maybeSingle();

  const orgId = (membership as { org_id?: string } | null)?.org_id;
  if (!orgId) {
    throw new OrgContextError("No organization found for this user.", 403);
  }

  const { data: orgData, error: orgError } = await supabase
    .from("organizations")
    .select("id, name, plan, entitlements, tochat_user_client")
    .eq("id", orgId)
    .maybeSingle();

  if (orgError || !orgData) {
    throw new OrgContextError("Organization not found.", 403);
  }

  const org = orgData as OrgRow;
  let userClient = org.tochat_user_client;

  if (!userClient) {
    userClient = userClientForOrg(org.id);
    const admin = supabaseAdmin();
    const { error: updateError } = await admin
      .from("organizations")
      .update({ tochat_user_client: userClient })
      .eq("id", org.id);
    if (updateError) {
      // Non-fatal: the tag is deterministic (`org-{id}`), so the
      // request can proceed correctly even if persistence failed —
      // the next call will just retry the write.
      console.error(
        "[org-context] failed to persist tochat_user_client:",
        updateError.message
      );
    }
    org.tochat_user_client = userClient;
  }

  return { supabase, userId: user.id, org, userClient };
}

export type OrgContextResult =
  | { ok: true; ctx: OrgContext }
  | { ok: false; response: NextResponse };

/**
 * Non-throwing wrapper for route handlers:
 *
 *   const result = await resolveOrgContext();
 *   if (!result.ok) return result.response;
 *   const { ctx } = result;
 */
export async function resolveOrgContext(): Promise<OrgContextResult> {
  try {
    const ctx = await requireOrgContext();
    return { ok: true, ctx };
  } catch (err) {
    if (err instanceof OrgContextError) {
      return {
        ok: false,
        response: NextResponse.json({ error: err.message }, { status: err.status }),
      };
    }
    const message = err instanceof Error ? err.message : "Unexpected error";
    console.error("[org-context] unexpected error:", message);
    return { ok: false, response: NextResponse.json({ error: message }, { status: 500 }) };
  }
}

/** Uniform error → NextResponse mapping for Tochat client calls. */
export function tochatErrorResponse(err: unknown): NextResponse {
  if (err instanceof TochatApiError) {
    return NextResponse.json({ error: err.message }, { status: err.status });
  }
  const message = err instanceof Error ? err.message : "Unexpected error";
  console.error("[tochat-route] unexpected error:", message);
  return NextResponse.json({ error: message }, { status: 500 });
}

export function jsonError(message: string, status: number): NextResponse {
  return NextResponse.json({ error: message }, { status });
}

/** Re-exported for route call sites that only need the type names. */
export type { Feature, Limit };
