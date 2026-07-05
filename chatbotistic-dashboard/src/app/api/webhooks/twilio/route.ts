import { NextResponse } from "next/server";
import { decrypt } from "@/lib/encryption";
import { supabaseAdmin } from "@/lib/supabase/admin";
import { createTwilioProvider, type TwilioCredentials } from "@/lib/whatsapp/twilio-provider";

/**
 * Twilio WhatsApp inbound webhook. MUST be mounted at exactly this
 * path — `src/lib/whatsapp/twilio-provider.ts` exports
 * `TWILIO_WEBHOOK_PATH = "/api/webhooks/twilio"` and reconstructs the
 * request URL from that constant to verify `X-Twilio-Signature`
 * (Twilio's HMAC covers the exact URL it was configured to call).
 * This file lives at `src/app/api/webhooks/twilio/route.ts`, matching.
 *
 * BYO multi-tenant, same shape as the Meta webhook in
 * src/app/api/webhooks/meta/route.ts: every org connects its own
 * Twilio account + WhatsApp sender, so the org has to be identified
 * from the request itself (via the `To` number) before anything can
 * be verified.
 *
 * POST only — Twilio's inbound-message and status-callback webhooks
 * are both POST with `application/x-www-form-urlencoded` bodies.
 */

function stripWhatsappPrefix(value: string): string {
  return value.startsWith("whatsapp:") ? value.slice("whatsapp:".length) : value;
}

/**
 * Twilio's `MessageStatus` values collapse onto our narrower
 * `messages.status` enum (sent/delivered/read/failed):
 * queued/accepted/sending/sent all mean "Twilio has it, not yet
 * delivered" -> "sent"; undelivered -> "failed".
 */
function mapTwilioStatus(status: string): "sent" | "delivered" | "read" | "failed" | null {
  switch (status) {
    case "queued":
    case "accepted":
    case "sending":
    case "sent":
      return "sent";
    case "delivered":
      return "delivered";
    case "read":
      return "read";
    case "failed":
    case "undelivered":
      return "failed";
    default:
      return null;
  }
}

export async function POST(request: Request) {
  // Read the raw body ONCE as text — Twilio's signature covers the
  // exact form-encoded bytes, and the same string is parsed into
  // fields below.
  const rawBody = await request.text();
  const params = new URLSearchParams(rawBody);

  const to = params.get("To");
  const from = params.get("From");
  const body = params.get("Body");
  const messageSid = params.get("MessageSid");
  const messageStatus = params.get("MessageStatus");

  if (!to || !messageSid) {
    return new NextResponse(null, { status: 200 });
  }

  const toNumber = stripWhatsappPrefix(to).trim();
  const admin = supabaseAdmin();

  // Scaling caveat: identifying the owning org means decrypting every
  // BYO-Twilio org's credentials and comparing WhatsApp numbers — O(n)
  // in the number of Twilio connections on the whole platform. Same
  // note as the Meta webhook: fine at current scale, would want a
  // plaintext indexed lookup column if this becomes a bottleneck.
  const { data: candidates, error: candidatesError } = await admin
    .from("whatsapp_connections")
    .select("org_id, credentials_encrypted")
    .eq("provider", "twilio")
    .eq("status", "connected");

  if (candidatesError) {
    console.error("[webhooks/twilio] failed to load candidate connections:", candidatesError.message);
    return new NextResponse(null, { status: 200 });
  }

  let orgId: string | null = null;
  let creds: TwilioCredentials | null = null;

  for (const candidate of candidates ?? []) {
    if (!candidate.credentials_encrypted) continue;
    try {
      const parsed = JSON.parse(decrypt(candidate.credentials_encrypted)) as TwilioCredentials;
      if (stripWhatsappPrefix(parsed.whatsappNumber).trim() === toNumber) {
        orgId = candidate.org_id as string;
        creds = parsed;
        break;
      }
    } catch {
      continue;
    }
  }

  if (!orgId || !creds) {
    console.error(`[webhooks/twilio] no connected org matched WhatsApp number ${toNumber}`);
    return new NextResponse(null, { status: 200 });
  }

  const provider = createTwilioProvider(creds);
  if (!provider.verifyWebhookSignature(rawBody, request.headers)) {
    return NextResponse.json({ error: "Invalid signature." }, { status: 401 });
  }

  try {
    if (messageStatus && !body) {
      // Delivery/read status callback for a previously-sent outbound message.
      const mapped = mapTwilioStatus(messageStatus);
      if (mapped) {
        const { error } = await admin
          .from("messages")
          .update({ status: mapped })
          .eq("org_id", orgId)
          .eq("provider_message_id", messageSid);
        if (error) {
          console.error("[webhooks/twilio] failed to update message status:", error.message);
        }
      }
    } else if (from && body) {
      const contactPhone = stripWhatsappPrefix(from).trim();
      const conversationId = await upsertInboundConversation(admin, orgId, contactPhone);
      if (conversationId) {
        const { error } = await admin.from("messages").insert({
          org_id: orgId,
          conversation_id: conversationId,
          direction: "inbound",
          body,
          provider_message_id: messageSid,
        });
        if (error) {
          console.error("[webhooks/twilio] failed to insert inbound message:", error.message);
        }
      }
    }
  } catch (err) {
    console.error("[webhooks/twilio] processing error:", err instanceof Error ? err.message : err);
  }

  // Twilio accepts a plain empty 200 for both inbound-message and
  // status-callback webhooks — no TwiML body is required unless you
  // want Twilio to auto-reply, which the Inbox handles itself via
  // POST /api/inbox/send.
  return new NextResponse(null, { status: 200 });
}

/** Same upsert shape as src/app/api/webhooks/meta/route.ts's helper — duplicated here rather than shared, since this task's scope is limited to files under src/app/api/webhooks/twilio/. */
async function upsertInboundConversation(
  admin: ReturnType<typeof supabaseAdmin>,
  orgId: string,
  contactPhone: string
): Promise<string | null> {
  const nowIso = new Date().toISOString();

  const { data: existing, error: existingError } = await admin
    .from("conversations")
    .select("id, unread_count")
    .eq("org_id", orgId)
    .eq("contact_phone", contactPhone)
    .maybeSingle();

  if (existingError) {
    console.error("[webhooks/twilio] failed to look up conversation:", existingError.message);
    return null;
  }

  if (existing) {
    const { error } = await admin
      .from("conversations")
      .update({ last_message_at: nowIso, unread_count: (existing.unread_count ?? 0) + 1 })
      .eq("id", existing.id);
    if (error) {
      console.error("[webhooks/twilio] failed to update conversation:", error.message);
      return null;
    }
    return existing.id;
  }

  const { data: inserted, error: insertError } = await admin
    .from("conversations")
    .insert({ org_id: orgId, contact_phone: contactPhone, last_message_at: nowIso, unread_count: 1 })
    .select("id")
    .single();

  if (insertError) {
    console.error("[webhooks/twilio] failed to insert conversation:", insertError.message);
    return null;
  }
  return inserted.id as string;
}
