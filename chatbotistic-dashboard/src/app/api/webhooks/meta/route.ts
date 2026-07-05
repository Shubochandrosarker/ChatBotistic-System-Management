import { NextResponse } from "next/server";
import { decrypt } from "@/lib/encryption";
import { supabaseAdmin } from "@/lib/supabase/admin";
import { createMetaProvider, type MetaCredentials } from "@/lib/whatsapp/meta-provider";

/**
 * Meta WhatsApp Cloud API inbound webhook. This is a BYO multi-tenant
 * setup — every org runs its own Meta app (its own verify token, app
 * secret, phone_number_id), so there is no single shared platform
 * secret to check against. Both handlers have to first figure out
 * *which org* a request belongs to before they can validate it.
 */

const MESSAGE_STATUSES = new Set(["sent", "delivered", "read", "failed"]);

interface MetaWebhookPayload {
  entry?: Array<{
    changes?: Array<{
      value?: {
        metadata?: { phone_number_id?: string };
        contacts?: Array<{ profile?: { name?: string }; wa_id?: string }>;
        messages?: Array<{
          from?: string;
          id?: string;
          type?: string;
          text?: { body?: string };
        }>;
        statuses?: Array<{
          id?: string;
          status?: string;
        }>;
      };
    }>;
  }>;
}

/**
 * GET — Meta's webhook verification handshake (`hub.mode` /
 * `hub.verify_token` / `hub.challenge`). Each org's own verify token
 * is stored on their `whatsapp_connections` row
 * (`webhook_verify_token`, issued in src/app/api/settings/whatsapp/
 * route.ts when they connect Meta), so verification is "does any org
 * have this token" rather than a single global secret comparison.
 */
export async function GET(request: Request) {
  const url = new URL(request.url);
  const mode = url.searchParams.get("hub.mode");
  const verifyToken = url.searchParams.get("hub.verify_token");
  const challenge = url.searchParams.get("hub.challenge");

  if (!mode || !verifyToken || challenge === null) {
    return NextResponse.json({ error: "Missing verification parameters." }, { status: 400 });
  }

  const admin = supabaseAdmin();
  const { data: connection } = await admin
    .from("whatsapp_connections")
    .select("id")
    .eq("webhook_verify_token", verifyToken)
    .eq("provider", "meta")
    .maybeSingle();

  if (!connection || mode !== "subscribe") {
    return NextResponse.json({ error: "Verification failed." }, { status: 403 });
  }

  // Meta requires the raw challenge string back as text/plain — not JSON.
  return new NextResponse(challenge, { status: 200, headers: { "Content-Type": "text/plain" } });
}

/**
 * POST — inbound messages + delivery/read status callbacks.
 *
 * Always resolves 200 once the org has been identified and the
 * signature has checked out, even if inner per-message processing
 * fails — Meta retries aggressively on non-200/timeout, and a single
 * malformed message in a batch shouldn't cause the whole webhook
 * delivery (and everything else in the same payload) to be retried
 * forever. Errors are logged instead of thrown.
 *
 * The one case that does NOT return 200 is a signature that fails to
 * verify once an org has been matched — that's a fail-closed reject
 * (401), per `WhatsAppProvider.verifyWebhookSignature`'s contract.
 */
export async function POST(request: Request) {
  // Read the raw body ONCE as text — needed verbatim (exact bytes) for
  // HMAC signature verification, and then JSON.parse'd from the same
  // string for processing.
  const rawBody = await request.text();

  let payload: MetaWebhookPayload;
  try {
    payload = JSON.parse(rawBody) as MetaWebhookPayload;
  } catch {
    // Nothing usable here, but still 200 so Meta doesn't retry a body
    // that will never parse.
    return NextResponse.json({ ok: true });
  }

  const phoneNumberId = payload.entry?.[0]?.changes?.[0]?.value?.metadata?.phone_number_id;
  if (!phoneNumberId) {
    return NextResponse.json({ ok: true });
  }

  const admin = supabaseAdmin();

  // Scaling caveat: identifying the owning org means decrypting every
  // BYO-Meta org's credentials and comparing phone_number_id — O(n) in
  // the number of Meta connections on the whole platform. Fine at
  // current scale; if this becomes a bottleneck, mirror
  // phone_number_id into a plaintext indexed column on
  // whatsapp_connections at connect-time so this becomes a single
  // indexed lookup instead of a decrypt loop.
  const { data: candidates, error: candidatesError } = await admin
    .from("whatsapp_connections")
    .select("org_id, credentials_encrypted")
    .eq("provider", "meta")
    .eq("status", "connected");

  if (candidatesError) {
    console.error("[webhooks/meta] failed to load candidate connections:", candidatesError.message);
    return NextResponse.json({ ok: true });
  }

  let orgId: string | null = null;
  let creds: MetaCredentials | null = null;

  for (const candidate of candidates ?? []) {
    if (!candidate.credentials_encrypted) continue;
    try {
      const parsed = JSON.parse(decrypt(candidate.credentials_encrypted)) as MetaCredentials;
      if (parsed.phoneNumberId === phoneNumberId) {
        orgId = candidate.org_id as string;
        creds = parsed;
        break;
      }
    } catch {
      // Not this org (or corrupt row) — keep looking.
      continue;
    }
  }

  if (!orgId || !creds) {
    console.error(`[webhooks/meta] no connected org matched phone_number_id ${phoneNumberId}`);
    return NextResponse.json({ ok: true });
  }

  const provider = createMetaProvider(creds);
  if (!provider.verifyWebhookSignature(rawBody, request.headers)) {
    return NextResponse.json({ error: "Invalid signature." }, { status: 401 });
  }

  try {
    for (const entry of payload.entry ?? []) {
      for (const change of entry.changes ?? []) {
        const value = change.value;
        if (!value) continue;

        for (const message of value.messages ?? []) {
          if (!message.from) continue;

          const contactName =
            value.contacts?.find((c) => c.wa_id === message.from)?.profile?.name ?? null;
          // Only plain text bodies are modeled today; other inbound
          // types (image/audio/location/interactive replies/...) are
          // recorded with a placeholder body so the message still
          // shows up in the thread rather than silently vanishing.
          const body =
            !message.type || message.type === "text"
              ? message.text?.body ?? null
              : `[${message.type} message]`;

          const conversationId = await upsertInboundConversation(admin, orgId, message.from, contactName);
          if (!conversationId) continue;

          const { error: msgError } = await admin.from("messages").insert({
            org_id: orgId,
            conversation_id: conversationId,
            direction: "inbound",
            body,
            provider_message_id: message.id ?? null,
          });
          if (msgError) {
            console.error("[webhooks/meta] failed to insert inbound message:", msgError.message);
          }
        }

        for (const status of value.statuses ?? []) {
          if (!status.id || !status.status || !MESSAGE_STATUSES.has(status.status)) continue;
          const { error: statusError } = await admin
            .from("messages")
            .update({ status: status.status })
            .eq("org_id", orgId)
            .eq("provider_message_id", status.id);
          if (statusError) {
            console.error("[webhooks/meta] failed to update message status:", statusError.message);
          }
        }
      }
    }
  } catch (err) {
    console.error("[webhooks/meta] processing error:", err instanceof Error ? err.message : err);
  }

  return NextResponse.json({ ok: true });
}

/**
 * Upsert the conversation an inbound message belongs to (by
 * `org_id, contact_phone`), bumping `unread_count` and
 * `last_message_at`. Returns the conversation id, or null on failure
 * (logged, never thrown — callers should skip the message insert but
 * keep processing the rest of the payload).
 */
async function upsertInboundConversation(
  admin: ReturnType<typeof supabaseAdmin>,
  orgId: string,
  contactPhone: string,
  contactName: string | null
): Promise<string | null> {
  const nowIso = new Date().toISOString();

  const { data: existing, error: existingError } = await admin
    .from("conversations")
    .select("id, contact_name, unread_count")
    .eq("org_id", orgId)
    .eq("contact_phone", contactPhone)
    .maybeSingle();

  if (existingError) {
    console.error("[webhooks/meta] failed to look up conversation:", existingError.message);
    return null;
  }

  if (existing) {
    const { error: updateError } = await admin
      .from("conversations")
      .update({
        last_message_at: nowIso,
        unread_count: (existing.unread_count ?? 0) + 1,
        // Only fill in a name if we didn't have one yet — never
        // clobber a name the team may have edited.
        ...(contactName && !existing.contact_name ? { contact_name: contactName } : {}),
      })
      .eq("id", existing.id);
    if (updateError) {
      console.error("[webhooks/meta] failed to update conversation:", updateError.message);
      return null;
    }
    return existing.id;
  }

  const { data: inserted, error: insertError } = await admin
    .from("conversations")
    .insert({
      org_id: orgId,
      contact_phone: contactPhone,
      contact_name: contactName,
      last_message_at: nowIso,
      unread_count: 1,
    })
    .select("id")
    .single();

  if (insertError) {
    console.error("[webhooks/meta] failed to insert conversation:", insertError.message);
    return null;
  }
  return inserted.id as string;
}
