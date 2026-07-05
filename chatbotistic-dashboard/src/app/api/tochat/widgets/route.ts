import { type NextRequest, NextResponse } from "next/server";
import { createWidget, listWidgets, resourceId } from "@/lib/tochat/client";
import { resolveOrgContext, tochatErrorResponse } from "@/lib/org-context";
import { withinLimit } from "@/lib/plans";

/**
 * GET /api/tochat/widgets
 * List the org's widgets. Serves from `widgets_cache` by default; pass
 * `?sync=1` to force a live Tochat pull and refresh the cache first
 * (also removing cache rows for widgets no longer on Tochat's side).
 *
 * POST /api/tochat/widgets
 * Create a widget. Entitlement-checked against the `widgets` limit
 * (src/lib/plans.ts) using the cached row count — cheaper than a live
 * Tochat count and accurate as long as the cache stays in sync, which
 * every write path in this file maintains.
 */

export async function GET(request: NextRequest) {
  const result = await resolveOrgContext();
  if (!result.ok) return result.response;
  const { ctx } = result;

  const sync = request.nextUrl.searchParams.get("sync") === "1";

  if (sync) {
    try {
      const liveWidgets = await listWidgets(ctx.userClient);
      const rows = liveWidgets.map((w) => ({
        org_id: ctx.org.id,
        tochat_widget_id: resourceId(w),
        name: w.name ?? null,
        settings: w as unknown as Record<string, unknown>,
        active: w.active ?? true,
        synced_at: new Date().toISOString(),
      }));

      if (rows.length > 0) {
        const { error: upsertError } = await ctx.supabase
          .from("widgets_cache")
          .upsert(rows, { onConflict: "org_id,tochat_widget_id" });
        if (upsertError) {
          return NextResponse.json({ error: upsertError.message }, { status: 500 });
        }
      }

      // Drop cache rows for widgets that no longer exist on Tochat.
      const liveIds = rows.map((r) => r.tochat_widget_id);
      let staleQuery = ctx.supabase
        .from("widgets_cache")
        .delete()
        .eq("org_id", ctx.org.id);
      staleQuery =
        liveIds.length > 0
          ? staleQuery.not("tochat_widget_id", "in", `(${liveIds.join(",")})`)
          : staleQuery;
      await staleQuery;
    } catch (err) {
      return tochatErrorResponse(err);
    }
  }

  const { data, error } = await ctx.supabase
    .from("widgets_cache")
    .select("*")
    .eq("org_id", ctx.org.id)
    .order("synced_at", { ascending: false });

  if (error) {
    return NextResponse.json({ error: error.message }, { status: 500 });
  }
  return NextResponse.json({ widgets: data ?? [] });
}

export async function POST(request: NextRequest) {
  const result = await resolveOrgContext();
  if (!result.ok) return result.response;
  const { ctx } = result;

  const { count, error: countError } = await ctx.supabase
    .from("widgets_cache")
    .select("id", { count: "exact", head: true })
    .eq("org_id", ctx.org.id);

  if (countError) {
    return NextResponse.json({ error: countError.message }, { status: 500 });
  }

  if (!withinLimit(ctx.org, "widgets", count ?? 0)) {
    return NextResponse.json(
      { error: "Widget limit reached for your plan. Upgrade to add more widgets." },
      { status: 403 }
    );
  }

  let payload: Record<string, unknown>;
  try {
    payload = await request.json();
  } catch {
    return NextResponse.json({ error: "Invalid JSON body." }, { status: 400 });
  }

  try {
    const created = await createWidget(payload, ctx.userClient);
    const row = {
      org_id: ctx.org.id,
      tochat_widget_id: resourceId(created),
      name: created.name ?? null,
      settings: created as unknown as Record<string, unknown>,
      active: created.active ?? true,
      synced_at: new Date().toISOString(),
    };

    const { data: cached, error: upsertError } = await ctx.supabase
      .from("widgets_cache")
      .upsert(row, { onConflict: "org_id,tochat_widget_id" })
      .select("*")
      .single();

    if (upsertError) {
      // Tochat write succeeded but the cache mirror failed — surface
      // the created widget anyway so the caller isn't told creation
      // failed when it didn't.
      console.error("[tochat/widgets] cache upsert failed:", upsertError.message);
      return NextResponse.json({ widget: created }, { status: 201 });
    }

    return NextResponse.json({ widget: cached }, { status: 201 });
  } catch (err) {
    return tochatErrorResponse(err);
  }
}
