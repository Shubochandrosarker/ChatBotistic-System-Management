import { NextResponse } from "next/server";
import { createFaq, deleteFaq, listFaqs, resourceId, updateFaq } from "@/lib/tochat/client";
import { jsonError, resolveOrgContext, tochatErrorResponse } from "@/lib/org-context";

/**
 * /api/tochat/operators/[id]/faq-groups — FAQ CRUD for one agent.
 *
 * PUT/DELETE identify *which* FAQ to mutate via `?id=` (query) or a
 * JSON body `id` field. src/lib/tochat/client.ts's `updateFaq`/
 * `deleteFaq` already verify the FAQ's parent operator belongs to the
 * caller's org (`ownedOperator`), but not that it belongs to *this*
 * path's operator id — we check that here by confirming the target
 * FAQ id shows up in this operator's own FAQ list first.
 */

async function resolveFaqId(request: Request): Promise<string | null> {
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
    const faqs = await listFaqs(id, ctx.userClient);
    return NextResponse.json({ faqs });
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
    const created = await createFaq({ ...payload, whatsapp: id }, ctx.userClient);
    return NextResponse.json({ faq: created }, { status: 201 });
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

  const faqId = await resolveFaqId(request);
  if (!faqId) {
    return jsonError("Missing FAQ id (pass ?id= or a JSON body `id` field).", 400);
  }

  let payload: Record<string, unknown>;
  try {
    payload = await request.json();
  } catch {
    return jsonError("Invalid JSON body.", 400);
  }

  try {
    const faqs = await listFaqs(id, ctx.userClient);
    if (!faqs.some((f) => resourceId(f) === faqId)) {
      return jsonError("FAQ not found on this agent.", 404);
    }
    const updated = await updateFaq(faqId, payload, ctx.userClient);
    return NextResponse.json({ faq: updated });
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

  const faqId = await resolveFaqId(request);
  if (!faqId) {
    return jsonError("Missing FAQ id (pass ?id= or a JSON body `id` field).", 400);
  }

  try {
    const faqs = await listFaqs(id, ctx.userClient);
    if (!faqs.some((f) => resourceId(f) === faqId)) {
      return jsonError("FAQ not found on this agent.", 404);
    }
    await deleteFaq(faqId, ctx.userClient);
    return NextResponse.json({ success: true });
  } catch (err) {
    return tochatErrorResponse(err);
  }
}
