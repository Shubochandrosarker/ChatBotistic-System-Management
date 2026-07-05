import { NextResponse } from "next/server";
import {
  createOperator,
  deleteOperator,
  getOperator,
  listOperators,
  operatorsForWidget,
  updateOperator,
} from "@/lib/tochat/client";
import type { TochatOperator, TochatWidget } from "@/lib/tochat/types";
import { jsonError, resolveOrgContext, tochatErrorResponse } from "@/lib/org-context";
import { withinLimit } from "@/lib/plans";

/**
 * /api/tochat/widgets/[id]/operators — WhatsApp agent CRUD for one
 * widget.
 *
 * The route only carries the widget id; PUT/DELETE identify *which*
 * operator to mutate via `?id=` (query) or a JSON body `id` field
 * (query wins if both are present). Every mutation is re-verified
 * against the path's widget id, not just the org, since an operator
 * id alone only proves org ownership (via `ownedOperator()`), not
 * that it belongs to *this* widget.
 */

function widgetIdFromBusiness(business: string | TochatWidget | undefined): string {
  if (business && typeof business === "object") {
    return String(business.uuid ?? business.id ?? "");
  }
  const iri = String(business ?? "").replace(/\/$/, "");
  return iri.slice(iri.lastIndexOf("/") + 1);
}

async function resolveOperatorId(request: Request): Promise<string | null> {
  const url = new URL(request.url);
  const fromQuery = url.searchParams.get("id");
  if (fromQuery) return fromQuery;
  try {
    const body = (await request.clone().json()) as { id?: string };
    return body.id ?? null;
  } catch {
    return null;
  }
}

export async function GET(
  _request: Request,
  { params }: { params: Promise<{ id: string }> }
) {
  const { id } = await params;
  const result = await resolveOrgContext();
  if (!result.ok) return result.response;
  const { ctx } = result;

  try {
    const operators = await operatorsForWidget(id, ctx.userClient);
    return NextResponse.json({ operators });
  } catch (err) {
    return tochatErrorResponse(err);
  }
}

export async function POST(
  request: Request,
  { params }: { params: Promise<{ id: string }> }
) {
  const { id } = await params;
  const result = await resolveOrgContext();
  if (!result.ok) return result.response;
  const { ctx } = result;

  let payload: Record<string, unknown>;
  try {
    payload = await request.json();
  } catch {
    return jsonError("Invalid JSON body.", 400);
  }

  try {
    // Org-wide agent count — no per-agent cache table exists yet, so
    // this is a live Tochat call (acceptable: agent creation is rare
    // compared to widget reads).
    const currentAgents = await listOperators(ctx.userClient);
    if (!withinLimit(ctx.org, "agents", currentAgents.length)) {
      return jsonError("Agent limit reached for your plan. Upgrade to add more agents.", 403);
    }

    const created = await createOperator(
      { ...payload, business: id },
      ctx.userClient
    );
    return NextResponse.json({ operator: created }, { status: 201 });
  } catch (err) {
    return tochatErrorResponse(err);
  }
}

export async function PUT(
  request: Request,
  { params }: { params: Promise<{ id: string }> }
) {
  const { id } = await params;
  const result = await resolveOrgContext();
  if (!result.ok) return result.response;
  const { ctx } = result;

  const operatorId = await resolveOperatorId(request);
  if (!operatorId) {
    return jsonError("Missing operator id (pass ?id= or a JSON body `id` field).", 400);
  }

  let payload: Record<string, unknown>;
  try {
    payload = await request.json();
  } catch {
    return jsonError("Invalid JSON body.", 400);
  }

  try {
    const existing = await getOperator(operatorId, ctx.userClient);
    if (widgetIdFromBusiness(existing.business) !== id) {
      return jsonError("Agent not found on this widget.", 404);
    }
    const updated = await updateOperator(operatorId, payload, ctx.userClient);
    return NextResponse.json({ operator: updated });
  } catch (err) {
    return tochatErrorResponse(err);
  }
}

export async function DELETE(
  request: Request,
  { params }: { params: Promise<{ id: string }> }
) {
  const { id } = await params;
  const result = await resolveOrgContext();
  if (!result.ok) return result.response;
  const { ctx } = result;

  const operatorId = await resolveOperatorId(request);
  if (!operatorId) {
    return jsonError("Missing operator id (pass ?id= or a JSON body `id` field).", 400);
  }

  try {
    const existing: TochatOperator = await getOperator(operatorId, ctx.userClient);
    if (widgetIdFromBusiness(existing.business) !== id) {
      return jsonError("Agent not found on this widget.", 404);
    }
    await deleteOperator(operatorId, ctx.userClient);
    return NextResponse.json({ success: true });
  } catch (err) {
    return tochatErrorResponse(err);
  }
}
