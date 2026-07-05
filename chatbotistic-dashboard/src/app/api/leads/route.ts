import { NextResponse } from "next/server";
import { resolveOrgContext } from "@/lib/org-context";

/**
 * GET /api/leads
 *
 * Thin wrapper over the Supabase `leads` table. Design choice: leads
 * pages *could* query Supabase directly from server components using
 * the session client (RLS already scopes rows to `user_org_ids()`,
 * same as this route), skipping the API layer entirely. This route
 * exists anyway so:
 *   - client components (filters, pagination, "load more") have a
 *     stable JSON endpoint without every page re-implementing the
 *     same query/pagination/status-filter logic, and
 *   - the shape returned here can diverge from the raw table (e.g.
 *     joining widget names in later) without touching every caller.
 *
 * Query params: `status` (open|contacted|won|lost), `widgetId`,
 * `limit` (default 50, max 200), `offset` (default 0).
 */
export async function GET(request: Request) {
  const result = await resolveOrgContext();
  if (!result.ok) return result.response;
  const { ctx } = result;

  const url = new URL(request.url);
  const status = url.searchParams.get("status");
  const widgetId = url.searchParams.get("widgetId");
  const limit = Math.min(Math.max(Number(url.searchParams.get("limit") ?? 50), 1), 200);
  const offset = Math.max(Number(url.searchParams.get("offset") ?? 0), 0);

  let query = ctx.supabase
    .from("leads")
    .select("*", { count: "exact" })
    .eq("org_id", ctx.org.id)
    .order("created_at", { ascending: false })
    .range(offset, offset + limit - 1);

  if (status) query = query.eq("status", status);
  if (widgetId) query = query.eq("widget_id", widgetId);

  const { data, error, count } = await query;

  if (error) {
    return NextResponse.json({ error: error.message }, { status: 500 });
  }

  return NextResponse.json({ leads: data ?? [], total: count ?? 0, limit, offset });
}
