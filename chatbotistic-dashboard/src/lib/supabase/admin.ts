import "server-only";
import { createClient, type SupabaseClient } from "@supabase/supabase-js";

/**
 * Service-role client — bypasses RLS. Server-only; used by SSO
 * provisioning, webhooks, cron sync, and quota RPCs.
 */
export function supabaseAdmin(): SupabaseClient {
  const url =
    process.env.NEXT_PUBLIC_SUPABASE_URL || "https://placeholder.supabase.co";
  const key =
    process.env.SUPABASE_SERVICE_ROLE_KEY || "placeholder-service-role-key";
  return createClient(url, key, {
    auth: { autoRefreshToken: false, persistSession: false },
  });
}
