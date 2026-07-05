import { NextResponse } from "next/server";
import { getLeads, resourceId } from "@/lib/tochat/client";
import type { TochatWidget } from "@/lib/tochat/types";
import { resolveOrgContext, tochatErrorResponse } from "@/lib/org-context";

/**
 * POST /api/leads/sync
 *
 * Pulls the org's leads from Tochat's /api/v2/stats (via
 * src/lib/tochat/client.ts's `getLeads`, which is already scoped to
 * the org's `userClient`) and upserts them into the `leads` table,
 * deduped on `tochat_stat_id`.
 *
 * Only the normal session auth check gates this today — it's callable
 * by any signed-in member of the org, which is fine for an on-demand
 * "refresh my leads" button. NOTE: if/when this is wired to a cron
 * (to pull leads periodically without a user in the loop), it must be
 * gated by a cron secret instead of/in addition to the session check,
 * since a cron invocation has no Supabase session cookie to check.
 */
export async function POST(request: Request) {
  const result = await resolveOrgContext();
  if (!result.ok) return result.response;
  const { ctx } = result;

  const url = new URL(request.url);
  const since = url.searchParams.get("since"); // optional YYYY-MM-DD

  try {
    // `createdAt[after]` is API-Platform's standard DateFilter query
    // param shape (property[after]/[before]) — the Tochat API is
    // confirmed API-Platform/Hydra flavored (see tochat/types.ts), but
    // this specific filter hasn't been exercised against a live
    // instance. If `stats` doesn't actually support filtering on
    // createdAt, Tochat will just ignore the unknown param and return
    // the unfiltered set — worth confirming against a real response
    // before relying on `?since=` for incremental syncs.
    const stats = await getLeads(
      ctx.userClient,
      since ? { "createdAt[after]": since } : {}
    );

    const rows = stats.map((stat) => {
      const business = stat.business as string | TochatWidget | undefined;
      const widgetId =
        business && typeof business === "object"
          ? String(business.uuid ?? business.id ?? "")
          : String(business ?? "").replace(/\/$/, "").split("/").pop() ?? "";

      return {
        org_id: ctx.org.id,
        tochat_stat_id: resourceId(stat),
        phone: stat.phone ?? null,
        name: stat.name ?? null,
        country: stat.country ?? null,
        referer: stat.referer ?? null,
        widget_id: widgetId || null,
        agent: stat.agent ?? null,
        fields: (stat.fields ?? null) as Record<string, unknown> | null,
        booking_data: (stat.bookingData ?? null) as Record<string, unknown> | null,
      };
    });

    if (rows.length === 0) {
      return NextResponse.json({ synced: 0, total: 0 });
    }

    // `status` is deliberately omitted from the upserted columns so a
    // re-sync never clobbers a lead the dashboard user has already
    // triaged (contacted/won/lost) back to 'open'.
    const { error } = await ctx.supabase
      .from("leads")
      .upsert(rows, { onConflict: "org_id,tochat_stat_id" });

    if (error) {
      return NextResponse.json({ error: error.message }, { status: 500 });
    }

    return NextResponse.json({ synced: rows.length, total: stats.length });
  } catch (err) {
    return tochatErrorResponse(err);
  }
}
