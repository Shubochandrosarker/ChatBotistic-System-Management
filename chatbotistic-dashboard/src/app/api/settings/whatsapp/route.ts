import crypto from "node:crypto";
import { NextRequest, NextResponse } from "next/server";
import { resolveOrgContext } from "@/lib/org-context";
import { encrypt, decrypt } from "@/lib/encryption";
import { TWILIO_WEBHOOK_PATH } from "@/lib/whatsapp/twilio-provider";

const META_API_VERSION = "v20.0"; // mirrors src/lib/whatsapp/meta-provider.ts

/**
 * GET/POST /api/settings/whatsapp
 *
 * Upserts + reads the org's `whatsapp_connections` row. This has to be
 * a server route (rather than a direct browser-client write, which
 * RLS would otherwise allow — whatsapp_connections has a `FOR ALL` org
 * policy) because secrets must be encrypted with
 * `src/lib/encryption.ts` before they ever touch the database, and
 * `ENCRYPTION_KEY` is a server-only secret. The GET handler exists so
 * the Settings UI can render current connection state (provider,
 * phone number, status, webhook token/URL) without ever getting the
 * decrypted secret back — GET never selects `credentials_encrypted`.
 *
 * POST body:
 *   { provider: "personal", phoneNumber: string }
 *   { provider: "meta", phoneNumber?: string, phoneNumberId: string, wabaId: string,
 *     accessToken: string, appSecret: string }
 *     — accessToken/appSecret may be omitted (kept unchanged) when
 *       updating an existing Meta connection; both are required on a
 *       fresh connection.
 *   { provider: "twilio", phoneNumber?: string, accountSid: string, authToken: string,
 *     whatsappNumber: string }
 *     — authToken may be omitted (kept unchanged) when updating an
 *       existing Twilio connection.
 *   { action: "test" }         — live credential check against the provider's API.
 *   { action: "disconnect" }   — resets to personal/disconnected.
 */

interface ConnectionRow {
  provider: "personal" | "meta" | "twilio";
  phone_number: string | null;
  status: string;
  last_verified_at: string | null;
  webhook_verify_token: string | null;
  credentials_encrypted: string | null;
}

function siteUrl(request: NextRequest): string {
  return process.env.NEXT_PUBLIC_SITE_URL ?? request.nextUrl.origin;
}

/** Best-effort decrypt of a stored credentials blob; never throws. */
function tryDecryptCredentials(
  credentialsEncrypted: string | null,
): Record<string, unknown> | null {
  if (!credentialsEncrypted) return null;
  try {
    return JSON.parse(decrypt(credentialsEncrypted)) as Record<string, unknown>;
  } catch {
    return null;
  }
}

export async function GET(request: NextRequest) {
  const result = await resolveOrgContext();
  if (!result.ok) return result.response;
  const { ctx } = result;

  const { data, error } = await ctx.supabase
    .from("whatsapp_connections")
    .select("provider, phone_number, status, last_verified_at, webhook_verify_token")
    .eq("org_id", ctx.org.id)
    .maybeSingle();
  if (error) return NextResponse.json({ error: error.message }, { status: 500 });

  const row = data as Omit<ConnectionRow, "credentials_encrypted"> | null;

  return NextResponse.json({
    provider: row?.provider ?? "personal",
    phoneNumber: row?.phone_number ?? null,
    status: row?.status ?? "disconnected",
    lastVerifiedAt: row?.last_verified_at ?? null,
    webhookVerifyToken: row?.provider === "meta" ? row.webhook_verify_token ?? null : null,
    metaWebhookUrl: `${siteUrl(request)}/api/webhooks/meta`,
    twilioWebhookUrl: `${siteUrl(request)}${TWILIO_WEBHOOK_PATH}`,
  });
}

export async function POST(request: NextRequest) {
  const result = await resolveOrgContext();
  if (!result.ok) return result.response;
  const { ctx } = result;

  let body: Record<string, unknown>;
  try {
    body = await request.json();
  } catch {
    return NextResponse.json({ error: "Invalid JSON body." }, { status: 400 });
  }

  if (body.action === "disconnect") {
    const { error } = await ctx.supabase
      .from("whatsapp_connections")
      .upsert(
        {
          org_id: ctx.org.id,
          provider: "personal",
          phone_number: null,
          credentials_encrypted: null,
          webhook_verify_token: null,
          last_verified_at: null,
          status: "disconnected",
        },
        { onConflict: "org_id" }
      );
    if (error) return NextResponse.json({ error: error.message }, { status: 500 });
    return NextResponse.json({ ok: true });
  }

  if (body.action === "test") {
    const { data, error } = await ctx.supabase
      .from("whatsapp_connections")
      .select("provider, phone_number, status, credentials_encrypted")
      .eq("org_id", ctx.org.id)
      .maybeSingle();
    if (error) return NextResponse.json({ error: error.message }, { status: 500 });

    const row = data as ConnectionRow | null;
    if (!row || row.status !== "connected") {
      return NextResponse.json(
        { ok: false, message: "No connection is saved yet." },
        { status: 200 }
      );
    }

    if (row.provider === "personal") {
      return NextResponse.json({
        ok: true,
        message: "Personal number saved. Messaging still happens from your own WhatsApp app.",
      });
    }

    const creds = tryDecryptCredentials(row.credentials_encrypted);
    if (!creds) {
      return NextResponse.json({
        ok: false,
        message: "Stored credentials could not be read. Please reconnect and save again.",
      });
    }

    if (row.provider === "meta") {
      const phoneNumberId = String(creds.phoneNumberId ?? "");
      const accessToken = String(creds.accessToken ?? "");
      if (!phoneNumberId || !accessToken) {
        return NextResponse.json({
          ok: false,
          message: "Stored Meta credentials are incomplete. Please reconnect and save again.",
        });
      }
      try {
        const url =
          `https://graph.facebook.com/${META_API_VERSION}/${phoneNumberId}` +
          `?fields=display_phone_number&access_token=${encodeURIComponent(accessToken)}`;
        const res = await fetch(url, { method: "GET" });
        const json = (await res.json().catch(() => ({}))) as {
          display_phone_number?: string;
          error?: { message?: string };
        };
        if (!res.ok) {
          return NextResponse.json({
            ok: false,
            message: json.error?.message || `Meta rejected the request (HTTP ${res.status}).`,
          });
        }
        return NextResponse.json({
          ok: true,
          message: `Verified — Meta returned phone number ${json.display_phone_number ?? phoneNumberId}.`,
        });
      } catch (err) {
        return NextResponse.json({
          ok: false,
          message: err instanceof Error ? `Couldn't reach Meta: ${err.message}` : "Couldn't reach Meta.",
        });
      }
    }

    // row.provider === "twilio"
    const accountSid = String(creds.accountSid ?? "");
    const authToken = String(creds.authToken ?? "");
    if (!accountSid || !authToken) {
      return NextResponse.json({
        ok: false,
        message: "Stored Twilio credentials are incomplete. Please reconnect and save again.",
      });
    }
    try {
      const authHeader = "Basic " + Buffer.from(`${accountSid}:${authToken}`).toString("base64");
      const res = await fetch(`https://api.twilio.com/2010-04-01/Accounts/${accountSid}.json`, {
        method: "GET",
        headers: { Authorization: authHeader },
      });
      const json = (await res.json().catch(() => ({}))) as {
        friendly_name?: string;
        message?: string;
      };
      if (!res.ok) {
        return NextResponse.json({
          ok: false,
          message: json.message || `Twilio rejected the request (HTTP ${res.status}).`,
        });
      }
      return NextResponse.json({
        ok: true,
        message: `Verified — Twilio account "${json.friendly_name ?? accountSid}" is active.`,
      });
    } catch (err) {
      return NextResponse.json({
        ok: false,
        message: err instanceof Error ? `Couldn't reach Twilio: ${err.message}` : "Couldn't reach Twilio.",
      });
    }
  }

  const provider =
    body.provider === "meta" ? "meta" : body.provider === "twilio" ? "twilio" : "personal";

  if (provider === "personal") {
    const phoneNumber = typeof body.phoneNumber === "string" ? body.phoneNumber.trim() : "";
    if (!phoneNumber) {
      return NextResponse.json({ error: "Phone number is required." }, { status: 400 });
    }
    const { error } = await ctx.supabase.from("whatsapp_connections").upsert(
      {
        org_id: ctx.org.id,
        provider: "personal",
        phone_number: phoneNumber,
        credentials_encrypted: null,
        webhook_verify_token: null,
        status: "connected",
        last_verified_at: new Date().toISOString(),
      },
      { onConflict: "org_id" }
    );
    if (error) return NextResponse.json({ error: error.message }, { status: 500 });
    return NextResponse.json({ ok: true });
  }

  // Look up the existing row once — used both to reuse omitted secrets
  // (so re-saving to tweak one field doesn't force re-pasting tokens)
  // and to reuse an already-issued Meta webhook_verify_token.
  const { data: existingData, error: existingError } = await ctx.supabase
    .from("whatsapp_connections")
    .select("provider, credentials_encrypted, webhook_verify_token")
    .eq("org_id", ctx.org.id)
    .maybeSingle();
  if (existingError) return NextResponse.json({ error: existingError.message }, { status: 500 });
  const existing = existingData as
    | { provider: string; credentials_encrypted: string | null; webhook_verify_token: string | null }
    | null;
  const existingCreds =
    existing && existing.provider === provider
      ? tryDecryptCredentials(existing.credentials_encrypted)
      : null;

  const phoneNumber = typeof body.phoneNumber === "string" ? body.phoneNumber.trim() : "";

  if (provider === "meta") {
    const phoneNumberId = typeof body.phoneNumberId === "string" ? body.phoneNumberId.trim() : "";
    const wabaId = typeof body.wabaId === "string" ? body.wabaId.trim() : "";
    const accessTokenInput = typeof body.accessToken === "string" ? body.accessToken.trim() : "";
    const appSecretInput = typeof body.appSecret === "string" ? body.appSecret.trim() : "";

    const accessToken = accessTokenInput || String(existingCreds?.accessToken ?? "");
    const appSecret = appSecretInput || String(existingCreds?.appSecret ?? "");

    if (!phoneNumberId || !wabaId || !accessToken || !appSecret) {
      return NextResponse.json(
        {
          error:
            "Phone number ID, WABA ID, access token, and app secret are all required.",
        },
        { status: 400 }
      );
    }

    const webhookVerifyToken = existing?.webhook_verify_token ?? crypto.randomBytes(24).toString("hex");
    const credentials = encrypt(
      JSON.stringify({ phoneNumberId, wabaId, accessToken, appSecret })
    );

    const { error } = await ctx.supabase.from("whatsapp_connections").upsert(
      {
        org_id: ctx.org.id,
        provider: "meta",
        phone_number: phoneNumber || null,
        credentials_encrypted: credentials,
        webhook_verify_token: webhookVerifyToken,
        status: "connected",
        last_verified_at: new Date().toISOString(),
      },
      { onConflict: "org_id" }
    );
    if (error) return NextResponse.json({ error: error.message }, { status: 500 });
    return NextResponse.json({ ok: true, webhookVerifyToken });
  }

  // provider === "twilio"
  const accountSid = typeof body.accountSid === "string" ? body.accountSid.trim() : "";
  const authTokenInput = typeof body.authToken === "string" ? body.authToken.trim() : "";
  const whatsappNumber = typeof body.whatsappNumber === "string" ? body.whatsappNumber.trim() : "";

  const authToken = authTokenInput || String(existingCreds?.authToken ?? "");

  if (!accountSid || !authToken || !whatsappNumber) {
    return NextResponse.json(
      { error: "Account SID, auth token, and WhatsApp number are all required." },
      { status: 400 }
    );
  }

  const credentials = encrypt(JSON.stringify({ accountSid, authToken, whatsappNumber }));

  const { error } = await ctx.supabase.from("whatsapp_connections").upsert(
    {
      org_id: ctx.org.id,
      provider: "twilio",
      phone_number: phoneNumber || whatsappNumber || null,
      credentials_encrypted: credentials,
      webhook_verify_token: null,
      status: "connected",
      last_verified_at: new Date().toISOString(),
    },
    { onConflict: "org_id" }
  );
  if (error) return NextResponse.json({ error: error.message }, { status: 500 });
  return NextResponse.json({ ok: true });
}
