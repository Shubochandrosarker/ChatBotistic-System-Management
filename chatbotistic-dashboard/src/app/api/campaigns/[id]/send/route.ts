import { NextResponse } from "next/server";
import { jsonError, resolveOrgContext } from "@/lib/org-context";
import { currentPeriodMonth, limitOf } from "@/lib/plans";
import { supabaseAdmin } from "@/lib/supabase/admin";
import { loadProviderForOrg } from "@/lib/whatsapp/load-provider";

/**
 * POST /api/campaigns/[id]/send
 *
 * Fires a draft/scheduled campaign through the org's own connected
 * WhatsApp provider (Meta or Twilio — never "personal", which is
 * credential-less by design; see `loadProviderForOrg`).
 *
 * Quota policy (v1): reject the whole send up front if the resolved
 * audience is larger than the org's remaining monthly quota, rather
 * than partially sending and leaving the campaign in an ambiguous
 * half-sent state. This is simpler to reason about for the org (one
 * clear error telling them exactly how many they can send) at the
 * cost of not being able to "send what you can, schedule the rest" —
 * an acceptable v1 trade-off per the task brief.
 *
 * Usage accounting (v1): `increment_message_usage` and the campaign's
 * `sent_count` are both updated once, after the loop, with the total
 * successful-send count — not once per message. Per-recipient sends
 * still get individual try/catch handling (one bad number never
 * aborts the rest of the batch), but batching the two counter writes
 * avoids N round-trips for what both the RPC (SECURITY DEFINER,
 * additive) and the final `sent_count` update are always going to
 * converge to the same total for anyway. This mirrors the batched
 * option the wizard's own pre-existing TODO comment called out
 * ("once per message actually sent (or once with the batch count)").
 * Trade-off: if the process crashes mid-loop, already-sent messages
 * for that run won't be reflected in usage/sent_count — acceptable
 * for v1, not silently double-counted on any retry either since the
 * campaign is flipped to 'sending' (and stays there) before the loop
 * starts, blocking a second concurrent/duplicate send.
 */

interface CampaignRow {
  id: string;
  org_id: string;
  status: "draft" | "scheduled" | "sending" | "sent";
  message_body: string | null;
  audience: CampaignAudience | null;
  sent_count: number;
}

/** Matches exactly what `campaign-wizard.tsx`'s `submit()` writes. */
interface CampaignAudience {
  mode?: "all" | "widget" | "status";
  widgetId?: string | null;
  status?: string | null;
  estimatedCount?: number;
}

interface LeadRow {
  phone: string | null;
  name: string | null;
  widget_id: string | null;
}

interface Recipient {
  phone: string;
  name: string | null;
  widgetName: string | null;
}

/** Matches the `{{name}}` / `{{phone}}` / `{{widget}}` chips the wizard inserts. */
function personalize(template: string, recipient: Recipient): string {
  return template
    .replaceAll("{{name}}", recipient.name?.trim() || "there")
    .replaceAll("{{phone}}", recipient.phone)
    .replaceAll("{{widget}}", recipient.widgetName || "");
}

export async function POST(
  _request: Request,
  { params }: { params: Promise<{ id: string }> }
) {
  const result = await resolveOrgContext();
  if (!result.ok) return result.response;
  const { ctx } = result;
  const { id } = await params;

  const { data: campaignData, error: campaignError } = await ctx.supabase
    .from("campaigns")
    .select("id, org_id, status, message_body, audience, sent_count")
    .eq("id", id)
    .eq("org_id", ctx.org.id)
    .maybeSingle();

  if (campaignError) {
    return NextResponse.json({ error: campaignError.message }, { status: 500 });
  }
  if (!campaignData) {
    return jsonError("Campaign not found.", 404);
  }

  const campaign = campaignData as CampaignRow;

  if (campaign.status !== "draft" && campaign.status !== "scheduled") {
    return jsonError(
      campaign.status === "sending"
        ? "This campaign is already sending."
        : "This campaign has already been sent.",
      409
    );
  }

  if (!campaign.message_body || !campaign.message_body.trim()) {
    return jsonError("This campaign has no message body to send.", 400);
  }

  const providerBundle = await loadProviderForOrg(ctx.org.id);
  if (!providerBundle) {
    return jsonError(
      "No WhatsApp connection configured — connect one in Settings.",
      400
    );
  }
  const { provider } = providerBundle;

  // Resolve the audience the wizard recorded into an actual, deduped
  // set of leads with phone numbers.
  const audience: CampaignAudience = campaign.audience ?? { mode: "all" };
  let leadsQuery = ctx.supabase
    .from("leads")
    .select("phone, name, widget_id")
    .eq("org_id", ctx.org.id);

  if (audience.mode === "widget" && audience.widgetId) {
    leadsQuery = leadsQuery.eq("widget_id", audience.widgetId);
  } else if (audience.mode === "status" && audience.status) {
    leadsQuery = leadsQuery.eq("status", audience.status);
  }

  const { data: leadRows, error: leadsError } = await leadsQuery;
  if (leadsError) {
    return NextResponse.json({ error: leadsError.message }, { status: 500 });
  }

  const widgetIds = new Set<string>();
  for (const l of (leadRows ?? []) as LeadRow[]) {
    if (l.widget_id) widgetIds.add(l.widget_id);
  }
  const widgetNames: Record<string, string> = {};
  if (widgetIds.size > 0) {
    const { data: widgetRows } = await ctx.supabase
      .from("widgets_cache")
      .select("tochat_widget_id, name")
      .eq("org_id", ctx.org.id)
      .in("tochat_widget_id", Array.from(widgetIds));
    for (const w of widgetRows ?? []) {
      if (w.tochat_widget_id) widgetNames[w.tochat_widget_id] = w.name || w.tochat_widget_id;
    }
  }

  const seenPhones = new Set<string>();
  const recipients: Recipient[] = [];
  for (const l of (leadRows ?? []) as LeadRow[]) {
    const phone = l.phone?.trim();
    if (!phone || seenPhones.has(phone)) continue;
    seenPhones.add(phone);
    recipients.push({
      phone,
      name: l.name,
      widgetName: l.widget_id ? widgetNames[l.widget_id] ?? null : null,
    });
  }

  if (recipients.length === 0) {
    return jsonError(
      "No leads with a phone number match this campaign's audience.",
      400
    );
  }

  // Quota check — reject the whole send if it would exceed what's left
  // this month (see file-level comment for why this beats a partial send).
  const period = currentPeriodMonth();
  const { data: usageRow } = await ctx.supabase
    .from("message_usage")
    .select("messages_sent")
    .eq("org_id", ctx.org.id)
    .eq("period_month", period)
    .maybeSingle();

  const used = usageRow?.messages_sent ?? 0;
  const limit = limitOf(ctx.org, "messages_per_month");
  const remaining = limit < 0 ? Infinity : Math.max(limit - used, 0);

  if (recipients.length > remaining) {
    return jsonError(
      `This campaign would send ${recipients.length} messages, but your plan only has ${remaining} left this month. Trim the audience or upgrade your plan.`,
      400
    );
  }

  // Atomically flip draft/scheduled -> sending. If no row comes back,
  // another request already claimed this send (race) — bail out
  // rather than sending twice.
  const { data: claimed, error: claimError } = await ctx.supabase
    .from("campaigns")
    .update({ status: "sending" })
    .eq("id", campaign.id)
    .eq("org_id", ctx.org.id)
    .in("status", ["draft", "scheduled"])
    .select("id");

  if (claimError) {
    return NextResponse.json({ error: claimError.message }, { status: 500 });
  }
  if (!claimed || claimed.length === 0) {
    return jsonError("This campaign is already being sent.", 409);
  }

  let sent = 0;
  let failed = 0;
  for (let i = 0; i < recipients.length; i++) {
    const recipient = recipients[i];
    try {
      await provider.sendText(recipient.phone, personalize(campaign.message_body, recipient));
      sent++;
    } catch (err) {
      failed++;
      console.error(
        `[campaigns/send] failed to send campaign ${campaign.id} to ${recipient.phone}:`,
        err instanceof Error ? err.message : err
      );
    }
    // Small courtesy delay between sends — not a real rate limiter,
    // just enough to avoid hammering the provider in a tight loop.
    if (i < recipients.length - 1) {
      await new Promise((resolve) => setTimeout(resolve, 150));
    }
  }

  if (sent > 0) {
    // SECURITY DEFINER RPC — using the service-role client here since
    // 004's migration never explicitly GRANTs EXECUTE to `authenticated`
    // (only the implicit ownership grant is set up), so this doesn't
    // rely on that grant existing.
    const admin = supabaseAdmin();
    const { error: usageError } = await admin.rpc("increment_message_usage", {
      p_org_id: ctx.org.id,
      p_period: period,
      p_count: sent,
    });
    if (usageError) {
      console.error(
        `[campaigns/send] failed to record message usage for org ${ctx.org.id}:`,
        usageError.message
      );
    }
  }

  const { error: finalUpdateError } = await ctx.supabase
    .from("campaigns")
    .update({ status: "sent", sent_count: campaign.sent_count + sent })
    .eq("id", campaign.id)
    .eq("org_id", ctx.org.id);

  if (finalUpdateError) {
    console.error(
      `[campaigns/send] failed to finalize campaign ${campaign.id}:`,
      finalUpdateError.message
    );
  }

  return NextResponse.json({ sent, failed, total: recipients.length });
}
