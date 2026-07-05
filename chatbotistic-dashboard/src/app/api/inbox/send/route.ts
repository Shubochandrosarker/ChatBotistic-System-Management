import { NextResponse } from "next/server";
import { resolveOrgContext } from "@/lib/org-context";
import { loadProviderForOrg } from "@/lib/whatsapp/load-provider";
import { currentPeriodMonth, limitOf, withinLimit } from "@/lib/plans";

/**
 * POST /api/inbox/send  { conversationId: string, body: string }
 *
 * Sends an outbound WhatsApp reply from the shared team Inbox. Has to
 * be a server route (not a direct browser-client insert) because it:
 *   - calls out to the org's live WhatsApp provider
 *     (`loadProviderForOrg` — Meta Cloud API or Twilio, whichever the
 *     org connected in Settings),
 *   - enforces the org's monthly message quota before sending, and
 *   - increments the shared `message_usage` counter via the
 *     `increment_message_usage` RPC (SECURITY DEFINER, see
 *     004_campaigns_usage_whatsapp.sql) — the single write path for
 *     that counter so concurrent sends (Inbox + Campaigns) never race
 *     a read-then-write.
 *
 * Quota gate mirrors the Campaigns wizard's UX
 * (src/components/campaigns/campaign-wizard.tsx's `quotaExhausted`/
 * `canSendNow` logic): block with a clear 403 error rather than
 * silently no-op.
 */
export async function POST(request: Request) {
  const result = await resolveOrgContext();
  if (!result.ok) return result.response;
  const { ctx } = result;

  let payload: { conversationId?: unknown; body?: unknown };
  try {
    payload = await request.json();
  } catch {
    return NextResponse.json({ error: "Invalid JSON body." }, { status: 400 });
  }

  const conversationId = typeof payload.conversationId === "string" ? payload.conversationId : "";
  const body = typeof payload.body === "string" ? payload.body.trim() : "";

  if (!conversationId || !body) {
    return NextResponse.json(
      { error: "conversationId and a non-empty body are required." },
      { status: 400 }
    );
  }

  const { data: conversation, error: conversationError } = await ctx.supabase
    .from("conversations")
    .select("id, contact_phone")
    .eq("id", conversationId)
    .eq("org_id", ctx.org.id)
    .maybeSingle();

  if (conversationError) {
    return NextResponse.json({ error: conversationError.message }, { status: 500 });
  }
  if (!conversation) {
    return NextResponse.json({ error: "Conversation not found." }, { status: 404 });
  }

  const period = currentPeriodMonth();
  const { data: usage, error: usageError } = await ctx.supabase
    .from("message_usage")
    .select("messages_sent")
    .eq("org_id", ctx.org.id)
    .eq("period_month", period)
    .maybeSingle();

  if (usageError) {
    return NextResponse.json({ error: usageError.message }, { status: 500 });
  }

  const used = usage?.messages_sent ?? 0;
  const limit = limitOf(ctx.org, "messages_per_month");

  if (!withinLimit(ctx.org, "messages_per_month", used)) {
    return NextResponse.json(
      { error: "You've used all of this month's message quota. Upgrade your plan to keep sending." },
      { status: 403 }
    );
  }

  const loaded = await loadProviderForOrg(ctx.org.id);
  if (!loaded) {
    return NextResponse.json({ error: "Connect WhatsApp in Settings to reply." }, { status: 409 });
  }

  let sendResult: { messageId: string };
  try {
    // `conversation.contact_phone` was stored in whatever raw format
    // this org's own provider's inbound webhook delivered it in (see
    // src/app/api/webhooks/meta/route.ts and .../twilio/route.ts) —
    // since an org only ever has one active provider connection at a
    // time, sending back through that same provider with that same
    // format is always consistent, even though Meta and Twilio prefer
    // slightly different phone-number formatting conventions.
    sendResult = await loaded.provider.sendText(conversation.contact_phone, body);
  } catch (err) {
    const message = err instanceof Error ? err.message : "Failed to send message.";
    return NextResponse.json({ error: message }, { status: 502 });
  }

  const nowIso = new Date().toISOString();
  const { data: inserted, error: insertError } = await ctx.supabase
    .from("messages")
    .insert({
      org_id: ctx.org.id,
      conversation_id: conversationId,
      direction: "outbound",
      body,
      provider_message_id: sendResult.messageId,
      status: "sent",
    })
    .select("*")
    .single();

  if (insertError) {
    return NextResponse.json({ error: insertError.message }, { status: 500 });
  }

  const { error: updateError } = await ctx.supabase
    .from("conversations")
    .update({ last_message_at: nowIso })
    .eq("id", conversationId)
    .eq("org_id", ctx.org.id);

  if (updateError) {
    console.error("[inbox/send] failed to bump conversation.last_message_at:", updateError.message);
  }

  const { data: newTotal, error: usageRpcError } = await ctx.supabase.rpc("increment_message_usage", {
    p_org_id: ctx.org.id,
    p_period: period,
    p_count: 1,
  });

  if (usageRpcError) {
    console.error("[inbox/send] failed to increment message_usage:", usageRpcError.message);
  }

  return NextResponse.json({
    message: inserted,
    messagesUsed: typeof newTotal === "number" ? newTotal : used + 1,
    messagesLimit: limit,
  });
}
