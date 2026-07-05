import { NextResponse } from "next/server";
import {
  deleteWidget,
  getWidget,
  patchWidget,
  resourceId,
  updateWidget,
} from "@/lib/tochat/client";
import { jsonError, resolveOrgContext, tochatErrorResponse, type OrgContext } from "@/lib/org-context";

/**
 * /api/tochat/widgets/[id] — read / update / toggle-active / delete a
 * single widget.
 *
 * Every handler first checks `widgets_cache` for a row matching this
 * id under a *different* org. This is defense in depth *beyond* the
 * `ownedWidget()` check already inside src/lib/tochat/client.ts (which
 * re-fetches from Tochat and compares `userClient`): a mismatched id
 * 404s immediately from local data, before Tochat is ever asked. If
 * the cache has no row at all for this id (e.g. never synced for
 * anyone), the check passes and we fall through to the live Tochat
 * call, which is still fully ownership-checked there.
 */

/** 404s if a cache row exists for this id under a *different* org. */
async function assertNotOtherOrgsWidget(ctx: OrgContext, id: string): Promise<boolean> {
  const { data } = await ctx.supabase
    .from("widgets_cache")
    .select("org_id")
    .eq("tochat_widget_id", id)
    .neq("org_id", ctx.org.id)
    .maybeSingle();
  return !data;
}

export async function GET(
  _request: Request,
  { params }: { params: Promise<{ id: string }> }
) {
  const { id } = await params;
  const result = await resolveOrgContext();
  if (!result.ok) return result.response;
  const { ctx } = result;

  if (!(await assertNotOtherOrgsWidget(ctx, id))) {
    return jsonError("Widget not found.", 404);
  }

  try {
    const widget = await getWidget(id, ctx.userClient);
    await ctx.supabase.from("widgets_cache").upsert(
      {
        org_id: ctx.org.id,
        tochat_widget_id: resourceId(widget),
        name: widget.name ?? null,
        settings: widget as unknown as Record<string, unknown>,
        active: widget.active ?? true,
        synced_at: new Date().toISOString(),
      },
      { onConflict: "org_id,tochat_widget_id" }
    );
    return NextResponse.json({ widget });
  } catch (err) {
    return tochatErrorResponse(err);
  }
}

async function handleUpdate(
  id: string,
  request: Request,
  mode: "put" | "patch"
) {
  const result = await resolveOrgContext();
  if (!result.ok) return result.response;
  const { ctx } = result;

  if (!(await assertNotOtherOrgsWidget(ctx, id))) {
    return jsonError("Widget not found.", 404);
  }

  let payload: Record<string, unknown>;
  try {
    payload = await request.json();
  } catch {
    return jsonError("Invalid JSON body.", 400);
  }

  try {
    const updated =
      mode === "put"
        ? await updateWidget(id, payload, ctx.userClient)
        : await patchWidget(id, payload, ctx.userClient);

    const { data: cached, error: upsertError } = await ctx.supabase
      .from("widgets_cache")
      .upsert(
        {
          org_id: ctx.org.id,
          tochat_widget_id: resourceId(updated) || id,
          name: updated.name ?? null,
          settings: updated as unknown as Record<string, unknown>,
          active: updated.active ?? true,
          synced_at: new Date().toISOString(),
        },
        { onConflict: "org_id,tochat_widget_id" }
      )
      .select("*")
      .single();

    if (upsertError) {
      console.error("[tochat/widgets/:id] cache upsert failed:", upsertError.message);
      return NextResponse.json({ widget: updated });
    }
    return NextResponse.json({ widget: cached });
  } catch (err) {
    return tochatErrorResponse(err);
  }
}

export async function PUT(
  request: Request,
  { params }: { params: Promise<{ id: string }> }
) {
  const { id } = await params;
  return handleUpdate(id, request, "put");
}

/** Partial update — also how the UI toggles `active`. */
export async function PATCH(
  request: Request,
  { params }: { params: Promise<{ id: string }> }
) {
  const { id } = await params;
  return handleUpdate(id, request, "patch");
}

export async function DELETE(
  _request: Request,
  { params }: { params: Promise<{ id: string }> }
) {
  const { id } = await params;
  const result = await resolveOrgContext();
  if (!result.ok) return result.response;
  const { ctx } = result;

  if (!(await assertNotOtherOrgsWidget(ctx, id))) {
    return jsonError("Widget not found.", 404);
  }

  try {
    await deleteWidget(id, ctx.userClient);
    await ctx.supabase
      .from("widgets_cache")
      .delete()
      .eq("org_id", ctx.org.id)
      .eq("tochat_widget_id", id);
    return NextResponse.json({ success: true });
  } catch (err) {
    return tochatErrorResponse(err);
  }
}
