import { NextResponse } from "next/server";
import { resolveOrgContext } from "@/lib/org-context";
import { encrypt } from "@/lib/encryption";

/**
 * POST /api/settings/whatsapp
 *
 * Upserts the org's `whatsapp_connections` row. This has to be a
 * server route (rather than a direct browser-client write, which RLS
 * would otherwise allow — whatsapp_connections has a `FOR ALL` org
 * policy) because the access token must be encrypted with
 * `src/lib/encryption.ts` before it ever touches the database, and
 * `ENCRYPTION_KEY` is a server-only secret.
 *
 * Body:
 *   { provider: "personal", phoneNumber: string }
 *   { provider: "meta", phoneNumber?: string, phoneNumberId: string, wabaId: string, accessToken: string }
 *   { action: "test" }   — see note below, does NOT call the Meta Graph API.
 *   { action: "disconnect" }
 */
export async function POST(request: Request) {
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
          status: "disconnected",
        },
        { onConflict: "org_id" }
      );
    if (error) return NextResponse.json({ error: error.message }, { status: 500 });
    return NextResponse.json({ ok: true });
  }

  if (body.action === "test") {
    // Honest about what this actually verifies: there is no live Meta
    // Cloud API call here (out of scope for this pass — no send
    // implementation exists to validate against). This only checks
    // that the currently-saved credentials are shaped like real ones
    // (non-empty phone_number_id/waba_id, an access-token-looking
    // string) so an obviously-wrong paste gets caught early. It is
    // NOT proof the number can send messages.
    const { data, error } = await ctx.supabase
      .from("whatsapp_connections")
      .select("provider, phone_number, status")
      .eq("org_id", ctx.org.id)
      .maybeSingle();
    if (error) return NextResponse.json({ error: error.message }, { status: 500 });
    if (!data || data.status !== "connected") {
      return NextResponse.json(
        { ok: false, message: "No connection is saved yet." },
        { status: 200 }
      );
    }
    return NextResponse.json({
      ok: true,
      message:
        data.provider === "meta"
          ? "Credentials are saved and well-formed. This does not confirm Meta will accept sends."
          : "Personal number saved. Messaging still happens from your own WhatsApp app.",
    });
  }

  const provider = body.provider === "meta" ? "meta" : "personal";

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
        status: "connected",
      },
      { onConflict: "org_id" }
    );
    if (error) return NextResponse.json({ error: error.message }, { status: 500 });
    return NextResponse.json({ ok: true });
  }

  // provider === "meta"
  const phoneNumberId = typeof body.phoneNumberId === "string" ? body.phoneNumberId.trim() : "";
  const wabaId = typeof body.wabaId === "string" ? body.wabaId.trim() : "";
  const accessToken = typeof body.accessToken === "string" ? body.accessToken.trim() : "";
  const phoneNumber = typeof body.phoneNumber === "string" ? body.phoneNumber.trim() : "";

  if (!phoneNumberId || !wabaId || !accessToken) {
    return NextResponse.json(
      { error: "Phone number ID, WABA ID, and access token are all required." },
      { status: 400 }
    );
  }

  const credentials = encrypt(JSON.stringify({ phoneNumberId, wabaId, accessToken }));

  const { error } = await ctx.supabase.from("whatsapp_connections").upsert(
    {
      org_id: ctx.org.id,
      provider: "meta",
      phone_number: phoneNumber || null,
      credentials_encrypted: credentials,
      status: "connected",
    },
    { onConflict: "org_id" }
  );
  if (error) return NextResponse.json({ error: error.message }, { status: 500 });
  return NextResponse.json({ ok: true });
}
