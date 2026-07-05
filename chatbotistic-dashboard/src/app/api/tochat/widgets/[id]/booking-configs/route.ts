import { NextResponse } from "next/server";
import {
  createBookingConfig,
  deleteBookingConfig,
  getOperator,
  listBookingConfigs,
  operatorsForWidget,
  resourceId,
  updateBookingConfig,
} from "@/lib/tochat/client";
import type { TochatBookingConfig, TochatWidget } from "@/lib/tochat/types";
import { jsonError, resolveOrgContext, tochatErrorResponse } from "@/lib/org-context";
import { can } from "@/lib/plans";

/**
 * /api/tochat/widgets/[id]/booking-configs — booking configs, keyed to
 * an *operator* on Tochat's side (src/lib/tochat/types.ts:
 * `TochatBookingConfig.whatsapp`), not the widget directly. This route
 * fans the widget id out to its operators:
 *
 * - GET aggregates every booking config across all of the widget's
 *   operators.
 * - POST requires a JSON body `whatsapp` (operator id) and verifies
 *   that operator belongs to this widget before creating.
 * - PUT/DELETE identify the booking config via `?id=` / JSON body
 *   `id`, and are only allowed to touch a config whose operator
 *   belongs to this widget.
 */

function widgetIdFromBusiness(business: string | TochatWidget | undefined): string {
  if (business && typeof business === "object") {
    return String(business.uuid ?? business.id ?? "");
  }
  const iri = String(business ?? "").replace(/\/$/, "");
  return iri.slice(iri.lastIndexOf("/") + 1);
}

async function resolveConfigId(request: Request): Promise<string | null> {
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

/** Booking configs across every operator on this widget, plus the id
 * of the operator that owns the matching config, if found. */
async function findConfigOnWidget(
  widgetId: string,
  configId: string,
  userClient: string
): Promise<{ operatorId: string; config: TochatBookingConfig } | null> {
  const operators = await operatorsForWidget(widgetId, userClient);
  for (const operator of operators) {
    const operatorId = String(operator.uuid ?? operator.id ?? "");
    if (!operatorId) continue;
    const configs = await listBookingConfigs(operatorId, userClient);
    const match = configs.find((c) => resourceId(c) === configId);
    if (match) return { operatorId, config: match };
  }
  return null;
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
    const perOperator = await Promise.all(
      operators.map(async (operator) => {
        const operatorId = String(operator.uuid ?? operator.id ?? "");
        if (!operatorId) return [];
        return listBookingConfigs(operatorId, ctx.userClient);
      })
    );
    return NextResponse.json({ bookingConfigs: perOperator.flat() });
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

  if (!can(ctx.org, "booking_forms")) {
    return jsonError("Booking forms are not included in your plan. Upgrade to enable them.", 403);
  }

  let payload: Record<string, unknown>;
  try {
    payload = await request.json();
  } catch {
    return jsonError("Invalid JSON body.", 400);
  }

  const operatorId = String(payload.whatsapp ?? "");
  if (!operatorId) {
    return jsonError("Missing `whatsapp` (operator id) in the request body.", 400);
  }

  try {
    const operator = await getOperator(operatorId, ctx.userClient);
    if (widgetIdFromBusiness(operator.business) !== id) {
      return jsonError("Agent not found on this widget.", 404);
    }
    const created = await createBookingConfig(payload, ctx.userClient);
    return NextResponse.json({ bookingConfig: created }, { status: 201 });
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

  const configId = await resolveConfigId(request);
  if (!configId) {
    return jsonError("Missing booking config id (pass ?id= or a JSON body `id` field).", 400);
  }

  let payload: Record<string, unknown>;
  try {
    payload = await request.json();
  } catch {
    return jsonError("Invalid JSON body.", 400);
  }

  try {
    const found = await findConfigOnWidget(id, configId, ctx.userClient);
    if (!found) {
      return jsonError("Booking config not found on this widget.", 404);
    }
    const updated = await updateBookingConfig(configId, payload, ctx.userClient);
    return NextResponse.json({ bookingConfig: updated });
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

  const configId = await resolveConfigId(request);
  if (!configId) {
    return jsonError("Missing booking config id (pass ?id= or a JSON body `id` field).", 400);
  }

  try {
    const found = await findConfigOnWidget(id, configId, ctx.userClient);
    if (!found) {
      return jsonError("Booking config not found on this widget.", 404);
    }
    await deleteBookingConfig(configId, ctx.userClient);
    return NextResponse.json({ success: true });
  } catch (err) {
    return tochatErrorResponse(err);
  }
}
