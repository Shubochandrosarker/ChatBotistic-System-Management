import "server-only";
import { decrypt } from "@/lib/encryption";
import { supabaseAdmin } from "@/lib/supabase/admin";
import { createProvider } from "./factory";
import type { StoredWhatsAppCredentials, WhatsAppProvider } from "./types";

/** Shape of a `whatsapp_connections` row (see migrations 004 + 007). */
export interface WhatsAppConnectionRow {
  id: string;
  org_id: string;
  provider: "personal" | "meta" | "twilio";
  phone_number: string | null;
  credentials_encrypted: string | null;
  webhook_verify_token: string | null;
  status: string;
  last_verified_at: string | null;
  created_at: string;
  updated_at: string;
}

/**
 * Bridge between an org's `whatsapp_connections` row and a live
 * `WhatsAppProvider`. Always reads via the service-role client — this
 * needs to work from webhook routes and background jobs with no
 * signed-in session, not just requests carrying the caller's own
 * Supabase session.
 *
 * Returns `null` (never throws for "no connection") when:
 *   - the org has no `whatsapp_connections` row yet,
 *   - `status !== 'connected'`,
 *   - `provider === 'personal'` (credential-less, leads-only — no
 *     send/receive capability by design), or
 *   - the stored credentials fail to decrypt or parse.
 *
 * Call sites (campaign sending, inbox replies, webhook routes) should
 * treat `null` as "this org can't send/receive via API right now" and
 * surface that rather than throwing.
 */
export async function loadProviderForOrg(
  orgId: string,
): Promise<{ provider: WhatsAppProvider; connection: WhatsAppConnectionRow } | null> {
  const admin = supabaseAdmin();
  const { data, error } = await admin
    .from("whatsapp_connections")
    .select("*")
    .eq("org_id", orgId)
    .maybeSingle();

  if (error) {
    console.error(
      `[whatsapp] failed to load connection for org ${orgId}:`,
      error.message,
    );
    return null;
  }

  const connection = data as WhatsAppConnectionRow | null;
  if (!connection) return null;
  if (connection.status !== "connected") return null;
  if (connection.provider === "personal") return null;
  if (!connection.credentials_encrypted) return null;

  let creds: StoredWhatsAppCredentials;
  try {
    const parsed = JSON.parse(decrypt(connection.credentials_encrypted)) as Record<
      string,
      unknown
    >;
    creds = { ...parsed, provider: connection.provider } as StoredWhatsAppCredentials;
  } catch (err) {
    console.error(
      `[whatsapp] failed to decrypt/parse credentials for org ${orgId}:`,
      err instanceof Error ? err.message : err,
    );
    return null;
  }

  try {
    return { provider: createProvider(creds), connection };
  } catch (err) {
    console.error(
      `[whatsapp] failed to construct provider for org ${orgId}:`,
      err instanceof Error ? err.message : err,
    );
    return null;
  }
}
